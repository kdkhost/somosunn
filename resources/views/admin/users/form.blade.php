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
            
            {{-- Lógica de visibilidade: Não exibir papel/nível se estiver editando o próprio perfil --}}
            @if(auth()->id() !== $user->id)
                <div class="form-group col-md-3">
                    <label>Papel</label>
                    <select name="role" class="form-control">
                        <option value="member" {{ (old('role', $user->role) ?? 'member') == 'member' ? 'selected' : '' }}>Membro</option>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                        {{-- Apenas Super Admin pode atribuir papel de Super Admin --}}
                        @if(auth()->user()->role === 'superadmin')
                        <option value="superadmin" {{ old('role', $user->role) == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                        @endif
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Nível</label>
                    <select name="level" class="form-control">
                        @foreach(['iniciante', 'intermediario', 'avancado', 'sucesso', 'superadmin'] as $lvl)
                            <option value="{{ $lvl }}" {{ (old('level', $user->level) ?? 'iniciante') == $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="form-group col-md-6 d-flex align-items-center mt-4">
                    <p class="text-muted mb-0"><i class="fas fa-info-circle mr-1"></i> Papel e Nível não podem ser alterados no próprio perfil.</p>
                </div>
            @endif
        </div>
        <button class="btn btn-primary">Salvar</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary" data-pjax="true">Voltar</a>
    </form>
  </div>
</div>
@endsection
