<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $maintenanceTitle }}</title>
    @php
        $appName = \App\Models\Setting::get('app_name') ?: config('app.name', 'UNN');
        $logoUrl = \App\Models\Setting::getUrl('logo_front') ?: \App\Models\Setting::getUrl('logo_image') ?: asset('img/logo.svg');
        $faviconUrl = \App\Models\Setting::getUrl('favicon_image') ?: asset('favicon.ico');
        $primaryColor = (string) (\App\Models\Setting::get('site_color_primary') ?: '#1F5EDB');
        $secondaryColor = (string) (\App\Models\Setting::get('site_color_secondary') ?: '#177FD6');
        $returnAtIso = $maintenanceReturnAt ? $maintenanceReturnAt->toIso8601String() : '';
    @endphp

    <link rel="icon" href="{{ $faviconUrl }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --maintenance-primary: {{ $primaryColor }};
            --maintenance-secondary: {{ $secondaryColor }};
            --maintenance-bg: #031130;
            --maintenance-bg-soft: #081c49;
            --maintenance-card: #0a1b45;
            --maintenance-card-soft: #11265e;
            --maintenance-text: #f8fafc;
            --maintenance-muted: #bfd1ee;
            --maintenance-line: rgba(148, 163, 184, 0.24);
        }

        * {
            box-sizing: border-box;
            font-family: 'Manrope', sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--maintenance-text);
            background:
                radial-gradient(circle at 10% 10%, rgba(31, 94, 219, 0.32), transparent 38%),
                radial-gradient(circle at 88% 82%, rgba(23, 127, 214, 0.28), transparent 42%),
                linear-gradient(140deg, #020617 0%, var(--maintenance-bg) 42%, #020b24 100%);
            display: grid;
            place-items: center;
            padding: 24px;
            overflow-x: hidden;
        }

        .maintenance-shell {
            width: min(1080px, 100%);
            border: 1px solid var(--maintenance-line);
            border-radius: 30px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(6, 18, 49, 0.97) 0%, rgba(4, 15, 42, 0.97) 100%);
            box-shadow: 0 32px 70px rgba(2, 6, 23, 0.52);
            display: grid;
            grid-template-columns: minmax(280px, 420px) minmax(0, 1fr);
            position: relative;
        }

        .maintenance-shell::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(120deg, rgba(255, 255, 255, 0.04), transparent 36%),
                linear-gradient(320deg, rgba(255, 255, 255, 0.03), transparent 42%);
            pointer-events: none;
        }

        .maintenance-visual {
            position: relative;
            isolation: isolate;
            padding: clamp(24px, 4vw, 40px);
            border-right: 1px solid var(--maintenance-line);
            background:
                radial-gradient(circle at 72% 22%, rgba(23, 127, 214, 0.22), transparent 52%),
                linear-gradient(170deg, rgba(17, 38, 94, 0.98), rgba(7, 24, 67, 0.98));
            display: grid;
            align-content: center;
            gap: 20px;
        }

        .maintenance-visual::after {
            content: "";
            position: absolute;
            inset: 18px;
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .maintenance-logo-stage {
            position: relative;
            width: clamp(200px, 28vw, 290px);
            aspect-ratio: 1 / 1;
            margin: 0 auto;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at 32% 30%, rgba(255, 255, 255, 0.28), transparent 52%),
                linear-gradient(135deg, rgba(31, 94, 219, 0.3), rgba(23, 127, 214, 0.24));
            box-shadow: 0 24px 44px rgba(3, 12, 33, 0.6);
            animation: logoFloat 4.5s ease-in-out infinite;
        }

        .maintenance-logo-stage::before,
        .maintenance-logo-stage::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            border: 1px solid rgba(191, 219, 254, 0.35);
            inset: -12px;
            animation: ringPulse 3.8s ease-out infinite;
        }

        .maintenance-logo-stage::after {
            inset: -26px;
            border-color: rgba(125, 211, 252, 0.22);
            animation-delay: 1.6s;
        }

        .maintenance-logo {
            width: 78%;
            height: 78%;
            object-fit: contain;
            filter: drop-shadow(0 10px 22px rgba(2, 6, 23, 0.5));
        }

        .maintenance-visual-title {
            margin: 0;
            font-family: 'Sora', sans-serif;
            font-size: clamp(1.1rem, 2.3vw, 1.45rem);
            line-height: 1.45;
            font-weight: 700;
            color: #e7f0ff;
            text-align: center;
        }

        .maintenance-visual-caption {
            margin: 0;
            font-size: 0.86rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(191, 219, 254, 0.9);
            font-weight: 800;
            text-align: center;
        }

        .maintenance-pulse-track {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .maintenance-pulse-track span {
            display: block;
            height: 6px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(148, 163, 184, 0.22), rgba(191, 219, 254, 0.72));
            animation: pulseRail 1.6s ease-in-out infinite;
        }

        .maintenance-pulse-track span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .maintenance-pulse-track span:nth-child(3) {
            animation-delay: 0.4s;
        }

        .maintenance-content {
            padding: clamp(26px, 5vw, 50px);
            display: grid;
            gap: 18px;
        }

        .maintenance-brand {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px 14px;
        }

        .maintenance-status-pill {
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 800;
            color: #dbeafe;
            border: 1px solid rgba(191, 219, 254, 0.25);
            background: rgba(15, 23, 42, 0.5);
        }

        .maintenance-app {
            margin: 0;
            font-size: 0.92rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 800;
            color: rgba(241, 245, 249, 0.9);
        }

        .maintenance-kicker {
            margin: 0;
            font-size: 0.8rem;
            color: rgba(203, 213, 225, 0.88);
        }

        .maintenance-title {
            margin: 8px 0 0;
            font-family: 'Sora', sans-serif;
            font-size: clamp(2rem, 5vw, 3.4rem);
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .maintenance-subtitle {
            margin: 0;
            font-size: clamp(1rem, 2.4vw, 1.26rem);
            line-height: 1.55;
            color: #d5e7ff;
            font-weight: 700;
        }

        .maintenance-message {
            margin: 0;
            font-size: 1rem;
            line-height: 1.7;
            color: var(--maintenance-muted);
            max-width: 68ch;
        }

        .maintenance-highlights {
            list-style: none;
            margin: 2px 0 0;
            padding: 0;
            display: grid;
            gap: 8px;
        }

        .maintenance-highlights li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.92rem;
            color: #d7e4f9;
        }

        .maintenance-highlights li::before {
            content: "";
            flex: 0 0 8px;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(140deg, var(--maintenance-primary), var(--maintenance-secondary));
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.16);
        }

        .maintenance-return {
            border: 1px solid var(--maintenance-line);
            border-radius: 18px;
            background: linear-gradient(140deg, rgba(10, 27, 69, 0.92), rgba(8, 23, 60, 0.92));
            padding: 14px 16px;
            display: grid;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .maintenance-return::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: linear-gradient(180deg, var(--maintenance-primary), var(--maintenance-secondary));
        }

        .maintenance-return-label {
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(203, 213, 225, 0.85);
            font-weight: 700;
        }

        .maintenance-return-at {
            font-size: 1rem;
            font-weight: 700;
            color: #f8fbff;
        }

        .maintenance-return-countdown {
            font-size: 0.9rem;
            color: rgba(148, 163, 184, 0.95);
        }

        .maintenance-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 8px;
        }

        .maintenance-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 14px;
            padding: 12px 20px;
            font-size: 0.92rem;
            font-weight: 800;
            border: 1px solid transparent;
            transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
            cursor: pointer;
        }

        .maintenance-btn-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--maintenance-primary), var(--maintenance-secondary));
            box-shadow: 0 14px 30px rgba(31, 94, 219, 0.38);
        }

        .maintenance-btn-secondary {
            color: #e2e8f0;
            border-color: rgba(148, 163, 184, 0.34);
            background: rgba(15, 23, 42, 0.66);
        }

        .maintenance-btn:hover {
            transform: translateY(-2px);
            opacity: 0.96;
        }

        .maintenance-footer {
            margin-top: 8px;
            font-size: 0.84rem;
            color: rgba(148, 163, 184, 0.95);
        }

        .maintenance-footer a {
            color: #fff;
            font-weight: 700;
        }

        @keyframes logoFloat {
            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        @keyframes ringPulse {
            0% {
                opacity: 0.7;
                transform: scale(0.98);
            }

            70% {
                opacity: 0;
                transform: scale(1.08);
            }

            100% {
                opacity: 0;
                transform: scale(1.08);
            }
        }

        @keyframes pulseRail {
            0%,
            100% {
                opacity: 0.45;
                transform: scaleX(0.94);
            }

            50% {
                opacity: 1;
                transform: scaleX(1);
            }
        }

        @media (max-width: 980px) {
            .maintenance-shell {
                grid-template-columns: 1fr;
            }

            .maintenance-visual {
                border-right: 0;
                border-bottom: 1px solid var(--maintenance-line);
            }
        }

        @media (max-width: 680px) {
            .maintenance-actions {
                flex-direction: column;
            }

            .maintenance-btn {
                width: 100%;
            }

            .maintenance-content,
            .maintenance-visual {
                padding: 24px 20px;
            }

            .maintenance-logo-stage {
                width: min(250px, 82vw);
            }
        }
    </style>
</head>

<body>
    <main class="maintenance-shell" role="main" aria-live="polite">
        <aside class="maintenance-visual" aria-hidden="true">
            <div class="maintenance-logo-stage">
                <img src="{{ $logoUrl }}" alt="" class="maintenance-logo">
            </div>
            <p class="maintenance-visual-caption">Somos UNN</p>
            <p class="maintenance-visual-title">Estamos aprimorando a plataforma para entregar uma experiencia ainda melhor.</p>
            <div class="maintenance-pulse-track">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </aside>

        <section class="maintenance-content">
            <header class="maintenance-brand">
                <span class="maintenance-status-pill">Modo de manutencao</span>
                <div>
                    <p class="maintenance-app">{{ $appName }}</p>
                    <p class="maintenance-kicker">Status do sistema</p>
                </div>
            </header>

            <h1 class="maintenance-title">{{ $maintenanceTitle }}</h1>
            <p class="maintenance-subtitle">{{ $maintenanceSubtitle }}</p>
            <p class="maintenance-message">{{ $maintenanceMessage }}</p>
            <ul class="maintenance-highlights">
                <li>Ajustes de performance e estabilidade</li>
                <li>Melhorias visuais e de navegacao</li>
                <li>Camadas extras de seguranca na plataforma</li>
            </ul>

            @if($maintenanceReturnAt)
                <article class="maintenance-return" data-maintenance-return-at="{{ $returnAtIso }}">
                    <span class="maintenance-return-label">Previsao de retorno</span>
                    <strong class="maintenance-return-at">{{ $maintenanceReturnAt->format('d/m/Y H:i') }}</strong>
                    <span class="maintenance-return-countdown" data-maintenance-countdown>Atualizando horario...</span>
                </article>
            @endif

            <div class="maintenance-actions">
                @if(trim((string) $maintenanceButtonUrl) !== '')
                    <a href="{{ $maintenanceButtonUrl }}" class="maintenance-btn maintenance-btn-primary">
                        {{ $maintenanceButtonLabel }}
                    </a>
                @endif

                <button type="button" class="maintenance-btn maintenance-btn-secondary" onclick="window.location.reload();">
                    Atualizar pagina
                </button>
            </div>

            @if(trim((string) $maintenanceContactEmail) !== '')
                <p class="maintenance-footer">
                    Suporte: <a href="mailto:{{ $maintenanceContactEmail }}">{{ $maintenanceContactEmail }}</a>
                </p>
            @endif
        </section>
    </main>

    <script>
        (function() {
            const box = document.querySelector('[data-maintenance-return-at]');
            const countdown = document.querySelector('[data-maintenance-countdown]');
            if (!box || !countdown) return;

            const returnAtRaw = box.getAttribute('data-maintenance-return-at');
            const returnAt = returnAtRaw ? new Date(returnAtRaw) : null;
            if (!returnAt || Number.isNaN(returnAt.getTime())) {
                countdown.textContent = '';
                return;
            }

            const formatRemaining = function(totalSeconds) {
                const days = Math.floor(totalSeconds / 86400);
                const hours = Math.floor((totalSeconds % 86400) / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                const chunks = [];
                if (days > 0) chunks.push(days + 'd');
                if (hours > 0 || days > 0) chunks.push(hours + 'h');
                if (minutes > 0 || hours > 0 || days > 0) chunks.push(minutes + 'm');
                chunks.push(seconds + 's');

                return chunks.join(' ');
            };

            const tick = function() {
                const diff = Math.floor((returnAt.getTime() - Date.now()) / 1000);
                if (diff <= 0) {
                    countdown.textContent = 'Estamos finalizando os ultimos ajustes.';
                    return;
                }

                countdown.textContent = 'Retorno estimado em ' + formatRemaining(diff) + '.';
            };

            tick();
            setInterval(tick, 1000);
        })();
    </script>
</body>

</html>
