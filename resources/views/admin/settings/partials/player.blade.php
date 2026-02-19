<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-play-circle mr-2"></i> Personalize o player de vídeo das aulas. Defina cores, controles e
        proteção de conteúdo (marca d'água).
    </div>

    <div class="form-group mb-4">
        <div class="custom-control custom-switch custom-switch-lg custom-switch-off-danger custom-switch-on-success">
            <input type="hidden" name="video_player_enabled" value="0">
            <input type="checkbox" class="custom-control-input" id="video_player_enabled" name="video_player_enabled"
                value="1" {{ ($settings['video_player_enabled'] ?? 1) ? 'checked' : '' }}>
            <label class="custom-control-label font-weight-bold" for="video_player_enabled">Ativar Player Personalizado
                (Plyr)</label>
        </div>
    </div>

    <div class="row">
        {{-- VISUAL --}}
        <div class="col-md-6">
            <div class="card card-outline card-primary h-100">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-palette mr-2"></i> Aparência e
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
                            <input type="number" step="0.1" min="0" max="1" name="video_plyr_volume"
                                class="form-control" value="{{ $settings['video_plyr_volume'] ?? '0.8' }}">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-6">
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                                <input type="hidden" name="video_plyr_autoplay" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_autoplay"
                                    name="video_plyr_autoplay" value="1" {{ ($settings['video_plyr_autoplay'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_autoplay">Autoplay</label>
                            </div>
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                                <input type="hidden" name="video_plyr_muted" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_muted"
                                    name="video_plyr_muted" value="1" {{ ($settings['video_plyr_muted'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_muted">Mudo Inic.</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                                <input type="hidden" name="video_plyr_click_to_play" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_click_to_play"
                                    name="video_plyr_click_to_play" value="1" {{ ($settings['video_plyr_click_to_play'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_click_to_play">Clique Tela</label>
                            </div>
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                                <input type="hidden" name="video_plyr_disable_context_menu" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_disable_context_menu"
                                    name="video_plyr_disable_context_menu" value="1" {{ ($settings['video_plyr_disable_context_menu'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_disable_context_menu">No
                                    Context</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CONTROLES --}}
        <div class="col-md-6">
            <div class="card card-outline card-secondary h-100">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h mr-2"></i> Controles Visíveis
                    </h3>
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
                                        <label class="custom-control-label small"
                                            for="control_{{ $control }}">{{ ucfirst($control) }}</label>
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

    <h5 class="text-primary mb-3"><i class="fas fa-shield-alt mr-2"></i> Proteção e Marca D'água</h5>
    <div class="card card-outline card-danger">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                        <input type="hidden" name="video_watermark_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="video_watermark_enabled"
                            name="video_watermark_enabled" value="1" {{ ($settings['video_watermark_enabled'] ?? 0) ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold" for="video_watermark_enabled">Ativar Marca
                            D'água (Imagem)</label>
                    </div>
                    <div class="upload-box" data-remove-input="#remove_watermark_image"
                        data-existing-url="{{ $getUrl('watermark_image') }}">
                        <input type="file" name="watermark_image" class="d-none" accept="image/*">
                        <input type="hidden" name="remove_watermark_image" id="remove_watermark_image" value="0">
                        <div class="upload-preview mb-2 text-center">
                            @if($url = $getUrl('watermark_image'))
                                <img src="{{ $url }}" class="img-fluid" style="max-height: 100px; max-width: 100px;">
                            @else
                                <div class="text-muted p-2 border rounded bg-light">Sem imagem</div>
                            @endif
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary upload-btn">Selecionar</button>
                            <button type="button"
                                class="btn btn-sm btn-outline-danger upload-remove {{ $getUrl('watermark_image') ? '' : 'd-none' }}"><i
                                    class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                        <input type="hidden" name="video_watermark_text_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="video_watermark_text_enabled"
                            name="video_watermark_text_enabled" value="1" {{ ($settings['video_watermark_text_enabled'] ?? 0) ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold" for="video_watermark_text_enabled">Ativar
                            Marca D'água Dinâmica (Anti-pirataria)</label>
                    </div>
                    <div class="form-group">
                        <label>Template do Texto</label>
                        <input type="text" name="video_watermark_text_template" class="form-control"
                            value="{{ $settings['video_watermark_text_template'] ?? '{name} - {email}' }}">
                        <small class="text-muted">Variáveis: <code>{name}</code>, <code>{email}</code>,
                            <code>{cpf}</code>, <code>{id}</code>.</small>
                    </div>
                    <div class="row">
                        <div class="col-6 form-group">
                            <label>Opacidade</label>
                            <input type="number" step="0.1" name="video_watermark_opacity" class="form-control"
                                value="{{ $settings['video_watermark_opacity'] ?? '0.5' }}">
                        </div>
                        <div class="col-6 form-group">
                            <label>Posição</label>
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
            <i class="fas fa-cogs mr-1"></i> Configurações Avançadas (JSON)
        </a>
        <div class="collapse mt-2" id="advancedJson">
            <div class="form-group">
                <textarea name="video_plyr_options_json" class="form-control code-editor"
                    rows="5">{{ $settings['video_plyr_options_json'] ?? '' }}</textarea>
                <small class="text-muted">Cuidado: O JSON gerado automaticamente pelos checkboxes acima sobrescreverá
                    este campo ao salvar, a menos que você modifique o script JS.</small>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Player Controls Sync
            function updatePlyrJson() {
                var controls = [];
                $('.plyr-control-checkbox:checked').each(function () {
                    controls.push($(this).val());
                });
                $('[name="video_plyr_controls"]').val(controls.join(','));

                var settings = []; // Not visible but kept for logic if needed, loosely based on previous file
                // If there were settings checkboxes, we'd gather them here.
                // For now just controls.
            }

            $('.plyr-control-checkbox').change(function () {
                updatePlyrJson();
            });
        });
    </script>
@endpush