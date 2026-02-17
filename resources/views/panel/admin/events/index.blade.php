@extends('panel.layouts.app')

@section('title', 'Calendário de Eventos')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.events.index') }}" class="hover:underline">Eventos</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Eventos</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Visualize e gerencie todos os
                    eventos e workshops da plataforma.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.events.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all transform hover:scale-[1.02]">
                    <i class="fas fa-plus"></i>
                    <span>Novo Evento</span>
                </a>
            </div>
        </div>

        {{-- Calendar Container --}}
        <div
            class="bg-white dark:bg-slate-900 p-6 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 min-h-[700px] transition-colors duration-300">
            <div id="calendar"></div>
        </div>
    </div>

    @push('styles')
        <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
        <style>
            :root {
                --fc-border-color: #f1f5f9;
                --fc-button-bg-color: #3b82f6;
                --fc-button-border-color: #3b82f6;
                --fc-button-hover-bg-color: #2563eb;
                --fc-button-hover-border-color: #2563eb;
                --fc-button-active-bg-color: #1d4ed8;
                --fc-button-active-border-color: #1d4ed8;
            }

            .dark {
                --fc-border-color: #1e293b;
                --fc-page-bg-color: #0f172a;
                --fc-neutral-bg-color: #1e293b;
                --fc-list-event-hover-bg-color: #1e293b;
                --fc-today-bg-color: rgba(59, 130, 246, 0.05);
            }

            .fc-theme-standard td,
            .fc-theme-standard th {
                border: 1px solid var(--fc-border-color);
            }

            .fc .fc-toolbar-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: #0f172a;
                text-transform: capitalize;
                transition: color 0.3s;
            }

            .dark .fc .fc-toolbar-title {
                color: #f8fafc;
            }

            .fc .fc-col-header-cell-cushion {
                color: #64748b;
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                padding: 12px 4px;
            }

            .dark .fc .fc-col-header-cell-cushion {
                color: #94a3b8;
            }

            .fc .fc-daygrid-day-number {
                color: #64748b;
                font-weight: 600;
                padding: 8px;
                transition: color 0.3s;
            }

            .dark .fc .fc-daygrid-day-number {
                color: #94a3b8;
            }

            .fc .fc-button-primary {
                background-color: var(--fc-button-bg-color);
                border-color: var(--fc-button-border-color);
                border-radius: 0.75rem;
                font-weight: 600;
                padding: 0.5rem 1rem;
                transition: all 0.2s;
            }

            .fc .fc-button-primary:hover {
                background-color: #2563eb;
                border-color: #2563eb;
            }

            .fc .fc-button-primary:disabled {
                background-color: #94a3b8;
                border-color: #94a3b8;
            }

            .fc-event {
                border-radius: 6px;
                padding: 2px 4px;
                border: none;
                font-size: 0.75rem;
                font-weight: 600;
            }
        </style>
    @endpush

    @push('scripts')
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'pt-br',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: '{{ route("panel.admin.events.index") }}',
                    eventClick: function (info) {
                        window.location.href = '/painel/admin/events/' + info.event.id + '/edit';
                    },
                    buttonText: {
                        today: 'Hoje',
                        month: 'Mês',
                        week: 'Semana',
                        day: 'Dia'
                    }
                });
                calendar.render();
            });
        </script>
    @endpush
@endsection