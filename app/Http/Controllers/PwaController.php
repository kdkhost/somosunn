<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class PwaController extends Controller
{
    public function manifest()
    {
        $enabled = Setting::get('pwa_enabled', '1');
        $name = Setting::get('pwa_name', 'UNN');
        $short = Setting::get('pwa_short_name', 'UNN');
        $desc = Setting::get('pwa_description', 'Comunidade UNN');
        $theme = Setting::get('pwa_theme_color', '#5B21B6');
        $bg = Setting::get('pwa_bg_color', '#ffffff');
        $icon192 = Setting::get('pwa_icon_192');
        $icon512 = Setting::get('pwa_icon_512');

        $icons = [];
        if($icon192) {
            $icons[] = ['src' => asset($icon192), 'sizes' => '192x192', 'type' => 'image/png'];
        } else {
            $icons[] = ['src' => asset('img/logo.svg'), 'sizes' => 'any', 'type' => 'image/svg+xml'];
        }

        if($icon512) {
            $icons[] = ['src' => asset($icon512), 'sizes' => '512x512', 'type' => 'image/png'];
        }

        $manifest = [
            'name' => $name,
            'short_name' => $short,
            'description' => $desc,
            'start_url' => url('/'),
            'display' => 'standalone',
            'background_color' => $bg,
            'theme_color' => $theme,
            'icons' => $icons,
        ];

        return response()->json($manifest)->header('Content-Type','application/manifest+json');
    }
}
