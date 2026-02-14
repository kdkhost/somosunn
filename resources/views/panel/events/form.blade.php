@extends('member.layout')
@section('title', isset($event) && $event->id ? 'Editar Evento' : 'Novo Evento')
@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">{{ isset($event) && $event->id ? 'Editar Evento' : 'Novo Evento' }}</h1>
    <form method="POST" action="{{ isset($event) && $event->id ? route('panel.events.update', $event) : route('panel.events.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if(isset($event) && $event->id)
            @method('PUT')
        @endif
        <div>
            <label class="block text-sm font-semibold mb-1">Título</label>
            <input type="text" name="title" value="{{ old('title', $event->title ?? '') }}" required maxlength="255" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Descrição curta</label>
            <input type="text" name="short_description" value="{{ old('short_description', $event->short_description ?? '') }}" maxlength="500" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Descrição completa</label>
            <textarea name="full_description" rows="5" class="w-full border rounded px-3 py-2">{{ old('full_description', $event->full_description ?? '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Data</label>
            <input type="datetime-local" name="start_at" value="{{ old('start_at', isset($event->start_at) ? $event->start_at->format('Y-m-d\TH:i') : '') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2" required>
                <option value="draft" @if(old('status', $event->status ?? '')=='draft') selected @endif>Rascunho</option>
                <option value="published" @if(old('status', $event->status ?? '')=='published') selected @endif>Publicado</option>
                <option value="archived" @if(old('status', $event->status ?? '')=='archived') selected @endif>Arquivado</option>
                <option value="paused" @if(old('status', $event->status ?? '')=='paused') selected @endif>Pausado</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Imagem</label>
            <input type="file" name="image" accept="image/*" class="w-full border rounded px-3 py-2">
            @if(!empty($event->image))
                <img src="/{{ $event->image }}" alt="Imagem" class="h-20 mt-2">
            @endif
        </div>
        <div class="flex justify-end gap-2">
            <a href="{{ route('panel.events.index') }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-blue-700 text-white rounded hover:bg-blue-800">Salvar</button>
        </div>
    </form>
</div>
@endsection
