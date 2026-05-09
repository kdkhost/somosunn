<?php

namespace App\Console\Commands;

use App\Jobs\SendGenericTemplateEmail;
use App\Models\MailTemplate;
use App\Models\Order;
use App\Models\Setting;
use App\Services\Mail\SystemMailLayoutData;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAbandonedCartEmails extends Command
{
    protected $signature = 'abandoned-cart:send';
    protected $description = 'Envia lembretes de carrinho abandonado (pedidos pendentes entre 24h e 48h)';

    public function handle(): int
    {
        if ((int) Setting::get('cron_abandoned_cart_enabled', 1) !== 1) {
            $this->info('Cron de carrinho abandonado desativado.');
            return self::SUCCESS;
        }

        $startWindow = Carbon::now()->subHours(48);
        $endWindow = Carbon::now()->subHours(24);

        $orders = Order::with('user')
            ->where('status', 'pending')
            ->whereBetween('created_at', [$startWindow, $endWindow])
            ->whereHas('user')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Nenhum carrinho abandonado encontrado.');
            return self::SUCCESS;
        }

        $template = MailTemplate::where('slug', 'abandoned-cart')
            ->where('is_active', true)
            ->first();

        if (!$template) {
            $template = MailTemplate::firstOrCreate(
                ['slug' => 'abandoned-cart'],
                [
                    'name' => 'Carrinho Abandonado',
                    'category' => 'marketplace',
                    'subject' => 'Você esqueceu algo no carrinho - {{site.name}}',
                    'body' => '<h2>Olá, {{user.name}}!</h2>
<p>Notamos que você tem um pedido pendente (<strong>#{{order.id}}</strong>) no valor de <strong>{{order.total}}</strong> desde <strong>{{order.date}}</strong>.</p>
<p>Não perca essa oportunidade! Finalize sua compra agora mesmo.</p>
<p style="text-align: center; margin: 26px 0;">
    <a href="{{order.link}}" style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold;">Finalizar Compra</a>
</p>
<p style="color: #666; font-size: 13px;">Se você já realizou o pagamento, desconsidere este e-mail.</p>',
                    'is_active' => true,
                    'locale' => 'pt-BR',
                ]
            );
        }

        $layout = app(SystemMailLayoutData::class)->make();
        $count = 0;

        foreach ($orders as $order) {
            $metadata = $order->metadata ?? [];
            if (isset($metadata['abandoned_email_sent_at'])) {
                continue;
            }

            if (!$order->user || !$order->user->email) {
                continue;
            }

            $data = [
                'user' => ['name' => $order->user->name, 'email' => $order->user->email],
                'order' => [
                    'id' => (string) $order->id,
                    'total' => 'R$ ' . number_format((float) $order->total_amount, 2, ',', '.'),
                    'date' => $order->created_at->format('d/m/Y'),
                    'link' => url('/painel/compras'),
                ],
                'site' => [
                    'name' => $layout['siteName'],
                    'primary_color' => $layout['primaryColor'],
                    'url' => url('/'),
                ],
            ];

            $rendered = (string) $template->body;
            $subject = (string) $template->subject;

            foreach ($data as $key => $values) {
                foreach ($values as $k => $v) {
                    $pattern = '/\{\{\s*' . preg_quote($key, '/') . '\.' . preg_quote($k, '/') . '\s*\}\}/';
                    $rendered = preg_replace($pattern, (string) $v, $rendered);
                    $subject = preg_replace($pattern, (string) $v, $subject);
                }
            }

            SendGenericTemplateEmail::dispatch($order->user->email, $subject, $rendered);

            $metadata['abandoned_email_sent_at'] = now()->toIso8601String();
            $order->update(['metadata' => $metadata]);
            $count++;
        }

        $this->info("Emails de carrinho abandonado enviados: {$count}");
        Log::info("abandoned-cart:send - {$count} emails enviados.");
        return self::SUCCESS;
    }
}
