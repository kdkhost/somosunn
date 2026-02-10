<div class="card-body">
    <h5 class="text-primary mb-3"><i class="fas fa-play-circle mr-2"></i>Player de Vídeo (Plyr)</h5>

    <div class="alert alert-info">
        Configure o player de vídeo usado nas aulas/cursos. As opções avançadas aceitam o JSON de
        configuração do Plyr.
    </div>

    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
        <input type="hidden" name="video_player_enabled" value="0">
        <input type="checkbox" class="custom-control-input" id="video_player_enabled" name="video_player_enabled"
            value="1" {{ ($settings['video_player_enabled'] ?? 1) ? 'checked' : '' }}>
        <label class="custom-control-label" for="video_player_enabled">Ativar Plyr no site</label>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Cor principal do player</label>
                <div class="input-group colorpicker-element">
                    <input name="video_plyr_color" class="form-control"
                        value="{{ $settings['video_plyr_color'] ?? ($settings['site_color_primary'] ?? '#1F5EDB') }}">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-square"
                                style="color: {{ $settings['video_plyr_color'] ?? ($settings['site_color_primary'] ?? '#1F5EDB') }}"></i></span>
                    </div>
                </div>
                <small class="text-muted">CSS: <code>--plyr-color-main</code>.</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Seek (segundos)</label>
                <input name="video_plyr_seek_time" class="form-control"
                    value="{{ $settings['video_plyr_seek_time'] ?? '10' }}" placeholder="Ex: 10">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Volume inicial (0 a 1)</label>
                <input name="video_plyr_volume" class="form-control"
                    value="{{ $settings['video_plyr_volume'] ?? '0.8' }}" placeholder="Ex: 0.8">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                <input type="hidden" name="video_plyr_autoplay" value="0">
                <input type="checkbox" class="custom-control-input" id="video_plyr_autoplay" name="video_plyr_autoplay"
                    value="1" {{ ($settings['video_plyr_autoplay'] ?? 0) ? 'checked' : '' }}>
                <label class="custom-control-label" for="video_plyr_autoplay">Autoplay</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                <input type="hidden" name="video_plyr_muted" value="0">
                <input type="checkbox" class="custom-control-input" id="video_plyr_muted" name="video_plyr_muted"
                    value="1" {{ ($settings['video_plyr_muted'] ?? 0) ? 'checked' : '' }}>
                <label class="custom-control-label" for="video_plyr_muted">Iniciar mudo</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                <input type="hidden" name="video_plyr_click_to_play" value="0">
                <input type="checkbox" class="custom-control-input" id="video_plyr_click_to_play"
                    name="video_plyr_click_to_play" value="1" {{ ($settings['video_plyr_click_to_play'] ?? 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="video_plyr_click_to_play">Clique p/
                    tocar</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                <input type="hidden" name="video_plyr_disable_context_menu" value="0">
                <input type="checkbox" class="custom-control-input" id="video_plyr_disable_context_menu"
                    name="video_plyr_disable_context_menu" value="1" {{ ($settings['video_plyr_disable_context_menu'] ?? 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="video_plyr_disable_context_menu">Bloquear
                    Menu</label>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="hidden" name="video_plyr_rewind_enabled" value="0">
                <input type="checkbox" class="custom-control-input" id="video_plyr_rewind_enabled"
                    name="video_plyr_rewind_enabled" value="1" {{ ($settings['video_plyr_rewind_enabled'] ?? 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="video_plyr_rewind_enabled">Botão
                    Retroceder</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="hidden" name="video_plyr_fast_forward_enabled" value="0">
                <input type="checkbox" class="custom-control-input" id="video_plyr_fast_forward_enabled"
                    name="video_plyr_fast_forward_enabled" value="1" {{ ($settings['video_plyr_fast_forward_enabled'] ?? 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="video_plyr_fast_forward_enabled">Botão
                    Avançar</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="hidden" name="video_plyr_volume_enabled" value="0">
                <input type="checkbox" class="custom-control-input" id="video_plyr_volume_enabled"
                    name="video_plyr_volume_enabled" value="1" {{ ($settings['video_plyr_volume_enabled'] ?? 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="video_plyr_volume_enabled">Controle
                    Volume</label>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="form-group">
                <label>Controles do Player</label>
                <div class="row">
                    @php
                        $currentControls = explode(',', $settings['video_plyr_controls'] ?? 'play,progress,current-time,mute,volume,settings,fullscreen');
                        $availableControls = ['play-large', 'restart', 'rewind', 'play', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'download', 'fullscreen'];
                    @endphp
                    <input type="hidden" name="video_plyr_controls" value="{{ implode(',', $currentControls) }}">
                    @foreach($availableControls as $control)
                        <div class="col-md-4 mb-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input plyr-control-checkbox"
                                    id="control_{{ $control }}" value="{{ $control }}" {{ in_array($control, $currentControls) ? 'checked' : '' }}>
                                <label class="custom-control-label"
                                    for="control_{{ $control }}">{{ ucfirst($control) }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Menu Config</label>
                <div class="row">
                    @php
                        $currentSettings = explode(',', $settings['video_plyr_settings'] ?? 'captions,quality,speed,loop');
                        $availableSettings = ['captions', 'quality', 'speed', 'loop'];
                    @endphp
                    <input type="hidden" name="video_plyr_settings" value="{{ implode(',', $currentSettings) }}">
                    @foreach($availableSettings as $setting)
                        <div class="col-md-6 mb-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input plyr-setting-checkbox"
                                    id="setting_{{ $setting }}" value="{{ $setting }}" {{ in_array($setting, $currentSettings) ? 'checked' : '' }}>
                                <label class="custom-control-label"
                                    for="setting_{{ $setting }}">{{ ucfirst($setting) }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Velocidades (CSVs)</label>
                    <input name="video_plyr_speed_options" class="form-control"
                        value="{{ $settings['video_plyr_speed_options'] ?? '0.5,0.75,1,1.25,1.5,2' }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Velocidade Padrão</label>
                    <input name="video_plyr_speed_selected" class="form-control"
                        value="{{ $settings['video_plyr_speed_selected'] ?? '1' }}">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Opções avançadas (JSON)</label>
            <textarea name="video_plyr_options_json" class="form-control" rows="4"
                placeholder='{"tooltips":{"controls":true,"seek":true}}'>{{ $settings['video_plyr_options_json'] ?? '' }}</textarea>
            <small class="text-muted">JSON puro para sobrescrever configs.</small>
        </div>

        <hr>
        <h5 class="text-primary mb-3"><i class="fas fa-water mr-2"></i>Marca d'água (Cursos)</h5>

        <div class="row">
            <div class="col-md-6">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                    <input type="hidden" name="video_watermark_enabled" value="0">
                    <input type="checkbox" class="custom-control-input" id="video_watermark_enabled"
                        name="video_watermark_enabled" value="1" {{ ($settings['video_watermark_enabled'] ?? 0) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="video_watermark_enabled">Exibir
                        imagem</label>
                </div>

                <div class="form-group">
                    <label>Imagem</label>
                    <div class="upload-box" data-max-size="{{ 1 * 1024 * 1024 }}"
                        data-existing-url="{{ $getUrl('watermark_image') }}"
                        data-remove-input="[name='remove_watermark_image']">
                        <input type="hidden" name="remove_watermark_image" value="0">
                        <input type="file" name="watermark_image" accept="image/*" class="d-none">
                        <div class="upload-preview text-center text-muted"></div>
                        <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                        <button type="button"
                            class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                    <input type="hidden" name="video_watermark_text_enabled" value="0">
                    <input type="checkbox" class="custom-control-input" id="video_watermark_text_enabled"
                        name="video_watermark_text_enabled" value="1" {{ ($settings['video_watermark_text_enabled'] ?? 0) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="video_watermark_text_enabled">Texto
                        dinâmico
                        (Anti-pirataria)</label>
                </div>
                <div class="form-group">
                    <label>Template</label>
                    <input name="video_watermark_text_template" class="form-control"
                        value="{{ $settings['video_watermark_text_template'] ?? '{name} - {email}' }}">
                    <small class="text-muted">Tags: {name}, {email}, {cpf}, {id}</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Opacidade (0-1)</label>
                    <input name="video_watermark_opacity" class="form-control"
                        value="{{ $settings['video_watermark_opacity'] ?? '0.5' }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tamanho (%)</label>
                    <input name="video_watermark_size_percent" class="form-control"
                        value="{{ $settings['video_watermark_size_percent'] ?? '15' }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
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

                var settings = [];
                $('.plyr-setting-checkbox:checked').each(function () {
                    settings.push($(this).val());
                });
                $('[name="video_plyr_settings"]').val(settings.join(','));

                // Update JSON
                var jsonText = $('[name="video_plyr_options_json"]').val();
                var jsonObj = {};
                try {
                    jsonObj = JSON.parse(jsonText || '{}');
                } catch (e) {
                    console.error('Invalid JSON, starting fresh');
                }

                jsonObj.controls = controls;
                jsonObj.settings = settings;

                $('[name="video_plyr_options_json"]').val(JSON.stringify(jsonObj, null, 4));
            }

            $('.plyr-control-checkbox, .plyr-setting-checkbox').change(function () {
                updatePlyrJson();
            });
        });
    </script>
@endpush