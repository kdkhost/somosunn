<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class PwaController extends Controller
{
    public function manifest()
    {
        $enabled = (string) Setting::get('pwa_enabled', '1');
        if ($enabled !== '1') {
            abort(404);
        }

        $name = Setting::get('pwa_name', 'UNN');
        $short = Setting::get('pwa_short_name', 'UNN');
        $desc = Setting::get('pwa_description', 'Comunidade UNN');
        $theme = Setting::get('pwa_theme_color', '#1F5EDB');
        $bg = Setting::get('pwa_background_color');
        if (!$bg) {
            $bg = Setting::get('pwa_bg_color', '#ffffff');
        }
        $icon192 = Setting::get('pwa_icon_192');
        $icon512 = Setting::get('pwa_icon_512');

        foreach (['icon192', 'icon512'] as $var) {
            if (!$$var) {
                continue;
            }
            $$var = str_replace('\\', '/', ltrim((string) $$var, '/'));
            if (str_starts_with($$var, 'public/')) {
                $$var = substr($$var, strlen('public/'));
            }
        }

        $icons = [];
        if ($icon192) {
            $icons[] = ['src' => asset(ltrim($icon192, '/')), 'sizes' => '192x192', 'type' => 'image/png'];
        } else {
            // Fallback: usar PNG copiado para garantir compatibilidade Chrome
            $icons[] = ['src' => asset('img/pwa-icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png'];
            $icons[] = ['src' => asset('img/pwa-icon-512.png'), 'sizes' => '192x192', 'type' => 'image/png'];
        }

        if ($icon512) {
            $icons[] = ['src' => asset(ltrim($icon512, '/')), 'sizes' => '512x512', 'type' => 'image/png'];
        }

        $manifest = [
            'name' => $name,
            'short_name' => $short,
            'description' => $desc,
            'start_url' => url('/'),
            'scope' => url('/'),
            'display' => 'standalone',
            'background_color' => $bg,
            'theme_color' => $theme,
            'icons' => $icons,
            'orientation' => 'any',
            'categories' => ['business', 'productivity', 'utilities'],
            'prefer_related_applications' => false,
            'shortcuts' => [
                [
                    'name' => 'Scanner de Ingressos',
                    'short_name' => 'Scanner',
                    'url' => route('panel.admin.quick-scanner'),
                    'icons' => [['src' => asset('img/logo.svg'), 'sizes' => '192x192']]
                ]
            ],
            'permissions' => [
                ['name' => 'camera', 'status' => 'required', 'description' => 'Acesso à câmera é obrigatório para escanear QR Codes.'],
                ['name' => 'geolocation', 'status' => 'required', 'description' => 'Acesso à localização é obrigatório para validação de segurança.'],
                ['name' => 'notifications', 'status' => 'required', 'description' => 'Notificações de áudio e texto são necessárias para feedback de validação.']
            ],
            'features' => ['camera', 'geolocation', 'audio', 'vibrate']
        ];

        return response()->json($manifest)->header('Content-Type', 'application/manifest+json');
    }
}
