@extends('admin.layouts.app')

@section('title', (isset($type) && $type === 'album') ? 'Acervo de Mídia' : 'Gerenciar Eventos')

@section('page_title', (isset($type) && $type === 'album') ? 'Acervo de Mídia' : 'Gerenciar Eventos')

@section('breadcrumb_items')
    <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Eventos</a></li>
    <li class="breadcrumb-item active">{{ (isset($type) && $type === 'album') ? 'Acervo' : 'Gerenciar' }}</li>
@endsection

@section('content')
    <div class="container-fluid">
        {{-- KPI Cards --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="info-box bg-gradient-primary elevation-1">
                    <span class="info-box-icon"><i class="fas fa-{{ (isset($type) && $type === 'album') ? 'images' : 'calendar-alt' }}"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total</span>
                        <span class="info-box-number">{{ count($events) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="info-box bg-gradient-success elevation-1">
                    <span class="info-box-icon"><i class="fas fa-eye"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Publicados</span>
                        <span class="info-box-number">{{ $events->where('published', true)->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="info-box bg-gradient-warning elevation-1">
                    <span class="info-box-icon"><i class="fas fa-photo-video"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Na Galeria</span>
                        <span class="info-box-number">{{ $events->where('show_on_gallery', true)->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="info-box bg-gradient-secondary elevation-1">
                    <span class="info-box-icon"><i class="fas fa-eye-slash"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Ocultos</span>
                        <span class="info-box-number">{{ $events->where('published', false)->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="row mb-3">
            <div class="col-12 text-right">
                <div class="d-inline-flex" style="gap: 10px;">
                    @if(!(isset($type) && $type === 'album'))
                    <a href="{{ route('admin.quick-scanner') }}"
                        class="btn btn-success rounded-pill elevation-1">
                        <i class="fas fa-qrcode mr-2"></i> Scanner Universal
                    </a>
                    @endif
                    <a href="{{ (isset($type) && $type === 'album') ? route('admin.acervo.create') : route('admin.events.create') }}"
                        class="btn btn-primary rounded-pill elevation-1">
                        <i class="fas fa-plus mr-2"></i> Novo {{ (isset($type) && $type === 'album') ? 'Álbum' : 'Evento' }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-{{ (isset($type) && $type === 'album') ? 'images' : 'calendar-check' }} mr-2"></i>
                            Listagem de {{ (isset($type) && $type === 'album') ? 'Álbuns de Mídia' : 'Eventos' }}
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table id="admin-events-table" class="table table-hover align-middle mb-0 w-100">
                            <thead class="bg-light">
                                <tr>
                                    <th><i class="fas fa-{{ (isset($type) && $type === 'album') ? 'images' : 'calendar-star' }} text-muted mr-1"></i>{{ (isset($type) && $type === 'album') ? 'Álbum' : 'Evento' }}</th>
                                    @if(!(isset($type) && $type === 'album'))
                                    <th><i class="fas fa-clock text-muted mr-1"></i>Data/Hora</th>
                                    <th><i class="fas fa-map-marker-alt text-muted mr-1"></i>Local</th>
                                    @endif
                                    <th class="text-center"><i class="fas fa-eye text-muted mr-1"></i>Visível</th>
                                    <th class="text-center"><i class="fas fa-images text-muted mr-1"></i>Galeria</th>
                                    <th class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($events as $event)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($event->image)
                                                    <img src="{{ $event->image_url }}" alt="{{ $event->title }}"
                                                        class="img-rounded mr-3 shadow-sm"
                                                        style="object-fit: cover; width: 50px; height: 35px; border-radius: 4px;">
                                                @else
                                                    <div class="mr-3 d-flex align-items-center justify-content-center bg-light rounded"
                                                        style="width: 50px; height: 35px;">
                                                        <i class="fas fa-{{ $event->type === 'album' ? 'images' : 'calendar-star' }} text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <span class="font-weight-bold d-block text-truncate" style="max-width: 300px;"
                                                        title="{{ $event->title }}">{{ $event->title }}</span>
                                                    @if($event->type === 'album')
                                                        <span class="badge badge-info"><i class="fas fa-images mr-1"></i>Acervo</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        @if(!(isset($type) && $type === 'album'))
                                        <td data-order="{{ $event->start_at ? \Carbon\Carbon::parse($event->start_at)->timestamp : 0 }}">
                                            @if($event->start_at)
                                                <div class="d-flex flex-column">
                                                    <span class="font-weight-bold">{{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }}</span>
                                                    <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($event->start_at)->format('H:i') }}</small>
                                                </div>
                                            @else
                                                <span class="badge badge-light border"><i class="fas fa-minus mr-1"></i>Sem data</span>
                                            @endif
                                        </td>
                                        <td>
                                            <i class="fas fa-map-marker-alt mr-1 text-danger"></i>
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
                                                <a href="{{ route('admin.events.edit', ['event' => $event, 'tab' => 'gallery']) }}" class="btn btn-outline-warning rounded-left elevation-1"
                                                    title="Gerenciar Mídia">
                                                    <i class="fas fa-photo-video"></i>
                                                </a>
                                                @if($event->is_ticket_enabled)
                                                    <a href="{{ route('admin.events.scanner', $event) }}" class="btn btn-outline-success elevation-1"
                                                        title="Escanear Ingressos">
                                                        <i class="fas fa-qrcode"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-info elevation-1"
                                                    title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                                                    class="form-delete"
                                                    data-id="{{ $event->id }}"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-outline-danger rounded-right elevation-1 btn-delete" title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
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
            const isAlbum = {{ (isset($type) && $type === 'album') ? 'true' : 'false' }};
            const colCount = $('#admin-events-table thead th').length;
            const bodyColCount = $('#admin-events-table tbody tr:first td').length;
            console.log('DataTable Init: isAlbum=', isAlbum, 'thead cols:', colCount, 'tbody first tr cols:', bodyColCount);

            const table = $('#admin-events-table').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 15,
                order: isAlbum ? [[0, 'asc']] : [[1, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json',
                    emptyTable: `
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                            <p class="mb-2">Nenhum registro encontrado.</p>
                            <a href="{{ isset($type) && $type === 'album' ? route('admin.acervo.create') : route('admin.events.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1">
                                <i class="fas fa-plus mr-1"></i> Criar meu primeiro registro
                            </a>
                        </div>
                    `
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
