@extends('admin.layouts.app')

@section('page_title', 'Logs de Atividade')
@section('breadcrumb_items')
    <li class="breadcrumb-item active">Logs</li>
@endsection

@section('content')
    @php
        $totalLogs = $logs->total();
        $uniqueUsers = $logs->pluck('user_id')->filter()->unique()->count();
        $uniqueActions = $logs->pluck('action')->unique()->count();
    @endphp

    {{-- KPI Cards --}}
    <div class="row mb-4">
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-history"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Total de Registros</span>
                    <span class="info-box-number">{{ $totalLogs }}</span>
                    <span class="progress-description text-xs">Ações registradas no sistema</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-info elevation-1"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Usuários Ativos</span>
                    <span class="info-box-number text-info">{{ $uniqueUsers }}</span>
                    <span class="progress-description text-xs">Com atividade registrada</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-12">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-warning elevation-1"><i class="fas fa-bolt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Tipos de Ação</span>
                    <span class="info-box-number text-warning">{{ $uniqueActions }}</span>
                    <span class="progress-description text-xs">Categorias distintas</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtro + Botao Limpar --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-stream mr-2 text-primary"></i>Histórico de Ações
            </h3>
            <div class="card-tools d-flex align-items-center">
                <form action="{{ route('admin.activity_logs.index') }}" method="GET" class="mr-3">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" name="q" class="form-control" placeholder="Buscar..." value="{{ $q ?? '' }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
                <form action="{{ route('admin.activity_logs.clear') }}" method="POST" id="form-clear-logs" class="d-inline">
                    @csrf
                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 elevation-1 btn-clear-logs">
                        <i class="fas fa-trash-alt mr-1"></i> Limpar Histórico
                    </button>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="border-0 pl-4" style="width:15%;">Data/Hora</th>
                            <th class="border-0" style="width:20%;">Usuário</th>
                            <th class="border-0 text-center" style="width:15%;">Ação</th>
                            <th class="border-0" style="width:12%;">IP</th>
                            <th class="border-0">Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="pl-4">
                                    <div class="text-sm font-weight-bold">{{ $log->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    @if($log->user)
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-gradient-primary mr-2"
                                                style="width:32px; height:32px; flex-shrink:0;">
                                                <i class="fas fa-user text-white" style="font-size:12px;"></i>
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.users.edit', $log->user_id) }}" class="font-weight-bold text-sm">
                                                    {{ $log->user->name }}
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-gradient-secondary mr-2"
                                                style="width:32px; height:32px; flex-shrink:0;">
                                                <i class="fas fa-robot text-white" style="font-size:12px;"></i>
                                            </div>
                                            <span class="badge badge-secondary px-2 py-1">Sistema / Visitante</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info px-3 py-2" style="font-size:11px;">
                                        <i class="fas fa-bolt mr-1" style="font-size:9px;"></i>{{ $log->action }}
                                    </span>
                                </td>
                                <td>
                                    <code class="bg-light border rounded px-2 py-1 text-xs">{{ $log->ip_address }}</code>
                                </td>
                                <td>
                                    <span class="text-sm text-muted">{{ $log->description }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fas fa-history fa-3x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted font-weight-bold">Nenhum registro de atividade</h5>
                                    <p class="text-muted">As ações dos usuários serão registradas automaticamente aqui.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($logs->hasPages())
            <div class="card-footer clearfix">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- Info card --}}
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header border-0 py-2">
            <h3 class="card-title text-sm font-weight-bold"><i class="fas fa-info-circle mr-2 text-info"></i>Sobre os Logs de Atividade</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="font-weight-bold mb-2">O que é registrado:</h6>
                    <ul class="list-unstyled mb-0 text-sm text-muted">
                        <li class="mb-1"><i class="fas fa-check text-success mr-2"></i>Login e logout de usuários</li>
                        <li class="mb-1"><i class="fas fa-check text-success mr-2"></i>Criação e edição de registros</li>
                        <li class="mb-1"><i class="fas fa-check text-success mr-2"></i>Exclusão de dados</li>
                        <li class="mb-1"><i class="fas fa-check text-success mr-2"></i>Alterações de permissões</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold mb-2">Retenção:</h6>
                    <ul class="list-unstyled mb-0 text-sm text-muted">
                        <li class="mb-1"><i class="fas fa-info-circle text-info mr-2"></i>Logs são mantidos indefinidamente</li>
                        <li class="mb-1"><i class="fas fa-exclamation-triangle text-warning mr-2"></i>Limpar histórico é irreversível</li>
                        <li class="mb-1"><i class="fas fa-shield-alt text-primary mr-2"></i>IPs são registrados para auditoria</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .align-middle td { vertical-align: middle !important; }
    </style>
@endpush

@push('scripts')
    <script>
        $(function () {
            $('.btn-clear-logs').on('click', function() {
                Swal.fire({
                    title: 'Limpar Histórico?',
                    text: "Esta ação é irreversível e apagará todos os registros de atividade de todos os usuários!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, limpar tudo!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#form-clear-logs').submit();
                    }
                });
            });
        });
    </script>
@endpush
