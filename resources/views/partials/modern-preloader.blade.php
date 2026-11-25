{{-- ==========================================================================
     PRELOADER MODERNO CONFIGURÁVEL SOMOS UNN
     Padrão atual preservado: Orbital UNN (anéis orbitais duplos, núcleo pulsante,
     barra linear futurista e saída cinematográfica com blur).
     Suporta 5 modelos, 4 animações de saída, controle independente para /admin,
     cor de destaque, duração em ms, texto personalizado e preview ao vivo.
     ========================================================================== --}}
@php
    $isAdminArea = request()->is('admin', 'admin/*') || request()->routeIs('admin.*');
    $preloaderGlobalEnabled = (string) \App\Models\Setting::get('preloader_enabled', '1') !== '0';
    $preloaderAdminEnabled = (string) \App\Models\Setting::get('preloader_admin_enabled', '1') !== '0';
    $shouldRenderPreloader = $isAdminArea ? $preloaderAdminEnabled : $preloaderGlobalEnabled;

    // Permite que a página de configurações sempre carregue o engine de preview ao vivo
    $isAppearanceSettingsPage = request()->routeIs('admin.settings*', 'panel.admin.settings*') && (request()->route('group') === 'appearance' || request('group') === 'appearance');

    $preloaderStyle = (string) \App\Models\Setting::get('preloader_style', 'orbital');
    if (!in_array($preloaderStyle, ['orbital', 'pulse', 'minimal', 'glass', 'executive'], true)) {
        $preloaderStyle = 'orbital';
    }

    $preloaderExitAnim = (string) \App\Models\Setting::get('preloader_exit_animation', 'blur');
    if (!in_array($preloaderExitAnim, ['blur', 'fade', 'zoom', 'slide-up'], true)) {
        $preloaderExitAnim = 'blur';
    }

    $preloaderDurationMs = (int) \App\Models\Setting::get('preloader_duration_ms', 650);
    $preloaderDurationMs = max(150, min(3500, $preloaderDurationMs));

    $preloaderShowProgress = (string) \App\Models\Setting::get('preloader_show_progress', '1') !== '0';
    $preloaderPjaxEnabled = (string) \App\Models\Setting::get('preloader_pjax_enabled', '1') !== '0';

    $preloaderAccent = trim((string) \App\Models\Setting::get('preloader_accent_color', '#1F5EDB'));
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $preloaderAccent)) {
        $preloaderAccent = '#1F5EDB';
    }

    $preloaderText = trim((string) \App\Models\Setting::get('preloader_text', 'SOMOS UNN'));
    if ($preloaderText === '') {
        $preloaderText = 'SOMOS UNN';
    }

    $preloaderImgSrc = $preloaderImage ?? asset('img/logo.svg');
@endphp

@if($shouldRenderPreloader || $isAppearanceSettingsPage)
    <div id="unn-preloader"
         class="unn-preloader-overlay unn-style-{{ $preloaderStyle }} unn-exit-{{ $preloaderExitAnim }}"
         style="--unn-pre-accent: {{ $preloaderAccent }}; {{ !$shouldRenderPreloader ? 'display:none;' : '' }}"
         data-duration="{{ $preloaderDurationMs }}"
         data-pjax-enabled="{{ $preloaderPjaxEnabled ? '1' : '0' }}"
         data-auto-run="{{ $shouldRenderPreloader ? '1' : '0' }}"
         aria-hidden="true"
         role="status">

        {{-- Glow ambiente difuso nas cores da marca --}}
        <div class="unn-preloader-glow"></div>

        <div class="unn-preloader-content">
            {{-- Núcleo Visual (adapta-se aos 5 modelos via CSS) --}}
            <div class="unn-orbital-wrapper">
                <div class="unn-ring-outer"></div>
                <div class="unn-ring-reverse"></div>
                <div class="unn-ring-pulse"></div>
                <div class="unn-ring-pulse unn-ring-pulse-2"></div>

                <div class="unn-logo-container">
                    <img src="{{ $preloaderImgSrc }}" alt="SOMOS UNN" class="unn-logo-img"
                        onerror="this.onerror=null;this.src='{{ asset('img/logo.svg') }}';">
                </div>
            </div>

            {{-- Tipografia com Efeito Shimmer --}}
            <div class="unn-brand-text-wrapper">
                <span class="unn-brand-text" id="unn-preloader-label">{{ $preloaderText }}</span>
                <span class="unn-dots">
                    <span class="unn-dot"></span>
                    <span class="unn-dot"></span>
                    <span class="unn-dot"></span>
                </span>
            </div>

            {{-- Barra de Progresso Linear --}}
            <div class="unn-progress-container {{ $preloaderShowProgress ? '' : 'd-none' }}" id="unn-preloader-progress-wrap">
                <div class="unn-progress-bar">
                    <div class="unn-progress-shimmer"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ==========================================================================
           ESTILOS DO PRELOADER MODERNO SOMOS UNN (5 MODELOS + 4 ANIMAÇÕES DE SAÍDA)
           ========================================================================== */
        :root {
            --unn-pre-accent: #1F5EDB;
            --unn-pre-accent-2: #29ABE2;
        }

        #unn-preloader {
            position: fixed;
            inset: 0;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            transition: opacity 0.55s cubic-bezier(0.16, 1, 0.3, 1),
                        transform 0.55s cubic-bezier(0.16, 1, 0.3, 1),
                        filter 0.55s cubic-bezier(0.16, 1, 0.3, 1),
                        visibility 0.55s;
            will-change: opacity, transform, filter;
        }

        .dark #unn-preloader,
        .dark-mode #unn-preloader,
        [data-bs-theme="dark"] #unn-preloader {
            background: #090d16;
        }

        /* Animações de Saída */
        #unn-preloader.unn-preloader-exit {
            opacity: 0 !important;
            pointer-events: none !important;
            visibility: hidden !important;
        }
        #unn-preloader.unn-exit-blur.unn-preloader-exit {
            transform: scale(1.05) !important;
            filter: blur(8px) !important;
        }
        #unn-preloader.unn-exit-fade.unn-preloader-exit {
            transform: none !important;
            filter: none !important;
        }
        #unn-preloader.unn-exit-zoom.unn-preloader-exit {
            transform: scale(0.86) !important;
            filter: none !important;
        }
        #unn-preloader.unn-exit-slide-up.unn-preloader-exit {
            transform: translateY(-100%) !important;
            opacity: 1 !important;
            filter: none !important;
        }

        /* Glow Ambiente Difuso */
        .unn-preloader-glow {
            position: absolute;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, color-mix(in srgb, var(--unn-pre-accent) 24%, transparent) 0%, rgba(41, 171, 226, 0.08) 45%, transparent 70%);
            border-radius: 50%;
            filter: blur(35px);
            pointer-events: none;
            animation: unn-ambient-pulse 3s ease-in-out infinite alternate;
        }

        @keyframes unn-ambient-pulse {
            0% { transform: scale(0.9); opacity: 0.6; }
            100% { transform: scale(1.2); opacity: 1; }
        }

        .unn-preloader-content {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.25s ease;
        }

        /* Núcleo Orbital (Padrão: orbital) */
        .unn-orbital-wrapper {
            position: relative;
            width: 124px;
            height: 124px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
        }

        .unn-ring-outer {
            position: absolute;
            inset: -12px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, transparent 0%, var(--unn-pre-accent) 40%, var(--unn-pre-accent-2) 80%, transparent 100%);
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #fff calc(100% - 2.5px));
            mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #fff calc(100% - 2.5px));
            animation: unn-spin-clockwise 1.3s linear infinite;
            filter: drop-shadow(0 0 6px color-mix(in srgb, var(--unn-pre-accent) 55%, transparent));
        }

        .unn-ring-reverse {
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            border: 1.5px dashed color-mix(in srgb, var(--unn-pre-accent) 50%, transparent);
            animation: unn-spin-counter 3.5s linear infinite;
        }

        .unn-ring-pulse {
            position: absolute;
            inset: -20px;
            border-radius: 50%;
            border: 1px solid color-mix(in srgb, var(--unn-pre-accent-2) 40%, transparent);
            animation: unn-ripple 2s cubic-bezier(0.16, 1, 0.3, 1) infinite;
            opacity: 0;
        }

        .unn-ring-pulse-2 {
            display: none;
            animation-delay: 0.95s;
        }

        @keyframes unn-spin-clockwise { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes unn-spin-counter { from { transform: rotate(360deg); } to { transform: rotate(0deg); } }
        @keyframes unn-ripple { 0% { transform: scale(0.82); opacity: 0.85; } 100% { transform: scale(1.42); opacity: 0; } }

        .unn-logo-container {
            position: relative;
            z-index: 5;
            width: 78px;
            height: 78px;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px color-mix(in srgb, var(--unn-pre-accent) 24%, transparent), 0 0 0 1px rgba(226, 232, 240, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 11px;
            animation: unn-logo-breathe 2.4s ease-in-out infinite;
        }

        .dark .unn-logo-container,
        .dark-mode .unn-logo-container,
        [data-bs-theme="dark"] .unn-logo-container {
            background: #0f172a;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(30, 41, 59, 0.85);
        }

        .unn-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            user-select: none;
            pointer-events: none;
        }

        @keyframes unn-logo-breathe {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 2px 4px rgba(0,0,0,0.06)); }
            50% { transform: scale(1.06); filter: drop-shadow(0 6px 14px color-mix(in srgb, var(--unn-pre-accent) 35%, transparent)); }
        }

        /* Tipografia e Shimmer */
        .unn-brand-text-wrapper {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 14px;
        }

        .unn-brand-text {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            background: linear-gradient(90deg, var(--unn-pre-accent) 0%, var(--unn-pre-accent-2) 50%, var(--unn-pre-accent) 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: unn-shimmer-text 2.4s linear infinite;
        }

        @keyframes unn-shimmer-text { to { background-position: 200% center; } }

        .unn-dots { display: inline-flex; align-items: center; gap: 3.5px; }
        .unn-dot {
            width: 4px; height: 4px; border-radius: 50%;
            background-color: var(--unn-pre-accent);
            animation: unn-dot-pulse 1.4s ease-in-out infinite;
        }
        .unn-dot:nth-child(2) { animation-delay: 0.2s; }
        .unn-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes unn-dot-pulse {
            0%, 100% { opacity: 0.35; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1.35); }
        }

        /* Barra de Progresso Linear */
        .unn-progress-container {
            width: 190px;
            height: 3.5px;
            background: #e2e8f0;
            border-radius: 9999px;
            overflow: hidden;
            position: relative;
        }

        .dark .unn-progress-container,
        .dark-mode .unn-progress-container,
        [data-bs-theme="dark"] .unn-progress-container {
            background: #1e293b;
        }

        .unn-progress-bar {
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, var(--unn-pre-accent) 0%, var(--unn-pre-accent-2) 100%);
            border-radius: 9999px;
            position: relative;
            overflow: hidden;
            animation: unn-progress-fill 1.8s cubic-bezier(0.65, 0, 0.35, 1) infinite;
        }

        .unn-progress-shimmer {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.8) 50%, transparent 100%);
            animation: unn-progress-shimmer 1.2s infinite;
        }

        @keyframes unn-progress-fill {
            0% { transform: translateX(-100%); }
            50% { transform: translateX(0%); }
            100% { transform: translateX(100%); }
        }
        @keyframes unn-progress-shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* ==========================================================================
           MODELO 2: PULSE NEON / HALO (unn-style-pulse)
           ========================================================================== */
        #unn-preloader.unn-style-pulse .unn-ring-outer,
        #unn-preloader.unn-style-pulse .unn-ring-reverse {
            display: none;
        }
        #unn-preloader.unn-style-pulse .unn-ring-pulse,
        #unn-preloader.unn-style-pulse .unn-ring-pulse-2 {
            display: block;
            inset: -14px;
            border: 2.5px solid var(--unn-pre-accent);
            box-shadow: 0 0 18px color-mix(in srgb, var(--unn-pre-accent) 45%, transparent);
        }

        /* ==========================================================================
           MODELO 3: MINIMALISTA CLEAN (unn-style-minimal)
           ========================================================================== */
        #unn-preloader.unn-style-minimal .unn-preloader-glow,
        #unn-preloader.unn-style-minimal .unn-ring-reverse,
        #unn-preloader.unn-style-minimal .unn-ring-pulse {
            display: none;
        }
        #unn-preloader.unn-style-minimal .unn-orbital-wrapper {
            width: 86px;
            height: 86px;
            margin-bottom: 14px;
        }
        #unn-preloader.unn-style-minimal .unn-ring-outer {
            inset: -6px;
            filter: none;
            animation-duration: 0.9s;
        }
        #unn-preloader.unn-style-minimal .unn-logo-container {
            width: 62px;
            height: 62px;
            animation: none;
            box-shadow: none;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        #unn-preloader.unn-style-minimal .unn-progress-container {
            width: 130px;
            height: 2.5px;
        }

        /* ==========================================================================
           MODELO 4: GLASSMORPHISM CARD (unn-style-glass)
           ========================================================================== */
        #unn-preloader.unn-style-glass {
            background: rgba(241, 245, 249, 0.78);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }
        .dark #unn-preloader.unn-style-glass,
        .dark-mode #unn-preloader.unn-style-glass,
        [data-bs-theme="dark"] #unn-preloader.unn-style-glass {
            background: rgba(9, 13, 22, 0.78);
        }
        #unn-preloader.unn-style-glass .unn-preloader-content {
            padding: 2.25rem 2.75rem;
            border-radius: 1.5rem;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.65);
            box-shadow: 0 24px 55px -15px rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(16px);
        }
        .dark #unn-preloader.unn-style-glass .unn-preloader-content,
        .dark-mode #unn-preloader.unn-style-glass .unn-preloader-content,
        [data-bs-theme="dark"] #unn-preloader.unn-style-glass .unn-preloader-content {
            background: rgba(15, 23, 42, 0.72);
            border-color: rgba(148, 163, 184, 0.22);
            box-shadow: 0 24px 60px -15px rgba(0, 0, 0, 0.6);
        }
        #unn-preloader.unn-style-glass .unn-ring-reverse {
            display: none;
        }

        /* ==========================================================================
           MODELO 5: BARRA EXECUTIVA (unn-style-executive)
           ========================================================================== */
        #unn-preloader.unn-style-executive .unn-ring-outer,
        #unn-preloader.unn-style-executive .unn-ring-reverse,
        #unn-preloader.unn-style-executive .unn-ring-pulse {
            display: none;
        }
        #unn-preloader.unn-style-executive .unn-orbital-wrapper {
            width: 96px;
            height: 96px;
            margin-bottom: 16px;
        }
        #unn-preloader.unn-style-executive .unn-logo-container {
            width: 92px;
            height: 92px;
            border-radius: 1.25rem;
        }
        #unn-preloader.unn-style-executive .unn-progress-container {
            display: block !important;
            width: 260px;
            height: 6px;
        }

        @media (prefers-reduced-motion: reduce) {
            #unn-preloader { display: none !important; }
        }
    </style>

    <script>
        (function () {
            const preloader = document.getElementById('unn-preloader');
            if (!preloader) return;

            const autoRun = preloader.dataset.autoRun === '1';
            const pjaxEnabled = preloader.dataset.pjaxEnabled === '1';
            const minDisplayTime = Math.max(150, parseInt(preloader.dataset.duration || '650', 10) || 650);

            let hasExited = !autoRun;
            const startTime = performance.now();

            function exitPreloader() {
                if (hasExited) return;
                hasExited = true;

                const elapsed = performance.now() - startTime;
                const remaining = Math.max(0, minDisplayTime - elapsed);

                setTimeout(function () {
                    preloader.classList.add('unn-preloader-exit');
                    setTimeout(function () {
                        preloader.style.display = 'none';
                        if (window.UNNScrollReveal && typeof window.UNNScrollReveal.refresh === 'function') {
                            window.UNNScrollReveal.refresh();
                        }
                    }, 550);
                }, remaining);
            }

            if (autoRun) {
                if (document.readyState === 'complete') {
                    exitPreloader();
                } else {
                    window.addEventListener('load', exitPreloader);
                }
                setTimeout(exitPreloader, Math.max(2500, minDisplayTime + 1200));
            }

            if (autoRun && pjaxEnabled && typeof document.addEventListener === 'function') {
                document.addEventListener('pjax:send', function () {
                    preloader.classList.remove('unn-preloader-exit');
                    preloader.style.display = 'flex';
                });
                document.addEventListener('pjax:complete', function () {
                    setTimeout(function () {
                        preloader.classList.add('unn-preloader-exit');
                        setTimeout(function () { preloader.style.display = 'none'; }, 550);
                    }, 300);
                });
            }

            // API pública para testar ao vivo em /admin/settings/appearance e /painel/admin/settings/appearance
            window.UNNPreviewPreloader = function (opts) {
                opts = opts || {};
                const style = opts.style || 'orbital';
                const exitAnim = opts.exitAnimation || 'blur';
                const duration = Math.max(400, parseInt(opts.duration || '1200', 10) || 1200);
                const text = (opts.text || 'SOMOS UNN').trim() || 'SOMOS UNN';
                const accent = opts.accentColor || '#1F5EDB';
                const showProgress = opts.showProgress !== false;

                const prevClasses = preloader.className;
                const prevAccent = preloader.style.getPropertyValue('--unn-pre-accent');
                const labelEl = document.getElementById('unn-preloader-label');
                const progEl = document.getElementById('unn-preloader-progress-wrap');
                const prevText = labelEl ? labelEl.textContent : '';
                const prevProgHidden = progEl ? progEl.classList.contains('d-none') : false;

                preloader.className = 'unn-preloader-overlay unn-style-' + style + ' unn-exit-' + exitAnim;
                preloader.style.setProperty('--unn-pre-accent', accent);
                if (labelEl) labelEl.textContent = text;
                if (progEl) progEl.classList.toggle('d-none', !showProgress);

                preloader.classList.remove('unn-preloader-exit');
                preloader.style.display = 'flex';

                setTimeout(function () {
                    preloader.classList.add('unn-preloader-exit');
                    setTimeout(function () {
                        preloader.style.display = 'none';
                        preloader.className = prevClasses;
                        preloader.style.setProperty('--unn-pre-accent', prevAccent || '#1F5EDB');
                        if (labelEl) labelEl.textContent = prevText;
                        if (progEl) progEl.classList.toggle('d-none', prevProgHidden);
                    }, 560);
                }, duration);
            };
        })();
    </script>
@endif
