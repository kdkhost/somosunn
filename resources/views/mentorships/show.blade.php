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

        .mentorship-hero-glow {
            background: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.2), transparent 55%),
                radial-gradient(circle at 80% 10%, rgba(255, 255, 255, 0.14), transparent 55%),
                linear-gradient(135deg, rgba(8, 24, 58, 0.9), rgba(15, 23, 42, 0.8));
        }

        .mentorship-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            padding: 0.4rem 0.9rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.92);
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.24);
            backdrop-filter: blur(10px);
        }

        .mentorship-card {
            position: relative;
            background: #ffffff;
            border-radius: 1.75rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 20px 45px -30px rgba(15, 23, 42, 0.35);
        }

        .mentorship-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(14, 165, 233, 0.1), rgba(99, 102, 241, 0.25));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .mentorship-price {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.18), rgba(14, 165, 233, 0.08));
            border: 1px solid rgba(59, 130, 246, 0.15);
        }

        .mentorship-price-value {
            white-space: nowrap;
            letter-spacing: -0.02em;
        }

        .mentorship-cta {
            background: linear-gradient(135deg, #1f5edb, #2f7df6);
            box-shadow: 0 18px 35px -18px rgba(37, 99, 235, 0.55);
        }

        .mentorship-avatar-ring {
            background: conic-gradient(from 180deg, rgba(255, 255, 255, 0.2), rgba(59, 130, 246, 0.65), rgba(14, 165, 233, 0.4), rgba(255, 255, 255, 0.2));
            padding: 2px;
            border-radius: 999px;
        }

        .mentorship-float {
            animation: mentorshipFloat 6s ease-in-out infinite;
        }

        @keyframes mentorshipFloat {
            0%,
            100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-6px);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .mentorship-float {
                animation: none;
            }
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
        $heroExcerpt = $description !== ''
            ? Str::limit(strip_tags($description), 160)
            : 'Acompanhamento com orientaÃƒÂ§ÃƒÂ£o prÃƒÂ¡tica para acelerar resultados em networking e negÃƒÂ³cios.';
        $currentUser = Auth::user();
        $hasFullAccess = $currentUser ? $currentUser->hasMentorshipAccess($mentorship) : false;
        $canAccessByPlan = $currentUser ? $currentUser->canAccessFeature('mentorships_access') : false;
        $accessBlocked = session('access_blocked');
        $showAccessModal = is_array($accessBlocked)
            && ($accessBlocked['type'] ?? null) === 'mentorship'
            && (int) ($accessBlocked['mentorship_id'] ?? 0) === (int) $mentorship->id;
        $showAccessModal = $showAccessModal || (bool) request()->query('locked');

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
        $mentorAvatarRaw = optional($mentorship->mentor)->profile_photo_url ?? optional($mentorship->mentor)->photo ?? null;
        $mentorAvatarUrl = $resolveImageUrl($mentorAvatarRaw);

        $typeLabel = match ($mentorship->type ?? '') {
            \App\Models\Mentorship::TYPE_ONLINE => 'Online',
            \App\Models\Mentorship::TYPE_PRESENCIAL => 'Presencial',
            default => null,
        };

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
        <section class="unn-mentorship-show-hero mentorship-hero-glow relative overflow-hidden">
            @if($mentorshipImageUrl)
                <div class="absolute inset-0 pointer-events-none overflow-hidden">
                    <img src="{{ $mentorshipImageUrl }}" alt=""
                        class="absolute inset-0 w-full h-full object-cover scale-110 saturate-[1.1] brightness-[0.85]"
                        style="filter: blur({{ $heroBlurPx }}px); opacity: 0.7;" aria-hidden="true">

                    <!-- PelÃ­cula transparente em cor degradÃª -->
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
                            {{ $heroExcerpt }}
                        </p>
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <span class="mentorship-chip">
                                <i class="fas fa-crown text-[0.7rem]"></i> Premium
                            </span>
                            @if($typeLabel)
                                <span class="mentorship-chip">
                                    <i class="fas fa-satellite-dish text-[0.7rem]"></i> {{ $typeLabel }}
                                </span>
                            @endif
                            <span class="mentorship-chip">
                                <i class="fas fa-users text-[0.7rem]"></i> Vagas: {{ $slotsLabel }}
                            </span>
                            @if($reviewsCount > 0)
                                <span class="mentorship-chip">
                                    <i class="fas fa-star text-[0.7rem]"></i>
                                    {{ number_format((float) $reviewsAvg, 1, ',', '.') }}/5
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="px-4 md:px-12 lg:px-24 -mt-10 pb-14 md:pb-20">
            <div class="max-w-6xl mx-auto">
                <div class="grid lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 mentorship-card p-8 md:p-10">
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900">Sobre a mentoria</h2>

                        <div class="mt-6 grid sm:grid-cols-2 gap-4">
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center gap-4">
                                <div class="mentorship-avatar-ring">
                                    <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center overflow-hidden">
                                        @if($mentorAvatarUrl)
                                            <img src="{{ $mentorAvatarUrl }}" alt="{{ $mentorName }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-sm font-black text-slate-600">
                                                {{ mb_substr($mentorName, 0, 1) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mentor</p>
                                    <p class="text-base font-black text-slate-900">{{ $mentorName }}</p>
                                </div>
                            </div>
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Formato</p>
                                    <p class="text-base font-black text-slate-900">{{ $typeLabel ?? 'Mentoria Premium' }}</p>
                                </div>
                            </div>
                        </div>

                        @if($description !== '')
                            <div class="mt-5 text-slate-600 leading-relaxed">
                                {!! \App\Support\RichText::toHtml($description) !!}
                            </div>
                        @else
                            <p class="mt-5 text-slate-600 leading-relaxed">Sem descriÃ§Ã£o.</p>
                        @endif
                    </div>

                    <aside class="mentorship-card lg:sticky lg:top-8 p-8 md:p-10">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Investimento</p>
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-xs font-black uppercase tracking-wider">
                                Premium
                            </span>
                        </div>
                        <div class="mt-5 mentorship-price rounded-2xl p-5">
                            <div class="flex items-end justify-between gap-3">
                                <p class="text-4xl font-black text-slate-900 mentorship-price-value">
                                    {{ $price > 0 ? 'R$ ' . number_format($price, 2, ',', '.') : 'Gratuito' }}
                                </p>
                                @if($flashActive && $regularPrice > 0 && $price < $regularPrice)
                                    <p class="text-sm text-slate-400 line-through mb-1">
                                        {{ 'R$ ' . number_format($regularPrice, 2, ',', '.') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        @if($flashActive && $mentorship->flash_sale_ends_at)
                            <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-rose-50 border border-rose-200 px-3 py-1 text-xs font-black text-rose-800">
                                <i class="fas fa-bolt"></i> PromoÃ§Ã£o relÃ¢mpago termina {{ $mentorship->flash_sale_ends_at->diffForHumans() }}
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

                            @if($mentorship->is_certificate_enabled)
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center shrink-0">
                                        <i class="fas fa-certificate text-amber-500"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">Certificado de conclusão</div>
                                        <div class="text-sm text-slate-500">Disponível após finalizar a mentoria.</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-10 flex flex-col gap-4">
                            @if($price > 0)
                                @if($hasFullAccess)
                                    <a href="{{ route('panel.dashboard') }}"
                                        class="px-8 py-4 rounded-2xl font-bold inline-flex items-center justify-center gap-3 bg-emerald-500 text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 whitespace-nowrap">
                                        Acesso liberado <i class="fas fa-check-circle"></i>
                                    </a>
                                @else
                                    <button type="button" data-access-modal-trigger
                                        class="mentorship-cta text-white px-8 py-4 rounded-2xl font-bold inline-flex items-center justify-center gap-3 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 whitespace-nowrap mentorship-float">
                                        Comprar mentoria <i class="fas fa-lock"></i>
                                    </button>
                                @endif
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
                            Acesso exclusivo para membros. VocÃª pode comprar esta mentoria (quando disponÃ­vel) ou acessar via planos Premium (conforme regras do seu plano).
                        </p>
                    </aside>
                </div>

                <div class="mentorship-card p-8 md:p-10 mt-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900">AvaliaÃ§Ãµes</h2>
                            <p class="text-sm text-slate-500">ComentÃ¡rios dos membros sobre esta mentoria.</p>
                        </div>
                        @if($reviewsCount > 0)
                            <div
                                class="inline-flex items-center gap-2 rounded-full bg-amber-50 text-amber-700 px-4 py-2 text-sm font-semibold">
                                <i class="fas fa-star"></i>
                                {{ number_format((float) $reviewsAvg, 1, ',', '.') }}/5 ({{ $reviewsCount }}
                                {{ $reviewsCount === 1 ? 'avaliaÃ§Ã£o' : 'avaliaÃ§Ãµes' }})
                            </div>
                        @endif
                    </div>

                    @include('reviews.list', ['reviews' => $reviews, 'emptyMessage' => 'Esta mentoria ainda nÃ£o possui avaliaÃ§Ãµes aprovadas.'])

                    @include('reviews.form', [
                        'action' => route('mentorships.reviews.store', $mentorship->id),
                        'myReview' => $myReview
                    ])
                </div>
            </div>
        </section>
    </div>

    @if($price > 0)
        <div id="mentorship-access-modal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" data-mentorship-modal-close></div>
            <div class="relative w-full max-w-xl rounded-3xl bg-white shadow-2xl border border-slate-100 overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Conteúdo premium</p>
                            <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">
                                Desbloqueie {{ $mentorship->title ?? 'esta mentoria' }}
                            </h3>
                        </div>
                        <button type="button" class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 hover:text-slate-700"
                            data-mentorship-modal-close>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Para acessar este conteúdo, você pode <strong>fazer upgrade do seu plano</strong> e/ou
                            <strong>comprar a mentoria</strong>.
                            Mesmo com upgrade, mentorias pagas exigem compra para liberar o acesso completo.
                        </p>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('premium', ['feature' => 'mentorships_access']) }}"
                            class="flex-1 px-6 py-3 rounded-xl font-bold border-2 border-slate-200 text-slate-700 hover:bg-slate-50 text-center">
                            Fazer upgrade
                        </a>
                        <a href="{{ route('mentorships.checkout.show', $mentorship) }}"
                            class="flex-1 px-6 py-3 rounded-xl font-bold text-white text-center"
                            style="background: linear-gradient(135deg, #1f5edb, #2f7df6);">
                            Comprar mentoria
                        </a>
                    </div>

                    @if($currentUser && $canAccessByPlan)
                        <p class="mt-4 text-xs text-slate-500">
                            Seu plano atual permite acesso a mentorias, mas este conteúdo é pago e requer compra.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <script>
            (function () {
                const modal = document.getElementById('mentorship-access-modal');
                if (!modal) return;
                const openModal = () => {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                };
                const closeModal = () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                };

                document.querySelectorAll('[data-access-modal-trigger]').forEach((btn) => {
                    btn.addEventListener('click', openModal);
                });
                document.querySelectorAll('[data-mentorship-modal-close]').forEach((btn) => {
                    btn.addEventListener('click', closeModal);
                });

                if (@json($showAccessModal)) {
                    openModal();
                }
            })();
        </script>
    @endif
@endsection
