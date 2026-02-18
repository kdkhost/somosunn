@extends('layouts.app')

@section('title', ($mentorship->title ?? 'Mentoria') . ' - UNN')

@push('styles')
    <style>
        .unn-mentorship-show-hero {
            position: relative;
            background: #1e293b;
            /* Fallback */
        }

        .unn-mentorship-show-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(rgba(255, 255, 255, 0.35) 1px, transparent 1px),
                radial-gradient(rgba(255, 255, 255, 0.18) 1px, transparent 1px);
            background-size: 36px 36px, 64px 64px;
            background-position: 0 0, 18px 18px;
            opacity: 0.26;
            pointer-events: none;
        }

        /* Rating styles managed in partials/reviews/form.blade.php */
    </style>
@endpush

@section('content')
    @php
        $mentorName = optional($mentorship->mentor)->name ?? 'Mentor UNN';
        $regularPrice = (float) ($mentorship->price ?? 0);
        $price = (float) ($mentorship->effective_price ?? $regularPrice);
        $flashActive = method_exists($mentorship, 'isFlashSaleActive') ? (bool) $mentorship->isFlashSaleActive() : false;
        $slots = $mentorship->slots;
        $slotsLabel = is_null($slots) ? 'A confirmar' : (string) $slots;
        $description = trim((string) ($mentorship->description ?? ''));

        $selectedRating = old('rating', optional($myReview)->rating);
        $selectedRating = is_numeric($selectedRating) ? max(1, min(5, (int) $selectedRating)) : null;

        $resolveImageUrl = function (?string $path): ?string {
            $path = trim((string) $path);
            if ($path === '')
                return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
                return $path;
            if (str_starts_with($path, 'storage/'))
                return asset($path);
            if (str_starts_with($path, 'uploads/'))
                return asset($path);
            return asset('storage/' . ltrim($path, '/'));
        };

        $mentorshipImageUrl = $resolveImageUrl($mentorship->image ?? null);

        $sitePrimary = \App\Models\Setting::get('site_color_primary') ?: '#1F5EDB';
        $siteSecondary = \App\Models\Setting::get('site_color_secondary') ?: '#1D3FC4';

        $hexToRgba = function (?string $hex, float $alpha): ?string {
            $hex = trim((string) $hex);
            if ($hex === '')
                return null;
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

        // Admin controls
        $heroBlurPxRaw = \App\Models\Setting::get('events_hero_bg_blur_px');
        $heroBlurPx = is_numeric($heroBlurPxRaw) ? (int) $heroBlurPxRaw : 64;

        $heroFilmRaw = \App\Models\Setting::get('events_hero_film_strength_percent');
        $heroFilmScale = (is_numeric($heroFilmRaw) ? (int) $heroFilmRaw : 100) / 100;
    @endphp

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
        <section class="unn-mentorship-show-hero relative overflow-hidden">
            @if($mentorshipImageUrl)
                <div class="absolute inset-0 pointer-events-none overflow-hidden">
                    <img src="{{ $mentorshipImageUrl }}" alt=""
                        class="absolute inset-0 w-full h-full object-cover scale-110 saturate-[1.1] brightness-[0.85]"
                        style="filter: blur({{ $heroBlurPx }}px); opacity: 0.7;" aria-hidden="true">

                    <!-- Película transparente em cor degradê -->
                    <div class="absolute inset-0" style="background: linear-gradient(135deg, 
                                {{ $hexToRgba($sitePrimary, 0.8 * $heroFilmScale) }} 0%, 
                                {{ $hexToRgba($siteSecondary, 0.7 * $heroFilmScale) }} 50%, 
                                {{ $hexToRgba($sitePrimary, 0.9 * $heroFilmScale) }} 100%);">
                    </div>
                </div>
            @else
                <div class="absolute inset-0 pointer-events-none"
                    style="background: linear-gradient(135deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 100%);"></div>
            @endif

            <div
                class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-black/20 text-transparent pointer-events-none">
            </div>

            <div class="px-4 md:px-12 lg:px-24 pt-10 md:pt-14 pb-16 md:pb-20 relative">
                <div class="max-w-6xl mx-auto">
                    <a href="{{ route('mentorships.index') }}"
                        class="inline-flex items-center gap-2 text-white/85 hover:text-white font-semibold">
                        <i class="fas fa-arrow-left"></i> Voltar para mentorias
                    </a>

                    <div class="mt-8 md:mt-10">
                        <h1 class="text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-white drop-shadow-2xl">
                            {{ $mentorship->title ?? 'Mentoria' }}
                        </h1>
                        <p class="mt-4 text-white/90 text-base sm:text-lg max-w-3xl font-medium drop-shadow-md">
                            Acompanhamento com orientação prática para acelerar resultados em networking e negócios.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="px-4 md:px-12 lg:px-24 -mt-10 pb-14 md:pb-20">
            <div class="max-w-6xl mx-auto">
                <div class="grid lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white rounded-3xl shadow-xl border border-slate-100 p-8 md:p-10">
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900">Sobre a mentoria</h2>

                        @if($description !== '')
                            <div class="mt-5 text-slate-600 leading-relaxed">
                                {!! \App\Support\RichText::toHtml($description) !!}
                            </div>
                        @else
                            <p class="mt-5 text-slate-600 leading-relaxed">Sem descrição.</p>
                        @endif
                    </div>

                    <aside class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 md:p-10">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Investimento</p>
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-xs font-black uppercase tracking-wider">
                                Premium
                            </span>
                        </div>

                        <div class="mt-3 flex items-end gap-3">
                            <p class="text-4xl font-black text-slate-900">
                                {{ $price > 0 ? 'R$ ' . number_format($price, 2, ',', '.') : 'Gratuito' }}
                            </p>
                            @if($flashActive && $regularPrice > 0 && $price < $regularPrice)
                                <p class="text-sm text-slate-400 line-through mb-1">
                                    {{ 'R$ ' . number_format($regularPrice, 2, ',', '.') }}
                                </p>
                            @endif
                        </div>

                        @if($flashActive && $mentorship->flash_sale_ends_at)
                            <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-rose-50 border border-rose-200 px-3 py-1 text-xs font-black text-rose-800">
                                <i class="fas fa-bolt"></i> Promoção relâmpago ativa
                            </div>
                        @endif

                        <div class="mt-8 space-y-4">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                                    <i class="fas fa-user-tie text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">Mentor</div>
                                    <div class="text-sm text-slate-500">{{ $mentorName }}</div>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-green-50 flex items-center justify-center shrink-0">
                                    <i class="fas fa-users text-green-600"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">Vagas</div>
                                    <div class="text-sm text-slate-500">{{ $slotsLabel }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 flex flex-col gap-4">
                            @if($price > 0)
                                <a href="{{ route('mentorships.checkout.show', $mentorship) }}"
                                    class="btn-primary text-white px-8 py-4 rounded-2xl font-bold inline-flex items-center justify-center gap-3 shadow-[0_15px_30px_-10px_rgba(31,94,219,0.4)] hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 whitespace-nowrap">
                                    Comprar mentoria <i class="fas fa-lock"></i>
                                </a>
                            @endif

                            <a href="{{ route('premium') }}"
                                class="px-8 py-4 rounded-2xl font-bold border-2 border-slate-100 text-slate-700 hover:bg-slate-50 hover:border-slate-200 transition-all duration-300 inline-flex items-center justify-center whitespace-nowrap">
                                Ver planos Premium <i class="fas fa-crown ml-2"></i>
                            </a>

                            <a href="{{ route('mentorships.index') }}"
                                class="px-8 py-4 rounded-2xl font-bold border-2 border-slate-100 text-slate-600 hover:bg-slate-50 hover:border-slate-200 transition-all duration-300 inline-flex items-center justify-center whitespace-nowrap">
                                Ver outras mentorias
                            </a>
                        </div>

                        <p class="mt-8 text-xs text-slate-400 text-center leading-relaxed">
                            Acesso exclusivo para membros. Você pode comprar esta mentoria (quando disponível) ou acessar via planos Premium (conforme regras do seu plano).
                        </p>
                    </aside>
                </div>

                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 md:p-10 mt-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900">Avaliações</h2>
                            <p class="text-sm text-slate-500">Comentários dos membros sobre esta mentoria.</p>
                        </div>
                        @if($reviewsCount > 0)
                            <div
                                class="inline-flex items-center gap-2 rounded-full bg-amber-50 text-amber-700 px-4 py-2 text-sm font-semibold">
                                <i class="fas fa-star"></i>
                                {{ number_format((float) $reviewsAvg, 1, ',', '.') }}/5 ({{ $reviewsCount }}
                                {{ $reviewsCount === 1 ? 'avaliação' : 'avaliações' }})
                            </div>
                        @endif
                    </div>

                    @include('reviews.list', ['reviews' => $reviews, 'emptyMessage' => 'Esta mentoria ainda não possui avaliações aprovadas.'])

                    @include('reviews.form', [
                        'action' => route('mentorships.reviews.store', $mentorship->id),
                        'myReview' => $myReview
                    ])
                </div>
            </div>
        </section>
    </div>
@endsection
