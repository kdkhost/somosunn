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
    use SerializesModels;

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
        // Ensure PDF exists before attaching
        $pdfPath = null;

        if ($this->certificate->pdf_path) {
            $possiblePaths = [
                storage_path('app/public/' . $this->certificate->pdf_path),
                public_path('storage/' . $this->certificate->pdf_path),
            ];

            foreach ($possiblePaths as $testPath) {
                if (file_exists($testPath)) {
                    $pdfPath = $testPath;
                    break;
                }
            }
        }

        // If PDF doesn't exist, regenerate it
        if (!$pdfPath) {
            $controller = new \App\Http\Controllers\Admin\CertificateController();

            // Determine type and ID
            $type = null;
            $id = null;

            if ($this->certificate->course_id) {
                $type = 'course';
                $id = $this->certificate->course_id;
            } elseif ($this->certificate->mentorship_id) {
                $type = 'mentorship';
                $id = $this->certificate->mentorship_id;
            } elseif ($this->certificate->event_id) {
                $type = 'event';
                $id = $this->certificate->event_id;
            }

            if ($type && $id) {
                // Regenerate using public method
                $newCert = $controller->issueCertificate($this->certificate->user_id, $type, $id);
                $this->certificate->refresh(); // Reload from database
                $pdfPath = storage_path('app/public/' . $this->certificate->pdf_path);
            }
        }

        $mail = $this->markdown('emails.certificates.issued')
            ->subject('Seu Certificado do curso ' . $this->course->title);

        // Only attach if PDF exists
        if ($pdfPath && file_exists($pdfPath)) {
            $mail->attach($pdfPath, [
                'as' => 'certificado.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
