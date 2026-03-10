@extends('panel.layouts.app')

@section('title', 'Gerenciar Eventos')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.events.index') }}" class="hover:underline">Eventos</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Eventos</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Gerencie todos os eventos e workshops da plataforma.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('panel.admin.events.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-xl text-sm font-semibold text-white hover:bg-blue-700 transition-all shadow-sm shadow-blue-200">
                    <i class="fas fa-plus"></i>
                    <span>Novo Evento</span>
                </a>
                <a href="{{ route('panel.admin.quick-scanner') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 border border-transparent rounded-xl text-sm font-semibold text-white hover:bg-emerald-700 transition-all shadow-sm shadow-emerald-200">
                    <i class="fas fa-qrcode"></i>
                    <span>Scanner Universal</span>
                </a>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="p-4 sm:p-6">
                <table id="panel-events-table" class="w-full text-left border-collapse display">
                    <thead>
                        <tr
                            class="bg-slate-50/50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-bold transition-colors">
                            <th class="px-6 py-4">Evento</th>
                            <th class="px-6 py-4">Data/Hora</th>
                            <th class="px-6 py-4">Local</th>
                            <th class="px-6 py-4">Preço</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($events as $event)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-16 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden border border-slate-200 dark:border-slate-700 shrink-0 transition-colors">
                                            @if($event->image_url)
                                                <img src="{{ $event->image_url }}" alt="{{ $event->title }}"
                                                    class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center bg-slate-50 dark:bg-slate-800 transition-colors">
                                                    <i class="fas fa-calendar-star text-slate-300 dark:text-slate-600"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="max-w-xs font-bold text-slate-900 dark:text-white transition-colors panel-events-table__title" title="{{ $event->title }}">
                                            {{ $event->title }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-order="{{ \Carbon\Carbon::parse($event->start_at)->timestamp }}">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 transition-colors">
                                            {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }}
                                        </span>
                                        <span class="text-[10px] text-slate-500 uppercase font-medium">
                                            {{ \Carbon\Carbon::parse($event->start_at)->format('H:i') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-slate-600 dark:text-slate-400 transition-colors panel-events-table__location">
                                        <i class="fas fa-map-marker-alt mr-1 text-slate-400"></i>
                                        {{ $event->location ?: 'Online' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900 dark:text-white transition-colors">
                                        {{ $event->price > 0 ? 'R$ ' . number_format($event->price, 2, ',', '.') : 'Gratuito' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClass = $event->published 
                                            ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/50' 
                                            : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700';
                                        $statusLabel = $event->published ? 'Publicado' : 'Rascunho';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClass }} transition-colors">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2 text-slate-400 dark:text-slate-500 transition-opacity">
                                        @if($event->is_ticket_enabled)
                                            <a href="{{ route('panel.admin.events.scanner', $event) }}"
                                                class="p-2 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-600 dark:hover:text-emerald-400 rounded-lg transition-colors border border-transparent hover:border-emerald-100 dark:hover:border-emerald-800/50"
                                                title="Escanear Ingressos">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                        @else
                                            <span
                                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-800 text-slate-300 dark:text-slate-600 cursor-not-allowed"
                                                title="QR Code desativado">
                                                <i class="fas fa-ban"></i>
                                            </span>
                                        @endif

                                        <a href="{{ route('panel.admin.events.edit', $event) }}"
                                            class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg transition-colors border border-transparent hover:border-blue-100 dark:hover:border-blue-800/50"
                                            title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('panel.admin.events.destroy', $event) }}" method="POST"
                                              onsubmit="return confirmAction(event, 'Excluir evento?', 'Tem certeza que deseja excluir este evento?');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 hover:text-red-700 dark:hover:text-red-400 rounded-lg transition-colors border border-transparent hover:border-red-100 dark:hover:border-red-800/50 text-slate-400 dark:text-slate-500 hover:text-red-700 dark:hover:text-red-400"
                                                title="Excluir">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-calendar-alt text-slate-300 text-xl"></i>
                                    </div>
                                    <p class="text-sm">Nenhum evento encontrado.</p>
                                    <a href="{{ route('panel.admin.events.create') }}" class="text-blue-600 hover:underline mt-2 inline-block">Criar seu primeiro evento</a>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <style>
        #panel-events-table_wrapper .dataTables_length,
        #panel-events-table_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        #panel-events-table_wrapper .dataTables_filter input,
        #panel-events-table_wrapper .dataTables_length select {
            border-radius: 0.9rem;
            border: 1px solid rgb(226 232 240);
            background: rgb(248 250 252);
            color: rgb(15 23 42);
            padding: 0.55rem 0.85rem;
        }

        .dark #panel-events-table_wrapper .dataTables_filter input,
        .dark #panel-events-table_wrapper .dataTables_length select {
            border-color: rgb(51 65 85);
            background: rgb(15 23 42);
            color: rgb(248 250 252);
        }

        #panel-events-table_wrapper .dataTables_info,
        #panel-events-table_wrapper .dataTables_paginate {
            margin-top: 1rem;
            color: rgb(100 116 139) !important;
        }

        .dark #panel-events-table_wrapper .dataTables_info,
        .dark #panel-events-table_wrapper .dataTables_paginate {
            color: rgb(148 163 184) !important;
        }

        #panel-events-table_wrapper .paginate_button {
            border-radius: 0.85rem !important;
        }

        #panel-events-table_wrapper .paginate_button.current,
        #panel-events-table_wrapper .paginate_button.current:hover {
            background: rgb(37 99 235) !important;
            border-color: rgb(37 99 235) !important;
            color: #fff !important;
        }

        .panel-events-table__title,
        .panel-events-table__location {
            white-space: normal;
            word-break: break-word;
        }

        #panel-events-table td:last-child {
            white-space: nowrap;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script>
        $(function () {
            $('#panel-events-table').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
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
