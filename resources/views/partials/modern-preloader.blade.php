{{-- ==========================================================================
     PRELOADER MODERNO DE ALTA TECNOLOGIA SOMOS UNN
     Anéis orbitais duplos em contra-rotação, núcleo pulsante com efeito de respiração,
     glow ambiente, barra de progresso linear futurista e saída cinematográfica com blur.
     ========================================================================== --}}
@php
    $preloaderText = trim((string) \App\Models\Setting::get('preloader_text', 'SOMOS UNN'));
    if ($preloaderText === '') {
        $preloaderText = 'SOMOS UNN';
    }
@endphp

@if($preloaderEnabled)
    <div id="unn-preloader" class="unn-preloader-overlay" aria-hidden="true" role="status">
        {{-- Glow ambiente difuso nas cores da marca UNN --}}
        <div class="unn-preloader-glow"></div>

        <div class="unn-preloader-content">
            {{-- Núcleo Orbital Tecnológico --}}
            <div class="unn-orbital-wrapper">
                {{-- Anel Externo com Gradiente Cônico e Rotação Suave --}}
                <div class="unn-ring-outer"></div>
                {{-- Anel Intermediário Pontilhado em Contra-Rotação --}}
                <div class="unn-ring-reverse"></div>
                {{-- Pulso de Onda Radial Expansiva --}}
                <div class="unn-ring-pulse"></div>

                {{-- Moldura Central com Efeito de Respiração e Logo Oficial --}}
                <div class="unn-logo-container">
                    <img src="{{ $preloaderImage }}" alt="SOMOS UNN" class="unn-logo-img"
                        onerror="this.onerror=null;this.src='{{ asset('img/logo.svg') }}';">
                </div>
            </div>

            {{-- Tipografia com Efeito Shimmer Gradiente Metálico --}}
            <div class="unn-brand-text-wrapper">
                <span class="unn-brand-text">{{ $preloaderText }}</span>
                <span class="unn-dots">
                    <span class="unn-dot"></span>
                    <span class="unn-dot"></span>
                    <span class="unn-dot"></span>
                </span>
            </div>

            {{-- Barra de Progresso Linear Futurista --}}
            <div class="unn-progress-container">
                <div class="unn-progress-bar">
                    <div class="unn-progress-shimmer"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ==========================================================================
           ESTILOS DO PRELOADER MODERNO SOMOS UNN
           ========================================================================== */
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

        .dark #unn-preloader {
            background: #090d16;
        }

        /* Transição de Saída Cinematográfica (Expansão Suave + Blur + Fade Out) */
        #unn-preloader.unn-preloader-exit {
            opacity: 0 !important;
            transform: scale(1.05) !important;
            filter: blur(8px) !important;
            pointer-events: none !important;
            visibility: hidden !important;
        }

        /* Glow Ambiente Difuso Central */
        .unn-preloader-glow {
            position: absolute;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(31, 94, 219, 0.20) 0%, rgba(41, 171, 226, 0.08) 45%, transparent 70%);
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
        }

        /* Núcleo Orbital */
        .unn-orbital-wrapper {
            position: relative;
            width: 124px;
            height: 124px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
        }

        /* Anel Externo Conic */
        .unn-ring-outer {
            position: absolute;
            inset: -12px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, transparent 0%, #1F5EDB 40%, #29ABE2 80%, transparent 100%);
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #fff calc(100% - 2.5px));
            mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #fff calc(100% - 2.5px));
            animation: unn-spin-clockwise 1.3s linear infinite;
            filter: drop-shadow(0 0 6px rgba(31, 94, 219, 0.5));
        }

        /* Anel Intermediário Reverso */
        .unn-ring-reverse {
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            border: 1.5px dashed rgba(23, 127, 214, 0.45);
            animation: unn-spin-counter 3.5s linear infinite;
        }

        /* Anel de Pulso / Ripple */
        .unn-ring-pulse {
            position: absolute;
            inset: -20px;
            border-radius: 50%;
            border: 1px solid rgba(41, 171, 226, 0.35);
            animation: unn-ripple 2s cubic-bezier(0.16, 1, 0.3, 1) infinite;
            opacity: 0;
        }

        @keyframes unn-spin-clockwise {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes unn-spin-counter {
            from { transform: rotate(360deg); }
            to { transform: rotate(0deg); }
        }

        @keyframes unn-ripple {
            0% { transform: scale(0.85); opacity: 0.8; }
            100% { transform: scale(1.35); opacity: 0; }
        }

        /* Logo Central */
        .unn-logo-container {
            position: relative;
            z-index: 5;
            width: 78px;
            height: 78px;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(31, 94, 219, 0.2), 0 0 0 1px rgba(226, 232, 240, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 11px;
            animation: unn-logo-breathe 2.4s ease-in-out infinite;
        }

        .dark .unn-logo-container {
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
            50% { transform: scale(1.06); filter: drop-shadow(0 6px 14px rgba(31, 94, 219, 0.3)); }
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
            background: linear-gradient(90deg, #1F5EDB 0%, #29ABE2 50%, #1F5EDB 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: unn-shimmer-text 2.4s linear infinite;
        }

        @keyframes unn-shimmer-text {
            to { background-position: 200% center; }
        }

        /* Micro-Pontos */
        .unn-dots {
            display: inline-flex;
            align-items: center;
            gap: 3.5px;
        }

        .unn-dot {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background-color: #177FD6;
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

        .dark .unn-progress-container {
            background: #1e293b;
        }

        .unn-progress-bar {
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, #1F5EDB 0%, #177FD6 50%, #29ABE2 100%);
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

        @media (prefers-reduced-motion: reduce) {
            #unn-preloader { display: none !important; }
        }
    </style>

    <script>
        (function () {
            const preloader = document.getElementById('unn-preloader');
            if (!preloader) return;

            let hasExited = false;
            const startTime = performance.now();
            const minDisplayTime = 420; // Tempo agradável para percepção de marca sem lentidão

            function exitPreloader() {
                if (hasExited) return;
                hasExited = true;

                const elapsed = performance.now() - startTime;
                const remaining = Math.max(0, minDisplayTime - elapsed);

                setTimeout(function () {
                    preloader.classList.add('unn-preloader-exit');

                    setTimeout(function () {
                        if (preloader.parentNode) {
                            preloader.parentNode.removeChild(preloader);
                        }
                        // Dispara atualização do Scroll Reveal Global logo após a saída suave
                        if (window.UNNScrollReveal && typeof window.UNNScrollReveal.refresh === 'function') {
                            window.UNNScrollReveal.refresh();
                        }
                    }, 550);
                }, remaining);
            }

            if (document.readyState === 'complete') {
                exitPreloader();
            } else {
                window.addEventListener('load', exitPreloader);
            }

            // Trava de segurança máxima de 2.2s para nunca travar a navegação
            setTimeout(exitPreloader, 2200);
        })();
    </script>
@endif
