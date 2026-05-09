<?php

namespace App\Mail;

use App\Mail\Concerns\UsesMailTemplate;
use App\Models\Certificate;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificateIssued extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, UsesMailTemplate;

    public Certificate $certificate;
    public $user;
    public $itemTypeLabel;
    public $itemTitle;
    public $url;

    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
        $this->user = $certificate->user;
        $product = $certificate->course ?? $certificate->mentorship ?? $certificate->event;
        $this->itemTypeLabel = $certificate->course_id
            ? 'o curso'
            : ($certificate->mentorship_id ? 'a mentoria' : ($certificate->event_id ? 'o evento' : 'o conteúdo'));
        $this->itemTitle = (string) ($product->title ?? 'UNN');
        $this->url = asset('storage/' . $certificate->pdf_path);
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());

        if (EmailQueueSettings::shouldQueue() && EmailQueueSettings::delaySeconds() > 0) {
            $this->delay(now()->addSeconds(EmailQueueSettings::delaySeconds()));
        }
    }

    public function build()
    {
        // Resolver PDF
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

        $mail = $this->buildFromTemplate('certificate_issued', [
            'user' => [
                'name' => $this->user->name ?? 'Aluno',
            ],
            'certificate' => [
                'item_type' => $this->itemTypeLabel,
                'item_title' => $this->itemTitle,
                'url' => $this->url,
            ],
        ], [
            'name' => 'Certificado Emitido',
            'category' => 'sistema',
            'subject' => 'Seu Certificado - {{certificate.item_title}}',
            'body' => '<h2>Parabéns, {{user.name}}!</h2>
<p>Seu certificado de conclusão de {{certificate.item_type}} <strong>{{certificate.item_title}}</strong> foi emitido com sucesso.</p>
<p style="text-align: center; margin: 26px 0;">
    <a href="{{certificate.url}}" style="display: inline-block; background-color: {{site.primary_color}}; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold;">Baixar Certificado</a>
</p>
<p>O PDF também está anexo a este e-mail.</p>
<p>Atenciosamente,<br>Equipe {{site.name}}</p>',
        ]);

        if ($pdfPath && file_exists($pdfPath)) {
            $mail->attach($pdfPath, [
                'as' => 'certificado.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
