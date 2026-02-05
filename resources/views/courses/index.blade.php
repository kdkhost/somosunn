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

            {{-- Use logic: If user has 'enrolled' courses. Since the controller passes 'courses' which seems to be ALL
            courses list or Demo list, we need to adapt depending on context.
            If this is 'Meus Cursos' it should filter by acquired. For now, assuming the variable $courses passed contains
            what we want to show.
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
                                        <img src="{{ $courseImage }}" class="card-img-top w-100 h-100" style="object-fit: cover;"
                                            alt="{{ $course->title }}">
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
                                        <span class="small text-muted"><i class="far fa-clock mr-1"></i>
                                            {{ $course->duration ?? 0 }} min</span>

                                        @can('view', $course)
                                            <a href="{{ $courseSlug }}" class="btn btn-primary btn-sm shadow-sm rounded-pill px-3">
                                                Acessar Aula
                                            </a>
                                        @else
                                            <a href="{{ route('checkout.show', $course->id) }}"
                                                class="btn btn-outline-success btn-sm shadow-sm rounded-pill px-3">
                                                <i class="fas fa-shopping-cart mr-1"></i> Adquirir
                                            </a>
                                        @endcan
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
                            <p class="text-muted mb-4 h5 font-weight-light">Invista no seu conhecimento e alcance novos
                                patamares na sua carreira.</p>

                            <a href="{{ route('courses.index') }}?ref=dashboard_empty"
                                class="btn btn-primary btn-lg rounded-pill px-5 shadow hover-lift">
                                <i class="fas fa-plus-circle mr-2"></i> Adquirir Novo Curso
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .transition-hover {
            transition: transform 0.2s;
        }

        .transition-hover:hover {
            transform: translateY(-5px);
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }
    </style>
@endsection