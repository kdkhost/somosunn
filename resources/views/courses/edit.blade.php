@extends('layouts.app')

@section('title', 'Editar Curso - UNN')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-10">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Editar Curso</h1>
                <p class="text-gray-600">{{ $course->title }}</p>
            </div>
            <a href="{{ route('courses.show', $course->slug ?: $course->id) }}"
                class="text-sm text-blue-600 hover:underline">Ver Curso</a>
        </div>

        <form action="{{ route('courses.update', $course->id) }}" method="POST" enctype="multipart/form-data"
            class="bg-white shadow-md rounded-lg p-8 space-y-6">
            @csrf
            @method('PUT')

            {{-- Same fields as create, populated --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                <input type="text" name="title" value="{{ $course->title }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preço (R$)</label>
                    <input type="number" step="0.01" name="price" value="{{ $course->price }}" required
                        class="w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duração (min)</label>
                    <input type="number" name="duration" value="{{ $course->duration }}"
                        class="w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição Curta</label>
                <textarea name="short_description" rows="2"
                    class="w-full rounded-md border-gray-300 shadow-sm">{{ $course->short_description }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição Completa</label>
                @php
                    $fullDescriptionValue = old('full_description', (string) ($course->full_description ?? ''));
                    $fullDescriptionValue = html_entity_decode($fullDescriptionValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                @endphp
                <textarea name="full_description" rows="5"
                    class="w-full rounded-md border-gray-300 shadow-sm">{!! $fullDescriptionValue !!}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="draft" {{ $course->status == 'draft' ? 'selected' : '' }}>Rascunho</option>
                    <option value="published" {{ $course->status == 'published' ? 'selected' : '' }}>Publicado</option>
                    <option value="archived" {{ $course->status == 'archived' ? 'selected' : '' }}>Arquivado</option>
                </select>
            </div>

            <div class="pt-4 border-t flex justify-end">
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 transition">
                    Salvar Alterações
                </button>
            </div>
        </form>

        <div class="mt-8">
            <h2 class="text-xl font-bold mb-4">Gerenciar Aulas</h2>

            <div class="bg-white shadow rounded-lg p-6">
                <form action="{{ route('courses.lessons.store', $course->id) }}" method="POST" class="mb-6 border-b pb-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-1">
                            <label class="text-xs font-bold text-gray-500 uppercase">Ordem</label>
                            <input type="number" name="order" value="{{ $course->lessons->count() + 1 }}"
                                class="w-full rounded text-sm border-gray-300">
                        </div>
                        <div class="md:col-span-5">
                            <label class="text-xs font-bold text-gray-500 uppercase">Título da Aula</label>
                            <input type="text" name="title" required class="w-full rounded text-sm border-gray-300">
                        </div>
                        <div class="md:col-span-4">
                            <label class="text-xs font-bold text-gray-500 uppercase">URL Vídeo</label>
                            <input type="url" name="video_url" class="w-full rounded text-sm border-gray-300">
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit"
                                class="w-full py-2 bg-green-600 text-white text-sm font-bold rounded hover:bg-green-700">
                                + Adicionar
                            </button>
                        </div>
                    </div>
                </form>

                <div class="space-y-2">
                    @foreach($course->lessons()->orderBy('order')->get() as $lesson)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded border border-gray-100">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-gray-500 text-sm">#{{ $lesson->order }}</span>
                                <span class="font-medium">{{ $lesson->title }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('courses.lessons.destroy', [$course->id, $lesson->id]) }}" method="POST"
                                    onsubmit="return confirmAction(event, 'Excluir?', 'A aula será removida permanentemente.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm"><i
                                            class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection