@extends('admin.layouts.app')

@section('page_title', $course->exists ? 'Editar Curso' : 'Novo Curso')

@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $course->exists ? route('admin.courses.update',$course) : route('admin.courses.store') }}">
        @csrf
        @if($course->exists) @method('PUT') @endif
        <div class="form-group mb-2"><label>Título</label><input name="title" class="form-control" value="{{ old('title',$course->title) }}" required></div>
        <div class="form-group mb-2"><label>Descrição</label><textarea name="description" class="form-control summernote">{{ old('description',$course->description) }}</textarea></div>
        <div class="form-group mb-2"><label>Preço</label><input name="price" class="form-control" value="{{ old('price',$course->price) }}"></div>
        <div class="form-check mb-2"><input type="checkbox" name="published" value="1" class="form-check-input" {{ $course->published ? 'checked' : '' }}><label class="form-check-label">Publicado</label></div>
        <button class="btn btn-primary">Salvar</button>
    </form>
</div></div>
@endsection