<?php

namespace App\Mail;

use App\Models\Certificate;
use App\Support\EmailQueueSettings;
use App\Services\Mail\SystemMailLayoutData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CertificateIssued extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $certificate;
    public $user;
    public $product;
    public $itemTypeLabel;
    public $itemTitle;
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
        $this->product = $certificate->course ?? $certificate->mentorship ?? $certificate->event;
        $this->itemTypeLabel = $certificate->course_id
            ? 'o curso'
            : ($certificate->mentorship_id ? 'a mentoria' : ($certificate->event_id ? 'o evento' : 'o conteúdo'));
        $this->itemTitle = (string) ($this->product->title ?? 'UNN');
        $this->url = asset('storage/' . $certificate->pdf_path);
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());

        if (EmailQueueSettings::shouldQueue() && EmailQueueSettings::delaySeconds() > 0) {
            $this->delay(now()->addSeconds(EmailQueueSettings::delaySeconds()));
        }
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
                $this->product = $this->certificate->course ?? $this->certificate->mentorship ?? $this->certificate->event;
                $this->itemTitle = (string) ($this->product->title ?? $this->itemTitle);
                $this->url = asset('storage/' . $this->certificate->pdf_path);
                $pdfPath = storage_path('app/public/' . $this->certificate->pdf_path);
            }
        }

        $layout = app(SystemMailLayoutData::class)->make();

        $subjectPrefix = $this->certificate->course_id
            ? 'Seu Certificado do curso '
            : ($this->certificate->mentorship_id ? 'Seu Certificado da mentoria ' : ($this->certificate->event_id ? 'Seu Certificado do evento ' : 'Seu Certificado - '));

        $mail = $this
            ->subject($subjectPrefix . $this->itemTitle)
            ->view('emails.certificates.issued', array_merge($layout, [
                'user' => $this->user,
                'url' => $this->url,
                'itemTypeLabel' => $this->itemTypeLabel,
                'itemTitle' => $this->itemTitle,
            ]));

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
