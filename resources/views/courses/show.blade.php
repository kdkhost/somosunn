@extends('layouts.app')

@section('title', $course->title . ' - UNN')

@push('styles')
    <style>
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

        .unn-star-rating input:checked ~ label,
        .unn-star-rating label:hover,
        .unn-star-rating label:hover ~ label {
            color: #f59e0b;
        }
    </style>
@endpush

@section('content')
@php
    $thumbUrl = null;
    if (!empty($course->thumbnail)) {
        $path = trim((string) $course->thumbnail);
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) $thumbUrl = $path;
        elseif (str_starts_with($path, 'storage/')) $thumbUrl = asset($path);
        elseif (str_starts_with($path, 'uploads/')) $thumbUrl = asset($path);
        else $thumbUrl = asset('storage/' . ltrim($path, '/'));
    }

    $isPaused = (string) ($course->status ?? '') === 'paused';
    $authorName = $course->author_name ?? optional($course->creator)->name ?? 'UNN Academy';
    $firstLesson = $course->lessons->first();

    $selectedRating = old('rating', optional($myReview)->rating);
    $selectedRating = is_numeric($selectedRating) ? max(1, min(5, (int) $selectedRating)) : null;
@endphp
<div class="bg-gray-50 min-h-screen pb-12">
    <div class="bg-[#1F5EDB] text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row gap-8">
            <div class="flex-1">
                <nav class="text-blue-200 text-sm mb-4">
                    <a href="{{ route('courses.index') }}" class="hover:text-white">Cursos</a> /
                    <span class="text-white">{{ $course->title }}</span>
                </nav>
                <h1 class="text-3xl md:text-4xl font-bold mb-4">{{ $course->title }}</h1>
                <p class="text-lg text-blue-100 max-w-2xl mb-6">{{ $course->short_description }}</p>

                <div class="flex items-center gap-6 text-sm">
                    <span>Criado por <strong>{{ $authorName }}</strong></span>
                    <span><i class="far fa-clock mr-1"></i> {{ $course->duration }} min</span>
                    <span><i class="far fa-calendar-alt mr-1"></i> {{ $course->created_at->format('d/m/Y') }}</span>
                </div>
            </div>

            <div class="md:w-1/3">
                <div class="bg-white rounded-lg shadow-xl overflow-hidden text-gray-900 p-1">
                    @if($thumbUrl)
                        <img src="{{ $thumbUrl }}" class="w-full h-48 object-cover rounded-t-lg" alt="{{ $course->title }}">
                    @endif
                    <div class="p-6">
                        @if($isEnrolled)
                            <div class="text-center">
                                <span class="block text-sm text-green-600 font-bold mb-2">Você já possui este curso!</span>
                                @if($firstLesson)
                                    <a href="{{ route('courses.lessons.show', [$course->id, $firstLesson->id]) }}" class="block w-full py-3 bg-green-600 hover:bg-green-700 text-white font-bold text-center rounded-lg transition">
                                        Continuar estudando
                                    </a>
                                @else
                                    <button type="button" disabled class="block w-full py-3 bg-gray-200 text-gray-500 font-bold text-center rounded-lg cursor-not-allowed">
                                        Curso sem aulas
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="text-3xl font-bold text-gray-900 mb-4">{{ $course->price > 0 ? 'R$ ' . number_format($course->price, 2, ',', '.') : 'Gratuito' }}</div>
                            @if($isPaused)
                                <button type="button" disabled class="block w-full py-3 bg-gray-200 text-gray-500 font-bold rounded-lg transition mb-3 cursor-not-allowed">
                                    Vendas pausadas
                                </button>
                                <p class="text-xs text-gray-500 text-center">Este curso está publicado, mas as vendas estão pausadas no momento.</p>
                            @else
                                <a href="{{ route('checkout.show', $course->id) }}" class="block w-full py-3 bg-[#1F5EDB] hover:bg-blue-700 text-white font-bold rounded-lg transition mb-3 text-center">
                                    Comprar agora
                                </a>
                                <p class="text-xs text-gray-500 text-center">Pagamento seguro e liberação automática após confirmação.</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
                <h2 class="text-2xl font-bold mb-4">Sobre o curso</h2>
                <div class="prose max-w-none text-gray-600">
                    {!! \App\Support\RichText::toHtml($course->full_description) !!}
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
                <h2 class="text-2xl font-bold mb-6">Conteúdo do curso</h2>
                <div class="border rounded-lg divide-y">
                    @forelse($course->lessons as $lesson)
                        <div class="p-4 hover:bg-gray-50 flex items-center justify-between transition group">
                            <div class="flex items-center gap-3">
                                @if($isEnrolled || $lesson->is_free_preview)
                                    <i class="fas fa-play-circle text-[#1F5EDB] text-xl"></i>
                                @else
                                    <i class="fas fa-lock text-gray-400"></i>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $lesson->order }}. {{ $lesson->title }}</p>
                                    @if($lesson->is_free_preview && !$isEnrolled)
                                        <span class="text-xs text-green-600 font-semibold bg-green-100 px-2 py-0.5 rounded">Aula grátis</span>
                                    @endif
                                </div>
                            </div>

                            @if($isEnrolled || $lesson->is_free_preview)
                                <a href="{{ route('courses.lessons.show', [$course->id, $lesson->id]) }}" class="text-sm font-semibold text-[#1F5EDB] opacity-0 group-hover:opacity-100 transition">
                                    Assistir @if($lesson->duration) <span class="text-gray-400 font-normal ml-1">({{ gmdate('H:i', $lesson->duration) }})</span> @endif
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-500">Nenhuma aula cadastrada ainda.</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Avaliações</h2>
                        <p class="text-sm text-gray-500">Comentários dos alunos sobre este curso.</p>
                    </div>
                    @if($reviewsCount > 0)
                        <div class="inline-flex items-center gap-2 rounded-full bg-amber-50 text-amber-700 px-4 py-2 text-sm font-semibold">
                            <i class="fas fa-star"></i>
                            {{ number_format((float) $reviewsAvg, 1, ',', '.') }}/5 ({{ $reviewsCount }} {{ $reviewsCount === 1 ? 'avaliação' : 'avaliações' }})
                        </div>
                    @endif
                </div>

                @if($myReview)
                    <div class="mb-4 rounded-lg px-4 py-3 border {{ $myReview->status === 'approved' ? 'bg-green-50 border-green-200 text-green-700' : ($myReview->status === 'rejected' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-yellow-50 border-yellow-200 text-yellow-700') }}">
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
                                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center shrink-0 overflow-hidden">
                                        @if(!empty($review->user->photo))
                                            <img src="{{ $review->user->profile_photo_url ?? '' }}" alt="Foto de {{ $review->user->name ?? 'Usuário' }}" class="w-full h-full object-cover rounded-full">
                                        @else
                                            {{ strtoupper(mb_substr((string) ($review->user->name ?? 'U'), 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 truncate">{{ $review->user->name ?? 'Usuário' }}</div>
                                        <div class="text-xs text-gray-500">{{ optional($review->created_at)->format('d/m/Y') }}</div>
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
                        <div class="md:col-span-2 rounded-lg border border-dashed border-slate-300 px-4 py-6 text-center text-slate-500">
                            Este curso ainda não possui avaliações aprovadas.
                        </div>
                    @endforelse
                </div>

                @auth
                    <form method="POST" action="{{ route('courses.reviews.store', $course->id) }}" class="border border-slate-200 rounded-xl p-5">
                        @csrf
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Envie sua avaliação</h3>
                        <p class="text-sm text-gray-500 mb-4">Sua avaliação será moderada antes de aparecer na página.</p>

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Nota</label>
                            <div class="unn-star-rating" role="radiogroup" aria-label="Avaliação por estrelas">
                                @for($i = 5; $i >= 1; $i--)
                                    <input type="radio" id="course-rating-{{ $i }}" name="rating" value="{{ $i }}" {{ (string) $selectedRating === (string) $i ? 'checked' : '' }}>
                                    <label for="course-rating-{{ $i }}" title="{{ $i }} de 5"><i class="fas fa-star"></i></label>
                                @endfor
                            </div>
                            @error('rating')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="course-review-comment" class="block text-sm font-semibold text-gray-800 mb-2">Comentário</label>
                            <textarea id="course-review-comment" name="comment" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Conte como foi sua experiência com este curso...">{{ old('comment', optional($myReview)->comment) }}</textarea>
                            @error('comment')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#1F5EDB] hover:bg-blue-700 text-white font-semibold px-5 py-2.5 transition">
                            <i class="fas fa-paper-plane"></i>
                            {{ $myReview ? 'Atualizar avaliação' : 'Enviar avaliação' }}
                        </button>
                    </form>
                @else
                    <div class="rounded-lg border border-dashed border-slate-300 px-4 py-5 text-sm text-slate-600">
                        Faça <a href="{{ route('login') }}" class="text-blue-600 font-semibold">login</a> para avaliar este curso.
                    </div>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
