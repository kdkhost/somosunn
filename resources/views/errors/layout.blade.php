@extends('layouts.app', ['showPartnersCarousel' => false])

@php
    $statusCode = trim($__env->yieldContent('error_code', (string) ($code ?? 'Erro')));
    $errorHeading = trim($__env->yieldContent('error_heading', 'Algo inesperado aconteceu.'));
    $errorMessage = trim($__env->yieldContent('error_message', 'Nao foi possivel concluir esta solicitacao neste momento.'));
    $errorHint = trim($__env->yieldContent('error_hint', 'Tente novamente em alguns instantes.'));

    $primaryLabel = trim($__env->yieldContent('error_primary_label', 'Ir para a pagina inicial'));
    $primaryUrl = trim($__env->yieldContent('error_primary_url', route('home')));

    $previousUrl = url()->previous();
    if (!$previousUrl || $previousUrl === url()->current()) {
        $previousUrl = route('home');
    }

    $secondaryLabel = trim($__env->yieldContent('error_secondary_label', 'Voltar para a pagina anterior'));
    $secondaryUrl = trim($__env->yieldContent('error_secondary_url', (string) $previousUrl));

    $appName = (string) (\App\Models\Setting::get('app_name') ?: config('app.name', 'UNN'));
    $logoUrl = \App\Models\Setting::getUrl('logo_front') ?: \App\Models\Setting::getUrl('logo_image') ?: asset('img/logo.svg');
    $supportEmail = (string) (\App\Models\Setting::get('company_email') ?: \App\Models\Setting::get('smtp_from_email') ?: 'contato@somosunn.com.br');

    $requestPath = (string) request()->path();
    $requestPath = $requestPath === '/' ? '/' : '/' . ltrim($requestPath, '/');
    $capturedAt = now()->format('d/m/Y H:i');
@endphp

@section('title', $statusCode . ' - ' . $errorHeading)

@push('styles')
    <style>
        .error-page {
            position: relative;
            padding: clamp(24px, 5vw, 68px) 0;
        }

        .error-page::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 12% 14%, rgba(31, 94, 219, 0.20) 0%, rgba(31, 94, 219, 0) 42%),
                radial-gradient(circle at 86% 84%, rgba(23, 127, 214, 0.18) 0%, rgba(23, 127, 214, 0) 44%);
        }

        .error-shell {
            position: relative;
            z-index: 1;
        }

        .error-card {
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 28px 60px rgba(2, 6, 23, 0.28);
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr);
        }

        .error-main {
            padding: clamp(22px, 4vw, 42px);
        }

        .error-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 7px 14px;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #ffffff;
            background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-2), var(--unn-azul-3));
            box-shadow: 0 12px 26px rgba(31, 94, 219, 0.35);
        }

        .error-code {
            margin: 14px 0 10px;
            font-size: clamp(3.2rem, 8vw, 6.1rem);
            line-height: .92;
            letter-spacing: .02em;
            font-weight: 900;
            background: linear-gradient(120deg, var(--unn-azul-1), var(--unn-azul-2), var(--unn-azul-3));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .error-title {
            margin: 0;
            font-size: clamp(1.35rem, 3.2vw, 2rem);
            line-height: 1.15;
            font-weight: 800;
            color: #0f172a;
        }

        .error-message {
            margin: 14px 0 0;
            font-size: 1rem;
            line-height: 1.75;
            color: #334155;
            max-width: 64ch;
        }

        .error-hint {
            margin: 10px 0 0;
            font-size: .93rem;
            line-height: 1.65;
            color: #1d4ed8;
            font-weight: 600;
        }

        .error-actions {
            margin-top: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .error-btn-secondary,
        .error-btn-ghost {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 11px 18px;
            border-radius: 12px;
            font-size: .88rem;
            font-weight: 700;
            text-decoration: none;
            transition: all .18s ease;
            border: 1px solid transparent;
            cursor: pointer;
            white-space: nowrap;
        }

        .error-btn-secondary {
            color: #0f172a;
            background: #ffffff;
            border-color: rgba(15, 23, 42, 0.14);
        }

        .error-btn-secondary:hover {
            border-color: rgba(31, 94, 219, 0.35);
            color: var(--unn-azul-1);
            transform: translateY(-1px);
        }

        .error-btn-ghost {
            color: var(--unn-azul-1);
            background: rgba(31, 94, 219, 0.08);
            border-color: rgba(31, 94, 219, 0.24);
        }

        .error-btn-ghost:hover {
            background: rgba(31, 94, 219, 0.14);
            transform: translateY(-1px);
        }

        .error-side {
            padding: clamp(22px, 3.6vw, 34px);
            color: #ffffff;
            background:
                linear-gradient(145deg, rgba(3, 20, 63, 0.92) 0%, rgba(14, 59, 145, 0.92) 50%, rgba(23, 127, 214, 0.92) 100%);
            position: relative;
            overflow: hidden;
        }

        .error-side::after {
            content: '';
            position: absolute;
            inset: -80px;
            pointer-events: none;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.10) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.10) 1px, transparent 1px);
            background-size: 34px 34px;
            opacity: .28;
        }

        .error-side > * {
            position: relative;
            z-index: 1;
        }

        .error-side-brand {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 16px;
        }

        .error-side-logo {
            display: inline-flex;
            height: 52px;
            width: auto;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .error-side-logo img {
            height: 100%;
            width: auto;
            object-fit: contain;
        }

        .error-side-subtitle {
            margin: 0;
            font-size: .84rem;
            line-height: 1.5;
            opacity: .82;
        }

        .error-metas {
            margin-top: 14px;
            display: grid;
            gap: 10px;
        }

        .error-meta {
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.24);
            background: rgba(255, 255, 255, 0.08);
            padding: 11px 12px;
        }

        .error-meta-label {
            display: block;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            opacity: .78;
            margin-bottom: 3px;
            font-weight: 700;
        }

        .error-meta-value {
            display: block;
            font-size: .89rem;
            font-weight: 700;
            word-break: break-word;
        }

        .error-support {
            margin: 14px 0 0;
            font-size: .82rem;
            line-height: 1.6;
            opacity: .94;
        }

        .error-support a {
            color: #ffffff;
            font-weight: 700;
            text-decoration: underline;
        }

        @media (max-width: 960px) {
            .error-card {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <section class="error-page">
        <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 error-shell">
            <article class="error-card" role="alert" aria-live="polite">
                <div class="error-main">
                    <span class="error-badge">
                        <i class="fas fa-triangle-exclamation"></i>
                        Erro {{ $statusCode }}
                    </span>

                    <h1 class="error-code">{{ $statusCode }}</h1>
                    <h2 class="error-title">{{ $errorHeading }}</h2>
                    <p class="error-message">{{ $errorMessage }}</p>
                    <p class="error-hint">{{ $errorHint }}</p>

                    <div class="error-actions">
                        <a class="btn-primary px-6 py-3 rounded-xl inline-flex items-center gap-2 font-bold shadow-lg shadow-blue-600/30"
                            href="{{ $primaryUrl }}">
                            <i class="fas fa-arrow-right"></i>
                            {{ $primaryLabel }}
                        </a>

                        @if($secondaryUrl !== '')
                            <a class="error-btn-secondary" href="{{ $secondaryUrl }}">
                                <i class="fas fa-arrow-left"></i>
                                {{ $secondaryLabel }}
                            </a>
                        @endif

                        <button type="button" class="error-btn-ghost" onclick="window.location.reload();">
                            <i class="fas fa-rotate-right"></i>
                            Atualizar pagina
                        </button>
                    </div>
                </div>

                <aside class="error-side">
                    <div class="error-side-brand">
                        <div class="error-side-logo">
                            <img src="{{ $logoUrl }}" alt="" onerror="this.style.display='none';">
                        </div>
                        <p class="error-side-subtitle">Central de status do sistema</p>
                    </div>

                    <div class="error-metas">
                        <div class="error-meta">
                            <span class="error-meta-label">Pagina solicitada</span>
                            <span class="error-meta-value">{{ $requestPath }}</span>
                        </div>
                        <div class="error-meta">
                            <span class="error-meta-label">Horario</span>
                            <span class="error-meta-value">{{ $capturedAt }}</span>
                        </div>
                        <div class="error-meta">
                            <span class="error-meta-label">Codigo</span>
                            <span class="error-meta-value">{{ $statusCode }}</span>
                        </div>
                    </div>

                    <p class="error-support">
                        Precisa de ajuda? Fale com o suporte em
                        <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.
                    </p>
                </aside>
            </article>
        </div>
    </section>
@endsection
