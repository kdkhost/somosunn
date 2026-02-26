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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --maintenance-primary: {{ $primaryColor }};
            --maintenance-secondary: {{ $secondaryColor }};
            --maintenance-bg: #050b1e;
            --maintenance-card: #0c1737;
            --maintenance-text: #f8fafc;
            --maintenance-muted: #cbd5e1;
        }

        * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--maintenance-text);
            background:
                radial-gradient(circle at 15% 15%, rgba(31, 94, 219, 0.35), transparent 42%),
                radial-gradient(circle at 85% 85%, rgba(23, 127, 214, 0.3), transparent 46%),
                linear-gradient(145deg, #020617 0%, var(--maintenance-bg) 100%);
            display: grid;
            place-items: center;
            padding: 20px;
        }

        .maintenance-shell {
            width: min(900px, 100%);
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 28px;
            overflow: hidden;
            background: linear-gradient(140deg, rgba(12, 23, 55, 0.96) 0%, rgba(7, 18, 45, 0.96) 100%);
            box-shadow: 0 32px 60px rgba(2, 6, 23, 0.45);
        }

        .maintenance-content {
            padding: clamp(24px, 5vw, 48px);
            display: grid;
            gap: 20px;
        }

        .maintenance-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .maintenance-brand img {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.06);
            padding: 8px;
        }

        .maintenance-app {
            margin: 0;
            font-size: 0.92rem;
            letter-spacing: 0.08em;
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
            font-size: clamp(1.8rem, 4.8vw, 3rem);
            line-height: 1.06;
            font-weight: 900;
            letter-spacing: -0.02em;
        }

        .maintenance-subtitle {
            margin: 0;
            font-size: clamp(1rem, 2.6vw, 1.3rem);
            line-height: 1.5;
            color: #c7dcff;
            font-weight: 700;
        }

        .maintenance-message {
            margin: 0;
            font-size: 1rem;
            line-height: 1.75;
            color: var(--maintenance-muted);
            max-width: 68ch;
        }

        .maintenance-return {
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px;
            background: rgba(15, 23, 42, 0.62);
            padding: 14px 16px;
            display: grid;
            gap: 6px;
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
        }

        .maintenance-return-countdown {
            font-size: 0.9rem;
            color: rgba(148, 163, 184, 0.95);
        }

        .maintenance-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 4px;
        }

        .maintenance-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 12px;
            padding: 12px 18px;
            font-size: 0.92rem;
            font-weight: 800;
            border: 1px solid transparent;
            transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
        }

        .maintenance-btn-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--maintenance-primary), var(--maintenance-secondary));
            box-shadow: 0 14px 28px rgba(31, 94, 219, 0.35);
        }

        .maintenance-btn-secondary {
            color: #e2e8f0;
            border-color: rgba(148, 163, 184, 0.28);
            background: rgba(15, 23, 42, 0.5);
        }

        .maintenance-btn:hover {
            transform: translateY(-1px);
            opacity: 0.95;
        }

        .maintenance-footer {
            margin-top: 4px;
            font-size: 0.84rem;
            color: rgba(148, 163, 184, 0.95);
        }

        .maintenance-footer a {
            color: #fff;
            font-weight: 700;
        }

        @media (max-width: 680px) {
            .maintenance-actions {
                flex-direction: column;
            }

            .maintenance-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <main class="maintenance-shell" role="main" aria-live="polite">
        <section class="maintenance-content">
            <header class="maintenance-brand">
                <img src="{{ $logoUrl }}" alt="Logo {{ $appName }}">
                <div>
                    <p class="maintenance-app">{{ $appName }}</p>
                    <p class="maintenance-kicker">Status do sistema</p>
                </div>
            </header>

            <h1 class="maintenance-title">{{ $maintenanceTitle }}</h1>
            <p class="maintenance-subtitle">{{ $maintenanceSubtitle }}</p>
            <p class="maintenance-message">{{ $maintenanceMessage }}</p>

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
