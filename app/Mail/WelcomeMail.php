<?php

namespace App\Mail;

use App\Models\User;
use App\Services\Mail\SystemMailLayoutData;
use App\Services\Mail\SystemMailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        $layout = app(SystemMailLayoutData::class)->make();
        $siteUrl = url('/');

        $data = [
            'year' => date('Y'),
            'user' => [
                'name' => (string) ($this->user->name ?? 'Usuário'),
                'email' => (string) ($this->user->email ?? ''),
            ],
            'site' => [
                'name' => (string) ($layout['siteName'] ?? config('app.name', 'UNN')),
                'url' => $siteUrl,
                'logo' => (string) ($layout['logoUrl'] ?? asset('img/logo.svg')),
                'primary_color' => (string) ($layout['primaryColor'] ?? '#1F5EDB'),
                'secondary_color' => (string) ($layout['secondaryColor'] ?? '#177FD6'),
            ],
            'links' => [
                'account_url' => route('login'),
            ],
        ];

        $rendered = app(SystemMailTemplateService::class)->renderBySlug('welcome_email', $data);
        if ($rendered) {
            return $this
                ->subject($rendered['subject'])
                ->view('emails.system', array_merge($layout, [
                    'content' => $rendered['content'],
                ]));
        }

        return $this
            ->subject('Bem-vindo à ' . $layout['siteName'] . '!')
            ->view('emails.welcome', $layout);
    }
}
