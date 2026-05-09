<?php

namespace App\Console\Commands;

use App\Models\MailTemplate;
use App\Models\Setting;
use App\Models\User;
use App\Services\Mail\SystemMailLayoutData;
use App\Support\EmailQueueSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SubscriptionsCheckExpired extends Command
{
    protected $signature = 'subscriptions:check-expired';
    protected $description = 'Verifica assinaturas expiradas, envia lembretes de renovação e desativa planos vencidos';

    public function handle()
    {
        if ((int) Setting::get('cron_subscriptions_enabled', 1) !== 1) {
            $this->info('Cron de assinaturas desativado.');
            return self::SUCCESS;
        }

        $this->info('Verificando assinaturas...');

        $reminderDays = (int) Setting::get('subscription_reminder_days', 3);
        $now = now();

        // 1. Enviar lembretes para quem vai expirar em X dias
        $expiringUsers = User::whereNotNull('plan_id')
            ->whereNotNull('plan_expires_at')
            ->whereDate('plan_expires_at', '=', $now->copy()->addDays($reminderDays)->toDateString())
            ->get();

        $remindersCount = 0;
        foreach ($expiringUsers as $user) {
            $this->sendRenewalReminder($user);
            $remindersCount++;
        }

        // 2. Desativar planos expirados
        $expiredUsers = User::whereNotNull('plan_id')
            ->whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '<', $now)
            ->get();

        $expiredCount = 0;
        foreach ($expiredUsers as $user) {
            $this->expirePlan($user);
            $expiredCount++;
        }

        $this->info("Lembretes enviados: {$remindersCount} | Planos expirados: {$expiredCount}");

        return self::SUCCESS;
    }

    private function sendRenewalReminder(User $user): void
    {
        try {
            $plan = $user->plan;
            $daysLeft = (int) now()->diffInDays($user->plan_expires_at, false);

            $template = MailTemplate::where('slug', 'subscription_expiring')->where('is_active', true)->first();

            if (!$template) {
                $template = MailTemplate::firstOrCreate(
                    ['slug' => 'subscription_expiring'],
                    [
                        'name' => 'Lembrete de Renovação',
                        'category' => 'assinatura',
                        'subject' => 'Sua assinatura expira em {{subscription.days_left}} dias - {{site.name}}',
                        'body' => '<h2>Olá, {{user.name}}!</h2>
<p>Sua assinatura do plano <strong>{{subscription.plan_name}}</strong> expira em <strong>{{subscription.days_left}} dias</strong> ({{subscription.expires_at}}).</p>
<p>Renove agora para não perder acesso aos conteúdos exclusivos.</p>
<p style="text-align: center; margin: 26px 0;">
    <a href="{{subscription.renew_url}}" style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold;">Renovar Assinatura</a>
</p>
<p>Atenciosamente,<br>Equipe {{site.name}}</p>',
                        'is_active' => true,
                        'locale' => 'pt-BR',
                    ]
                );
            }

            $layout = app(SystemMailLayoutData::class)->make();

            $data = [
                'user' => ['name' => $user->name],
                'subscription' => [
                    'plan_name' => $plan->name ?? 'Premium',
                    'days_left' => (string) max(0, $daysLeft),
                    'expires_at' => $user->plan_expires_at->format('d/m/Y'),
                    'renew_url' => $plan ? route('subscription.checkout', $plan->id) : url('/premium'),
                ],
                'site' => [
                    'name' => $layout['siteName'],
                    'primary_color' => $layout['primaryColor'],
                ],
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

            Mail::to($user->email)->send(
                new \Illuminate\Mail\Mailable() === null // Usar view system
                    ? null
                    : (new class($subject, $rendered, $layout) extends \Illuminate\Mail\Mailable {
                        use \Illuminate\Bus\Queueable;
                        private string $subj;
                        private string $content;
                        private array $layout;

                        public function __construct(string $subject, string $content, array $layout)
                        {
                            $this->subj = $subject;
                            $this->content = $content;
                            $this->layout = $layout;
                            $this->onConnection(EmailQueueSettings::connection());
                            $this->onQueue(EmailQueueSettings::queueName());
                        }

                        public function build()
                        {
                            return $this->subject($this->subj)
                                ->view('emails.system', array_merge($this->layout, ['content' => $this->content]));
                        }
                    })
            );

            Log::info("Renewal reminder sent to {$user->email} (plan expires {$user->plan_expires_at})");
        } catch (\Throwable $e) {
            Log::error("Failed to send renewal reminder to {$user->email}: " . $e->getMessage());
        }
    }

    private function expirePlan(User $user): void
    {
        $planName = $user->plan->name ?? 'Premium';

        $user->update([
            'plan_id' => null,
            'plan_expires_at' => null,
        ]);

        // Enviar email de expiração
        try {
            $template = MailTemplate::where('slug', 'subscription_expired')->where('is_active', true)->first();

            if (!$template) {
                $template = MailTemplate::firstOrCreate(
                    ['slug' => 'subscription_expired'],
                    [
                        'name' => 'Assinatura Expirada',
                        'category' => 'assinatura',
                        'subject' => 'Sua assinatura expirou - {{site.name}}',
                        'body' => '<h2>Olá, {{user.name}}!</h2>
<p>Sua assinatura do plano <strong>{{subscription.plan_name}}</strong> expirou.</p>
<p>Renove agora para recuperar o acesso aos conteúdos exclusivos.</p>
<p style="text-align: center; margin: 26px 0;">
    <a href="{{site.url}}/premium" style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold;">Ver Planos</a>
</p>',
                        'is_active' => true,
                        'locale' => 'pt-BR',
                    ]
                );
            }

            // Enviar via queue (simplificado)
            $layout = app(SystemMailLayoutData::class)->make();
            $data = [
                'user' => ['name' => $user->name],
                'subscription' => ['plan_name' => $planName],
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

            // Dispatch como job simples
            \App\Jobs\SendGenericTemplateEmail::dispatch($user->email, $subject, $rendered);
        } catch (\Throwable $e) {
            Log::error("Failed to send expiration email to {$user->email}: " . $e->getMessage());
        }

        Log::info("Plan expired for user {$user->id} ({$user->email}). Plan '{$planName}' removed.");
    }
}
