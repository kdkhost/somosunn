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
    .fc .fc-toolbar.fc-header-toolbar { margin-bottom: 1.25rem; }
    .fc .fc-toolbar-title { font-size: 1.4rem; font-weight: 700; text-transform: lowercase; }
    .fc .fc-button { border-radius: 6px; box-shadow: none; }
    .fc .fc-button-primary { background: #1f5edb; border-color: #1f5edb; }
    .fc .fc-button-primary:not(:disabled):hover { background: #184bb0; border-color: #184bb0; }
    .fc .fc-button-primary:disabled { background: #8bb1ff; border-color: #8bb1ff; }
    .external-events { min-height: 120px; }
    .external-event {
        color: #fff;
        padding: 6px 10px;
        border-radius: 6px;
        margin-bottom: 8px;
        cursor: move;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .color-chooser { display: flex; gap: 8px; flex-wrap: wrap; }
    .color-chooser .color-item {
        width: 28px; height: 28px; border-radius: 6px; cursor: pointer;
        border: 2px solid transparent;
    }
    .color-chooser .color-item.active { border-color: #111827; }
    .calendar-card { border-top: 3px solid #1f5edb; }
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
                <div id="recent-events" class="small text-muted">Carregando...</div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Eventos Arrastáveis</h3></div>
            <div class="card-body">
                <div id="external-events" class="external-events">
                    <div class="external-event" data-title="Almoço de Negócios" data-color="#28a745" style="background:#28a745;">Almoço de Negócios</div>
                    <div class="external-event" data-title="Reunião com Parceiros" data-color="#ffc107" style="background:#ffc107;">Reunião com Parceiros</div>
                    <div class="external-event" data-title="Mentoria VIP" data-color="#17a2b8" style="background:#17a2b8;">Mentoria VIP</div>
                    <div class="external-event" data-title="Workshop" data-color="#007bff" style="background:#007bff;">Workshop</div>
                    <div class="external-event" data-title="Networking" data-color="#dc3545" style="background:#dc3545;">Networking</div>
                </div>
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input" id="drop-remove">
                    <label class="form-check-label" for="drop-remove">Remover após soltar</label>
                </div>
            </div>
        </div>
        <div class="card">
             <div class="card-header"><h3 class="card-title">Criar Evento Rápido</h3></div>
             <div class="card-body">
                 <div class="form-group">
                     <input type="text" class="form-control" id="new-event-title" placeholder="Título do evento">
                 </div>
                 <div class="color-chooser mb-3" id="new-event-colors">
                     <span class="color-item active" data-color="#007bff" style="background:#007bff;"></span>
                     <span class="color-item" data-color="#28a745" style="background:#28a745;"></span>
                     <span class="color-item" data-color="#17a2b8" style="background:#17a2b8;"></span>
                     <span class="color-item" data-color="#ffc107" style="background:#ffc107;"></span>
                     <span class="color-item" data-color="#dc3545" style="background:#dc3545;"></span>
                     <span class="color-item" data-color="#6f42c1" style="background:#6f42c1;"></span>
                 </div>
                 <button id="add-new-event" class="btn btn-primary btn-block"><i class="fas fa-plus"></i> Adicionar</button>
                 <button class="btn btn-outline-secondary btn-block mt-2" data-toggle="modal" data-target="#eventModal">
                     <i class="fas fa-pen"></i> Novo Evento Completo
                 </button>
             </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card calendar-card">
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
            <form id="eventForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="event_id" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" class="form-control" name="title" id="title" required>
                    </div>
                    <input type="hidden" name="published" value="0">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="published" id="published" value="1" checked>
                        <label class="form-check-label" for="published">Publicado no Site</label>
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

                    <input type="hidden" name="all_day" value="0">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="all_day" id="all_day" value="1">
                        <label class="form-check-label" for="all_day">Dia inteiro</label>
                    </div>

                    <div class="form-group">
                        <label>Local (nome)</label>
                        <input type="text" class="form-control" name="location" id="location" placeholder="Ex: Centro de Convenções UNN">
                    </div>
                    <div class="form-group">
                        <label>Endereço completo</label>
                        <input type="text" class="form-control" name="address" id="address" placeholder="Ex: Av. Paulista, 1000 - SP">
                    </div>
                     
                    <div class="form-group">
                        <label>Vagas (0 = ilimitado)</label>
                        <input type="number" class="form-control" name="capacity" id="capacity" min="0">
                    </div>

                    <div class="form-group">
                        <label>Preço base (entrada)</label>
                        <input type="text" class="form-control mask-money" name="price" id="price" placeholder="R$ 0,00">
                        <small class="text-muted">Se os lotes estiverem vazios, este valor será usado como entrada.</small>
                    </div>

                    <div class="card card-secondary">
                        <div class="card-header"><h3 class="card-title" style="font-size:1rem;">Lotes / Ingressos</h3></div>
                        <div class="card-body p-2">
                             <!-- Batch 1 -->
                             <div class="row">
                                 <div class="col-6">
                                     <label>1º Lote (R$)</label>
                                     <input type="text" class="form-control mask-money" name="batch_1_price" id="batch_1_price" placeholder="R$ 0,00">
                                 </div>
                                 <div class="col-6">
                                     <label>Até quando?</label>
                                     <input type="datetime-local" class="form-control" name="batch_1_deadline" id="batch_1_deadline">
                                 </div>
                             </div>
                             <div class="dropdown-divider"></div>
                             <!-- Batch 2 -->
                             <div class="row">
                                 <div class="col-6">
                                     <label>2º Lote (R$)</label>
                                     <input type="text" class="form-control mask-money" name="batch_2_price" id="batch_2_price" placeholder="R$ 0,00">
                                 </div>
                                 <div class="col-6">
                                     <label>Até quando?</label>
                                     <input type="datetime-local" class="form-control" name="batch_2_deadline" id="batch_2_deadline">
                                 </div>
                             </div>
                             <div class="dropdown-divider"></div>
                             <!-- Batch 3 -->
                             <div class="row">
                                 <div class="col-6">
                                     <label>3º Lote / Na hora (R$)</label>
                                     <input type="text" class="form-control mask-money" name="batch_3_price" id="batch_3_price" placeholder="R$ 0,00">
                                 </div>
                                 <div class="col-6">
                                     <label>Até (ou na hora)</label>
                                     <input type="datetime-local" class="form-control" name="batch_3_deadline" id="batch_3_deadline">
                                 </div>
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

                    <div class="form-group">
                        <label>Imagem do evento</label>
                        <input type="hidden" name="remove_image" value="0">
                        <div class="upload-box" id="eventImageBox" data-max-size="5242880" data-existing-url="" data-remove-input="[name='remove_image']">
                            <input type="file" name="image" id="image" accept="image/*" class="d-none">
                            <div class="upload-preview mb-2"></div>
                            <div class="upload-meta text-muted"></div>
                            <small class="text-muted upload-help"></small>
                            <div class="progress upload-progress progress-sm d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                            <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                        </div>
                    </div>
                    
                    <div id="modalMapContainer" style="display:none; margin-top:15px;">
                        <label>Localização</label>
                        <p id="modalAddress" class="text-muted small"></p>
                        <div id="modalMap" style="height: 200px; border-radius: 8px; border: 1px solid #ddd;"></div>
                    </div>
                </div>
                <input type="hidden" id="event_latitude" name="latitude">
                <input type="hidden" id="event_longitude" name="longitude">
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
        var tempExternalEvent = null;
        var tempExternalSaved = false;
        var pendingExternalDrop = null;
        var selectedQuickColor = '#007bff';
        var feedUrl = '{{ route("admin.events.feed") }}';

        // External draggable events
        var externalEventsEl = document.getElementById('external-events');
        var externalDraggable = new FullCalendar.Draggable(externalEventsEl, {
            itemSelector: '.external-event',
            eventData: function(eventEl) {
                var title = eventEl.getAttribute('data-title') || eventEl.innerText.trim();
                var color = eventEl.getAttribute('data-color') || '#1f5edb';
                return {
                    title: title,
                    backgroundColor: color,
                    borderColor: color,
                    textColor: '#ffffff'
                };
            }
        });

        var calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'bootstrap' ],
            themeSystem: 'bootstrap',
            locale: 'pt-br',
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'Hoje',
                month: 'Mês',
                week: 'Semana',
                day: 'Dia'
            },
            events: feedUrl,
            editable: true,
            droppable: true,
            selectable: true,
            selectMirror: true,
            eventClick: function(info) {
                openModal(info.event);
            },
            dateClick: function(info) {
                openModal({start: info.date, allDay: info.allDay});
            },
            select: function(info) {
                openModal({start: info.start, end: info.end, allDay: info.allDay});
                calendar.unselect();
            },
            eventDrop: function(info) {
                updateEvent(info.event);
            },
            eventResize: function(info) {
                updateEvent(info.event);
            },
            eventReceive: function(info) {
                tempExternalEvent = info.event;
                tempExternalSaved = false;
                pendingExternalDrop = {
                    remove: $('#drop-remove').is(':checked'),
                    el: info.draggedEl
                };
                openModal(info.event);
            }
        });
        calendar.render();

        // Recent events list
        loadRecentEvents();

        // Modal Handling
        function openModal(event) {
            $('#eventForm')[0].reset();
            $('#event_id').val('');
            $('#published').prop('checked', true);
            $('#all_day').prop('checked', false);
            $('#event_latitude').val('');
            $('#event_longitude').val('');
            $('#suggestions').empty().hide();
            $('#btnDelete').hide();
            $('#modalMapContainer').hide();

            setEventImageExisting(null);

            if (event && event.title) {
                $('#title').val(event.title);
            }
            if (event && event.backgroundColor) {
                $('#color').val(event.backgroundColor);
            }

            if (event && event.id) {
                $('#event_id').val(event.id);
                $('#title').val(event.title);
                $('#color').val(event.backgroundColor || '#3788d8');
                $('#description').val(event.extendedProps.description || '');
                $('#location').val(event.extendedProps.location || '');
                $('#all_day').prop('checked', !!event.allDay);
                
                if(event.start) $('#start_at').val(formatDate(event.start));
                if(event.end) $('#end_at').val(formatDate(event.end));
                
                $('#address').val(event.extendedProps.address || '');
                $('#capacity').val(event.extendedProps.capacity === null || event.extendedProps.capacity === undefined ? '' : event.extendedProps.capacity);
                setMoneyValue($('#price'), event.extendedProps.price);
                $('#published').prop('checked', !!event.extendedProps.published);
                
                setMoneyValue($('#batch_1_price'), event.extendedProps.batch_1_price);
                if(event.extendedProps.batch_1_deadline) $('#batch_1_deadline').val(formatDate(new Date(event.extendedProps.batch_1_deadline)));
                
                setMoneyValue($('#batch_2_price'), event.extendedProps.batch_2_price);
                if(event.extendedProps.batch_2_deadline) $('#batch_2_deadline').val(formatDate(new Date(event.extendedProps.batch_2_deadline)));
                
                setMoneyValue($('#batch_3_price'), event.extendedProps.batch_3_price);
                if(event.extendedProps.batch_3_deadline) $('#batch_3_deadline').val(formatDate(new Date(event.extendedProps.batch_3_deadline)));

                setEventImageExisting(event.extendedProps.image_url || null);
                
                if (event.extendedProps.latitude && event.extendedProps.longitude) {
                     $('#event_latitude').val(event.extendedProps.latitude);
                     $('#event_longitude').val(event.extendedProps.longitude);
                     showMap(event.extendedProps.latitude, event.extendedProps.longitude, event.extendedProps.address);
                }

                $('#btnDelete').show().off('click').on('click', function(){
                    deleteEvent(event.id);
                });
            } else {
                if (event && event.start) $('#start_at').val(formatDate(new Date(event.start)));
                if (event && event.end) $('#end_at').val(formatDate(new Date(event.end)));
                if (event && event.allDay !== undefined) $('#all_day').prop('checked', !!event.allDay);
            }
            $('#eventModal').modal('show');
            
            setTimeout(function(){ 
                if(modalMap) modalMap.invalidateSize(); 
            }, 500);
        }

        $('#eventModal').on('hidden.bs.modal', function() {
            if (tempExternalEvent && !tempExternalSaved) {
                tempExternalEvent.remove();
            }
            tempExternalEvent = null;
            tempExternalSaved = false;
            pendingExternalDrop = null;
        });

        function showMap(lat, lng, address) {
            $('#modalMapContainer').show();
            $('#modalAddress').text(address || 'Localização selecionada');
            
            if(!modalMap) {
                modalMap = L.map('modalMap');
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '&copy; OSM'}).addTo(modalMap);
                
                modalMap.on('click', function(e) {
                    updateMarker(e.latlng.lat, e.latlng.lng);
                });
            }
            
            setTimeout(function(){
                modalMap.invalidateSize();
                modalMap.setView([lat, lng], 16);
                updateMarker(lat, lng);
            }, 300);
        }

        function updateMarker(lat, lng) {
             if(modalMarker) modalMap.removeLayer(modalMarker);
             
             modalMarker = L.marker([lat, lng], {draggable: true}).addTo(modalMap);
             
             $('#event_latitude').val(lat);
             $('#event_longitude').val(lng);
             
             modalMarker.on('dragend', function(e) {
                 var position = modalMarker.getLatLng();
                 $('#event_latitude').val(position.lat);
                 $('#event_longitude').val(position.lng);
             });
        }

        // Live Geocoding with Autocomplete
        let debounceTimer;
        if($('#suggestions').length === 0) {
            $('<ul id="suggestions" class="list-group" style="position:absolute; z-index:1000; width:95%; max-height:200px; overflow-y:auto; display:none;"></ul>').insertAfter('#address');
        }

        $('#address').on('input', function() {
            clearTimeout(debounceTimer);
            var query = $(this).val();
            var suggestions = $('#suggestions');
            
            if (query.length < 3) {
                suggestions.hide();
                return;
            }

            debounceTimer = setTimeout(function() {
                var bias = '{{ $companyLocation ?? "" }}';
                var searchQuery = query;
                if(bias && query.indexOf(',') === -1 && query.length < 15) {
                    searchQuery = query + ', ' + bias;
                }

                fetch('https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&q=' + encodeURIComponent(searchQuery))
                    .then(response => response.json())
                    .then(data => {
                        suggestions.empty();
                        if (data && data.length > 0) {
                            data.forEach(function(item) {
                                let display_name = item.display_name;
                                let li = $('<li class="list-group-item list-group-item-action" style="cursor:pointer;">' + display_name + '</li>');
                                li.on('click', function() {
                                    $('#address').val(display_name);
                                    $('#event_latitude').val(item.lat);
                                    $('#event_longitude').val(item.lon);
                                    suggestions.hide();
                                    showMap(item.lat, item.lon, display_name);
                                });
                                suggestions.append(li);
                            });
                            suggestions.show();
                        } else {
                            suggestions.hide();
                        }
                    });
            }, 500);
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#address').length && !$(e.target).closest('#suggestions').length) {
                $('#suggestions').hide();
            }
        });

        function formatDate(date) {
            const offset = date.getTimezoneOffset();
            date = new Date(date.getTime() - (offset*60*1000));
            return date.toISOString().slice(0, 16);
        }

        // Quick event creator
        $('#new-event-colors .color-item').on('click', function() {
            $('#new-event-colors .color-item').removeClass('active');
            $(this).addClass('active');
            selectedQuickColor = $(this).data('color');
        });

        $('#add-new-event').on('click', function(e) {
            e.preventDefault();
            var title = $('#new-event-title').val().trim();
            if (!title) {
                toastr.error('Informe o título do evento.');
                return;
            }
            var eventEl = $('<div class="external-event"></div>');
            eventEl.text(title);
            eventEl.attr('data-title', title);
            eventEl.attr('data-color', selectedQuickColor);
            eventEl.css('background', selectedQuickColor);
            $('#external-events').prepend(eventEl);
            $('#new-event-title').val('');
        });

        // CRUD AJAX
        $('#eventForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#event_id').val();
            const url = id ? '/admin/events/' + id : '{{ route("admin.events.store") }}';
            const formData = new FormData(this);
            if (id) {
                formData.append('_method', 'PUT');
            }
            
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#eventModal').modal('hide');
                    toastr.success(response.message || 'Evento salvo');
                    tempExternalSaved = true;
                    if (pendingExternalDrop && pendingExternalDrop.remove && pendingExternalDrop.el) {
                        $(pendingExternalDrop.el).remove();
                    }
                    pendingExternalDrop = null;
                    calendar.refetchEvents();
                    loadRecentEvents();
                },
                error: function(xhr) {
                    var errorMsg = 'Erro ao salvar evento';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    if(xhr.responseJSON && xhr.responseJSON.errors) {
                         var keys = Object.keys(xhr.responseJSON.errors);
                         if(keys.length > 0) errorMsg = xhr.responseJSON.errors[keys[0]][0];
                    }
                    toastr.error(errorMsg);
                }
            });
        });

        function updateEvent(event) {
            $.ajax({
                url: '/admin/events/' + event.id,
                type: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    title: event.title,
                    start_at: event.start ? formatDate(event.start) : null,
                    end_at: event.end ? formatDate(event.end) : null,
                    all_day: event.allDay ? 1 : 0
                },
                success: function(response) {
                    toastr.success('Evento atualizado');
                    loadRecentEvents();
                },
                error: function(xhr) {
                    toastr.error('Erro ao mover evento');
                    calendar.refetchEvents();
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
                            toastr.success(response.message || 'Evento removido');
                            calendar.refetchEvents();
                            loadRecentEvents();
                         },
                         error: function() {
                             toastr.error('Erro ao excluir evento');
                         }
                    });
                }
            });
        }

        function normalizeMoneyForMask(value) {
            if (value === null || value === undefined) return '';
            let str = String(value).trim();
            if (!str) return '';
            str = str.replace(/^R\$\s?/, '').trim();
            if (str.includes(',')) return str;
            if (/^\d+(\.\d{1,2})$/.test(str)) return str.replace('.', ',');
            return str;
        }

        function setMoneyValue($input, value) {
            const normalized = normalizeMoneyForMask(value);
            if (!normalized && normalized !== '0') {
                $input.val('');
                return;
            }

            if (typeof $input.inputmask === 'function') {
                $input.inputmask('setvalue', normalized);
            } else {
                $input.val(normalized);
            }
        }

        function setEventImageExisting(url) {
            const box = $('#eventImageBox');
            const preview = box.find('.upload-preview');
            const meta = box.find('.upload-meta');
            const removeBtn = box.find('.upload-remove');
            const fileInput = box.find('input[type=file]');

            fileInput.val('');
            $('[name="remove_image"]').val('0');

            if (!url) {
                preview.html('<i class="upload-icon fas fa-cloud-upload-alt"></i><div class="text-muted small">Clique ou arraste para enviar</div>');
                meta.text('');
                removeBtn.addClass('d-none');
                return;
            }

            preview.html('<img src="' + url + '" alt="imagem">');
            meta.text('Arquivo atual');
            removeBtn.removeClass('d-none');
        }

        function loadRecentEvents() {
            var start = new Date();
            start.setMonth(start.getMonth() - 2);
            var end = new Date();
            end.setMonth(end.getMonth() + 2);
            $.getJSON(feedUrl, { start: start.toISOString(), end: end.toISOString() })
                .done(function(items) {
                    if (!items || !items.length) {
                        $('#recent-events').html('<span class="text-muted">Nenhum evento encontrado.</span>');
                        return;
                    }
                    var html = '<ul class="list-unstyled mb-0">';
                    items.slice(0, 6).forEach(function(ev) {
                        var when = ev.start ? new Date(ev.start).toLocaleDateString('pt-BR') : '';
                        html += '<li class="mb-2"><span class="badge badge-light mr-2">' + when + '</span>' + ev.title + '</li>';
                    });
                    html += '</ul>';
                    $('#recent-events').html(html);
                })
                .fail(function() {
                    $('#recent-events').html('<span class="text-muted">Não foi possível carregar.</span>');
                });
        }
    });
</script>
@endpush
