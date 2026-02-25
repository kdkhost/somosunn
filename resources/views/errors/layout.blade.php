@extends('layouts.app', ['showNavigation' => false, 'showFooter' => false])

@php
    $statusCode = trim($__env->yieldContent('error_code', (string) ($code ?? 'Erro')));
    $errorHeading = trim($__env->yieldContent('error_heading', 'Algo inesperado aconteceu.'));
    $errorMessage = trim($__env->yieldContent('error_message', 'Nao foi possivel concluir esta solicitacao neste momento.'));
    $errorHint = trim($__env->yieldContent('error_hint', 'Tente novamente em instantes.'));

    $primaryLabel = trim($__env->yieldContent('error_primary_label', 'Ir para a pagina inicial'));
    $primaryUrl = trim($__env->yieldContent('error_primary_url', route('home')));

    $previousUrl = url()->previous();
    if (!$previousUrl || $previousUrl === url()->current()) {
        $previousUrl = route('home');
    }

    $secondaryLabel = trim($__env->yieldContent('error_secondary_label', 'Voltar para a pagina anterior'));
    $secondaryUrl = trim($__env->yieldContent('error_secondary_url', (string) $previousUrl));

    $accent = trim($__env->yieldContent('error_accent', '#2563EB'));
    $accentSoft = trim($__env->yieldContent('error_accent_soft', '#06B6D4'));

    $appName = (string) (\App\Models\Setting::get('app_name') ?: config('app.name', 'UNN'));
    $logoUrl = \App\Models\Setting::getUrl('logo_front') ?: \App\Models\Setting::getUrl('logo_image') ?: asset('img/logo.svg');
    $supportEmail = (string) (\App\Models\Setting::get('company_email') ?: \App\Models\Setting::get('smtp_from_email') ?: 'contato@somosunn.com.br');

    $requestPath = (string) request()->path();
    $requestPath = $requestPath === '/' ? '/' : '/' . ltrim($requestPath, '/');
    $capturedAt = now()->format('d/m/Y H:i');
@endphp

@section('title', $statusCode . ' - ' . $errorHeading)

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap"
        rel="stylesheet">
    <style>
        .error-stage {
            --error-accent: {{ $accent }};
            --error-accent-soft: {{ $accentSoft }};
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            background:
                radial-gradient(circle at 12% 16%, color-mix(in srgb, var(--error-accent) 28%, #ffffff 72%) 0%, transparent 42%),
                radial-gradient(circle at 86% 82%, color-mix(in srgb, var(--error-accent-soft) 22%, #ffffff 78%) 0%, transparent 48%),
                linear-gradient(135deg, #f8fbff 0%, #edf3ff 48%, #fff7ee 100%);
            font-family: 'Manrope', sans-serif;
            color: #0f172a;
        }

        .dark .error-stage {
            background:
                radial-gradient(circle at 10% 8%, color-mix(in srgb, var(--error-accent) 30%, #020617 70%) 0%, transparent 45%),
                radial-gradient(circle at 84% 80%, color-mix(in srgb, var(--error-accent-soft) 28%, #020617 72%) 0%, transparent 52%),
                linear-gradient(135deg, #040b1c 0%, #071023 48%, #0b1529 100%);
            color: #e2e8f0;
        }

        .error-stage::before {
            content: '';
            position: absolute;
            inset: -120px;
            background-image:
                linear-gradient(to right, rgba(15, 23, 42, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(15, 23, 42, 0.05) 1px, transparent 1px);
            background-size: 42px 42px;
            opacity: .35;
            pointer-events: none;
        }

        .dark .error-stage::before {
            background-image:
                linear-gradient(to right, rgba(148, 163, 184, 0.1) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(148, 163, 184, 0.1) 1px, transparent 1px);
            opacity: .25;
        }

        .error-orb {
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            filter: blur(2px);
            animation: errorFloat 16s ease-in-out infinite;
        }

        .error-orb-a {
            width: 420px;
            height: 420px;
            left: -140px;
            top: -120px;
            background: radial-gradient(circle at center, color-mix(in srgb, var(--error-accent) 55%, transparent) 0%, transparent 68%);
        }

        .error-orb-b {
            width: 360px;
            height: 360px;
            right: -110px;
            bottom: -90px;
            background: radial-gradient(circle at center, color-mix(in srgb, var(--error-accent-soft) 48%, transparent) 0%, transparent 72%);
            animation-delay: -7s;
        }

        .error-shell {
            position: relative;
            z-index: 1;
            max-width: 1040px;
            margin: 0 auto;
            padding: 40px 20px 64px;
            display: grid;
            gap: 22px;
        }

        .error-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
            width: fit-content;
        }

        .error-brand img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(15, 23, 42, 0.08);
            padding: 8px;
        }

        .dark .error-brand img {
            background: rgba(2, 6, 23, .7);
            border-color: rgba(148, 163, 184, 0.22);
        }

        .error-brand-text {
            font-family: 'Space Grotesk', sans-serif;
            font-size: .84rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .85;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.68);
            border: 1px solid rgba(15, 23, 42, 0.12);
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.14);
            border-radius: 28px;
            padding: clamp(22px, 5vw, 38px);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(0, .75fr);
            gap: clamp(18px, 4vw, 32px);
            animation: errorRise .75s cubic-bezier(.2, .8, .2, 1);
        }

        .dark .error-card {
            background: rgba(2, 6, 23, .66);
            border-color: rgba(148, 163, 184, 0.26);
            box-shadow: 0 26px 70px rgba(2, 6, 23, .6);
        }

        .error-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 8px;
        }

        .error-badge,
        .error-chip {
            border-radius: 999px;
            padding: 6px 14px;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .error-badge {
            color: #fff;
            background: linear-gradient(120deg, var(--error-accent), var(--error-accent-soft));
            box-shadow: 0 8px 18px color-mix(in srgb, var(--error-accent) 36%, transparent);
        }

        .error-chip {
            color: color-mix(in srgb, var(--error-accent) 72%, #0f172a 28%);
            background: color-mix(in srgb, var(--error-accent) 12%, #ffffff 88%);
            border: 1px solid color-mix(in srgb, var(--error-accent) 20%, transparent);
        }

        .dark .error-chip {
            color: color-mix(in srgb, var(--error-accent-soft) 70%, #e2e8f0 30%);
            background: color-mix(in srgb, var(--error-accent) 14%, #020617 86%);
        }

        .error-code {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(3rem, 9vw, 6.6rem);
            line-height: .9;
            font-weight: 700;
            margin: 4px 0 12px;
            letter-spacing: .02em;
            background: linear-gradient(128deg, var(--error-accent), var(--error-accent-soft));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .error-title {
            font-size: clamp(1.45rem, 3vw, 2rem);
            line-height: 1.15;
            font-weight: 800;
            margin: 0 0 10px;
            color: #0b1222;
        }

        .dark .error-title {
            color: #f8fafc;
        }

        .error-message {
            margin: 0;
            font-size: 1.04rem;
            line-height: 1.68;
            color: #334155;
            max-width: 58ch;
        }

        .dark .error-message {
            color: #cbd5e1;
        }

        .error-hint {
            margin-top: 10px;
            font-size: .94rem;
            line-height: 1.6;
            color: color-mix(in srgb, var(--error-accent) 56%, #1e293b 44%);
        }

        .dark .error-hint {
            color: color-mix(in srgb, var(--error-accent-soft) 58%, #cbd5e1 42%);
        }

        .error-actions {
            margin-top: 22px;
            display: flex;
            flex-wrap: wrap;
            gap: 11px;
        }

        .error-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            border-radius: 14px;
            min-height: 44px;
            padding: 10px 18px;
            font-weight: 800;
            font-size: .9rem;
            letter-spacing: .01em;
            border: 1px solid transparent;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
            cursor: pointer;
        }

        .error-btn:hover {
            transform: translateY(-1px);
        }

        .error-btn-primary {
            color: #fff;
            background: linear-gradient(130deg, var(--error-accent), var(--error-accent-soft));
            box-shadow: 0 12px 26px color-mix(in srgb, var(--error-accent) 30%, transparent);
        }

        .error-btn-secondary {
            color: #0f172a;
            background: rgba(255, 255, 255, 0.86);
            border-color: rgba(15, 23, 42, 0.15);
        }

        .dark .error-btn-secondary {
            color: #e2e8f0;
            background: rgba(15, 23, 42, 0.78);
            border-color: rgba(148, 163, 184, 0.28);
        }

        .error-btn-ghost {
            color: color-mix(in srgb, var(--error-accent) 70%, #0f172a 30%);
            background: color-mix(in srgb, var(--error-accent) 8%, #ffffff 92%);
            border-color: color-mix(in srgb, var(--error-accent) 22%, transparent);
        }

        .dark .error-btn-ghost {
            color: color-mix(in srgb, var(--error-accent-soft) 60%, #e2e8f0 40%);
            background: color-mix(in srgb, var(--error-accent) 10%, #020617 90%);
        }

        .error-panel {
            border-radius: 20px;
            border: 1px solid rgba(15, 23, 42, 0.12);
            background: rgba(255, 255, 255, 0.7);
            padding: 18px;
            display: grid;
            gap: 14px;
        }

        .dark .error-panel {
            border-color: rgba(148, 163, 184, 0.24);
            background: rgba(7, 16, 35, 0.82);
        }

        .error-panel h2 {
            margin: 0;
            font-family: 'Space Grotesk', sans-serif;
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #0f172a;
        }

        .dark .error-panel h2 {
            color: #f8fafc;
        }

        .error-meta {
            display: grid;
            gap: 10px;
        }

        .error-meta-item {
            border-radius: 14px;
            border: 1px solid rgba(15, 23, 42, 0.1);
            background: rgba(255, 255, 255, 0.8);
            padding: 12px 13px;
            display: grid;
            gap: 4px;
        }

        .dark .error-meta-item {
            border-color: rgba(148, 163, 184, 0.22);
            background: rgba(2, 6, 23, 0.75);
        }

        .error-meta-label {
            font-size: .72rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
        }

        .dark .error-meta-label {
            color: #94a3b8;
        }

        .error-meta-value {
            font-size: .9rem;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
        }

        .dark .error-meta-value {
            color: #e2e8f0;
        }

        .error-support {
            margin-top: 6px;
            font-size: .83rem;
            line-height: 1.6;
            color: #475569;
        }

        .error-support a {
            color: color-mix(in srgb, var(--error-accent) 72%, #1e293b 28%);
            font-weight: 700;
            text-decoration: none;
        }

        .dark .error-support {
            color: #9fb0c9;
        }

        .dark .error-support a {
            color: color-mix(in srgb, var(--error-accent-soft) 66%, #f8fafc 34%);
        }

        @keyframes errorRise {
            from {
                opacity: 0;
                transform: translateY(18px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes errorFloat {
            0%,
            100% {
                transform: translateY(0) translateX(0);
            }

            50% {
                transform: translateY(-14px) translateX(8px);
            }
        }

        @media (max-width: 900px) {
            .error-card {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <section class="error-stage">
        <div class="error-orb error-orb-a"></div>
        <div class="error-orb error-orb-b"></div>

        <div class="error-shell">
            <a href="{{ route('home') }}" class="error-brand" aria-label="Voltar para a home">
                <img src="{{ $logoUrl }}" alt="Logo {{ $appName }}">
                <span class="error-brand-text">{{ $appName }}</span>
            </a>

            <article class="error-card" role="alert" aria-live="polite">
                <div>
                    <div class="error-head">
                        <span class="error-badge">Erro {{ $statusCode }}</span>
                        <span class="error-chip">Status Center</span>
                    </div>

                    <div class="error-code">{{ $statusCode }}</div>

                    <h1 class="error-title">{{ $errorHeading }}</h1>
                    <p class="error-message">{{ $errorMessage }}</p>
                    <p class="error-hint">{{ $errorHint }}</p>

                    <div class="error-actions">
                        <a class="error-btn error-btn-primary" href="{{ $primaryUrl }}">{{ $primaryLabel }}</a>
                        @if($secondaryUrl !== '')
                            <a class="error-btn error-btn-secondary" href="{{ $secondaryUrl }}">{{ $secondaryLabel }}</a>
                        @endif
                        <button type="button" class="error-btn error-btn-ghost" onclick="window.location.reload();">Atualizar pagina</button>
                    </div>
                </div>

                <aside class="error-panel">
                    <h2>Detalhes rapidos</h2>

                    <div class="error-meta">
                        <div class="error-meta-item">
                            <span class="error-meta-label">Pagina solicitada</span>
                            <span class="error-meta-value">{{ $requestPath }}</span>
                        </div>
                        <div class="error-meta-item">
                            <span class="error-meta-label">Horario</span>
                            <span class="error-meta-value">{{ $capturedAt }}</span>
                        </div>
                        <div class="error-meta-item">
                            <span class="error-meta-label">Status</span>
                            <span class="error-meta-value">{{ $statusCode }}</span>
                        </div>
                    </div>

                    <p class="error-support">
                        Se voce precisar de ajuda, fale com o suporte em
                        <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.
                    </p>
                </aside>
            </article>
        </div>
    </section>
@endsection

