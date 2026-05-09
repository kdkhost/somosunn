@extends('admin.layouts.app')

@section('page_title', 'Cursos')
@section('breadcrumb_items')
    <li class="breadcrumb-item active">Cursos</li>
@endsection

@section('content')
    @php
        $totalCourses = $courses->count();
        $publishedCount = $courses->where('status', 'published')->count();
        $draftCount = $courses->where('status', 'draft')->count();
    @endphp

    {{-- KPI Cards --}}
    <div class="row mb-4">
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-graduation-cap"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Total de Cursos</span>
                    <span class="info-box-number">{{ $totalCourses }}</span>
                    <span class="progress-description text-xs">Cadastrados na plataforma</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-success elevation-1"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Publicados</span>
                    <span class="info-box-number text-success">{{ $publishedCount }}</span>
                    <div class="progress progress-xs mt-2">
                        <div class="progress-bar bg-success" style="width: {{ $totalCourses > 0 ? round(($publishedCount / $totalCourses) * 100) : 0 }}%"></div>
                    </div>
                    <span class="progress-description text-xs">Disponíveis para alunos</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-12">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-warning elevation-1"><i class="fas fa-pencil-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Rascunhos</span>
                    <span class="info-box-number text-warning">{{ $draftCount }}</span>
                    <div class="progress progress-xs mt-2">
                        <div class="progress-bar bg-warning" style="width: {{ $totalCourses > 0 ? round(($draftCount / $totalCourses) * 100) : 0 }}%"></div>
                    </div>
                    <span class="progress-description text-xs">Em preparação</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela principal --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-book-open mr-2 text-primary"></i>Listagem de Cursos
            </h3>
            <div class="card-tools">
                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('courses.create'))
                    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 elevation-1">
                        <i class="fas fa-plus mr-1"></i> Novo Curso
                    </a>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="border-0 pl-4" style="width:80px;">Capa</th>
                            <th class="border-0">Título</th>
                            <th class="border-0 text-right">Preço</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0 text-center" style="width:150px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $c)
                            <tr>
                                <td class="pl-4">
                                    @if($c->thumbnail)
                                        <img src="{{ $c->thumbnail_url }}" alt="Capa" class="img-circle elevation-2"
                                            style="width: 46px; height: 46px; object-fit: cover;">
                                    @else
                                        <div class="img-circle elevation-2 d-flex align-items-center justify-content-center bg-gradient-secondary"
                                            style="width: 46px; height: 46px;">
                                            <i class="fas fa-image text-white"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-weight-bold text-sm">{{ $c->title }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="font-weight-bold" style="font-size:14px;">R$ {{ number_format($c->price, 2, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    @if($c->status === 'published')
                                        <span class="badge badge-success px-3 py-2" style="font-size:11px;">
                                            <i class="fas fa-check-circle mr-1"></i>Publicado
                                        </span>
                                    @elseif($c->status === 'draft')
                                        <span class="badge badge-warning px-3 py-2" style="font-size:11px;">
                                            <i class="fas fa-pencil-alt mr-1"></i>Rascunho
                                        </span>
                                    @else
                                        <span class="badge badge-secondary px-3 py-2" style="font-size:11px;">{{ ucfirst($c->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex" style="gap:4px;">
                                        @if(auth()->user()->isAdmin() || (auth()->user()->hasPermission('courses.edit') && $c->user_id === auth()->id()))
                                            <a href="{{ route('admin.courses.edit', $c) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->isAdmin() || (auth()->user()->hasPermission('courses.delete') && $c->user_id === auth()->id()))
                                            <form method="POST" action="{{ route('admin.courses.destroy', $c) }}" style="display:inline"
                                                class="delete-course-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fas fa-graduation-cap fa-3x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted font-weight-bold">Nenhum curso cadastrado</h5>
                                    <p class="text-muted">Crie o primeiro curso para começar a oferecer conteúdo.</p>
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('courses.create'))
                                        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary rounded-pill px-4 elevation-1">
                                            <i class="fas fa-plus mr-1"></i> Novo Curso
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
    <style>
        .align-middle td { vertical-align: middle !important; }
        #example1_wrapper .dataTables_paginate {
            display: flex;
            justify-content: center;
            padding: 0.75rem;
        }
        #example1_wrapper .dataTables_info {
            margin: 0.95rem 0 0 0.75rem;
            color: #6c757d;
            font-size: 0.875rem;
        }
    </style>
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
