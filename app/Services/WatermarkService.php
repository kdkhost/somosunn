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

        // 1. Add Watermark Logo (Bottom Right)
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

                // Resize logo to max 150px width
                $newLogoWidth = 150;
                $newLogoHeight = ($logoHeight / $logoWidth) * $newLogoWidth;

                $resizedLogo = imagecreatetruecolor($newLogoWidth, $newLogoHeight);
                imagealphablending($resizedLogo, false);
                imagesavealpha($resizedLogo, true);
                $transparent = imagecolorallocatealpha($resizedLogo, 255, 255, 255, 127);
                imagefilledrectangle($resizedLogo, 0, 0, $newLogoWidth, $newLogoHeight, $transparent);

                imagecopyresampled($resizedLogo, $logo, 0, 0, 0, 0, $newLogoWidth, $newLogoHeight, $logoWidth, $logoHeight);

                $destX = $imgWidth - $newLogoWidth - 20;
                $destY = $imgHeight - $newLogoHeight - 20;

                // Fix transparent PNG blending onto JPEG
                imagealphablending($img, true);
                imagecopy($img, $resizedLogo, $destX, $destY, 0, 0, $newLogoWidth, $newLogoHeight);

                imagedestroy($logo);
                imagedestroy($resizedLogo);
            }
        }

        // 2. Add Text (Bottom Left)
        $text = sprintf(
            "%s | %s\nOrg: %s",
            $event->title,
            \Carbon\Carbon::parse($event->start_at)->format('d/m/Y'),
            $event->user ? $event->user->name : 'N/A'
        );

        $fontPath = public_path('fonts/Roboto-Bold.ttf');
        if (file_exists($fontPath)) {
            $fontSize = 24;
            $color = imagecolorallocatealpha($img, 255, 255, 255, 20); // White with some transparency
            $shadowColor = imagecolorallocatealpha($img, 0, 0, 0, 50); // Black shadow

            $lines = explode("\n", $text);
            $y = $imgHeight - 40 - (count($lines) * ($fontSize + 5));

            foreach ($lines as $line) {
                // Drop shadow
                imagettftext($img, $fontSize, 0, 22, $y + 2, $shadowColor, $fontPath, $line);
                // Text
                imagettftext($img, $fontSize, 0, 20, $y, $color, $fontPath, $line);
                $y += $fontSize + 10;
            }
        }

        // 3. Save Image
        $fullPath = storage_path('app/public/' . $path);
        imagejpeg($img, $fullPath, 85); // Save as JPG with 85% quality

        // Cleanup
        imagedestroy($img);

        return $path;
    }

    private function getWatermarkLogo(): ?string
    {
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
