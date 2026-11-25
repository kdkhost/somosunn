<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-paint-brush me-2"></i> Personalize as cores, fontes e estilo visual do seu site para combinar
        com sua marca.
    </div>

    <h5 class="text-primary mb-3"><i class="fas fa-home me-2"></i> Hero (Página Inicial)</h5>
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
            <small class="form-text text-muted">Controla o quão escuro fica o fundo para melhorar a leitura do
                texto.</small>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-palette me-2"></i> Cores e Tema</h5>
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

    <div class="row">
        <div class="col-md-5 col-xl-4">
            <div class="form-group">
                <label class="form-label">Imagem / Logo do Preloader (SVG, PNG ou GIF)</label>
                <div class="upload-box" data-remove-input="#remove_preloader_image"
                    data-existing-url="{{ $getUrl('preloader_image') }}">
                    <input type="file" name="preloader_image" class="d-none" accept="image/*">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('#preloaderModelGrid .preloader-model-card');
    cards.forEach(function (card) {
        card.addEventListener('click', function () {
            cards.forEach(function (c) { c.classList.remove('is-selected'); });
            card.classList.add('is-selected');
        });
    });

    const picker = document.getElementById('preloader_accent_color_picker');
    const hexInput = document.getElementById('preloader_accent_color');
    if (picker && hexInput) {
        picker.addEventListener('input', function () { hexInput.value = picker.value; });
        hexInput.addEventListener('input', function () {
            if (/^#[0-9a-fA-F]{6}$/.test(hexInput.value)) picker.value = hexInput.value;
        });
    }

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
});
</script>
@endpush
