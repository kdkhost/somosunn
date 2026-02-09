<?php

namespace App\Notifications;

use App\Models\MailTemplate;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

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

        $expireMinutes = config('auth.passwords.users.expire', 60);

        // Busca o template do banco de dados
        $template = MailTemplate::where('slug', 'password_reset')->where('is_active', true)->first();

        if (!$template) {
            // Cria template padrão se não existir
            $template = MailTemplate::firstOrCreate(
                ['slug' => 'password_reset'],
                [
                    'name' => 'Redefinição de Senha',
                    'category' => 'sistema',
                    'subject' => 'Redefinição de Senha - {{site.name}}',
                    'body' => '<h1>Olá, {{user.name}}!</h1>
                        <p>Você está recebendo este e-mail porque recebemos uma solicitação de redefinição de senha para sua conta.</p>
                        <p style="text-align: center; margin: 30px 0;">
                            <a href="{{reset.url}}" style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold;">Redefinir Senha</a>
                        </p>
                        <p>Este link de redefinição de senha expirará em {{reset.expire_minutes}} minutos.</p>
                        <p>Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.</p>
                        <br>
                        <p>Atenciosamente,<br>Equipe {{site.name}}</p>',
                    'is_active' => true,
                    'locale' => 'pt-BR'
                ]
            );
        }

        // Busca configurações do site
        $logo = Setting::where('key', 'logo_admin')->value('value');
        if (!$logo) $logo = Setting::where('key', 'logo_front')->value('value');
        if (!$logo) $logo = Setting::where('key', 'logo_image')->value('value');
        $logoUrl = $logo ? asset($logo) : asset('img/logo.svg');

        $siteName = Setting::where('key', 'app_name')->value('value');
        if (!$siteName) $siteName = Setting::where('key', 'company_name')->value('value');
        if (!$siteName) $siteName = config('app.name');

        $primaryColor = Setting::where('key', 'site_color_primary')->value('value') ?? '#1F5EDB';
        $secondaryColor = Setting::where('key', 'site_color_secondary')->value('value') ?? '#177FD6';

        // Dados para substituição
        $data = [
            'user' => [
                'name' => $notifiable->name ?? 'Usuário',
                'email' => $notifiable->email,
            ],
            'site' => [
                'name' => $siteName,
                'logo' => $logoUrl,
                'primary_color' => $primaryColor,
            ],
            'reset' => [
                'url' => $url,
                'expire_minutes' => $expireMinutes,
            ],
        ];

        // Renderiza o template
        $rendered = $template->body;
        $subject = $template->subject ?? 'Redefinição de Senha';

        foreach ($data as $key => $values) {
            foreach ($values as $k => $v) {
                $pattern = '/\{\{\s*' . $key . '\.' . $k . '\s*\}\}/';
                $rendered = preg_replace($pattern, $v, $rendered);
                $subject = preg_replace($pattern, $v, $subject);
            }
        }

        // Wrap com layout do sistema
        $layout = '
        <div style="background-color: #f4f6f9; padding: 20px; font-family: sans-serif; min-height: 100%;">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td align="center">
                        <div style="background-color: #ffffff; max-width: 600px; padding: 0px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">
                            <!-- Header -->
                            <div style="background: linear-gradient(135deg, ' . $primaryColor . ' 0%, ' . $secondaryColor . ' 100%); padding: 30px 20px; text-align: center;">
                                <img src="' . $logoUrl . '" alt="' . $siteName . '" style="max-height: 60px; max-width: 200px;">
                            </div>
                            
                            <!-- Body -->
                            <div style="padding: 30px; color: #333333; line-height: 1.6;">
                                ' . $rendered . '
                            </div>
                            
                            <!-- Footer -->
                            <div style="background-color: #f8f9fa; padding: 20px; text-align: center; color: #777777; font-size: 12px; border-top: 1px solid #eeeeee;">
                                <p>&copy; ' . date('Y') . ' ' . $siteName . '. Todos os direitos reservados.</p>
                                <p style="font-size: 11px; color: #999999; margin-top: 10px;">
                                    Se você está tendo problemas clicando no botão "Redefinir Senha", copie e cole o URL abaixo no seu navegador:<br>
                                    <a href="' . $url . '" style="color: ' . $primaryColor . '; word-break: break-all;">' . $url . '</a>
                                </p>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.raw', ['content' => $layout]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
