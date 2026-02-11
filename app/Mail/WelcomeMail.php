<?php

namespace App\Mail;

use App\Models\User;
use App\Services\Mail\SystemMailLayoutData;
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

        return $this
            ->subject('Bem-vindo à ' . $layout['siteName'] . '!')
            ->view('emails.welcome', $layout);
    }
}
