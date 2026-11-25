<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-paint-brush me-2"></i> Personalize as cores, fontes e estilo visual do seu site para combinar
        com sua marca.
    </div>

    <h5 class="text-primary mb-3"><i class="fas fa-home me-2"></i> Hero (Página Inicial)</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="form-group">
                <label for="appearance_hero_title">Título Principal</label>
                <input type="text" id="appearance_hero_title" name="hero_title" class="form-control form-control-lg"
                    value="{{ $settings['hero_title'] ?? 'Transforme sua carreira' }}">
            </div>
            <div class="form-group">
                <label for="appearance_hero_subtitle">Subtítulo</label>
                <textarea id="appearance_hero_subtitle" name="hero_subtitle" class="form-control"
                    rows="3">{{ $settings['hero_subtitle'] ?? 'Junte-se a milhares de membros e aprenda com os melhores.' }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="appearance_bg_gradient_opacity">Opacidade do Degradê (%)</label>
                        <input id="appearance_bg_gradient_opacity" name="site_bg_gradient_opacity" type="number" min="0" max="100" class="form-control"
                            value="{{ $settings['site_bg_gradient_opacity'] ?? 85 }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="appearance_bg_gradient_start">Cor do Degradê (Início)</label>
                        <div class="input-group">
                            <input type="color" id="appearance_bg_gradient_start_picker" class="form-control form-control-color"
                                value="{{ preg_match('/^#[0-9a-fA-F]{6}$/', $settings['site_bg_gradient_start'] ?? '') ? $settings['site_bg_gradient_start'] : '#000000' }}" style="max-width: 48px;">
                            <input id="appearance_bg_gradient_start" name="site_bg_gradient_start" class="form-control"
                                value="{{ $settings['site_bg_gradient_start'] ?? '#000000' }}">
                        </div>
                    </div>
                </div>
            </div>
            <small class="text-muted">Essas configurações afetam a sobreposição escura sobre a imagem de fundo do Hero.</small>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-calendar-alt me-2"></i> Fundo de Eventos e Mentorias</h5>
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
            <small class="form-text text-muted">Controla o quão escuro fica o fundo para melhorar a leitura do texto.</small>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-palette me-2"></i> Cores e Tema (Preview Dinâmico em Tempo Real)</h5>
    @php
        $primaryHex = preg_match('/^#[0-9a-fA-F]{6}$/', $settings['site_color_primary'] ?? '') ? $settings['site_color_primary'] : '#1F5EDB';
        $secondaryHex = preg_match('/^#[0-9a-fA-F]{6}$/', $settings['site_color_secondary'] ?? '') ? $settings['site_color_secondary'] : '#64748b';
    @endphp
    <div class="row g-3 mb-3">
        <div class="col-md-3 form-group">
            <label for="appearance_site_theme">Tema Padrão</label>
            <select id="appearance_site_theme" name="site_theme" class="form-select">
                <option value="light" {{ ($settings['site_theme'] ?? 'light') === 'light' ? 'selected' : '' }}>Light (Claro)</option>
                <option value="dark" {{ ($settings['site_theme'] ?? '') === 'dark' ? 'selected' : '' }}>Dark (Escuro)</option>
            </select>
        </div>
        <div class="col-md-3 form-group">
            <label for="appearance_color_primary">Cor Primária</label>
            <div class="input-group">
                <input type="color" id="appearance_color_primary_picker" class="form-control form-control-color" value="{{ $primaryHex }}" style="max-width: 48px;">
                <input id="appearance_color_primary" name="site_color_primary" class="form-control" value="{{ $settings['site_color_primary'] ?? '#1F5EDB' }}">
            </div>
            <small class="text-secondary">Botões principais, links e destaques.</small>
        </div>
        <div class="col-md-3 form-group">
            <label for="appearance_color_secondary">Cor Secundária</label>
            <div class="input-group">
                <input type="color" id="appearance_color_secondary_picker" class="form-control form-control-color" value="{{ $secondaryHex }}" style="max-width: 48px;">
                <input id="appearance_color_secondary" name="site_color_secondary" class="form-control" value="{{ $settings['site_color_secondary'] ?? '#6c757d' }}">
            </div>
            <small class="text-secondary">Badges, subtítulos e botões secundários.</small>
        </div>
        <div class="col-md-3 form-group">
            <label for="appearance_font_family">Font Family</label>
            <input id="appearance_font_family" name="site_font_family" class="form-control"
                value="{{ $settings['site_font_family'] ?? 'Inter, sans-serif' }}">
            <small class="text-muted">Ex: 'Inter', sans-serif</small>
        </div>
    </div>

    {{-- Preview Ao Vivo de Onde Cada Cor é Aplicada --}}
    <div class="unn-section-box mb-4" id="liveBrandPreviewCard">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="fw-bold small text-uppercase text-secondary">
                <i class="fas fa-eye me-1 text-primary"></i> Preview em Tempo Real — Onde suas cores são aplicadas
            </div>
            <div class="d-flex flex-wrap gap-2 small">
                <span class="badge rounded-pill text-bg-light border text-dark">1. Degradê do Hero</span>
                <span class="badge rounded-pill text-bg-light border text-dark">2. Botão &amp; Barra Primária</span>
                <span class="badge rounded-pill text-bg-light border text-dark">3. Badge &amp; Ação Secundária</span>
            </div>
        </div>
        <div class="row g-3 align-items-stretch">
            <div class="col-lg-7">
                <div id="liveHeroPreview" class="rounded-4 p-4 text-white position-relative overflow-hidden h-100 d-flex flex-column justify-content-center"
                    style="background: linear-gradient(135deg, rgba(0,0,0,0.85), rgba(31,94,219,0.75)); min-height: 158px;">
                    <span class="badge rounded-pill align-self-start mb-2" id="liveSecondaryBadge" style="background: {{ $secondaryHex }}; color: #fff;">
                        Badge Secundária
                    </span>
                    <h4 class="fw-bold mb-1 text-white" id="liveHeroTitle">{{ $settings['hero_title'] ?? 'Transforme sua carreira' }}</h4>
                    <p class="small mb-3 text-white-50" id="liveHeroSubtitle">{{ $settings['hero_subtitle'] ?? 'Junte-se a milhares de membros e aprenda com os melhores.' }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="btn btn-sm fw-bold text-white px-3" id="livePrimaryBtn" style="background: {{ $primaryHex }}; border-color: {{ $primaryHex }};">
                            <i class="fas fa-rocket me-1"></i> Botão Primário
                        </span>
                        <span class="btn btn-sm fw-bold px-3" id="liveSecondaryBtn" style="border: 1px solid {{ $secondaryHex }}; color: #fff; background: rgba(255,255,255,0.08);">
                            Botão Secundário
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card mb-0 h-100 border">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small">Elementos da Interface</span>
                                <span class="small fw-bold" id="livePrimaryLink" style="color: {{ $primaryHex }};">Link de Destaque &rarr;</span>
                            </div>
                            <p class="small text-secondary mb-3">Acompanhe instantaneamente o contraste da barra de progresso, ícones e botões antes de salvar.</p>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-secondary">Progresso / Indicador Primário</span>
                                <strong id="livePrimaryHexLabel">{{ $primaryHex }}</strong>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 999px;">
                                <div class="progress-bar" id="livePrimaryBar" style="width: 72%; background: {{ $primaryHex }};"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-window-maximize me-2"></i> Rodapé e Redes Sociais</h5>
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

    <h5 class="text-primary mb-3"><i class="fas fa-magic me-2"></i> Autenticação (Animações)</h5>
    <div class="alert alert-light border mb-4">
        <i class="fas fa-info-circle me-2"></i> A animação do banner aparece apenas em telas de notebook/desktop
        (coluna visual do formulário).
    </div>

    <div class="form-group mb-4">
        <div class="form-check form-switch custom-switch-lg">
            <input type="hidden" name="auth_visual_animation_enabled" value="0">
            <input type="checkbox" class="form-check-input" id="auth_visual_animation_enabled"
                name="auth_visual_animation_enabled" value="1" {{ ($settings['auth_visual_animation_enabled'] ?? 1) ? 'checked' : '' }}>
            <label class="form-check-label fw-bold" for="auth_visual_animation_enabled">Ativar animação do banner de autenticação</label>
        </div>
        <small class="text-muted">Quando desativado, a coluna visual fica estática (sem partículas/animações).</small>
    </div>

    <div class="row">
        <div class="col-md-3 form-group">
            <div class="form-check form-switch">
                <input type="hidden" name="auth_visual_animation_login" value="0">
                <input type="checkbox" class="form-check-input" id="auth_visual_animation_login"
                    name="auth_visual_animation_login" value="1" {{ ($settings['auth_visual_animation_login'] ?? 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="auth_visual_animation_login">Login</label>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <div class="form-check form-switch">
                <input type="hidden" name="auth_visual_animation_register" value="0">
                <input type="checkbox" class="form-check-input" id="auth_visual_animation_register"
                    name="auth_visual_animation_register" value="1" {{ ($settings['auth_visual_animation_register'] ?? 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="auth_visual_animation_register">Cadastro</label>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <div class="form-check form-switch">
                <input type="hidden" name="auth_visual_animation_password_email" value="0">
                <input type="checkbox" class="form-check-input" id="auth_visual_animation_password_email"
                    name="auth_visual_animation_password_email" value="1" {{ ($settings['auth_visual_animation_password_email'] ?? 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="auth_visual_animation_password_email">Recuperar senha</label>
            </div>
        </div>
        <div class="col-md-3 form-group">
            <div class="form-check form-switch">
                <input type="hidden" name="auth_visual_animation_password_reset" value="0">
                <input type="checkbox" class="form-check-input" id="auth_visual_animation_password_reset"
                    name="auth_visual_animation_password_reset" value="1" {{ ($settings['auth_visual_animation_password_reset'] ?? 1) ? 'checked' : '' }}>
                <label class="form-check-label" for="auth_visual_animation_password_reset">Resetar senha</label>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-spinner me-2"></i> Central do Preloader (Animação de Carregamento)</h5>
    @php
        $preloaderEnabledVal = (string) ($settings['preloader_enabled'] ?? '1') !== '0';
        $preloaderAdminEnabledVal = (string) ($settings['preloader_admin_enabled'] ?? '1') !== '0';
        $preloaderShowProgressVal = (string) ($settings['preloader_show_progress'] ?? '1') !== '0';
        $preloaderPjaxEnabledVal = (string) ($settings['preloader_pjax_enabled'] ?? '1') !== '0';
        $preloaderStyleVal = $settings['preloader_style'] ?? 'orbital';
        $preloaderExitVal = $settings['preloader_exit_animation'] ?? 'blur';
        $preloaderDurationVal = (int) ($settings['preloader_duration_ms'] ?? 650);
        $preloaderTextVal = $settings['preloader_text'] ?? 'SOMOS UNN';
        $preloaderAccentVal = $settings['preloader_accent_color'] ?? '#1F5EDB';
        $preloaderModels = [
            'orbital' => [
                'name' => 'Orbital UNN (Atual)',
                'desc' => 'Anéis orbitais duplos, pulso radial e barra futurista',
                'icon' => 'fas fa-atom',
                'badge' => 'Padrão',
            ],
            'pulse' => [
                'name' => 'Pulso Neon / Halo',
                'desc' => 'Ondas radiais concêntricas em neon suave',
                'icon' => 'fas fa-bullseye',
                'badge' => 'Moderno',
            ],
            'minimal' => [
                'name' => 'Minimalista Clean',
                'desc' => 'Spinner fino ultra-rápido com logo limpa',
                'icon' => 'fas fa-circle-notch',
                'badge' => 'Leve',
            ],
            'glass' => [
                'name' => 'Glassmorphism Card',
                'desc' => 'Cartão translúcido com blur e borda luminosa',
                'icon' => 'fas fa-layer-group',
                'badge' => 'SaaS',
            ],
            'executive' => [
                'name' => 'Barra Executiva',
                'desc' => 'Logo em destaque com barra larga e shimmer',
                'icon' => 'fas fa-bars-progress',
                'badge' => 'Clean',
            ],
        ];
    @endphp

    <style>
        .preloader-model-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
            gap: 0.75rem;
        }
        .preloader-model-card {
            position: relative;
            display: block;
            border: 1.5px solid var(--bs-border-color);
            border-radius: 0.85rem;
            padding: 0.9rem;
            cursor: pointer;
            background: var(--bs-body-bg);
            transition: all 0.18s ease;
            margin: 0;
        }
        .preloader-model-card:hover {
            border-color: rgba(31, 94, 219, 0.55);
            transform: translateY(-2px);
        }
        .preloader-model-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .preloader-model-card.is-selected {
            border-color: #1F5EDB;
            background: rgba(31, 94, 219, 0.08);
            box-shadow: 0 6px 18px -6px rgba(31, 94, 219, 0.35);
        }
        .preloader-model-icon {
            width: 38px;
            height: 38px;
            border-radius: 0.65rem;
            background: rgba(31, 94, 219, 0.12);
            color: #1F5EDB;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        [data-bs-theme="dark"] .preloader-model-icon {
            background: rgba(59, 130, 246, 0.22);
            color: #93c5fd;
        }
    </style>

    <div class="unn-section-box mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <div class="fw-bold">Onde o Preloader será exibido</div>
                <small class="text-secondary">Ative ou desative separadamente para o site público e para o painel administrativo.</small>
            </div>
            <button type="button" class="btn btn-primary btn-sm" id="btnPreviewPreloaderAdmin">
                <i class="fas fa-play me-1"></i> Testar Preloader Agora
            </button>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-xl-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="preloader_enabled" value="0">
                    <input type="checkbox" class="form-check-input" id="preloader_enabled" name="preloader_enabled" value="1" {{ $preloaderEnabledVal ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="preloader_enabled">Ativar no Site / Portal</label>
                </div>
                <small class="text-secondary d-block">Páginas públicas e painel de membros.</small>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="preloader_admin_enabled" value="0">
                    <input type="checkbox" class="form-check-input" id="preloader_admin_enabled" name="preloader_admin_enabled" value="1" {{ $preloaderAdminEnabledVal ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="preloader_admin_enabled">Ativar no Painel Admin</label>
                </div>
                <small class="text-secondary d-block">Opcional para rotas <code>/admin/*</code>.</small>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="preloader_show_progress" value="0">
                    <input type="checkbox" class="form-check-input" id="preloader_show_progress" name="preloader_show_progress" value="1" {{ $preloaderShowProgressVal ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="preloader_show_progress">Exibir Barra de Progresso</label>
                </div>
                <small class="text-secondary d-block">Barra linear animada abaixo do texto.</small>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="preloader_pjax_enabled" value="0">
                    <input type="checkbox" class="form-check-input" id="preloader_pjax_enabled" name="preloader_pjax_enabled" value="1" {{ $preloaderPjaxEnabledVal ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="preloader_pjax_enabled">Exibir em Navegação PJAX</label>
                </div>
                <small class="text-secondary d-block">Animação rápida ao trocar telas no admin.</small>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label d-block mb-2">Modelo Visual do Preloader</label>
        <div class="preloader-model-grid" id="preloaderModelGrid">
            @foreach($preloaderModels as $slug => $meta)
                <label class="preloader-model-card {{ $preloaderStyleVal === $slug ? 'is-selected' : '' }}">
                    <input type="radio" name="preloader_style" value="{{ $slug }}" {{ $preloaderStyleVal === $slug ? 'checked' : '' }}>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="preloader-model-icon"><i class="{{ $meta['icon'] }}"></i></span>
                        <span class="badge rounded-pill {{ $slug === 'orbital' ? 'text-bg-primary' : 'text-bg-secondary' }}">{{ $meta['badge'] }}</span>
                    </div>
                    <div class="fw-bold small mb-1">{{ $meta['name'] }}</div>
                    <div class="text-secondary" style="font-size: 0.76rem; line-height: 1.35;">{{ $meta['desc'] }}</div>
                </label>
            @endforeach
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label" for="preloader_exit_animation">Animação de Saída</label>
            <select name="preloader_exit_animation" id="preloader_exit_animation" class="form-select">
                <option value="blur" {{ $preloaderExitVal === 'blur' ? 'selected' : '' }}>Blur + Expansão Cinematográfica (Padrão)</option>
                <option value="fade" {{ $preloaderExitVal === 'fade' ? 'selected' : '' }}>Fade Out Suave</option>
                <option value="zoom" {{ $preloaderExitVal === 'zoom' ? 'selected' : '' }}>Zoom Out Rápido</option>
                <option value="slide-up" {{ $preloaderExitVal === 'slide-up' ? 'selected' : '' }}>Cortina Deslizando para Cima</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="preloader_duration_ms">Tempo Mínimo (ms)</label>
            <div class="input-group">
                <input type="number" name="preloader_duration_ms" id="preloader_duration_ms" class="form-control" min="150" max="3500" step="50" value="{{ $preloaderDurationVal }}">
                <span class="input-group-text">ms</span>
            </div>
            <small class="text-secondary">Padrão recomendado: 650 ms.</small>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="preloader_text">Texto / Legenda</label>
            <input type="text" name="preloader_text" id="preloader_text" class="form-control" maxlength="40" value="{{ $preloaderTextVal }}" placeholder="SOMOS UNN">
        </div>
        <div class="col-md-2">
            <label class="form-label" for="preloader_accent_color">Cor de Destaque</label>
            <div class="input-group">
                <input type="color" id="preloader_accent_color_picker" class="form-control form-control-color" value="{{ $preloaderAccentVal }}" style="max-width: 48px;">
                <input type="text" name="preloader_accent_color" id="preloader_accent_color" class="form-control" value="{{ $preloaderAccentVal }}" placeholder="#1F5EDB">
            </div>
        </div>
    </div>

    <div class="row g-4 align-items-stretch">
        <div class="col-lg-5">
            <div class="form-group mb-0 h-100 d-flex flex-column">
                <label class="form-label">Imagem / Logo do Preloader (SVG, PNG ou GIF)</label>
                <div class="upload-box flex-grow-1 d-flex flex-column align-items-center justify-content-center" data-remove-input="#remove_preloader_image"
                    data-existing-url="{{ $getUrl('preloader_image') }}">
                    <input type="file" name="preloader_image" id="admin_preloader_image_input" class="d-none" accept="image/*">
                    <input type="hidden" name="remove_preloader_image" id="remove_preloader_image" value="0">
                    <div class="upload-preview mb-2">
                        @if($url = $getUrl('preloader_image'))
                            <img src="{{ $url }}" class="img-fluid" style="max-height: 64px;" loading="lazy" decoding="async">
                        @else
                            <div class="text-muted p-4 border rounded bg-light">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                            </div>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i class="fas fa-upload me-1"></i> Selecionar</button>
                        <button type="button" class="btn btn-sm btn-outline-danger upload-remove {{ $getUrl('preloader_image') ? '' : 'd-none' }}"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Palco de Preview Embutido em Tempo Real (Aprovação antes de salvar) --}}
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0"><i class="fas fa-desktop me-1 text-primary"></i> Preview ao Vivo do Preloader (aprove antes de salvar)</label>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" id="btnStageLight">Claro</button>
                    <button type="button" class="btn btn-outline-secondary" id="btnStageDark">Escuro</button>
                    <button type="button" class="btn btn-outline-primary" id="btnStageSimulateExit"><i class="fas fa-wand-magic-sparkles me-1"></i>Simular Saída</button>
                </div>
            </div>
            <div id="preloaderLiveStage" class="preloader-live-stage unn-style-{{ $preloaderStyleVal }} unn-exit-{{ $preloaderExitVal }}"
                style="--unn-pre-accent: {{ $preloaderAccentVal }}; --unn-pre-accent-2: #29ABE2;">
                <div class="unn-preloader-glow"></div>
                <div class="unn-preloader-content">
                    <div class="unn-orbital-wrapper">
                        <div class="unn-ring-outer"></div>
                        <div class="unn-ring-reverse"></div>
                        <div class="unn-ring-pulse"></div>
                        <div class="unn-ring-pulse unn-ring-pulse-2"></div>
                        <div class="unn-logo-container">
                            <img id="preloaderStageLogo" src="{{ $getUrl('preloader_image') ?: asset('img/logo.svg') }}" alt="Preview" class="unn-logo-img"
                                onerror="this.onerror=null;this.src='{{ asset('img/logo.svg') }}';">
                        </div>
                    </div>
                    <div class="unn-brand-text-wrapper">
                        <span class="unn-brand-text" id="preloaderStageText">{{ $preloaderTextVal }}</span>
                        <span class="unn-dots"><span class="unn-dot"></span><span class="unn-dot"></span><span class="unn-dot"></span></span>
                    </div>
                    <div class="unn-progress-container {{ $preloaderShowProgressVal ? '' : 'd-none' }}" id="preloaderStageProgress">
                        <div class="unn-progress-bar"><div class="unn-progress-shimmer"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .preloader-live-stage {
        --unn-pre-accent: #1F5EDB;
        --unn-pre-accent-2: #29ABE2;
        position: relative;
        min-height: 270px;
        padding: 2rem 1.5rem;
        border-radius: 1rem;
        border: 1px solid var(--bs-border-color);
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        transition: opacity 0.45s ease, transform 0.45s ease, filter 0.45s ease, background-color 0.25s ease;
    }
    .preloader-live-stage.is-dark-stage {
        background: #090d16;
    }
    .preloader-live-stage.is-dark-stage .unn-logo-container {
        background: #0f172a;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(30, 41, 59, 0.85);
    }
    .preloader-live-stage.is-dark-stage .unn-progress-container {
        background: #1e293b;
    }
    .preloader-live-stage.unn-style-pulse .unn-ring-outer,
    .preloader-live-stage.unn-style-pulse .unn-ring-reverse { display: none; }
    .preloader-live-stage.unn-style-pulse .unn-ring-pulse,
    .preloader-live-stage.unn-style-pulse .unn-ring-pulse-2 {
        display: block; inset: -14px; border: 2.5px solid var(--unn-pre-accent);
    }
    .preloader-live-stage.unn-style-minimal .unn-preloader-glow,
    .preloader-live-stage.unn-style-minimal .unn-ring-reverse,
    .preloader-live-stage.unn-style-minimal .unn-ring-pulse { display: none; }
    .preloader-live-stage.unn-style-minimal .unn-orbital-wrapper { width: 86px; height: 86px; margin-bottom: 14px; }
    .preloader-live-stage.unn-style-minimal .unn-ring-outer { inset: -6px; filter: none; animation-duration: 0.9s; }
    .preloader-live-stage.unn-style-minimal .unn-logo-container { width: 62px; height: 62px; animation: none; box-shadow: none; }
    .preloader-live-stage.unn-style-glass .unn-preloader-content {
        padding: 1.5rem 2rem; border-radius: 1.25rem;
        background: rgba(255, 255, 255, 0.72); border: 1px solid rgba(148, 163, 184, 0.35);
        backdrop-filter: blur(14px);
    }
    .preloader-live-stage.is-dark-stage.unn-style-glass .unn-preloader-content {
        background: rgba(15, 23, 42, 0.75); border-color: rgba(148, 163, 184, 0.25);
    }
    .preloader-live-stage.unn-style-glass .unn-ring-reverse { display: none; }
    .preloader-live-stage.unn-style-executive .unn-ring-outer,
    .preloader-live-stage.unn-style-executive .unn-ring-reverse,
    .preloader-live-stage.unn-style-executive .unn-ring-pulse { display: none; }
    .preloader-live-stage.unn-style-executive .unn-logo-container { width: 84px; height: 84px; border-radius: 1.15rem; }
    .preloader-live-stage.unn-style-executive .unn-progress-container { display: block !important; width: 230px; height: 6px; }
    .preloader-live-stage.stage-exiting.unn-exit-blur { opacity: 0; transform: scale(1.06); filter: blur(8px); }
    .preloader-live-stage.stage-exiting.unn-exit-fade { opacity: 0; }
    .preloader-live-stage.stage-exiting.unn-exit-zoom { opacity: 0; transform: scale(0.86); }
    .preloader-live-stage.stage-exiting.unn-exit-slide-up { transform: translateY(-100%); }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Preview Dinâmico em Tempo Real de Cores e Hero
    function hexToRgba(hex, alpha) {
        const clean = String(hex || '#000000').replace('#', '');
        if (!/^[0-9a-fA-F]{6}$/.test(clean)) return 'rgba(0,0,0,' + alpha + ')';
        const r = parseInt(clean.slice(0, 2), 16);
        const g = parseInt(clean.slice(2, 4), 16);
        const b = parseInt(clean.slice(4, 6), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    function bindColorPair(pickerId, textId, onChange) {
        const picker = document.getElementById(pickerId);
        const text = document.getElementById(textId);
        if (!picker || !text) return;
        picker.addEventListener('input', function () {
            text.value = picker.value;
            if (onChange) onChange(picker.value);
        });
        text.addEventListener('input', function () {
            if (/^#[0-9a-fA-F]{6}$/.test(text.value)) {
                picker.value = text.value;
                if (onChange) onChange(text.value);
            }
        });
    }

    function updateLiveBrandPreview() {
        const title = document.getElementById('appearance_hero_title')?.value || 'Transforme sua carreira';
        const subtitle = document.getElementById('appearance_hero_subtitle')?.value || '';
        const gradHex = document.getElementById('appearance_bg_gradient_start')?.value || '#000000';
        const gradOpacity = Math.min(100, Math.max(0, parseInt(document.getElementById('appearance_bg_gradient_opacity')?.value || '85', 10) || 85)) / 100;
        const primary = document.getElementById('appearance_color_primary')?.value || '#1F5EDB';
        const secondary = document.getElementById('appearance_color_secondary')?.value || '#64748b';

        const hero = document.getElementById('liveHeroPreview');
        if (hero) {
            hero.style.background = 'linear-gradient(135deg, ' + hexToRgba(gradHex, gradOpacity) + ' 0%, ' + hexToRgba(primary, Math.max(0.45, gradOpacity * 0.85)) + ' 100%)';
        }
        const titleEl = document.getElementById('liveHeroTitle');
        const subEl = document.getElementById('liveHeroSubtitle');
        if (titleEl) titleEl.textContent = title;
        if (subEl) subEl.textContent = subtitle;

        const pBtn = document.getElementById('livePrimaryBtn');
        const pLink = document.getElementById('livePrimaryLink');
        const pBar = document.getElementById('livePrimaryBar');
        const pHex = document.getElementById('livePrimaryHexLabel');
        if (pBtn) { pBtn.style.background = primary; pBtn.style.borderColor = primary; }
        if (pLink) pLink.style.color = primary;
        if (pBar) pBar.style.background = primary;
        if (pHex) pHex.textContent = primary;

        const sBadge = document.getElementById('liveSecondaryBadge');
        const sBtn = document.getElementById('liveSecondaryBtn');
        if (sBadge) sBadge.style.background = secondary;
        if (sBtn) sBtn.style.borderColor = secondary;
    }

    bindColorPair('appearance_bg_gradient_start_picker', 'appearance_bg_gradient_start', updateLiveBrandPreview);
    bindColorPair('appearance_color_primary_picker', 'appearance_color_primary', updateLiveBrandPreview);
    bindColorPair('appearance_color_secondary_picker', 'appearance_color_secondary', updateLiveBrandPreview);

    ['appearance_hero_title', 'appearance_hero_subtitle', 'appearance_bg_gradient_opacity'].forEach(function (id) {
        document.getElementById(id)?.addEventListener('input', updateLiveBrandPreview);
    });
    updateLiveBrandPreview();

    // 2. Palco de Preview Embutido do Preloader em Tempo Real
    const stage = document.getElementById('preloaderLiveStage');
    const stageText = document.getElementById('preloaderStageText');
    const stageProgress = document.getElementById('preloaderStageProgress');
    const stageLogo = document.getElementById('preloaderStageLogo');

    function syncPreloaderStage() {
        if (!stage) return;
        const style = document.querySelector('input[name="preloader_style"]:checked')?.value || 'orbital';
        const exitAnim = document.getElementById('preloader_exit_animation')?.value || 'blur';
        const text = (document.getElementById('preloader_text')?.value || 'SOMOS UNN').trim() || 'SOMOS UNN';
        const accent = document.getElementById('preloader_accent_color')?.value || '#1F5EDB';
        const showProgress = Boolean(document.getElementById('preloader_show_progress')?.checked);
        const isDarkStage = stage.classList.contains('is-dark-stage');

        stage.className = 'preloader-live-stage unn-style-' + style + ' unn-exit-' + exitAnim + (isDarkStage ? ' is-dark-stage' : '');
        stage.style.setProperty('--unn-pre-accent', accent);
        if (stageText) stageText.textContent = text;
        if (stageProgress) stageProgress.classList.toggle('d-none', !showProgress);
    }

    const cards = document.querySelectorAll('#preloaderModelGrid .preloader-model-card');
    cards.forEach(function (card) {
        card.addEventListener('click', function () {
            cards.forEach(function (c) { c.classList.remove('is-selected'); });
            card.classList.add('is-selected');
            setTimeout(syncPreloaderStage, 0);
        });
    });

    bindColorPair('preloader_accent_color_picker', 'preloader_accent_color', syncPreloaderStage);
    document.getElementById('preloader_exit_animation')?.addEventListener('change', syncPreloaderStage);
    document.getElementById('preloader_text')?.addEventListener('input', syncPreloaderStage);
    document.getElementById('preloader_show_progress')?.addEventListener('change', syncPreloaderStage);

    document.getElementById('admin_preloader_image_input')?.addEventListener('change', function () {
        const f = this.files && this.files[0] ? this.files[0] : null;
        if (f && stageLogo) {
            stageLogo.src = URL.createObjectURL(f);
        }
    });

    document.getElementById('btnStageLight')?.addEventListener('click', function () {
        stage?.classList.remove('is-dark-stage');
        this.classList.add('active');
        document.getElementById('btnStageDark')?.classList.remove('active');
    });
    document.getElementById('btnStageDark')?.addEventListener('click', function () {
        stage?.classList.add('is-dark-stage');
        this.classList.add('active');
        document.getElementById('btnStageLight')?.classList.remove('active');
    });
    document.getElementById('btnStageSimulateExit')?.addEventListener('click', function () {
        if (!stage) return;
        syncPreloaderStage();
        stage.classList.add('stage-exiting');
        setTimeout(function () {
            stage.classList.remove('stage-exiting');
        }, 900);
    });

    const previewBtn = document.getElementById('btnPreviewPreloaderAdmin');
    if (previewBtn) {
        previewBtn.addEventListener('click', function () {
            if (typeof window.UNNPreviewPreloader !== 'function') {
                if (typeof toastr !== 'undefined') toastr.info('Salve e recarregue a página para visualizar o preloader.');
                return;
            }
            const selectedStyle = document.querySelector('input[name="preloader_style"]:checked')?.value || 'orbital';
            const exitAnimation = document.getElementById('preloader_exit_animation')?.value || 'blur';
            const duration = Math.max(850, parseInt(document.getElementById('preloader_duration_ms')?.value || '1200', 10) || 1200);
            const text = document.getElementById('preloader_text')?.value || 'SOMOS UNN';
            const accentColor = document.getElementById('preloader_accent_color')?.value || '#1F5EDB';
            const showProgress = Boolean(document.getElementById('preloader_show_progress')?.checked);

            window.UNNPreviewPreloader({
                style: selectedStyle,
                exitAnimation: exitAnimation,
                duration: duration,
                text: text,
                accentColor: accentColor,
                showProgress: showProgress
            });
        });
    }

    syncPreloaderStage();
});
</script>
@endpush
