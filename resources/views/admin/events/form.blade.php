@extends('admin.layouts.app')

@section('page_title', $event->exists ? ($event->type === 'album' ? 'Editar Álbum' : 'Editar Evento') : (request('type') === 'album' ? 'Novo Álbum' : 'Novo Evento'))

@section('content')
    <style>
        #legacy-general:not(.active), #legacy-certificate:not(.active), #legacy-gallery:not(.active) { 
            display: none !important; 
        }
    </style>
    @push('scripts')
    <script>
        $(function () {
            // Se há ?tab= na URL, forçar a tab correta e ignorar o localStorage
            var urlTab = new URLSearchParams(location.search).get('tab');
            if (urlTab) {
                var $target = $('a[data-toggle="tab"][href="#legacy-' + urlTab + '"]');
                if ($target.length) {
                    // Remove active de todas as tabs/panes primeiro
                    $('#event-tabs .nav-link').removeClass('active');
                    $('#event-tabs-content .tab-pane').removeClass('show active');
                    // Ativa a correta
                    $target.addClass('active');
                    $('#legacy-' + urlTab).addClass('show active');
                }
            }
        });
    </script>
    @endpush
    <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs" id="event-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ request('tab') !== 'gallery' ? 'active' : '' }}" id="general-tab" data-toggle="tab" href="#legacy-general" role="tab"
                        aria-controls="legacy-general" aria-selected="{{ request('tab') !== 'gallery' ? 'true' : 'false' }}">Dados Gerais</a>
                </li>
                <li class="nav-item event-only-field">
                    <a class="nav-link" id="cert-tab" data-toggle="tab" href="#legacy-certificate" role="tab"
                        aria-controls="legacy-certificate" aria-selected="false">Certificado</a>
                </li>
                @if($event->exists)
                <li class="nav-item">
                    <a class="nav-link {{ request('tab') === 'gallery' ? 'active' : '' }}" id="gallery-tab" data-toggle="tab" href="#legacy-gallery" role="tab"
                        aria-controls="legacy-gallery" aria-selected="{{ request('tab') === 'gallery' ? 'true' : 'false' }}">Galeria de Fotos</a>
                </li>
                @endif
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="event-tabs-content">
                <!-- TAB GERAL -->
                <div class="tab-pane {{ request('tab') !== 'gallery' ? 'show active' : '' }}" id="legacy-general" role="tabpanel" aria-labelledby="general-tab">
                    <form method="POST" enctype="multipart/form-data" autocomplete="off"
                        action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}">
                        @csrf
                        @if($event->exists) @method('PUT') @endif

                        <div class="card card-outline card-info mb-3">
                            <div class="card-header"><h3 class="card-title font-weight-bold">Configurações do Registro</h3></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label>Tipo de Registro</label>
                                            <select name="type" id="registryType" class="form-control">
                                                <option value="event" {{ old('type', $event->type ?? request('type', 'event')) == 'event' ? 'selected' : '' }}>Evento Tradicional</option>
                                                <option value="album" {{ old('type', $event->type ?? request('type', 'event')) == 'album' ? 'selected' : '' }}>Álbum Privado / Galeria</option>
                                            </select>
                                            <small class="text-muted">Álbuns não aparecem no calendário e não possuem venda de ingressos.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6 event-col-expand">
                                        <div class="form-group mb-0">
                                            <label>URL Amigável (Slug)</label>
                                            <input name="slug" class="form-control" value="{{ old('slug', $event->slug) }}" placeholder="ex: onde-o-network-me-levou">
                                            <small class="text-muted">Deixe em branco para gerar automaticamente.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-2"><label>Título</label><input name="title" class="form-control"
                                value="{{ old('title', $event->title) }}" required></div>

                        <div class="form-group mb-2">
                            <label>Descrição</label>
                            <textarea name="description" id="eventDescriptionLegacy" class="form-control" rows="6">{{ old('description', $event->description) }}</textarea>
                        </div>

                        <div class="form-group mb-2 event-only-field"><label>Início</label><input name="start_at" type="datetime-local"
                                class="form-control" value="{{ old('start_at', $event->start_at) }}"></div>

                        <div class="event-only-field">
                            <div class="form-group mb-2"><label>Preço</label><input name="price" class="form-control mask-money"
                                    value="{{ old('price', $event->price) }}"></div>
                            <div class="form-group mb-2">
                                <label>Promoção relâmpago (preço)</label>
                                <input name="flash_sale_price" class="form-control mask-money"
                                    value="{{ old('flash_sale_price', $event->flash_sale_price) }}" placeholder="0,00">
                            </div>
                            <div class="form-group mb-2">
                                <label>Promoção relâmpago (termina em)</label>
                                <input name="flash_sale_ends_at" type="datetime-local" class="form-control"
                                    value="{{ old('flash_sale_ends_at', $event->flash_sale_ends_at ? $event->flash_sale_ends_at->format('Y-m-d\\TH:i') : '') }}">
                                <small class="text-muted d-block mt-1">Quando expirar, o valor volta ao normal automaticamente.</small>
                            </div>
                        </div>
                        
                        <div class="card card-outline card-secondary my-3 event-only-field">
                            <div class="card-header"><h3 class="card-title font-weight-bold">Preços por Lote</h3></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Preço Lote 1</label>
                                            <input name="batch_1_price" class="form-control mask-money" value="{{ old('batch_1_price', $event->batch_1_price ? number_format((float) $event->batch_1_price, 2, ',', '.') : '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Virada Lote 1 (Prazo final)</label>
                                            <input name="batch_1_deadline" type="datetime-local" class="form-control" value="{{ old('batch_1_deadline', $event->batch_1_deadline ? $event->batch_1_deadline->format('Y-m-d\\TH:i') : '') }}">
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Preço Lote 2</label>
                                            <input name="batch_2_price" class="form-control mask-money" value="{{ old('batch_2_price', $event->batch_2_price ? number_format((float) $event->batch_2_price, 2, ',', '.') : '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Virada Lote 2 (Prazo final)</label>
                                            <input name="batch_2_deadline" type="datetime-local" class="form-control" value="{{ old('batch_2_deadline', $event->batch_2_deadline ? $event->batch_2_deadline->format('Y-m-d\\TH:i') : '') }}">
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label>Preço Lote 3</label>
                                            <input name="batch_3_price" class="form-control mask-money" value="{{ old('batch_3_price', $event->batch_3_price ? number_format((float) $event->batch_3_price, 2, ',', '.') : '') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label>Virada Lote 3 (Prazo final)</label>
                                            <input name="batch_3_deadline" type="datetime-local" class="form-control" value="{{ old('batch_3_deadline', $event->batch_3_deadline ? $event->batch_3_deadline->format('Y-m-d\\TH:i') : '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-2 event-only-field">
                            <label id="coverImageLabel">{{ (!isset($event) || $event->type !== 'album' ? (request('type') !== 'album' ? 'Imagem Principal (Capa do Evento)' : 'Imagem Principal (Capa do Álbum)') : 'Imagem Principal (Capa do Álbum)') }}</label>
                            <input type="hidden" name="remove_image" value="0">
                            <div class="upload-box" data-max-size="5242880"
                                data-existing-url="{{ $event->image ? asset('storage/' . $event->image) : '' }}"
                                data-remove-input="[name='remove_image']">
                                <input type="file" name="image" accept="image/*" class="d-none">
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
                        <div class="row event-only-field">
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label>Local (Nome do Estabelecimento)</label>
                                    <div class="input-group">
                                        <input name="location" id="locationInput" class="form-control"
                                            value="{{ old('location', $event->location) }}"
                                            placeholder="Digite o nome do local ou estabelecimento..."
                                            autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" id="searchVenueBtn" title="Buscar estabelecimento">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="venueResults" class="list-group mt-1 shadow-sm" style="position:absolute;z-index:1050;width:calc(100% - 30px);max-height:250px;overflow-y:auto;display:none;"></div>
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Digite o nome do local e clique em <i class="fas fa-map-marker-alt text-info"></i> para buscar. Prioridade: seu estado.
                                    </small>
                                </div>

                                {{-- Checkbox: evento fora do estado --}}
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="eventOutOfState" name="event_out_of_state" value="1"
                                        {{ old('event_out_of_state', $event->event_out_of_state ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label text-sm" for="eventOutOfState">
                                        <i class="fas fa-plane-departure mr-1 text-muted"></i>
                                        Evento fora do meu estado
                                    </label>
                                </div>

                                <div class="form-group mb-2"><label>Endereço Completo</label>
                                    <div class="input-group"><input name="address" id="addressInput" class="form-control"
                                            value="{{ old('address', $event->address) }}"
                                            placeholder="Rua, numero, bairro, cidade - UF">
                                        <div class="input-group-append"><button type="button" class="btn btn-secondary"
                                                id="searchBtn"><i class="fas fa-search"></i> Buscar</button></div>
                                    </div>
                                </div>
                                <div class="form-group mb-2"><label>Latitude</label><input name="latitude" id="latInput"
                                        class="form-control" value="{{ old('latitude', $event->latitude) }}" readonly></div>
                                <div class="form-group mb-2"><label>Longitude</label><input name="longitude" id="lngInput"
                                        class="form-control" value="{{ old('longitude', $event->longitude) }}" readonly>
                                </div>
                                <input type="hidden" name="published" value="0">
                            </div>
                            <div class="col-md-6">
                                <label>Mapa (Clique para marcar)</label>
                                <div id="map" style="height: 300px; border-radius: 8px; border: 1px solid #ddd;"></div>
                            </div>
                        </div>

                        <div class="form-check mb-2 mt-4 event-only-field">
                                    <input type="checkbox" name="is_ticket_enabled" value="1" class="form-check-input" {{ old('is_ticket_enabled', $event->is_ticket_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label font-weight-bold" style="color:#007bff;"><i class="fas fa-qrcode mr-1"></i> Habilitar Validação de Entrada por QR Code</label>
                                    <small class="d-block text-muted">Quando ativo, o sistema criará um ingresso com QR Code e pontuará organizador e participante após validação.</small>
                                </div>
                                <div class="card card-outline card-info mb-3 event-only-field">
                                    <div class="card-body">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold"><i class="fas fa-map-marked-alt mr-1"></i> Restricao de leitura do QR Code</label>
                                            <select name="scanner_restriction_mode" id="scannerRestrictionMode" class="form-control">
                                                <option value="disabled" {{ old('scanner_restriction_mode', $event->scannerRestrictionMode()) === 'disabled' ? 'selected' : '' }}>Sem restricao de localizacao</option>
                                                <option value="exact" {{ old('scanner_restriction_mode', $event->scannerRestrictionMode()) === 'exact' ? 'selected' : '' }}>Localizacao exata do evento</option>
                                                <option value="radius" {{ old('scanner_restriction_mode', $event->scannerRestrictionMode()) === 'radius' ? 'selected' : '' }}>Margem de erro configuravel</option>
                                            </select>
                                            <small class="d-block text-muted mt-1">Use localizacao exata para exigir leitura no ponto do evento ou defina uma margem de erro em metros ou km.</small>
                                        </div>
                                        <div id="scannerRadiusFields" class="row mb-3">
                                            <div class="col-md-7">
                                                <label>Margem permitida</label>
                                                <input type="number" step="0.1" min="0.1" name="scanner_radius_value" class="form-control"
                                                    value="{{ old('scanner_radius_value', $event->scannerFormRadiusValue()) }}" placeholder="50">
                                            </div>
                                            <div class="col-md-5">
                                                <label>Unidade</label>
                                                <select name="scanner_radius_unit" class="form-control">
                                                    <option value="m" {{ old('scanner_radius_unit', $event->scannerFormRadiusUnit()) === 'm' ? 'selected' : '' }}>Metros</option>
                                                    <option value="km" {{ old('scanner_radius_unit', $event->scannerFormRadiusUnit()) === 'km' ? 'selected' : '' }}>Quilometros</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div id="scannerExactHint" class="alert alert-warning mb-0 d-none">
                                            A localizacao exata exige leitura no ponto configurado para o evento, com tolerancia tecnica de ate 5 metros.
                                        </div>

                                        <hr class="my-3">
                                        <h6 class="font-weight-bold mb-2"><i class="fas fa-clock mr-1"></i> Janela de validação do ingresso</h6>
                                        <p class="text-muted small mb-3">
                                            Configure quanto tempo antes do início e depois do término do evento o scanner aceita ingressos. Útil para permitir entrada antecipada e atrasados.
                                        </p>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="small font-weight-bold">
                                                    <i class="fas fa-door-open text-success mr-1"></i> Abrir scanner antes (minutos)
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" min="0" max="1440" step="5" name="scanner_early_minutes" class="form-control"
                                                        value="{{ old('scanner_early_minutes', $event->scanner_early_minutes ?? 0) }}" placeholder="0">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-light">min</span>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Ex: 120 = scanner abre 2h antes do evento</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small font-weight-bold">
                                                    <i class="fas fa-hourglass-end text-warning mr-1"></i> Aceitar após o fim (minutos)
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" min="0" max="1440" step="5" name="scanner_late_minutes" class="form-control"
                                                        value="{{ old('scanner_late_minutes', $event->scanner_late_minutes ?? 0) }}" placeholder="0">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-light">min</span>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Tolerância para atrasados após o término</small>
                                            </div>
                                        </div>

                                        <div class="alert alert-secondary mt-3 mb-0">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Toda leitura, com sucesso ou erro, fica registrada no sistema para auditoria.
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold"><i class="fas fa-eye mr-1"></i> Onde Exibir?</label>
                                    <select name="visibility" class="form-control" style="border-radius: 8px;">
                                        <option value="ambos" {{ old('visibility', $event->visibility ?? 'ambos') == 'ambos' ? 'selected' : '' }}>Ambos os locais</option>
                                        <option value="somos_unn" {{ old('visibility', $event->visibility ?? 'ambos') == 'somos_unn' ? 'selected' : '' }}>Somente Somos UNN</option>
                                        <option value="somos_unicas" {{ old('visibility', $event->visibility ?? 'ambos') == 'somos_unicas' ? 'selected' : '' }} style="color: #ec4899; font-weight: bold;">Somente Somos Únicas</option>
                                    </select>
                                </div>

                        <button class="btn btn-primary mt-3">Salvar</button>
                    </form>
                </div>

                <!-- TAB CERTIFICADO -->
                <div class="tab-pane event-only-field" id="legacy-certificate" role="tabpanel" aria-labelledby="cert-tab">
                    @if(!$event->exists)
                        <div class="alert alert-info border-0 shadow-sm">
                            <i class="fas fa-info-circle mr-2"></i> Você poderá configurar o certificado após salvar o evento
                            pela primeira vez.
                        </div>
                    @else
                        <form id="certForm" method="POST" action="{{ route('admin.events.update', $event) }}"
                            enctype="multipart/form-data">
                            @csrf
                             @method('PUT')

                             <div class="row">
                                <div class="col-12">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-secondary text-white small py-2">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                <span>Editor Visual (A4 Paisagem)</span>
                                                <div class="d-flex align-items-center">
                                                    <div class="input-group input-group-sm" style="width: 220px;">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">Zoom</span>
                                                        </div>
                                                        <select id="cert-zoom" class="custom-select custom-select-sm">
                                                            <option value="0.5">50%</option>
                                                            <option value="0.75">75%</option>
                                                            <option value="1" selected>100%</option>
                                                            <option value="1.25">125%</option>
                                                            <option value="1.5">150%</option>
                                                            <option value="2">200%</option>
                                                            <option value="2.5">250%</option>
                                                            <option value="3">300%</option>
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-outline-light" id="cert-fit">
                                                                Fit
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body bg-dark d-flex justify-content-center align-items-center p-4"
                                            style="min-height: 600px; overflow: auto;">
                                            <div id="cert-canvas"
                                                style="position: relative; width: 842px; height: 595px; background-color: white; box-shadow: 0 0 30px rgba(0,0,0,0.5); flex-shrink: 0; overflow: hidden;">
                                                @if($event->certificate_bg)
                                                    <img src="{{ asset($event->certificate_bg) }}" id="cert-bg-img"
                                                        style="width: 100%; height: 100%; object-fit: cover; position: absolute; z-index: 1;">
                                                @else
                                                    <div id="cert-bg-placeholder"
                                                        style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #ccc; z-index: 1; position: absolute; background: #eee;">
                                                        <div class="text-center">
                                                            <i class="fas fa-image fa-3x mb-2"></i>
                                                            <h5>Sem imagem de fundo</h5>
                                                            <p class="small">Faça upload no painel abaixo</p>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div id="cert-grid-overlay"
                                                    style="position: absolute; top:0; left:0; width: 100%; height: 100%; z-index: 5; pointer-events: none; display: none;">
                                                </div>
                                                <div id="cert-elements-layer"
                                                    style="position: absolute; top:0; left:0; width: 100%; height: 100%; z-index: 10;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-3">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-dark text-white font-weight-bold">Configurações</div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12 col-xl-6">
                                            <div class="form-group custom-control custom-switch mb-4">
                                                <input type="checkbox" class="custom-control-input" id="is_certificate_enabled"
                                                    name="is_certificate_enabled" value="1" {{ $event->is_certificate_enabled ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold"
                                                    for="is_certificate_enabled">Habilitar Certificado</label>
                                            </div>

                                            <div class="form-group">
                                                <label class="small text-muted text-uppercase font-weight-bold">Fundo do
                                                    Certificado</label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" name="certificate_bg"
                                                        accept="image/*" onchange="previewCertBg(this)">
                                                    <label class="custom-file-label">Escolher arquivo</label>
                                                </div>
                                                <small class="text-muted">Recomendado: 1920x1080px (PNG/JPG)</small>
                                            </div>

                                            <div class="form-group">
                                                <label class="small text-muted text-uppercase font-weight-bold">Ajuste do
                                                    Fundo</label>
                                                <select id="cert-bg-fit" class="form-control form-control-sm">
                                                    <option value="cover">Cover (cortar)</option>
                                                    <option value="stretch">Stretch (esticar)</option>
                                                </select>
                                            </div>

                                            @php
                                                $certTitleInput = data_get($event->certificate_settings, 'meta.titleText');
                                                if (!is_string($certTitleInput) || trim($certTitleInput) === '') {
                                                    $legacyCustom = data_get($event->certificate_settings, 'custom_title');
                                                    $legacyTitle = data_get($event->certificate_settings, 'title');
                                                    $certTitleInput = is_string($legacyCustom)
                                                        ? $legacyCustom
                                                        : (is_string($legacyTitle) ? $legacyTitle : 'CERTIFICADO DE PARTICIPAÇÃO');
                                                }

                                                $certPresentationInput = data_get($event->certificate_settings, 'meta.presentationText');
                                                if (!is_string($certPresentationInput)) {
                                                    $legacyCustomPres = data_get($event->certificate_settings, 'custom_presentation_text');
                                                    $legacyPres = data_get($event->certificate_settings, 'presentation_text');
                                                    $certPresentationInput = is_string($legacyCustomPres)
                                                        ? $legacyCustomPres
                                                        : (is_string($legacyPres) ? $legacyPres : '');
                                                }
                                            @endphp

                                            <div class="form-group">
                                                <label class="small text-muted text-uppercase font-weight-bold">Título do
                                                    Certificado</label>
                                                <input type="text" class="form-control" name="certificate_title"
                                                    id="certificate_title"
                                                    value="{{ old('certificate_title', $certTitleInput) }}"
                                                    placeholder="CERTIFICADO DE PARTICIPAÇÃO">
                                            </div>

                                            <div class="form-group">
                                                <label class="small text-muted text-uppercase font-weight-bold">Texto de
                                                    Apresentação</label>
                                                <textarea class="form-control" name="presentation_text" id="presentation_text"
                                                    rows="2"
                                                    placeholder="Texto de apresentação (opcional)">{{ old('presentation_text', $certPresentationInput) }}</textarea>
                                                <small class="text-muted">Texto acima do nome do participante (opcional)</small>
                                            </div>

                                            <div class="form-group mt-3">
                                                <label class="small text-muted text-uppercase font-weight-bold">Assinatura do
                                                    Organizador</label>
                                                @if($event->instructor_signature)
                                                    <div class="mb-2 text-center border p-2 bg-light rounded">
                                                        <img src="{{ asset($event->instructor_signature) }}"
                                                            style="max-height: 50px;" class="img-fluid">
                                                    </div>
                                                @endif
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" name="instructor_signature"
                                                        accept="image/*">
                                                    <label class="custom-file-label">Trocar assinatura</label>
                                                </div>
                                            </div>

                                            <hr>

                                            <h6 class="small text-muted text-uppercase font-weight-bold mb-2">Ferramentas</h6>
                                            <div class="bg-light p-2 rounded border mb-3">
                                                <div class="custom-control custom-switch mb-2">
                                                    <input type="checkbox" class="custom-control-input" id="cert-grid-enabled">
                                                    <label class="custom-control-label" for="cert-grid-enabled">Mostrar
                                                        grade</label>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Grade (%):</label>
                                                    <select id="cert-grid-step" class="form-control form-control-sm">
                                                        <option value="10">10%</option>
                                                        <option value="5" selected>5%</option>
                                                        <option value="2">2%</option>
                                                        <option value="1">1%</option>
                                                        <option value="0.5">0.5%</option>
                                                    </select>
                                                </div>

                                                <div class="custom-control custom-switch mb-2">
                                                    <input type="checkbox" class="custom-control-input" id="cert-snap-enabled"
                                                        checked>
                                                    <label class="custom-control-label" for="cert-snap-enabled">Snap na
                                                        grade</label>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Snap (%):</label>
                                                    <input type="number" id="cert-snap-step" class="form-control form-control-sm"
                                                        step="0.1" value="1">
                                                </div>

                                                <div class="form-group mb-0">
                                                    <label class="small mb-1">Nudge (setas):</label>
                                                    <select id="cert-nudge-step" class="form-control form-control-sm">
                                                        <option value="0.1">0.1%</option>
                                                        <option value="0.25">0.25%</option>
                                                        <option value="0.5" selected>0.5%</option>
                                                        <option value="1">1%</option>
                                                        <option value="2">2%</option>
                                                    </select>
                                                    <small class="text-muted">Dica: segure Shift para 5x</small>
                                                </div>
                                            </div>

                                                </div>

                                                <div class="col-12 col-xl-6 mt-3 mt-xl-0">
                                                    <hr class="d-xl-none">

                                                    <h6 class="small text-muted text-uppercase font-weight-bold mb-2">Camadas</h6>
                                            <div class="list-group mb-3" id="cert-layers"></div>

                                            <div id="cert-style-controls" style="display:none;">
                                                <label class="small text-muted text-uppercase font-weight-bold mb-2">Editar:
                                                    <span id="selected-elem-name" class="text-primary"></span></label>
                                                <div class="form-row">
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small mb-1">X (%)</label>
                                                            <input type="number" id="style-x"
                                                                class="form-control form-control-sm" step="0.1">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group mb-2">
                                                            <label class="small mb-1">Y (%)</label>
                                                            <input type="number" id="style-y"
                                                                class="form-control form-control-sm" step="0.1">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="custom-control custom-switch mb-2">
                                                    <input type="checkbox" class="custom-control-input" id="style-locked">
                                                    <label class="custom-control-label" for="style-locked">Bloquear
                                                        elemento</label>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Tamanho da Fonte (px)</label>
                                                    <input type="number" id="style-font-size"
                                                        class="form-control form-control-sm" min="8" max="120">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Camada (Z-Index)</label>
                                                    <input type="number" id="style-z-index"
                                                        class="form-control form-control-sm" min="0" max="999">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Cor do Texto</label>
                                                    <input type="color" id="style-color" class="form-control form-control-sm"
                                                        style="height: 30px;">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Peso da Fonte</label>
                                                    <select id="style-font-weight" class="form-control form-control-sm">
                                                        <option value="normal">Normal</option>
                                                        <option value="bold">Negrito</option>
                                                        <option value="500">Médio (500)</option>
                                                    </select>
                                                </div>
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">Fonte</label>
                                                    <select id="style-font-family" class="form-control form-control-sm">
                                                        <option value="Arial, sans-serif">Arial</option>
                                                        <option value="'Times New Roman', serif">Times New Roman</option>
                                                        <option value="Georgia, serif">Georgia</option>
                                                        <option value="'Courier New', monospace">Courier New</option>
                                                    </select>
                                                </div>
                                                <div id="logo-dims" class="form-row" style="display:none;">
                                                    <div class="col-6">
                                                        <label class="small mb-1">Largura</label>
                                                        <input type="number" id="logo-width"
                                                            class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="small mb-1">Altura</label>
                                                        <input type="number" id="logo-height"
                                                            class="form-control form-control-sm">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4">
                                                <label
                                                    class="small text-muted text-uppercase font-weight-bold mb-2">Visibilidade</label>
                                                <div class="list-group list-group-flush border rounded overflow-hidden">
                                                    @foreach(['student_name' => 'Nome do Participante', 'course_name' => 'Nome do Evento', 'completion_date' => 'Data do Evento', 'certificate_code' => 'Cód. Validação', 'author_name' => 'Organizador', 'workload_hours' => 'Carga Horária', 'title' => 'Título do Certificado', 'presentation_text' => 'Texto de Apresentação', 'instructor_signature' => 'Assinatura do Organizador', 'platform_logo' => 'Logo UNN'] as $tag => $label)
                                                        <div
                                                            class="list-group-item py-2 px-3 d-flex justify-content-between align-items-center bg-light">
                                                            <span class="small font-weight-bold">{{ $label }}</span>
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox" class="custom-control-input cert-toggle"
                                                                    id="toggle-{{ $tag }}" data-tag="{{ $tag }}" checked>
                                                                <label class="custom-control-label" for="toggle-{{ $tag }}"></label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                                </div>
                                            </div>

                                            <input type="hidden" name="certificate_settings" id="certificate_settings_input">
                                            <button type="submit" class="btn btn-primary btn-block mt-4" id="btn-save-cert">
                                                <i class="fas fa-save mr-1"></i> Salvar Certificado
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>

                <!-- TAB GALERIA -->
                @if($event->exists)
                <div class="tab-pane {{ request('tab') === 'gallery' ? 'show active' : '' }}" id="legacy-gallery" role="tabpanel" aria-labelledby="gallery-tab">
                    <div class="card shadow-sm border-0 mt-3 event-gallery-panel" style="overflow:hidden">
                        <div class="card-body">
                            <h4 class="mb-3">Galeria de Fotos e Vídeos do Evento</h4>
                            <p class="text-muted mb-4">Faça o upload de fotos ou vídeos do evento. As imagens receberão uma marca d'água automaticamente com o nome da plataforma e do organizador.</p>

                            {{-- Input real oculto, acionado via JS ao clicar na drop-zone --}}
                            <div class="alert alert-light border d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 event-gallery-toolbar">
                                <div class="pr-md-4 mb-3 mb-md-0 event-gallery-toolbar__content">
                                    <div class="font-weight-bold text-dark">Armazenamento e gestao rapida</div>
                                    <small class="text-muted d-block">
                                        As imagens ficam em <code>storage/app/public/events/{{ $event->id }}/gallery</code> e os videos em <code>storage/app/public/events/{{ $event->id }}/gallery/videos</code>.
                                    </small>
                                </div>
                                <a href="{{ route('admin.gallery.index', ['event_id' => $event->id]) }}?tab=gallery"
                                    class="btn event-gallery-toolbar__action rounded-pill px-4 py-2 font-weight-bold">
                                    <i class="fas fa-images mr-2"></i> Abrir galeria completa do evento
                                </a>
                            </div>

                            <input type="file" id="adminGalleryInput" multiple accept="image/*,video/*" style="display:none;">

                            <div class="premium-upload-box mb-4 event-gallery-upload-box" id="eventMediaUploadBox">
                                <div class="drop-zone-area p-5 text-center rounded event-gallery-drop-zone" id="eventDropZone"
                                    style="border: 2px dashed #d0dae8; background: #f8fafc; cursor:pointer; transition: all 0.2s;">
                                    <div class="drop-zone-content">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3 d-block"></i>
                                        <h5 class="font-weight-bold">Arraste fotos e vídeos aqui</h5>
                                        <p class="text-muted mb-3">ou <span class="text-primary" style="text-decoration:underline;">clique para selecionar</span> do seu dispositivo</p>
                                        <div class="d-flex justify-content-center flex-wrap gap-2 mb-2">
                                            <span class="badge badge-pill badge-primary px-3">Imagens (JPG, PNG)</span>
                                            <span class="badge badge-pill badge-info px-3">Vídeos (MP4)</span>
                                        </div>
                                        <small class="text-secondary">As imagens receberão marca d'água automaticamente</small>
                                    </div>
                                </div>

                                <!-- Progresso de Upload -->
                                <div id="eventUploadProgress" class="mt-3 d-none">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small font-weight-bold text-primary" id="eventUploadStatus">Enviando arquivos...</span>
                                        <span class="small font-weight-bold" id="eventUploadPercent">0%</span>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 5px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="eventUploadProgressBar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted" id="eventUploadDetails">0 / 0 arquivos</small>
                                        <small class="text-muted" id="eventUploadRemaining">calculando...</small>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Existing Media -->
                            <div class="row mt-4 event-gallery-media-grid" id="adminGalleryContainer">
                                @forelse($event->media as $media)
                                <div class="col-6 col-md-4 col-lg-3 mb-4 event-gallery-media-item" id="admin-media-{{ $media->id }}">
                                    <div class="card h-100 position-relative group">
                                        @if($media->type === 'image')
                                            <a href="{{ asset('storage/'.$media->file_path) }}" data-fancybox="gallery">
                                                <img src="{{ asset('storage/'.$media->file_path) }}" class="card-img-top object-cover" style="height: 150px; object-fit: cover;">
                                            </a>
                                        @else
                                            <div class="card-img-top bg-dark d-flex align-items-center justify-content-center" style="height: 150px;">
                                                <i class="fas fa-video text-white fa-3x"></i>
                                            </div>
                                        @endif
                                        @if($media->type === 'image')
                                            <button type="button" onclick="setAsCover({{ $media->id }})" class="btn btn-primary btn-sm position-absolute" style="top: 5px; left: 5px;" title="Definir como Capa">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        @endif
                                        <button type="button" onclick="deleteAdminMedia({{ $media->id }})" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @empty
                                    <div class="col-12" id="noMediaMessage">
                                        <div class="alert alert-light text-center border">Nenhuma mídia enviada ainda.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        .cert-element {
            padding: 4px;
            border-radius: 4px;
        }

        .cert-element:hover {
            background: rgba(0, 123, 255, 0.1);
            border: 1px dashed #007bff !important;
        }

        .ui-draggable-dragging {
            opacity: 0.7;
            z-index: 1000;
            border: 1px solid #007bff !important;
        }

        .event-gallery-panel,
        .event-gallery-panel .card-body {
            overflow-x: hidden;
        }

        .event-gallery-toolbar {
            gap: 1rem;
            flex-wrap: wrap;
        }

        .event-gallery-toolbar__content {
            min-width: 0;
            flex: 1 1 26rem;
        }

        .event-gallery-toolbar__action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: min(100%, 360px);
            white-space: normal;
            text-align: center;
            line-height: 1.35;
            color: #fff !important;
            background: linear-gradient(135deg, #0d6efd, #1d4ed8);
            border: 1px solid rgba(29, 78, 216, 0.35);
            box-shadow: 0 14px 30px rgba(29, 78, 216, 0.2);
        }

        .event-gallery-toolbar__action:hover,
        .event-gallery-toolbar__action:focus {
            color: #fff !important;
            background: linear-gradient(135deg, #0b5ed7, #1e40af);
            border-color: rgba(30, 64, 175, 0.45);
        }

        .event-gallery-upload-box,
        .event-gallery-drop-zone {
            overflow: hidden;
            max-width: 100%;
        }

        .event-gallery-media-grid {
            margin-left: -10px;
            margin-right: -10px;
            overflow-x: hidden;
        }

        .event-gallery-media-grid > [class*="col-"] {
            padding-left: 10px;
            padding-right: 10px;
        }

        @media (max-width: 767.98px) {
            .event-gallery-toolbar__action {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function () {
            function syncScannerRestrictionFields() {
                const mode = $('#scannerRestrictionMode').val() || 'disabled';
                $('#scannerRadiusFields').toggle(mode === 'radius');
                $('#scannerExactHint').toggleClass('d-none', mode !== 'exact');
            }

            $('#scannerRestrictionMode').on('change', syncScannerRestrictionFields);
            syncScannerRestrictionFields();

            function syncRegistryType() {
                const type = $('#registryType').val();
                const coverLabel = $('#coverImageLabel');
                if (type === 'album') {
                    $('.event-only-field').hide().find('input, select, textarea').not('#registryType').prop('disabled', true).prop('required', false);
                    if (coverLabel.length) coverLabel.text('Imagem Principal (Capa do Álbum)');
                    
                    // Se estiver na aba de certificado, volta para a geral
                    if ($('#certificate').hasClass('active')) {
                        $('#general-tab').tab('show');
                    }
                } else {
                    $('.event-only-field').show().find('input, select, textarea').prop('disabled', false).prop('required', function() {
                        const name = $(this).attr('name');
                        return name === 'start_at' || name === 'title';
                    });
                    if (coverLabel.length) coverLabel.text('Imagem Principal (Capa do Evento)');
                }
            }
            $('#registryType').on('change', syncRegistryType);
            syncRegistryType();

            // Reforço drástico para o comportamento das abas do Bootstrap 4
            $('a[data-toggle="tab"]').on('shown.bs.tab click', function (e) {
                const target = $(this).attr('href');
                if(!target || !target.startsWith('#legacy-')) return;
                
                // Forçar remoção de classes de todas as abas
                $('.tab-pane').removeClass('show active').css('display', 'none');
                
                // Ativar apenas a selecionada
                $(target).addClass('show active').css('display', 'block');
                
                // Sincronizar URL para manter estado em recarregamentos
                const url = new URL(window.location);
                url.searchParams.set('tab', target.replace('#legacy-', ''));
                window.history.replaceState({}, '', url);
            });
            
            // Ativação inicial baseada na URL
            const initialTab = new URLSearchParams(window.location.search).get('tab');
            if (initialTab && $('#legacy-' + initialTab).length) {
                $('[href="#legacy-' + initialTab + '"]').tab('show');
            }
        });
    </script>
@endpush

@push('scripts')
@if($event->exists)
<script>
// Conectar clique na drop-zone ao input file real
(function() {
    const dropZone = document.getElementById('eventDropZone');
    const fileInput = document.getElementById('adminGalleryInput');
    if (dropZone && fileInput) {
        dropZone.addEventListener('click', function() { fileInput.click(); });
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropZone.style.borderColor = '#3b82f6';
            dropZone.style.background = '#eff6ff';
        });
        dropZone.addEventListener('dragleave', function() {
            dropZone.style.borderColor = '#d0dae8';
            dropZone.style.background = '#f8fafc';
        });
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.style.borderColor = '#d0dae8';
            dropZone.style.background = '#f8fafc';
            if (e.dataTransfer.files.length) uploadAdminGallery(e.dataTransfer.files);
        });
        fileInput.addEventListener('change', function() {
            if (this.files.length) uploadAdminGallery(this.files);
        });
    }
})();

async function uploadAdminGallery(files) {
    if (!files || files.length === 0) return;

    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }

    const progressBox = document.getElementById('eventUploadProgress');
    const progressBar = document.getElementById('eventUploadProgressBar');
    const percentText = document.getElementById('eventUploadPercent');
    const statusText = document.getElementById('eventUploadStatus');
    const detailsText = document.getElementById('eventUploadDetails');
    const remainingText = document.getElementById('eventUploadRemaining');

    progressBox.classList.remove('d-none');
    progressBar.style.width = '0%';
    percentText.innerText = '0%';
    statusText.innerText = 'Preparando envio...';
    detailsText.innerText = `0 / ${files.length} arquivos`;

    const startedAt = Date.now();

    try {
        const response = await axios.post('{{ route("admin.events.media.store", $event) }}', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            onUploadProgress: (progressEvent) => {
                const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                progressBar.style.width = percent + '%';
                percentText.innerText = percent + '%';
                
                const elapsedSeconds = (Date.now() - startedAt) / 1000;
                const speed = progressEvent.loaded / elapsedSeconds;
                const remainingSeconds = speed > 0 ? (progressEvent.total - progressEvent.loaded) / speed : 0;
                
                statusText.innerText = percent < 100 ? 'Enviando arquivos...' : 'Processando no servidor...';
                detailsText.innerText = `${(progressEvent.loaded / 1024 / 1024).toFixed(2)} MB / ${(progressEvent.total / 1024 / 1024).toFixed(2)} MB`;
                
                if (percent < 100) {
                    const min = Math.floor(remainingSeconds / 60);
                    const sec = Math.round(remainingSeconds % 60);
                    remainingText.innerText = `restam ${min}m ${sec}s`;
                } else {
                    remainingText.innerText = 'quase pronto';
                }
            }
        });

        if (response.data.success) {
            const container = document.getElementById('adminGalleryContainer');
            const emptyState = document.getElementById('noMediaMessage');

            if (container && Array.isArray(response.data.media)) {
                response.data.media.slice().reverse().forEach((media) => {
                    const preview = media.type === 'image'
                        ? `<a href="${media.url}" data-fancybox="gallery"><img src="${media.url}" class="card-img-top object-cover" style="height: 150px; object-fit: cover;"></a>`
                        : `<div class="card-img-top bg-dark d-flex align-items-center justify-content-center" style="height: 150px;"><i class="fas fa-video text-white fa-3x"></i></div>`;

                    container.insertAdjacentHTML('afterbegin', `
                        <div class="col-6 col-md-4 col-lg-3 mb-4 event-gallery-media-item" id="admin-media-${media.id}">
                            <div class="card h-100 position-relative group">
                                ${preview}
                                <button type="button" onclick="deleteAdminMedia(${media.id})" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                                ${media.type === 'image' ? `
                                <button type="button" onclick="setAsCover(${media.id})" class="btn btn-primary btn-sm position-absolute" style="top: 5px; left: 5px;" title="Definir como Capa">
                                    <i class="fas fa-star"></i>
                                </button>
                                ` : ''}
                            </div>
                        </div>
                    `);
                });
            }

            if (emptyState) {
                emptyState.remove();
            }

            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: response.data.message || 'Arquivos enviados com sucesso.',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            throw new Error(response.data.message || 'Erro no upload');
        }
    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Erro no Upload',
            text: error.response?.data?.message || error.message || 'Ocorreu um erro ao enviar os arquivos.',
            confirmButtonText: 'Tentar novamente',
            confirmButtonColor: '#3085d6'
        });
        progressBox.classList.add('d-none');
    }
}



async function deleteAdminMedia(id) {
    const confirmed = await window.showConfirmDialog({
        title: 'Excluir mídia?',
        text: 'Tem certeza que deseja apagar esta mídia?',
        icon: 'warning'
    });

    if (!confirmed) return;

    try {
        const response = await fetch(`{{ route('admin.events.media.destroy', [$event, 'MEDIA_ID']) }}`.replace('MEDIA_ID', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();
        if (response.ok && data.success) {
            document.getElementById(`admin-media-${id}`).remove();
            const container = document.getElementById('adminGalleryContainer');
            if (container && container.children.length === 0) {
                container.innerHTML = `
                    <div class="col-12" id="noMediaMessage">
                        <div class="alert alert-light text-center border">Nenhuma midia enviada ainda.</div>
                    </div>
                `;
            }
        } else {
            Swal.fire('Erro', data.message || 'Erro ao excluir mídia.', 'error');
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Erro', 'Ocorreu um erro na requisição.', 'error');
    }
}

async function setAsCover(mediaId) {
    try {
        const response = await fetch('{{ route("admin.events.set-cover", $event) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ media_id: mediaId })
        });

        const data = await response.json();
        if (response.ok && data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Erro', data.message || 'Erro ao definir capa.', 'error');
        }
    } catch (e) {
        console.error(e);
        Swal.fire('Erro', 'Ocorreu um erro na requisição.', 'error');
    }
}
</script>
@endif
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        $(document).ready(function () {
            // Map Logic
            var initialLat = {{ $event->latitude ?? '-23.5505' }};
            var initialLng = {{ $event->longitude ?? '-46.6333' }};
            var zoom = {{ $event->latitude ? 15 : 10 }};
            var mapEl = document.getElementById('map');
            var map = null;
            var marker = null;

            function initMap() {
                if (!mapEl || map) return;
                try {
                    map = L.map('map').setView([initialLat, initialLng], zoom);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
                    if ({{ $event->latitude ? 'true' : 'false' }}) marker = L.marker([initialLat, initialLng]).addTo(map);
                    map.on('click', function (e) { setMarker(e.latlng.lat, e.latlng.lng); });
                    // Força redesenho após render (corrige mapa cinza em tabs ou divs ocultos)
                    setTimeout(function () { map.invalidateSize(); }, 250);
                } catch (err) {
                    console.error('Erro ao inicializar mapa:', err);
                }
            }

            // Inicializa com delay para garantir que o div tenha dimensões
            setTimeout(initMap, 100);

            // Re-invalidar mapa quando tab é mostrada
            $('a[data-toggle="tab"], a[data-toggle="pill"]').on('shown.bs.tab', function () {
                if (map) setTimeout(function () { map.invalidateSize(); }, 50);
            });

            function setMarker(lat, lng) {
                if (!map) return;
                if (marker) marker.setLatLng([lat, lng]);
                else marker = L.marker([lat, lng]).addTo(map);
                document.getElementById('latInput').value = lat;
                document.getElementById('lngInput').value = lng;
            }
            $('#searchBtn').on('click', function () {
                var query = document.getElementById('addressInput').value;
                if (!query) return;
                toastr.info('Buscando endereço...');
                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query) + '&countrycodes=br&limit=5')
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            var lat = data[0].lat; var lon = data[0].lon;
                            map.setView([lat, lon], 16); setMarker(lat, lon);
                            toastr.success('Endereço encontrado!');
                        } else toastr.error('Endereço não encontrado.');
                    }).catch(err => toastr.error('Erro na busca.'));
            });

            // ── Busca de estabelecimento por nome (Nominatim) ──
            (function() {
                var locationInput = document.getElementById('locationInput');
                var venueResults = document.getElementById('venueResults');
                var searchVenueBtn = document.getElementById('searchVenueBtn');
                var outOfStateCheck = document.getElementById('eventOutOfState');
                var debounceTimer = null;

                // Estado do usuario registrante (prioridade na busca)
                var userState = @json(auth()->user()->state ?? '');
                var userCity = @json(auth()->user()->city ?? '');

                function buildSearchQuery(text) {
                    var query = text.trim();
                    if (!query) return '';

                    // Se "fora do estado" nao esta marcado, prioriza o estado do usuario
                    if (!outOfStateCheck.checked && userState) {
                        query += ', ' + userState + ', Brasil';
                    } else {
                        query += ', Brasil';
                    }
                    return query;
                }

                function searchVenue() {
                    var text = locationInput.value.trim();
                    if (text.length < 3) {
                        venueResults.style.display = 'none';
                        return;
                    }

                    venueResults.innerHTML = '<div class="list-group-item text-center py-3"><i class="fas fa-spinner fa-spin mr-2"></i>Buscando estabelecimentos...</div>';
                    venueResults.style.display = 'block';

                    var localQuery = text + (userState ? ', ' + userState + ', Brasil' : ', Brasil');
                    var nationalQuery = text + ', Brasil';
                    var localUrl = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(localQuery) + '&countrycodes=br&limit=20&addressdetails=1';
                    var nationalUrl = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(nationalQuery) + '&countrycodes=br&limit=40&addressdetails=1';
                    var headers = { 'Accept-Language': 'pt-BR' };

                    if (outOfStateCheck.checked) {
                        fetch(nationalUrl, { headers: headers })
                            .then(function(r) { return r.json(); })
                            .then(function(data) { renderVenueResults(data || []); })
                            .catch(function() { venueResults.innerHTML = '<div class="list-group-item text-danger text-center py-3"><i class="fas fa-exclamation-triangle mr-1"></i>Erro na busca</div>'; });
                    } else {
                        Promise.all([
                            fetch(localUrl, { headers: headers }).then(function(r) { return r.json(); }).catch(function() { return []; }),
                            new Promise(function(resolve) {
                                setTimeout(function() {
                                    fetch(nationalUrl, { headers: headers }).then(function(r) { return r.json(); }).then(resolve).catch(function() { resolve([]); });
                                }, 1100);
                            })
                        ]).then(function(results) {
                            var local = results[0] || [];
                            var national = results[1] || [];
                            var seen = {};
                            var combined = [];
                            local.forEach(function(item) { var key = item.lat + ',' + item.lon; if (!seen[key]) { seen[key] = true; item._isLocal = true; combined.push(item); } });
                            national.forEach(function(item) { var key = item.lat + ',' + item.lon; if (!seen[key]) { seen[key] = true; combined.push(item); } });
                            renderVenueResults(combined);
                        });
                    }
                }

                function renderVenueResults(data) {
                    if (!data || data.length === 0) {
                        venueResults.innerHTML = '<div class="list-group-item text-muted text-center py-3"><i class="fas fa-search mr-1"></i>Nenhum resultado encontrado</div>';
                        return;
                    }

                    var items = data.slice(0, 20);
                    var html = '<div class="list-group-item bg-light py-2 px-3 text-xs font-weight-bold text-muted"><i class="fas fa-list mr-1"></i>' + Math.min(data.length, 20) + ' de ' + data.length + ' resultados' + (data.length > 20 ? ' (refine sua busca)' : '') + '</div>';

                    items.forEach(function(item, idx) {
                        var addr = item.address || {};
                        var shortName = (addr.amenity || addr.tourism || addr.leisure || addr.building || addr.shop || '').trim();
                        var city = addr.city || addr.town || addr.village || '';
                        var state = addr.state || '';
                        var road = addr.road || '';
                        var number = addr.house_number || '';
                        var neighbourhood = addr.suburb || addr.neighbourhood || '';
                        var fullAddress = [road, number, neighbourhood, city, state].filter(Boolean).join(', ');
                        var displayTitle = shortName || item.display_name.split(',')[0];
                        var isLocal = item._isLocal || (userState && state.toLowerCase().indexOf(userState.toLowerCase()) !== -1);

                        html += '<a href="#" class="list-group-item list-group-item-action venue-result py-2 px-3" '
                            + 'data-lat="' + item.lat + '" '
                            + 'data-lon="' + item.lon + '" '
                            + 'data-name="' + displayTitle.replace(/"/g, '&quot;') + '" '
                            + 'data-address="' + fullAddress.replace(/"/g, '&quot;') + '">'
                            + '<div class="d-flex align-items-start gap-2">'
                            + '<span class="font-weight-bold ' + (isLocal ? 'text-success' : 'text-muted') + '" style="min-width:20px">' + (idx + 1) + '.</span>'
                            + '<div class="flex-1">'
                            + '<strong class="d-block text-sm">' + displayTitle + '</strong>'
                            + '<small class="text-muted d-block">' + fullAddress + '</small>'
                            + (isLocal ? '<span class="badge badge-success badge-sm mt-1"><i class="fas fa-check mr-1"></i>Proximo</span>' : '')
                            + '</div>'
                            + '</div></a>';
                    });

                    venueResults.innerHTML = html;

                    venueResults.querySelectorAll('.venue-result').forEach(function(el) {
                        el.addEventListener('click', function(e) {
                            e.preventDefault();
                            var lat = parseFloat(this.dataset.lat);
                            var lon = parseFloat(this.dataset.lon);
                            locationInput.value = this.dataset.name;
                            document.getElementById('addressInput').value = this.dataset.address;
                            setMarker(lat, lon);
                            if (map) map.setView([lat, lon], 16);
                            venueResults.style.display = 'none';
                            toastr.success('Local selecionado: ' + this.dataset.name);
                        });
                    });
                }

                // Buscar ao clicar no botao
                if (searchVenueBtn) {
                    searchVenueBtn.addEventListener('click', searchVenue);
                }

                // Buscar ao pressionar Enter no campo
                if (locationInput) {
                    locationInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            searchVenue();
                        }
                    });
                }

                // Fechar resultados ao clicar fora
                document.addEventListener('click', function(e) {
                    if (!venueResults.contains(e.target) && e.target !== locationInput && e.target !== searchVenueBtn) {
                        venueResults.style.display = 'none';
                    }
                });

                // Quando muda o checkbox "fora do estado", limpa resultados
                if (outOfStateCheck) {
                    outOfStateCheck.addEventListener('change', function() {
                        venueResults.style.display = 'none';
                    });
                }
            })();

            // Certificate Logic
            if ('{{ $event->exists }}' == '') return;
            let rawCertSettings = {!! $event->certificate_settings ? json_encode($event->certificate_settings) : 'null' !!};

            let certDoc = {
                schemaVersion: 2,
                meta: { backgroundFit: 'cover' },
                elements: {}
            };

            const isV2 = rawCertSettings
                && typeof rawCertSettings === 'object'
                && rawCertSettings.schemaVersion === 2
                && rawCertSettings.elements
                && typeof rawCertSettings.elements === 'object';

            if (isV2) {
                certDoc = rawCertSettings;
                certDoc.meta = (certDoc.meta && typeof certDoc.meta === 'object') ? certDoc.meta : {};
                certDoc.elements = (certDoc.elements && typeof certDoc.elements === 'object') ? certDoc.elements : {};
            } else {
                certDoc.meta = certDoc.meta || {};
                if (rawCertSettings && typeof rawCertSettings.backgroundFit === 'string') {
                    certDoc.meta.backgroundFit = rawCertSettings.backgroundFit;
                }

                if (rawCertSettings && typeof rawCertSettings.custom_title === 'string') {
                    certDoc.meta.titleText = rawCertSettings.custom_title;
                } else if (rawCertSettings && typeof rawCertSettings.title === 'string') {
                    certDoc.meta.titleText = rawCertSettings.title;
                }

                if (rawCertSettings && typeof rawCertSettings.custom_presentation_text === 'string') {
                    certDoc.meta.presentationText = rawCertSettings.custom_presentation_text;
                } else if (rawCertSettings && typeof rawCertSettings.presentation_text === 'string') {
                    certDoc.meta.presentationText = rawCertSettings.presentation_text;
                }

                if (rawCertSettings && typeof rawCertSettings === 'object') {
                    Object.keys(rawCertSettings).forEach((k) => {
                        const v = rawCertSettings[k];
                        if (v && typeof v === 'object' && v.x !== undefined && v.y !== undefined) {
                            certDoc.elements[k] = v;
                        }
                    });
                }
            }

            certDoc.meta.backgroundFit = certDoc.meta.backgroundFit || 'cover';

            let certSettings = certDoc.elements;

            @php
                $logoAuth = \App\Models\Setting::get('logo_auth') ?: \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
                $logoAuthSrc = $logoAuth ? asset(ltrim($logoAuth, '/')) : asset('img/logo.svg');
            @endphp
            const platformLogoUrl = "{{ $logoAuthSrc }}";
            const instructorSignatureUrl = "{{ $event->instructor_signature ? asset($event->instructor_signature) : '' }}";
            let instructorSignaturePreviewUrl = instructorSignatureUrl;

            const initialTitleText = ($('#certificate_title').length ? ($('#certificate_title').val() || certDoc.meta.titleText) : certDoc.meta.titleText) || 'CERTIFICADO DE PARTICIPAÇÃO';
            const initialPresentationText = ($('#presentation_text').length ? ($('#presentation_text').val() || certDoc.meta.presentationText) : certDoc.meta.presentationText) || '';

            const defaultTags = {
                'student_name': { x: 50, y: 40, text: '[Nome do Participante]', fontSize: 30, color: '#000000', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                'course_name': { x: 50, y: 55, text: '{{ $event->title }}', fontSize: 24, color: '#333333', fontWeight: 'bold', fontFamily: 'Arial, sans-serif' },
                'completion_date': { x: 50, y: 65, text: 'Participou em: {{ $event->start_at ? $event->start_at->format("d/m/Y") : "01/01/2026" }}', fontSize: 16, color: '#555555', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                'certificate_code': { x: 50, y: 85, text: 'Validação: ABC-123', fontSize: 12, color: '#999999', fontWeight: 'normal', fontFamily: 'Arial, sans-serif' },
                'author_name': { x: 50, y: 90, text: 'UNN Eventos', fontSize: 18, color: '#333333', fontWeight: 'bold', fontFamily: 'Arial, sans-serif', zIndex: 10 },
                'workload_hours': { x: 80, y: 90, text: 'Evento', fontSize: 14, color: '#666666', fontWeight: 'normal', fontFamily: 'Arial, sans-serif', zIndex: 10 },
                'title': { x: 10, y: 18, text: initialTitleText, fontSize: 34, color: '#000000', fontWeight: 'bold', fontFamily: 'Arial, sans-serif', zIndex: 15, visible: false, multiline: true, maxWidth: 700, textAlign: 'center' },
                'presentation_text': { x: 10, y: 28, text: initialPresentationText, fontSize: 16, color: '#333333', fontWeight: 'normal', fontFamily: 'Arial, sans-serif', zIndex: 15, visible: false, multiline: true, maxWidth: 700, textAlign: 'center' },
                'instructor_signature': { x: 70, y: 80, text: 'Assinatura do Organizador', fontSize: 12, color: '#6c757d', fontWeight: 'normal', fontFamily: 'Arial, sans-serif', width: 200, height: 60, zIndex: 10, visible: !!instructorSignatureUrl },
                'platform_logo': { x: 50, y: 10, text: 'LOGO', fontSize: 36, color: '#0066cc', fontWeight: 'bold', fontFamily: 'Georgia, serif', width: 120, height: 60, mandatory: true, zIndex: 20 }
            };

            @php
                $certificateTagLabels = [
                    'student_name' => 'Nome do Participante',
                    'course_name' => 'Nome do Evento',
                    'completion_date' => 'Data do Evento',
                    'certificate_code' => 'Cód. Validação',
                    'author_name' => 'Organizador',
                    'workload_hours' => 'Info Extra',
                    'title' => 'Título do Certificado',
                    'presentation_text' => 'Texto de Apresentação',
                    'instructor_signature' => 'Assinatura do Organizador',
                    'platform_logo' => 'Logo da Plataforma',
                ];
            @endphp
            const tagLabels = @json($certificateTagLabels);

            $.each(defaultTags, function (key, val) {
                if (!certSettings[key]) {
                    certSettings[key] = val;
                }
            });

            // Ensure deterministic defaults for v2 fields (without changing x/y)
            $.each(certSettings, function (key, data) {
                if (!data || typeof data !== 'object') return;
                if (data.visible === undefined) data.visible = true;
                if (data.locked === undefined) data.locked = false;
                if (data.zIndex === undefined) data.zIndex = (key === 'platform_logo') ? 20 : 10;
            });
            if (certSettings['platform_logo']) {
                certSettings['platform_logo'].mandatory = true;
                certSettings['platform_logo'].visible = true;
            }
            if (certSettings['instructor_signature']) {
                if (certSettings['instructor_signature'].width === undefined) certSettings['instructor_signature'].width = 200;
                if (certSettings['instructor_signature'].height === undefined) certSettings['instructor_signature'].height = 60;
            }

            if (certSettings['title'] && $('#certificate_title').length) {
                certSettings['title'].text = $('#certificate_title').val() || certSettings['title'].text || '';
            }
            if (certSettings['presentation_text'] && $('#presentation_text').length) {
                certSettings['presentation_text'].text = $('#presentation_text').val() || certSettings['presentation_text'].text || '';
            }

            const $canvas = $('#cert-elements-layer');
            let activeElementId = null;
            let customFonts = [];
            const BASE_W = 842;
            const BASE_H = 595;

            function applyZoom(zoom) {
                const z = Math.max(0.25, Math.min(zoom || 1, 3));
                $('#cert-canvas').css({
                    width: (BASE_W * z) + 'px',
                    height: (BASE_H * z) + 'px'
                });
            }

            function fitCanvas() {
                const $wrap = $('#cert-canvas').parent();
                const availW = $wrap.width() - 20;
                const availH = $wrap.height() - 20;
                const target = Math.max(0.25, Math.min(availW / BASE_W, availH / BASE_H));

                const opts = $('#cert-zoom option').map(function () { return parseFloat($(this).val()); }).get();
                let nearest = opts[0] || 1;
                opts.forEach(function (v) {
                    if (Math.abs(v - target) < Math.abs(nearest - target)) nearest = v;
                });

                $('#cert-zoom').val(nearest.toString()).trigger('change');
            }

            $('#cert-zoom').on('change', function () {
                applyZoom(parseFloat($(this).val()));
            });
            $('#cert-fit').on('click', function () {
                fitCanvas();
            });
            applyZoom(parseFloat($('#cert-zoom').val()) || 1);

            function scheduleFitCanvas() {
                setTimeout(function () {
                    const $tab = $('#certificate');
                    if ($tab.length && ($tab.hasClass('active') || $tab.hasClass('show'))) {
                        fitCanvas();
                    }
                }, 50);
            }

            $('a[data-toggle="tab"][href="#certificate"]').on('shown.bs.tab', function () {
                scheduleFitCanvas();
            });
            scheduleFitCanvas();

            function applyBackgroundFit() {
                const fit = ($('#cert-bg-fit').val() || 'cover') === 'stretch' ? 'fill' : 'cover';
                $('#cert-bg-img').css('object-fit', fit);
            }

            $('#cert-bg-fit').val((certDoc.meta && certDoc.meta.backgroundFit) ? certDoc.meta.backgroundFit : 'cover');
            $('#cert-bg-fit').on('change', function () {
                certDoc.meta.backgroundFit = $(this).val() || 'cover';
                applyBackgroundFit();
            });
            applyBackgroundFit();

            function updateGridOverlay() {
                const enabled = $('#cert-grid-enabled').is(':checked');
                const step = parseFloat($('#cert-grid-step').val()) || 5;
                const $grid = $('#cert-grid-overlay');

                if (!enabled) {
                    $grid.hide();
                    return;
                }

                $grid.show().css({
                    backgroundImage:
                        'linear-gradient(to right, rgba(0, 123, 255, 0.25) 1px, transparent 1px), ' +
                        'linear-gradient(to bottom, rgba(0, 123, 255, 0.25) 1px, transparent 1px)',
                    backgroundSize: step + '% ' + step + '%'
                });
            }

            $('#cert-grid-enabled').on('change', updateGridOverlay);
            $('#cert-grid-step').on('change', updateGridOverlay);
            updateGridOverlay();

            const fontsApiUrl = @json(
                app('router')->has('admin.fonts.api.active')
                    ? route('admin.fonts.api.active')
                    : (app('router')->has('panel.admin.fonts.api.active') ? route('panel.admin.fonts.api.active') : null)
            );

            // Load Custom Fonts
            if (fontsApiUrl) {
                $.ajax({
                    url: fontsApiUrl,
                    type: 'GET',
                    success: function (fonts) {
                        customFonts = fonts;
                        fonts.forEach(font => {
                            $('#style-font-family').append(`<option value="${font.font_family}">${font.name}</option>`);

                            if (font.type === 'google_link' && font.google_font_url) {
                                $('head').append(`<link href="${font.google_font_url}" rel="stylesheet">`);
                            } else if (font.type === 'file' && font.file_path) {
                                const fontUrl = '{{ asset('')}}' + font.file_path;
                                $('head').append(`<style>@font-face { font-family: '${font.font_family}'; src: url('${fontUrl}'); }</style>`);
                            }
                        });
                    }
                });
            }

            function updateLayersList() {
                const $list = $('#cert-layers');
                if (!$list.length) return;

                $list.empty();

                const items = Object.keys(certSettings)
                    .filter((k) => certSettings[k] && typeof certSettings[k] === 'object' && certSettings[k].x !== undefined && certSettings[k].y !== undefined)
                    .map((k) => {
                        const z = (certSettings[k].zIndex !== undefined) ? parseInt(certSettings[k].zIndex) : (k === 'platform_logo' ? 20 : 10);
                        const visible = (k === 'platform_logo') ? true : (certSettings[k].visible !== false);
                        const locked = !!certSettings[k].locked;
                        return { key: k, zIndex: isNaN(z) ? 10 : z, visible, locked };
                    })
                    .sort((a, b) => (b.zIndex - a.zIndex));

                items.forEach((item) => {
                    const label = tagLabels[item.key] || item.key;
                    const $btn = $('<button type="button">')
                        .addClass('list-group-item list-group-item-action py-1 px-2 d-flex align-items-center justify-content-between')
                        .toggleClass('active', activeElementId === item.key);

                    const $left = $('<span>').addClass('text-truncate').text(label);
                    const $right = $('<span>').addClass('d-flex align-items-center');

                    if (item.key !== 'platform_logo' && !item.visible) {
                        $right.append($('<span>').addClass('badge badge-secondary mr-1').text('Oculto'));
                    }
                    if (item.locked) {
                        $right.append($('<span>').addClass('badge badge-warning mr-1').text('Lock'));
                    }

                    $right.append($('<span>').addClass('badge badge-light border').text('z:' + item.zIndex));

                    $btn.append($left).append($right);
                    $btn.on('click', function () {
                        $('#el-' + item.key).trigger('mousedown');
                    });

                    $list.append($btn);
                });
            }

            function renderElements() {
                $canvas.empty();

                $.each(certSettings, function (key, data) {
                    if (!data || typeof data !== 'object' || data.x === undefined || data.y === undefined) return;

                    let $el = $('<div>')
                        .addClass('cert-element')
                        .attr('id', 'el-' + key)
                        .attr('data-tag', key)
                        .css({
                            position: 'absolute',
                            left: data.x + '%',
                            top: data.y + '%',
                            fontSize: (data.fontSize || 16) + 'px',
                            color: data.color || '#000000',
                            fontWeight: data.fontWeight || 'normal',
                            fontFamily: data.fontFamily || 'Arial, sans-serif',
                            cursor: data.locked ? 'not-allowed' : 'move',
                            whiteSpace: data.multiline ? 'pre-line' : 'nowrap',
                            width: (data.multiline && data.maxWidth) ? (data.maxWidth + 'px') : 'auto',
                            textAlign: data.textAlign || 'left',
                            border: '1px dashed transparent',
                            padding: '4px',
                            zIndex: data.zIndex || 10,
                            display: (key !== 'platform_logo' && data.visible === false) ? 'none' : 'block'
                        });

                    if (key === 'platform_logo') {
                        $el.css({
                            width: (data.width || 120) + 'px',
                            height: (data.height || 60) + 'px',
                            padding: '0px',
                            backgroundImage: 'url(\"' + platformLogoUrl + '\")',
                            backgroundSize: '100% 100%',
                            backgroundRepeat: 'no-repeat',
                            backgroundPosition: 'center'
                        });
                        $el.text('');
                    } else if (key === 'instructor_signature') {
                        const w = (data.width || 200);
                        const h = (data.height || 60);
                        const url = instructorSignaturePreviewUrl || '';
                        const isHidden = (data.visible === false);
                        const showAs = isHidden ? 'none' : (url ? 'block' : 'flex');

                        $el.css({
                            width: w + 'px',
                            height: h + 'px',
                            padding: '0px',
                            backgroundImage: url ? ('url(\"' + url + '\")') : 'none',
                            backgroundSize: 'contain',
                            backgroundRepeat: 'no-repeat',
                            backgroundPosition: 'center',
                            backgroundColor: url ? 'transparent' : '#f8f9fa',
                            color: url ? 'transparent' : '#6c757d',
                            fontSize: '12px',
                            borderColor: url ? 'transparent' : '#adb5bd',
                            display: showAs,
                            alignItems: 'center',
                            justifyContent: 'center',
                        });
                        $el.text(url ? '' : 'Assinatura');
                    } else {
                        $el.text(data.text || '');
                    }

                    $el.on('mousedown', function (e) {
                        $('.cert-element').css('border-color', 'transparent');
                        $(this).css('border-color', '#007bff');
                        activeElementId = key;

                        $('#selected-elem-name').text(tagLabels[key] || data.text || key);
                        $('#style-x').val(parseFloat(data.x ?? 0).toFixed(2));
                        $('#style-y').val(parseFloat(data.y ?? 0).toFixed(2));
                        $('#style-locked').prop('checked', !!data.locked);
                        $('#style-font-size').val(data.fontSize || 16);
                        $('#style-z-index').val(data.zIndex || 10);
                        $('#style-color').val(data.color || '#000000');
                        $('#style-font-weight').val(data.fontWeight || 'normal');
                        $('#style-font-family').val(data.fontFamily || 'Arial, sans-serif');

                        $('#cert-style-controls').show();
                        $('#logo-dims').toggle(key === 'platform_logo');

                        if (key === 'platform_logo') {
                            $('#logo-width').val(data.width || 120);
                            $('#logo-height').val(data.height || 60);
                            $('#logo-width, #logo-height').prop('disabled', !!data.locked);
                        }

                        updateLayersList();
                        e.stopPropagation();
                    });

                    $canvas.append($el);

                    if (key === 'platform_logo' || key === 'instructor_signature') {
                        $el.resizable({
                            aspectRatio: false,
                            disabled: !!data.locked,
                            handles: 'n, e, s, w, ne, se, sw, nw',
                            stop: function (event, ui) {
                                let w = ui.size.width;
                                let h = ui.size.height;
                                certSettings[key].width = w;
                                certSettings[key].height = h;

                                if (key === 'platform_logo') {
                                    $('#logo-width').val(Math.round(w));
                                    $('#logo-height').val(Math.round(h));
                                }
                            }
                        });
                    }
                });

                $('.cert-element').draggable({
                    containment: "#cert-canvas",
                    scroll: false,
                    start: function () {
                        let key = $(this).data('tag');
                        if (certSettings[key] && certSettings[key].locked) {
                            return false;
                        }
                    },
                    stop: function (event, ui) {
                        let key = $(this).data('tag');
                        let parentW = $('#cert-canvas').width();
                        let parentH = $('#cert-canvas').height();

                        let x = (ui.position.left / parentW) * 100;
                        let y = (ui.position.top / parentH) * 100;

                        if ($('#cert-snap-enabled').is(':checked')) {
                            let step = parseFloat($('#cert-snap-step').val()) || 1;
                            x = Math.round(x / step) * step;
                            y = Math.round(y / step) * step;
                            $(this).css({ left: x + '%', top: y + '%' });
                        }

                        certSettings[key].x = x;
                        certSettings[key].y = y;

                        if (activeElementId === key) {
                            $('#style-x').val(parseFloat(x).toFixed(2));
                            $('#style-y').val(parseFloat(y).toFixed(2));
                        }

                        updateLayersList();
                    }
                });

                // Sync visibility toggles from persisted state
                $('.cert-toggle').each(function () {
                    let key = $(this).data('tag');
                    if (key === 'platform_logo') {
                        $(this).prop('checked', true);
                        $('#el-platform_logo').show();
                        return;
                    }

                    const visible = certSettings[key] ? (certSettings[key].visible !== false) : true;
                    $(this).prop('checked', visible);
                    if (!visible) {
                        $('#el-' + key).hide();
                    }
                });

                // Load logo size from settings
                if (certSettings['platform_logo']) {
                    $('#logo-width').val(certSettings['platform_logo'].width || 120);
                    $('#logo-height').val(certSettings['platform_logo'].height || 60);
                    $('#logo-width, #logo-height').prop('disabled', !!certSettings['platform_logo'].locked);
                }

                updateLayersList();
            }

            renderElements();

            // Live sync: title/presentation in the canvas
            $('#certificate_title').on('input', function () {
                const val = $(this).val() || '';
                certDoc.meta = certDoc.meta || {};
                certDoc.meta.titleText = val;
                if (certSettings['title']) {
                    certSettings['title'].text = val;
                    $('#el-title').text(val);
                }
            });

            $('#presentation_text').on('input', function () {
                const val = $(this).val() || '';
                certDoc.meta = certDoc.meta || {};
                certDoc.meta.presentationText = val;
                if (certSettings['presentation_text']) {
                    certSettings['presentation_text'].text = val;
                    $('#el-presentation_text').text(val);
                }
            });

            // Live preview: instructor signature in the canvas
            $('input[name="instructor_signature"]').on('change', function () {
                if (!this.files || !this.files[0]) return;
                const reader = new FileReader();
                reader.onload = function (e) {
                    instructorSignaturePreviewUrl = e.target.result;

                    if (certSettings['instructor_signature']) {
                        certSettings['instructor_signature'].visible = true;
                    }
                    $('.cert-toggle[data-tag="instructor_signature"]').prop('checked', true);

                    const $sig = $('#el-instructor_signature');
                    if ($sig.length) {
                        $sig.css({
                            backgroundImage: 'url("' + instructorSignaturePreviewUrl + '")',
                            backgroundSize: 'contain',
                            backgroundRepeat: 'no-repeat',
                            backgroundPosition: 'center',
                            backgroundColor: 'transparent',
                            borderColor: 'transparent',
                            color: 'transparent',
                            display: 'block',
                        }).text('');
                    }

                    updateLayersList();
                };
                reader.readAsDataURL(this.files[0]);
            });

            // Style Change Listeners
            $('#style-font-size').on('input', function () {
                if (!activeElementId) return;
                let val = $(this).val();
                certSettings[activeElementId].fontSize = val;
                $('#el-' + activeElementId).css('font-size', val + 'px');
            });

            $('#style-z-index').on('input', function () {
                if (!activeElementId) return;
                let val = $(this).val();
                certSettings[activeElementId].zIndex = val;
                $('#el-' + activeElementId).css('z-index', val);
                updateLayersList();
            });

            $('#style-color').on('input', function () {
                if (!activeElementId) return;
                let val = $(this).val();
                certSettings[activeElementId].color = val;
                $('#el-' + activeElementId).css('color', val);
            });

            $('#style-font-weight').on('change', function () {
                if (!activeElementId) return;
                let val = $(this).val();
                certSettings[activeElementId].fontWeight = val;
                $('#el-' + activeElementId).css('font-weight', val);
            });

            $('#style-font-family').on('change', function () {
                if (!activeElementId) return;
                let val = $(this).val();
                certSettings[activeElementId].fontFamily = val;
                $('#el-' + activeElementId).css('font-family', val);
            });

            $('#style-x').on('input', function () {
                if (!activeElementId) return;
                let val = parseFloat($(this).val());
                if (isNaN(val)) return;
                certSettings[activeElementId].x = val;
                $('#el-' + activeElementId).css('left', val + '%');
            });

            $('#style-y').on('input', function () {
                if (!activeElementId) return;
                let val = parseFloat($(this).val());
                if (isNaN(val)) return;
                certSettings[activeElementId].y = val;
                $('#el-' + activeElementId).css('top', val + '%');
            });

            $('#style-locked').on('change', function () {
                if (!activeElementId) return;
                const locked = $(this).is(':checked');
                certSettings[activeElementId].locked = locked;

                const $el = $('#el-' + activeElementId);
                $el.css('cursor', locked ? 'not-allowed' : 'move');

                try { locked ? $el.draggable('disable') : $el.draggable('enable'); } catch (e) { }
                try { locked ? $el.resizable('disable') : $el.resizable('enable'); } catch (e) { }

                if (activeElementId === 'platform_logo') {
                    $('#logo-width, #logo-height').prop('disabled', locked);
                }

                updateLayersList();
            });

            // Keyboard nudging (arrow keys)
            function clampPercent(val) {
                return Math.max(0, Math.min(100, val));
            }

            function nudgeSelected(dx, dy) {
                if (!activeElementId) return;
                const data = certSettings[activeElementId];
                if (!data || data.locked) return;

                let x = parseFloat(data.x);
                let y = parseFloat(data.y);
                if (isNaN(x)) x = 0;
                if (isNaN(y)) y = 0;

                x = clampPercent(x + dx);
                y = clampPercent(y + dy);

                if ($('#cert-snap-enabled').is(':checked')) {
                    const snap = parseFloat($('#cert-snap-step').val()) || 1;
                    x = Math.round(x / snap) * snap;
                    y = Math.round(y / snap) * snap;
                }

                x = Math.round(x * 10000) / 10000;
                y = Math.round(y * 10000) / 10000;

                data.x = x;
                data.y = y;

                $('#el-' + activeElementId).css({ left: x + '%', top: y + '%' });
                $('#style-x').val(parseFloat(x).toFixed(2));
                $('#style-y').val(parseFloat(y).toFixed(2));
            }

            $(document).on('keydown.certNudge', function (e) {
                if (!activeElementId) return;
                if (!$('#certificate').hasClass('show')) return;

                const $target = $(e.target);
                if (
                    $target.is('input, textarea, select') ||
                    $target.closest('input, textarea, select').length ||
                    $target.is('[contenteditable=true]') ||
                    $target.closest('[contenteditable=true]').length
                ) {
                    return;
                }

                if (e.ctrlKey || e.metaKey || e.altKey) return;

                let step = parseFloat($('#cert-nudge-step').val());
                if (isNaN(step) || step <= 0) step = 0.5;
                if (e.shiftKey) step = step * 5;

                const key = e.key || '';
                const code = e.which || e.keyCode;

                let dx = 0, dy = 0;
                if (key === 'ArrowLeft' || code === 37) dx = -step;
                else if (key === 'ArrowRight' || code === 39) dx = step;
                else if (key === 'ArrowUp' || code === 38) dy = -step;
                else if (key === 'ArrowDown' || code === 40) dy = step;
                else return;

                e.preventDefault();
                nudgeSelected(dx, dy);
            });

            // Logo Size Controls
            $('#logo-width').on('input', function () {
                let val = parseInt($(this).val()) || 120;
                val = Math.max(50, Math.min(400, val));
                if (certSettings['platform_logo'] && certSettings['platform_logo'].locked) {
                    $(this).val(certSettings['platform_logo'].width || 120);
                    return;
                }
                certSettings['platform_logo'].width = val;
                $('#el-platform_logo').css('width', val + 'px');
            });

            $('#logo-height').on('input', function () {
                let val = parseInt($(this).val()) || 60;
                val = Math.max(30, Math.min(200, val));
                if (certSettings['platform_logo'] && certSettings['platform_logo'].locked) {
                    $(this).val(certSettings['platform_logo'].height || 60);
                    return;
                }
                certSettings['platform_logo'].height = val;
                $('#el-platform_logo').css('height', val + 'px');
            });

            // Toggle Visibility (logo is mandatory)
            $('.cert-toggle').on('change', function () {
                let key = $(this).data('tag');

                if (key === 'platform_logo') {
                    $(this).prop('checked', true);
                    toastr.warning('A logo da plataforma é obrigatória e não pode ser removida.');
                    return;
                }

                if ($(this).is(':checked')) {
                    certSettings[key].visible = true;

                    if (key === 'instructor_signature') {
                        const hasUrl = !!(instructorSignaturePreviewUrl || '');
                        $('#el-' + key).css('display', hasUrl ? 'block' : 'flex');
                    } else {
                        $('#el-' + key).show();
                    }
                } else {
                    certSettings[key].visible = false;
                    $('#el-' + key).hide();
                }

                updateLayersList();
            });

            $('#certForm').on('submit', function (e) {
                e.preventDefault();

                certDoc.meta = certDoc.meta || {};
                certDoc.meta.backgroundFit = $('#cert-bg-fit').val() || 'cover';
                certDoc.meta.titleText = $('#certificate_title').val() || '';
                certDoc.meta.presentationText = $('#presentation_text').val() || '';

                if (certSettings['platform_logo']) {
                    certSettings['platform_logo'].visible = true;
                    certSettings['platform_logo'].mandatory = true;
                }

                $('#certificate_settings_input').val(JSON.stringify(certDoc));
                var formData = new FormData(this);
                $.ajax({
                    url: $(this).attr('action'), type: 'POST', data: formData, processData: false, contentType: false,
                    success: function () { toastr.success('Certificado salvo!'); },
                    error: function () { toastr.error('Erro ao salvar.'); }
                });
            });
        });
        window.previewCertBg = function (input) {
            if (input.files && input.files[0]) {
                try {
                    $(input).next('.custom-file-label').html(input.files[0].name);
                } catch (e) { }

                var reader = new FileReader();
                reader.onload = function (e) {
                    if ($('#cert-bg-img').length) $('#cert-bg-img').attr('src', e.target.result);
                    else $('#cert-bg-placeholder').replaceWith('<img src="' + e.target.result + '" id="cert-bg-img" style="width: 100%; height: 100%; object-fit: cover; position: absolute; z-index: 1;">');

                    const fit = ($('#cert-bg-fit').val() || 'cover') === 'stretch' ? 'fill' : 'cover';
                    $('#cert-bg-img').css('object-fit', fit);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush

@push('scripts')
    <script>
        $(function () {
            // Summernote - Descrição do Evento (Painel Legado)
            var $desc = $('#eventDescriptionLegacy');
            if ($desc.length && $.fn.summernote && !$desc.next('.note-editor').length) {
                $desc.summernote({
                    height: 300,
                    placeholder: 'Descreva o evento aqui...',
                    lang: 'pt-BR',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'italic', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            }
        });
    </script>
@endpush
