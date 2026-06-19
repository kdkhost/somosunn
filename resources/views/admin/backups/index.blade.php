@extends('admin.layouts.app')

@section('title', 'Backups - Superadmin')

@push('styles')
<style>
    .backup-page-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1e293b;
    }
    .backup-kpi {
        border-radius: 8px;
        padding: 16px;
        color: #fff;
        min-height: 96px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
    }
    .backup-kpi i {
        position: absolute;
        right: 14px;
        top: 16px;
        font-size: 2.2rem;
        opacity: .22;
    }
    .backup-kpi-value {
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .backup-kpi-label {
        font-size: .8rem;
        opacity: .9;
        margin-top: 6px;
    }
    .backup-card {
        border-radius: 8px;
        box-shadow: 0 6px 22px rgba(15, 23, 42, .08);
    }
    .backup-card .card-header {
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    .backup-table th {
        font-size: .78rem;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .backup-table td {
        vertical-align: middle;
        font-size: .88rem;
    }
    .backup-path {
        max-width: 360px;
        word-break: break-all;
        font-family: Consolas, Monaco, monospace;
        font-size: .78rem;
    }
    .backup-actions {
        display: flex;
        gap: 6px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    .backup-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        padding: 28px;
        text-align: center;
        color: #64748b;
    }
    .backup-tabs {
        padding-left: 10px;
        padding-right: 10px;
    }
    .backup-tabs .nav-link {
        border-radius: 8px 8px 0 0;
    }
    .dark-mode .backup-page-title,
    body.dark-mode .backup-page-title {
        color: #f8fafc !important;
    }
    .dark-mode .backup-card,
    body.dark-mode .backup-card {
        box-shadow: none;
    }
    .dark-mode .backup-card .text-muted,
    body.dark-mode .backup-card .text-muted,
    .dark-mode .backup-path.text-muted,
    body.dark-mode .backup-path.text-muted {
        color: #94a3b8 !important;
    }
    .dark-mode .backup-empty,
    body.dark-mode .backup-empty {
        border-color: #475569;
        color: #cbd5e1;
    }
    .dark-mode .backup-tabs,
    body.dark-mode .backup-tabs {
        border-color: #334155;
    }
    .dark-mode .backup-tabs .nav-link,
    body.dark-mode .backup-tabs .nav-link {
        color: #cbd5e1;
        border-color: transparent;
    }
    .dark-mode .backup-tabs .nav-link.active,
    body.dark-mode .backup-tabs .nav-link.active {
        background-color: #1e293b;
        border-color: #334155 #334155 #1e293b;
        color: #ffffff;
    }
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-sm-7">
                <h1 class="m-0 backup-page-title">
                    <i class="fas fa-database text-primary"></i> Backups do Sistema
                </h1>
                <small class="text-muted">Administracao exclusiva do superadmin em /admin</small>
            </div>
            <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
                <form action="{{ route('admin.backups.run') }}" method="POST" class="d-inline backup-run-form">
                    @csrf
                    <input type="hidden" name="type" value="database">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-play mr-1"></i> Gerar backup do banco
                    </button>
                </form>
                <form action="{{ route('admin.backups.run') }}" method="POST" class="d-inline backup-run-form">
                    @csrf
                    <input type="hidden" name="type" value="config">
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-file-archive mr-1"></i> Gerar backup de configuracoes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-1"></i>{{ session('error') }}</div>
        @endif

        <div class="row mb-3">
            <div class="col-lg-3 col-sm-6 mb-2">
                <div class="backup-kpi" style="background:linear-gradient(135deg,#1F5EDB,#177FD6);">
                    <i class="fas fa-layer-group"></i>
                    <div class="backup-kpi-value">{{ $stats['total'] }}</div>
                    <div class="backup-kpi-label">Backups encontrados</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-2">
                <div class="backup-kpi" style="background:linear-gradient(135deg,#0f766e,#14b8a6);">
                    <i class="fas fa-database"></i>
                    <div class="backup-kpi-value">{{ $stats['db_total'] }}</div>
                    <div class="backup-kpi-label">Banco de dados</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-2">
                <div class="backup-kpi" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">
                    <i class="fas fa-file-archive"></i>
                    <div class="backup-kpi-value">{{ $stats['config_total'] }}</div>
                    <div class="backup-kpi-label">Configuracoes</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-2">
                <div class="backup-kpi" style="background:linear-gradient(135deg,#334155,#0f172a);">
                    <i class="fas fa-hdd"></i>
                    <div class="backup-kpi-value">{{ $stats['total_size'] }}</div>
                    <div class="backup-kpi-label">Espaco ocupado</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card backup-card">
                    <div class="card-header p-0 pt-2">
                        <ul class="nav nav-tabs backup-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tab-db" role="tab">
                                    <i class="fas fa-database mr-1"></i> Banco de dados
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-config" role="tab">
                                    <i class="fas fa-file-archive mr-1"></i> Configuracoes
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="tab-db" role="tabpanel">
                                @include('admin.backups.partials.table', ['items' => $dbBackups])
                            </div>
                            <div class="tab-pane fade" id="tab-config" role="tabpanel">
                                @include('admin.backups.partials.table', ['items' => $configBackups])
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card backup-card mb-3">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-sliders-h mr-1"></i> Configuracoes</h3>
                    </div>
                    <form action="{{ route('admin.backups.settings') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label>Retencao banco de dados</label>
                                <div class="input-group">
                                    <input type="number" name="backup_keep_daily" class="form-control" min="1" max="365" value="{{ old('backup_keep_daily', $backupSettings['backup_keep_daily']) }}" required>
                                    <div class="input-group-append"><span class="input-group-text">arquivos</span></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Retencao configuracoes</label>
                                <div class="input-group">
                                    <input type="number" name="backup_keep_weekly" class="form-control" min="1" max="104" value="{{ old('backup_keep_weekly', $backupSettings['backup_keep_weekly']) }}" required>
                                    <div class="input-group-append"><span class="input-group-text">arquivos</span></div>
                                </div>
                            </div>
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" name="backup_database_enabled" value="1" class="custom-control-input" id="backup_database_enabled" {{ old('backup_database_enabled', $backupSettings['backup_database_enabled']) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="backup_database_enabled">Backup automatico do banco</label>
                            </div>
                            <div class="form-group">
                                <label>Horario do backup do banco</label>
                                <input type="time" name="backup_database_time" class="form-control" value="{{ old('backup_database_time', $backupSettings['backup_database_time']) }}" required>
                                <small class="text-muted">Executado no fuso America/Sao_Paulo.</small>
                            </div>
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" name="backup_config_enabled" value="1" class="custom-control-input" id="backup_config_enabled" {{ old('backup_config_enabled', $backupSettings['backup_config_enabled']) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="backup_config_enabled">Backup automatico de configuracoes</label>
                            </div>
                            <div class="form-group">
                                <label>Dia do backup de configuracoes</label>
                                <select name="backup_config_weekday" class="form-control">
                                    @foreach([0 => 'Domingo', 1 => 'Segunda', 2 => 'Terca', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sabado'] as $weekday => $label)
                                        <option value="{{ $weekday }}" {{ (int) old('backup_config_weekday', $backupSettings['backup_config_weekday']) === $weekday ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Horario do backup de configuracoes</label>
                                <input type="time" name="backup_config_time" class="form-control" value="{{ old('backup_config_time', $backupSettings['backup_config_time']) }}" required>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="backup_notify_success" value="1" class="custom-control-input" id="backup_notify_success" {{ old('backup_notify_success', $backupSettings['backup_notify_success']) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="backup_notify_success">Enviar e-mail quando o backup concluir com sucesso</label>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save mr-1"></i> Salvar configuracoes
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card backup-card">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-cloud mr-1"></i> Armazenamento remoto</h3>
                    </div>
                    <div class="card-body">
                        @if($s3Status['configured'])
                            <span class="badge badge-success mb-2"><i class="fas fa-check mr-1"></i> S3 configurado</span>
                            <p class="text-muted mb-0">O sistema tentara gravar no S3 antes de usar o fallback local.</p>
                        @else
                            <span class="badge badge-warning mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> S3 incompleto</span>
                            <p class="text-muted mb-2">Enquanto a configuracao remota estiver incompleta, os backups serao salvos localmente.</p>
                            <small class="d-block text-muted">Pendentes: {{ implode(', ', $s3Status['missing']) }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.backup-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Remover backup?',
                text: 'Esta acao remove apenas o arquivo selecionado.',
                showCancelButton: true,
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    document.querySelectorAll('.backup-run-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            Swal.fire({
                icon: 'question',
                title: 'Gerar backup agora?',
                text: 'A rotina pode levar alguns segundos e enviara e-mail com o resultado.',
                showCancelButton: true,
                confirmButtonText: 'Gerar agora',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#1F5EDB'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
