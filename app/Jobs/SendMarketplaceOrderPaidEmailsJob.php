<?php

namespace App\Jobs;

use App\Models\Course;
use App\Models\Event;
use App\Models\MailTemplate;
use App\Models\Mentorship;
use App\Models\Order;
use App\Services\Mail\SystemMailLayoutData;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMarketplaceOrderPaidEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());

        if (EmailQueueSettings::shouldQueue() && EmailQueueSettings::delaySeconds() > 0) {
            $this->delay(now()->addSeconds(EmailQueueSettings::delaySeconds()));
        }
    }

    public function middleware()
    {
        return [new RateLimited('marketplace_email')];
    }

    public function handle(SystemMailLayoutData $layoutData): void
    {
        $order = Order::with(['items', 'user', 'seller'])->find($this->orderId);
        if (!$order) {
            return;
        }

        if ((string) ($order->status ?? '') !== 'paid') {
            return;
        }

        // Evita disparar para cobranças de plano/assinatura
        $context = (string) data_get($order->metadata, 'context', '');
        if ($context === 'subscription' || $context === 'plan') {
            return;
        }

        if (!$order->user || empty($order->user->email)) {
            return;
        }

        $shouldSend = (int) ($order->seller_id ?? 0) > 0 || in_array($context, ['course', 'mentorship', 'event', 'marketplace'], true);
        if (!$shouldSend) {
            return;
        }

        if ($this->markAsSentOnce($order->id)) {
            return;
        }

        $layout = $layoutData->make();

        $buyerData = $this->buildTemplateData($order, $layout);
        $this->sendTemplateIfExists('marketplace_order_paid_buyer', (string) $order->user->email, $buyerData, $layout);

        if ($order->seller && !empty($order->seller->email)) {
            $sellerData = $buyerData;
            $sellerData['seller'] = [
                'name' => (string) ($order->seller->name ?? 'Vendedor'),
                'email' => (string) ($order->seller->email ?? ''),
            ];
            $sellerData['links']['seller_panel_url'] = route('panel.marketplace.sales');

            $this->sendTemplateIfExists('marketplace_order_paid_seller', (string) $order->seller->email, $sellerData, $layout);
        }
    }

    private function markAsSentOnce(int $orderId): bool
    {
        try {
            $alreadySent = false;

            DB::transaction(function () use ($orderId, &$alreadySent) {
                $locked = Order::query()->whereKey($orderId)->lockForUpdate()->first();
                if (!$locked) {
                    $alreadySent = true;
                    return;
                }

                $meta = is_array($locked->metadata) ? $locked->metadata : [];
                $sentAt = data_get($meta, 'emails.marketplace_paid_sent_at');
                if ($sentAt) {
                    $alreadySent = true;
                    return;
                }

                $meta['emails'] = is_array($meta['emails'] ?? null) ? $meta['emails'] : [];
                $meta['emails']['marketplace_paid_sent_at'] = now()->toIso8601String();
                $locked->metadata = $meta;
                $locked->save();
            });

            return $alreadySent;
        } catch (\Throwable $e) {
            // Em caso de falha na marcação, ainda tenta enviar (melhor do que ficar sem e-mail).
            Log::warning('Marketplace email: falha ao marcar idempotência do pedido #' . $orderId . ': ' . $e->getMessage());
            return false;
        }
    }

    private function buildTemplateData(Order $order, array $layout): array
    {
        $order->loadMissing(['items', 'user', 'seller']);

        $site = [
            'name' => (string) ($layout['siteName'] ?? config('app.name', 'UNN')),
            'url' => url('/'),
            'logo' => (string) ($layout['logoUrl'] ?? asset('img/logo.svg')),
            'primary_color' => (string) ($layout['primaryColor'] ?? '#1F5EDB'),
            'secondary_color' => (string) ($layout['secondaryColor'] ?? '#177FD6'),
        ];

        $itemsHtml = $this->buildItemsHtml($order);

        return [
            'site' => $site,
            'user' => [
                'name' => (string) ($order->user->name ?? 'Cliente'),
                'email' => (string) ($order->user->email ?? ''),
            ],
            'buyer' => [
                'name' => (string) ($order->user->name ?? ''),
                'email' => (string) ($order->user->email ?? ''),
            ],
            'seller' => [
                'name' => (string) ($order->seller?->name ?? ''),
                'email' => (string) ($order->seller?->email ?? ''),
            ],
            'order' => [
                'id' => (string) $order->id,
                'date' => ($order->created_at ? $order->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i')),
                'total' => 'R$ ' . number_format((float) ($order->total_amount ?? 0), 2, ',', '.'),
                'items_html' => $itemsHtml,
            ],
            'links' => [
                'account_url' => route('login'),
                'seller_panel_url' => route('login'),
            ],
        ];
    }

    private function buildItemsHtml(Order $order): string
    {
        $order->loadMissing('items');
        if ($order->items->isEmpty()) {
            return '';
        }

        $primary = (string) \App\Models\Setting::get('site_color_primary', '#1F5EDB');

        $html = '<h3 style="margin: 0 0 10px 0; font-size: 16px; color: #111827;">Itens</h3>';
        $html .= '<ul style="margin: 0 0 22px 0; padding-left: 18px;">';

        foreach ($order->items as $item) {
            $title = e((string) ($item->title ?? 'Item'));
            $qty = (int) ($item->quantity ?? 1);
            $unit = (float) ($item->price ?? 0);
            $price = 'R$ ' . number_format($unit, 2, ',', '.');

            $accessUrl = $this->resolveAccessUrl((string) ($item->item_type ?? ''), (int) ($item->item_id ?? 0));
            $accessLink = $accessUrl ? '<div style="margin-top: 6px;"><a href="' . e($accessUrl) . '" style="color: ' . e($primary) . '; text-decoration: none;">Acessar</a></div>' : '';

            $qtyText = $qty > 1 ? ' (x' . $qty . ')' : '';
            $html .= '<li style="margin: 0 0 8px 0;">' . $title . $qtyText . ' — <strong>' . e($price) . '</strong>' . $accessLink . '</li>';
        }

        $html .= '</ul>';
        return $html;
    }

    private function resolveAccessUrl(string $type, int $id): ?string
    {
        try {
            if ($type === 'course' && $id > 0) {
                $course = Course::query()->select(['id', 'slug'])->find($id);
                if ($course) {
                    $param = $course->slug ?: $course->id;
                    return route('courses.show', $param);
                }
            }

            if ($type === 'mentorship' && $id > 0) {
                $mentorship = Mentorship::query()->select(['id'])->find($id);
                if ($mentorship) {
                    return route('mentorships.show', $mentorship->id);
                }
            }

            if ($type === 'event' && $id > 0) {
                $event = Event::query()->select(['id'])->find($id);
                if ($event) {
                    return route('events.show', $event->id);
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function sendTemplateIfExists(string $slug, string $toEmail, array $data, array $layout): void
    {
        $toEmail = trim($toEmail);
        if ($toEmail === '') {
            return;
        }

        $template = MailTemplate::query()->where('slug', $slug)->first();

        if (!$template) {
            $defaults = $this->defaultTemplates();
            if (isset($defaults[$slug])) {
                $template = MailTemplate::firstOrCreate(['slug' => $slug], $defaults[$slug]);
            }
        }

        if (!$template || !(bool) $template->is_active) {
            return;
        }

        try {
            [$subject, $content] = $this->renderTemplate($template, $data);

            $subject = trim(preg_replace('/\\s+/', ' ', strip_tags($subject)));
            if ($subject === '') {
                $subject = (string) ($template->name ?? 'Notificação');
            }

            $allowed = '<p><a><strong><em><ul><ol><li><br><img><table><tr><td><th><tbody><thead><h1><h2><h3><h4><h5><span><div><style><center>';
            $content = strip_tags($content, $allowed);

            $html = view('emails.system', array_merge($layout, [
                'content' => $content,
            ]))->render();

            Mail::html($html, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Marketplace email: falha ao enviar template ' . $slug . ' para ' . $toEmail . ': ' . $e->getMessage());
        }
    }

    private function defaultTemplates(): array
    {
        return [
            'marketplace_order_paid_buyer' => [
                'name' => 'Marketplace: Compra Confirmada (Cliente)',
                'category' => 'marketplace',
                'locale' => 'pt-BR',
                'subject' => 'Compra confirmada! Pedido #{{ $order[\'id\'] ?? \'\' }} - {{ $site[\'name\'] ?? \'\' }}',
                'body' => '<h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">Compra confirmada!</h2>
<p style="margin: 0 0 14px 0;">Olá, <strong>{{ $user[\'name\'] ?? \'Cliente\' }}</strong>.</p>
<p style="margin: 0 0 22px 0;">Recebemos a confirmação do seu pagamento. Seus produtos já estão disponíveis na sua conta.</p>
<div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 10px; margin: 0 0 18px 0;">
  <p style="margin: 0 0 6px 0;"><strong>Pedido:</strong> #{{ $order[\'id\'] ?? \'\' }}</p>
  <p style="margin: 0 0 6px 0;"><strong>Data:</strong> {{ $order[\'date\'] ?? \'\' }}</p>
  <p style="margin: 0;"><strong>Total:</strong> {{ $order[\'total\'] ?? \'\' }}</p>
</div>
{!! $order[\'items_html\'] ?? \'\' !!}
<p style="text-align: center; margin: 24px 0 26px 0;">
  <a href="{{ $links[\'account_url\'] ?? ($site[\'url\'] ?? \'#\') }}" style="display: inline-block; background-color: {{ $site[\'primary_color\'] ?? \'#1F5EDB\' }}; color: #ffffff; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 700;">Acessar minha conta</a>
</p>
<p style="margin: 0;">Obrigado,<br>{{ $site[\'name\'] ?? \'UNN\' }}</p>',
                'is_active' => true,
            ],
            'marketplace_order_paid_seller' => [
                'name' => 'Marketplace: Nova Venda (Vendedor)',
                'category' => 'marketplace',
                'locale' => 'pt-BR',
                'subject' => 'Nova venda! Pedido #{{ $order[\'id\'] ?? \'\' }} - {{ $site[\'name\'] ?? \'\' }}',
                'body' => '<h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">Você fez uma nova venda!</h2>
<p style="margin: 0 0 14px 0;">Olá, <strong>{{ $seller[\'name\'] ?? \'Vendedor\' }}</strong>.</p>
<p style="margin: 0 0 22px 0;">Uma compra foi aprovada no marketplace e já está registrada na sua conta.</p>
<div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 10px; margin: 0 0 18px 0;">
  <p style="margin: 0 0 6px 0;"><strong>Pedido:</strong> #{{ $order[\'id\'] ?? \'\' }}</p>
  <p style="margin: 0 0 6px 0;"><strong>Comprador:</strong> {{ $buyer[\'name\'] ?? \'\' }} ({{ $buyer[\'email\'] ?? \'\' }})</p>
  <p style="margin: 0;"><strong>Total do pedido:</strong> {{ $order[\'total\'] ?? \'\' }}</p>
</div>
{!! $order[\'items_html\'] ?? \'\' !!}
<p style="text-align: center; margin: 24px 0 26px 0;">
  <a href="{{ $links[\'seller_panel_url\'] ?? ($site[\'url\'] ?? \'#\') }}" style="display: inline-block; background-color: {{ $site[\'primary_color\'] ?? \'#1F5EDB\' }}; color: #ffffff; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 700;">Ver vendas no painel</a>
</p>
<p style="margin: 0;">Obrigado,<br>{{ $site[\'name\'] ?? \'UNN\' }}</p>',
                'is_active' => true,
            ],
        ];
    }

    private function renderTemplate(MailTemplate $template, array $data): array
    {
        $subject = (string) ($template->subject ?? '');
        $body = (string) ($template->body ?? '');

        try {
            $subjectRendered = (string) Blade::render($subject, $data);
            $bodyRendered = (string) Blade::render($body, $data);
            return [$subjectRendered, $bodyRendered];
        } catch (\Throwable $e) {
            // fallback: {{key.subkey}}
            $subjectRendered = $this->replacePlaceholders($subject, $data);
            $bodyRendered = $this->replacePlaceholders($body, $data);
            return [$subjectRendered, $bodyRendered];
        }
    }

    private function replacePlaceholders(string $text, array $data): string
    {
        $rendered = $text;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue)) {
                        continue;
                    }
                    $pattern = '/\\{\\{\\s*' . preg_quote((string) $key . '.' . (string) $subKey, '/') . '\\s*\\}\\}/';
                    $rendered = preg_replace($pattern, (string) $subValue, $rendered);
                }
                continue;
            }

            $pattern = '/\\{\\{\\s*' . preg_quote((string) $key, '/') . '\\s*\\}\\}/';
            $rendered = preg_replace($pattern, (string) $value, $rendered);
        }

        return (string) $rendered;
    }
}
