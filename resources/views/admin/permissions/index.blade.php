@extends('admin.layouts.app')

@section('page_title','Permissões')
@section('breadcrumb')<li class="breadcrumb-item active">Permissões</li>@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0"><i class="fas fa-user-shield mr-2"></i>Papéis e permissões</h3>
    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary" data-pjax="true">Novo papel</a>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead><tr><th>Nome</th><th>Rótulo</th><th>Permissões</th><th style="width:140px" class="text-right">Ações</th></tr></thead>
      <tbody>
        @forelse($roles as $role)
        <tr>
          <td>{{ $role->name }}</td>
          <td>{{ $role->label }}</td>
          <td>
            @foreach($role->permissions as $p)
              <span class="badge badge-secondary mb-1">{{ $p->name }}</span>
            @endforeach
          </td>
          <td class="text-right">
            <a href="{{ route('admin.permissions.edit',$role) }}" class="btn btn-sm btn-outline-secondary" data-pjax="true"><i class="fas fa-edit"></i></a>
            <button class="btn btn-sm btn-outline-danger btn-delete" data-action="{{ route('admin.permissions.destroy',$role) }}"><i class="fas fa-trash"></i></button>
          </td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center text-muted">Nenhum papel cadastrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">{{ $roles->links() }}</div>
</div>
@endsection
