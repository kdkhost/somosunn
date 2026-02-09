@extends('admin.layouts.app')

@section('page_title','Permissões')
@section('breadcrumb')<li class="breadcrumb-item active">Permissões</li>@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

@php
    $categoryColors = [
        // Nomes de categoria quando migration foi rodada
        'Dashboard' => 'primary',
        'Usuários' => 'info',
        'Cursos' => 'success',
        'Mentorias' => 'warning',
        'Eventos' => 'danger',
        'Planos' => 'secondary',
        'Vendas' => 'dark',
        'Faturas' => 'primary',
        'Cupons' => 'info',
        'Certificados' => 'success',
        'Pontuação' => 'warning',
        'Comunidade' => 'danger',
        'E-mails' => 'secondary',
        'Depoimentos' => 'dark',
        'FAQ' => 'primary',
        'Uploads' => 'info',
        'Pagamentos' => 'success',
        'Relatórios' => 'warning',
        'Configurações' => 'danger',
        'Fontes' => 'secondary',
        'Permissões' => 'dark',
        'Outros' => 'light',
        // Prefixos quando migration não foi rodada (fallback)
        'dashboard' => 'primary',
        'users' => 'info',
        'courses' => 'success',
        'mentorships' => 'warning',
        'events' => 'danger',
        'plans' => 'secondary',
        'orders' => 'dark',
        'invoices' => 'primary',
        'coupons' => 'info',
        'certificates' => 'success',
        'points' => 'warning',
        'ranking' => 'warning',
        'community' => 'danger',
        'mailtemplates' => 'secondary',
        'mail' => 'secondary',
        'testimonials' => 'dark',
        'faq' => 'primary',
        'uploads' => 'info',
        'gateways' => 'success',
        'reports' => 'warning',
        'settings' => 'danger',
        'fonts' => 'secondary',
        'permissions' => 'dark',
        'roles' => 'dark',
    ];

    // Mapeia nome da permissão para sua categoria
    $permissionCategories = [];
    foreach($permissions as $p) {
        $permissionCategories[$p->name] = $p->category ?? explode('.', $p->name)[0] ?? 'Outros';
    }
@endphp

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0"><i class="fas fa-user-shield mr-2"></i>Papéis e permissões</h3>
    <div>
        <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary" data-pjax="true"><i class="fas fa-plus mr-1"></i>Novo papel</a>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="thead-light">
        <tr>
            <th style="width:120px">Nome</th>
            <th style="width:150px">Rótulo</th>
            <th>Permissões</th>
            <th style="width:100px" class="text-right">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse($roles as $role)
        <tr>
          <td><code>{{ $role->name }}</code></td>
          <td>{{ $role->label }}</td>
          <td>
            @php
                // Agrupa as permissões do papel por categoria (com fallback para prefixo)
                $rolePermsByCategory = $role->permissions->groupBy(function($p) {
                    return $p->category ?? explode('.', $p->name)[0] ?? 'Outros';
                });
            @endphp
            @foreach($rolePermsByCategory as $category => $perms)
                @php $color = $categoryColors[$category] ?? 'secondary'; @endphp
                <div class="mb-1">
                    <small class="text-muted d-block"><strong>{{ ucfirst($category) }}:</strong></small>
                    @foreach($perms as $p)
                        <span class="badge badge-{{ $color }} mb-1" title="{{ $p->label }}">{{ $p->name }}</span>
                    @endforeach
                </div>
            @endforeach
          </td>
          <td class="text-right">
            <a href="{{ route('admin.permissions.edit',$role) }}" class="btn btn-sm btn-outline-secondary" data-pjax="true" title="Editar"><i class="fas fa-edit"></i></a>
            @if(!in_array($role->name, ['superadmin', 'admin', 'membro']))
            <button class="btn btn-sm btn-outline-danger btn-delete" data-action="{{ route('admin.permissions.destroy',$role) }}" title="Excluir"><i class="fas fa-trash"></i></button>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center text-muted py-4">Nenhum papel cadastrado.</td></tr>
        @endforelse
      </tbody>
    </table>
    </div>
  </div>
  @if($roles->hasPages())
  <div class="card-footer">{{ $roles->links() }}</div>
  @endif
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title mb-0"><i class="fas fa-key mr-2"></i>Legenda de categorias</h3>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($categoryColors as $cat => $color)
            <div class="col-md-3 col-6 mb-2">
                <span class="badge badge-{{ $color }}">{{ $cat }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
