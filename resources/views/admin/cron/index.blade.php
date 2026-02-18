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
                                        <a href="{{ route('admin.cron.edit', $task) }}"
                                            class="btn btn-sm btn-primary">Editar</a>
                                        <a href="{{ route('admin.cron.logs', $task) }}" class="btn btn-sm btn-info">Logs</a>
                                        <form action="{{ route('admin.cron.destroy', $task) }}" method="POST" class="d-inline"
                                            onsubmit="return confirmAction(event, 'Excluir esta tarefa?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
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