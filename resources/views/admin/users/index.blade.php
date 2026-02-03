@extends('admin.layouts.app')

@section('page_title','Usuários')
@section('breadcrumb')<li class="breadcrumb-item active">Usuários</li>@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0"><i class="fas fa-users-cog mr-2"></i>Gerenciar usuários</h3>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary" data-pjax="true">Novo</a>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead><tr><th>Nome</th><th>E-mail</th><th>Papel</th><th>Nível</th><th class="text-right" style="width:140px;">Ações</th></tr></thead>
      <tbody>
        @forelse($users as $user)
        <tr>
          <td>{{ $user->name }}</td>
          <td>{{ $user->email }}</td>
          <td>{{ $user->role ?? '-' }}</td>
          <td>{{ $user->level ?? '-' }}</td>
          <td class="text-right">
            @if(auth()->user()->role === 'superadmin' && $user->id !== auth()->id())
                <a href="{{ route('admin.users.impersonate', $user) }}" class="btn btn-sm btn-outline-warning" title="Acessar como usuário" data-pjax="false"><i class="fas fa-user-secret"></i></a>
            @endif
            <a href="{{ route('admin.users.edit',$user) }}" class="btn btn-sm btn-outline-secondary" data-pjax="true"><i class="fas fa-edit"></i></a>
            <button class="btn btn-sm btn-outline-danger btn-delete" data-action="{{ route('admin.users.destroy',$user) }}"><i class="fas fa-trash"></i></button>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center text-muted">Nenhum usuário cadastrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">{{ $users->links() }}</div>
</div>
@endsection
