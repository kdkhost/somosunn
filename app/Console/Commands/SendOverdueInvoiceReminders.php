<?php

namespace App\Console\Commands;

use App\Jobs\SendGenericTemplateEmail;
use App\Models\Invoice;
use App\Models\MailTemplate;
use App\Models\Setting;
use App\Services\Mail\SystemMailLayoutData;
use Illuminate\Console\Command;

class SendOverdueInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-overdue-reminders';
    protected $description = 'Envia lembretes de faturas em atraso';

    public function handle()
    {
        if ((int) Setting::get('cron_invoices_enabled', 1) !== 1) {
            $this->info('Cron de faturas desativado.');
            return self::SUCCESS;
        }

        $invoices = Invoice::with('user')
            ->where('status', 'pending')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->where(function ($q) {
                $q->whereNull('last_reminder_at')
                    ->orWhere('last_reminder_at', '<', now()->subDays(3));
            })
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('Nenhuma fatura em atraso para lembrar.');
            return self::SUCCESS;
        }

        $template = MailTemplate::where('slug', 'invoice_overdue')->where('is_active', true)->first();
        if (!$template) {
            $template = MailTemplate::firstOrCreate(
                ['slug' => 'invoice_overdue'],
                [
                    'name' => 'Fatura em Atraso',
                    'category' => 'financeiro',
                    'subject' => 'Fatura {{invoice.number}} em atraso - {{site.name}}',
                    'body' => '<h2>Olá, {{user.name}}!</h2>
<p>Sua fatura <strong>{{invoice.number}}</strong> no valor de <strong>{{invoice.amount}}</strong> está em atraso desde <strong>{{invoice.due_date}}</strong>.</p>
<p>Por favor, regularize o pagamento para manter seu acesso ativo.</p>
<p style="text-align: center; margin: 26px 0;">
    <a href="{{site.url}}/painel/compras" style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold;">Ver meus pedidos</a>
</p>',
                    'is_active' => true,
                    'locale' => 'pt-BR',
                ]
            );
        }

        $layout = app(SystemMailLayoutData::class)->make();
        $count = 0;

        foreach ($invoices as $invoice) {
            if (!$invoice->user || !$invoice->user->email) continue;

            $data = [
                'user' => ['name' => $invoice->user->name],
                'invoice' => [
                    'number' => $invoice->number ?: '#' . $invoice->id,
                    'amount' => 'R$ ' . number_format((float) $invoice->total_amount, 2, ',', '.'),
                    'due_date' => optional($invoice->due_at)->format('d/m/Y') ?? '-',
                ],
                'site' => ['name' => $layout['siteName'], 'primary_color' => $layout['primaryColor'], 'url' => url('/')],
            ];

            $rendered = (string) $template->body;
            $subject = (string) $template->subject;

            foreach ($data as $key => $values) {
                foreach ($values as $k => $v) {
                    $pattern = '/\{\{\s*' . $key . '\.' . $k . '\s*\}\}/';
                    $rendered = preg_replace($pattern, (string) $v, $rendered);
                    $subject = preg_replace($pattern, (string) $v, $subject);
                }
            }

            SendGenericTemplateEmail::dispatch($invoice->user->email, $subject, $rendered);

            $invoice->update(['last_reminder_at' => now()]);
            $count++;
        }

        $this->info("Lembretes de fatura enviados: {$count}");
        return self::SUCCESS;
    }
}
