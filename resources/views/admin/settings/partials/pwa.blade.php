<div class="card-body">
    <div class="alert alert-info">
        <i class="fas fa-mobile-alt mr-2"></i> Transforme seu site em um aplicativo instalável (PWA). Usuários poderão
        instalar o site direto da barra de endereço ou menu do navegador.
    </div>

    <div class="form-group">
        <div class="custom-control custom-switch custom-switch-lg">
            <input type="checkbox" class="custom-control-input" id="pwa_enabled" name="pwa_enabled" value="1" {{ ($settings['pwa_enabled'] ?? 0) ? 'checked' : '' }}>
            <label class="custom-control-label font-weight-bold" for="pwa_enabled">Ativar Progressive Web App
                (PWA)</label>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Nome do App (Curto)</label>
                <input type="text" name="pwa_short_name" class="form-control"
                    value="{{ $settings['pwa_short_name'] ?? config('app.name') }}" placeholder="Ex: UNN">
                <small class="form-text text-muted">Nome exibido na tela inicial do dispositivo.</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Cor do Tema (Status Bar)</label>
                <div class="input-group colorpicker-element">
                    <input type="text" name="pwa_theme_color" class="form-control"
                        value="{{ $settings['pwa_theme_color'] ?? '#0C6BF7' }}">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-square"
                                style="color: {{ $settings['pwa_theme_color'] ?? '#0C6BF7' }}"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Cor de Fundo (Splash Screen)</label>
                <div class="input-group colorpicker-element">
                    <input type="text" name="pwa_background_color" class="form-control"
                        value="{{ $settings['pwa_background_color'] ?? '#FFFFFF' }}">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-square"
                                style="color: {{ $settings['pwa_background_color'] ?? '#FFFFFF' }}"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Display Mode</label>
                <select name="pwa_display" class="form-control">
                    <option value="standalone" {{ ($settings['pwa_display'] ?? 'standalone') == 'standalone' ? 'selected' : '' }}>Standalone (App Nativo - Recomendado)</option>
                    <option value="fullscreen" {{ ($settings['pwa_display'] ?? 'standalone') == 'fullscreen' ? 'selected' : '' }}>Fullscreen (Tela Cheia)</option>
                    <option value="minimal-ui" {{ ($settings['pwa_display'] ?? 'standalone') == 'minimal-ui' ? 'selected' : '' }}>Minimal UI (Com controles básicos)</option>
                    <option value="browser" {{ ($settings['pwa_display'] ?? 'standalone') == 'browser' ? 'selected' : '' }}>Browser (Aba Normal)</option>
                </select>
            </div>
        </div>
    </div>

    <hr>

    <h5 class="mb-3"><i class="fas fa-images mr-2 text-muted"></i> Ícones e Imagens</h5>
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="form-group text-center">
                <label>Ícone (192x192)</label>
                <div class="upload-box" data-remove-input="#remove_pwa_icon_192"
                    data-existing-url="{{ $getUrl('pwa_icon_192') }}">
                    <input type="file" name="pwa_icon_192" class="d-none" accept="image/png">
                    <input type="hidden" name="remove_pwa_icon_192" id="remove_pwa_icon_192" value="0">
                    <div class="upload-preview mb-2">
                        @if($url = $getUrl('pwa_icon_192'))
                            <img src="{{ $url }}" class="img-fluid rounded border">
                        @else
                            <div class="text-muted p-4 border rounded bg-light">
                                <i class="fas fa-image fa-2x mb-2"></i><br>192x192
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i
                            class="fas fa-upload"></i> Selecionar</button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger upload-remove {{ $getUrl('pwa_icon_192') ? '' : 'd-none' }}"><i
                            class="fas fa-trash"></i></button>
                </div>
                <small class="form-text text-muted">PNG obrigatório</small>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="form-group text-center">
                <label>Ícone (512x512)</label>
                <div class="upload-box" data-remove-input="#remove_pwa_icon_512"
                    data-existing-url="{{ $getUrl('pwa_icon_512') }}">
                    <input type="file" name="pwa_icon_512" class="d-none" accept="image/png">
                    <input type="hidden" name="remove_pwa_icon_512" id="remove_pwa_icon_512" value="0">
                    <div class="upload-preview mb-2">
                        @if($url = $getUrl('pwa_icon_512'))
                            <img src="{{ $url }}" class="img-fluid rounded border">
                        @else
                            <div class="text-muted p-4 border rounded bg-light">
                                <i class="fas fa-image fa-2x mb-2"></i><br>512x512
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i
                            class="fas fa-upload"></i> Selecionar</button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger upload-remove {{ $getUrl('pwa_icon_512') ? '' : 'd-none' }}"><i
                            class="fas fa-trash"></i></button>
                </div>
                <small class="form-text text-muted">PNG obrigatório (Splash)</small>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label>Banner de Instalação (Opcional)</label>
                <div class="upload-box" data-remove-input="#remove_pwa_banner"
                    data-existing-url="{{ $getUrl('pwa_banner') }}" style="height: auto;">
                    <input type="file" name="pwa_banner" class="d-none" accept="image/*">
                    <input type="hidden" name="remove_pwa_banner" id="remove_pwa_banner" value="0">
                    <div class="upload-preview mb-2 text-center">
                        @if($url = $getUrl('pwa_banner'))
                            <img src="{{ $url }}" class="img-fluid rounded border" style="max-height: 200px;">
                        @else
                            <div class="text-muted p-5 border rounded bg-light">
                                <i class="fas fa-image fa-3x mb-2"></i><br>Banner Promocional
                            </div>
                        @endif
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i
                                class="fas fa-upload"></i> Selecionar Imagem</button>
                        <button type="button"
                            class="btn btn-sm btn-outline-danger upload-remove {{ $getUrl('pwa_banner') ? '' : 'd-none' }}"><i
                                class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>