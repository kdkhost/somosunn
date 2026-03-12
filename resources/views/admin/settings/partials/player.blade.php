@php
    $currentControls = explode(',', $settings['video_plyr_controls'] ?? 'play,progress,current-time,mute,volume,settings,fullscreen');
    $availableControls = ['play-large', 'restart', 'rewind', 'play', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'download', 'fullscreen'];
    $imageWatermarkOpacity = (string) ($settings['image_watermark_opacity'] ?? '30');
    $imageWatermarkSize = (string) ($settings['image_watermark_size_percent'] ?? '12');
    $imageWatermarkMargin = (string) ($settings['image_watermark_margin'] ?? '20');
    $imageWatermarkPosition = (string) ($settings['image_watermark_position'] ?? 'bottom-right');
@endphp

@push('styles')
    <style>
        .player-settings .upload-box[data-preview-max-height="120"] .upload-preview img {
            max-height: 120px !important;
            max-width: 180px !important;
            width: auto !important;
            object-fit: contain;
        }

        .player-settings .watermark-preview-surface {
            background-color: #f8fafc;
            background-image:
                linear-gradient(45deg, rgba(148, 163, 184, 0.16) 25%, transparent 25%),
                linear-gradient(-45deg, rgba(148, 163, 184, 0.16) 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, rgba(148, 163, 184, 0.16) 75%),
                linear-gradient(-45deg, transparent 75%, rgba(148, 163, 184, 0.16) 75%);
            background-size: 18px 18px;
            background-position: 0 0, 0 9px, 9px -9px, -9px 0;
        }

        .player-settings .setting-card {
            height: 100%;
        }

        .player-settings .setting-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        }
    </style>
@endpush

<div class="card-body player-settings">
    <div class="alert alert-info mb-4">
        <i class="fas fa-play-circle mr-2"></i>
        Personalize o player das aulas e a protecao visual das midias publicadas.
        <span class="float-right badge badge-success ml-2 d-none" id="auto-save-badge">
            <i class="fas fa-check mr-1"></i> Salvo
        </span>
    </div>

    <div class="custom-control custom-switch custom-switch-lg custom-switch-off-danger custom-switch-on-success mb-4">
        <input type="checkbox" class="custom-control-input" id="video_player_enabled" name="video_player_enabled"
            value="1" {{ ($settings['video_player_enabled'] ?? 1) ? 'checked' : '' }}>
        <label class="custom-control-label font-weight-bold" for="video_player_enabled">
            Ativar player personalizado (Plyr)
        </label>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card card-outline card-primary setting-card">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-palette mr-2"></i>Aparencia e comportamento
                    </h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Cor de destaque</label>
                        <div class="input-group colorpicker-element">
                            <input name="video_plyr_color" class="form-control"
                                value="{{ $settings['video_plyr_color'] ?? ($settings['site_color_primary'] ?? '#1F5EDB') }}">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-square"
                                        style="color: {{ $settings['video_plyr_color'] ?? ($settings['site_color_primary'] ?? '#1F5EDB') }}"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Seek (segundos)</label>
                            <input type="number" name="video_plyr_seek_time" class="form-control"
                                value="{{ $settings['video_plyr_seek_time'] ?? '10' }}">
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Volume inicial (0 a 1)</label>
                            <input type="number" step="0.1" min="0" max="1" name="video_plyr_volume" class="form-control"
                                value="{{ $settings['video_plyr_volume'] ?? '0.8' }}">
                        </div>
                    </div>

                    <div class="row pt-2">
                        <div class="col-sm-6">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                                <input type="hidden" name="video_plyr_autoplay" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_autoplay"
                                    name="video_plyr_autoplay" value="1" {{ ($settings['video_plyr_autoplay'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_autoplay">Autoplay</label>
                            </div>
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                                <input type="hidden" name="video_plyr_muted" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_muted"
                                    name="video_plyr_muted" value="1" {{ ($settings['video_plyr_muted'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_muted">Iniciar mudo</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                                <input type="hidden" name="video_plyr_click_to_play" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_click_to_play"
                                    name="video_plyr_click_to_play" value="1" {{ ($settings['video_plyr_click_to_play'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_click_to_play">Clique na tela</label>
                            </div>
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                                <input type="hidden" name="video_plyr_disable_context_menu" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_disable_context_menu"
                                    name="video_plyr_disable_context_menu" value="1" {{ ($settings['video_plyr_disable_context_menu'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_disable_context_menu">Bloquear menu</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card card-outline card-secondary setting-card">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-sliders-h mr-2"></i>Controles visiveis
                    </h3>
                </div>
                <div class="card-body">
                    <input type="hidden" name="video_plyr_controls" value="{{ implode(',', $currentControls) }}">
                    <div class="row">
                        @foreach($availableControls as $control)
                            <div class="col-md-6 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input plyr-control-checkbox"
                                        id="control_{{ $control }}" value="{{ $control }}"
                                        {{ in_array($control, $currentControls) ? 'checked' : '' }}>
                                    <label class="custom-control-label small" for="control_{{ $control }}">
                                        {{ ucfirst($control) }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-danger mb-4">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-image mr-2"></i>Marca d'agua para imagens
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-7">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                        <input type="hidden" name="image_watermark_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="image_watermark_enabled"
                            name="image_watermark_enabled" value="1" {{ ($settings['image_watermark_enabled'] ?? 1) ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold" for="image_watermark_enabled">
                            Ativar marca d'agua nas imagens publicadas
                        </label>
                    </div>

                    <div class="alert alert-light border">
                        <strong>Arquivo recomendado:</strong> PNG ou WEBP com fundo transparente.
                        O sistema nao vai mais usar o logo geral como fallback nessa watermark.
                    </div>

                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                        <input type="hidden" name="video_watermark_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="video_watermark_enabled"
                            name="video_watermark_enabled" value="1" {{ ($settings['video_watermark_enabled'] ?? 0) ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold" for="video_watermark_enabled">
                            Usar a mesma imagem tambem no player de video
                        </label>
                    </div>

                    <div class="upload-box" data-remove-input="#remove_watermark_image"
                        data-existing-url="{{ $getUrl('watermark_image') }}" data-preview-max-height="120"
                        data-preview-max-width="180">
                        <input type="file" name="watermark_image" class="d-none" accept=".png,.webp">
                        <input type="hidden" name="remove_watermark_image" id="remove_watermark_image" value="0">
                        <div class="upload-preview mb-3 text-center watermark-preview-surface rounded border p-3">
                            @if($url = $getUrl('watermark_image'))
                                <img src="{{ $url }}" class="img-fluid" style="max-height: 120px; max-width: 180px;">
                            @else
                                <div class="text-muted p-3">Nenhuma marca d'agua transparente enviada</div>
                            @endif
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary upload-btn">
                                Selecionar PNG/WEBP
                            </button>
                            <button type="button"
                                class="btn btn-sm btn-outline-danger upload-remove {{ $getUrl('watermark_image') ? '' : 'd-none' }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <small class="d-block text-muted mt-2">
                            Recomendado: logo horizontal, fundo transparente, ate 1600x900px.
                        </small>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Posicao</label>
                            <select name="image_watermark_position" class="form-control">
                                <option value="top-left" {{ $imageWatermarkPosition === 'top-left' ? 'selected' : '' }}>Topo Esq</option>
                                <option value="top-right" {{ $imageWatermarkPosition === 'top-right' ? 'selected' : '' }}>Topo Dir</option>
                                <option value="bottom-left" {{ $imageWatermarkPosition === 'bottom-left' ? 'selected' : '' }}>Inf Esq</option>
                                <option value="bottom-right" {{ $imageWatermarkPosition === 'bottom-right' ? 'selected' : '' }}>Inf Dir</option>
                                <option value="center" {{ $imageWatermarkPosition === 'center' ? 'selected' : '' }}>Centro</option>
                            </select>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Opacidade (%)</label>
                            <input type="number" min="5" max="100" name="image_watermark_opacity" class="form-control"
                                value="{{ $imageWatermarkOpacity }}">
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Tamanho (%)</label>
                            <input type="number" min="1" max="60" name="image_watermark_size_percent" class="form-control"
                                value="{{ $imageWatermarkSize }}">
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Margem (px)</label>
                            <input type="number" min="0" max="300" name="image_watermark_margin" class="form-control"
                                value="{{ $imageWatermarkMargin }}">
                        </div>
                    </div>

                    <div class="callout callout-warning mb-0">
                        <h5 class="mb-2">Dica de uso</h5>
                        <p class="mb-0">
                            Para a maioria das fotos, o melhor ponto inicial e <strong>inferior direito</strong>,
                            opacidade entre <strong>20% e 35%</strong> e tamanho entre <strong>10% e 14%</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-warning mb-4">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-user-secret mr-2"></i>Marca d'agua dinamica de video
            </h3>
        </div>
        <div class="card-body">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                <input type="hidden" name="video_watermark_text_enabled" value="0">
                <input type="checkbox" class="custom-control-input" id="video_watermark_text_enabled"
                    name="video_watermark_text_enabled" value="1" {{ ($settings['video_watermark_text_enabled'] ?? 0) ? 'checked' : '' }}>
                <label class="custom-control-label font-weight-bold" for="video_watermark_text_enabled">
                    Ativar marca d'agua dinamica (anti-pirataria)
                </label>
            </div>

            <div class="row">
                <div class="col-lg-6 form-group">
                    <label>Template do texto</label>
                    <input type="text" name="video_watermark_text_template" class="form-control"
                        value="{{ $settings['video_watermark_text_template'] ?? '{name} - {email}' }}">
                    <small class="text-muted">Tags: {name}, {email}, {cpf}, {id}.</small>
                </div>
                <div class="col-sm-3 form-group">
                    <label>Opacidade</label>
                    <input type="number" step="0.1" name="video_watermark_opacity" class="form-control"
                        value="{{ $settings['video_watermark_opacity'] ?? '0.5' }}">
                </div>
                <div class="col-sm-3 form-group">
                    <label>Posicao</label>
                    <select name="video_watermark_position" class="form-control">
                        <option value="top-right" {{ ($settings['video_watermark_position'] ?? 'top-right') === 'top-right' ? 'selected' : '' }}>Topo Dir</option>
                        <option value="top-left" {{ ($settings['video_watermark_position'] ?? '') === 'top-left' ? 'selected' : '' }}>Topo Esq</option>
                        <option value="bottom-right" {{ ($settings['video_watermark_position'] ?? '') === 'bottom-right' ? 'selected' : '' }}>Inf Dir</option>
                        <option value="bottom-left" {{ ($settings['video_watermark_position'] ?? '') === 'bottom-left' ? 'selected' : '' }}>Inf Esq</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-cogs mr-2"></i>Configuracoes avancadas
            </h3>
        </div>
        <div class="card-body">
            <div class="form-group mb-0">
                <label>JSON avancado do Plyr</label>
                <textarea name="video_plyr_options_json" class="form-control code-editor"
                    rows="5">{{ $settings['video_plyr_options_json'] ?? '' }}</textarea>
                <small class="text-muted">
                    O JSON gerado pelos checkboxes pode sobrescrever este campo ao salvar.
                </small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function updatePlyrJson() {
                var controls = [];
                $('.plyr-control-checkbox:checked').each(function () {
                    controls.push($(this).val());
                });
                $('[name="video_plyr_controls"]').val(controls.join(','));
            }

            $('.plyr-control-checkbox').change(function () {
                updatePlyrJson();
            });

            let autoSaveTimer;
            const formSelector = 'form[action*="/admin/settings"]';

            function triggerAutoSave() {
                var form = $(this).closest('form');
                if (form.length === 0) return;

                updatePlyrJson();
                clearTimeout(autoSaveTimer);

                autoSaveTimer = setTimeout(function () {
                    var formData = new FormData(form[0]);

                    $('#auto-save-badge')
                        .removeClass('d-none badge-success badge-danger')
                        .addClass('badge-warning')
                        .html('<i class="fas fa-sync fa-spin mr-1"></i> Salvando...');

                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function () {
                            $('#auto-save-badge')
                                .removeClass('badge-warning badge-danger')
                                .addClass('badge-success')
                                .html('<i class="fas fa-check mr-1"></i> Salvo');

                            setTimeout(function () {
                                $('#auto-save-badge').addClass('d-none');
                            }, 2000);
                        },
                        error: function (xhr) {
                            $('#auto-save-badge')
                                .removeClass('badge-warning badge-success')
                                .addClass('badge-danger')
                                .html('<i class="fas fa-times mr-1"></i> Erro');
                            console.error('Auto-save error:', xhr);
                        }
                    });
                }, 500);
            }

            $(document).on('change', formSelector + ' input:not([type=file]), ' + formSelector + ' select, ' + formSelector + ' textarea', triggerAutoSave);

            $('.colorpicker-element').on('colorpickerChange', function () {
                triggerAutoSave.call(this);
            });
        });
    </script>
@endpush
