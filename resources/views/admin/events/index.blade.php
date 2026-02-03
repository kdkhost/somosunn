@extends('admin.layouts.app')
@section('title', 'Eventos e Reuniões')
@section('page_title', 'Calendário de Eventos')
@section('breadcrumb')
    <li class="breadcrumb-item active">Eventos</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/main.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/daygrid/main.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/timegrid/main.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/bootstrap/main.min.css">
<style>
    .getError{color:#dc3545; font-size: 80%;}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Eventos Recentes</h4>
            </div>
            <div class="card-body">
                <div id="external-events">
                    <p class="text-muted">Clique no calendário para adicionar.</p>
                </div>
            </div>
        </div>
        <div class="card">
             <div class="card-header"><h3 class="card-title">Criar Evento Rápido</h3></div>
             <div class="card-body">
                 <button class="btn btn-primary btn-block" data-toggle="modal" data-target="#eventModal"><i class="fas fa-plus"></i> Novo Evento</button>
             </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card card-primary">
            <div class="card-body p-0">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gerenciar Evento</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="eventForm">
                @csrf
                <input type="hidden" id="event_id" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" class="form-control" name="title" id="title" required>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Início</label>
                                <input type="datetime-local" class="form-control" name="start_at" id="start_at" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Fim</label>
                                <input type="datetime-local" class="form-control" name="end_at" id="end_at">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Cor</label>
                        <div class="input-group">
                            <input type="color" class="form-control" name="color" id="color" value="#3788d8">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descrição</label>
                        <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                    </div>
                    
                    <div id="modalMapContainer" style="display:none; margin-top:15px;">
                        <label>Localização</label>
                        <p id="modalAddress" class="text-muted small"></p>
                        <div id="modalMap" style="height: 200px; border-radius: 8px; border: 1px solid #ddd;"></div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" id="btnDelete" style="display:none;">Excluir</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/main.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/interaction/main.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/daygrid/main.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/timegrid/main.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/bootstrap/main.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/locales/pt-br.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var modalMap = null;
        var modalMarker = null;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'bootstrap' ],
            themeSystem: 'bootstrap',
            locale: 'pt-br',
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: '{{ route("admin.events.index") }}',
            editable: true,
            droppable: true,
            eventClick: function(info) {
                openModal(info.event);
            },
            dateClick: function(info) {
                openModal({start: info.dateStr});
            },
            eventDrop: function(info) {
                updateEvent(info.event);
            },
            eventResize: function(info) {
                updateEvent(info.event);
            }
        });
        calendar.render();

        // Modal Handling
        function openModal(event) {
            $('#eventForm')[0].reset();
            $('#event_id').val('');
            $('#btnDelete').hide();
            $('#modalMapContainer').hide();
            
            if (event.id) {
                $('#event_id').val(event.id);
                $('#title').val(event.title);
                $('#color').val(event.backgroundColor || '#3788d8');
                $('#description').val(event.extendedProps.description || '');
                
                // Format dates for input datetime-local
                if(event.start) $('#start_at').val(formatDate(event.start));
                if(event.end) $('#end_at').val(formatDate(event.end));
                
                // Map Handling
                if (event.extendedProps.latitude && event.extendedProps.longitude) {
                     $('#modalMapContainer').show();
                     $('#modalAddress').text(event.extendedProps.address || 'Localização no mapa');
                     
                     setTimeout(function(){
                         if(!modalMap) {
                             modalMap = L.map('modalMap');
                             L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '&copy; OSM'}).addTo(modalMap);
                         }
                         modalMap.invalidateSize();
                         modalMap.setView([event.extendedProps.latitude, event.extendedProps.longitude], 15);
                         
                         if(modalMarker) modalMap.removeLayer(modalMarker);
                         modalMarker = L.marker([event.extendedProps.latitude, event.extendedProps.longitude]).addTo(modalMap);
                     }, 300);
                }

                $('#btnDelete').show().off('click').on('click', function(){
                    deleteEvent(event.id);
                });
            } else {
                // New event
                if(event.start) {
                    let date = new Date(event.start);
                    $('#start_at').val(formatDate(date));
                }
            }
            $('#eventModal').modal('show');
        }

        // Format Date for Input
        function formatDate(date) {
            const offset = date.getTimezoneOffset();
            date = new Date(date.getTime() - (offset*60*1000));
            return date.toISOString().slice(0, 16);
        }

        // CRUD AJAX
        $('#eventForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#event_id').val();
            let url = id ? '/admin/events/' + id : '{{ route("admin.events.store") }}';
            let method = id ? 'PUT' : 'POST';
            
            $.ajax({
                url: url,
                type: method,
                data: $(this).serialize(),
                success: function(response) {
                    $('#eventModal').modal('hide');
                    toastr.success(response.message);
                    calendar.refetchEvents();
                },
                error: function(xhr) {
                    toastr.error('Erro ao salvar evento');
                }
            });
        });

        function updateEvent(event) {
            $.ajax({
                url: '/admin/events/' + event.id,
                type: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    start_at: event.start.toISOString(),
                    end_at: event.end ? event.end.toISOString() : null
                },
                success: function(response) {
                    toastr.success('Evento atualizado');
                },
                error: function(xhr) {
                    toastr.error('Erro ao mover evento');
                    calendar.refetchEvents(); // Revert
                }
            });
        }

        function deleteEvent(id) {
             Swal.fire({
                title: 'Excluir evento?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sim'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/events/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            $('#eventModal').modal('hide');
                            toastr.success(response.message);
                            calendar.refetchEvents();
                        }
                    });
                }
            });
        }
    });
</script>
@endpush
