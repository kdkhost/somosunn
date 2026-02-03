@extends('layouts.app')

@section('title', 'Cursos - UNN')

@section('content')
<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Cursos</h1>
                <p class="mt-1 text-sm text-gray-500">Aprenda e ensine na nossa comunidade.</p>
            </div>
            @auth
                <a href="{{ route('courses.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Criar Curso
                </a>
            @endauth
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($courses as $course)
                <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-300">
                    <a href="{{ route('courses.show', $course->slug) }}">
                        <div class="h-48 bg-gray-200 relative">
                            @if($course->thumbnail)
                                <img src="{{ asset('storage/'.$course->thumbnail) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="flex items-center justify-center h-full text-gray-400">
                                    <i class="fas fa-image text-4xl"></i>
                                </div>
                            @endif
                            <div class="absolute top-2 right-2 bg-white px-2 py-1 text-xs font-bold rounded shadow">
                                {{ $course->price > 0 ? 'R$ ' . number_format($course->price, 2, ',', '.') : 'Grátis' }}
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900 truncate">{{ $course->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $course->author_name }}</p>
                            <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                                <span><i class="far fa-clock mr-1"></i> {{ $course->duration }} min</span>
                                <span><i class="fas fa-book-open mr-1"></i> {{ $course->lessons->count() }} aulas</span>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg">Nenhum curso disponível no momento.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $courses->links() }}
        </div>
    </div>
</div>
@endsection
