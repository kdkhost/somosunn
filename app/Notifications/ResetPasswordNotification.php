<?php

namespace App\Notifications;

use App\Models\MailTemplate;
use App\Services\Mail\SystemMailLayoutData;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
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

        $template = MailTemplate::where('slug', 'password_reset')->where('is_active', true)->first();

        if (!$template) {
            $template = MailTemplate::firstOrCreate(
                ['slug' => 'password_reset'],
                [
                    'name' => 'Redefinição de Senha',
                    'category' => 'sistema',
                    'subject' => 'Redefinição de Senha - {{site.name}}',
                    'body' => '<h2 style="margin: 0 0 14px 0;">Olá, {{user.name}}!</h2>
                        <p>Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.</p>
                        <p style="text-align: center; margin: 26px 0;">
                            <a href="{{reset.url}}" style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold;">Redefinir Senha</a>
                        </p>
                        <p>Este link de redefinição de senha expirará em {{reset.expire_minutes}} minutos.</p>
                        <p>Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.</p>
                        <p style="margin-top: 22px;">Atenciosamente,<br>Equipe {{site.name}}</p>',
                    'is_active' => true,
                    'locale' => 'pt-BR',
                ]
            );
        }

        $layout = app(SystemMailLayoutData::class)->make();

        $data = [
            'user' => [
                'name' => $notifiable->name ?? 'Usuário',
                'email' => $notifiable->email,
            ],
            'site' => [
                'name' => $layout['siteName'],
                'logo' => $layout['logoUrl'],
                'primary_color' => $layout['primaryColor'],
            ],
            'reset' => [
                'url' => $url,
                'expire_minutes' => $expireMinutes,
            ],
        ];

        $rendered = (string) ($template->body ?? '');
        $subject = (string) ($template->subject ?? ('Redefinição de Senha - ' . $layout['siteName']));

        foreach ($data as $key => $values) {
            foreach ($values as $k => $v) {
                $pattern = '/\{\{\s*' . $key . '\.' . $k . '\s*\}\}/';
                $rendered = preg_replace($pattern, (string) $v, $rendered);
                $subject = preg_replace($pattern, (string) $v, $subject);
            }
        }

        $content = $rendered . '
            <p style="font-size: 11px; color: #999999; margin-top: 18px;">
                Se você estiver tendo problemas clicando no botão, copie e cole o URL abaixo no seu navegador:<br>
                <a href="' . $url . '" style="color: ' . $layout['primaryColor'] . '; word-break: break-all;">' . $url . '</a>
            </p>
        ';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.system', array_merge($layout, [
                'content' => $content,
            ]));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}

