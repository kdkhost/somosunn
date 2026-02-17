@extends('panel.layouts.app')

@section('title', 'Calendário de Eventos')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Eventos</h1>
                <p class="text-sm text-slate-500 mt-1">Visualize e gerencie todos os eventos e workshops da plataforma.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.events.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-200 transition-all">
                    <i class="fas fa-plus"></i>
                    <span>Novo Evento</span>
                </a>
            </div>
        </div>

        {{-- Calendar Container --}}
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 min-h-[700px]">
            <div id="calendar"></div>
        </div>
    </div>

    @push('styles')
        <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
        <style>
            .fc-theme-standard td,
            .fc-theme-standard th {
                border: 1px solid #f1f5f9;
            }

            .fc .fc-toolbar-title {
                font-size: 1.25rem;
                font-weight: 700;
                color: #0f172a;
                text-transform: capitalize;
            }

            .fc .fc-button-primary {
                background-color: #3b82f6;
                border-color: #3b82f6;
                border-radius: 0.75rem;
                font-weight: 600;
                padding: 0.5rem 1rem;
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