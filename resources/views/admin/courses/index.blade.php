@extends('admin.layouts.app')

@section('page_title', 'Cursos')
@section('breadcrumb_items')
    <li class="breadcrumb-item active">Cursos</li>
@endsection

@section('content')
    @php
        $totalCourses = $totalCoursesCount ?? ($courses instanceof \Illuminate\Pagination\AbstractPaginator ? $courses->total() : $courses->count());
        $publishedCount = $publishedCoursesCount ?? $courses->where('status', 'published')->count();
        $draftCount = $draftCoursesCount ?? $courses->where('status', 'draft')->count();
    @endphp

    {{-- KPI Cards --}}
    <div class="row mb-4">
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-graduation-cap"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text fw-bold">Total de Cursos</span>
                    <span class="info-box-number">{{ $totalCourses }}</span>
                    <span class="progress-description text-xs">Cadastrados na plataforma</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-sm-6">
            <div class="info-box shadow-sm elevation-1">
                <span class="info-box-icon bg-gradient-success elevation-1"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text fw-bold">Publicados</span>
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
                    <span class="info-box-text fw-bold">Rascunhos</span>
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
            <h3 class="card-title fw-bold">
                <i class="fas fa-book-open me-2 text-primary"></i>Listagem de Cursos
            </h3>
            <div class="card-tools">
                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('courses.create'))
                    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 elevation-1">
                        <i class="fas fa-plus me-1"></i> Novo Curso
                    </a>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="border-0 ps-4" style="width:80px;">Capa</th>
                            <th class="border-0">Título</th>
                            <th class="border-0 text-end">Preço</th>
                            <th class="border-0 text-center">Vendas / Inscritos</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0 text-center" style="width:150px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $c)
                            <tr>
                                <td class="ps-4">
                                    @if($c->thumbnail)
                                        <img src="{{ $c->thumbnail_url }}" alt="Capa" class="img-circle elevation-2"
                                            style="width: 46px; height: 46px; object-fit: cover;" loading="lazy" decoding="async">
                                    @else
                                        <div class="img-circle elevation-2 d-flex align-items-center justify-content-center bg-gradient-secondary"
                                            style="width: 46px; height: 46px;">
                                            <i class="fas fa-image text-white"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-sm">{{ $c->title }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold" style="font-size:14px;">R$ {{ number_format($c->price, 2, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold">{{ (int) ($c->sales_count ?? 0) }} venda(s)</span>
                                        <small class="text-muted">{{ (int) ($c->enrollments_count ?? 0) }} inscrito(s)</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($c->status === 'published')
                                        <span class="badge text-bg-success px-3 py-2" style="font-size:11px;">
                                            <i class="fas fa-check-circle me-1"></i>Publicado
                                        </span>
                                    @elseif($c->status === 'draft')
                                        <span class="badge text-bg-warning px-3 py-2" style="font-size:11px;">
                                            <i class="fas fa-pencil-alt me-1"></i>Rascunho
                                        </span>
                                    @else
                                        <span class="badge text-bg-secondary px-3 py-2" style="font-size:11px;">{{ ucfirst($c->status) }}</span>
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
                                <td colspan="6" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fas fa-graduation-cap fa-3x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted fw-bold">Nenhum curso cadastrado</h5>
                                    <p class="text-muted">Crie o primeiro curso para começar a oferecer conteúdo.</p>
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('courses.create'))
                                        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary rounded-pill px-4 elevation-1">
                                            <i class="fas fa-plus me-1"></i> Novo Curso
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($courses instanceof \Illuminate\Pagination\AbstractPaginator && $courses->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <!-- DataTables Local -->
    <link rel="stylesheet" href="{{ cdn_asset('assets/admin/datatables/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ cdn_asset('assets/admin/datatables/responsive.bootstrap4.min.css') }}">
    <style>
        .align-middle td { vertical-align: middle !important; }
    </style>
@endpush

@push('scripts')
    <!-- DataTables Local -->
    <script src="{{ cdn_asset('assets/admin/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ cdn_asset('assets/admin/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ cdn_asset('assets/admin/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ cdn_asset('assets/admin/datatables/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(function () {
            $("#example1").DataTable({
                "responsive": true,
                "paging": false,
                "info": false,
                "searching": false,
                "autoWidth": false,
                "language": {
                    "url": "{{ cdn_asset('assets/admin/datatables/pt-BR.json') }}"
                }
            });

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
