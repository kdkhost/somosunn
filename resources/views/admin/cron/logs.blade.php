@extends('admin.layouts.app')

@section('page_title', 'Logs da Tarefa Agendada')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.cron.index') }}">Cron</a></li>
    <li class="breadcrumb-item active">Logs</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-gradient-primary text-white">
                    <h3 class="card-title">Logs da Tarefa: <code>{{ $task->command }}</code></h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.cron.index') }}" class="btn btn-tool text-white">
                            <i class="fas fa-arrow-left mr-1"></i> Voltar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Execução</th>
                                <th>Sucesso</th>
                                <th>Saída</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->executed_at->format('d/m/Y H:i:s') }}</td>
                                    <td>{!! $log->success ? '<span class="badge badge-success">Sim</span>' : '<span class="badge badge-danger">Não</span>' !!}
                                    </td>
                                    <td>
                                        <pre style="max-width:400px;white-space:pre-wrap;">{{ $log->output }}</pre>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">Nenhum log encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection