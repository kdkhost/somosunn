<?php

namespace App\Mail;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CertificateIssued extends Mailable
{
    use Queueable, SerializesModels;

    public $certificate;
    public $user;
    public $course;
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
        $this->user = $certificate->user;
        $this->course = $certificate->course;
        $this->url = asset('storage/' . $certificate->pdf_path);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.certificates.issued')
            ->subject('Seu Certificado do curso ' . $this->course->title)
            ->attach(storage_path('app/public/' . $this->certificate->pdf_path), [
                'as' => 'certificado.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
