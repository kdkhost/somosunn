<?php

namespace App\Services\Certificate;

use App\Models\User;
use App\Models\Course;
use App\Models\Mentorship;
use App\Models\Event;
use App\Support\PdfBranding;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;

class CertificateGenerator
{
    protected $fontCssGenerator;

    public function __construct(CertificateFontCssGenerator $fontCssGenerator)
    {
        $this->fontCssGenerator = $fontCssGenerator;
    }

    public function generatePdfContent(User $user, string $type, $product, string $certHash, float $workload = 0): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true); // Allow remote images (http/https)
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', public_path()); // Allow access to public folder
        $options->set('dpi', 72); // Align pixels with editor canvas (842x595)

        // Configure font directories
        $dompdfStorageDir = storage_path('app/dompdf');
        $fontDir = $dompdfStorageDir . DIRECTORY_SEPARATOR . 'fonts';
        $fontCache = $dompdfStorageDir . DIRECTORY_SEPARATOR . 'font-cache';
        $tempDir = $dompdfStorageDir . DIRECTORY_SEPARATOR . 'tmp';

        foreach ([$fontDir, $fontCache, $tempDir] as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
        }

        $options->set('fontDir', $fontDir);
        $options->set('fontCache', $fontCache);
        $options->set('tempDir', $tempDir);

        $dompdf = new Dompdf($options);

        // Determine Author Name and Default Workload if not provided
        $authorName = 'Instrutor';

        if ($type === 'course') {
            $authorName = $product->author_name;
            if ($workload <= 0) {
                $workload = $product->total_hours;
            }
        } elseif ($type === 'mentorship') {
            $authorName = $product->mentor ? $product->mentor->name : 'Mentor';
            if ($workload <= 0) {
                $workload = $product->total_hours ?? 0;
            }
        } elseif ($type === 'event') {
            $authorName = $product->user ? $product->user->name : 'Organizador';
            if ($workload <= 0) {
                $workload = $product->duration_hours ?? 0;
            }
        }

        // Sanitize background path for Windows (DomPDF prefers forward slashes)
        if ($product->certificate_bg) {
            $product->certificate_bg = str_replace('\\', '/', $product->certificate_bg);
        }

        $fontCss = $this->fontCssGenerator->buildFontCss($product->certificate_settings ?? [], false);

        $html = View::make('admin.certificates.template', [
            'user' => $user,
            'course' => $product, // View expects 'course' but handles polymorphic product fields
            'certHash' => $certHash,
            'authorName' => $authorName,
            'workload' => $workload,
            'type' => $type,
            'fontCss' => $fontCss,
            'isPreview' => false
        ])->render();

        $html = PdfBranding::injectDefaultLogoWatermark($html);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }
}
