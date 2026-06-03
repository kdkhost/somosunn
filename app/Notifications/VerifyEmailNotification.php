<?php

namespace App\Notifications;

use App\Services\Mail\SystemMailTemplateService;
use App\Support\EmailQueueSettings;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());

        if (EmailQueueSettings::shouldQueue() && EmailQueueSettings::delaySeconds() > 0) {
            $this->delay(now()->addSeconds(EmailQueueSettings::delaySeconds()));
        }
    }

    /**
     * Get the mail representation of the notification usando MailTemplate customizado.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        $expireMinutes = (int) config('auth.verification.expire', 60);

        return app(SystemMailTemplateService::class)->mailMessage('email_verification', [
            'user' => [
                'name' => $notifiable->name ?? 'Usuario',
                'email' => $notifiable->getEmailForVerification(),
            ],
            'verify' => [
                'url' => $verificationUrl,
                'expire_minutes' => $expireMinutes,
            ],
        ], [
            'name' => 'Verificacao de E-mail',
            'category' => 'sistema',
            'subject' => 'Confirme seu e-mail - {{site.name}}',
            'body' => '<h2>Ola, {{user.name}}!</h2><p>Confirme seu endereco de e-mail para ativar sua conta.</p><p><a href="{{verify.url}}">Confirmar meu e-mail</a></p><p>Este link expira em {{verify.expire_minutes}} minutos.</p>',
        ]);
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        $expireMinutes = (int) config('auth.verification.expire', 60);

        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes($expireMinutes),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
