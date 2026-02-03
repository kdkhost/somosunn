@extends('admin.layouts.app')

@section('page_title','Gerar Certificado')

@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('admin.certificates.generate') }}">
        @csrf
        <div class="form-group mb-2"><label>Usuário</label><select name="user_id" class="form-control">@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>@endforeach</select></div>
        <div class="form-group mb-2"><label>Curso</label><select name="course_id" class="form-control">@foreach($courses as $c)<option value="{{ $c->id }}">{{ $c->title }}</option>@endforeach</select></div>
        <button class="btn btn-primary">Gerar PDF</button>
    </form>
</div></div>
@endsection