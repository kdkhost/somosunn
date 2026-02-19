@extends('admin.layouts.app')

@section('page_title', 'Logs de Atividade')
@section('breadcrumb_items')
    <li class="breadcrumb-item active">Logs</li>
@endsection

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history mr-2"></i>Histórico de Ações</h3>
            <div class="card-tools">
                <form action="{{ route('admin.activity_logs.destroy') }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Tem certeza que deseja apagar TODOS os logs histórico? Esta ação não pode ser desfeita.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" title="Limpar todos os registros">
                        <i class="fas fa-trash-alt mr-1"></i> Limpar Logs
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <table id="table_logs" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 15%">Data/Hora</th>
                        <th style="width: 20%">Usuário</th>
                        <th style="width: 15%">Ação</th>
                        <th style="width: 15%">IP</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td data-sort="{{ $log->created_at->format('YmdHis') }}">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td>
                                @if($log->user)
                                    <a href="{{ route('admin.users.edit', $log->user_id) }}" class="font-weight-bold">
                                        {{ $log->user->name }}
                                    </a>
                                @else
                                    <span class="badge badge-secondary">Sistema / Visitante</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $log->action }}</span>
                            </td>
                            <td>{{ $log->ip_address }}</td>
                            <td>
                                @if($log->description == 'User performed an action.')
                                    Usuário realizou uma ação no sistema.
                                @else
                                    {{ $log->description }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@endpush

@push('scripts')
    <!-- DataTables & Plugins -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <script>
        $(function () {
            $("#table_logs").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "pageLength": 20,
                "order": [[0, "desc"]],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
                },
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#table_logs_wrapper .col-md-6:eq(0)');
        });
    </script>
@endpush