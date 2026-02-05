@extends('admin.layouts.app')

@section('page_title', 'Cursos')
@section('breadcrumb_items')
    <li class="breadcrumb-item active">Cursos</li>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listagem de Cursos</h3>
            <div class="card-tools">
                <a href="{{ route('admin.courses.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Novo curso
                </a>
            </div>
        </div>
        <div class="card-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">Capa</th>
                        <th>Título</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th style="width: 150px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $c)
                        <tr>
                            <td>
                                @if($c->thumbnail)
                                    <img src="{{ asset($c->thumbnail) }}" alt="Capa" class="img-circle elevation-2"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="img-circle elevation-2 d-flex align-items-center justify-content-center bg-secondary"
                                        style="width: 50px; height: 50px;">
                                        <i class="fas fa-image text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $c->title }}</td>
                            <td>{{ number_format($c->price, 2, ',', '.') }}</td>
                            <td>
                                @if($c->status === 'published')
                                    <span class="badge badge-success">Publicado</span>
                                @elseif($c->status === 'draft')
                                    <span class="badge badge-warning">Rascunho</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($c->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.courses.edit', $c) }}" class="btn btn-sm btn-info" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.courses.destroy', $c) }}" style="display:inline"
                                    class="delete-course-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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
                "pageLength": 20,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json"
                },
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

            // SweetAlert2 for delete course confirmation
            $('.delete-course-form').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);

                Swal.fire({
                    title: 'Tem certeza que deseja excluir?',
                    text: "Este curso será removido permanentemente!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.off('submit').submit();
                    }
                });
            });
        });
    </script>
@endpush