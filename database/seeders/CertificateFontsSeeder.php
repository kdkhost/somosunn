<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomFont;

class CertificateFontsSeeder extends Seeder
{
    /**
     * Seed certificate fonts - Medieval and Cursive/Script fonts for certificates.
     */
    public function run(): void
    {
        $fonts = [
            // Medieval/Formal Fonts
            [
                'name' => 'Cinzel (Medieval)',
                'font_family' => 'Cinzel, serif',
                'type' => 'google_link',
                'google_font_url' => 'https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&display=swap',
                'file_path' => null,
                'is_active' => true,
            ],
            [
                'name' => 'UnifrakturMaguntia (Blackletter)',
                'font_family' => 'UnifrakturMaguntia, cursive',
                'type' => 'google_link',
                'google_font_url' => 'https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&display=swap',
                'file_path' => null,
                'is_active' => true,
            ],
            [
                'name' => 'IM Fell English (Old English)',
                'font_family' => 'IM Fell English, serif',
                'type' => 'google_link',
                'google_font_url' => 'https://fonts.googleapis.com/css2?family=IM+Fell+English:ital@0;1&display=swap',
                'file_path' => null,
                'is_active' => true,
            ],

            // Cursive/Script Fonts (for signatures)
            [
                'name' => 'Great Vibes (Assinatura Elegante)',
                'font_family' => 'Great Vibes, cursive',
                'type' => 'google_link',
                'google_font_url' => 'https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap',
                'file_path' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Dancing Script (Manuscrito)',
                'font_family' => 'Dancing Script, cursive',
                'type' => 'google_link',
                'google_font_url' => 'https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap',
                'file_path' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Pacifico (Brush Script)',
                'font_family' => 'Pacifico, cursive',
                'type' => 'google_link',
                'google_font_url' => 'https://fonts.googleapis.com/css2?family=Pacifico&display=swap',
                'file_path' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Allura (Caligrafia Formal)',
                'font_family' => 'Allura, cursive',
                'type' => 'google_link',
                'google_font_url' => 'https://fonts.googleapis.com/css2?family=Allura&display=swap',
                'file_path' => null,
                'is_active' => true,
            ],
        ];

        foreach ($fonts as $fontData) {
            CustomFont::updateOrCreate(
                ['font_family' => $fontData['font_family']],
                $fontData
            );
        }

        $this->command->info('Certificate fonts seeded successfully!');
    }
}
