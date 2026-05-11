<?php

namespace App\Notifications;

use App\Jobs\SendGenericTemplateEmail;
use App\Models\MailTemplate;
use App\Services\Mail\SystemMailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MarketingManagerAssigned extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Envia o email usando o sistema de templates personalizados.
     * Chamado manualmente apos notify() para garantir uso do layout correto.
     */
    public static function sendMail($notifiable): void
    {
        $platformName = config('app.name', 'Somos UNN');
        $panelUrl = url('/painel/marketing');
        $slug = 'marketing_manager_assigned';

        $data = [
            'user' => [
                'name'  => $notifiable->name,
                'email' => $notifiable->email,
            ],
            'platform' => [
                'name' => $platformName,
            ],
            'panel' => [
                'url' => $panelUrl,
            ],
            'action' => [
                'url' => $panelUrl,
            ],
        ];

        $service = app(SystemMailTemplateService::class);
        $rendered = $service->renderFullHtml($slug, $data);

        if ($rendered) {
            // Template personalizado existe e esta ativo — usa ele
            SendGenericTemplateEmail::dispatch(
                $notifiable->email,
                $rendered['subject'],
                $rendered['html']
            );
        } else {
            // Fallback: cria conteudo padrao e envia pelo layout do sistema
            $subject = "Voce foi designado como Responsavel de Marketing da {$platformName}";
            $content = "<p>Ola <strong>{$notifiable->name}</strong>,</p>"
                . "<p>O administrador da plataforma <strong>{$platformName}</strong> designou voce como <strong>Responsavel de Marketing</strong>.</p>"
                . "<p>A partir de agora voce tera acesso a uma area exclusiva no seu painel com informacoes sobre os valores destinados ao marketing da plataforma.</p>"
                . "<p>Alem disso, voce sera responsavel por coordenar o profissional que fara o trafego pago da plataforma.</p>"
                . "<p><a href=\"{$panelUrl}\" style=\"display:inline-block;padding:12px 24px;background:linear-gradient(135deg,#1F5EDB,#177FD6);color:#fff;border-radius:8px;text-decoration:none;font-weight:bold;\">Acessar painel de Marketing</a></p>"
                . "<p>Qualquer duvida, entre em contato com o administrador.</p>";

            SendGenericTemplateEmail::dispatch($notifiable->email, $subject, $content);
        }
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'    => 'marketing_manager_assigned',
            'title'   => 'Voce e o novo Responsavel de Marketing!',
            'message' => 'Foi atribuida a voce a responsabilidade de gerenciar o marketing da plataforma. Acesse a area exclusiva no seu painel.',
            'icon'    => 'fas fa-bullhorn',
            'color'   => 'purple',
            'url'     => '/painel/marketing',
        ];
    }
}
