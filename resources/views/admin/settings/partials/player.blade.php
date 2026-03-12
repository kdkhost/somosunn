<div class="alert alert-info mb-4">
    <i class="fas fa-play-circle mr-2"></i> Personalize o player de video das aulas. Defina cores, controles e
    protecao de conteudo (marca d'agua).
    <span class="float-right badge badge-success ml-2 d-none" id="auto-save-badge"><i class="fas fa-check mr-1"></i>
        Salvo</span>
</div>

<style>
    .upload-box[data-preview-max-height="120"] .upload-preview img {
        max-height: 120px !important;
        max-width: 180px !important;
        width: auto !important;
        object-fit: contain;
    }

    .watermark-preview-surface {
        background-color: #f8fafc;
        background-image:
            linear-gradient(45deg, rgba(148, 163, 184, 0.16) 25%, transparent 25%),
            linear-gradient(-45deg, rgba(148, 163, 184, 0.16) 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, rgba(148, 163, 184, 0.16) 75%),
            linear-gradient(-45deg, transparent 75%, rgba(148, 163, 184, 0.16) 75%);
        background-size: 18px 18px;
        background-position: 0 0, 0 9px, 9px -9px, -9px 0;
    }
</style>

<div class="form-group mb-4">
    <div class="custom-control custom-switch custom-switch-lg custom-switch-off-danger custom-switch-on-success">
        <input type="checkbox" class="custom-control-input" id="video_player_enabled" name="video_player_enabled"
            value="1" {{ ($settings['video_player_enabled'] ?? 1) ? 'checked' : '' }}>
        <label class="custom-control-label font-weight-bold" for="video_player_enabled">Ativar Player Personalizado
            (Plyr)</label>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary h-100">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-palette mr-2"></i> Aparencia e
                    Comportamento</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Cor de Destaque (Principal)</label>
                    <div class="input-group colorpicker-element">
                        <input name="video_plyr_color" class="form-control"
                            value="{{ $settings['video_plyr_color'] ?? ($settings['site_color_primary'] ?? '#1F5EDB') }}">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-square"
                                    style="color: {{ $settings['video_plyr_color'] ?? ($settings['site_color_primary'] ?? '#1F5EDB') }}"></i></span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6 form-group">
                        <label>Seek (Segundos)</label>
                        <input type="number" name="video_plyr_seek_time" class="form-control"
                            value="{{ $settings['video_plyr_seek_time'] ?? '10' }}">
                    </div>
                    <div class="col-6 form-group">
                        <label>Volume Inicial (0.0 - 1.0)</label>
                        <input type="number" step="0.1" min="0" max="1" name="video_plyr_volume" class="form-control"
                            value="{{ $settings['video_plyr_volume'] ?? '0.8' }}">
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-6">
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
                            <label class="custom-control-label" for="video_plyr_muted">Mudo Inic.</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                            <input type="hidden" name="video_plyr_click_to_play" value="0">
                            <input type="checkbox" class="custom-control-input" id="video_plyr_click_to_play"
                                name="video_plyr_click_to_play" value="1" {{ ($settings['video_plyr_click_to_play'] ?? 1) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="video_plyr_click_to_play">Clique Tela</label>
                        </div>
                        <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                            <input type="hidden" name="video_plyr_disable_context_menu" value="0">
                            <input type="checkbox" class="custom-control-input" id="video_plyr_disable_context_menu"
                                name="video_plyr_disable_context_menu" value="1" {{ ($settings['video_plyr_disable_context_menu'] ?? 1) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="video_plyr_disable_context_menu">No Context</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-secondary h-100">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h mr-2"></i> Controles Visiveis</h3>
            </div>
            <div class="card-body">
                <div class="form-group mb-0">
                    @php
                        $currentControls = explode(',', $settings['video_plyr_controls'] ?? 'play,progress,current-time,mute,volume,settings,fullscreen');
                        $availableControls = ['play-large', 'restart', 'rewind', 'play', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'download', 'fullscreen'];
                    @endphp
                    <input type="hidden" name="video_plyr_controls" value="{{ implode(',', $currentControls) }}">
                    <div class="row">
                        @foreach($availableControls as $control)
                            <div class="col-md-6 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input plyr-control-checkbox"
                                        id="control_{{ $control }}" value="{{ $control }}" {{ in_array($control, $currentControls) ? 'checked' : '' }}>
                                    <label class="custom-control-label small" for="control_{{ $control }}">{{ ucfirst($control) }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

@php
    $imageWatermarkOpacity = (string) ($settings['image_watermark_opacity'] ?? '30');
    $imageWatermarkSize = (string) ($settings['image_watermark_size_percent'] ?? '12');
    $imageWatermarkMargin = (string) ($settings['image_watermark_margin'] ?? '20');
    $imageWatermarkPosition = (string) ($settings['image_watermark_position'] ?? 'bottom-right');
@endphp

<h5 class="text-primary mb-3"><i class="fas fa-shield-alt mr-2"></i> Protecao e Marca D'Agua</h5>
<div class="card card-outline card-danger">
    <div class="card-body">
        <div class="row">
            <div class="col-md-7">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                    <input type="hidden" name="image_watermark_enabled" value="0">
                    <input type="checkbox" class="custom-control-input" id="image_watermark_enabled"
                        name="image_watermark_enabled" value="1" {{ ($settings['image_watermark_enabled'] ?? 1) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="image_watermark_enabled">Ativar marca d'agua nas imagens publicadas</label>
                </div>
                <div class="alert alert-light border">
                    <strong>Envie uma imagem transparente.</strong> Use <code>PNG</code> ou <code>WEBP</code>, sem fundo chapado, para a marca d'agua ficar discreta.
                </div>
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                    <input type="hidden" name="video_watermark_enabled" value="0">
                    <input type="checkbox" class="custom-control-input" id="video_watermark_enabled"
                        name="video_watermark_enabled" value="1" {{ ($settings['video_watermark_enabled'] ?? 0) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="video_watermark_enabled">Usar a mesma imagem tambem no player de video</label>
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
                        <button type="button" class="btn btn-sm btn-outline-primary upload-btn">Selecionar PNG/WEBP</button>
                        <button type="button"
                            class="btn btn-sm btn-outline-danger upload-remove {{ $getUrl('watermark_image') ? '' : 'd-none' }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <small class="d-block text-muted mt-2">Recomendado: logo horizontal com fundo transparente, ate 1600x900px.</small>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 form-group">
                        <label>Posicao</label>
                        <select name="image_watermark_position" class="form-control">
                            <option value="top-left" {{ $imageWatermarkPosition === 'top-left' ? 'selected' : '' }}>Topo Esq</option>
                            <option value="top-right" {{ $imageWatermarkPosition === 'top-right' ? 'selected' : '' }}>Topo Dir</option>
                            <option value="bottom-left" {{ $imageWatermarkPosition === 'bottom-left' ? 'selected' : '' }}>Inf Esq</option>
                            <option value="bottom-right" {{ $imageWatermarkPosition === 'bottom-right' ? 'selected' : '' }}>Inf Dir</option>
                            <option value="center" {{ $imageWatermarkPosition === 'center' ? 'selected' : '' }}>Centro</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Opacidade (%)</label>
                        <input type="number" min="5" max="100" name="image_watermark_opacity" class="form-control"
                            value="{{ $imageWatermarkOpacity }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Tamanho (%)</label>
                        <input type="number" min="1" max="60" name="image_watermark_size_percent" class="form-control"
                            value="{{ $imageWatermarkSize }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Margem (px)</label>
                        <input type="number" min="0" max="300" name="image_watermark_margin" class="form-control"
                            value="{{ $imageWatermarkMargin }}">
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                    <input type="hidden" name="video_watermark_text_enabled" value="0">
                    <input type="checkbox" class="custom-control-input" id="video_watermark_text_enabled"
                        name="video_watermark_text_enabled" value="1" {{ ($settings['video_watermark_text_enabled'] ?? 0) ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="video_watermark_text_enabled">Ativar marca d'agua dinamica (anti-pirataria)</label>
                </div>
                <div class="form-group">
                    <label>Template do Texto</label>
                    <input type="text" name="video_watermark_text_template" class="form-control"
                        value="{{ $settings['video_watermark_text_template'] ?? '{name} - {email}' }}">
                    <small class="text-muted">Variaveis: <code>{name}</code>, <code>{email}</code>, <code>{cpf}</code>, <code>{id}</code>.</small>
                </div>
                <div class="row">
                    <div class="col-6 form-group">
                        <label>Opacidade</label>
                        <input type="number" step="0.1" name="video_watermark_opacity" class="form-control"
                            value="{{ $settings['video_watermark_opacity'] ?? '0.5' }}">
                    </div>
                    <div class="col-6 form-group">
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
    </div>
</div>

<div class="mt-4">
    <a class="btn btn-link text-muted p-0" data-toggle="collapse" href="#advancedJson" role="button"
        aria-expanded="false" aria-controls="advancedJson">
        <i class="fas fa-cogs mr-1"></i> Configuracoes Avancadas (JSON)
    </a>
    <div class="collapse mt-2" id="advancedJson">
        <div class="form-group">
            <textarea name="video_plyr_options_json" class="form-control code-editor"
                rows="5">{{ $settings['video_plyr_options_json'] ?? '' }}</textarea>
            <small class="text-muted">Cuidado: o JSON gerado automaticamente pelos checkboxes acima sobrescrevera este campo ao salvar, a menos que voce modifique o script JS.</small>
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

                    $('#auto-save-badge').removeClass('d-none').removeClass('badge-success').addClass('badge-warning').html('<i class="fas fa-sync fa-spin mr-1"></i> Salvando...');

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
                            $('#auto-save-badge').removeClass('badge-warning').addClass('badge-success').html('<i class="fas fa-check mr-1"></i> Salvo');
                            setTimeout(() => {
                                $('#auto-save-badge').addClass('d-none');
                            }, 2000);
                        },
                        error: function (xhr) {
                            $('#auto-save-badge').removeClass('badge-warning').addClass('badge-danger').html('<i class="fas fa-times mr-1"></i> Erro');
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
