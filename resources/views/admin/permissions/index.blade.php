@extends('admin.layouts.app')

@section('page_title', 'Permissões e Papéis')
@section('breadcrumb')<li class="breadcrumb-item active">Permissões</li>@endsection

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
    @endphp

    {{-- Header com stats --}}
    <div class="row mb-4">
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-user-shield"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Papéis cadastrados</span>
                    <span class="info-box-number">{{ $roles->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-success elevation-1"><i class="fas fa-key"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Permissões disponíveis</span>
                    <span class="info-box-number">{{ $permissions->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-info elevation-1"><i class="fas fa-layer-group"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Categorias</span>
                    <span class="info-box-number">{{ $permissions->groupBy(fn($p) => $p->category ?? explode('.', $p->name)[0])->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Card principal: Papéis --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-user-shield mr-2 text-primary"></i>Papéis do Sistema
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm elevation-1" data-pjax="true">
                    <i class="fas fa-plus mr-1"></i> Novo Papel
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            @forelse($roles as $role)
                <div class="border-bottom px-4 py-3 {{ $loop->last ? 'border-bottom-0' : '' }}">
                    <div class="d-flex align-items-start justify-content-between">
                        {{-- Role info --}}
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-gradient-{{ in_array($role->name, ['superadmin']) ? 'danger' : (in_array($role->name, ['admin']) ? 'primary' : 'secondary') }}"
                                style="width:42px; height:42px; flex-shrink:0;">
                                <i class="fas fa-shield-alt text-white"></i>
                            </div>
                            <div class="ml-3">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="mb-0 font-weight-bold">{{ $role->label ?: $role->name }}</h6>
                                    <code class="ml-2 text-muted" style="font-size:11px;">{{ $role->name }}</code>
                                    @if(in_array($role->name, ['superadmin', 'admin', 'membro']))
                                        <span class="badge badge-light border ml-2" style="font-size:9px;">SISTEMA</span>
                                    @endif
                                </div>
                                <div class="mt-1">
                                    <span class="text-muted" style="font-size:12px;">
                                        <i class="fas fa-key mr-1"></i>{{ $role->permissions->count() }} permissão(ões)
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.permissions.edit', $role) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill px-3" data-pjax="true">
                                <i class="fas fa-edit mr-1"></i> Editar
                            </a>
                            @if(!in_array($role->name, ['superadmin', 'admin', 'membro']))
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-delete"
                                    data-action="{{ route('admin.permissions.destroy', $role) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Permissions grouped by category --}}
                    @if($role->permissions->count() > 0)
                        @php
                            $rolePermsByCategory = $role->permissions->groupBy(fn($p) => $p->category ?? explode('.', $p->name)[0] ?? 'Outros');
                        @endphp
                        <div class="mt-3 ml-5 pl-3">
                            <div class="d-flex flex-wrap gap-2" style="gap:6px;">
                                @foreach($rolePermsByCategory as $category => $perms)
                                    @php
                                        $color = $categoryColors[$category] ?? 'secondary';
                                        $icon = $categoryIcons[$category] ?? 'fa-folder';
                                    @endphp
                                    <div class="d-inline-flex align-items-center rounded-pill border px-2 py-1 mr-1 mb-1"
                                        style="background:rgba(0,0,0,.02); font-size:11px;">
                                        <i class="fas {{ $icon }} text-{{ $color }} mr-1" style="font-size:10px;"></i>
                                        <span class="font-weight-bold text-{{ $color }}">{{ ucfirst($category) }}</span>
                                        <span class="badge badge-{{ $color }} ml-1 rounded-pill" style="font-size:9px; min-width:18px;">{{ $perms->count() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="mt-2 ml-5 pl-3">
                            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Nenhuma permissão atribuída</small>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-user-shield fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">Nenhum papel cadastrado</h5>
                    <p class="text-muted">Crie o primeiro papel para começar a gerenciar permissões.</p>
                    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary" data-pjax="true">
                        <i class="fas fa-plus mr-1"></i> Criar Papel
                    </a>
                </div>
            @endforelse
        </div>

        @if($roles->hasPages())
            <div class="card-footer d-flex justify-content-center border-top">
                {{ $roles->links() }}
            </div>
        @endif
    </div>

    {{-- Card de referência: Todas as permissões por categoria --}}
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-key mr-2 text-secondary"></i>Mapa de Permissões
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            @php
                $allGrouped = $permissions->groupBy(fn($p) => $p->category ?? explode('.', $p->name)[0] ?? 'Outros');
            @endphp
            <div class="row">
                @foreach($allGrouped as $category => $perms)
                    @php
                        $color = $categoryColors[$category] ?? 'secondary';
                        $icon = $categoryIcons[$category] ?? 'fa-folder';
                    @endphp
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card border-left border-{{ $color }} h-100 mb-0" style="border-left-width:3px !important;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded bg-{{ $color }} mr-2"
                                        style="width:28px; height:28px;">
                                        <i class="fas {{ $icon }} text-white" style="font-size:12px;"></i>
                                    </span>
                                    <strong class="text-{{ $color }}" style="font-size:13px;">{{ ucfirst($category) }}</strong>
                                    <span class="badge badge-light border ml-auto" style="font-size:10px;">{{ $perms->count() }}</span>
                                </div>
                                <div class="d-flex flex-wrap" style="gap:4px;">
                                    @foreach($perms as $p)
                                        <span class="badge badge-light border" style="font-size:10px; font-weight:500;" title="{{ $p->label }}">
                                            {{ $p->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .gap-2 { gap: 0.5rem; }
    .gap-3 { gap: 0.75rem; }
</style>
@endpush
