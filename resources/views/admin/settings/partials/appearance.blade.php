<div class="card-body">
    <h5 class="text-primary mb-3"><i class="fas fa-home mr-2"></i>Hero (Página Inicial)</h5>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Título Principal</label>
                <input name="hero_title" class="form-control"
                    value="{{ $settings['hero_title'] ?? 'Transforme sua carreira' }}">
            </div>
            <div class="form-group">
                <label>Subtítulo</label>
                <textarea name="hero_subtitle" class="form-control"
                    rows="3">{{ $settings['hero_subtitle'] ?? 'Junte-se a milhares de membros e aprenda com os melhores.' }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Opacidade do Degradê (%)</label>
                <input name="site_bg_gradient_opacity" type="number" min="0" max="100" class="form-control"
                    value="{{ $settings['site_bg_gradient_opacity'] ?? 85 }}">
            </div>
            <div class="form-group">
                <label>Cor do Degradê (Início)</label>
                <div class="input-group colorpicker-element">
                    <input name="site_bg_gradient_start" class="form-control"
                        value="{{ $settings['site_bg_gradient_start'] ?? '#000000' }}">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-square"
                                style="color: {{ $settings['site_bg_gradient_start'] ?? '#000000' }}"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>
    <h5 class="text-primary mb-3"><i class="fas fa-calendar-alt mr-2"></i>Eventos e Mentorias (Fundo Hero)</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Desfoque (Blur px)</label>
            <input name="events_hero_bg_blur_px" type="number" class="form-control"
                value="{{ $settings['events_hero_bg_blur_px'] ?? 64 }}">
        </div>
        <div class="col-md-6 form-group">
            <label>Intensidade da Película (%)</label>
            <input name="events_hero_film_strength_percent" type="number" min="0" max="100" class="form-control"
                value="{{ $settings['events_hero_film_strength_percent'] ?? 100 }}">
        </div>
    </div>

    <hr>
    <h5 class="text-primary mb-3"><i class="fas fa-palette mr-2"></i>Identidade Visual</h5>
    <div class="row">
        <div class="col-md-4 form-group">
            <label>Tema Padrão</label>
            <select name="site_theme" class="form-control">
                <option value="light" {{ ($settings['site_theme'] ?? 'light') === 'light' ? 'selected' : '' }}>Light (Claro)</option>
                <option value="dark" {{ ($settings['site_theme'] ?? '') === 'dark' ? 'selected' : '' }}>Dark (Escuro)</option>
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label>Cor Primária</label>
            <div class="input-group colorpicker-element">
                <input name="site_color_primary" class="form-control"
                    value="{{ $settings['site_color_primary'] ?? '#007bff' }}">
                <div class="input-group-append">
                    <span class="input-group-text"><i class="fas fa-square"
                            style="color: {{ $settings['site_color_primary'] ?? '#007bff' }}"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-4 form-group">
            <label>Cor Secundária</label>
            <div class="input-group colorpicker-element">
                <input name="site_color_secondary" class="form-control"
                    value="{{ $settings['site_color_secondary'] ?? '#6c757d' }}">
                <div class="input-group-append">
                    <span class="input-group-text"><i class="fas fa-square"
                            style="color: {{ $settings['site_color_secondary'] ?? '#6c757d' }}"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label>Font Family</label>
            <input name="site_font_family" class="form-control"
                value="{{ $settings['site_font_family'] ?? 'Inter, sans-serif' }}">
            <small class="text-muted">Ex: 'Inter', sans-serif</small>
        </div>
    </div>

    <hr>
    <h5 class="text-primary mb-3"><i class="fas fa-window-maximize mr-2"></i>Rodapé</h5>
    <div class="form-group">
        <label>Texto do Rodapé</label>
        <textarea name="footer_text" class="form-control"
            rows="3">{{ $settings['footer_text'] ?? '' }}</textarea>
    </div>

    <div class="row">
        <div class="col-md-3 form-group">
            <label><i class="fab fa-instagram mr-1"></i>Instagram URL</label>
            <input name="social_instagram" class="form-control"
                value="{{ $settings['social_instagram'] ?? '' }}">
        </div>
        <div class="col-md-3 form-group">
            <label><i class="fab fa-facebook mr-1"></i>Facebook URL</label>
            <input name="social_facebook" class="form-control"
                value="{{ $settings['social_facebook'] ?? '' }}">
        </div>
        <div class="col-md-3 form-group">
            <label><i class="fab fa-youtube mr-1"></i>Youtube URL</label>
            <input name="social_youtube" class="form-control"
                value="{{ $settings['social_youtube'] ?? '' }}">
        </div>
        <div class="col-md-3 form-group">
            <label><i class="fab fa-linkedin mr-1"></i>LinkedIn URL</label>
            <input name="social_linkedin" class="form-control"
                value="{{ $settings['social_linkedin'] ?? '' }}">
        </div>
    </div>

    <hr>
    <h5 class="text-primary mb-3"><i class="fas fa-quote-left mr-2"></i>Depoimentos (Carrossel)</h5>
    <div class="row">
        <div class="col-md-4">
            <div
                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                <input type="hidden" name="testimonials_carousel_enabled" value="0">
                <input type="checkbox" class="custom-control-input" id="testimonials_carousel_enabled"
                    name="testimonials_carousel_enabled" value="1" {{ ($settings['testimonials_carousel_enabled'] ?? 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="testimonials_carousel_enabled">Ativar
                    Carrossel</label>
            </div>
        </div>
        <div class="col-md-4">
            <div
                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                <input type="hidden" name="testimonials_carousel_show_arrows" value="0">
                <input type="checkbox" class="custom-control-input" id="testimonials_carousel_show_arrows"
                    name="testimonials_carousel_show_arrows" value="1" {{ ($settings['testimonials_carousel_show_arrows'] ?? 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="testimonials_carousel_show_arrows">Exibir
                    Setas</label>
            </div>
        </div>
        <div class="col-md-4">
            <div
                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                <input type="hidden" name="testimonials_carousel_show_dots" value="0">
                <input type="checkbox" class="custom-control-input" id="testimonials_carousel_show_dots"
                    name="testimonials_carousel_show_dots" value="1" {{ ($settings['testimonials_carousel_show_dots'] ?? 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="testimonials_carousel_show_dots">Exibir
                    Bolinhas</label>
            </div>
        </div>
    </div>

    <hr>
    <h5 class="text-primary mb-3"><i class="fas fa-spinner mr-2"></i>Preloader</h5>
    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
        <input type="hidden" name="preloader_enabled" value="0">
        <input type="checkbox" class="custom-control-input" id="preloader_enabled" name="preloader_enabled"
            value="1" {{ ($settings['preloader_enabled'] ?? 0) ? 'checked' : '' }}>
        <label class="custom-control-label" for="preloader_enabled">Ativar Preloader</label>
    </div>
    <div class="form-group" style="max-width: 300px;">
        <label>Imagem do Preloader</label>
        <div class="upload-box" data-max-size="{{ 1 * 1024 * 1024 }}"
            data-existing-url="{{ $getUrl('preloader_image') }}"
            data-remove-input="[name='remove_preloader_image']">
            <input type="hidden" name="remove_preloader_image" value="0">
            <input type="file" name="preloader_image" accept="image/*" class="d-none">
            <div class="upload-preview text-center text-muted"></div>
            <div class="upload-help text-muted small mt-2">GIF, SVG ou PNG</div>
            <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar
                arquivo</button>
            <button type="button"
                class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
        </div>
    </div>
</div>
