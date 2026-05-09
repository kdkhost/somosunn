@extends('admin.layouts.app')

@section('page_title', $role->exists ? 'Editar Papel' : 'Novo Papel')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissões</a></li>
<li class="breadcrumb-item active">{{ $role->exists ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
    @php
        $categoryIcons = [
            'Dashboard' => 'fa-tachometer-alt', 'dashboard' => 'fa-tachometer-alt',
            'Usuários' => 'fa-users', 'users' => 'fa-users',
            'Cursos' => 'fa-graduation-cap', 'courses' => 'fa-graduation-cap',
            'Mentorias' => 'fa-chalkboard-teacher', 'mentorships' => 'fa-chalkboard-teacher',
            'Eventos' => 'fa-calendar-alt', 'events' => 'fa-calendar-alt',
            'Planos' => 'fa-gem', 'plans' => 'fa-gem',
            'Vendas' => 'fa-shopping-cart', 'orders' => 'fa-shopping-cart',
            'Faturas' => 'fa-file-invoice-dollar', 'invoices' => 'fa-file-invoice-dollar',
            'Cupons' => 'fa-ticket-alt', 'coupons' => 'fa-ticket-alt',
            'Certificados' => 'fa-award', 'certificates' => 'fa-award',
            'Pontuação' => 'fa-star', 'points' => 'fa-star', 'ranking' => 'fa-trophy',
            'Comunidade' => 'fa-users-cog', 'community' => 'fa-users-cog',
            'E-mails' => 'fa-envelope', 'mailtemplates' => 'fa-envelope', 'mail' => 'fa-envelope',
            'Depoimentos' => 'fa-quote-left', 'testimonials' => 'fa-quote-left',
            'FAQ' => 'fa-question-circle', 'faq' => 'fa-question-circle',
            'Uploads' => 'fa-cloud-upload-alt', 'uploads' => 'fa-cloud-upload-alt',
            'Pagamentos' => 'fa-credit-card', 'gateways' => 'fa-credit-card',
            'Relatórios' => 'fa-chart-bar', 'reports' => 'fa-chart-bar',
            'Configurações' => 'fa-cog', 'settings' => 'fa-cog',
            'Fontes' => 'fa-font', 'fonts' => 'fa-font',
            'Permissões' => 'fa-shield-alt', 'permissions' => 'fa-shield-alt', 'roles' => 'fa-shield-alt',
        ];
        $categoryColors = [
            'Dashboard' => 'primary', 'dashboard' => 'primary',
            'Usuários' => 'info', 'users' => 'info',
            'Cursos' => 'success', 'courses' => 'success',
            'Mentorias' => 'warning', 'mentorships' => 'warning',
            'Eventos' => 'danger', 'events' => 'danger',
            'Planos' => 'indigo', 'plans' => 'indigo',
            'Vendas' => 'dark', 'orders' => 'dark',
            'Faturas' => 'primary', 'invoices' => 'primary',
            'Cupons' => 'info', 'coupons' => 'info',
            'Certificados' => 'success', 'certificates' => 'success',
            'Pontuação' => 'warning', 'points' => 'warning', 'ranking' => 'warning',
            'Comunidade' => 'danger', 'community' => 'danger',
            'E-mails' => 'secondary', 'mailtemplates' => 'secondary', 'mail' => 'secondary',
            'Depoimentos' => 'dark', 'testimonials' => 'dark',
            'FAQ' => 'primary', 'faq' => 'primary',
            'Uploads' => 'info', 'uploads' => 'info',
            'Pagamentos' => 'success', 'gateways' => 'success',
            'Relatórios' => 'warning', 'reports' => 'warning',
            'Configurações' => 'danger', 'settings' => 'danger',
            'Fontes' => 'secondary', 'fonts' => 'secondary',
            'Permissões' => 'dark', 'permissions' => 'dark', 'roles' => 'dark',
        ];
        $totalPerms = $permissionsGrouped->flatten()->count();
        $selectedCount = $role->exists ? $role->permissions->count() : 0;
    @endphp

    <form method="POST"
        action="{{ $role->exists ? route('admin.permissions.update', $role) : route('admin.permissions.store') }}"
        class="ajax-form">
        @csrf
        @if($role->exists) @method('PUT') @endif

        {{-- Card: Dados do papel --}}
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-user-shield mr-2 text-primary"></i>
                    {{ $role->exists ? 'Editar Papel' : 'Novo Papel' }}
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-tag mr-1 text-muted"></i> Nome (slug)
                            </label>
                            <input name="name" class="form-control" value="{{ old('name', $role->name) }}"
                                required placeholder="ex: editor" {{ in_array($role->name, ['superadmin', 'admin', 'membro']) ? 'readonly' : '' }}>
                            <small class="text-muted">Identificador único do papel (sem espaços ou acentos).</small>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="font-weight-bold">
                                <i class="fas fa-id-badge mr-1 text-muted"></i> Rótulo
                            </label>
                            <input name="label" class="form-control" value="{{ old('label', $role->label) }}"
                                placeholder="ex: Editor de conteúdo">
                            <small class="text-muted">Nome amigável exibido na interface.</small>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center">
                        <div class="text-center w-100">
                            <div class="text-muted mb-1" style="font-size:11px; font-weight:700;">SELECIONADAS</div>
                            <div class="h3 font-weight-black mb-0 text-primary" id="selectedCounter">{{ $selectedCount }}</div>
                            <div class="text-muted" style="font-size:11px;">de {{ $totalPerms }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Permissões --}}
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-key mr-2 text-success"></i> Permissões
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 mr-1" id="selectAll">
                        <i class="fas fa-check-double mr-1"></i> Marcar todas
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" id="deselectAll">
                        <i class="fas fa-times mr-1"></i> Desmarcar todas
                    </button>
                </div>
            </div>

            <div class="card-body pt-0">
                @foreach($permissionsGrouped as $category => $permissions)
                    @php
                        $color = $categoryColors[$category] ?? 'secondary';
                        $icon = $categoryIcons[$category] ?? 'fa-folder';
                        $catPermsCount = $permissions->count();
                        $catSelectedCount = $role->exists ? $role->permissions->whereIn('id', $permissions->pluck('id'))->count() : 0;
                    @endphp
                    <div class="mb-3 rounded border overflow-hidden">
                        {{-- Category header --}}
                        <div class="d-flex align-items-center justify-content-between px-3 py-2"
                            style="background: linear-gradient(135deg, rgba(0,0,0,.02), rgba(0,0,0,.04));">
                            <div class="d-flex align-items-center">
                                <span class="d-inline-flex align-items-center justify-content-center rounded mr-2"
                                    style="width:30px; height:30px; background:var(--{{ $color }}, #6c757d); opacity:.85;">
                                    <i class="fas {{ $icon }} text-white" style="font-size:12px;"></i>
                                </span>
                                <strong style="font-size:13px;">{{ ucfirst($category) }}</strong>
                                <span class="badge badge-{{ $color }} ml-2 rounded-pill" style="font-size:10px;">
                                    {{ $catPermsCount }}
                                </span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-xs btn-light border selectCategory" data-category="{{ $category }}">
                                    <i class="fas fa-check text-success"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-light border deselectCategory" data-category="{{ $category }}">
                                    <i class="fas fa-times text-danger"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Permissions grid --}}
                        <div class="px-3 py-2">
                            <div class="row">
                                @foreach($permissions as $p)
                                    <div class="col-xl-3 col-lg-4 col-md-6 mb-2">
                                        <label class="d-flex align-items-start mb-0 p-2 rounded border cursor-pointer permission-label {{ $role->permissions->contains($p->id) ? 'border-' . $color . ' bg-light' : 'border-transparent' }}"
                                            style="font-size:12px; transition: all .15s ease; cursor:pointer;">
                                            <input type="checkbox" name="permissions[]" value="{{ $p->id }}"
                                                class="mr-2 mt-1 perm-checkbox" data-category="{{ $category }}"
                                                {{ $role->permissions->contains($p->id) ? 'checked' : '' }}
                                                style="accent-color: var(--{{ $color }}, #007bff);">
                                            <span>
                                                <span class="font-weight-bold d-block text-dark">{{ $p->name }}</span>
                                                @if($p->label)
                                                    <small class="text-muted">{{ $p->label }}</small>
                                                @endif
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary rounded-pill px-4" data-pjax="true">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
                <button type="submit" class="btn btn-primary rounded-pill px-4 elevation-1">
                    <i class="fas fa-save mr-1"></i> Salvar Papel
                </button>
            </div>
        </div>
    </form>
@endsection

@push('styles')
<style>
    .permission-label:hover {
        background: #f8f9fa !important;
        border-color: #dee2e6 !important;
    }
    .permission-label input:checked ~ span .font-weight-bold {
        color: #007bff;
    }
    .cursor-pointer { cursor: pointer; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const counter = document.getElementById('selectedCounter');
    const checkboxes = document.querySelectorAll('.perm-checkbox');

    function updateCounter() {
        const count = document.querySelectorAll('.perm-checkbox:checked').length;
        if (counter) counter.textContent = count;
    }

    function updateLabel(cb) {
        const label = cb.closest('.permission-label');
        if (!label) return;
        if (cb.checked) {
            label.classList.add('bg-light');
            label.style.borderColor = '#dee2e6';
        } else {
            label.classList.remove('bg-light');
            label.style.borderColor = 'transparent';
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateCounter();
            updateLabel(this);
        });
    });

    document.getElementById('selectAll').addEventListener('click', function() {
        checkboxes.forEach(cb => { cb.checked = true; updateLabel(cb); });
        updateCounter();
    });

    document.getElementById('deselectAll').addEventListener('click', function() {
        checkboxes.forEach(cb => { cb.checked = false; updateLabel(cb); });
        updateCounter();
    });

    document.querySelectorAll('.selectCategory').forEach(btn => {
        btn.addEventListener('click', function() {
            const cat = this.dataset.category;
            document.querySelectorAll('.perm-checkbox[data-category="'+cat+'"]').forEach(cb => { cb.checked = true; updateLabel(cb); });
            updateCounter();
        });
    });

    document.querySelectorAll('.deselectCategory').forEach(btn => {
        btn.addEventListener('click', function() {
            const cat = this.dataset.category;
            document.querySelectorAll('.perm-checkbox[data-category="'+cat+'"]').forEach(cb => { cb.checked = false; updateLabel(cb); });
            updateCounter();
        });
    });
});
</script>
@endpush
