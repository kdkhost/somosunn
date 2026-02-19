@extends('admin.layouts.app')

@section('page_title', 'Gerenciamento de Tarefas Agendadas (Cron)')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Cron</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gradient-primary text-white">
                    <h3 class="card-title">Tarefas Agendadas</h3>
                    <a href="{{ route('admin.cron.create') }}" class="btn btn-light float-right">Nova Tarefa</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Comando</th>
                                <th>Frequência</th>
                                <th>Status</th>
                                <th>Última Execução</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $task)
                                <tr>
                                    <td>{{ $task->id }}</td>
                                    <td><code>{{ $task->command }}</code></td>
                                    <td>{{ $task->frequency }}</td>
                                    <td>
                                        @if($task->active)
                                            <span class="badge badge-success">Ativa</span>
                                        @else
                                            <span class="badge badge-secondary">Inativa</span>
                                        @endif
                                    </td>
                                    <td>{{ $task->last_run_at ? $task->last_run_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                    <td>
                                        <form action="{{ route('admin.cron.run', $task) }}" method="POST"
                                            class="d-inline run-cron-form">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-success btn-run-cron"
                                                title="Executar Agora">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.cron.edit', $task) }}" class="btn btn-sm btn-primary"
                                            title="Editar"><i class="fas fa-edit"></i></a>
                                        <a href="{{ route('admin.cron.logs', $task) }}" class="btn btn-sm btn-info"
                                            title="Logs"><i class="fas fa-list"></i></a>
                                        <form action="{{ route('admin.cron.destroy', $task) }}" method="POST"
                                            class="d-inline delete-cron-form">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-cron"
                                                title="Excluir"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Nenhuma tarefa cadastrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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
                        text: "Isso vai forçar a execução imediata deste comando. Pode levar alguns segundos.",
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
                        text: "Você não poderá reverter isso!",
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