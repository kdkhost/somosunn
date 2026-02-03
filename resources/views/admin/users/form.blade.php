@extends('admin.layouts.app')

@section('page_title', $user->exists ? 'Editar usuário' : 'Novo usuário')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Usuários</a></li>
<li class="breadcrumb-item active">{{ $user->exists ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ $user->exists ? route('admin.users.update',$user) : route('admin.users.store') }}" class="ajax-form">
        @csrf
        @if($user->exists) @method('PUT') @endif
        <div class="form-group mb-3"><label>Nome</label><input name="name" class="form-control" value="{{ old('name',$user->name) }}" required></div>
        <div class="form-group mb-3"><label>E-mail</label><input name="email" type="email" class="form-control" value="{{ old('email',$user->email) }}" required></div>
        <div class="form-row">
            <div class="form-group col-md-6"><label>Senha @if($user->exists)<small class="text-muted">(deixe em branco para não alterar)</small>@endif</label><input name="password" type="password" class="form-control"></div>
            <div class="form-group col-md-3"><label>Papel</label><input name="role" class="form-control" value="{{ old('role',$user->role) }}" placeholder="admin / user / superadmin"></div>
            <div class="form-group col-md-3"><label>Nível</label><input name="level" class="form-control" value="{{ old('level',$user->level) }}" placeholder="sucesso / iniciante"></div>
        </div>
        <button class="btn btn-primary">Salvar</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary" data-pjax="true">Voltar</a>
    </form>
  </div>
</div>
@endsection
