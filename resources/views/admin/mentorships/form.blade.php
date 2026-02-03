@extends('admin.layouts.app')

@section('page_title', $mentorship->exists ? 'Editar Mentoria' : 'Nova Mentoria')

@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $mentorship->exists ? route('admin.mentorships.update',$mentorship) : route('admin.mentorships.store') }}">
        @csrf
        @if($mentorship->exists) @method('PUT') @endif
        <div class="form-group mb-2"><label>Título</label><input name="title" class="form-control" value="{{ old('title',$mentorship->title) }}" required></div>
        <div class="form-group mb-2"><label>Mentor (ID)</label><input name="mentor_id" class="form-control" value="{{ old('mentor_id',$mentorship->mentor_id) }}"></div>
        <div class="form-group mb-2"><label>Preço</label><input name="price" class="form-control" value="{{ old('price',$mentorship->price) }}"></div>
        <div class="form-group mb-2"><label>Vagas</label><input name="slots" class="form-control" value="{{ old('slots',$mentorship->slots) }}"></div>
        <button class="btn btn-primary">Salvar</button>
    </form>
</div></div>
@endsection