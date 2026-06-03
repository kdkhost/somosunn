<?php

namespace App\Notifications;

use App\Services\Mail\SystemMailTemplateService;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * O token de reset de senha.
     */
    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());

        if (EmailQueueSettings::shouldQueue() && EmailQueueSettings::delaySeconds() > 0) {
            $this->delay(now()->addSeconds(EmailQueueSettings::delaySeconds()));
        }
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = (int) config('auth.passwords.users.expire', 60);

        return app(SystemMailTemplateService::class)->mailMessage('password_reset', [
            'user' => [
                'name' => $notifiable->name ?? 'Usuario',
                'email' => $notifiable->email,
            ],
            'reset' => [
                'url' => $url,
                'expire_minutes' => $expireMinutes,
            ],
        ], [
            'name' => 'Redefinicao de Senha',
            'category' => 'sistema',
            'subject' => 'Redefinicao de Senha - {{site.name}}',
            'body' => '<h2>Ola, {{user.name}}!</h2><p>Recebemos uma solicitacao de redefinicao de senha.</p><p><a href="{{reset.url}}">Redefinir senha</a></p><p>Este link expira em {{reset.expire_minutes}} minutos.</p>',
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
