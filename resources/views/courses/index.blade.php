@extends('layouts.app')

@section('title', 'Cursos - UNN')

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-12 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6">
                <span class="text-gradient">Cursos</span> UNN
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Aprenda e ensine na nossa comunidade. Conhecimento que gera resultados.
            </p>
        </div>
    </section>

    <!-- Courses Grid -->
    <section class="pb-24 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            
            @if(session('success'))
                <div class="mb-8 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($courses as $course)
                    @php
                        $isDemo = $course->is_demo ?? false;
                        $authorName = $isDemo ? ($course->creator->name ?? 'UNN Academy') : ($course->author_name ?? optional($course->creator)->name ?? 'Instrutor');
                        $lessonsCount = $isDemo ? 0 : ($course->lessons ? $course->lessons->count() : 0);
                        // Fallback click URL
                        $courseSlug = (!$isDemo && $course->slug) ? route('courses.show', $course->slug) : '#';
                        $courseImage = $course->thumbnail ? asset($course->thumbnail) : null;
                    @endphp
                    <div class="group bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 transform hover:-translate-y-1 {{ $isDemo ? 'opacity-90' : '' }}">
                        <a href="{{ $courseSlug }}" class="block h-full flex flex-col">
                            <!-- Image Container -->
                            <div class="h-56 bg-gray-100 relative overflow-hidden">
                                @if($courseImage)
                                    <img src="{{ $courseImage }}" alt="{{ $course->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="flex items-center justify-center h-full bg-gradient-to-br from-blue-100 to-indigo-50">
                                        <i class="fas fa-graduation-cap text-5xl text-blue-300"></i>
                                    </div>
                                @endif
                                
                                <!-- Badges -->
                                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 text-xs font-bold rounded-full shadow-sm" style="color: var(--unn-azul-1)">
                                    {{ $course->price > 0 ? 'R$ ' . number_format($course->price, 2, ',', '.') : 'Grátis' }}
                                </div>

                                @if($isDemo)
                                    <div class="absolute top-4 left-4 bg-yellow-400 text-yellow-900 px-3 py-1 text-xs font-bold rounded-full shadow-sm">
                                        DEMONSTRAÇÃO
                                    </div>
                                @elseif($course->is_featured)
                                    <div class="absolute top-4 left-4 bg-purple-600 text-white px-3 py-1 text-xs font-bold rounded-full shadow-sm">
                                        DESTAQUE
                                    </div>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="p-8 flex-1 flex flex-col">
                                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2">{{ $course->title }}</h3>
                                
                                <p class="text-sm font-medium mb-4 flex items-center gap-2" style="color: var(--unn-azul-2)">
                                    <i class="fas fa-user-circle"></i> {{ $authorName }}
                                </p>
                                
                                <p class="text-gray-500 text-sm line-clamp-3 mb-6 flex-1 leadinig-relaxed">
                                    {{ $course->short_description ?? 'Sem descrição curta definida.' }}
                                </p>

                                <div class="pt-6 border-t border-gray-100 flex items-center justify-between text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                    <span class="flex items-center gap-1"><i class="far fa-clock"></i> {{ $course->duration ?? 0 }} min</span>
                                    <span class="flex items-center gap-1"><i class="fas fa-book-reader"></i> {{ $lessonsCount }} aulas</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center">
                        <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-chalkboard-teacher text-3xl text-blue-300"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Nenhum curso disponível</h3>
                        <p class="text-gray-500">Volte em breve para conferir novos conteúdos.</p>
                    </div>
                @endforelse
            </div>

            @if(method_exists($courses, 'links'))
            <div class="mt-12">
                {{ $courses->links() }}
            </div>
            @endif
        </div>
    </section>
</div>

<style>
.text-gradient {
    background: linear-gradient(135deg, var(--unn-azul-1) 0%, var(--unn-azul-3) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>
@endsection
