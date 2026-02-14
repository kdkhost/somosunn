@extends('member.layout')
@section('title', 'Meus Cursos')
@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Meus Cursos</h1>
    <div class="mb-4 flex justify-end">
        <a href="{{ route('panel.courses.create') }}" class="bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800 transition">Novo Curso</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($courses as $course)
            <div class="bg-white rounded-lg shadow p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-2">
                    <i class="fas fa-graduation-cap text-blue-700 text-2xl"></i>
                    <span class="font-semibold text-lg">{{ $course->title }}</span>
                </div>
                <div class="text-gray-600 mb-2">{{ $course->short_description }}</div>
                <div class="flex-1"></div>
                <div class="flex gap-2 mt-4">
                    <a href="{{ route('panel.courses.edit', $course) }}" class="text-blue-700 hover:underline">Editar</a>
                    <form action="{{ route('panel.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este curso?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline ml-2">Excluir</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-gray-500">Nenhum curso encontrado.</div>
        @endforelse
    </div>
</div>
@endsection
