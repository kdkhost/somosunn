<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Models\Event;

class WatermarkService
{
    /**
     * Process an uploaded image, add the watermark and text, and save it using native GD.
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param \App\Models\Event $event
     * @return string The path to the saved watermarked file
     */
    public function processEventImage($file, Event $event): string
    {
        $filename = uniqid('event_media_') . '.jpg'; // Convert to jpg for simplicity
        $directory = 'events/' . $event->id . '/gallery/';
        $path = $directory . $filename;

        Storage::disk('public')->makeDirectory($directory);

        $sourcePath = $file->getRealPath();
        $mime = mime_content_type($sourcePath);

        // Load image based on MIME type
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $img = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $img = @imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $img = @imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $img = @imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new \Exception("Unsupported image format: " . $mime);
        }

        if (!$img) {
            throw new \Exception("Failed to load image for watermarking.");
        }

        $imgWidth = imagesx($img);
        $imgHeight = imagesy($img);

        // 1. Add Watermark Logo
        $logoPath = $this->getWatermarkLogo();
        if ($logoPath && file_exists($logoPath)) {
            $logoMime = mime_content_type($logoPath);
            $logo = null;

            if (strpos($logoMime, 'png') !== false) {
                $logo = @imagecreatefrompng($logoPath);
            } elseif (strpos($logoMime, 'jpeg') !== false || strpos($logoMime, 'jpg') !== false) {
                $logo = @imagecreatefromjpeg($logoPath);
            }

            if ($logo) {
                $logoWidth = imagesx($logo);
                $logoHeight = imagesy($logo);

                // Resize logo to max 150px width (or proportional to image)
                $newLogoWidth = min(150, $imgWidth * 0.2);
                $newLogoHeight = ($logoHeight / $logoWidth) * $newLogoWidth;

                $resizedLogo = imagecreatetruecolor($newLogoWidth, $newLogoHeight);
                imagealphablending($resizedLogo, false);
                imagesavealpha($resizedLogo, true);
                $transparent = imagecolorallocatealpha($resizedLogo, 255, 255, 255, 127);
                imagefilledrectangle($resizedLogo, 0, 0, $newLogoWidth, $newLogoHeight, $transparent);

                imagecopyresampled($resizedLogo, $logo, 0, 0, 0, 0, $newLogoWidth, $newLogoHeight, $logoWidth, $logoHeight);

                // Calculate position based on settings
                $pos = \App\Models\Setting::get('watermark_position', 'bottom-right');
                $margin = 20;

                switch ($pos) {
                    case 'top-left':
                        $destX = $margin;
                        $destY = $margin;
                        break;
                    case 'top-right':
                        $destX = $imgWidth - $newLogoWidth - $margin;
                        $destY = $margin;
                        break;
                    case 'bottom-left':
                        $destX = $margin;
                        $destY = $imgHeight - $newLogoHeight - $margin;
                        break;
                    case 'center':
                        $destX = ($imgWidth / 2) - ($newLogoWidth / 2);
                        $destY = ($imgHeight / 2) - ($newLogoHeight / 2);
                        break;
                    case 'bottom-right':
                    default:
                        $destX = $imgWidth - $newLogoWidth - $margin;
                        $destY = $imgHeight - $newLogoHeight - $margin;
                        break;
                }

                $opacity = (int) \App\Models\Setting::get('watermark_opacity', 50);

                // For a more robust opacity control with GD
                imagealphablending($img, true);
                $this->imagecopymerge_alpha($img, $resizedLogo, $destX, $destY, 0, 0, $newLogoWidth, $newLogoHeight, $opacity);

                imagedestroy($logo);
                imagedestroy($resizedLogo);
            }
        }

        // 2. Add Text (Bottom Right or opposite to logo)
        // ... (texto pode ficar fixo ou seguir uma lógica oposta)
        // Mantendo o texto como estava mas com opacidade dinâmica se desejar, 
        // mas o foco principal do usuário era a marca d'água (logo/imagem).

        $text = sprintf(
            "%s | %s\nOrg: %s",
            $event->title,
            \Carbon\Carbon::parse($event->start_at)->format('d/m/Y'),
            $event->user ? $event->user->name : 'N/A'
        );

        $fontPath = public_path('fonts/Roboto-Bold.ttf');
        if (file_exists($fontPath)) {
            $fontSize = 24;
            $opacity = (int) \App\Models\Setting::get('watermark_opacity', 50);
            $alpha = 127 - (int) ($opacity * 1.27); // Convert 0-100 to 127-0

            $color = imagecolorallocatealpha($img, 255, 255, 255, max(0, min(127, $alpha)));
            $shadowColor = imagecolorallocatealpha($img, 0, 0, 0, 100);

            $lines = explode("\n", $text);
            $y = $imgHeight - 40 - (count($lines) * ($fontSize + 5));

            foreach ($lines as $line) {
                imagettftext($img, $fontSize, 0, 22, $y + 2, $shadowColor, $fontPath, $line);
                imagettftext($img, $fontSize, 0, 20, $y, $color, $fontPath, $line);
                $y += $fontSize + 10;
            }
        }

        // 3. Save Image
        $fullPath = storage_path('app/public/' . $path);
        imagejpeg($img, $fullPath, 85);

        // Cleanup
        imagedestroy($img);

        return $path;
    }

    /**
     * Helper to merge images with alpha channel and opacity
     */
    private function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
    {
        if ($pct >= 100) {
            imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
            return;
        }

        $cut = imagecreatetruecolor($src_w, $src_h);
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
        imagedestroy($cut);
    }

    private function getWatermarkLogo(): ?string
    {
        // Tenta primeiro a logo específica da marca d'água
        $setting = \App\Models\Setting::where('key', 'watermark_image')->first();
        if ($setting && $setting->value) {
            $path = storage_path('app/public/' . $setting->value);
            if (file_exists($path)) {
                return $path;
            }
        }

        // Fallback para a logo do site light
        $setting = \App\Models\Setting::where('key', 'site_logo_light')->first();
        if ($setting && $setting->value) {
            $path = storage_path('app/public/' . $setting->value);
            if (file_exists($path)) {
                return $path;
            }
        }

        $fallback = public_path('images/logo.png');
        if (file_exists($fallback)) {
            return $fallback;
        }

        return null;
    }
}
