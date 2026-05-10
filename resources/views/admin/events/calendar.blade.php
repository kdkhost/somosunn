@extends('admin.layouts.app')
@section('title', 'Eventos e Reuniões')
@section('page_title', 'Calendário de Eventos')
@section('breadcrumb')
    <li class="breadcrumb-item active">Eventos</li>
@endsection

@php
    $calendarSettings = is_array($calendarSettings ?? null) ? $calendarSettings : [];

    $sanitizeHex = function ($value, string $default): string {
        $value = strtoupper(trim((string) $value));
        return preg_match('/^#[0-9A-F]{6}$/', $value) ? $value : $default;
    };

    $darkenHex = function (string $hexColor, float $factor = 0.82): string {
        $hexColor = ltrim($hexColor, '#');
        if (strlen($hexColor) !== 6) {
            return '#184BB0';
        }

        $r = (int) round(hexdec(substr($hexColor, 0, 2)) * $factor);
        $g = (int) round(hexdec(substr($hexColor, 2, 2)) * $factor);
        $b = (int) round(hexdec(substr($hexColor, 4, 2)) * $factor);

        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    };

    $buttonColor = $sanitizeHex($calendarSettings['button_color'] ?? '', '#1F5EDB');
    $buttonColorHover = $darkenHex($buttonColor, 0.82);
    $eventTextColor = $sanitizeHex($calendarSettings['event_text_color'] ?? '', '#FFFFFF');

    $allowedViews = ['dayGridMonth', 'timeGridWeek', 'timeGridDay'];
    $initialView = (string) ($calendarSettings['initial_view'] ?? 'dayGridMonth');
    if (!in_array($initialView, $allowedViews, true)) {
        $initialView = 'dayGridMonth';
    }

    $firstDay = (int) ($calendarSettings['first_day'] ?? 0);
    $firstDay = max(0, min(6, $firstDay));

    $weekends = (bool) ($calendarSettings['weekends'] ?? true);
    $weekNumbers = (bool) ($calendarSettings['week_numbers'] ?? false);
    $recentLimit = (int) ($calendarSettings['recent_limit'] ?? 6);
    $recentLimit = max(1, min(20, $recentLimit));
    $defaultRemoveAfterDrop = (bool) ($calendarSettings['default_remove_after_drop'] ?? false);

    $calendarTemplates = $calendarSettings['templates'] ?? [];
    if (!is_array($calendarTemplates) || count($calendarTemplates) === 0) {
        $calendarTemplates = [
            ['title' => 'Almoço de Negócios', 'color' => '#28A745'],
            ['title' => 'Reunião com Parceiros', 'color' => '#FFC107'],
            ['title' => 'Mentoria VIP', 'color' => '#17A2B8'],
            ['title' => 'Workshop', 'color' => '#007BFF'],
            ['title' => 'Networking', 'color' => '#DC3545'],
        ];
    }
    $calendarTemplates = array_values(array_filter(array_map(function ($tpl) use ($sanitizeHex) {
        $title = trim((string) ($tpl['title'] ?? ''));
        $color = $sanitizeHex($tpl['color'] ?? '', '#1F5EDB');
        return $title !== '' ? ['title' => $title, 'color' => $color] : null;
    }, $calendarTemplates)));

    $calendarQuickColors = $calendarSettings['quick_colors'] ?? [];
    if (!is_array($calendarQuickColors) || count($calendarQuickColors) === 0) {
        $calendarQuickColors = ['#007BFF', '#28A745', '#17A2B8', '#FFC107', '#DC3545', '#6F42C1'];
    }
    $calendarQuickColors = array_values(array_unique(array_filter(array_map(function ($color) use ($sanitizeHex) {
        return $sanitizeHex($color ?? '', '#007BFF');
    }, $calendarQuickColors))));
@endphp

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/main.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/daygrid/main.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/timegrid/main.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/bootstrap/main.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.css">
    <style>
        .getError {
            color: #dc3545;
            font-size: 80%;
        }

        :root {
            --unn-calendar-primary:
                {{ $buttonColor }}
            ;
            --unn-calendar-primary-hover:
                {{ $buttonColorHover }}
            ;
        }

        .fc .fc-toolbar.fc-header-toolbar {
            margin-bottom: 1.25rem;
        }

        .fc .fc-toolbar-title {
            font-size: 1.4rem;
            font-weight: 700;
            text-transform: lowercase;
        }

        .fc .fc-button {
            border-radius: 6px;
            box-shadow: none;
        }

        .fc .fc-button-primary {
            background: var(--unn-calendar-primary);
            border-color: var(--unn-calendar-primary);
        }

        .fc .fc-button-primary:not(:disabled):hover {
            background: var(--unn-calendar-primary-hover);
            border-color: var(--unn-calendar-primary-hover);
        }

        .fc .fc-button-primary:disabled {
            background: #8bb1ff;
            border-color: #8bb1ff;
        }

        .external-events {
            min-height: 120px;
        }

        .external-event {
            color: #fff;
            padding: 8px 12px;
            border-radius: 10px;
            margin-bottom: 8px;
            cursor: move;
            font-weight: 600;
            font-size: 0.85rem;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            display: flex;
            align-items: center;
        }

        .external-event:hover {
            transform: translateX(3px);
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
        }

        .color-chooser {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .color-chooser .color-item {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.15s ease;
        }

        .color-chooser .color-item:hover {
            transform: scale(1.1);
        }

        .color-chooser .color-item.active {
            border-color: #111827;
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #111827;
        }

        .calendar-card {
            border-top: 4px solid var(--unn-calendar-primary);
            border-radius: 10px;
            overflow: hidden;
        }

        #calendar {
            min-height: 760px;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header border-0">
                    <h4 class="card-title font-weight-bold">
                        <i class="fas fa-clock-rotate-left mr-2 text-primary"></i>Eventos Recentes
                    </h4>
                </div>
                <div class="card-body">
                    <div id="recent-events" class="small text-muted">Carregando...</div>
                </div>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('events.create'))
                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-arrows-alt mr-2 text-success"></i>Eventos Arrastáveis
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2"><i class="fas fa-hand-pointer mr-1"></i>Arraste um modelo para o calendário</p>
                        <div id="external-events" class="external-events">
                            @foreach($calendarTemplates as $tpl)
                                <div class="external-event shadow-sm" data-title="{{ $tpl['title'] }}" data-color="{{ $tpl['color'] }}"
                                    style="background:{{ $tpl['color'] }};">
                                    <i class="fas fa-grip-vertical mr-1"></i>{{ $tpl['title'] }}
                                </div>
                            @endforeach
                        </div>
                        <div class="form-check mt-3 p-2 rounded border bg-light">
                            <input type="checkbox" class="form-check-input" id="drop-remove" {{ $defaultRemoveAfterDrop ? 'checked' : '' }}>
                            <label class="form-check-label small font-weight-bold" for="drop-remove">Remover modelo após soltar</label>
                        </div>
                    </div>
                </div>
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-bolt mr-2 text-info"></i>Criar Evento Rápido
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="small font-weight-bold text-muted">Título</label>
                            <input type="text" class="form-control" id="new-event-title" placeholder="Digite o título do evento">
                        </div>
                        <label class="small font-weight-bold text-muted mb-2">Cor do evento</label>
                        <div class="color-chooser mb-3" id="new-event-colors">
                            @foreach($calendarQuickColors as $idx => $color)
                                <span class="color-item {{ $idx === 0 ? 'active' : '' }}" data-color="{{ $color }}"
                                    style="background:{{ $color }};"></span>
                            @endforeach
                        </div>
                        <button id="add-new-event" class="btn btn-primary btn-block rounded-pill elevation-1">
                            <i class="fas fa-plus mr-1"></i> Adicionar ao Calendário
                        </button>
                        <button class="btn btn-outline-secondary btn-block mt-2 rounded-pill" data-toggle="modal" data-target="#eventModal">
                            <i class="fas fa-pen mr-1"></i> Novo Evento Completo
                        </button>
                    </div>
                </div>
            @endif
        </div>
        <div class="col-md-9">
            <div class="card calendar-card shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2 text-primary"></i>Calendário
                    </h3>
                    @if(auth()->user()->isAdmin())
                        <div class="card-tools">
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 elevation-1" data-toggle="modal"
                                data-target="#calendarSettingsModal">
                                <i class="fas fa-sliders-h mr-1"></i> Personalizar
                            </button>
                        </div>
                    @endif
                </div>
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
                            <input type="checkbox" class="form-check-input" name="published" id="published" value="1"
                                checked>
                            <label class="form-check-label" for="published">Publicado no Site</label>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Início</label>
                                    <input type="datetime-local" class="form-control" name="start_at" id="start_at"
                                        required>
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
                            <input type="text" class="form-control" name="location" id="location"
                                placeholder="Ex: Centro de Convenções UNN">
                        </div>
                        <div class="form-group">
                            <label>Endereço completo</label>
                            <input type="text" class="form-control" name="address" id="address"
                                placeholder="Ex: Av. Paulista, 1000 - SP">
                        </div>

                        <div class="form-group">
                            <label>Vagas (0 = ilimitado)</label>
                            <input type="number" class="form-control" name="capacity" id="capacity" min="0">
                        </div>

                        <div class="form-group">
                            <label>Preço base (entrada)</label>
                            <input type="text" class="form-control mask-money" name="price" id="price"
                                placeholder="R$ 0,00">
                            <small class="text-muted">Se os lotes estiverem vazios, este valor será usado como
                                entrada.</small>
                        </div>

                        <div class="card card-secondary">
                            <div class="card-header">
                                <h3 class="card-title" style="font-size:1rem;">Lotes / Ingressos</h3>
                            </div>
                            <div class="card-body p-2">
                                <!-- Batch 1 -->
                                <div class="row">
                                    <div class="col-6">
                                        <label>1º Lote (R$)</label>
                                        <input type="text" class="form-control mask-money" name="batch_1_price"
                                            id="batch_1_price" placeholder="R$ 0,00">
                                    </div>
                                    <div class="col-6">
                                        <label>Até quando?</label>
                                        <input type="datetime-local" class="form-control" name="batch_1_deadline"
                                            id="batch_1_deadline">
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <!-- Batch 2 -->
                                <div class="row">
                                    <div class="col-6">
                                        <label>2º Lote (R$)</label>
                                        <input type="text" class="form-control mask-money" name="batch_2_price"
                                            id="batch_2_price" placeholder="R$ 0,00">
                                    </div>
                                    <div class="col-6">
                                        <label>Até quando?</label>
                                        <input type="datetime-local" class="form-control" name="batch_2_deadline"
                                            id="batch_2_deadline">
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <!-- Batch 3 -->
                                <div class="row">
                                    <div class="col-6">
                                        <label>3º Lote / Na hora (R$)</label>
                                        <input type="text" class="form-control mask-money" name="batch_3_price"
                                            id="batch_3_price" placeholder="R$ 0,00">
                                    </div>
                                    <div class="col-6">
                                        <label>Até (ou na hora)</label>
                                        <input type="datetime-local" class="form-control" name="batch_3_deadline"
                                            id="batch_3_deadline">
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
                            <textarea class="form-control summernote" name="description" id="description"
                                rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Imagem do evento</label>
                            <input type="hidden" name="remove_image" value="0">
                            <div class="upload-box" id="eventImageBox" data-max-size="5242880" data-existing-url=""
                                data-remove-input="[name='remove_image']">
                                <input type="file" name="image" id="image" accept="image/*" class="d-none">
                                <div class="upload-preview mb-2"></div>
                                <div class="upload-meta text-muted"></div>
                                <small class="text-muted upload-help"></small>
                                <div class="progress upload-progress progress-sm d-none mt-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
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
                        <div>
                            <a href="#" id="modalEventEdit" class="btn btn-warning"><i class="fas fa-edit mr-1"></i> Editar</a>
                            <a href="#" id="modalEventScanner" class="btn btn-primary d-none"><i class="fas fa-qrcode mr-1"></i>
                                Ler Ingressos</a>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-success" id="modalEventSave"><i class="fas fa-save mr-1"></i>
                                Salvar</button>
                            <button type="button" class="btn btn-danger" id="modalEventDelete"><i class="fas fa-trash mr-1"></i>
                                Excluir</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Calendar Settings Modal -->
    <div class="modal fade" id="calendarSettingsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Personalizar calendário</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form id="calendarSettingsForm" action="{{ route('admin.events.calendar.settings') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Visualização inicial</label>
                                    <select name="initial_view" class="form-control">
                                        <option value="dayGridMonth" {{ $initialView === 'dayGridMonth' ? 'selected' : '' }}>
                                            Mês</option>
                                        <option value="timeGridWeek" {{ $initialView === 'timeGridWeek' ? 'selected' : '' }}>
                                            Semana</option>
                                        <option value="timeGridDay" {{ $initialView === 'timeGridDay' ? 'selected' : '' }}>Dia
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Primeiro dia da semana</label>
                                    <select name="first_day" class="form-control">
                                        <option value="0" {{ $firstDay === 0 ? 'selected' : '' }}>Domingo</option>
                                        <option value="1" {{ $firstDay === 1 ? 'selected' : '' }}>Segunda</option>
                                        <option value="2" {{ $firstDay === 2 ? 'selected' : '' }}>Terça</option>
                                        <option value="3" {{ $firstDay === 3 ? 'selected' : '' }}>Quarta</option>
                                        <option value="4" {{ $firstDay === 4 ? 'selected' : '' }}>Quinta</option>
                                        <option value="5" {{ $firstDay === 5 ? 'selected' : '' }}>Sexta</option>
                                        <option value="6" {{ $firstDay === 6 ? 'selected' : '' }}>Sábado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Eventos recentes (quantidade)</label>
                                    <input type="number" name="recent_limit" class="form-control" min="1" max="20"
                                        value="{{ $recentLimit }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cor dos botões</label>
                                    <input type="color" name="button_color" class="form-control" value="{{ $buttonColor }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cor do texto do evento</label>
                                    <input type="color" name="event_text_color" class="form-control"
                                        value="{{ $eventTextColor }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Remover após soltar (padrão)</label>
                                    <select name="default_remove_after_drop" class="form-control">
                                        <option value="0" {{ !$defaultRemoveAfterDrop ? 'selected' : '' }}>Não</option>
                                        <option value="1" {{ $defaultRemoveAfterDrop ? 'selected' : '' }}>Sim</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="d-block">Opções</label>
                                    <input type="hidden" name="weekends" value="0">
                                    <div class="custom-control custom-checkbox d-inline-block mr-3">
                                        <input type="checkbox" class="custom-control-input" id="cal-weekends"
                                            name="weekends" value="1" {{ $weekends ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="cal-weekends">Mostrar fins de
                                            semana</label>
                                    </div>

                                    <input type="hidden" name="week_numbers" value="0">
                                    <div class="custom-control custom-checkbox d-inline-block">
                                        <input type="checkbox" class="custom-control-input" id="cal-weeknumbers"
                                            name="week_numbers" value="1" {{ $weekNumbers ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="cal-weeknumbers">Mostrar número da
                                            semana</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-2">Paleta de cores (criação rápida)</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddQuickColor"><i
                                            class="fas fa-plus"></i> Adicionar cor</button>
                                </div>
                                <div id="quickColorsList">
                                    @foreach($calendarQuickColors as $idx => $color)
                                        <div class="form-row align-items-center mb-2 calendar-color-row">
                                            <div class="col-10">
                                                <input type="color" class="form-control" name="quick_colors[{{ $idx }}]"
                                                    value="{{ $color }}">
                                            </div>
                                            <div class="col-2 text-right">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger btnRemoveQuickColor"><i
                                                        class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Essas cores aparecem no “Criar Evento Rápido”.</small>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-2">Modelos de eventos arrastáveis</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddTemplate"><i
                                            class="fas fa-plus"></i> Adicionar modelo</button>
                                </div>
                                <div id="templatesList">
                                    @foreach($calendarTemplates as $idx => $tpl)
                                        <div class="form-row align-items-center mb-2 calendar-template-row">
                                            <div class="col-7">
                                                <input type="text" class="form-control" name="templates[{{ $idx }}][title]"
                                                    value="{{ $tpl['title'] }}" placeholder="Título">
                                            </div>
                                            <div class="col-3">
                                                <input type="color" class="form-control" name="templates[{{ $idx }}][color]"
                                                    value="{{ $tpl['color'] }}">
                                            </div>
                                            <div class="col-2 text-right">
                                                <button type="button" class="btn btn-sm btn-outline-danger btnRemoveTemplate"><i
                                                        class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Arraste um modelo para o calendário para criar rápido.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/interaction/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/daygrid/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/timegrid/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/bootstrap/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/bootstrap/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/locales/pt-br.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-pt-BR.min.js"></script>

    <script>
        (function () {
            function initAdminEventsCalendar() {
                var calendarEl = document.getElementById('calendar');
                if (!calendarEl) {
                    return;
                }
                if (typeof window.FullCalendar === 'undefined' || typeof FullCalendar.Calendar !== 'function') {
                    calendarEl.innerHTML = '<div class="alert alert-warning m-3 mb-0">Nao foi possivel carregar o FullCalendar. Recarregue a pagina.</div>';
                    $('#recent-events').html('<span class="text-muted">Nao foi possivel carregar o calendario.</span>');
                    return;
                }
                var modalMap = null;
                var modalMarker = null;
                var tempExternalEvent = null;
                var tempExternalSaved = false;
                var pendingExternalDrop = null;
                var selectedQuickColor = '{{ $calendarQuickColors[0] ?? '#007BFF' }}';
                var eventTextColor = '{{ $eventTextColor }}';
                var recentLimit = {{ $recentLimit }};
                var feedUrl = '{{ route("admin.events.feed") }}';
                var canCreateEvents = @json(auth()->user()->isAdmin() || auth()->user()->hasPermission('events.create'));
                var canEditEvents = @json(auth()->user()->isAdmin() || auth()->user()->hasPermission('events.edit'));

                // External draggable events
                var externalEventsEl = document.getElementById('external-events');
                var DraggableCtor = null;
                if (typeof FullCalendar !== 'undefined' && typeof FullCalendar.Draggable === 'function') {
                    DraggableCtor = FullCalendar.Draggable;
                } else if (typeof window.FullCalendarInteraction !== 'undefined' && typeof FullCalendarInteraction.Draggable === 'function') {
                    DraggableCtor = FullCalendarInteraction.Draggable;
                }

                if (externalEventsEl && typeof DraggableCtor === 'function') {
                    new DraggableCtor(externalEventsEl, {
                        itemSelector: '.external-event',
                        eventData: function (eventEl) {
                            var title = eventEl.getAttribute('data-title') || eventEl.innerText.trim();
                            var color = eventEl.getAttribute('data-color') || '{{ $buttonColor }}';
                            return {
                                title: title,
                                backgroundColor: color,
                                borderColor: color,
                                textColor: eventTextColor
                            };
                        }
                    });
                }

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    plugins: ['interaction', 'dayGrid', 'timeGrid', 'bootstrap'],
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
                    defaultView: '{{ $initialView }}',
                    firstDay: {{ $firstDay }},
                    weekends: {{ $weekends ? 'true' : 'false' }},
                    weekNumbers: {{ $weekNumbers ? 'true' : 'false' }},
                    height: 'auto',
                    contentHeight: 700,
                    events: feedUrl,
                    editable: canEditEvents,
                    droppable: canCreateEvents,
                    eventStartEditable: canEditEvents,
                    eventDurationEditable: canEditEvents,
                    selectable: canCreateEvents,
                    selectMirror: true,
                    eventClick: function (info) {
                        openModal(info.event);
                    },
                    dateClick: function (info) {
                        openModal({ start: info.date, allDay: info.allDay });
                    },
                    select: function (info) {
                        openModal({ start: info.start, end: info.end, allDay: info.allDay });
                        calendar.unselect();
                    },
                    eventDrop: function (info) {
                        updateEvent(info.event);
                    },
                    eventResize: function (info) {
                        updateEvent(info.event);
                    },
                    eventReceive: function (info) {
                        tempExternalEvent = info.event;
                        tempExternalSaved = false;
                        pendingExternalDrop = {
                            remove: $('#drop-remove').is(':checked'),
                            el: info.draggedEl
                        };

                        // Cria evento rápido automaticamente (sem modal)
                        quickCreateFromExternalDrop(info);
                    }
                });
                calendar.render();

                // Initialize Summernote for description on modal show
                $(document).on('shown.bs.modal', '#eventModal', function () {
                    const $desc = $('#description');
                    if ($.fn.summernote && !$desc.next().hasClass('note-editor')) {
                        $desc.summernote({
                            height: 250,
                            placeholder: 'Descreva os detalhes do evento...',
                            toolbar: [
                                ['style', ['bold', 'italic', 'underline', 'clear']],
                                ['font', ['strikethrough', 'superscript', 'subscript']],
                                ['fontsize', ['fontsize']],
                                ['color', ['color']],
                                ['para', ['ul', 'ol', 'paragraph']],
                                ['insert', ['link', 'picture', 'video', 'table']],
                                ['view', ['fullscreen', 'codeview', 'help']]
                            ]
                        });
                    }
                    // Sincroniza o conteúdo do textarea no editor Summernote
                    var existingContent = $desc.val() || '';
                    if (existingContent) {
                        $desc.summernote('code', existingContent);
                    }
                });

                $(document).on('hidden.bs.modal', '#eventModal', function () {
                    if ($.fn.summernote) {
                        $('#description').summernote('destroy');
                    }
                });

                // Recent events list
                loadRecentEvents();

                function quickCreateFromExternalDrop(info) {
                    if (!canCreateEvents) {
                        info.event.remove();
                        return;
                    }

                    var start = info.event && info.event.start ? new Date(info.event.start) : null;
                    if (!start) {
                        info.event.remove();
                        return;
                    }

                    var end = info.event && info.event.end ? new Date(info.event.end) : null;
                    if (!end) {
                        var minutes = info.event.allDay ? (24 * 60) : 60;
                        end = new Date(start.getTime() + minutes * 60000);
                    }

                    var formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('title', info.event.title || 'Evento');
                    formData.append('start_at', formatDate(start));
                    formData.append('end_at', formatDate(end));
                    formData.append('all_day', info.event.allDay ? '1' : '0');
                    formData.append('color', info.event.backgroundColor || selectedQuickColor);
                    formData.append('published', '1');

                    $.ajax({
                        url: '{{ route("admin.events.store") }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            toastr.success((response && response.message) ? response.message : 'Evento criado com sucesso.');
                            tempExternalSaved = true;

                            if (pendingExternalDrop && pendingExternalDrop.remove && pendingExternalDrop.el) {
                                $(pendingExternalDrop.el).remove();
                            }
                            pendingExternalDrop = null;

                            if (tempExternalEvent) {
                                tempExternalEvent.remove();
                            }
                            tempExternalEvent = null;

                            calendar.refetchEvents();
                            loadRecentEvents();
                        },
                        error: function (xhr) {
                            var errorMsg = 'Erro ao criar evento rápido';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                var keys = Object.keys(xhr.responseJSON.errors);
                                if (keys.length > 0) errorMsg = xhr.responseJSON.errors[keys[0]][0];
                            }
                            toastr.error(errorMsg);

                            if (tempExternalEvent) {
                                tempExternalEvent.remove();
                            }
                            tempExternalEvent = null;
                            tempExternalSaved = false;
                            pendingExternalDrop = null;
                        }
                    });
                }

                // Modal Handling
                function openModal(event) {
                    $('#eventForm')[0].reset();
                    $('#event_id').val('');
                    $('#published').prop('checked', true);
                    $('#all_day').prop('checked', false);
                    $('#event_latitude').val('');
                    $('#event_longitude').val('');
                    $('#suggestions').empty().hide();
                    $('#modalMapContainer').hide();

                    const $form = $('#eventForm');
                    const isExistingEvent = !!(event && event.id);
                    const canSubmit = isExistingEvent ? canEditEvents : canCreateEvents;

                    if (!canSubmit) {
                        $form.find('input, select, textarea').prop('disabled', true);
                        $form.find('button[type="submit"]').hide();
                    } else {
                        $form.find('input, select, textarea').prop('disabled', false);
                        $form.find('button[type="submit"]').show();
                    }

                    setEventImageExisting(null);

                    // Apply values from the event object (standard or dropped)
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

                        if (event.start) $('#start_at').val(formatDate(event.start));
                        if (event.end) $('#end_at').val(formatDate(event.end));

                        $('#address').val(event.extendedProps.address || '');
                        $('#capacity').val(event.extendedProps.capacity === null || event.extendedProps.capacity === undefined ? '' : event.extendedProps.capacity);
                        setMoneyValue($('#price'), event.extendedProps.price);
                        $('#published').prop('checked', !!event.extendedProps.published);

                        setMoneyValue($('#batch_1_price'), event.extendedProps.batch_1_price);
                        if (event.extendedProps.batch_1_deadline) $('#batch_1_deadline').val(formatDate(new Date(event.extendedProps.batch_1_deadline)));

                        setMoneyValue($('#batch_2_price'), event.extendedProps.batch_2_price);
                        if (event.extendedProps.batch_2_deadline) $('#batch_2_deadline').val(formatDate(new Date(event.extendedProps.batch_2_deadline)));

                        setMoneyValue($('#batch_3_price'), event.extendedProps.batch_3_price);
                        if (event.extendedProps.batch_3_deadline) $('#batch_3_deadline').val(formatDate(new Date(event.extendedProps.batch_3_deadline)));

                        setEventImageExisting(event.extendedProps.image_url || null);

                        if (event.extendedProps.latitude && event.extendedProps.longitude) {
                            $('#event_latitude').val(event.extendedProps.latitude);
                            $('#event_longitude').val(event.extendedProps.longitude);
                            showMap(event.extendedProps.latitude, event.extendedProps.longitude, event.extendedProps.address);
                        }

                        $('#modalEventEdit').attr('href', '/admin/events/' + event.id + '/edit');

                        if (event.extendedProps.is_ticket_enabled) {
                            $('#modalEventScanner').removeClass('d-none').attr('href', '/admin/events/' + event.id + '/scanner');
                        } else {
                            $('#modalEventScanner').addClass('d-none').attr('href', '#');
                        }

                        if (canEditEvents) {
                            $('#modalEventDelete').show().off('click').on('click', function () {
                                deleteEvent(event.id);
                            });
                        } else {
                            $('#modalEventDelete').hide().off('click');
                        }
                    } else {
                        if (event && event.start) $('#start_at').val(formatDate(new Date(event.start)));
                        if (event && event.end) $('#end_at').val(formatDate(new Date(event.end)));
                        if (event && event.allDay !== undefined) $('#all_day').prop('checked', !!event.allDay);
                    }
                    $('#eventModal').modal('show');

                    setTimeout(function () {
                        if (modalMap) modalMap.invalidateSize();
                    }, 500);
                }

                $('#eventModal').on('hidden.bs.modal', function () {
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

                    if (!modalMap) {
                        modalMap = L.map('modalMap');
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM' }).addTo(modalMap);

                        modalMap.on('click', function (e) {
                            updateMarker(e.latlng.lat, e.latlng.lng);
                        });
                    }

                    setTimeout(function () {
                        modalMap.invalidateSize();
                        modalMap.setView([lat, lng], 16);
                        updateMarker(lat, lng);
                    }, 300);
                }

                function updateMarker(lat, lng) {
                    if (modalMarker) modalMap.removeLayer(modalMarker);

                    modalMarker = L.marker([lat, lng], { draggable: true }).addTo(modalMap);

                    $('#event_latitude').val(lat);
                    $('#event_longitude').val(lng);

                    modalMarker.on('dragend', function (e) {
                        var position = modalMarker.getLatLng();
                        $('#event_latitude').val(position.lat);
                        $('#event_longitude').val(position.lng);
                    });
                }

                // Live Geocoding with Autocomplete
                let debounceTimer;
                if ($('#suggestions').length === 0) {
                    $('<ul id="suggestions" class="list-group" style="position:absolute; z-index:1000; width:95%; max-height:200px; overflow-y:auto; display:none;"></ul>').insertAfter('#address');
                }

                $('#address').on('input', function () {
                    clearTimeout(debounceTimer);
                    var query = $(this).val();
                    var suggestions = $('#suggestions');

                    if (query.length < 3) {
                        suggestions.hide();
                        return;
                    }

                    debounceTimer = setTimeout(function () {
                        var bias = '{{ $companyLocation ?? "" }}';
                        var searchQuery = query;
                        if (bias && query.indexOf(',') === -1 && query.length < 15) {
                            searchQuery = query + ', ' + bias;
                        }

                        fetch('https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&q=' + encodeURIComponent(searchQuery))
                            .then(response => response.json())
                            .then(data => {
                                suggestions.empty();
                                if (data && data.length > 0) {
                                    data.forEach(function (item) {
                                        let display_name = item.display_name;
                                        let li = $('<li class="list-group-item list-group-item-action" style="cursor:pointer;">' + display_name + '</li>');
                                        li.on('click', function () {
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

                $(document).on('click', function (e) {
                    if (!$(e.target).closest('#address').length && !$(e.target).closest('#suggestions').length) {
                        $('#suggestions').hide();
                    }
                });

                function formatDate(date) {
                    const offset = date.getTimezoneOffset();
                    date = new Date(date.getTime() - (offset * 60 * 1000));
                    return date.toISOString().slice(0, 16);
                }

                // Quick event creator
                $('#new-event-colors .color-item').on('click', function () {
                    $('#new-event-colors .color-item').removeClass('active');
                    $(this).addClass('active');
                    selectedQuickColor = $(this).data('color');
                });

                $('#add-new-event').on('click', function (e) {
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
                $('#eventForm').on('submit', function (e) {
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
                        success: function (response) {
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
                        error: function (xhr) {
                            var errorMsg = 'Erro ao salvar evento';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                var keys = Object.keys(xhr.responseJSON.errors);
                                if (keys.length > 0) errorMsg = xhr.responseJSON.errors[keys[0]][0];
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
                        success: function (response) {
                            toastr.success('Evento atualizado');
                            loadRecentEvents();
                        },
                        error: function (xhr) {
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
                                success: function (response) {
                                    $('#eventModal').modal('hide');
                                    toastr.success(response.message || 'Evento removido');
                                    calendar.refetchEvents();
                                    loadRecentEvents();
                                },
                                error: function () {
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
                        .done(function (items) {
                            if (!items || !items.length) {
                                $('#recent-events').html('<span class="text-muted">Nenhum evento encontrado.</span>');
                                return;
                            }
                            var html = '<ul class="list-unstyled mb-0">';
                            items.slice(0, recentLimit).forEach(function (ev) {
                                var when = ev.start ? new Date(ev.start).toLocaleDateString('pt-BR') : '';
                                html += '<li class="mb-2"><span class="badge badge-light mr-2">' + when + '</span>' + ev.title + '</li>';
                            });
                            html += '</ul>';
                            $('#recent-events').html(html);
                        })
                        .fail(function () {
                            $('#recent-events').html('<span class="text-muted">Não foi possível carregar.</span>');
                        });
                }

                // Calendar settings (admin)
                var templateIndex = $('#templatesList .calendar-template-row').length;
                var quickColorIndex = $('#quickColorsList .calendar-color-row').length;

                $('#btnAddTemplate').on('click', function () {
                    var idx = templateIndex++;
                    var row = $(
                        '<div class="form-row align-items-center mb-2 calendar-template-row">' +
                        '<div class="col-7"><input type="text" class="form-control" name="templates[' + idx + '][title]" placeholder="Título"></div>' +
                        '<div class="col-3"><input type="color" class="form-control" name="templates[' + idx + '][color]" value="{{ $buttonColor }}"></div>' +
                        '<div class="col-2 text-right"><button type="button" class="btn btn-sm btn-outline-danger btnRemoveTemplate"><i class="fas fa-times"></i></button></div>' +
                        '</div>'
                    );
                    $('#templatesList').append(row);
                });

                $('#btnAddQuickColor').on('click', function () {
                    var idx = quickColorIndex++;
                    var row = $(
                        '<div class="form-row align-items-center mb-2 calendar-color-row">' +
                        '<div class="col-10"><input type="color" class="form-control" name="quick_colors[' + idx + ']" value="{{ $buttonColor }}"></div>' +
                        '<div class="col-2 text-right"><button type="button" class="btn btn-sm btn-outline-danger btnRemoveQuickColor"><i class="fas fa-times"></i></button></div>' +
                        '</div>'
                    );
                    $('#quickColorsList').append(row);
                });

                $(document).on('click', '.btnRemoveTemplate', function () {
                    $(this).closest('.calendar-template-row').remove();
                });

                $(document).on('click', '.btnRemoveQuickColor', function () {
                    $(this).closest('.calendar-color-row').remove();
                });

                $('#calendarSettingsForm').on('submit', function (e) {
                    e.preventDefault();
                    var $form = $(this);
                    var formData = new FormData(this);

                    $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (resp) {
                            toastr.success((resp && resp.message) ? resp.message : 'Configurações salvas');
                            $('#calendarSettingsModal').modal('hide');
                            setTimeout(function () {
                                window.location.reload();
                            }, 350);
                        },
                        error: function (xhr) {
                            var msg = 'Erro ao salvar configurações';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            toastr.error(msg);
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAdminEventsCalendar);
                return;
            }

            initAdminEventsCalendar();
        })();
    </script>
@endpush