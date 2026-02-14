@extends('member.layout')
@section('title', isset($course) && $course->id ? 'Editar Curso' : 'Novo Curso')
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">{{ isset($course) && $course->id ? 'Editar Curso' : 'Novo Curso' }}</h1>
    <form method="POST" action="{{ isset($course) && $course->id ? route('panel.courses.update', $course) : route('panel.courses.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if(isset($course) && $course->id)
            @method('PUT')
        @endif
        <div>
            <label class="block text-sm font-semibold mb-1">Título</label>
            <input type="text" name="title" value="{{ old('title', $course->title ?? '') }}" required maxlength="255" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Descrição curta</label>
            <input type="text" name="short_description" value="{{ old('short_description', $course->short_description ?? '') }}" maxlength="500" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Descrição completa</label>
            <textarea name="full_description" rows="5" class="w-full border rounded px-3 py-2">{{ old('full_description', $course->full_description ?? '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Preço (R$)</label>
            <input type="number" name="price" value="{{ old('price', $course->price ?? '') }}" step="0.01" min="0" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2" required>
                <option value="draft" @if(old('status', $course->status ?? '')=='draft') selected @endif>Rascunho</option>
                <option value="published" @if(old('status', $course->status ?? '')=='published') selected @endif>Publicado</option>
                <option value="archived" @if(old('status', $course->status ?? '')=='archived') selected @endif>Arquivado</option>
                <option value="paused" @if(old('status', $course->status ?? '')=='paused') selected @endif>Pausado</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Thumbnail</label>
            <input type="file" name="thumbnail" accept="image/*" class="w-full border rounded px-3 py-2">
            @if(!empty($course->thumbnail))
                <img src="/{{ $course->thumbnail }}" alt="Thumbnail" class="h-20 mt-2">
            @endif
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('panel.courses.index') }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800">Salvar</button>
        </div>
    </form>
</div>
@endsection
