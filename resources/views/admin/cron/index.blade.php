@extends('admin.layouts.app')

@section('page_title', 'Gerenciamento de Tarefas Agendadas (Cron)')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Cron</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/datatables/responsive.bootstrap4.min.css') }}">
    <style>
        .blink_me {
            animation: blinker 2s linear infinite;
        }
        @keyframes blinker {
            50% { opacity: 0.6; }
        }
        #cron-tasks-table_wrapper .row {
            margin-left: 0;
            margin-right: 0;
        }
        #cron-tasks-table_wrapper .dataTables_length,
        #cron-tasks-table_wrapper .dataTables_filter {
            padding: 1rem 1rem 0;
        }
        #cron-tasks-table_wrapper .dataTables_info,
        #cron-tasks-table_wrapper .dataTables_paginate {
            padding: 1rem;
        }
        #cron-tasks-table_wrapper .dataTables_filter input,
        #cron-tasks-table_wrapper .dataTables_length select {
            border-radius: .5rem;
        }
        #cron-tasks-table td,
        #cron-tasks-table th {
            vertical-align: middle;
        }
        #cron-tasks-table .cron-command {
            display: inline-block;
            max-width: 420px;
            white-space: normal;
            word-break: break-word;
        }
        #cron-tasks-table tbody td:last-child {
            white-space: nowrap;
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
    @endphp

    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-primary elevation-1">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Tarefas</span>
                    <span class="info-box-number">{{ $totalCount }}</span>
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
            <div class="table-responsive">
                <table id="cron-tasks-table" class="table table-hover table-striped align-middle mb-0 w-100">
                    <thead class="bg-light">
                        <tr>
                            <th style="width:50px">ID</th>
                            <th>Comando</th>
                            <th>Frequência</th>
                            <th class="text-center">Status</th>
                            <th>Última Execução</th>
                            <th>Próxima Execução</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/admin/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/admin/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/admin/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/admin/datatables/responsive.bootstrap4.min.js') }}"></script>
    <script>
        $(function () {
            const table = $('#cron-tasks-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'desc']],
                ajax: {
                    url: '{{ route('admin.cron.data') }}',
                    type: 'GET',
                    error: function () {
                        $('#cron-tasks-table tbody').html(`
                            <tr>
                                <td colspan="7" class="py-5 text-center text-danger">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-3 d-block"></i>
                                    Não foi possível carregar as tarefas. Atualize a página e tente novamente.
                                </td>
                            </tr>
                        `);
                        toastr.error('Não foi possível carregar a lista de tarefas agendadas.');
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'command', name: 'command' },
                    { data: 'frequency', name: 'frequency' },
                    { data: 'status', name: 'active', className: 'text-center' },
                    { data: 'last_run_at', name: 'last_run_at' },
                    { data: 'next_run_at', name: 'next_run_at', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right' }
                ],
                language: {
                    url: '{{ asset('assets/admin/datatables/pt-BR.json') }}',
                    emptyTable: `
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-clock fa-3x mb-3 d-block"></i>
                            <p class="mb-2">Nenhuma tarefa cadastrada.</p>
                            <a href="{{ route('admin.cron.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1">
                                <i class="fas fa-plus mr-1"></i> Criar primeira tarefa
                            </a>
                        </div>
                    `
                }
            });

            $('#cron-tasks-table').on('click', '.btn-run-cron', function () {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Executar agora?',
                    text: 'Isso vai forçar a execução imediata deste comando. Pode levar alguns segundos.',
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

            $('#cron-tasks-table').on('click', '.btn-delete-cron', function () {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Tem certeza?',
                    text: 'Você não poderá reverter isso!',
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
    </script>
@endpush
