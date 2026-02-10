@extends('admin.layouts.app')

@section('page_title', 'Logs de Atividade')
@section('breadcrumb_items')
    <li class="breadcrumb-item active">Logs</li>
@endsection

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Histórico de Ações</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>IP</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                @if($log->user)
                                    <a href="{{ route('admin.users.edit', $log->user_id) }}">{{ $log->user->name }}</a>
                                @else
                                    <span class="text-muted">Sistema / Visitante</span>
                                @endif
                            </td>
                            <td><span class="badge badge-info">{{ $log->action }}</span></td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ Str::limit($log->description, 50) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Nenhum registro encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $logs->links() }}
        </div>
    </div>
@endsection