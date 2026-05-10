<?php

namespace App\Notifications;

use App\Models\MailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MarketingManagerAssigned extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $platformName = config('app.name', 'Somos UNN');
        $panelUrl = url('/painel/marketing');

        // Se existir um MailTemplate personalizado, usa o subject + body dele.
        $template = MailTemplate::where('slug', 'marketing_manager_assigned')->first();

        if ($template) {
            $vars = [
                'user.name'      => $notifiable->name,
                'user.email'     => $notifiable->email,
                'platform.name'  => $platformName,
                'panel.url'      => $panelUrl,
                'action.url'     => $panelUrl,
            ];
            $subject = $this->replaceVars($template->subject ?? 'Voce foi designado como Responsavel de Marketing', $vars);
            $body    = $this->replaceVars($template->body ?? '', $vars);

            $msg = (new MailMessage())->subject($subject);
            foreach (preg_split('/\r?\n\r?\n/', trim($body)) as $paragraph) {
                if (trim($paragraph) !== '') {
                    $msg->line(trim($paragraph));
                }
            }
            return $msg->action('Acessar painel de Marketing', $panelUrl);
        }

        return (new MailMessage())
            ->subject("Voce foi designado como Responsavel de Marketing da {$platformName}")
            ->greeting("Ola {$notifiable->name},")
            ->line("O administrador da plataforma {$platformName} designou voce como **Responsavel de Marketing**.")
            ->line('A partir de agora voce tera acesso a uma area exclusiva no seu painel com informacoes sobre os valores destinados ao marketing da plataforma.')
            ->line('Alem disso, voce sera responsavel por coordenar o profissional que fara o trafego pago da plataforma.')
            ->action('Acessar painel de Marketing', $panelUrl)
            ->line('Qualquer duvida, entre em contato com o administrador.');
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

    protected function replaceVars(string $text, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $pattern = '/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/';
            $text = preg_replace($pattern, (string) $value, $text);
        }
        return $text;
    }
}
