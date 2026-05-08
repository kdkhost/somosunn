<?php

namespace App\Notifications;

use App\Models\MailTemplate;
use App\Services\Mail\SystemMailLayoutData;
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

        $template = MailTemplate::where('slug', 'email_verification')->where('is_active', true)->first();

        if (!$template) {
            $template = MailTemplate::firstOrCreate(
                ['slug' => 'email_verification'],
                [
                    'name' => 'Verificação de E-mail',
                    'category' => 'sistema',
                    'subject' => 'Confirme seu e-mail - {{site.name}}',
                    'body' => '<h2 style="margin: 0 0 14px 0;">Olá, {{user.name}}!</h2>
                        <p>Bem-vindo(a) à {{site.name}}. Para ativar sua conta e começar a usar a plataforma, por favor confirme seu endereço de e-mail clicando no botão abaixo.</p>
                        <p style="text-align: center; margin: 26px 0;">
                            <a href="{{verify.url}}" style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold;">Confirmar meu e-mail</a>
                        </p>
                        <p>Este link expira em {{verify.expire_minutes}} minutos. Se você não criou uma conta, ignore este e-mail.</p>
                        <p style="margin-top: 22px;">Atenciosamente,<br>Equipe {{site.name}}</p>',
                    'is_active' => true,
                    'locale' => 'pt-BR',
                ]
            );
        }

        $layout = app(SystemMailLayoutData::class)->make();
        $expireMinutes = (int) config('auth.verification.expire', 60);

        $data = [
            'user' => [
                'name' => $notifiable->name ?? 'Usuário',
                'email' => $notifiable->getEmailForVerification(),
            ],
            'site' => [
                'name' => $layout['siteName'],
                'logo' => $layout['logoUrl'],
                'primary_color' => $layout['primaryColor'],
            ],
            'verify' => [
                'url' => $verificationUrl,
                'expire_minutes' => $expireMinutes,
            ],
        ];

        $rendered = (string) ($template->body ?? '');
        $subject  = (string) ($template->subject ?? ('Confirme seu e-mail - ' . $layout['siteName']));

        foreach ($data as $key => $values) {
            foreach ($values as $k => $v) {
                $pattern = '/\{\{\s*' . $key . '\.' . $k . '\s*\}\}/';
                $rendered = preg_replace($pattern, (string) $v, $rendered);
                $subject  = preg_replace($pattern, (string) $v, $subject);
            }
        }

        $content = $rendered . '
            <p style="font-size: 11px; color: #999999; margin-top: 18px;">
                Se o botão não funcionar, copie e cole este link no seu navegador:<br>
                <a href="' . $verificationUrl . '" style="color: ' . $layout['primaryColor'] . '; word-break: break-all;">' . $verificationUrl . '</a>
            </p>
        ';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.system', array_merge($layout, [
                'content' => $content,
            ]));
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
