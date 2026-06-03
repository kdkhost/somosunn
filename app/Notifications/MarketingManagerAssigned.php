<?php

namespace App\Notifications;

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

        app(SystemMailTemplateService::class)->send($slug, $notifiable->email, $data, [
            'name' => 'Responsavel de Marketing Designado',
            'category' => 'marketing',
            'subject' => 'Voce foi designado como Responsavel de Marketing da {{platform.name}}',
            'body' => '<h2>Ola, {{user.name}}!</h2><p>Voce foi designado como <strong>Responsavel de Marketing</strong> da {{platform.name}}.</p><p><a href="{{panel.url}}">Acessar painel de Marketing</a></p>',
        ]);
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
