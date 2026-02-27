@extends('layouts.app')

@section('title', $course->title . ' - UNN')

@push('styles')
    <style>
        /* Rating styles managed in partials/reviews/form.blade.php */
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
    $currentUser = Auth::user();
    $canAccessByPlan = $currentUser ? $currentUser->canAccessFeature('courses_access') : false;
    $effectivePrice = (float) ($course->effective_price ?? $course->price ?? 0);
    $accessBlocked = session('access_blocked');
    $showAccessModal = is_array($accessBlocked)
        && ($accessBlocked['type'] ?? null) === 'course'
        && (int) ($accessBlocked['course_id'] ?? 0) === (int) $course->id;
    $showAccessModal = $showAccessModal || (bool) request()->query('locked');

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
                            @php
                                $regularPrice = (float) ($course->price ?? 0);
                                $flashActive = method_exists($course, 'isFlashSaleActive') ? (bool) $course->isFlashSaleActive() : false;
                            @endphp
                            <div class="flex items-end gap-3 mb-4">
                                <div class="text-3xl font-bold text-gray-900 whitespace-nowrap leading-none">
                                    {{ $effectivePrice > 0 ? 'R$ ' . number_format($effectivePrice, 2, ',', '.') : 'Gratuito' }}
                                </div>
                                @if($flashActive && $regularPrice > 0 && $effectivePrice < $regularPrice)
                                    <div class="text-sm text-gray-400 line-through mb-1">
                                        {{ 'R$ ' . number_format($regularPrice, 2, ',', '.') }}
                                    </div>
                                @endif
                            </div>
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
                {{-- Certificate Button --}}
                @auth
                    @if($course->isCompletedBy(auth()->user()))
                        @php
                            $userCert = auth()->user()->certificates()->where('course_id', $course->id)->first();
                        @endphp
                        
                        <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 shrink-0">
                                    <i class="fas fa-trophy text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-green-800">Parabéns! Você concluiu este curso.</h3>
                                    <p class="text-sm text-green-700">Seu certificado de conclusão já está disponível.</p>
                                </div>
                            </div>
                            
                            @if($userCert)
                                <a href="{{ route('panel.certificates.download', $userCert->id) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition shadow-sm flex items-center gap-2 whitespace-nowrap">
                                    <i class="fas fa-download"></i> Baixar Certificado
                                </a>
                            @else
                                <form action="{{ route('panel.certificates.generate') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition shadow-sm flex items-center gap-2 whitespace-nowrap">
                                        <i class="fas fa-certificate"></i> Gerar Certificado
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                @endauth

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
                                <a href="{{ route('courses.lessons.show', [$course->id, $lesson->id]) }}" class="text-sm font-semibold text-[#1F5EDB] transition">
                                    Assistir @if($lesson->duration) <span class="text-gray-400 font-normal ml-1">({{ gmdate('H:i', $lesson->duration) }})</span> @endif
                                </a>
                            @else
                                <button type="button"
                                    class="text-sm font-semibold text-slate-500 hover:text-[#1F5EDB] transition inline-flex items-center gap-2"
                                    data-access-modal-trigger>
                                    Desbloquear <i class="fas fa-lock text-xs"></i>
                                </button>
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

                @include('reviews.list', ['reviews' => $reviews])

                @include('reviews.form', [
                    'action' => route('courses.reviews.store', $course->id),
                    'myReview' => $myReview
                ])
            </div>
        </div>
    </div>
</div>

@if($effectivePrice > 0)
    <div id="access-modal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" data-access-modal-close></div>
        <div class="relative w-full max-w-xl rounded-3xl bg-white shadow-2xl border border-slate-100 overflow-hidden">
            <div class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Conteúdo premium</p>
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">
                            Desbloqueie {{ $course->title }}
                        </h3>
                    </div>
                    <button type="button" 
                        class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-red-500 transform hover:scale-125 hover:rotate-90 transition-all duration-300 border-none bg-transparent"
                        data-access-modal-close
                        title="Fechar">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Para acessar este conteúdo, você pode <strong>fazer upgrade do seu plano</strong> e/ou
                        <strong>comprar o curso</strong>.
                        Mesmo com upgrade, cursos pagos exigem compra para liberar o acesso completo.
                    </p>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('premium', ['feature' => 'courses_access']) }}"
                        class="flex-1 px-6 py-3 rounded-xl font-bold border-2 border-slate-200 text-slate-700 hover:bg-slate-50 text-center">
                        Fazer upgrade
                    </a>
                    <a href="{{ route('checkout.show', $course->id) }}"
                        class="flex-1 px-6 py-3 rounded-xl font-bold text-white text-center"
                        style="background: linear-gradient(135deg, #1f5edb, #2f7df6);">
                        Comprar curso
                    </a>
                </div>

                @if($currentUser && $canAccessByPlan)
                    <p class="mt-4 text-xs text-slate-500">
                        Seu plano atual permite acesso a cursos, mas este conteúdo é pago e requer compra.
                    </p>
                @endif
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('access-modal');
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
            document.querySelectorAll('[data-access-modal-close]').forEach((btn) => {
                btn.addEventListener('click', closeModal);
            });

            if (@json($showAccessModal)) {
                openModal();
            }
        })();
    </script>
@endif
@endsection
