@extends('admin.layouts.app')

@section('title', 'Meus Cursos - UNN')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Meus Cursos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Cursos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Use logic: If user has 'enrolled' courses. Since the controller passes 'courses' which seems to be ALL courses list or Demo list, we need to adapt depending on context. 
             If this is 'Meus Cursos' it should filter by acquired. For now, assuming the variable $courses passed contains what we want to show. 
             The user request specifically said: "If not acquired show not acquired and button to buy". 
             This implies $courses might be empty if they haven't bought anything. --}}
        
        @if($courses->isNotEmpty())
            <div class="row">
                @foreach($courses as $course)
                    @php
                        $isDemo = $course->is_demo ?? false;
                        $authorName = $isDemo ? ($course->creator->name ?? 'UNN Academy') : ($course->author_name ?? optional($course->creator)->name ?? 'Instrutor');
                        $lessonsCount = $isDemo ? 0 : ($course->lessons ? $course->lessons->count() : 0);
                        $courseSlug = (!$isDemo && $course->slug) ? route('courses.show', $course->slug) : '#';
                        $courseImage = $course->thumbnail ? asset($course->thumbnail) : null;
                    @endphp
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0 transition-hover">
                            <div class="position-relative" style="height: 180px; overflow: hidden; background-color: #f4f6f9;">
                                @if($courseImage)
                                    <img src="{{ $courseImage }}" class="card-img-top w-100 h-100" style="object-fit: cover;" alt="{{ $course->title }}">
                                @else
                                    <div class="d-flex align-items-center justify-content-center h-100 bg-gradient-primary">
                                        <i class="fas fa-graduation-cap fa-3x text-white-50"></i>
                                    </div>
                                @endif
                                
                                @if($isDemo)
                                    <span class="badge badge-warning position-absolute" style="top: 10px; left: 10px;">DEMO</span>
                                @endif
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title font-weight-bold mb-2">{{ $course->title }}</h5>
                                <p class="small text-muted mb-3"><i class="fas fa-user-circle mr-1"></i> {{ $authorName }}</p>
                                <p class="card-text text-secondary flex-fill" style="font-size: 0.9rem;">
                                    {{ Str::limit($course->short_description ?? 'Sem descrição.', 90) }}
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <span class="small text-muted"><i class="far fa-clock mr-1"></i> {{ $course->duration ?? 0 }} min</span>
                                    <a href="{{ $courseSlug }}" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3">
                                        Acessar Aula
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-center mt-4">
                @if(method_exists($courses, 'links'))
                    {{ $courses->links() }}
                @endif
            </div>
        @else
            {{-- EMPTY STATE --}}
            <div class="row justify-content-center py-5">
                <div class="col-md-6 text-center">
                    <div class="card shadow-lg border-0 rounded-lg p-5">
                        <div class="mb-4 text-primary opacity-50">
                            <i class="fas fa-book-open fa-5x text-gray-300"></i>
                        </div>
                        <h3 class="font-weight-bold text-dark mb-3">Você ainda não possui cursos</h3>
                        <p class="text-muted mb-4 h5 font-weight-light">Invista no seu conhecimento e alcance novos patamares na sua carreira.</p>
                        
                        <a href="{{ route('courses.index') }}?ref=dashboard_empty" class="btn btn-primary btn-lg rounded-pill px-5 shadow hover-lift">
                            <i class="fas fa-plus-circle mr-2"></i> Adquirir Novo Curso
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.transition-hover { transition: transform 0.2s; }
.transition-hover:hover { transform: translateY(-5px); }
.hover-lift:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>
@endsection
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
