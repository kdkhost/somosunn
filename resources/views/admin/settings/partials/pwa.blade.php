<div class="card-body">
    <h5 class="text-primary mb-3"><i class="fas fa-mobile-alt mr-2"></i>Progressive Web App (PWA)</h5>
    <div class="alert alert-info">
        Transforme seu site em um aplicativo instalável.
    </div>

    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
        <input type="hidden" name="pwa_enabled" value="0">
        <input type="checkbox" class="custom-control-input" id="pwa_enabled" name="pwa_enabled" value="1" {{ ($settings['pwa_enabled'] ?? 0) ? 'checked' : '' }}>
        <label class="custom-control-label" for="pwa_enabled">Ativar PWA</label>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label>Nome do App (Curto)</label>
            <input name="pwa_short_name" class="form-control"
                value="{{ $settings['pwa_short_name'] ?? config('app.name') }}" placeholder="Ex: SomosUNN">
        </div>
        <div class="col-md-6 form-group">
            <label>Cor do Tema (Status Bar)</label>
            <div class="input-group colorpicker-element">
                <input name="pwa_theme_color" class="form-control"
                    value="{{ $settings['pwa_theme_color'] ?? '#007bff' }}">
                <div class="input-group-append">
                    <span class="input-group-text"><i class="fas fa-square"
                            style="color: {{ $settings['pwa_theme_color'] ?? '#007bff' }}"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label>Cor de Fundo (Splash Screen)</label>
            <div class="input-group colorpicker-element">
                <input name="pwa_background_color" class="form-control"
                    value="{{ $settings['pwa_background_color'] ?? '#ffffff' }}">
                <div class="input-group-append">
                    <span class="input-group-text"><i class="fas fa-square"
                            style="color: {{ $settings['pwa_background_color'] ?? '#ffffff' }}"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-6 form-group">
            <label>Display Mode</label>
            <select name="pwa_display" class="form-control">
                <option value="standalone" {{ ($settings['pwa_display'] ?? 'standalone') === 'standalone' ? 'selected' : '' }}>Standalone (App Nativo)</option>
                <option value="fullscreen" {{ ($settings['pwa_display'] ?? '') === 'fullscreen' ? 'selected' : '' }}>
                    Fullscreen (Tela Cheia)</option>
                <option value="minimal-ui" {{ ($settings['pwa_display'] ?? '') === 'minimal-ui' ? 'selected' : '' }}>
                    Minimal UI</option>
                <option value="browser" {{ ($settings['pwa_display'] ?? '') === 'browser' ? 'selected' : '' }}>Browser
                </option>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 form-group">
            <label>Ícone (192x192)</label>
            <div class="upload-box" data-max-size="{{ 1 * 1024 * 1024 }}"
                data-existing-url="{{ $getUrl('pwa_icon_192') }}" data-remove-input="[name='remove_pwa_icon_192']">
                <input type="hidden" name="remove_pwa_icon_192" value="0">
                <input type="file" name="pwa_icon_192" accept="image/png" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <label>Ícone (512x512)</label>
            <div class="upload-box" data-max-size="{{ 1 * 1024 * 1024 }}"
                data-existing-url="{{ $getUrl('pwa_icon_512') }}" data-remove-input="[name='remove_pwa_icon_512']">
                <input type="hidden" name="remove_pwa_icon_512" value="0">
                <input type="file" name="pwa_icon_512" accept="image/png" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <label>Splash Screen (Capa)</label>
            <div class="upload-box" data-max-size="{{ 2 * 1024 * 1024 }}"
                data-existing-url="{{ $getUrl('pwa_splash_image') }}"
                data-remove-input="[name='remove_pwa_splash_image']">
                <input type="hidden" name="remove_pwa_splash_image" value="0">
                <input type="file" name="pwa_splash_image" accept="image/*" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <label>Banner (Restrito PWA)</label>
            <div class="upload-box" data-max-size="{{ 2 * 1024 * 1024 }}"
                data-existing-url="{{ $getUrl('pwa_banner_image') }}"
                data-remove-input="[name='remove_pwa_banner_image']">
                <input type="hidden" name="remove_pwa_banner_image" value="0">
                <input type="file" name="pwa_banner_image" accept="image/*" class="d-none">
                <div class="upload-preview text-center text-muted"></div>
                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
            </div>
            <small class="text-muted">Exibido apenas no App.</small>
        </div>
    </div>
</div>