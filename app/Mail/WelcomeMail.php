<?php

namespace App\Mail;

use App\Mail\Concerns\UsesMailTemplate;
use App\Models\User;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, UsesMailTemplate;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());

        if (EmailQueueSettings::shouldQueue() && EmailQueueSettings::delaySeconds() > 0) {
            $this->delay(now()->addSeconds(EmailQueueSettings::delaySeconds()));
        }
    }

    public function build()
    {
        return $this->buildFromTemplate('welcome_email', [
            'user' => [
                'name' => $this->user->name ?? 'Usuário',
                'email' => $this->user->email,
            ],
        ], [
            'name' => 'Boas-vindas',
            'category' => 'auth',
            'subject' => 'Bem-vindo(a) à {{site.name}}!',
            'body' => '<h2>Olá, {{user.name}}!</h2>
<p>Seja muito bem-vindo(a) à {{site.name}}. Estamos felizes em ter você aqui.</p>
<p>Explore cursos, mentorias, eventos e muito mais na nossa plataforma.</p>
<p style="text-align: center; margin: 26px 0;">
    <a href="{{site.url}}" style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold;">Acessar plataforma</a>
</p>
<p>Atenciosamente,<br>Equipe {{site.name}}</p>',
        ]);
    }
}
