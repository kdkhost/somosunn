@extends('admin.layouts.app')

@section('page_title', 'Usuários')
@section('breadcrumb')<li class="breadcrumb-item active">Usuários</li>@endsection

@section('content')
    {{-- Toastr global --}}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users-cog mr-2"></i>Gerenciar usuários</h3>
            <div class="card-tools">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm" data-pjax="true">
                    <i class="fas fa-plus"></i> Novo
                </a>
            </div>
        </div>
        <div class="card-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Papel</th>
                        <th>Nível</th>
                        <th class="text-right" style="width:140px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'superadmin')
                                    <span class="badge badge-danger">Super Admin</span>
                                @elseif($user->role === 'admin')
                                    <span class="badge badge-warning">Admin</span>
                                @else
                                    <span class="badge badge-secondary">Membro</span>
                                @endif
                            </td>
                            <td>{{ ucfirst($user->level ?? 'Iniciante') }}</td>
                            <td class="text-right">
                                @if(auth()->user()->isAdmin() && $user->id !== auth()->id() && !session()->has('impersonator_id'))
                                    {{-- Admin pode impersonate membros, Superadmin pode impersonate todos --}}
                                    @if(!$user->isAdmin() || auth()->user()->role === 'superadmin')
                                        <a href="{{ route('admin.users.impersonate', $user) }}" class="btn btn-sm btn-outline-warning"
                                            title="Acessar como usuário" data-pjax="false"><i class="fas fa-user-secret"></i></a>
                                    @endif
                                @endif
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-info" title="Editar"
                                    data-pjax="true"><i class="fas fa-edit"></i></a>
                                <button class="btn btn-sm btn-danger btn-delete" title="Excluir"
                                    data-action="{{ route('admin.users.destroy', $user) }}"><i
                                        class="fas fa-trash"></i></button>
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
            $("#example1").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "pageLength": 15,
                "order": [[0, "asc"]],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
                },
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>
@endpush