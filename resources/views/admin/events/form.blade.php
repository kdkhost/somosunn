@extends('admin.layouts.app')

@section('page_title', $event->exists ? 'Editar Evento' : 'Novo Evento')

@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $event->exists ? route('admin.events.update',$event) : route('admin.events.store') }}">
        @csrf
        @if($event->exists) @method('PUT') @endif
        <div class="form-group mb-2"><label>Título</label><input name="title" class="form-control" value="{{ old('title',$event->title) }}" required></div>
        <div class="form-group mb-2"><label>Início</label><input name="start_at" type="datetime-local" class="form-control" value="{{ old('start_at',$event->start_at) }}"></div>
        <div class="form-group mb-2"><label>Preço</label><input name="price" class="form-control" value="{{ old('price',$event->price) }}"></div>
        <div class="form-check mb-2"><input type="checkbox" name="published" value="1" class="form-check-input" {{ $event->published ? 'checked' : '' }}><label class="form-check-label">Publicado</label></div>
        <button class="btn btn-primary">Salvar</button>
    </form>
</div></div>
@endsection