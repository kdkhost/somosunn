<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderControlCopyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<int, string> $bccRecipients
     */
    public function __construct(
        public string $renderedSubject,
        public string $renderedHtml,
        public array $bccRecipients
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject($this->renderedSubject)
            ->html($this->renderedHtml)
            ->bcc($this->bccRecipients);
    }
}
