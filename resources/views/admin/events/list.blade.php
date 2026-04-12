@extends('admin.layouts.app')

@section('title', (isset($type) && $type === 'album') ? 'Acervo de Mídia' : 'Gerenciar Eventos')

@section('page_title', (isset($type) && $type === 'album') ? 'Acervo de Mídia' : 'Gerenciar Eventos')

@section('breadcrumb_items')
    <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Eventos</a></li>
    <li class="breadcrumb-item active">{{ (isset($type) && $type === 'album') ? 'Acervo' : 'Gerenciar' }}</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12 text-right">
                <div class="d-inline-flex" style="gap: 10px;">
                    @if(!(isset($type) && $type === 'album'))
                    <a href="{{ route('admin.quick-scanner') }}"
                        class="btn btn-success shadow-sm hover:translate-y-[-2px] transition-all">
                        <i class="fas fa-qrcode mr-2"></i> Scanner Universal
                    </a>
                    @endif
                    <a href="{{ route('admin.events.create', ['type' => $type ?? 'event']) }}"
                        class="btn btn-primary shadow-sm hover:translate-y-[-2px] transition-all">
                        <i class="fas fa-plus mr-2"></i> Novo {{ (isset($type) && $type === 'album') ? 'Álbum' : 'Evento' }}
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-lg border-0">
                    <div class="card-header">
                        <h3 class="card-title">Listagem de {{ (isset($type) && $type === 'album') ? 'Álbuns de Mídia' : 'Eventos' }}</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body p-0">
                        <table id="admin-events-table" class="table table-hover align-middle mb-0 w-100">
                            <thead>
                                <tr>
                                    <th>{{ (isset($type) && $type === 'album') ? 'Álbum' : 'Evento' }}</th>
                                    @if(!(isset($type) && $type === 'album'))
                                    <th>Data/Hora</th>
                                    <th>Local</th>
                                    @endif
                                    <th class="text-center">Visível</th>
                                    <th class="text-center">Galeria</th>
                                    <th class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($event->image)
                                                    <img src="{{ $event->image_url }}" alt="{{ $event->title }}"
                                                        class="img-size-50 mr-3 img-rounded shadow-sm"
                                                        style="object-fit: cover; width: 50px; height: 35px;">
                                                @else
                                                    <div class="img-size-50 mr-3 d-flex align-items-center justify-content-center bg-light img-rounded"
                                                        style="width: 50px; height: 35px;">
                                                        <i class="fas fa-{{ $event->type === 'album' ? 'images' : 'calendar-star' }} text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <span class="font-weight-bold d-block text-truncate" style="max-width: 300px;"
                                                        title="{{ $event->title }}">{{ $event->title }}</span>
                                                    @if($event->type === 'album')
                                                        <small class="badge badge-info uppercase">Acervo</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        @if(!(isset($type) && $type === 'album'))
                                        <td data-order="{{ $event->start_at ? \Carbon\Carbon::parse($event->start_at)->timestamp : 0 }}">
                                            @if($event->start_at)
                                                <div class="d-flex flex-column">
                                                    <span>{{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }}</span>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($event->start_at)->format('H:i') }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">Sem data</span>
                                            @endif
                                        </td>
                                        <td>
                                            <i class="fas fa-map-marker-alt mr-1 text-muted"></i>
                                            {{ $event->location ?: 'Online' }}
                                        </td>
                                        @endif
                                        <td class="text-center">
                                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                <input type="checkbox" class="custom-control-input ajax-toggle" 
                                                       id="toggle-pub-{{ $event->id }}" 
                                                       data-id="{{ $event->id }}" 
                                                       data-field="published"
                                                       {{ $event->published ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="toggle-pub-{{ $event->id }}"></label>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                <input type="checkbox" class="custom-control-input ajax-toggle" 
                                                       id="toggle-gal-{{ $event->id }}" 
                                                       data-id="{{ $event->id }}" 
                                                       data-field="show_on_gallery"
                                                       {{ $event->show_on_gallery ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="toggle-gal-{{ $event->id }}"></label>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.events.edit', ['event' => $event, 'tab' => 'gallery']) }}" class="btn btn-warning"
                                                    title="Gerenciar Mídia">
                                                    <i class="fas fa-photo-video"></i>
                                                </a>
                                                @if($event->is_ticket_enabled)
                                                    <a href="{{ route('admin.events.scanner', $event) }}" class="btn btn-success"
                                                        title="Escanear Ingressos">
                                                        <i class="fas fa-qrcode"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-info"
                                                    title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                                                    class="form-delete"
                                                    data-id="{{ $event->id }}"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-delete" title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                                            <p>Nenhum registro encontrado.</p>
                                            <a href="{{ route('admin.events.create', ['type' => $type ?? 'event']) }}" class="btn btn-primary btn-sm">Criar
                                                meu primeiro registro</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>  <!-- /.card -->
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        .img-rounded {
            border-radius: 4px;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        #admin-events-table_wrapper .row {
            margin-left: 0;
            margin-right: 0;
        }

        #admin-events-table_wrapper .dataTables_length,
        #admin-events-table_wrapper .dataTables_filter {
            padding: 1rem 1rem 0;
        }

        #admin-events-table_wrapper .dataTables_info,
        #admin-events-table_wrapper .dataTables_paginate {
            padding: 1rem;
        }

        #admin-events-table_wrapper .dataTables_filter input,
        #admin-events-table_wrapper .dataTables_length select {
            border-radius: .5rem;
        }

        .admin-events-table__title,
        .admin-events-table__location {
            white-space: normal;
            word-break: break-word;
        }

        #admin-events-table tbody td:last-child {
            white-space: nowrap;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script>
        $(function () {
            const table = $('#admin-events-table').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 15,
                order: [[1, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                columnDefs: [
                    { targets: -1, orderable: false, searchable: false, responsivePriority: 1 },
                    { targets: 0, responsivePriority: 2 }
                ]
            });

            // Handle AJAX Toggle
            $('.ajax-toggle').on('change', function() {
                const $checkbox = $(this);
                const id = $checkbox.data('id');
                const field = $checkbox.data('field');
                const value = $checkbox.is(':checked');

                $checkbox.prop('disabled', true);

                axios.post('{{ route("admin.events.toggle-field", "") }}/' + id, {
                    field: field
                })
                .then(response => {
                    if (response.data.status === 'success') {
                        toastr.success(response.data.message || 'Atualizado com sucesso!');
                    } else {
                        toastr.error(response.data.message || 'Erro ao atualizar.');
                        $checkbox.prop('checked', !value);
                    }
                })
                .catch(error => {
                    console.error(error);
                    toastr.error('Erro de conexão ao servidor.');
                    $checkbox.prop('checked', !value);
                })
                .finally(() => {
                    $checkbox.prop('disabled', false);
                });
            });

            // Handle Delete with SweetAlert2
            $('.btn-delete').on('click', function(e) {
                e.preventDefault();
                const $form = $(this).closest('form');
                const isAlbum = {{ (isset($type) && $type === 'album') ? 'true' : 'false' }};
                const title = isAlbum ? 'Excluir álbum?' : 'Excluir evento?';
                
                Swal.fire({
                    title: title,
                    text: "Esta ação não poderá ser revertida!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $form.submit();
                    }
                });
            });
        });
    </script>
@endpush
