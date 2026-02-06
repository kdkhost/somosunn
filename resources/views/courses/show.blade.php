@extends('layouts.app')

@section('title', $course->title . ' - UNN')

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

            <div class="bg-white rounded-lg shadow-sm p-8">
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
                                        <span class="text-xs text-green-600 font-semibold bg-green-100 px-2 py-0.5 rounded">Aula Grátis</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if($isEnrolled || $lesson->is_free_preview)
                                <a href="{{ route('courses.lessons.show', [$course->id, $lesson->id]) }}" class="text-sm font-semibold text-[#1F5EDB] opacity-0 group-hover:opacity-100 transition">
                                    Assistir  @if($lesson->duration) <span class="text-gray-400 font-normal ml-1">({{ gmdate("H:i", $lesson->duration) }})</span> @endif
                                </a>
                            @endif
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-500">Nenhuma aula cadastrada ainda.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
