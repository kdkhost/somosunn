@extends('admin.layouts.app')

@section('title', 'Central de Backups - Superadmin')
@section('page_title', 'Central de Backups')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.settings', ['group' => 'system']) }}">Sistema</a></li>
    <li class="breadcrumb-item active" aria-current="page">Backups</li>
@endsection

@push('styles')
<style>
    .backup-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1.1rem;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #1F5EDB 100%);
        color: #ffffff;
        padding: 1.65rem 1.75rem;
        box-shadow: 0 18px 40px -12px rgba(15, 23, 42, 0.35);
    }

    .backup-hero::after {
        content: '';
        position: absolute;
        right: -60px;
        top: -60px;
        width: 240px;
        height: 240px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.25) 0%, rgba(56, 189, 248, 0) 70%);
        pointer-events: none;
    }

    .backup-hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.38rem 0.8rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #f8fafc;
        backdrop-filter: blur(8px);
    }

    .backup-hero-pill.is-ok {
        background: rgba(16, 185, 129, 0.22);
        border-color: rgba(52, 211, 153, 0.4);
    }

    .backup-hero-pill.is-warn {
        background: rgba(245, 158, 11, 0.22);
        border-color: rgba(251, 191, 36, 0.4);
    }

    .backup-hero-btn {
        border-radius: 0.7rem;
        font-weight: 600;
        padding: 0.55rem 1.05rem;
        transition: all 0.2s ease;
    }

    .backup-hero-btn-light {
        background: #ffffff;
        color: #0f172a;
        border: 1px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 8px 20px -6px rgba(15, 23, 42, 0.35);
    }

    .backup-hero-btn-light:hover {
        background: #f8fafc;
        color: #1F5EDB;
        transform: translateY(-1px);
    }

    .backup-hero-btn-ghost {
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.26);
        backdrop-filter: blur(8px);
    }

    .backup-hero-btn-ghost:hover {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .backup-segmented {
        display: inline-flex;
        padding: 0.28rem;
        border-radius: 0.8rem;
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color);
        gap: 0.25rem;
    }

    .backup-segmented .nav-link {
        border: 0 !important;
        border-radius: 0.6rem !important;
        padding: 0.45rem 0.9rem;
        font-size: 0.84rem;
        font-weight: 600;
        color: var(--bs-secondary-color);
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .backup-segmented .nav-link.active {
        background: var(--unn-gradient) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px -3px rgba(31, 94, 219, 0.45);
    }

    .backup-segmented .nav-link.active .badge {
        background: rgba(255, 255, 255, 0.22) !important;
        color: #ffffff !important;
    }

    .backup-file-icon {
        width: 40px;
        height: 40px;
        border-radius: 0.7rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .backup-file-icon.is-db {
        background: rgba(20, 184, 166, 0.14);
        color: #0d9488;
    }

    .backup-file-icon.is-config {
        background: rgba(139, 92, 246, 0.14);
        color: #7c3aed;
    }

    [data-bs-theme="dark"] .backup-file-icon.is-db {
        background: rgba(20, 184, 166, 0.22);
        color: #5eead4;
    }

    [data-bs-theme="dark"] .backup-file-icon.is-config {
        background: rgba(139, 92, 246, 0.22);
        color: #c4b5fd;
    }

    .backup-path {
        max-width: 340px;
        word-break: break-all;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.74rem;
        color: var(--bs-secondary-color);
    }

    .backup-empty {
        padding: 3rem 1.5rem;
        text-align: center;
    }

    .backup-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 999px;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        background: rgba(31, 94, 219, 0.10);
        color: var(--unn-azul-1);
    }

    .backup-schedule-block {
        border: 1px solid var(--bs-border-color);
        border-radius: 0.85rem;
        padding: 0.95rem 1rem;
        background: var(--bs-tertiary-bg);
        margin-bottom: 0.95rem;
    }

    .backup-disk-filter .btn {
        border-radius: 999px;
        font-size: 0.76rem;
        padding: 0.28rem 0.72rem;
    }
</style>
@endpush

@section('content')
    @php
        $weekdays = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];
        $currentWeekdayLabel = $weekdays[(int) ($backupSettings['backup_config_weekday'] ?? 0)] ?? 'Domingo';
    @endphp

    {{-- Hero Banner Moderno --}}
    <div class="backup-hero mb-4">
        <div class="row align-items-center g-3 position-relative" style="z-index: 2;">
            <div class="col-lg-7">
                <div class="text-uppercase fw-bold mb-1" style="font-size: 0.72rem; letter-spacing: 0.14em; color: #93c5fd;">
                    <i class="fas fa-shield-halved me-1"></i> Segurança &amp; Continuidade • Superadmin
                </div>
                <h2 class="h4 fw-bold text-white mb-2">Backups Automáticos &amp; Recuperação</h2>
                <p class="mb-3 text-white-50" style="max-width: 640px; font-size: 0.9rem;">
                    Gere snapshots sob demanda, gerencie a política de retenção e monitore o armazenamento remoto S3 ou local em tempo real.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="backup-hero-pill {{ $s3Status['configured'] ? 'is-ok' : 'is-warn' }}">
                        <i class="fas {{ $s3Status['configured'] ? 'fa-cloud-arrow-up' : 'fa-hard-drive' }}"></i>
                        {{ $s3Status['configured'] ? 'Amazon S3 Ativo' : 'Modo Fallback Local' }}
                    </span>
                    <span class="backup-hero-pill {{ $backupSettings['backup_database_enabled'] ? 'is-ok' : '' }}">
                        <i class="fas fa-database"></i>
                        Banco: {{ $backupSettings['backup_database_enabled'] ? 'Diário às ' . $backupSettings['backup_database_time'] : 'Manual' }}
                    </span>
                    <span class="backup-hero-pill {{ $backupSettings['backup_config_enabled'] ? 'is-ok' : '' }}">
                        <i class="fas fa-file-zipper"></i>
                        Config: {{ $backupSettings['backup_config_enabled'] ? $currentWeekdayLabel . ' às ' . $backupSettings['backup_config_time'] : 'Manual' }}
                    </span>
                </div>
            </div>
            <div class="col-lg-5 d-flex flex-wrap justify-content-lg-end gap-2">
                <form action="{{ route('admin.backups.run') }}" method="POST" class="d-inline backup-run-form" data-backup-label="do banco de dados">
                    @csrf
                    <input type="hidden" name="type" value="database">
                    <button type="submit" class="btn backup-hero-btn backup-hero-btn-light">
                        <i class="fas fa-database me-2 text-primary"></i>Gerar Backup do Banco
                    </button>
                </form>
                <form action="{{ route('admin.backups.run') }}" method="POST" class="d-inline backup-run-form" data-backup-label="de configurações">
                    @csrf
                    <input type="hidden" name="type" value="config">
                    <button type="submit" class="btn backup-hero-btn backup-hero-btn-ghost">
                        <i class="fas fa-file-archive me-2"></i>Backup de Configurações
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Feedback de sessão --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    {{-- KPI Cards (Padrão Oficial AdminLTE 4 .small-box com animação CountUp) --}}
    <div class="row g-3 mb-2">
        <div class="col-xl-3 col-sm-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Backups Encontrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="small-box-footer">
                    <span>Total consolidado (S3 + Local)</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3>{{ $stats['db_total'] }}</h3>
                    <p>Banco de Dados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="small-box-footer">
                    <span>Retenção: {{ $backupSettings['backup_keep_daily'] }} arquivos</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ $stats['config_total'] }}</h3>
                    <p>Configurações</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-archive"></i>
                </div>
                <div class="small-box-footer">
                    <span>Retenção: {{ $backupSettings['backup_keep_weekly'] }} arquivos</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="small-box bg-navy">
                <div class="inner">
                    <h3>{{ $stats['total_size'] }}</h3>
                    <p>Espaço Ocupado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hdd"></i>
                </div>
                <div class="small-box-footer">
                    <span>Compressão GZIP ativa</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid Principal: Repositório de Backups + Políticas/S3 --}}
    <div class="row g-4">
        {{-- Coluna Esquerda: Lista de Backups --}}
        <div class="col-xl-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <ul class="nav backup-segmented" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-db-btn" data-bs-toggle="tab" data-bs-target="#tab-db" type="button" role="tab" aria-controls="tab-db" aria-selected="true">
                                <i class="fas fa-database"></i>
                                <span>Banco de Dados</span>
                                <span class="badge rounded-pill text-bg-secondary">{{ count($dbBackups) }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-config-btn" data-bs-toggle="tab" data-bs-target="#tab-config" type="button" role="tab" aria-controls="tab-config" aria-selected="false">
                                <i class="fas fa-file-archive"></i>
                                <span>Configurações</span>
                                <span class="badge rounded-pill text-bg-secondary">{{ count($configBackups) }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="btn-group backup-disk-filter" role="group" aria-label="Filtrar por disco">
                            <button type="button" class="btn btn-primary btn-sm" data-disk-filter="all">Todos</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-disk-filter="s3"><i class="fas fa-cloud me-1"></i>S3</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-disk-filter="local"><i class="fas fa-hdd me-1"></i>Local</button>
                        </div>
                        <div class="input-group input-group-sm" style="width: 210px;">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="backupSearchInput" class="form-control" placeholder="Filtrar arquivo...">
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-db" role="tabpanel" aria-labelledby="tab-db-btn">
                            @include('admin.backups.partials.table', ['items' => $dbBackups])
                        </div>
                        <div class="tab-pane fade" id="tab-config" role="tabpanel" aria-labelledby="tab-config-btn">
                            @include('admin.backups.partials.table', ['items' => $configBackups])
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Coluna Direita: Configurações & Armazenamento --}}
        <div class="col-xl-4">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-sliders-h me-2 text-primary"></i>Retenção &amp; Agendamento</h3>
                </div>
                <form action="{{ route('admin.backups.settings') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="backup_keep_daily">Retenção do Banco</label>
                                <div class="input-group">
                                    <input type="number" id="backup_keep_daily" name="backup_keep_daily" class="form-control" min="1" max="365" value="{{ old('backup_keep_daily', $backupSettings['backup_keep_daily']) }}" required>
                                    <span class="input-group-text">arqs</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="backup_keep_weekly">Retenção de Configs</label>
                                <div class="input-group">
                                    <input type="number" id="backup_keep_weekly" name="backup_keep_weekly" class="form-control" min="1" max="104" value="{{ old('backup_keep_weekly', $backupSettings['backup_keep_weekly']) }}" required>
                                    <span class="input-group-text">arqs</span>
                                </div>
                            </div>
                        </div>

                        <div class="backup-schedule-block">
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" name="backup_database_enabled" value="1" class="form-check-input" id="backup_database_enabled" {{ old('backup_database_enabled', $backupSettings['backup_database_enabled']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="backup_database_enabled">Backup automático do banco</label>
                            </div>
                            <div>
                                <label class="form-label small text-secondary" for="backup_database_time">Horário diário (America/Sao_Paulo)</label>
                                <input type="time" id="backup_database_time" name="backup_database_time" class="form-control form-control-sm" value="{{ old('backup_database_time', $backupSettings['backup_database_time']) }}" required>
                            </div>
                        </div>

                        <div class="backup-schedule-block">
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" name="backup_config_enabled" value="1" class="form-check-input" id="backup_config_enabled" {{ old('backup_config_enabled', $backupSettings['backup_config_enabled']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="backup_config_enabled">Backup automático de configurações</label>
                            </div>
                            <div class="row g-2">
                                <div class="col-7">
                                    <label class="form-label small text-secondary" for="backup_config_weekday">Dia da semana</label>
                                    <select id="backup_config_weekday" name="backup_config_weekday" class="form-select form-select-sm">
                                        @foreach($weekdays as $weekday => $label)
                                            <option value="{{ $weekday }}" {{ (int) old('backup_config_weekday', $backupSettings['backup_config_weekday']) === $weekday ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-5">
                                    <label class="form-label small text-secondary" for="backup_config_time">Horário</label>
                                    <input type="time" id="backup_config_time" name="backup_config_time" class="form-control form-control-sm" value="{{ old('backup_config_time', $backupSettings['backup_config_time']) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-check form-switch">
                            <input type="checkbox" name="backup_notify_success" value="1" class="form-check-input" id="backup_notify_success" {{ old('backup_notify_success', $backupSettings['backup_notify_success']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="backup_notify_success">Notificar por e-mail ao concluir com sucesso</label>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="fas fa-save me-1"></i> Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>

            <div class="card card-outline {{ $s3Status['configured'] ? 'card-success' : 'card-warning' }}">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cloud me-2 {{ $s3Status['configured'] ? 'text-success' : 'text-warning' }}"></i>Armazenamento Remoto</h3>
                </div>
                <div class="card-body">
                    @if($s3Status['configured'])
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge rounded-pill text-bg-success"><i class="fas fa-check-circle me-1"></i>S3 Conectado</span>
                        </div>
                        <p class="text-secondary small mb-0">
                            Os snapshots são enviados automaticamente para o bucket S3 configurado, com fallback local em caso de indisponibilidade.
                        </p>
                    @else
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge rounded-pill text-bg-warning"><i class="fas fa-exclamation-triangle me-1"></i>S3 Incompleto</span>
                        </div>
                        <p class="text-secondary small mb-2">
                            Enquanto as credenciais remotas estiverem pendentes, os backups continuam sendo gravados com segurança no disco local do servidor.
                        </p>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            @foreach($s3Status['missing'] as $envKey)
                                <code class="px-2 py-1 rounded small">{{ $envKey }}</code>
                            @endforeach
                        </div>
                        <a href="{{ route('admin.settings', ['group' => 'storage']) }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-sliders-h me-1"></i> Configurar Armazenamento S3
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Filtro em tempo real por texto + disco (S3/Local)
    const searchInput = document.getElementById('backupSearchInput');
    const diskButtons = document.querySelectorAll('[data-disk-filter]');
    let activeDisk = 'all';

    function applyBackupFilters() {
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        document.querySelectorAll('.backup-row').forEach(function (row) {
            const name = (row.getAttribute('data-backup-search') || '').toLowerCase();
            const disk = (row.getAttribute('data-backup-disk') || '').toLowerCase();
            const matchText = !query || name.includes(query);
            const matchDisk = activeDisk === 'all' || disk === activeDisk;
            row.classList.toggle('d-none', !(matchText && matchDisk));
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyBackupFilters);
    }

    diskButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            activeDisk = btn.getAttribute('data-disk-filter') || 'all';
            diskButtons.forEach(function (other) {
                other.classList.remove('btn-primary');
                other.classList.add('btn-outline-secondary');
            });
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-primary');
            applyBackupFilters();
        });
    });

    // Confirmação SweetAlert2 para remoção
    document.querySelectorAll('.backup-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Remover backup?',
                text: 'Esta ação remove permanentemente apenas o arquivo selecionado.',
                showCancelButton: true,
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Confirmação SweetAlert2 para geração sob demanda
    document.querySelectorAll('.backup-run-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const label = form.getAttribute('data-backup-label') || '';

            Swal.fire({
                icon: 'question',
                title: 'Gerar backup ' + label + ' agora?',
                text: 'A rotina pode levar alguns segundos e enviará uma notificação com o resultado.',
                showCancelButton: true,
                confirmButtonText: 'Gerar agora',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#1F5EDB',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Gerando backup...';
                    }
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
