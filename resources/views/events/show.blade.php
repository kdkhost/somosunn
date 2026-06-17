@extends('layouts.app')

@section('title', $event->title . ' - Eventos UNN')

@php
    $_seoEventDesc = trim(strip_tags((string) ($event->description ?? $event->short_description ?? '')));
    $_seoEventDesc = $_seoEventDesc !== '' ? \Illuminate\Support\Str::limit($_seoEventDesc, 155) : ($event->title . ' - Confira todos os detalhes do evento e garanta sua vaga.');
    $_seoEventImg = $event->image_url ?? null;
@endphp
@section('meta_title', $event->title . ' | Eventos UNN')
@section('meta_description', $_seoEventDesc)
@if($_seoEventImg)
    @section('meta_image', $_seoEventImg)
@endif
@section('og_type', 'article')

@push('styles')
    <style>
        .event-show-premium {
            background:
                radial-gradient(circle at top left, rgba(31, 94, 219, 0.16), transparent 34rem),
                linear-gradient(180deg, #f8fafc 0%, #eef4ff 48%, #ffffff 100%);
        }

        .event-hero-premium {
            position: relative;
            isolation: isolate;
            min-height: auto;
            padding: 96px 24px 44px;
            overflow: hidden;
            background: #eef6ff;
        }

        .event-hero-premium::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -2;
            background:
                linear-gradient(90deg, rgba(248, 250, 252, 0.86) 0%, rgba(239, 246, 255, 0.72) 55%, rgba(219, 234, 254, 0.70) 100%),
                radial-gradient(circle at 74% 20%, rgba(23, 127, 214, 0.22), transparent 24rem),
                radial-gradient(circle at 18% 88%, rgba(16, 185, 129, 0.10), transparent 20rem);
        }

        .event-hero-bg {
            position: absolute;
            inset: -32px;
            z-index: -3;
            width: calc(100% + 64px);
            height: calc(100% + 64px);
            object-fit: cover;
            opacity: 0.48;
            transform: scale(1.05);
        }

        .event-shell {
            width: min(1180px, 100%);
            margin-inline: auto;
        }

        .event-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 390px);
            gap: 24px;
            align-items: start;
        }

        .event-copy-panel {
            max-width: 760px;
            color: #0f172a;
            padding: clamp(20px, 3vw, 28px);
            border: 1px solid rgba(191, 219, 254, 0.92);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 18px 46px rgba(15, 23, 42, 0.14);
            backdrop-filter: blur(16px);
        }

        .event-back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-height: 44px;
            padding: 10px 14px;
            border: 1px solid rgba(191, 219, 254, 0.92);
            border-radius: 999px;
            color: #1d4ed8;
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(14px);
            font-weight: 800;
            transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease;
        }

        .event-back-link:hover {
            color: #1e3a8a;
            background: #ffffff;
            transform: translateY(-1px);
        }

        .event-status-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 28px 0 18px;
        }

        .event-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 36px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .event-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #1d4ed8;
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            margin-bottom: 12px;
        }

        .event-title-premium {
            color: #0f172a;
            font-size: clamp(1.8rem, 3.25vw, 2.85rem);
            line-height: 1.10;
            font-weight: 900;
            letter-spacing: 0;
            max-width: 760px;
            text-wrap: balance;
            text-shadow: none;
        }

        .event-speaker-premium,
        .event-summary-premium {
            color: #334155;
            text-shadow: none;
        }

        .event-summary-premium {
            max-width: 720px;
            margin-top: 14px;
            font-size: clamp(0.95rem, 1.3vw, 1.05rem);
            line-height: 1.65;
        }

        .event-fact-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 22px;
        }

        .event-fact {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            min-height: 92px;
            padding: 14px;
            border: 1px solid #dbeafe;
            border-radius: 18px;
            background: #f8fbff;
            box-shadow: none;
        }

        .event-fact-icon {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            color: #ffffff;
            background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-2));
            box-shadow: 0 10px 22px rgba(31, 94, 219, 0.18);
        }

        .event-fact-label {
            color: #64748b;
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .event-fact-value {
            margin-top: 5px;
            color: #0f172a;
            font-size: 0.95rem;
            line-height: 1.45;
            font-weight: 850;
        }

        .event-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 18px;
        }

        .event-hero-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 46px;
            padding: 12px 18px;
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 900;
            white-space: nowrap;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .event-hero-action:hover {
            transform: translateY(-2px);
        }

        .event-hero-action-primary {
            color: #ffffff;
            background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-2));
            box-shadow: 0 14px 32px rgba(31, 94, 219, 0.22);
        }

        .event-hero-action-secondary {
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            background: #ffffff;
            backdrop-filter: blur(14px);
        }

        .event-action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 46px;
            width: 100%;
            padding: 12px 18px;
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 900;
            line-height: 1.1;
            white-space: nowrap;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .event-action-button:hover {
            transform: translateY(-1px);
        }

        .event-action-button-primary {
            color: #ffffff;
            background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-2));
            box-shadow: 0 12px 28px rgba(31, 94, 219, 0.22);
        }

        .event-action-button-secondary {
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
        }

        .event-action-button-muted {
            color: #475569;
            background: #e2e8f0;
        }

        .event-purchase-card {
            position: sticky;
            top: 96px;
            overflow: hidden;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 18px 46px rgba(15, 23, 42, 0.16);
            border: 1px solid #dbeafe;
        }

        .event-purchase-media {
            position: relative;
            min-height: 152px;
            background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-2));
        }

        .event-purchase-media img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .event-purchase-media::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(2, 6, 23, 0.56) 100%);
        }

        .event-card-ribbon {
            position: absolute;
            top: 14px;
            right: 14px;
            z-index: 10;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 34px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.94);
            color: #0f766e;
            font-size: 0.76rem;
            font-weight: 900;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(10px);
        }

        .event-batch-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #eef5ff;
            color: #1d4ed8;
            font-size: 0.76rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .event-price-premium {
            color: #071225;
            font-size: clamp(2rem, 3vw, 2.65rem);
            line-height: 0.95;
            font-weight: 950;
            letter-spacing: -0.05em;
        }

        .event-seat-box {
            border: 1px solid #dbeafe;
            border-radius: 18px;
            background: linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);
            padding: 14px;
        }

        .event-trust-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 8px;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            background: #f8fbff;
            color: #475569;
            font-size: 0.80rem;
            font-weight: 850;
        }

        .event-trust-row span {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 34px;
            flex: 1 1 0;
            min-width: max-content;
            padding: 0 10px;
            border-radius: 12px;
            background: #ffffff;
            color: #334155;
            white-space: nowrap;
        }

        .event-trust-row i {
            color: #1d4ed8;
        }

        .event-details-section {
            margin-top: 0;
            position: relative;
            z-index: 3;
        }

        .event-details-card {
            border: 1px solid #e2e8f0;
            border-radius: 34px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 26px 80px rgba(15, 23, 42, 0.10);
        }

        .event-details-card .editor-content {
            color: #334155;
        }

        .event-details-card .editor-content :is(p, li) {
            color: #334155;
            line-height: 1.85;
        }

        .event-details-card .editor-content :is(strong, b) {
            color: #0f172a;
        }

        .event-exhibitor-band {
            border: 1px solid #bfdbfe;
            border-radius: 26px;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(239, 246, 255, 0.96)),
                radial-gradient(circle at 92% 8%, rgba(23, 127, 214, 0.18), transparent 18rem);
            box-shadow: 0 20px 52px rgba(31, 94, 219, 0.12);
            overflow: hidden;
        }

        .event-exhibitor-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(260px, 330px);
            gap: 22px;
            align-items: center;
            padding: 24px;
        }

        .event-exhibitor-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            color: #ffffff;
            background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-2));
            box-shadow: 0 14px 34px rgba(31, 94, 219, 0.22);
        }

        .event-exhibitor-metric {
            border: 1px solid #dbeafe;
            border-radius: 18px;
            background: #ffffff;
            padding: 14px;
        }

        .event-exhibitor-media {
            aspect-ratio: 16 / 10;
            overflow: hidden;
            border-radius: 20px;
            background: #dbeafe;
        }

        .event-exhibitor-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @media (max-width: 1024px) {
            .event-hero-premium {
                padding-top: 92px;
                min-height: auto;
            }

            .event-hero-grid,
            .event-exhibitor-grid {
                grid-template-columns: 1fr;
            }

            .event-purchase-card {
                position: relative;
                top: auto;
            }
        }

        @media (max-width: 640px) {
            .event-hero-premium {
                padding: 84px 16px 34px;
            }

            .event-title-premium {
                font-size: clamp(1.65rem, 8.8vw, 2.25rem);
            }

            .event-action-button,
            .event-hero-action {
                font-size: 0.86rem;
                padding-inline: 14px;
            }

            .event-trust-row {
                gap: 6px;
                padding: 6px;
                font-size: 0.74rem;
            }

            .event-fact-grid,
            .event-trust-row {
                grid-template-columns: 1fr;
            }

            .event-fact {
                min-height: auto;
            }

            .event-hero-action {
                width: 100%;
            }

            .event-copy-panel {
                padding: 18px;
                border-radius: 22px;
            }

            .event-exhibitor-grid {
                padding: 18px;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $suppressFlashErrorToast = true;
        $isDemo = $event->is_demo ?? false;
        $startDate = is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at;
        $endDate = null;
        if ($event->end_at) {
            $endDate = is_string($event->end_at) ? \Carbon\Carbon::parse($event->end_at) : $event->end_at;
        }
        $eventColor = $event->color ?? '#1F5EDB';

        $hexToRgba = function (?string $hex, float $alpha): ?string {
            $hex = trim((string) $hex);
            if ($hex === '') {
                return null;
            }

            $alpha = max(0, min(1, $alpha));

            if (preg_match('/^#?[0-9a-fA-F]{3}$/', $hex)) {
                $hex = ltrim($hex, '#');
                $r = hexdec(str_repeat($hex[0], 2));
                $g = hexdec(str_repeat($hex[1], 2));
                $b = hexdec(str_repeat($hex[2], 2));
                return "rgba({$r},{$g},{$b},{$alpha})";
            }

            if (preg_match('/^#?[0-9a-fA-F]{6}$/', $hex)) {
                $hex = ltrim($hex, '#');
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                return "rgba({$r},{$g},{$b},{$alpha})";
            }

            return null;
        };

        $sitePrimary = \App\Models\Setting::get('site_color_primary') ?: '#1F5EDB';
        $siteSecondary = \App\Models\Setting::get('site_color_secondary') ?: '#1D3FC4';

        // Admin controls (Settings -> Aparência -> Eventos)
        $eventsHeroBlurPxRaw = \App\Models\Setting::get('events_hero_bg_blur_px');
        $eventsHeroBlurPx = is_numeric($eventsHeroBlurPxRaw) ? (int) $eventsHeroBlurPxRaw : 64;
        $eventsHeroBlurPx = max(0, min(140, $eventsHeroBlurPx));

        $eventsHeroFilmRaw = \App\Models\Setting::get('events_hero_film_strength_percent');
        $eventsHeroFilmPercent = is_numeric($eventsHeroFilmRaw) ? (int) $eventsHeroFilmRaw : 100;
        $eventsHeroFilmPercent = max(0, min(100, $eventsHeroFilmPercent));
        $eventsHeroFilmScale = $eventsHeroFilmPercent / 100;
        $filmAlpha = static function (float $base) use ($eventsHeroFilmScale): float {
            $value = $base * $eventsHeroFilmScale;
            return max(0.0, min(1.0, $value));
        };

        // Base background (subtle, always on)
        $sitePrimary14 = $hexToRgba($sitePrimary, 0.14) ?: 'rgba(31,94,219,0.14)';
        $siteSecondary08 = $hexToRgba($siteSecondary, 0.08) ?: 'rgba(29,63,196,0.08)';

        // Film (insulfilm) overlay scales by Admin slider
        $sitePrimary38 = $hexToRgba($sitePrimary, $filmAlpha(0.38)) ?: ('rgba(31,94,219,' . $filmAlpha(0.38) . ')');
        $sitePrimary30 = $hexToRgba($sitePrimary, $filmAlpha(0.30)) ?: ('rgba(31,94,219,' . $filmAlpha(0.30) . ')');
        $sitePrimary22 = $hexToRgba($sitePrimary, $filmAlpha(0.22)) ?: ('rgba(31,94,219,' . $filmAlpha(0.22) . ')');

        $siteSecondary28 = $hexToRgba($siteSecondary, $filmAlpha(0.28)) ?: ('rgba(29,63,196,' . $filmAlpha(0.28) . ')');
        $siteSecondary18 = $hexToRgba($siteSecondary, $filmAlpha(0.18)) ?: ('rgba(29,63,196,' . $filmAlpha(0.18) . ')');

        $eventImageUrl = $event->image_url;
        $mapQuery = urlencode($event->address);
        $confirmedSeats = $event->confirmed_seats;
        $remainingSeats = $event->remaining_seats;
        $now = now();
        $isClosed = false;
        if ($endDate) {
            $isClosed = $endDate->lt($now);
        } elseif ($startDate) {
            $isClosed = $startDate->lt($now->copy()->startOfDay());
        }
        $exhibitorStatus = $event->exhibitorSalesStatus();
        $showExhibitorOption = (bool) ($event->exhibitor_sales_enabled ?? false)
            && (bool) ($event->exhibitor_show_publicly ?? true)
            && $event->isEvent()
            && !in_array((string) ($exhibitorStatus['key'] ?? ''), ['inativo', 'sem_configuracao'], true);
        $canSellExhibitor = $showExhibitorOption && $event->canSellExhibitorArea();
        $exhibitorPrice = $event->currentExhibitorPriceFor();
        $exhibitorBatch = $event->currentExhibitorBatchLabelFor();
        $exhibitorRemaining = $event->remaining_exhibitor_slots;
        $heroSummarySource = preg_replace('/<\s*(br|\/p|\/div|\/li|\/h[1-6])\s*\/?>/i', ' ', (string) ($event->description ?? ''));
        $heroSummary = trim(preg_replace('/\s+/', ' ', strip_tags((string) $heroSummarySource)));
        $heroSummary = $heroSummary !== ''
            ? \Illuminate\Support\Str::limit($heroSummary, 230)
            : 'Um encontro pensado para conexoes relevantes, oportunidades reais e experiencias que aproximam pessoas e negocios.';
        $eventLocationLabel = trim((string) ($event->location ?: $event->address ?: 'Local a confirmar'));
        $eventAddressLabel = trim((string) ($event->address ?: $eventLocationLabel));
        $ticketPriceLabel = $event->current_price > 0
            ? 'R$ ' . number_format((float) $event->current_price, 2, ',', '.')
            : 'Gratuito';
        $exhibitorDescriptionSource = preg_replace('/<\s*(br|\/p|\/div|\/li|\/h[1-6])\s*\/?>/i', ' ', (string) ($event->exhibitor_description ?? ''));
        $rawExhibitorSummary = trim(preg_replace('/\s+/', ' ', strip_tags((string) $exhibitorDescriptionSource)));
        $descriptionPlainText = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($event->description ?? ''))));
        $descriptionMentionsExhibitor = \Illuminate\Support\Str::contains(
            \Illuminate\Support\Str::lower($descriptionPlainText),
            ['expositor', 'expositores']
        );
        $hasConfiguredExhibitorInfo = $rawExhibitorSummary !== '' || !empty($event->exhibitor_area_image);
        $showExhibitorPublicSection = $canSellExhibitor;
        $exhibitorDetectedPrice = null;
        if (!$exhibitorPrice && preg_match('/expositor.{0,90}R\$\s*([\d\.\,]+)/iu', $descriptionPlainText, $priceMatches)) {
            $exhibitorDetectedPrice = 'R$ ' . trim($priceMatches[1]);
        }
        $exhibitorPublicPriceLabel = $exhibitorPrice
            ? 'R$ ' . number_format((float) $exhibitorPrice, 2, ',', '.')
            : ($exhibitorDetectedPrice ?: 'Sob consulta');
        $exhibitorPublicBatchLabel = $showExhibitorOption ? ($exhibitorBatch ?: 'A confirmar') : 'Informativo';
        $exhibitorPublicAvailabilityLabel = $showExhibitorOption
            ? ($canSellExhibitor ? ((int) $exhibitorRemaining . ' restante(s)') : ($exhibitorStatus['label'] ?? 'Indisponivel'))
            : 'Consulte a organizacao';
        $exhibitorSummary = $rawExhibitorSummary;
        $exhibitorSummary = $exhibitorSummary !== ''
            ? \Illuminate\Support\Str::limit($exhibitorSummary, 220)
            : 'Espaco comercial para marcas, empresas e profissionais apresentarem produtos, servicos e conexoes durante o evento.';
        $startDateLabel = $startDate ? $startDate->translatedFormat('d/m/Y (l)') : 'Data a confirmar';
        $startTimeLabel = $startDate ? $startDate->format('H:i') : 'Horario a confirmar';
        $endTimeLabel = $endDate ? $endDate->format('H:i') : null;
        $durationLabel = null;
        if ($startDate && $endDate) {
            $diffMinutes = $startDate->diffInMinutes($endDate);
            $durationHours = intdiv($diffMinutes, 60);
            $durationMins = $diffMinutes % 60;
            $durationLabel = '';
            if ($durationHours > 0) {
                $durationLabel .= $durationHours . 'h';
            }
            if ($durationMins > 0) {
                $durationLabel .= ($durationHours > 0 ? ' ' : '') . $durationMins . 'min';
            }
            if ($durationLabel === '') {
                $durationLabel = '0min';
            }
        }
    @endphp

    <div class="event-show-premium min-h-screen">
        <!-- Hero Section -->
        <section class="event-hero-premium">
            @if($eventImageUrl)
                <div class="absolute inset-0 pointer-events-none overflow-hidden">
                    <img src="{{ $eventImageUrl }}" alt=""
                        class="absolute inset-0 w-full h-full object-cover scale-110 saturate-[1.12] brightness-105"
                        style="filter: blur({{ max(14, (int) round($eventsHeroBlurPx * 0.42)) }}px); opacity: 0.72;" loading="lazy" aria-hidden="true">

                    <!-- Película transparente em cor degradê -->
                    <div class="absolute inset-0" style="background: linear-gradient(115deg,
                            rgba(248, 250, 252, 0.74) 0%,
                            {{ $hexToRgba($sitePrimary, 0.12 * $eventsHeroFilmScale) }} 48%,
                            rgba(239, 246, 255, 0.70) 100%);">
                    </div>
                </div>
            @endif

            <div class="event-shell relative">
                <a href="{{ route('events.index') }}"
                    class="event-back-link mb-6">
                    <i class="fas fa-arrow-left"></i> Voltar para eventos
                </a>

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6">
                        <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('error') }}
                    </div>
                @endif

                <div class="event-hero-grid">
                    <!-- Event Info -->
                    <div class="event-copy-panel">
                        @if($isDemo)
                            <span
                                class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold mb-4">
                                <i class="fas fa-info-circle"></i> Evento de Demonstração
                            </span>
                        @endif

                        @if($isClosed)
                            <span
                                class="inline-flex items-center gap-1 bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold mb-4">
                                <i class="fas fa-flag-checkered"></i> Evento encerrado
                            </span>
                        @endif

                        <div class="event-eyebrow">
                            <i class="fas fa-bolt"></i> Evento Somos UNN
                        </div>

                        <h1 class="event-title-premium">{{ $event->title }}</h1>

                        @if($event->speaker)
                            <p class="event-speaker-premium mt-3 text-base font-bold sm:text-lg">
                                <i class="fas fa-user-tie mr-2"></i> {{ $event->speaker }}
                            </p>
                        @endif

                        <p class="event-summary-premium">{{ $heroSummary }}</p>

                        <div class="event-fact-grid">
                            <div class="event-fact">
                                <div class="flex items-center gap-4">
                                    <div class="event-fact-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div>
                                        <p class="event-fact-label">Data</p>
                                        <p class="event-fact-value">
                                            {{ $startDate->translatedFormat('d \d\e F \d\e Y') }}</p>
                                        @if($endDate && !$startDate->isSameDay($endDate))
                                            <p class="text-sm text-gray-500">até {{ $endDate->translatedFormat('d \d\e F \d\e Y') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="event-fact">
                                <div class="flex items-center gap-4">
                                    <div class="event-fact-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div>
                                        <p class="event-fact-label">Horario</p>
                                        <p class="event-fact-value">
                                            {{ $startDate->format('H:i') }}
                                            @if($endDate) às {{ $endDate->format('H:i') }} @endif
                                        </p>
                                        @if($endDate)
                                            @php
                                                $diffMinutes = $startDate->diffInMinutes($endDate);
                                                $durationHours = intdiv($diffMinutes, 60);
                                                $durationMins = $diffMinutes % 60;
                                                $durationLabel = '';
                                                if ($durationHours > 0) {
                                                    $durationLabel .= $durationHours . 'h';
                                                }
                                                if ($durationMins > 0) {
                                                    $durationLabel .= ($durationHours > 0 ? ' ' : '') . $durationMins . 'min';
                                                }
                                                if ($durationLabel === '') {
                                                    $durationLabel = '0min';
                                                }
                                            @endphp
                                            <p class="text-sm text-gray-500">Duração: {{ $durationLabel }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="event-hero-actions">
                            @if(!$isClosed && !$isDemo && (!$event->capacity || $remainingSeats !== 0))
                                <a href="{{ route('events.checkout', $event) }}" class="event-hero-action event-hero-action-primary">
                                    <i class="fas fa-ticket-alt"></i>
                                    {{ $event->current_price > 0 ? 'Comprar ingresso' : 'Garantir minha vaga' }}
                                </a>
                            @endif
                            @if($canSellExhibitor)
                                <a href="{{ route('events.checkout', $event) }}" class="event-hero-action event-hero-action-secondary">
                                    <i class="fas fa-store"></i> Escolher como participar
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Ticket Card -->
                    <div class="w-full lg:w-96 shrink-0">
                        <div class="event-purchase-card">
                            @if($eventImageUrl)
                                <div class="event-purchase-media">
                                    <img src="{{ $eventImageUrl }}" alt="Imagem do evento"
                                        class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                                    <span class="event-card-ribbon">
                                        <i class="fas fa-shield-halved"></i> Pagamento seguro
                                    </span>
                                    <div class="absolute bottom-4 left-4 right-4 z-10">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-[0.18em] text-white/75">Ingresso oficial</p>
                                            <p class="mt-1 text-base font-black leading-tight text-white">{{ \Illuminate\Support\Str::limit($event->title, 42) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="h-2" style="background-color: {{ $eventColor }}"></div>
                            @endif
                            <div class="p-6">
                                <div class="mb-6">
                                    @if($event->current_price > 0)
                                        <span class="event-batch-badge">
                                            <i class="fas fa-layer-group"></i> {{ $event->current_batch_label }}
                                        </span>
                                        <p class="mt-4 text-sm font-bold text-slate-500">Investimento por pessoa</p>
                                        <p class="event-price-premium mt-1">R$ {{ number_format($event->current_price, 2, ',', '.') }}</p>
                                    @else
                                        <p class="text-sm font-bold text-slate-500">Entrada</p>
                                        <p class="event-price-premium mt-1 text-emerald-600">Gratuita</p>
                                    @endif
                                    <p class="mt-2 text-sm font-semibold text-slate-500">
                                        {{ $startDateLabel }} · {{ $startTimeLabel }}{{ $endTimeLabel ? ' as ' . $endTimeLabel : '' }}
                                    </p>
                                </div>

                                @if($event->capacity)
                                    <div class="event-seat-box mb-6">
                                        <div class="flex items-center justify-between text-sm mb-2">
                                            <span class="font-bold text-slate-600">Vagas disponiveis</span>
                                            <span
                                                class="font-black {{ $remainingSeats === 0 ? 'text-red-600' : 'text-slate-950' }}">
                                                {{ $remainingSeats }} / {{ (int) $event->capacity }}
                                            </span>
                                        </div>
                                        <div class="h-3 bg-white rounded-full overflow-hidden shadow-inner">
                                            @php
                                                $capacity = max(1, (int) $event->capacity);
                                                $percent = min(100, max(0, (int) round(($confirmedSeats / $capacity) * 100)));
                                            @endphp
                                            <div class="h-full rounded-full transition-all duration-500"
                                                style="width: {{ $percent }}%; background: linear-gradient(90deg, var(--unn-azul-1), var(--unn-azul-2))">
                                            </div>
                                        </div>
                                        @if($remainingSeats === 0)
                                            <p class="text-xs text-red-600 mt-2 font-medium">
                                                <i class="fas fa-ban mr-1"></i> Esgotado
                                            </p>
                                        @elseif($remainingSeats !== null && $remainingSeats <= 5)
                                            <p class="text-xs text-orange-600 mt-2 font-black">
                                                <i class="fas fa-fire mr-1"></i> Ultimas vagas!
                                            </p>
                                        @endif
                                    </div>
                                @endif

                                @if(isset($userRegistration) && $userRegistration)
                                    @php
                                        $ticketState = $userRegistration->ticketStatusState();
                                        $ticketExpired = $ticketState === 'expired';
                                        $ticketUsed = $ticketState === 'used';
                                        $ticketStatusMessage = $userRegistration->ticketStatusMessage();
                                        $ticketPayload = [
                                            'code' => $userRegistration->ticket_code,
                                            'title' => $event->title,
                                            'date' => $startDate->translatedFormat('d M Y, \à\s H:i'),
                                            'state' => $ticketState,
                                            'statusMessage' => $ticketStatusMessage,
                                        ];
                                    @endphp
                                    <div class="text-center">
                                        <div
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold mb-4 w-full justify-center {{ $ticketUsed ? 'bg-emerald-100 text-emerald-800' : 'bg-green-100 text-green-800' }}">
                                            <i class="fas {{ $ticketUsed ? 'fa-check-double' : 'fa-check-circle' }}"></i> {{ $ticketUsed ? 'Ingresso ja utilizado' : 'Vaga Confirmada' }}
                                        </div>
                                        @if($event->is_ticket_enabled && $userRegistration->ticket_code)
                                            <button type="button"
                                                onclick='showTicketModal({!! json_encode($ticketPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!})'
                                                class="event-action-button event-action-button-primary">
                                                <i class="fas fa-qrcode"></i> Ver Ingresso Digital
                                            </button>
                                            @if($ticketUsed)
                                                <p class="mt-3 text-sm font-bold text-emerald-600 flex items-center justify-center gap-2">
                                                    <i class="fas fa-check-double"></i> {{ $ticketStatusMessage }}
                                                </p>
                                            @elseif($ticketExpired)
                                                <p class="mt-3 text-sm font-bold text-red-600 flex items-center justify-center gap-2">
                                                    <i class="fas fa-ban"></i> {{ $ticketStatusMessage }}
                                                </p>
                                            @endif
                                        @endif

                                        {{-- Acesso ao grupo do evento --}}
                                        @include('events.partials.group-access', [
                                            'event' => $event,
                                            'registration' => $userRegistration,
                                            'buttonClass' => 'event-action-button event-action-button-primary',
                                            'wrapClass' => 'mt-3',
                                        ])

                                        @if(!$isClosed && $remainingSeats !== 0)
                                            <a href="{{ route('events.checkout', $event) }}"
                                                class="event-action-button event-action-button-secondary mt-3">
                                                <i class="fas fa-plus-circle"></i> Comprar mais ingressos
                                            </a>
                                        @endif
                                    </div>
                                @elseif($isClosed)
                                    <a href="{{ route('events.index') }}"
                                        class="event-action-button event-action-button-muted">
                                        <i class="fas fa-calendar-check"></i> Ver próximos eventos
                                    </a>
                                @elseif($isDemo)
                                    <button type="button" data-demo="1"
                                        class="js-demo-event-alert event-action-button event-action-button-primary cursor-not-allowed opacity-75">
                                        <i class="fas fa-ticket-alt"></i>
                                        {{ $event->current_price > 0 ? 'Comprar Ingresso' : 'Garantir Minha Vaga' }}
                                    </button>
                                @elseif($event->capacity && $remainingSeats === 0)
                                    <button
                                        class="event-action-button event-action-button-muted cursor-not-allowed"
                                        disabled>
                                        <i class="fas fa-ban"></i> Esgotado
                                    </button>
                                @else
                                    <a href="{{ route('events.checkout', $event) }}"
                                        class="event-action-button event-action-button-primary">
                                        <i class="fas fa-ticket-alt"></i>
                                        {{ $event->current_price > 0 ? 'Comprar Ingresso' : 'Garantir Minha Vaga' }}
                                    </a>
                                @endif

                                @if($canSellExhibitor)
                                    <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-4 text-left">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-blue-700 shadow-sm">
                                                <i class="fas fa-store"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-black text-blue-950">Comprar área para expositor</p>
                                                @if($canSellExhibitor)
                                                    <p class="mt-1 text-sm text-blue-800">
                                                        {{ $exhibitorBatch }} · {{ 'R$ ' . number_format((float) $exhibitorPrice, 2, ',', '.') }} · {{ (int) $exhibitorRemaining }} restante(s)
                                                    </p>
                                                    @if($event->exhibitor_includes_ticket)
                                                        <p class="mt-1 text-xs font-bold text-emerald-700">
                                                            <i class="fas fa-ticket-alt mr-1"></i> Ingresso incluso no pacote
                                                        </p>
                                                    @endif
                                                    <a href="{{ route('events.checkout', $event) }}"
                                                        class="event-action-button event-action-button-secondary mt-3">
                                                        <i class="fas fa-arrow-right"></i> Escolher no checkout
                                                    </a>
                                                @elseif(($exhibitorStatus['key'] ?? '') === 'esgotado')
                                                    <p class="mt-1 text-sm font-bold text-red-700">Áreas para expositores esgotadas</p>
                                                @elseif(($exhibitorStatus['key'] ?? '') === 'encerrado_por_data')
                                                    <p class="mt-1 text-sm font-bold text-slate-700">Venda de áreas encerrada por data.</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="event-trust-row mt-5">
                                    <span><i class="fas fa-lock mr-1"></i> Pagamento seguro</span>
                                    <span><i class="fas fa-undo mr-1"></i> Reembolso garantido</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if($showExhibitorPublicSection)
            <section id="sobre-expositores" class="px-4 py-8 md:px-12 lg:px-24">
                <div class="event-shell">
                    <div class="event-exhibitor-band">
                        <div class="event-exhibitor-grid">
                            <div>
                                <div class="mb-4 flex items-center gap-4">
                                    <div class="event-exhibitor-icon">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black uppercase tracking-[0.16em] text-blue-700">Espaco comercial</p>
                                        <h2 class="text-2xl font-black leading-tight text-slate-950 md:text-3xl">Areas para expositores</h2>
                                    </div>
                                </div>

                                <p class="max-w-3xl text-base leading-relaxed text-slate-600">{{ $exhibitorSummary }}</p>

                                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                    <div class="event-exhibitor-metric">
                                        <p class="text-xs font-black uppercase tracking-[0.10em] text-slate-500">Lote atual</p>
                                        <p class="mt-1 text-lg font-black text-slate-950">{{ $exhibitorPublicBatchLabel }}</p>
                                    </div>
                                    <div class="event-exhibitor-metric">
                                        <p class="text-xs font-black uppercase tracking-[0.10em] text-slate-500">Valor</p>
                                        <p class="mt-1 text-lg font-black text-slate-950">
                                            {{ $exhibitorPublicPriceLabel }}
                                        </p>
                                    </div>
                                    <div class="event-exhibitor-metric">
                                        <p class="text-xs font-black uppercase tracking-[0.10em] text-slate-500">Disponibilidade</p>
                                        <p class="mt-1 text-lg font-black {{ $canSellExhibitor ? 'text-emerald-700' : 'text-slate-700' }}">
                                            {{ $exhibitorPublicAvailabilityLabel }}
                                        </p>
                                    </div>
                                </div>

                                @if($event->exhibitor_includes_ticket)
                                    <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-black text-emerald-700">
                                        <i class="fas fa-ticket-alt"></i> Ingresso incluso no pacote de expositor
                                    </p>
                                @endif
                            </div>

                            <div>
                                @if($event->exhibitor_area_image_url)
                                    <div class="event-exhibitor-media mb-4">
                                        <img src="{{ $event->exhibitor_area_image_url }}" alt="Planta ou mapa da area de expositores" loading="lazy">
                                    </div>
                                @endif

                                @if($canSellExhibitor)
                                    <a href="{{ route('events.checkout', $event) }}"
                                        class="btn-primary flex w-full items-center justify-center gap-2 rounded-2xl px-5 py-4 text-sm font-black text-white shadow-lg transition hover:shadow-xl">
                                        <i class="fas fa-store"></i> Escolher no checkout
                                    </a>
                                @elseif(($exhibitorStatus['key'] ?? '') === 'esgotado')
                                    <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-center text-sm font-black text-red-700">
                                        Areas para expositores esgotadas
                                    </div>
                                @else
                                    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 text-center text-sm font-black text-slate-600">
                                        Informacoes de expositor disponiveis no evento
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <!-- Location Section -->
        <section class="py-16 px-6 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-8">
                    <i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Localização
                </h2>

                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Address Card -->
                    <div class="bg-white rounded-3xl shadow-lg p-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $event->location }}</h3>
                        <p class="text-gray-600 mb-6">{{ $event->address }}</p>

                        <div class="space-y-3">
                            @php
                                $mapsQuery = ($event->latitude && $event->longitude)
                                    ? $event->latitude . ',' . $event->longitude
                                    : urlencode($event->address ?? $event->location ?? '');
                                $googleMapsUrl = ($event->latitude && $event->longitude)
                                    ? 'https://www.google.com/maps/dir/?api=1&destination=' . $event->latitude . ',' . $event->longitude
                                    : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($event->address ?? $event->location ?? '');
                                $wazeUrl = ($event->latitude && $event->longitude)
                                    ? 'https://waze.com/ul?ll=' . $event->latitude . ',' . $event->longitude . '&navigate=yes'
                                    : 'https://waze.com/ul?q=' . urlencode($event->address ?? $event->location ?? '') . '&navigate=yes';
                            @endphp
                            <a href="{{ $googleMapsUrl }}"
                                target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 font-medium transition"
                                style="color: var(--unn-azul-1)">
                                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-directions"></i>
                                </div>
                                Abrir rota no Google Maps
                            </a>
                            <a href="{{ $wazeUrl }}"
                                target="_blank" rel="noopener noreferrer"
                                class="flex items-center gap-3 text-purple-600 hover:text-purple-700 font-medium transition">
                                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                                    <i class="fab fa-waze"></i>
                                </div>
                                Abrir rota no Waze
                            </a>
                        </div>
                    </div>

                    <!-- Map -->
                    <div class="lg:col-span-2 bg-white rounded-3xl shadow-lg overflow-hidden h-[400px]">
                        @if($event->latitude && $event->longitude)
                            <iframe
                                src="https://www.openstreetmap.org/export/embed.html?bbox={{ $event->longitude - 0.005 }},{{ $event->latitude - 0.005 }},{{ $event->longitude + 0.005 }},{{ $event->latitude + 0.005 }}&layer=mapnik&marker={{ $event->latitude }},{{ $event->longitude }}"
                                class="w-full h-full border-0" loading="lazy" title="Mapa do evento"></iframe>
                        @elseif($event->address)
                            <iframe
                                src="https://maps.google.com/maps?q={{ urlencode($event->address) }}&output=embed"
                                class="w-full h-full border-0" loading="lazy" title="Mapa do evento"></iframe>
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                <p class="text-gray-500">Mapa não disponível</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- Event Details -->
        <section class="event-details-section px-6 pb-16 md:px-12 lg:px-24">
            <div class="event-details-card mx-auto max-w-5xl p-6 md:p-10">
                <div class="mb-8 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-blue-700">Detalhes completos</p>
                        <h2 class="mt-2 text-3xl font-black text-gray-900 md:text-4xl">Sobre o evento</h2>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-black text-blue-700">
                        <i class="fas fa-circle-info"></i> Informacoes oficiais
                    </span>
                </div>

                <div class="prose prose-lg max-w-none leading-relaxed editor-content">
                    {!! $event->description !!}
                </div>

                <div class="mt-12 grid sm:grid-cols-3 gap-6">
                    <div class="bg-slate-50 rounded-2xl p-6 text-center">
                        <i class="fas fa-users text-3xl mb-3" style="color: var(--unn-azul-1)"></i>
                        <p class="text-2xl font-bold text-gray-900">{{ $event->capacity ?? '∞' }}</p>
                        <p class="text-sm text-gray-500">Participantes</p>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-6 text-center">
                        <i class="fas fa-hourglass-half text-3xl text-green-500 mb-3"></i>
                        <p class="text-2xl font-bold text-gray-900">
                            @if($endDate)
                                {{ $durationLabel ?? ($startDate->diffInHours($endDate) . 'h') }}
                            @else
                                —
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">Duração</p>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-6 text-center">
                        <i class="fas fa-ticket-alt text-3xl mb-3" style="color: var(--unn-azul-3)"></i>
                        <p class="text-2xl font-bold text-gray-900">
                            @if($isClosed)
                                Encerrado
                            @elseif($event->capacity && $remainingSeats === 0)
                                Esgotado
                            @else
                                Aberto
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">Inscrições</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Galeria de Fotos e Vídeos -->
        @if($event->media && $event->media->count() > 0)
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-slate-50">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-8 items-center flex gap-3">
                    <i class="fas fa-camera-retro" style="color: var(--unn-azul-1)"></i> Galeria do Evento
                </h2>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($event->media as $media)
                        <div class="group relative rounded-2xl overflow-hidden bg-gray-200 aspect-square shadow-sm hover:shadow-xl transition-all duration-300">
                            @if($media->type === 'image')
                                <a href="{{ asset('storage/' . $media->file_path) }}" data-fancybox="gallery" data-caption="{{ $event->title }} - Galeria">
                                    <img src="{{ asset('storage/' . $media->file_path) }}" alt="Foto do evento" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                </a>
                            @elseif($media->type === 'video')
                                <a href="{{ asset('storage/' . $media->file_path) }}" data-fancybox="gallery" data-caption="{{ $event->title }} - Vídeo do evento">
                                    <div class="w-full h-full bg-slate-900 flex items-center justify-center relative">
                                        <i class="fas fa-play text-white/50 text-5xl group-hover:text-white/80 transition-colors"></i>
                                    </div>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
        
        <!-- TICKET MODAL -->
<div id="ticketModal"
    class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/50 backdrop-blur-sm px-4"
    style="display: none;" aria-hidden="true">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden relative"
        onclick="event.stopPropagation()">
        <!-- Close button -->
        <button type="button" onclick="closeTicketModal()"
            class="absolute top-4 right-4 w-10 h-10 bg-slate-100 text-slate-500 rounded-full flex items-center justify-center hover:bg-slate-200 transition-colors z-10">
            <i class="fas fa-times"></i>
        </button>

        <!-- Ticket Header (Pink/Purple vibrant gradient) -->
        <div class="h-28 px-6 pt-6 flex flex-col justify-end pb-4"
            style="background: linear-gradient(135deg, #FF6B6B 0%, #C0392B 100%);">
            <h3 class="text-white font-black text-xl leading-tight drop-shadow uppercase tracking-wider"
                id="modalTicketTitle">Meu Evento</h3>
            <p class="text-white/80 font-medium text-sm flex items-center gap-2 mt-1">
                <i class="fas fa-calendar-day"></i> <span id="modalTicketDate">00/00/0000 00:00</span>
            </p>
        </div>

        <!-- Ticket Body with QR -->
        <div class="p-6 text-center bg-zinc-50 relative">
            <!-- Cutout holes for ticket effect -->
            <div class="absolute -top-4 -left-4 w-8 h-8 rounded-full bg-black/50 backdrop-blur-sm border border-black/10 z-0 hidden lg:block"
                style="background-color: rgb(15 23 42 / 0.5);"></div>
            <div class="absolute -top-4 -right-4 w-8 h-8 rounded-full bg-black/50 backdrop-blur-sm border border-black/10 z-0 hidden lg:block"
                style="background-color: rgb(15 23 42 / 0.5);"></div>

            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Apresente este código na entrada
            </p>

            <div id="ticketStateAlert"
                class="hidden mb-5 rounded-2xl px-4 py-3 text-sm font-bold">
            </div>

            <div class="relative inline-block mb-6">
                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 inline-block"
                    id="qrcode-container">
                    <!-- QR Code vai aqui via JS -->
                </div>
                <div id="ticketStateOverlay"
                    class="hidden absolute inset-0 rounded-xl bg-white/90 backdrop-blur-[1px] border-2 flex items-center justify-center">
                    <span id="ticketStateOverlayLabel"
                        class="rotate-[-10deg] rounded-xl border-2 px-4 py-2 text-center text-lg font-black uppercase tracking-[0.12em]">
                        Expirado
                    </span>
                </div>
            </div>

            <p class="font-mono text-sm text-slate-600 tracking-widest bg-slate-100 py-2 px-4 rounded-lg select-all"
                id="modalTicketCodeString">
                XXXX-XXXX
            </p>
        </div>
    </div>
</div>
    </div>
@endsection

@push('scripts')
    <!-- Passamos o script do QRCodeJS apenas se preciso -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-demo-event-alert');
            if (!btn) return;

            e.preventDefault();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Evento de demonstração',
                    text: 'Este é um evento de demonstração. Configure eventos reais no painel administrativo.',
                    icon: 'info'
                });
                return;
            }

            if (typeof toastr !== 'undefined') {
                toastr.info('Este é um evento de demonstração. Configure eventos reais no painel administrativo.');
            }
        });

        const modal = document.getElementById('ticketModal');
        let qrcodeInstance = null;

        window.showTicketModal = function (payload) {
            const stateAlert = document.getElementById('ticketStateAlert');
            const stateOverlay = document.getElementById('ticketStateOverlay');
            const stateOverlayLabel = document.getElementById('ticketStateOverlayLabel');

            document.getElementById('modalTicketTitle').innerText = payload.title;
            document.getElementById('modalTicketDate').innerText = payload.date;
            document.getElementById('modalTicketCodeString').innerText = payload.code;

            const qrContainer = document.getElementById('qrcode-container');
            qrContainer.innerHTML = '';

            // Generate QR Code
            qrcodeInstance = new QRCode(qrContainer, {
                text: payload.code,
                width: 220,
                height: 220,
                colorDark: "#1e293b", // slate-800
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            stateAlert.className = 'hidden mb-5 rounded-2xl px-4 py-3 text-sm font-bold';
            stateOverlay.className = 'hidden absolute inset-0 rounded-xl bg-white/90 backdrop-blur-[1px] border-2 flex items-center justify-center';
            stateOverlayLabel.className = 'rotate-[-10deg] rounded-xl border-2 px-4 py-2 text-center text-lg font-black uppercase tracking-[0.12em]';

            if (payload.state === 'used') {
                stateAlert.textContent = payload.statusMessage || 'Ja utilizado.';
                stateAlert.classList.add('border', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
                stateAlert.classList.remove('hidden');
                stateOverlay.classList.remove('hidden');
                stateOverlay.classList.add('border-emerald-300');
                stateOverlayLabel.textContent = 'Ja utilizado';
                stateOverlayLabel.classList.add('border-emerald-600', 'bg-emerald-600/10', 'text-emerald-700');
            } else if (payload.state === 'expired') {
                stateAlert.textContent = payload.statusMessage || 'Ingresso invalido ou expirado.';
                stateAlert.classList.add('border', 'border-red-200', 'bg-red-50', 'text-red-700');
                stateAlert.classList.remove('hidden');
                stateOverlay.classList.remove('hidden');
                stateOverlay.classList.add('border-red-300');
                stateOverlayLabel.textContent = 'Ingresso invalido ou expirado';
                stateOverlayLabel.classList.add('border-red-600', 'bg-red-600/10', 'text-red-700');
            } else {
                stateAlert.textContent = '';
                stateAlert.classList.add('hidden');
                stateOverlay.classList.add('hidden');
            }

            modal.setAttribute('aria-hidden', 'false');
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        };

        window.closeTicketModal = function () {
            modal.setAttribute('aria-hidden', 'true');
            modal.style.display = 'none';
            modal.classList.add('hidden');
        };

        // Hide modal on backdrop click
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeTicketModal();
            }
        });
    </script>
@endpush
