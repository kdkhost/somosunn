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

        .unn-star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 6px;
        }

        .unn-star-rating input {
            display: none;
        }

        .unn-star-rating label {
            cursor: pointer;
            color: #cbd5e1;
            font-size: 24px;
            line-height: 1;
            margin: 0;
            transition: color 0.15s ease;
        }

        .unn-star-rating input:checked~label,
        .unn-star-rating label:hover,
        .unn-star-rating label:hover~label {
            color: #f59e0b;
        }
    </style>
@endpush

@section('content')
    @php
        $mentorName = optional($mentorship->mentor)->name ?? 'Mentor UNN';
        $price = (float) ($mentorship->price ?? 0);
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

                        <p class="mt-3 text-4xl font-black text-slate-900">
                            {{ $price > 0 ? 'R$ ' . number_format($price, 2, ',', '.') : 'Gratuito' }}
                        </p>

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
                            <a href="{{ route('premium') }}"
                                class="btn-primary text-white px-8 py-4 rounded-2xl font-bold inline-flex items-center justify-center gap-3 shadow-[0_15px_30px_-10px_rgba(31,94,219,0.4)] hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 whitespace-nowrap">
                                Ver planos Premium <i class="fas fa-crown"></i>
                            </a>
                            <a href="{{ route('mentorships.index') }}"
                                class="px-8 py-4 rounded-2xl font-bold border-2 border-slate-100 text-slate-600 hover:bg-slate-50 hover:border-slate-200 transition-all duration-300 inline-flex items-center justify-center whitespace-nowrap">
                                Ver outras mentorias
                            </a>
                        </div>

                        <p class="mt-8 text-xs text-slate-400 text-center leading-relaxed">
                            Acesso exclusivo para membros. Planos Premium liberam mentorias ilimitadas.
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

                    @if($myReview)
                        <div
                            class="mb-4 rounded-lg px-4 py-3 border {{ $myReview->status === 'approved' ? 'bg-green-50 border-green-200 text-green-700' : ($myReview->status === 'rejected' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-yellow-50 border-yellow-200 text-yellow-700') }}">
                            @if($myReview->status === 'approved')
                                Sua avaliação está publicada.
                            @elseif($myReview->status === 'rejected')
                                Sua avaliação foi recusada. Você pode ajustar e enviar novamente.
                            @else
                                Sua avaliação está em moderação.
                            @endif
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                        @forelse($reviews as $review)
                            <article class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center shrink-0">
                                            {{ strtoupper(mb_substr((string) ($review->user->name ?? 'U'), 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-gray-900 truncate">
                                                {{ $review->user->name ?? 'Usuário' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ optional($review->created_at)->format('d/m/Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-amber-500 text-sm whitespace-nowrap">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-sm text-gray-700 leading-relaxed">{!! nl2br(e($review->comment)) !!}</p>
                            </article>
                        @empty
                            <div
                                class="md:col-span-2 rounded-lg border border-dashed border-slate-300 px-4 py-6 text-center text-slate-500">
                                Esta mentoria ainda não possui avaliações aprovadas.
                            </div>
                        @endforelse
                    </div>

                    @auth
                        <form method="POST" action="{{ route('mentorships.reviews.store', $mentorship->id) }}"
                            class="border border-slate-200 rounded-xl p-5">
                            @csrf
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Envie sua avaliação</h3>
                            <p class="text-sm text-gray-500 mb-4">Sua avaliação será moderada antes de aparecer na página.</p>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-800 mb-2">Nota</label>
                                <div class="unn-star-rating" role="radiogroup" aria-label="Avaliação por estrelas">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="mentorship-rating-{{ $i }}" name="rating" value="{{ $i }}" {{ (string) $selectedRating === (string) $i ? 'checked' : '' }}>
                                        <label for="mentorship-rating-{{ $i }}" title="{{ $i }} de 5"><i
                                                class="fas fa-star"></i></label>
                                    @endfor
                                </div>
                                @error('rating')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                            </div>

                            <div class="mb-4">
                                <label for="mentorship-review-comment"
                                    class="block text-sm font-semibold text-gray-800 mb-2">Comentário</label>
                                <textarea id="mentorship-review-comment" name="comment" rows="4"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Conte como foi sua experiência com esta mentoria...">{{ old('comment', optional($myReview)->comment) }}</textarea>
                                @error('comment')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                            </div>

                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#1F5EDB] hover:bg-blue-700 text-white font-semibold px-5 py-2.5 transition">
                                <i class="fas fa-paper-plane"></i>
                                {{ $myReview ? 'Atualizar avaliação' : 'Enviar avaliação' }}
                            </button>
                        </form>
                    @else
                        <div class="rounded-lg border border-dashed border-slate-300 px-4 py-5 text-sm text-slate-600">
                            Faça <a href="{{ route('login') }}" class="text-blue-600 font-semibold">login</a> para avaliar esta
                            mentoria.
                        </div>
                    @endauth
                </div>
            </div>
        </section>
    </div>
@endsection