@extends('admin.layouts.app')

@section('page_title', $role->exists ? 'Editar papel' : 'Novo papel')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissões</a></li>
<li class="breadcrumb-item active">{{ $role->exists ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ $role->exists ? route('admin.permissions.update',$role) : route('admin.permissions.store') }}" class="ajax-form">
        @csrf
        @if($role->exists) @method('PUT') @endif
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Nome (slug)</label>
                    <input name="name" class="form-control" value="{{ old('name',$role->name) }}" required placeholder="ex: editor">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Rótulo</label>
                    <input name="label" class="form-control" value="{{ old('label',$role->label) }}" placeholder="ex: Editor de conteúdo">
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="d-flex justify-content-between align-items-center">
                <span>Permissões</span>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Marcar todas</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">Desmarcar todas</button>
                </div>
            </label>

            @php
                $categoryColors = [
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
                ];
            @endphp

            @foreach($permissionsGrouped as $category => $permissions)
                @php $color = $categoryColors[$category] ?? 'secondary'; @endphp
                <div class="card mb-3 border-{{ $color }}">
                    <div class="card-header bg-{{ $color }} {{ in_array($color, ['warning', 'light']) ? 'text-dark' : 'text-white' }} py-2 d-flex justify-content-between align-items-center">
                        <strong><i class="fas fa-folder mr-2"></i>{{ $category }}</strong>
                        <div>
                            <button type="button" class="btn btn-sm btn-light selectCategory" data-category="{{ $category }}">Marcar</button>
                            <button type="button" class="btn btn-sm btn-outline-light deselectCategory" data-category="{{ $category }}">Desmarcar</button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <div class="row">
                            @foreach($permissions as $p)
                                <div class="col-md-4 col-lg-3 mb-2">
                                    <label class="mb-0 d-flex align-items-start" title="{{ $p->label }}">
                                        <input type="checkbox" name="permissions[]" value="{{ $p->id }}" 
                                            class="mr-2 mt-1 perm-checkbox" data-category="{{ $category }}"
                                            {{ $role->permissions->contains($p->id) ? 'checked' : '' }}>
                                        <span>
                                            <code class="text-{{ $color }}">{{ $p->name }}</code>
                                            <small class="d-block text-muted">{{ $p->label }}</small>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <button class="btn btn-primary"><i class="fas fa-save mr-2"></i>Salvar</button>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary" data-pjax="true">Voltar</a>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('selectAll').addEventListener('click', function() {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
    });
    document.getElementById('deselectAll').addEventListener('click', function() {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
    });
    document.querySelectorAll('.selectCategory').forEach(btn => {
        btn.addEventListener('click', function() {
            const cat = this.dataset.category;
            document.querySelectorAll('.perm-checkbox[data-category="'+cat+'"]').forEach(cb => cb.checked = true);
        });
    });
    document.querySelectorAll('.deselectCategory').forEach(btn => {
        btn.addEventListener('click', function() {
            const cat = this.dataset.category;
            document.querySelectorAll('.perm-checkbox[data-category="'+cat+'"]').forEach(cb => cb.checked = false);
        });
    });
});
</script>
@endpush
