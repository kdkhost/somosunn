@extends('panel.layouts.app')

@section('title', (isset($type) && $type === 'album') ? 'Acervo de Mídia' : 'Gerenciar Eventos')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.events.index') }}" class="hover:underline">Eventos</a>
    <span class="mx-2 text-slate-300">/</span>
    <span class="text-slate-600 dark:text-slate-400 font-bold">{{ (isset($type) && $type === 'album') ? 'Acervo' : 'Gerenciar' }}</span>
@endsection

@section('panel_content')
    @php
        $panelUser = auth()->user();
        $canCreateEvent = $panelUser && $panelUser->hasPermission('events.create');
        $canEditEvent = $panelUser && $panelUser->hasPermission('events.edit');
        $canDeleteEvent = $panelUser && $panelUser->hasPermission('events.delete');
        $canScanEvents = $panelUser && ($panelUser->hasPermission('events.view') || $panelUser->canAccessFeature('events_access'));
        $canManageExhibitorsFor = fn ($event) => $panelUser
            && method_exists($panelUser, 'canManageEventExhibitors')
            && $panelUser->canManageEventExhibitors($event);
    @endphp
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-slate-800 transition-all duration-500">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white transition-colors">
                    {{ (isset($type) && $type === 'album') ? 'Acervo de Mídia' : 'Eventos' }}
                </h1>
                <p class="text-base text-slate-500 dark:text-slate-400 mt-2 transition-colors">
                    {{ (isset($type) && $type === 'album') ? 'Gerencie seus álbuns de fotos e vídeos de forma rápida e organizada.' : 'Gerencie todos os eventos e workshops da plataforma.' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                @if(!(isset($type) && $type === 'album') && $canScanEvents)
                <a href="{{ route('panel.admin.quick-scanner') }}"
                    class="group inline-flex items-center gap-2.5 px-6 py-3.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl text-sm font-black transition-all duration-300 shadow-lg shadow-emerald-200/50 dark:shadow-none hover:scale-105 active:scale-95">
                    <i class="fas fa-qrcode text-lg transition-transform group-hover:rotate-12"></i>
                    <span>Scanner Universal</span>
                </a>
                @endif
                @if($canCreateEvent)
                <a href="{{ (isset($type) && $type === 'album') ? route('panel.admin.acervo.create') : route('panel.admin.events.create') }}"
                    class="group inline-flex items-center gap-2.5 px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-sm font-black transition-all duration-300 shadow-lg shadow-blue-200/50 dark:shadow-none hover:scale-105 active:scale-95">
                    <i class="fas fa-plus text-lg transition-transform group-hover:rotate-12"></i>
                    <span>Novo {{ (isset($type) && $type === 'album') ? 'Álbum' : 'Evento' }}</span>
                </a>
                @endif
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl shadow-slate-200/40 dark:shadow-none border border-slate-100 dark:border-slate-800 overflow-hidden transition-all duration-500">
            <div class="p-4 sm:p-8">
                <table id="panel-events-table" class="w-full text-left border-collapse display">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-950/50 border-b border-slate-100 dark:border-slate-800 text-[11px] uppercase tracking-[0.15em] text-slate-400 dark:text-slate-500 font-black transition-colors">
                            <th class="px-6 py-5">{{ (isset($type) && $type === 'album') ? 'Álbum' : 'Evento' }}</th>
                            @if(!(isset($type) && $type === 'album'))
                            <th class="px-6 py-5">Data/Hora</th>
                            <th class="px-6 py-5">Local</th>
                            @endif
                            <th class="px-6 py-5 text-center">Visível</th>
                            <th class="px-6 py-5 text-center">Galeria</th>
                            <th class="px-6 py-5 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($events as $event)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-5">
                                        <div class="relative group/thumb">
                                            <div class="w-20 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden border-2 border-slate-200/60 dark:border-slate-700 shrink-0 transition-all duration-500 group-hover/thumb:scale-110 shadow-sm">
                                                @if($event->image_url)
                                                    <img src="{{ $event->image_url }}" alt="{{ $event->title }}"
                                                        class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-slate-50 dark:bg-slate-800 transition-colors">
                                                        <i class="fas fa-{{ $event->type === 'album' ? 'images' : 'calendar-star' }} text-slate-300 dark:text-slate-600 text-xl"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            @if($event->type === 'album')
                                                <div class="absolute -top-2 -right-2 bg-blue-600 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-lg border border-white dark:border-slate-900 uppercase tracking-tighter">Álbum</div>
                                            @endif
                                        </div>
                                        <div class="max-w-md font-black text-slate-900 dark:text-white transition-colors text-lg" title="{{ $event->title }}">
                                            {{ $event->title }}
                                        </div>
                                    </div>
                                </td>
                                @if(!(isset($type) && $type === 'album'))
                                <td class="px-6 py-5 whitespace-nowrap" data-order="{{ $event->start_at ? \Carbon\Carbon::parse($event->start_at)->timestamp : 0 }}">
                                    @if($event->start_at)
                                        <div class="flex flex-col">
                                            <span class="text-base font-black text-slate-700 dark:text-slate-300 transition-colors">
                                                {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }}
                                            </span>
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 uppercase font-black tracking-widest mt-0.5">
                                                <i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($event->start_at)->format('H:i') }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-sm font-bold text-slate-400">Pendente</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-sm font-bold text-slate-600 dark:text-slate-400 transition-colors rounded-xl bg-slate-100/50 dark:bg-slate-800/50 px-3 py-1.5 inline-flex items-center gap-2">
                                        <i class="fas fa-map-marker-alt text-blue-500"></i>
                                        {{ $event->location ?: 'Online' }}
                                    </div>
                                </td>
                                @endif
                                
                                <td class="px-6 py-5 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer {{ $canEditEvent ? 'ajax-toggle' : '' }}" data-id="{{ $event->id }}" data-field="published" {{ $event->published ? 'checked' : '' }} {{ $canEditEvent ? '' : 'disabled' }}>
                                        <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none dark:peer-focus:ring-white/10 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-[24px] peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-blue-600 shadow-inner"></div>
                                    </label>
                                </td>
                                
                                <td class="px-6 py-5 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer {{ $canEditEvent ? 'ajax-toggle' : '' }}" data-id="{{ $event->id }}" data-field="show_on_gallery" {{ $event->show_on_gallery ? 'checked' : '' }} {{ $canEditEvent ? '' : 'disabled' }}>
                                        <div class="w-12 h-6 bg-slate-200 peer-focus:outline-none dark:peer-focus:ring-white/10 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-[24px] peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-500 shadow-inner"></div>
                                    </label>
                                </td>

                                <td class="px-6 py-5 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2.5">
                                        @if($canEditEvent)
                                        <a href="{{ route('panel.admin.events.edit', ['event' => $event, 'tab' => 'gallery']) }}"
                                            class="w-10 h-10 flex items-center justify-center bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-[0.9rem] transition-all duration-300 border border-amber-100 dark:border-amber-800/50 hover:scale-110 active:scale-95 shadow-sm"
                                            title="Gerenciar Mídia">
                                            <i class="fas fa-photo-video"></i>
                                        </a>
                                        @endif

                                        @if($event->type !== 'album' && $canManageExhibitorsFor($event))
                                            <a href="{{ route('panel.admin.events.exhibitors.index', $event) }}"
                                                class="w-10 h-10 flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-[0.9rem] transition-all duration-300 border border-indigo-100 dark:border-indigo-800/50 hover:scale-110 active:scale-95 shadow-sm"
                                                title="Áreas para Expositores">
                                                <i class="fas fa-store"></i>
                                            </a>
                                        @endif

                                        @if($event->type !== 'album' && $panelUser?->hasPermission('admin.events.coupons.view'))
                                            <a href="{{ route('panel.admin.events.coupons.index', $event) }}"
                                                class="w-10 h-10 flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-[0.9rem] transition-all duration-300 border border-emerald-100 dark:border-emerald-800/50 hover:scale-110 active:scale-95 shadow-sm"
                                                title="Cupons de gratuidade">
                                                <i class="fas fa-ticket-alt"></i>
                                            </a>
                                        @endif

                                        @if($event->is_ticket_enabled && $event->type !== 'album' && $canScanEvents)
                                            <a href="{{ route('panel.admin.events.scanner', $event) }}"
                                                class="w-10 h-10 flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-[0.9rem] transition-all duration-300 border border-emerald-100 dark:border-emerald-800/50 hover:scale-110 active:scale-95 shadow-sm"
                                                title="Escanear Ingressos">
                                                <i class="fas fa-qrcode"></i>
                                            </a>
                                        @endif

                                        @if($canEditEvent)
                                        <a href="{{ route('panel.admin.events.edit', $event) }}"
                                            class="w-10 h-10 flex items-center justify-center bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-[0.9rem] transition-all duration-300 border border-blue-100 dark:border-blue-800/50 hover:scale-110 active:scale-95 shadow-sm"
                                            title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif

                                        @if($canDeleteEvent)
                                        <button type="button"
                                            onclick="confirmDelete({{ $event->id }})"
                                            class="w-10 h-10 flex items-center justify-center bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-[0.9rem] transition-all duration-300 border border-red-100 dark:border-red-800/50 hover:scale-110 active:scale-95 shadow-sm"
                                            title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        
                                        <form id="delete-form-{{ $event->id }}" action="{{ route('panel.admin.events.destroy', $event) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center">
                                    <div class="w-24 h-24 bg-slate-50 dark:bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-6 transition-colors duration-500">
                                        <i class="fas fa-folder-open text-slate-300 dark:text-slate-600 text-3xl"></i>
                                    </div>
                                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Nenhum registro encontrado</h3>
                                    <p class="text-slate-500 dark:text-slate-400 mt-2 max-w-xs mx-auto">Comece criando um novo registro para visualizar aqui.</p>
                                    @if($canCreateEvent)
                                    <a href="{{ (isset($type) && $type === 'album') ? route('panel.admin.acervo.create') : route('panel.admin.events.create') }}" class="mt-8 inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-2xl font-black transition-all hover:scale-105 active:scale-95 shadow-lg shadow-blue-200">
                                        <i class="fas fa-plus"></i>
                                        Novo Registro
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
            const table = $('#panel-events-table').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                order: [[0, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                columnDefs: [
                    { targets: -1, orderable: false, searchable: false, responsivePriority: 1 },
                    { targets: 0, responsivePriority: 2 }
                ]
            });

            // AJAX Toggle Handler
            $('.ajax-toggle').on('change', function() {
                const $checkbox = $(this);
                const id = $checkbox.data('id');
                const field = $checkbox.data('field');
                const value = $checkbox.is(':checked');

                // Visual feedback
                $checkbox.prop('disabled', true);
                
                axios.post('{{ route("panel.admin.events.toggle-field", "") }}/' + id, {
                    field: field
                })
                .then(response => {
                    if (response.data.status === 'success') {
                        Toast.fire({
                            icon: 'success',
                            title: response.data.message || 'Atualizado com sucesso!'
                        });
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.data.message || 'Erro ao atualizar.'
                        });
                        $checkbox.prop('checked', !value);
                    }
                })
                .catch(error => {
                    console.error(error);
                    Toast.fire({
                        icon: 'error',
                        title: 'Falha na comunicação com o servidor.'
                    });
                    $checkbox.prop('checked', !value);
                })
                .finally(() => {
                    $checkbox.prop('disabled', false);
                });
            });
        });

        function confirmDelete(id) {
            const isAlbum = {{ (isset($type) && $type === 'album') ? 'true' : 'false' }};
            Swal.fire({
                title: isAlbum ? 'Excluir álbum?' : 'Excluir evento?',
                text: "Esta ação excluirá permanentemente este registro e todas as mídias associadas!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar',
                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
@endpush
