@extends('admin.layouts.app')

@section('page_title', 'Gerenciamento de Tarefas Agendadas (Cron)')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Cron</li>
@endsection

@push('styles')
    <style>
        .blink_me {
            animation: blinker 2s linear infinite;
        }
        @keyframes blinker {
            50% { opacity: 0.6; }
        }
    </style>
@endpush

@section('content')
    @php
        $lastHeartbeat = \Illuminate\Support\Facades\Cache::get('cron_heartbeat');
        if ($lastHeartbeat && !($lastHeartbeat instanceof \Carbon\Carbon)) {
            try {
                $lastHeartbeat = \Carbon\Carbon::parse($lastHeartbeat);
            } catch (\Exception $e) {
                $lastHeartbeat = null;
            }
        }
        $isRunning = $lastHeartbeat && $lastHeartbeat->diffInMinutes(now()) < 5;
        $activeCount = $tasks->where('active', true)->count();
        $inactiveCount = $tasks->where('active', false)->count();
    @endphp

    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-primary elevation-1">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Tarefas</span>
                    <span class="info-box-number">{{ $tasks->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-success elevation-1">
                <span class="info-box-icon"><i class="fas fa-play-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ativas</span>
                    <span class="info-box-number">{{ $activeCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-warning elevation-1">
                <span class="info-box-icon"><i class="fas fa-pause-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Inativas</span>
                    <span class="info-box-number">{{ $inactiveCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box {{ $isRunning ? 'bg-gradient-info' : 'bg-gradient-danger' }} elevation-1">
                <span class="info-box-icon"><i class="fas fa-heartbeat"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Agendador</span>
                    <span class="info-box-number">{{ $isRunning ? 'Online' : 'Offline' }}</span>
                    @if($isRunning && $lastHeartbeat)
                        <span class="progress-description">Sync: {{ $lastHeartbeat->format('H:i') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-tasks text-primary mr-2"></i>Tarefas Agendadas
            </h3>
            <div class="card-tools">
                @if($isRunning)
                    <span class="badge badge-success p-2 mr-2 blink_me">
                        <i class="fas fa-check-circle mr-1"></i> Sincronizado: {{ $lastHeartbeat->format('H:i') }}
                    </span>
                @else
                    <span class="badge badge-danger p-2 mr-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Agendador nao detectado
                    </span>
                @endif
                <a href="{{ route('admin.cron.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1">
                    <i class="fas fa-plus mr-1"></i> Nova Tarefa
                </a>
                <form action="{{ route('admin.cron.run-all') }}" method="POST" class="d-inline ml-1" onsubmit="return confirm('Executar TODAS as tarefas ativas agora?')">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm rounded-pill elevation-1">
                        <i class="fas fa-play mr-1"></i> Executar Todas
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            @if($tasks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width:50px">ID</th>
                                <th>Comando</th>
                                <th>Frequencia</th>
                                <th class="text-center">Status</th>
                                <th>Ultima Execucao</th>
                                <th>Proxima Execucao</th>
                                <th class="text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                                <tr>
                                    <td><span class="badge badge-light border">{{ $task->id }}</span></td>
                                    <td><code class="text-dark">{{ $task->command }}</code></td>
                                    <td><span class="badge badge-info"><i class="fas fa-redo mr-1"></i>{{ $task->frequency }}</span></td>
                                    <td class="text-center">
                                        @if($task->active)
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Ativa</span>
                                        @else
                                            <span class="badge badge-secondary"><i class="fas fa-pause mr-1"></i>Inativa</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($task->last_run_at)
                                            <small><i class="fas fa-calendar-check text-muted mr-1"></i>{{ $task->last_run_at->format('d/m/Y H:i') }}</small>
                                        @else
                                            <small class="text-muted">Nunca executada</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            try {
                                                $cron = new \Cron\CronExpression($task->frequency);
                                                $nextRun = \Illuminate\Support\Carbon::instance($cron->getNextRunDate());
                                                echo '<small><i class="fas fa-clock text-muted mr-1"></i>' . $nextRun->format('d/m/Y H:i') . '</small>';
                                            } catch (\Exception $e) {
                                                echo '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Invalido</span>';
                                            }
                                        @endphp
                                    </td>
                                    <td class="text-right" style="white-space:nowrap">
                                        <form action="{{ route('admin.cron.run', $task) }}" method="POST"
                                            class="d-inline run-cron-form">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill btn-run-cron"
                                                title="Executar Agora">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.cron.edit', $task) }}" class="btn btn-sm btn-outline-primary rounded-pill"
                                            title="Editar"><i class="fas fa-edit"></i></a>
                                        <a href="{{ route('admin.cron.logs', $task) }}" class="btn btn-sm btn-outline-info rounded-pill"
                                            title="Logs"><i class="fas fa-list"></i></a>
                                        <form action="{{ route('admin.cron.destroy', $task) }}" method="POST"
                                            class="d-inline delete-cron-form">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill btn-delete-cron"
                                                title="Excluir"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-1">Nenhuma tarefa cadastrada.</p>
                    <a href="{{ route('admin.cron.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1 mt-2">
                        <i class="fas fa-plus mr-1"></i> Criar primeira tarefa
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Run Button
            document.querySelectorAll('.btn-run-cron').forEach(btn => {
                btn.addEventListener('click', function () {
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'Executar agora?',
                        text: "Isso vai forcar a execucao imediata deste comando. Pode levar alguns segundos.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sim, executar!',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // Delete Button
            document.querySelectorAll('.btn-delete-cron').forEach(btn => {
                btn.addEventListener('click', function () {
                    const form = this.closest('form');
                    Swal.fire({
                        title: 'Tem certeza?',
                        text: "Voce nao podera reverter isso!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sim, excluir!',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
