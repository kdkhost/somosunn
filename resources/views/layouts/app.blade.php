{{-- /**
* Sistema UNN - Layout principal
*
* Autor: George Marcelo (KDKHOST SOLUÇÕES)
* Telefone: +55 (21) 98132-5441
* Telegram: https://t.me/MARCELO_BRAD
*
* Copyright (c) 2026 Kdkhost Soluções. Todos os direitos reservados.
*
* AVISO LEGAL:
* Este software e seu código-fonte são propriedade intelectual de kdkhost soluções.
* É proibida a reprodução, distribuição, modificação, engenharia reversa ou uso não autorizado,
* total ou parcial, sem autorização prévia e por escrito.
*
* Contato: contato@kdkhost.com.br
* Licenciamento: Uso restrito conforme contrato/termos aplicáveis.
*/ --}}
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', \App\Models\Setting::get('seo_meta_title') ?: config('app.name', 'UNN'))</title>
    @php
        $logoFront = \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
        $logo = $logoFront ? asset(ltrim($logoFront, '/')) : asset('img/logo.svg');
        $faviconValue = \App\Models\Setting::get('favicon_image');
        $favicon = $faviconValue ? asset(ltrim($faviconValue, '/')) : asset('favicon.ico');
        $pwaEnabled = (string) \App\Models\Setting::get('pwa_enabled', '1') === '1';
        $pwaTheme = \App\Models\Setting::get('pwa_theme_color', '#1F5EDB');

        $seoDefaultTitle = \App\Models\Setting::get('seo_meta_title') ?: (\App\Models\Setting::get('app_name') ?: config('app.name', 'UNN'));
        $seoDefaultDescription = (string) (\App\Models\Setting::get('seo_meta_description') ?: '');
        $seoDefaultKeywords = (string) (\App\Models\Setting::get('seo_meta_keywords') ?: '');
        $seoRobots = (string) (\App\Models\Setting::get('seo_robots') ?: 'index,follow');
        $seoGoogleVerification = (string) (\App\Models\Setting::get('seo_google_verification') ?: '');

        $seoOgImageValue = (string) (\App\Models\Setting::get('seo_og_image') ?: '');
        $seoOgImage = $seoOgImageValue !== '' ? asset(ltrim($seoOgImageValue, '/')) : '';

        $seoTwitterImageValue = (string) (\App\Models\Setting::get('seo_twitter_image') ?: '');
        if ($seoTwitterImageValue === '') {
            $seoTwitterImageValue = $seoOgImageValue;
        }
        $seoTwitterImage = $seoTwitterImageValue !== '' ? asset(ltrim($seoTwitterImageValue, '/')) : '';

        $seoTwitterSite = (string) (\App\Models\Setting::get('seo_twitter_site') ?: '');

        $trackingHead = (string) (\App\Models\Setting::get('tracking_head') ?: '');
        $trackingBody = (string) (\App\Models\Setting::get('tracking_body') ?: '');

        // Google AdSense
        $adsensePublisherId = (string) (\App\Models\Setting::get('adsense_publisher_id') ?: '');
        $adsEnabled = (string) \App\Models\Setting::get('ads_enabled', '0') === '1';

        // Video player (Plyr) - global config
        $videoPlayerEnabled = (string) \App\Models\Setting::get('video_player_enabled', '1') === '1';
        $videoPlyrColor = (string) (\App\Models\Setting::get('video_plyr_color') ?: (\App\Models\Setting::get('site_color_primary') ?: '#1F5EDB'));
        if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $videoPlyrColor)) {
            $videoPlyrColor = '#1F5EDB';
        }

        $videoPlyrControlsRaw = (string) (\App\Models\Setting::get('video_plyr_controls') ?: 'play,progress,current-time,mute,volume,settings,fullscreen');
        $videoPlyrControls = array_values(array_filter(array_map('trim', explode(',', $videoPlyrControlsRaw))));

        $videoPlyrSettingsRaw = (string) (\App\Models\Setting::get('video_plyr_settings') ?: 'captions,quality,speed,loop');
        $videoPlyrSettings = array_values(array_filter(array_map('trim', explode(',', $videoPlyrSettingsRaw))));

        $videoPlyrSpeedOptionsRaw = (string) (\App\Models\Setting::get('video_plyr_speed_options') ?: '0.5,0.75,1,1.25,1.5,2');
        $videoPlyrSpeedOptions = array_values(array_filter(array_map(function ($value) {
            $value = trim((string) $value);
            if ($value === '') {
                return null;
            }
            $value = str_replace(',', '.', $value);
            return (float) $value;
        }, explode(',', $videoPlyrSpeedOptionsRaw))));
        $videoPlyrSpeedSelected = (float) str_replace(',', '.', (string) (\App\Models\Setting::get('video_plyr_speed_selected') ?: '1'));

        $videoPlyrSeekTime = (int) (\App\Models\Setting::get('video_plyr_seek_time') ?: 10);

        $videoPlyrVolumeRaw = trim((string) (\App\Models\Setting::get('video_plyr_volume') ?: ''));
        $videoPlyrVolumeValue = $videoPlyrVolumeRaw !== '' ? (float) str_replace(',', '.', $videoPlyrVolumeRaw) : null;

        $videoPlyrAutoplay = (string) \App\Models\Setting::get('video_plyr_autoplay', '0') === '1';
        $videoPlyrMuted = (string) \App\Models\Setting::get('video_plyr_muted', '0') === '1';
        $videoPlyrClickToPlay = (string) \App\Models\Setting::get('video_plyr_click_to_play', '1') === '1';
        $videoPlyrDisableContextMenu = (string) \App\Models\Setting::get('video_plyr_disable_context_menu', '1') === '1';

        $videoPlyrRewind = (string) \App\Models\Setting::get('video_plyr_rewind_enabled', '1') === '1';
        $videoPlyrFastForward = (string) \App\Models\Setting::get('video_plyr_fast_forward_enabled', '1') === '1';
        $videoPlyrVolumeEnabled = (string) \App\Models\Setting::get('video_plyr_volume_enabled', '1') === '1';

        $videoPlyrOptionsJson = trim((string) (\App\Models\Setting::get('video_plyr_options_json') ?: ''));
        $videoPlyrOptionsCustom = null;
        if ($videoPlyrOptionsJson !== '') {
            $decoded = json_decode($videoPlyrOptionsJson, true);
            if (is_array($decoded)) {
                $videoPlyrOptionsCustom = $decoded;
            }
        }

        $videoWatermarkEnabled = (string) \App\Models\Setting::get('video_watermark_enabled', '0') === '1';
        $videoWatermarkTextEnabled = (string) \App\Models\Setting::get('video_watermark_text_enabled', '0') === '1';
        $videoWatermarkTextTemplate = (string) (\App\Models\Setting::get('video_watermark_text_template') ?: '{name}');

        $watermarkImageValue = (string) (\App\Models\Setting::get('watermark_image') ?: '');
        $watermarkImageUrl = $watermarkImageValue !== '' ? asset(ltrim($watermarkImageValue, '/')) : '';

        $videoWatermarkOpacityRaw = (string) (\App\Models\Setting::get('video_watermark_opacity') ?: '0.15');
        $videoWatermarkOpacity = (float) str_replace(',', '.', $videoWatermarkOpacityRaw);
        $videoWatermarkOpacity = max(0.0, min(1.0, $videoWatermarkOpacity));

        $videoWatermarkSize = (int) (\App\Models\Setting::get('video_watermark_size_percent') ?: 18);
        $videoWatermarkSize = max(1, min(100, $videoWatermarkSize));

        $videoWatermarkPosition = (string) (\App\Models\Setting::get('video_watermark_position') ?: 'top-right');

        $videoWatermarkMargin = (int) (\App\Models\Setting::get('video_watermark_margin') ?: 16);
        $videoWatermarkMargin = max(0, min(200, $videoWatermarkMargin));

        $videoWatermarkRotate = (int) (\App\Models\Setting::get('video_watermark_rotate') ?: 0);
        $videoWatermarkRotate = max(-180, min(180, $videoWatermarkRotate));

        $videoWatermarkBlend = (string) (\App\Models\Setting::get('video_watermark_blend') ?: 'normal');
        $videoWatermarkAnimate = (string) \App\Models\Setting::get('video_watermark_animate', '0') === '1';

        $videoWatermarkText = '';
        if ($videoWatermarkTextEnabled && auth()->check()) {
            $replacements = [
                '{id}' => (string) auth()->id(),
                '{name}' => (string) auth()->user()->name,
                '{email}' => (string) auth()->user()->email,
            ];
            $videoWatermarkText = trim(str_replace(array_keys($replacements), array_values($replacements), $videoWatermarkTextTemplate));
        }

        $videoPlayerConfig = [
            'enabled' => $videoPlayerEnabled,
            'plyr' => [
                'autoplay' => $videoPlyrAutoplay,
                'muted' => $videoPlyrMuted,
                'clickToPlay' => $videoPlyrClickToPlay,
                'disableContextMenu' => $videoPlyrDisableContextMenu,
                'rewindEnabled' => $videoPlyrRewind,
                'fastForwardEnabled' => $videoPlyrFastForward,
                'volumeEnabled' => $videoPlyrVolumeEnabled,
                'seekTime' => $videoPlyrSeekTime,
                'volume' => $videoPlyrVolumeValue,
                'controls' => $videoPlyrControls,
                'settings' => $videoPlyrSettings,
                'speedOptions' => $videoPlyrSpeedOptions,
                'speedSelected' => $videoPlyrSpeedSelected,
                'customOptions' => $videoPlyrOptionsCustom,
            ],
            'watermark' => [
                'enabled' => $videoWatermarkEnabled,
                'imageUrl' => $watermarkImageUrl,
                'text' => $videoWatermarkText,
                'opacity' => $videoWatermarkOpacity,
                'sizePercent' => $videoWatermarkSize,
                'position' => $videoWatermarkPosition,
                'margin' => $videoWatermarkMargin,
                'rotate' => $videoWatermarkRotate,
                'blend' => $videoWatermarkBlend,
                'animate' => $videoWatermarkAnimate,
            ],
        ];
        $videoPlayerConfigJson = json_encode(
            $videoPlayerConfig,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_INVALID_UTF8_SUBSTITUTE
            | JSON_HEX_TAG
            | JSON_HEX_APOS
            | JSON_HEX_AMP
            | JSON_HEX_QUOT
        );
        if (!is_string($videoPlayerConfigJson) || $videoPlayerConfigJson === '') {
            $videoPlayerConfigJson = '{"enabled":false}';
        }

        $pageTitle = trim($__env->yieldContent('title'));
        if ($pageTitle === '') {
            $pageTitle = $seoDefaultTitle;
        }

        $metaTitle = trim($__env->yieldContent('meta_title'));
        if ($metaTitle === '') {
            $metaTitle = $pageTitle;
        }

        $metaDescription = trim($__env->yieldContent('meta_description'));
        if ($metaDescription === '') {
            $metaDescription = $seoDefaultDescription;
        }

        $metaKeywords = trim($__env->yieldContent('meta_keywords'));
        if ($metaKeywords === '') {
            $metaKeywords = $seoDefaultKeywords;
        }

        $metaImage = trim($__env->yieldContent('meta_image'));
        if ($metaImage === '') {
            $metaImage = $seoOgImage;
        }

        $canonical = trim($__env->yieldContent('canonical'));
        if ($canonical === '') {
            $canonical = url()->current();
        }
    @endphp

    @if($metaDescription !== '')
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @if($metaKeywords !== '')
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif
    <meta name="robots" content="{{ trim($__env->yieldContent('meta_robots')) ?: $seoRobots }}">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $metaTitle }}">
    @if($metaDescription !== '')
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:type" content="{{ trim($__env->yieldContent('og_type')) ?: 'website' }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:site_name" content="{{ \App\Models\Setting::get('app_name') ?: config('app.name', 'UNN') }}">
    @if($metaImage !== '')
        <meta property="og:image" content="{{ $metaImage }}">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ trim($__env->yieldContent('twitter_card')) ?: 'summary_large_image' }}">
    @if($seoTwitterSite !== '')
        <meta name="twitter:site" content="{{ $seoTwitterSite }}">
    @endif
    <meta name="twitter:title" content="{{ $metaTitle }}">
    @if($metaDescription !== '')
        <meta name="twitter:description" content="{{ $metaDescription }}">
    @endif
    @if($seoTwitterImage !== '')
        <meta name="twitter:image" content="{{ trim($__env->yieldContent('twitter_image')) ?: $seoTwitterImage }}">
    @endif

    @if($seoGoogleVerification !== '')
        <meta name="google-site-verification" content="{{ $seoGoogleVerification }}">
    @endif

    @if($trackingHead !== '')
        {!! $trackingHead !!}
    @endif

    {{-- Google AdSense Script --}}
    @if($adsEnabled && $adsensePublisherId !== '')
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsensePublisherId }}"
             crossorigin="anonymous"></script>
    @endif

    <link rel="icon" href="{{ $favicon }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ $logo }}">
    @if ($pwaEnabled)
        <link rel="manifest" href="{{ route('manifest') }}">
    @endif
    <meta name="theme-color" content="{{ $pwaTheme }}">

    <!-- Fonts & Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @if($videoPlayerEnabled)
        <link rel="stylesheet" href="{{ asset('vendor/plyr/plyr.css') }}">
    @endif

    @stack('styles')

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        :root {
            --unn-azul-1: #1F5EDB;
            /* principal */
            --unn-azul-2: #177FD6;
            /* secundário */
            --unn-azul-3: #1D3FC4;
            /* escuro */
            --unn-card: #ffffff;
            --unn-text: #0f172a;
            @if($videoPlayerEnabled)
                --plyr-color-main: {{ $videoPlyrColor }};
                --plyr-font-family: 'Inter', sans-serif;
                --plyr-menu-background: rgba(15, 23, 42, 0.92);
                --plyr-menu-color: #ffffff;
                --plyr-tooltip-background: var(--unn-azul-1);
                --plyr-tooltip-color: #ffffff;
            @endif
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--unn-azul-1) 0%, var(--unn-azul-2) 50%, var(--unn-azul-3) 100%);
            color: #fff;
            border: none;
        }

        .ui-tooltip {
            position: relative;
        }

        .ui-tooltip::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 50%;
            bottom: calc(100% + 10px);
            transform: translateX(-50%);
            background: var(--unn-azul-1, #1F5EDB);
            color: #fff;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s ease, transform 0.15s ease;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
            z-index: 20;
        }

        .ui-tooltip::before {
            content: '';
            position: absolute;
            left: 50%;
            bottom: calc(100% + 4px);
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: var(--unn-azul-1, #1F5EDB) transparent transparent transparent;
            opacity: 0;
            transition: opacity 0.15s ease;
            z-index: 20;
        }

        .ui-tooltip:hover::after,
        .ui-tooltip:focus-visible::after,
        .ui-tooltip:hover::before,
        .ui-tooltip:focus-visible::before {
            opacity: 1;
            transform: translateX(-50%) translateY(-2px);
        }

        html,
        body {
            overflow-x: hidden;
            max-width: 100vw;
        }

        body {
            @php
                $bgImage = \App\Models\Setting::get('site_bg_image');
                $bgOpacity = (int) \App\Models\Setting::get('site_bg_gradient_opacity', 85);
                $bgStart = \App\Models\Setting::get('site_bg_gradient_start', '#000000');
                $rgbaColor = 'rgba(0,0,0,0.85)';
                if ($bgStart) {
                    try {
                        list($r, $g, $b) = sscanf($bgStart, "#%02x%02x%02x");
                        $rgbaColor = "rgba($r, $g, $b, " . ($bgOpacity / 100) . ")";
                    } catch (\Throwable $e) { }
                }
            @endphp

            @if($bgImage)
                background: linear-gradient({{ $rgbaColor }}, {{ $rgbaColor }}), url('{{ asset($bgImage) }}');
                background-attachment: fixed;
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            @else
                background: linear-gradient(180deg, #f8fbff 0%, #ffffff 40%);
            @endif
            color: var(--unn-text);
        }

        /* Responsive fixes */
        img,
        video,
        iframe {
            max-width: 100%;
            height: auto;
        }

        .max-w-7xl,
        .max-w-6xl,
        .max-w-5xl,
        .max-w-4xl {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        @media (max-width: 640px) {
            /* Typography scale for small screens */
            .text-3xl {
                font-size: 1.5rem;
                line-height: 1.25;
            }

            .text-2xl {
                font-size: 1.25rem;
                line-height: 1.25;
            }

            .text-xl {
                font-size: 1.125rem;
                line-height: 1.35;
            }

            .text-lg {
                font-size: 1rem;
                line-height: 1.5;
            }

            .text-5xl {
                font-size: 2rem;
                line-height: 1.2;
            }

            .text-6xl {
                font-size: 2.25rem;
                line-height: 1.2;
            }

            .text-4xl {
                font-size: 1.75rem;
                line-height: 1.3;
            }

            /* Common spacing utilities used in CTAs */
            .px-10 {
                padding-left: 1.25rem;
                padding-right: 1.25rem;
            }

            .px-8 {
                padding-left: 1.25rem;
                padding-right: 1.25rem;
            }

            .px-6 {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            /* Normalize button paddings without collapsing layout blocks */
            a.py-4,
            button.py-4,
            [role="button"].py-4 {
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
            }

            a.py-3,
            button.py-3,
            [role="button"].py-3 {
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }

            table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 1200px) {
            .text-6xl {
                font-size: clamp(2.2rem, 6vw, 3.1rem);
                line-height: 1.15;
            }

            .text-5xl {
                font-size: clamp(1.95rem, 5vw, 2.7rem);
                line-height: 1.2;
            }

            .text-4xl {
                font-size: clamp(1.65rem, 4vw, 2.2rem);
                line-height: 1.25;
            }
        }

        @media (max-width: 1024px) {
            a.btn-primary,
            button.btn-primary {
                font-size: 0.95rem;
                line-height: 1.25rem;
                padding-left: 1rem;
                padding-right: 1rem;
            }

            a.rounded-full,
            button.rounded-full {
                max-width: 100%;
            }
        }

        @if($videoPlayerEnabled)
            .unn-video-player {
                position: relative;
            }

            .unn-video-player .plyr {
                border-radius: 0.75rem;
            }

            .unn-video-float-placeholder {
                display: none;
            }

            .unn-video-float {
                position: fixed !important;
                width: var(--unn-float-width, 420px) !important;
                height: var(--unn-float-height, 236px) !important;
                right: 24px;
                bottom: 92px;
                z-index: 9999;
                margin: 0 !important;
                border-radius: 16px;
                overflow: hidden;
                background: #000;
                box-shadow: 0 18px 55px rgba(0, 0, 0, 0.35);
            }

            .unn-video-float .unn-video-float-close {
                display: inline-flex;
            }

            .unn-video-float-close {
                display: none;
                position: absolute;
                z-index: 10;
                top: 10px;
                right: 10px;
                width: 34px;
                height: 34px;
                border-radius: 999px;
                align-items: center;
                justify-content: center;
                background: rgba(15, 23, 42, 0.65);
                border: 1px solid rgba(255, 255, 255, 0.25);
                color: #fff;
                cursor: pointer;
                backdrop-filter: blur(6px);
            }

            .unn-video-float-close:hover {
                background: rgba(15, 23, 42, 0.85);
            }

            @media (max-width: 768px) {
                .unn-video-float {
                    right: 12px;
                    bottom: 12px;
                    width: min(92vw, var(--unn-float-width, 420px)) !important;
                    height: min(52vw, var(--unn-float-height, 236px)) !important;
                }
            }

            .unn-video-watermark {
                position: absolute;
                z-index: 6;
                pointer-events: none;
                user-select: none;
                opacity: var(--unn-wm-opacity, 0.15);
                width: var(--unn-wm-size, 18%);
                max-width: 280px;
                min-width: 96px;
                mix-blend-mode: var(--unn-wm-blend, normal);
            }

            .unn-video-watermark-inner {
                width: 100%;
                transform: rotate(var(--unn-wm-rotate, 0deg));
            }

            .unn-video-watermark img {
                width: 100%;
                height: auto;
                display: block;
            }

            .unn-video-watermark-text {
                margin-top: 6px;
                font-size: 12px;
                font-weight: 800;
                letter-spacing: 0.02em;
                color: rgba(255, 255, 255, 0.92);
                text-shadow: 0 2px 6px rgba(0, 0, 0, 0.55);
                line-height: 1.1;
                word-break: break-word;
            }

            .unn-video-watermark--top-left {
                top: var(--unn-wm-margin, 16px);
                left: var(--unn-wm-margin, 16px);
            }

            .unn-video-watermark--top-right {
                top: var(--unn-wm-margin, 16px);
                right: var(--unn-wm-margin, 16px);
            }

            .unn-video-watermark--bottom-left {
                bottom: var(--unn-wm-margin, 16px);
                left: var(--unn-wm-margin, 16px);
            }

            .unn-video-watermark--bottom-right {
                bottom: var(--unn-wm-margin, 16px);
                right: var(--unn-wm-margin, 16px);
            }

            .unn-video-watermark--center {
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }

            @keyframes unnWatermarkDrift {
                0% {
                    transform: translate(0, 0) rotate(var(--unn-wm-rotate, 0deg));
                }
                50% {
                    transform: translate(-8px, 8px) rotate(var(--unn-wm-rotate, 0deg));
                }
                100% {
                    transform: translate(0, 0) rotate(var(--unn-wm-rotate, 0deg));
                }
            }

            .unn-video-watermark--animate .unn-video-watermark-inner {
                animation: unnWatermarkDrift 11s ease-in-out infinite;
            }
        @endif
    </style>
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
</head>

<body class="bg-slate-50 min-h-screen">
    @if($trackingBody !== '')
        {!! $trackingBody !!}
    @endif
    {{-- Banner de Impersonation - Flutuante discreto --}}
    @if(session()->has('impersonator_id'))
        <div id="impersonation-badge"
            class="fixed bottom-4 left-4 z-[9999] bg-yellow-400 text-yellow-900 px-3 py-2 rounded-lg shadow-lg text-xs font-bold flex items-center gap-2 max-w-xs">
            <i class="fas fa-user-secret"></i>
            <span class="truncate">{{ auth()->user()->name }}</span>
            <a href="{{ route('admin.impersonate.stop') }}"
                class="bg-yellow-900 text-yellow-100 px-2 py-1 rounded hover:bg-yellow-800 transition whitespace-nowrap">
                Sair
            </a>
            <button onclick="document.getElementById('impersonation-badge').style.display='none'" 
                class="text-yellow-900 hover:text-yellow-700 ml-1" title="Minimizar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @php $showNavigation = $showNavigation ?? true; @endphp
    @if($showNavigation)
        @include('partials.header')
    @endif

    <main class="{{ $showNavigation ? 'pt-20 lg:pt-24' : 'pt-0' }} min-h-[calc(100vh-80px)]">
        @yield('content')
    </main>

    @includeWhen(true, 'partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/inputmask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/zxcvbn@4.4.2/dist/zxcvbn.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Input masks
            var imasks = document.querySelectorAll('input[data-mask]');
            imasks.forEach(function (el) {
                var m = el.getAttribute('data-mask');
                Inputmask(m).mask(el);
            });

            // CEP auto-complete on any input with id=cep
            var cep = document.getElementById('cep');
            if (cep) {
                cep.addEventListener('input', function (e) {
                    var v = e.target.value.replace(/\D/g, '');
                    if (v.length === 8) {
                        fetch(`https://viacep.com.br/ws/${v}/json/`).then(r => r.json()).then(data => {
                            if (!data.erro) {
                                var address = document.getElementById('address');
                                if (address) {
                                    address.value = `${data.logradouro} - ${data.bairro} - ${data.localidade}/${data.uf}`;
                                    var campos = Array.prototype.slice.call(document.querySelectorAll('input,select,textarea'));
                                    var idx = campos.indexOf(address);
                                    if (idx > -1 && campos[idx + 1]) {
                                        campos[idx + 1].focus();
                                    }
                                }
                            }
                        });
                    }
                });
            }

            // Password strength indicator
            var pwd = document.getElementById('password');
            var strengthWrap = document.getElementById('pw-strength');
            var strength = strengthWrap ? strengthWrap.querySelector('span') : null;
            if (pwd && strength) {
                pwd.addEventListener('input', function () {
                    var score = (typeof zxcvbn === 'function') ? zxcvbn(pwd.value).score : 0;
                    var texts = ['Muito fraca', 'Fraca', 'OK', 'Boa', 'Forte'];
                    strength.textContent = texts[score];
                });
            }

            // Mobile menu toggle
            var mobileToggle = document.getElementById('mobile-menu-toggle');
            var mobileMenu = document.getElementById('mobile-menu');
            var mobilePanel = document.getElementById('mobile-menu-panel');
            var mobileOverlay = document.getElementById('mobile-menu-overlay');
            var mobileClose = document.getElementById('mobile-menu-close');
            if (mobileToggle && mobileMenu && mobilePanel && mobileOverlay && mobileClose) {
                var openMenu = function () {
                    mobileMenu.classList.remove('hidden');
                    mobileMenu.setAttribute('aria-hidden', 'false');
                    mobileOverlay.classList.remove('pointer-events-none');
                    setTimeout(function () {
                        mobileOverlay.classList.add('opacity-100');
                        mobilePanel.classList.remove('-translate-x-full');
                    }, 20);
                };
                var closeMenu = function () {
                    mobileOverlay.classList.remove('opacity-100');
                    mobilePanel.classList.add('-translate-x-full');
                    mobileOverlay.classList.add('pointer-events-none');
                    setTimeout(function () {
                        mobileMenu.classList.add('hidden');
                        mobileMenu.setAttribute('aria-hidden', 'true');
                    }, 400);
                };
                mobileToggle.addEventListener('click', openMenu);
                mobileClose.addEventListener('click', closeMenu);
                mobileOverlay.addEventListener('click', closeMenu);
            }

            document.querySelectorAll('button[title], a[title]').forEach(function (el) {
                var tooltipText = el.getAttribute('title');
                if (!tooltipText) {
                    return;
                }

                el.setAttribute('data-tooltip', tooltipText);
                el.classList.add('ui-tooltip');
                el.removeAttribute('title');
            });
        });

        // Global Notifications Polling
        @auth
            window.refreshNotifications = function() {
                fetch('{{ route("connection.notifications") }}')
                    .then(r => r.json())
                    .then(data => {
                        const badge = document.getElementById('connection-notification-count');
                        if (badge) {
                            if (data.count > 0) {
                                badge.textContent = data.count;
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                    });
            };
            setInterval(window.refreshNotifications, 15000);
            window.refreshNotifications();
        @endauth
    </script>

    @if($videoPlayerEnabled)
        <script>
            window.UNN = window.UNN || {};
            window.UNN.videoPlayer = {!! $videoPlayerConfigJson !!};
        </script>
        <script src="{{ asset('vendor/plyr/plyr.polyfilled.js') }}"></script>
        <script>
            (function () {
                const config = (window.UNN && window.UNN.videoPlayer) ? window.UNN.videoPlayer : null;
                if (!config || !config.enabled) return;
                const canUsePlyr = () => typeof Plyr !== 'undefined';

                function deepMerge(target, source) {
                    if (!source || typeof source !== 'object') return target;

                    for (const key of Object.keys(source)) {
                        const value = source[key];
                        if (value && typeof value === 'object' && !Array.isArray(value)) {
                            if (!target[key] || typeof target[key] !== 'object' || Array.isArray(target[key])) {
                                target[key] = {};
                            }
                            deepMerge(target[key], value);
                            continue;
                        }
                        target[key] = value;
                    }
                    return target;
                }

                function buildPlyrOptions() {
                    const plyr = (config && config.plyr) ? config.plyr : {};

                    const base = {
                        autoplay: !!plyr.autoplay,
                        muted: !!plyr.muted,
                        clickToPlay: plyr.clickToPlay !== false,
                        disableContextMenu: plyr.disableContextMenu !== false,
                        seekTime: Number.isFinite(plyr.seekTime) ? plyr.seekTime : 10,
                    };

                    if (Array.isArray(plyr.controls) && plyr.controls.length) {
                        let controls = [...plyr.controls];
                        if (plyr.rewindEnabled === false) controls = controls.filter(c => c !== 'rewind');
                        if (plyr.fastForwardEnabled === false) controls = controls.filter(c => c !== 'fast-forward');
                        if (plyr.volumeEnabled === false) controls = controls.filter(c => c !== 'mute' && c !== 'volume');
                        
                        // Ensure buttons are present if enabled
                        if (plyr.rewindEnabled && !controls.includes('rewind')) controls.splice(1, 0, 'rewind');
                        if (plyr.fastForwardEnabled && !controls.includes('fast-forward')) controls.splice(2, 0, 'fast-forward');

                        base.controls = controls;
                    }
                    if (Array.isArray(plyr.settings) && plyr.settings.length) {
                        base.settings = plyr.settings;
                    }
                    if (Number.isFinite(plyr.volume) && plyr.volumeEnabled !== false) {
                        base.volume = Math.max(0, Math.min(1, Number(plyr.volume)));
                    }
                    if (Array.isArray(plyr.speedOptions) && plyr.speedOptions.length) {
                        const selected = Number.isFinite(plyr.speedSelected) ? Number(plyr.speedSelected) : 1;
                        base.speed = {
                            selected: selected,
                            options: plyr.speedOptions.map(n => Number(n)).filter(n => Number.isFinite(n))
                        };
                    }

                    const custom = plyr.customOptions && typeof plyr.customOptions === 'object'
                        ? plyr.customOptions
                        : null;

                    return deepMerge(base, custom || {});
                }

                function extractYouTubeId(url) {
                    if (!url) return null;
                    const patterns = [
                        /youtu\.be\/([^?&#/]+)/i,
                        /youtube\.com\/watch\?v=([^?&#/]+)/i,
                        /youtube\.com\/embed\/([^?&#/]+)/i,
                        /youtube\.com\/shorts\/([^?&#/]+)/i,
                    ];
                    for (const pattern of patterns) {
                        const match = String(url).match(pattern);
                        if (match && match[1]) return match[1];
                    }

                    try {
                        const parsed = new URL(String(url));
                        const v = parsed.searchParams.get('v');
                        if (v) return v;
                    } catch (e) { /* ignore */ }

                    return null;
                }

                function extractVimeoId(url) {
                    if (!url) return null;
                    const patterns = [
                        /vimeo\.com\/(\d+)/i,
                        /player\.vimeo\.com\/video\/(\d+)/i,
                    ];
                    for (const pattern of patterns) {
                        const match = String(url).match(pattern);
                        if (match && match[1]) return match[1];
                    }
                    return null;
                }

                function normalizeVideoUrl(value) {
                    let url = (value == null) ? '' : String(value);
                    url = url.trim();
                    if (!url) return '';

                    if (/^https?:\/\//i.test(url)) {
                        try {
                            const parsed = new URL(url);
                            const sameHost = parsed.host === window.location.host;
                            if (!sameHost) {
                                return url;
                            }

                            let path = String(parsed.pathname || '').replace(/^\/+/, '');
                            if (path.startsWith('public/storage/app/public/')) {
                                return '/storage/' + path.replace(/^public\/storage\/app\/public\//, '');
                            }
                            if (path.startsWith('storage/app/public/')) {
                                return '/storage/' + path.replace(/^storage\/app\/public\//, '');
                            }
                            if (path.startsWith('public/')) {
                                path = path.replace(/^public\//, '');
                            }
                            if (/^(course-videos|course-materials)\//i.test(path)) {
                                return '/storage/' + path;
                            }
                            if (path.startsWith('storage/')) {
                                return '/' + path;
                            }
                        } catch (e) { /* ignore */ }
                        return url;
                    }
                    if (url.startsWith('//')) return (window.location.protocol || 'https:') + url;

                    if (url.startsWith('/storage/app/public/')) {
                        return '/storage/' + url.replace(/^\/storage\/app\/public\//, '');
                    }
                    if (url.startsWith('storage/app/public/')) {
                        return '/storage/' + url.replace(/^storage\/app\/public\//, '');
                    }
                    if (url.startsWith('public/')) {
                        url = url.replace(/^public\//, '');
                    }
                    if (/^(course-videos|course-materials)\//i.test(url)) {
                        return '/storage/' + url;
                    }

                    if (url.startsWith('/')) return url;

                    // Allow common providers without protocol.
                    if (/^(www\.)?(youtube\.com|youtu\.be|vimeo\.com|player\.vimeo\.com)\//i.test(url)) {
                        url = url.replace(/^www\./i, '');
                        return 'https://' + url;
                    }

                    // Most stored paths are relative to the public root (ex: storage/... or uploads/...).
                    return '/' + url;
                }

                function isDirectVideo(url) {
                    if (!url) return false;
                    const cleaned = String(url).split('#')[0].split('?')[0].toLowerCase();
                    return (
                        cleaned.endsWith('.mp4') ||
                        cleaned.endsWith('.webm') ||
                        cleaned.endsWith('.ogg') ||
                        cleaned.endsWith('.m4v')
                    );
                }

                function guessMimeType(url) {
                    const cleaned = String(url).split('#')[0].split('?')[0].toLowerCase();
                    if (cleaned.endsWith('.webm')) return 'video/webm';
                    if (cleaned.endsWith('.ogg')) return 'video/ogg';
                    return 'video/mp4';
                }

                function applyWatermark(container) {
                    const wm = config.watermark || {};
                    if (!wm.enabled) return;
                    if (!wm.imageUrl && !wm.text) return;

                    if (!container) return;
                    if (container.querySelector('.unn-video-watermark')) return;

                    const outer = document.createElement('div');
                    const position = wm.position || 'top-right';
                    outer.className = [
                        'unn-video-watermark',
                        'unn-video-watermark--' + position,
                        wm.animate ? 'unn-video-watermark--animate' : '',
                    ].filter(Boolean).join(' ');

                    outer.style.setProperty('--unn-wm-opacity', Number.isFinite(wm.opacity) ? String(wm.opacity) : '0.15');
                    outer.style.setProperty('--unn-wm-size', (Number.isFinite(wm.sizePercent) ? wm.sizePercent : 18) + '%');
                    outer.style.setProperty('--unn-wm-margin', (Number.isFinite(wm.margin) ? wm.margin : 16) + 'px');
                    outer.style.setProperty('--unn-wm-rotate', (Number.isFinite(wm.rotate) ? wm.rotate : 0) + 'deg');
                    outer.style.setProperty('--unn-wm-blend', wm.blend || 'normal');

                    const inner = document.createElement('div');
                    inner.className = 'unn-video-watermark-inner';

                    if (wm.imageUrl) {
                        const img = document.createElement('img');
                        img.src = wm.imageUrl;
                        img.alt = "Marca d'agua";
                        img.loading = 'lazy';
                        inner.appendChild(img);
                    }

                    if (wm.text) {
                        const text = document.createElement('div');
                        text.className = 'unn-video-watermark-text';
                        text.textContent = String(wm.text);
                        inner.appendChild(text);
                    }

                    outer.appendChild(inner);
                    container.appendChild(outer);
                }

                function bindPlaybackProtections(target, disableContextMenu, blockDownload) {
                    if (!target) return;

                    if (disableContextMenu) {
                        target.addEventListener('contextmenu', function (event) {
                            event.preventDefault();
                        }, true);
                        target.setAttribute('oncontextmenu', 'return false;');
                    }

                    if (!blockDownload) {
                        return;
                    }

                    target.addEventListener('dragstart', function (event) {
                        event.preventDefault();
                    }, true);

                    if (target.tagName === 'VIDEO') {
                        target.setAttribute('controlsList', 'nodownload noplaybackrate noremoteplayback');
                        target.setAttribute('disablePictureInPicture', '');
                        target.setAttribute('disableRemotePlayback', '');
                    }
                }

                function initOne(wrapper) {
                    if (!wrapper || wrapper.dataset.unnVideoPlayerInit === '1') return;
                    wrapper.dataset.unnVideoPlayerInit = '1';

                    const rawUrl = wrapper.dataset.videoUrl || wrapper.getAttribute('data-video-url') || '';
                    const url = normalizeVideoUrl(rawUrl);
                    if (!url) return;

                    const youtubeId = extractYouTubeId(url);
                    const vimeoId = youtubeId ? null : extractVimeoId(url);
                    const options = buildPlyrOptions();

                    const blockDownload = (wrapper.dataset.blockDownload === '1') || (wrapper.getAttribute('data-block-download') === '1');
                    const floatingEnabled = (wrapper.dataset.floatingEnabled === '1') || (wrapper.getAttribute('data-floating-enabled') === '1');
                    const floatingWidth = parseInt(wrapper.dataset.floatingWidth || wrapper.getAttribute('data-floating-width') || '', 10);
                    const floatingHeight = parseInt(wrapper.dataset.floatingHeight || wrapper.getAttribute('data-floating-height') || '', 10);
                    const plyrAvailable = canUsePlyr();
                    const disableContextMenu = blockDownload || options.disableContextMenu !== false;

                    options.disableContextMenu = disableContextMenu;
                    if (blockDownload && Array.isArray(options.controls) && options.controls.length) {
                        options.controls = options.controls.filter(c => c !== 'download');
                    }

                    wrapper.classList.add('unn-video-player');
                    wrapper.innerHTML = '';
                    bindPlaybackProtections(wrapper, disableContextMenu, blockDownload);

                    const host = wrapper.closest('[data-unn-video-host]') || wrapper;

                    let target;
                    if (youtubeId) {
                        if (plyrAvailable) {
                            target = document.createElement('div');
                            target.setAttribute('data-plyr-provider', 'youtube');
                            target.setAttribute('data-plyr-embed-id', youtubeId);
                            wrapper.appendChild(target);
                        } else {
                            target = document.createElement('iframe');
                            target.src = 'https://www.youtube.com/embed/' + encodeURIComponent(youtubeId);
                            target.setAttribute('allowfullscreen', '');
                            target.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
                            target.className = 'w-full h-full min-h-[400px]';
                            wrapper.appendChild(target);
                        }
                    } else if (vimeoId) {
                        if (plyrAvailable) {
                            target = document.createElement('div');
                            target.setAttribute('data-plyr-provider', 'vimeo');
                            target.setAttribute('data-plyr-embed-id', vimeoId);
                            wrapper.appendChild(target);
                        } else {
                            target = document.createElement('iframe');
                            target.src = 'https://player.vimeo.com/video/' + encodeURIComponent(vimeoId);
                            target.setAttribute('allowfullscreen', '');
                            target.setAttribute('allow', 'autoplay; fullscreen; picture-in-picture');
                            target.className = 'w-full h-full min-h-[400px]';
                            wrapper.appendChild(target);
                        }
                    } else {
                        target = document.createElement('video');
                        target.setAttribute('playsinline', '');
                        target.setAttribute('controls', '');

                        const source = document.createElement('source');
                        source.src = String(url);
                        source.type = guessMimeType(url);
                        target.appendChild(source);

                        wrapper.appendChild(target);
                    }
                    bindPlaybackProtections(target, disableContextMenu, blockDownload);

                    if (!plyrAvailable) {
                        wrapper.__unnVideoApi = {
                            player: null,
                            media: target && target.tagName === 'VIDEO' ? target : null,
                            wrapper: wrapper,
                        };
                        wrapper.dispatchEvent(new CustomEvent('unn:video-ready', { detail: wrapper.__unnVideoApi }));
                        applyWatermark(wrapper);
                        if (floatingEnabled) {
                            const width = Number.isFinite(floatingWidth) ? floatingWidth : 420;
                            const height = Number.isFinite(floatingHeight) ? floatingHeight : Math.round(width * 9 / 16);
                            setupFloatingPlayer(host, null, { width, height });
                        }
                        return;
                    }

                    const player = new Plyr(target, options);
                    const media = (player && player.media) ? player.media : null;
                    if (media) {
                        bindPlaybackProtections(media, disableContextMenu, blockDownload);
                    }
                    const container = (player && player.elements) ? player.elements.container : wrapper;
                    if (container) {
                        bindPlaybackProtections(container, disableContextMenu, blockDownload);
                    }
                    wrapper.__unnVideoApi = {
                        player: player,
                        media: media,
                        wrapper: wrapper,
                    };
                    wrapper.dispatchEvent(new CustomEvent('unn:video-ready', { detail: wrapper.__unnVideoApi }));
                    applyWatermark(container);

                    if (floatingEnabled) {
                        const width = Number.isFinite(floatingWidth) ? floatingWidth : 420;
                        const height = Number.isFinite(floatingHeight) ? floatingHeight : Math.round(width * 9 / 16);
                        setupFloatingPlayer(host, player, { width, height });
                    }
                }

                function setupFloatingPlayer(host, player, cfg) {
                    if (!host || host.dataset.unnVideoFloatingInit === '1') return;
                    host.dataset.unnVideoFloatingInit = '1';

                    let disabled = false;

                    const clampInt = function (value, fallback, min, max) {
                        const parsed = parseInt(String(value || ''), 10);
                        const n = Number.isFinite(parsed) ? parsed : fallback;
                        return Math.max(min, Math.min(max, n));
                    };

                    const width = clampInt(cfg && cfg.width, 420, 260, 960);
                    const height = clampInt(cfg && cfg.height, Math.round(width * 9 / 16), 160, 720);

                    host.style.setProperty('--unn-float-width', width + 'px');
                    host.style.setProperty('--unn-float-height', height + 'px');

                    const closeBtn = document.createElement('button');
                    closeBtn.type = 'button';
                    closeBtn.className = 'unn-video-float-close';
                    closeBtn.setAttribute('aria-label', 'Fechar mini player');
                    closeBtn.innerHTML = '&times;';
                    closeBtn.addEventListener('click', function () {
                        disabled = true;
                        setFloating(false);
                    });
                    host.appendChild(closeBtn);

                    const placeholder = document.createElement('div');
                    placeholder.className = 'unn-video-float-placeholder';
                    placeholder.style.display = 'none';
                    if (host.parentNode) {
                        host.parentNode.insertBefore(placeholder, host);
                    }

                    function setFloating(on) {
                        if (disabled) on = false;

                        if (on) {
                            if (!host.classList.contains('unn-video-float')) {
                                const rect = host.getBoundingClientRect();
                                placeholder.style.height = rect.height + 'px';
                                placeholder.style.display = 'block';
                                host.classList.add('unn-video-float');
                            }
                        } else {
                            if (host.classList.contains('unn-video-float')) {
                                host.classList.remove('unn-video-float');
                                placeholder.style.display = 'none';
                            }
                        }

                        try {
                            if (player && typeof player.resize === 'function') {
                                player.resize();
                            }
                        } catch (e) { /* ignore */ }
                    }

                    if ('IntersectionObserver' in window) {
                        const observer = new IntersectionObserver(function (entries) {
                            for (const entry of entries) {
                                // Float when the player is mostly out of view.
                                setFloating(!entry.isIntersecting);
                            }
                        }, { threshold: 0.15 });
                        observer.observe(host);
                    } else {
                        const onScroll = function () {
                            const rect = host.getBoundingClientRect();
                            const inView = rect.bottom > 100 && rect.top < (window.innerHeight - 100);
                            setFloating(!inView);
                        };
                        window.addEventListener('scroll', onScroll, { passive: true });
                        onScroll();
                    }
                }

                function initAll() {
                    document.querySelectorAll('[data-unn-video-player]').forEach(initOne);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initAll);
                } else {
                    initAll();
                }
            })();
        </script>
    @endif

    {{-- Floating Chat Component (persiste entre páginas) --}}
    @include('partials.floating-chat')

    @stack('scripts')

    @if ($pwaEnabled)
        <script>
                    if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(function () { console.log('Service Worker registrado'); })
                    .catch(function (err) { console.error('SW erro:', err); });
            }

            let deferredPrompt;

            const showInstallModal = () => {
                if (document.getElementById('pwa-install-modal')) return;

                const modal = document.createElement('div');
                modal.id = 'pwa-install-modal';
                modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm animate-fade-in';

                modal.innerHTML = `
                                <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center relative transform transition-all scale-100">
                                    <div class="flex justify-center mb-6">
                                        <img src="{{ $logo }}" alt="Logo" class="h-16 object-contain">
                                    </div>

                                    <h3 class="text-xl font-bold text-slate-900 mb-3">Instale nosso aplicativo!</h3>
                                    <p class="text-slate-600 text-sm mb-8 leading-relaxed">
                                        Tenha acesso mais rápido e use mesmo offline! Instale nosso app diretamente na sua tela inicial.
                                    </p>

                                    <div class="flex flex-col gap-3">
                                        <button id="pwa-install-btn" class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:translate-y-[-2px] transition-all">
                                            Instalar Agora
                                        </button>
                                        <button id="pwa-dismiss-btn" class="w-full py-3 px-4 bg-slate-100 text-slate-600 font-medium rounded-xl hover:bg-slate-200 transition-colors">
                                            Mais tarde
                                        </button>
                                    </div>
                                </div>
                            `;

                document.body.appendChild(modal);

                // Animação de entrada
                requestAnimationFrame(() => {
                    modal.querySelector('div').classList.add('scale-100');
                    modal.querySelector('div').classList.remove('scale-95');
                });

                document.getElementById('pwa-install-btn').addEventListener('click', async () => {
                    if (!deferredPrompt) return;
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log('User response to the install prompt: ' + outcome);
                    deferredPrompt = null;
                    removeModal();
                });

                document.getElementById('pwa-dismiss-btn').addEventListener('click', () => {
                    removeModal();
                    // Opcional: Salvar em cookie/localStorage para não mostrar novamente por X dias
                });

                function removeModal() {
                    modal.classList.add('opacity-0');
                    setTimeout(() => modal.remove(), 300);
                }
            };

            window.showInstallModal = showInstallModal;

            window.addEventListener('beforeinstallprompt', (e) => {
                // Impede que o Chrome mostre o prompt nativo automaticamente (para mobile principalmente)
                e.preventDefault();
                // Guarda o evento para acionar depois
                deferredPrompt = e;

                // Mostra o modal customizado
                // Pequeno delay para garantir que a página carregou
                setTimeout(showInstallModal, 2000);
            });
        </script>
    @endif
    <!-- jQuery (necessário para Toastr) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
    <script>
        toastr.options = { positionClass: 'toast-top-right', timeOut: 4000, progressBar: true };
        @if(session('success'))
            toastr.success(@json(session('success')));
        @endif
        @if(session('status'))
            toastr.success(@json(session('status')));
        @endif
        @if(session('error'))
            toastr.error(@json(session('error')));
        @endif
        @if(session('warning'))
            toastr.warning(@json(session('warning')));
        @endif
        @if(session('info'))
            toastr.info(@json(session('info')));
        @endif
    </script>
</body>

</html>
