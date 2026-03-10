@extends('admin.layouts.app')

@section('title', 'Gerenciar Eventos')

@section('page_title', 'Gerenciar Eventos')

@section('breadcrumb_items')
    <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Eventos</a></li>
    <li class="breadcrumb-item active">Gerenciar</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex flex-wrap" style="gap: 10px;">
                    <a href="{{ route('admin.quick-scanner') }}"
                        class="btn btn-success shadow-sm hover:translate-y-[-2px] transition-all">
                        <i class="fas fa-qrcode mr-2"></i> Scanner Universal
                    </a>
                    <a href="{{ route('admin.events.create') }}"
                        class="btn btn-primary shadow-sm hover:translate-y-[-2px] transition-all">
                        <i class="fas fa-plus mr-2"></i> Novo Evento
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Listagem de Eventos</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body p-0">
                        <table id="admin-events-table" class="table table-hover align-middle mb-0 w-100">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Data/Hora</th>
                                    <th>Local</th>
                                    <th>Preço</th>
                                    <th>Status</th>
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
                                                    <div class="img-size-50 mr-3 d-flex align-items-center justify-center bg-light img-rounded"
                                                        style="width: 50px; height: 35px;">
                                                        <i class="fas fa-calendar-star text-muted"></i>
                                                    </div>
                                                @endif
                                                <span class="font-weight-bold admin-events-table__title"
                                                    title="{{ $event->title }}">{{ Str::limit($event->title, 60) }}</span>
                                            </div>
                                        </td>
                                        <td data-order="{{ \Carbon\Carbon::parse($event->start_at)->timestamp }}">
                                            <div class="d-flex flex-column">
                                                <span>{{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }}</span>
                                                <small
                                                    class="text-muted">{{ \Carbon\Carbon::parse($event->start_at)->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td class="admin-events-table__location">
                                            <i class="fas fa-map-marker-alt mr-1 text-muted"></i>
                                            {{ $event->location ?: 'Online' }}
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">
                                                {{ $event->price > 0 ? 'R$ ' . number_format($event->price, 2, ',', '.') : 'Gratuito' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($event->published)
                                                <span class="badge badge-success">Publicado</span>
                                            @else
                                                <span class="badge badge-secondary">Rascunho</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <div class="btn-group btn-group-sm">
                                                @if($event->is_ticket_enabled)
                                                    <a href="{{ route('admin.events.scanner', $event) }}" class="btn btn-success"
                                                        title="Escanear Ingressos">
                                                        <i class="fas fa-qrcode"></i>
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-light text-muted" title="QR Code desativado" disabled>
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @endif
                                                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-info"
                                                    title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                                                    data-confirm-title="Excluir evento?"
                                                    data-confirm-text="Deseja realmente excluir este evento?"
                                                    data-confirm-icon="warning"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                            <p>Nenhum evento encontrado.</p>
                                            <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm">Criar
                                                meu primeiro evento</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card -->
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
            $('#admin-events-table').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 15,
                order: [[1, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                columnDefs: [
                    { targets: 5, orderable: false, searchable: false, responsivePriority: 1 },
                    { targets: 0, responsivePriority: 2 },
                    { targets: 1, responsivePriority: 3 },
                    { targets: 2, responsivePriority: 4 }
                ]
            });
        });
    </script>
@endpush
