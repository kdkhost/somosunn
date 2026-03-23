<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-paint-brush mr-2"></i> Personalize as cores, fontes e estilo visual do seu site para combinar
        com sua marca.
    </div>

    <h5 class="text-primary mb-3"><i class="fas fa-home mr-2"></i> Hero (Página Inicial)</h5>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Título Principal</label>
                <input type="text" name="hero_title" class="form-control form-control-lg"
                    value="{{ $settings['hero_title'] ?? 'Transforme sua carreira' }}">
            </div>
            <div class="form-group">
                <label>Subtítulo</label>
                <textarea name="hero_subtitle" class="form-control"
                    rows="3">{{ $settings['hero_subtitle'] ?? 'Junte-se a milhares de membros e aprenda com os melhores.' }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Opacidade do Degradê (%)</label>
                        <input name="site_bg_gradient_opacity" type="number" min="0" max="100" class="form-control"
                            value="{{ $settings['site_bg_gradient_opacity'] ?? 85 }}">
                    </div>
                </div>
                <div class="col-md-6">
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
            <small class="text-muted">Essas configurações afetam a sobreposição escura sobre a imagem de fundo do
                Hero.</small>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-calendar-alt mr-2"></i> Fundo de Eventos e Mentorias</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Desfoque (Blur px)</label>
            <input name="events_hero_bg_blur_px" type="number" class="form-control"
                value="{{ $settings['events_hero_bg_blur_px'] ?? 64 }}">
            <small class="form-text text-muted">Quanto maior, mais desfocada será a imagem de fundo.</small>
        </div>
        <div class="col-md-6 form-group">
            <label>Intensidade da Película Escura (%)</label>
            <input name="events_hero_film_strength_percent" type="number" min="0" max="100" class="form-control"
                value="{{ $settings['events_hero_film_strength_percent'] ?? 100 }}">
            <small class="form-text text-muted">Controla o quão escuro fica o fundo para melhorar a leitura do
                texto.</small>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-palette mr-2"></i> Cores e Tema</h5>
    <div class="row">
        <div class="col-md-3 form-group">
            <label>Tema Padrão</label>
            <select name="site_theme" class="form-control">
                <option value="light" {{ ($settings['site_theme'] ?? 'light') === 'light' ? 'selected' : '' }}>Light
                    (Claro)</option>
                <option value="dark" {{ ($settings['site_theme'] ?? '') === 'dark' ? 'selected' : '' }}>Dark (Escuro)
                </option>
            </select>
        </div>
        <div class="col-md-3 form-group">
            <label>Tipo de Layout (Largura)</label>
            <select name="site_layout_type" class="form-control">
                <option value="boxed" {{ ($settings['site_layout_type'] ?? 'boxed') === 'boxed' ? 'selected' : '' }}>Boxed (Centralizado)</option>
                <option value="full" {{ ($settings['site_layout_type'] ?? '') === 'full' ? 'selected' : '' }}>Full (Tela Cheia)</option>
            </select>
        </div>
        <div class="col-md-3 form-group">
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
        <div class="col-md-3 form-group">
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
        <div class="col-md-3 form-group">
            <label>Font Family</label>
            <input name="site_font_family" class="form-control"
                value="{{ $settings['site_font_family'] ?? 'Inter, sans-serif' }}">
            <small class="text-muted">Ex: 'Inter', sans-serif</small>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-window-maximize mr-2"></i> Rodapé e Redes Sociais</h5>
    <div class="form-group">
        <label>Texto do Rodapé</label>
        <textarea name="footer_text" class="form-control" rows="2">{{ $settings['footer_text'] ?? '' }}</textarea>
    </div>

    <div class="row">
        <div class="col-md-3 form-group">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                </div>
                <input name="social_instagram" class="form-control" placeholder="Instagram URL"
                    value="{{ $settings['social_instagram'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-3 form-group">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fab fa-facebook"></i></span>
                </div>
                <input name="social_facebook" class="form-control" placeholder="Facebook URL"
                    value="{{ $settings['social_facebook'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-3 form-group">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fab fa-youtube"></i></span>
                </div>
                <input name="social_youtube" class="form-control" placeholder="Youtube URL"
                    value="{{ $settings['social_youtube'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-3 form-group">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fab fa-linkedin"></i></span>
                </div>
                <input name="social_linkedin" class="form-control" placeholder="LinkedIn URL"
                    value="{{ $settings['social_linkedin'] ?? '' }}">
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-magic mr-2"></i> Autenticação (Animações)</h5>
    <div class="alert alert-light border mb-4">
        <i class="fas fa-info-circle mr-2"></i> A animação do banner aparece apenas em telas de notebook/desktop
        (coluna visual do formulário).
    </div>

    <div class="form-group mb-4">
        <div class="custom-control custom-switch custom-switch-lg">
            <input type="hidden" name="auth_visual_animation_enabled" value="0">
            <input type="checkbox" class="custom-control-input" id="auth_visual_animation_enabled"
                name="auth_visual_animation_enabled" value="1" {{ ($settings['auth_visual_animation_enabled'] ?? 1) ? 'checked' : '' }}>
            <label class="custom-control-label font-weight-bold" for="auth_visual_animation_enabled">Ativar animação do banner de autenticação</label>
        </div>
        <small class="text-muted">Quando desativado, a coluna visual fica estática (sem partículas/animações).</small>
    </div>

    <div class="row">
        <div class="col-md-3 form-group">
            <div class="custom-control custom-switch">
                <input type="hidden" name="auth_visual_animation_login" value="0">
                <input type="checkbox" class="custom-control-input" id="auth_visual_animation_login"
                    name="auth_visual_animation_login" value="1" {{ ($settings['auth_visual_animation_login'] ?? 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="auth_visual_animation_login">Login</label>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <div class="custom-control custom-switch">
                <input type="hidden" name="auth_visual_animation_register" value="0">
                <input type="checkbox" class="custom-control-input" id="auth_visual_animation_register"
                    name="auth_visual_animation_register" value="1" {{ ($settings['auth_visual_animation_register'] ?? 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="auth_visual_animation_register">Cadastro</label>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <div class="custom-control custom-switch">
                <input type="hidden" name="auth_visual_animation_password_email" value="0">
                <input type="checkbox" class="custom-control-input" id="auth_visual_animation_password_email"
                    name="auth_visual_animation_password_email" value="1" {{ ($settings['auth_visual_animation_password_email'] ?? 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="auth_visual_animation_password_email">Recuperar senha</label>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <div class="custom-control custom-switch">
                <input type="hidden" name="auth_visual_animation_password_reset" value="0">
                <input type="checkbox" class="custom-control-input" id="auth_visual_animation_password_reset"
                    name="auth_visual_animation_password_reset" value="1" {{ ($settings['auth_visual_animation_password_reset'] ?? 1) ? 'checked' : '' }}>
                <label class="custom-control-label" for="auth_visual_animation_password_reset">Resetar senha</label>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-spinner mr-2"></i> Preloader</h5>
    <div class="form-group">
        <div class="custom-control custom-switch custom-switch-lg">
            <input type="checkbox" class="custom-control-input" id="preloader_enabled" name="preloader_enabled"
                value="1" {{ ($settings['preloader_enabled'] ?? 0) ? 'checked' : '' }}>
            <label class="custom-control-label font-weight-bold" for="preloader_enabled">Ativar Animação de
                Carregamento</label>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Imagem do Preloader (GIF/SVG)</label>
                <div class="upload-box" data-remove-input="#remove_preloader_image"
                    data-existing-url="{{ $getUrl('preloader_image') }}">
                    <input type="file" name="preloader_image" class="d-none" accept="image/*">
                    <input type="hidden" name="remove_preloader_image" id="remove_preloader_image" value="0">
                    <div class="upload-preview mb-2">
                        @if($url = $getUrl('preloader_image'))
                            <img src="{{ $url }}" class="img-fluid" style="max-height: 64px;">
                        @else
                            <div class="text-muted p-4 border rounded bg-light">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i
                            class="fas fa-upload"></i> Selecionar</button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger upload-remove {{ $getUrl('preloader_image') ? '' : 'd-none' }}"><i
                            class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
