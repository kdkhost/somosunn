<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\Mail\SystemMailLayoutData;
use App\Services\Mail\SystemMailTemplateService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class OrderPendingPaymentNotificationService
{
    public function sendReminder(Order $order, int $reminderHour, int $cancelAfterHours): bool
    {
        $data = $this->makeData($order, $cancelAfterHours);
        $data['reminder'] = [
            'hour' => (string) $reminderHour,
            'label' => $reminderHour . 'h',
        ];

        return $this->sendTemplate($order, 'order_unpaid_payment_reminder', $data, [
            'name' => 'Lembrete de pedido nao pago',
            'category' => 'pedidos',
            'locale' => 'pt-BR',
            'subject' => 'Pedido #{{order.id}} aguardando pagamento - {{site.name}}',
            'body' => '<h2>Ola, {{user.name}}!</h2>
<p>Seu pedido <strong>#{{order.id}}</strong> ainda esta aguardando pagamento.</p>
<p><strong>Valor:</strong> {{order.total}}<br><strong>Compra feita em:</strong> {{order.created_at}}<br><strong>Prazo para pagamento:</strong> {{order.cancel_at}}</p>
<p>{{order.items_html}}</p>
<p style="text-align:center;margin:26px 0;">
    <a href="{{order.payment_url}}" style="display:inline-block;background-color:{{site.primary_color}};color:#ffffff;padding:14px 28px;text-decoration:none;border-radius:8px;font-weight:bold;">Finalizar pagamento</a>
</p>
<p style="color:#666;font-size:13px;">Se voce ja pagou, desconsidere este e-mail. O sistema confirmara o pagamento automaticamente quando o gateway retornar a aprovacao.</p>',
            'is_active' => true,
        ]);
    }

    public function sendAutoCancellation(Order $order, int $cancelAfterHours, string $reason): bool
    {
        $data = $this->makeData($order, $cancelAfterHours);
        $data['cancellation'] = [
            'reason' => $reason,
            'cancelled_at' => now($this->timezone())->format('d/m/Y H:i'),
        ];

        return $this->sendTemplate($order, 'order_unpaid_auto_cancelled', $data, [
            'name' => 'Pedido nao pago cancelado automaticamente',
            'category' => 'pedidos',
            'locale' => 'pt-BR',
            'subject' => 'Pedido #{{order.id}} cancelado automaticamente - {{site.name}}',
            'body' => '<h2>Ola, {{user.name}}!</h2>
<p>Seu pedido <strong>#{{order.id}}</strong> foi cancelado automaticamente porque o pagamento nao foi identificado dentro do prazo de <strong>{{order.cancel_after_hours}} horas</strong>.</p>
<p><strong>Valor:</strong> {{order.total}}<br><strong>Compra feita em:</strong> {{order.created_at}}<br><strong>Cancelado em:</strong> {{cancellation.cancelled_at}}</p>
<p>{{order.items_html}}</p>
<p>Se ainda tiver interesse, acesse a plataforma e refaca a compra.</p>
<p style="text-align:center;margin:26px 0;">
    <a href="{{site.url}}" style="display:inline-block;background-color:{{site.primary_color}};color:#ffffff;padding:14px 28px;text-decoration:none;border-radius:8px;font-weight:bold;">Acessar plataforma</a>
</p>',
            'is_active' => true,
        ]);
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,mixed> $defaults
     */
    private function sendTemplate(Order $order, string $slug, array $data, array $defaults): bool
    {
        $order->loadMissing('user');
        $to = trim((string) ($order->user->email ?? ''));

        if ($to === '') {
            Log::warning('order.pending_payment_email.no_recipient', [
                'order_id' => $order->id,
                'template' => $slug,
            ]);

            return false;
        }

        $bcc = $this->adminBccEmails($to);

        try {
            return app(SystemMailTemplateService::class)->send(
                $slug,
                $to,
                $data,
                $defaults,
                function ($message) use ($bcc): void {
                    if (!empty($bcc)) {
                        $message->bcc($bcc);
                    }
                }
            );
        } catch (\Throwable $e) {
            Log::error('order.pending_payment_email.failed', [
                'order_id' => $order->id,
                'template' => $slug,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function makeData(Order $order, int $cancelAfterHours): array
    {
        $order->loadMissing('user', 'items');

        $layout = app(SystemMailLayoutData::class)->make();
        $createdAt = $order->created_at
            ? $order->created_at->copy()->timezone($this->timezone())
            : now($this->timezone());
        $cancelAt = $createdAt->copy()->addHours($cancelAfterHours);

        return [
            'site' => [
                'name' => $layout['siteName'],
                'logo' => $layout['logoUrl'],
                'primary_color' => $layout['primaryColor'],
                'secondary_color' => $layout['secondaryColor'],
                'url' => url('/'),
            ],
            'user' => [
                'name' => (string) ($order->user->name ?? 'cliente'),
                'email' => (string) ($order->user->email ?? ''),
            ],
            'order' => [
                'id' => (string) $order->id,
                'total' => $this->money((float) $order->total_amount),
                'created_at' => $createdAt->format('d/m/Y H:i'),
                'cancel_at' => $cancelAt->format('d/m/Y H:i'),
                'cancel_after_hours' => (string) $cancelAfterHours,
                'payment_method' => $this->paymentMethodLabel((string) ($order->payment_method ?: $order->gateway)),
                'payment_url' => $this->paymentUrl($order),
                'items_html' => $this->itemsHtml($order),
                'items_text' => $this->itemsText($order),
            ],
        ];
    }

    /**
     * @return array<int,string>
     */
    private function adminBccEmails(string $clientEmail): array
    {
        if ((int) Setting::get('orders_unpaid_admin_bcc_enabled', 1) !== 1) {
            return [];
        }

        $emails = [];

        $configuredBcc = (string) Setting::get('smtp_bcc', '');
        foreach (preg_split('/[,;\s]+/', $configuredBcc) ?: [] as $email) {
            $email = strtolower(trim((string) $email));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }

        try {
            $adminEmails = User::query()
                ->whereNotNull('email')
                ->where(function ($query): void {
                    $query->whereIn('role', ['admin', 'superadmin'])
                        ->orWhereIn('level', ['superadmin', 'sucesso']);
                })
                ->pluck('email')
                ->all();

            foreach ($adminEmails as $email) {
                $email = strtolower(trim((string) $email));
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $email;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('order.pending_payment_email.admin_bcc_failed', [
                'error' => $e->getMessage(),
            ]);
        }

        $clientEmail = strtolower(trim($clientEmail));

        return array_values(array_diff(array_unique($emails), [$clientEmail]));
    }

    private function paymentUrl(Order $order): string
    {
        foreach ([
            'mercadopago_init_point',
            'mercadopago_sandbox_init_point',
            'sumup_checkout_url',
            'checkout_url',
        ] as $key) {
            $url = trim((string) data_get($order->metadata, $key, ''));
            if ($url !== '') {
                return $url;
            }
        }

        return Route::has('panel.purchases.index') ? route('panel.purchases.index') : url('/painel/compras');
    }

    private function itemsHtml(Order $order): string
    {
        $items = $order->items->map(function ($item): string {
            $quantity = max(1, (int) ($item->quantity ?? 1));
            $title = e((string) ($item->title ?: 'Item do pedido'));
            $total = $this->money((float) $item->price * $quantity);

            return $title . ' x' . $quantity . ' - ' . $total;
        })->filter()->values();

        if ($items->isEmpty()) {
            return 'Itens do pedido nao informados.';
        }

        return '<strong>Itens:</strong><br>' . $items->implode('<br>');
    }

    private function itemsText(Order $order): string
    {
        return strip_tags(str_replace('<br>', ', ', $this->itemsHtml($order)));
    }

    private function paymentMethodLabel(string $method): string
    {
        $method = strtolower(trim($method));

        return match (true) {
            str_contains($method, 'pix') => 'Pix',
            str_contains($method, 'ticket'), str_contains($method, 'boleto') => 'Boleto',
            str_contains($method, 'card'), str_contains($method, 'credit') => 'Cartao',
            $method !== '' => ucfirst(str_replace(['_', '-'], ' ', $method)),
            default => 'Nao informado',
        };
    }

    private function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    private function timezone(): string
    {
        $timezone = trim((string) Setting::get('system_timezone', config('app.timezone', 'America/Sao_Paulo')));

        return $timezone !== '' ? $timezone : 'America/Sao_Paulo';
    }
}
