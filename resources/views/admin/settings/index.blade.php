@extends('admin.layouts.app')

@section('page_title', 'Configurações do Sistema')

@push('styles')
    <style>
        /* Drag & Drop Visual Feedback */
        .upload-box {
            border: 2px dashed #ced4da;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            position: relative;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .upload-box:hover,
        .upload-box.dragover {
            border-color: var(--primary, #007bff);
            background-color: rgba(0, 123, 255, 0.05);
        }

        .upload-box.dragover {
            transform: scale(1.02);
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.1);
        }

        .upload-preview img {
            max-height: 100px;
            max-width: 100%;
            object-fit: contain;
            margin-bottom: 10px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .upload-icon {
            font-size: 2rem;
            color: #adb5bd;
            margin-bottom: 0.5rem;
        }

        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary, #007bff);
            font-weight: 600;
            border-top: 3px solid var(--primary, #007bff);
        }
    </style>
@endpush

@section('content')
@php
    use Illuminate\Support\Str;
    // Helper closure to resolve URL similar to layouts.app
    $getUrl = function ($key) use ($settings) {
        $val = $settings[$key] ?? null;
        if (!$val)
            return null;
        if (Str::startsWith($val, 'http'))
            return $val;
        return asset($val);
    };
@endphp

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="ajax-form">
    @csrf
    <div class="card card-primary card-outline card-outline-tabs">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-general" role="tab"><i
                            class="fas fa-cogs mr-1"></i> Geral</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-appearance" role="tab"><i
                            class="fas fa-paint-brush mr-1"></i> Aparência</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-images" role="tab"><i
                            class="fas fa-images mr-1"></i> Imagens</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-player" role="tab"><i
                            class="fas fa-play-circle mr-1"></i> Player</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-ads" role="tab"><i
                            class="fas fa-ad mr-1"></i> Anúncios</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-pwa" role="tab"><i
                            class="fas fa-mobile-alt mr-1"></i> PWA</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-gateway" role="tab"><i
                            class="fas fa-credit-card mr-1"></i> Gateway</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-smtp" role="tab"><i
                            class="fas fa-envelope mr-1"></i> SMTP</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-social" role="tab"><i
                            class="fas fa-share-alt mr-1"></i> Login Social</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-seo" role="tab"><i
                            class="fas fa-search mr-1"></i> SEO</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-system" role="tab"><i
                            class="fas fa-server mr-1"></i> Sistema</a></li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                {{-- GERAL --}}
                <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                    <h5 class="text-primary mb-3"><i class="fas fa-info-circle mr-2"></i>Informações Básicas</h5>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nome do site</label>
                            <input name="app_name" class="form-control"
                                value="{{ $settings['app_name'] ?? config('app.name') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Empresa</label>
                            <input name="company_name" class="form-control"
                                value="{{ $settings['company_name'] ?? '' }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Telefone</label>
                            <input name="company_phone" class="form-control mask-phone"
                                value="{{ $settings['company_phone'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>E-mail de Contato</label>
                            <input name="company_email" class="form-control"
                                value="{{ $settings['company_email'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>CEP</label>
                            <input id="company_zip" name="company_zip" class="form-control mask-cep"
                                value="{{ $settings['company_zip'] ?? '' }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Endereço</label>
                            <input name="company_address" class="form-control"
                                value="{{ $settings['company_address'] ?? '' }}">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Número</label>
                            <input id="company_number" name="company_number" class="form-control"
                                value="{{ $settings['company_number'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Complemento</label>
                            <input name="company_complement" class="form-control"
                                value="{{ $settings['company_complement'] ?? '' }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Bairro</label>
                            <input name="company_district" class="form-control"
                                value="{{ $settings['company_district'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Cidade</label>
                            <input name="company_city" class="form-control"
                                value="{{ $settings['company_city'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Estado</label>
                            <input name="company_state" class="form-control"
                                value="{{ $settings['company_state'] ?? '' }}">
                        </div>
                    </div>
                </div>

                {{-- APARÊNCIA --}}
                <div class="tab-pane fade" id="tab-appearance" role="tabpanel">
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
                                <input name="site_bg_gradient_opacity" type="number" min="0" max="100"
                                    class="form-control" value="{{ $settings['site_bg_gradient_opacity'] ?? 85 }}">
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
                    <h5 class="text-primary mb-3"><i class="fas fa-calendar-alt mr-2"></i>Eventos e Mentorias (Fundo
                        Hero)</h5>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Desfoque (Blur px)</label>
                            <input name="events_hero_bg_blur_px" type="number" class="form-control"
                                value="{{ $settings['events_hero_bg_blur_px'] ?? 64 }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Intensidade da Película (%)</label>
                            <input name="events_hero_film_strength_percent" type="number" min="0" max="100"
                                class="form-control"
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
                                <option value="dark" {{ ($settings['site_theme'] ?? '') === 'dark' ? 'selected' : '' }}>
                                    Dark (Escuro)</option>
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
                                <input type="checkbox" class="custom-control-input"
                                    id="testimonials_carousel_show_arrows" name="testimonials_carousel_show_arrows"
                                    value="1" {{ ($settings['testimonials_carousel_show_arrows'] ?? 1) ? 'checked' : '' }}>
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
                        <input type="checkbox" class="custom-control-input" id="preloader_enabled"
                            name="preloader_enabled" value="1" {{ ($settings['preloader_enabled'] ?? 0) ? 'checked' : '' }}>
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

                {{-- IMAGENS --}}
                <div class="tab-pane fade" id="tab-images" role="tabpanel">
                    <h5 class="text-primary mb-3"><i class="fas fa-images mr-2"></i>Logotipos e Ícones</h5>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Logo Principal</label>
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('logo_image') }}"
                                data-remove-input="[name='remove_logo_image']">
                                <input type="hidden" name="remove_logo_image" value="0">
                                <input type="file" name="logo_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Favicon</label>
                            <div class="upload-box" data-max-size="{{ 1 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('favicon_image') }}"
                                data-remove-input="[name='remove_favicon_image']">
                                <input type="hidden" name="remove_favicon_image" value="0">
                                <input type="file" name="favicon_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Logo (Painel Admin)</label>
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('logo_admin') }}"
                                data-remove-input="[name='remove_logo_admin']">
                                <input type="hidden" name="remove_logo_admin" value="0">
                                <input type="file" name="logo_admin" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Logo (Login/Auth)</label>
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('logo_auth') }}"
                                data-remove-input="[name='remove_logo_auth']">
                                <input type="hidden" name="remove_logo_auth" value="0">
                                <input type="file" name="logo_auth" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Logo (Frontend/Site)</label>
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('logo_front') }}"
                                data-remove-input="[name='remove_logo_front']">
                                <input type="hidden" name="remove_logo_front" value="0">
                                <input type="file" name="logo_front" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5 class="text-primary mb-3"><i class="fas fa-image mr-2"></i>Backgrounds</h5>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Hero Image (Frente)</label>
                            <div class="upload-box" data-max-size="{{ 10 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('hero_image') }}"
                                data-remove-input="[name='remove_hero_image']">
                                <input type="hidden" name="remove_hero_image" value="0">
                                <input type="file" name="hero_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Background do Site</label>
                            <div class="upload-box" data-max-size="{{ 10 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('site_bg_image') }}"
                                data-remove-input="[name='remove_site_bg_image']">
                                <input type="hidden" name="remove_site_bg_image" value="0">
                                <input type="file" name="site_bg_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- VÍDEO PLAYER (PLYR) --}}
                <div class="tab-pane fade" id="tab-player" role="tabpanel">
                    <h5 class="text-primary mb-3"><i class="fas fa-play-circle mr-2"></i>Player de Vídeo (Plyr)</h5>

                    <div class="alert alert-info">
                        Configure o player de vídeo usado nas aulas/cursos. As opções avançadas aceitam o JSON de
                        configuração do Plyr.
                    </div>

                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                        <input type="hidden" name="video_player_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="video_player_enabled"
                            name="video_player_enabled" value="1" {{ ($settings['video_player_enabled'] ?? 1) ? 'checked' : '' }}>
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
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                                <input type="hidden" name="video_plyr_autoplay" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_autoplay"
                                    name="video_plyr_autoplay" value="1" {{ ($settings['video_plyr_autoplay'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_autoplay">Autoplay</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                                <input type="hidden" name="video_plyr_muted" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_muted"
                                    name="video_plyr_muted" value="1" {{ ($settings['video_plyr_muted'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_muted">Iniciar mudo</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                                <input type="hidden" name="video_plyr_click_to_play" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_click_to_play"
                                    name="video_plyr_click_to_play" value="1" {{ ($settings['video_plyr_click_to_play'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_click_to_play">Clique p/
                                    tocar</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
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
                                                <input type="checkbox" class="custom-control-input plyr-control-checkbox" id="control_{{ $control }}" value="{{ $control }}" {{ in_array($control, $currentControls) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="control_{{ $control }}">{{ ucfirst($control) }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Menu Config (CSVs)</label>
                                <input name="video_plyr_settings" class="form-control"
                                    value="{{ $settings['video_plyr_settings'] ?? 'captions,quality,speed,loop' }}">
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
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                                <input type="hidden" name="video_watermark_enabled" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_watermark_enabled"
                                    name="video_watermark_enabled" value="1" {{ ($settings['video_watermark_enabled'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_watermark_enabled">Exibir imagem</label>
                            </div>

                            <div class="form-group">
                                <label>Imagem</label>
                                <div class="upload-box" data-max-size="{{ 1 * 1024 * 1024 }}"
                                    data-existing-url="{{ $getUrl('watermark_image') }}"
                                    data-remove-input="[name='remove_watermark_image']">
                                    <input type="hidden" name="remove_watermark_image" value="0">
                                    <input type="file" name="watermark_image" accept="image/*" class="d-none">
                                    <div class="upload-preview text-center text-muted"></div>
                                    <button type="button"
                                        class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                    <button type="button"
                                        class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                                <input type="hidden" name="video_watermark_text_enabled" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_watermark_text_enabled"
                                    name="video_watermark_text_enabled" value="1" {{ ($settings['video_watermark_text_enabled'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_watermark_text_enabled">Texto dinâmico
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



                {{-- ANÚNCIOS --}}
                <div class="tab-pane fade" id="tab-ads" role="tabpanel">
                    <h5 class="text-primary mb-3"><i class="fas fa-ad mr-2"></i>Monetização e Anúncios</h5>
                    <div class="alert alert-info">
                        Configure aqui os anúncios que aparecem na comunidade e entre lições.
                        <br><b>Global:</b> Exibido em todas as páginas (ex: rodapé ou lateral).
                        <br><b>Inter-feed:</b> Exibido entre postagens da comunidade (feed).
                    </div>

                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                        <input type="hidden" name="ads_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="ads_enabled" name="ads_enabled"
                            value="1" {{ ($settings['ads_enabled'] ?? 0) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="ads_enabled">Ativar Anúncios Globais</label>
                    </div>

                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Google AdSense</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Publisher ID (ca-pub-...)</label>
                                    <input name="adsense_publisher_id" class="form-control"
                                        value="{{ $settings['adsense_publisher_id'] ?? '' }}"
                                        placeholder="ca-pub-000000000000">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Slot ID</label>
                                    <input name="adsense_slot_id" class="form-control"
                                        value="{{ $settings['adsense_slot_id'] ?? '' }}" placeholder="1234567890">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Formato</label>
                                    <select name="adsense_format" class="form-control">
                                        @php($adsFormat = $settings['adsense_format'] ?? 'auto')
                                        <option value="auto" {{ $adsFormat === 'auto' ? 'selected' : '' }}>Automático
                                        </option>
                                        <option value="fluid" {{ $adsFormat === 'fluid' ? 'selected' : '' }}>In-feed
                                            (Fluido)</option>
                                        <option value="rectangle" {{ $adsFormat === 'rectangle' ? 'selected' : '' }}>
                                            Retângulo</option>
                                        <option value="horizontal" {{ $adsFormat === 'horizontal' ? 'selected' : '' }}>
                                            Horizontal</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Frequência (Inter-feed)</label>
                                    <select name="adsense_frequency" class="form-control">
                                        @php($adsFreq = (int) ($settings['adsense_frequency'] ?? 5))
                                        <option value="3" {{ $adsFreq === 3 ? 'selected' : '' }}>A cada 3 posts</option>
                                        <option value="5" {{ $adsFreq === 5 ? 'selected' : '' }}>A cada 5 posts</option>
                                        <option value="10" {{ $adsFreq === 10 ? 'selected' : '' }}>A cada 10 posts
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label>HTML/JS Personalizado (Global)</label>
                        <textarea name="ads_code_html" class="form-control" rows="4"
                            placeholder="Cole aqui o código de embed">{{ $settings['ads_code_html'] ?? '' }}</textarea>
                        <small class="text-muted">Se usar AdSense acima, este campo pode ficar vazio.</small>
                    </div>

                    <div
                        class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-3 mb-2">
                        <input type="hidden" name="ads_inter_feed_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="ads_inter_feed_enabled"
                            name="ads_inter_feed_enabled" value="1" {{ ($settings['ads_inter_feed_enabled'] ?? 0) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="ads_inter_feed_enabled">Exibir anúncios entre postagens
                            do feed</label>
                    </div>

                    <div class="form-group">
                        <label>HTML/JS Personalizado (Inter-feed)</label>
                        <textarea name="ads_inter_feed_code" class="form-control" rows="4"
                            placeholder="Código específico para o feed (opcional)">{{ $settings['ads_inter_feed_code'] ?? '' }}</textarea>
                    </div>
                </div>

            </div>

            {{-- PWA --}}
            <div class="tab-pane fade" id="tab-pwa" role="tabpanel">
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
                            <option value="fullscreen" {{ ($settings['pwa_display'] ?? '') === 'fullscreen' ? 'selected' : '' }}>Fullscreen (Tela Cheia)</option>
                            <option value="minimal-ui" {{ ($settings['pwa_display'] ?? '') === 'minimal-ui' ? 'selected' : '' }}>Minimal UI</option>
                            <option value="browser" {{ ($settings['pwa_display'] ?? '') === 'browser' ? 'selected' : '' }}>Browser</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- GATEWAY --}}
            <div class="tab-pane fade" id="tab-gateway" role="tabpanel">
                <h5 class="text-primary mb-3"><i class="fas fa-credit-card mr-2"></i>Gateways de Pagamento</h5>
                <p class="text-muted">Configure credenciais e opções dos gateways suportados. <b>Webhooks</b> são URLs
                    que você deve configurar no painel do gateway para receber atualizações de pagamento.</p>

                {{-- MERCADO PAGO --}}
                <div class="card card-outline card-success collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">MercadoPago</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="card-body" style="display: none;">
                        <div class="form-group">
                            <label>Ambiente</label>
                            <select name="mercadopago_env" class="form-control gateway-env-select" data-gateway="mercadopago">
                                <option value="sandbox" {{ ($settings['mercadopago_env'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                                <option value="production" {{ ($settings['mercadopago_env'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Produção</option>
                            </select>
                        </div>
                        <div class="env-sandbox">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Public Key (Sandbox)</label>
                                    <input name="mercadopago_sandbox_public_key" class="form-control"
                                        value="{{ $settings['mercadopago_sandbox_public_key'] ?? '' }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Access Token (Sandbox)</label>
                                    <input name="mercadopago_sandbox_access_token" class="form-control"
                                        value="{{ $settings['mercadopago_sandbox_access_token'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="env-production">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Public Key (Produção)</label>
                                    <input name="mercadopago_prod_public_key" class="form-control"
                                        value="{{ $settings['mercadopago_prod_public_key'] ?? '' }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Access Token (Produção)</label>
                                    <input name="mercadopago_prod_access_token" class="form-control"
                                        value="{{ $settings['mercadopago_prod_access_token'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Webhook URL (Copie e cole no painel do MP)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" readonly
                                    value="{{ route('api.webhooks.mercadopago') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-default" type="button"
                                        onclick="navigator.clipboard.writeText(this.parentElement.previousElementSibling.value); toastr.success('Copiado!')"><i
                                            class="fas fa-copy"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PAGSEGURO --}}
                <div class="card card-outline card-warning collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">PagSeguro</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                    class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="card-body" style="display: none;">
                        <div class="form-group">
                            <label>Ambiente</label>
                            <select name="pagseguro_env" class="form-control gateway-env-select" data-gateway="pagseguro">
                                <option value="sandbox" {{ ($settings['pagseguro_env'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                                <option value="production" {{ ($settings['pagseguro_env'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Produção</option>
                            </select>
                        </div>
                        <div class="env-sandbox">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Email</label>
                                    <input name="pagseguro_email" class="form-control"
                                        value="{{ $settings['pagseguro_email'] ?? '' }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Token (Sandbox)</label>
                                    <input name="pagseguro_sandbox_token" class="form-control"
                                        value="{{ $settings['pagseguro_sandbox_token'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="env-production">
                            <div class="row">
                                <div class="col-md-6 offset-md-6 form-group">
                                    <label>Token (Produção)</label>
                                    <input name="pagseguro_prod_token" class="form-control"
                                        value="{{ $settings['pagseguro_prod_token'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Webhook URL (Notificações)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" readonly
                                    value="{{ route('api.webhooks.pagseguro') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-default" type="button"
                                        onclick="navigator.clipboard.writeText(this.parentElement.previousElementSibling.value); toastr.success('Copiado!')"><i
                                            class="fas fa-copy"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <h5 class="text-primary mb-3"><i class="fas fa-sliders-h mr-2"></i>Opções Gerais de Pagamento</h5>
                <div class="row">
                    <div class="col-md-12">
                        <div
                            class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                            <input type="hidden" name="gateway_transparent_checkout" value="0">
                            <input type="checkbox" class="custom-control-input" id="gateway_transparent_checkout"
                                name="gateway_transparent_checkout" value="1" {{ ($settings['gateway_transparent_checkout'] ?? 0) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="gateway_transparent_checkout">Checkout Transparente
                                (Manter usuário no site)</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Juros de Parcelamento (% a.m.)</label>
                        <input type="number" step="0.01" name="gateway_installment_tax" class="form-control"
                            value="{{ $settings['gateway_installment_tax'] ?? '0.00' }}">
                        <small class="text-muted">Se 0, assume sem juros (ou config do gateway).</small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Max. Parcelas sem Juros</label>
                        <input type="number" name="gateway_max_installments_no_interest" class="form-control"
                            value="{{ $settings['gateway_max_installments_no_interest'] ?? '1' }}">
                    </div>
                    <div class="col-md-4">
                        <label>Repassar Taxas ao Cliente?</label>
                        <select name="gateway_pass_tax_to_client" class="form-control">
                            <option value="0" {{ ($settings['gateway_pass_tax_to_client'] ?? 0) == 0 ? 'selected' : '' }}>
                                Não (Absorver taxas)</option>
                            <option value="1" {{ ($settings['gateway_pass_tax_to_client'] ?? 0) == 1 ? 'selected' : '' }}>
                                Sim (Acrescer ao total)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- SMTP --}}
            <div class="tab-pane fade" id="tab-smtp" role="tabpanel">
                <h5 class="text-primary mb-3"><i class="fas fa-envelope-open mr-2"></i>Servidor de E-mail</h5>
                <div class="row">
                    <div class="col-md-8 form-group">
                        <label>Host SMTP</label>
                        <input name="smtp_host" class="form-control" value="{{ $settings['smtp_host'] ?? '' }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label>Porta</label>
                        <input name="smtp_port" class="form-control" value="{{ $settings['smtp_port'] ?? '587' }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label>Criptografia</label>
                        <select name="smtp_encryption" class="form-control">
                            <option value="tls" {{ ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS
                            </option>
                            <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL
                            </option>
                            <option value="null" {{ ($settings['smtp_encryption'] ?? '') === 'null' ? 'selected' : '' }}>
                                Nenhuma</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Usuário</label>
                        <input name="smtp_username" class="form-control" value="{{ $settings['smtp_username'] ?? '' }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Senha</label>
                        <input name="smtp_password" type="password" class="form-control"
                            value="{{ $settings['smtp_password'] ?? '' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>E-mail Remetente</label>
                        <input name="smtp_from_email" class="form-control"
                            value="{{ $settings['smtp_from_email'] ?? '' }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Nome Remetente</label>
                        <input name="smtp_from_name" class="form-control"
                            value="{{ $settings['smtp_from_name'] ?? config('app.name') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Cópia (CC)</label>
                        <input name="smtp_cc" class="form-control" value="{{ $settings['smtp_cc'] ?? '' }}"
                            placeholder="email@exemplo.com">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Cópia Oculta (BCC)</label>
                        <input name="smtp_bcc" class="form-control" value="{{ $settings['smtp_bcc'] ?? '' }}"
                            placeholder="auditoria@exemplo.com">
                    </div>
                </div>

                <hr>
                <div class="form-group">
                    <label>Testar envio para:</label>
                    <div class="input-group">
                        <input type="email" name="smtp_test_email" class="form-control" placeholder="seu@email.com">
                        <div class="input-group-append">
                            <button type="button" id="btnTestSmtp" class="btn btn-outline-primary"><i
                                    class="fas fa-paper-plane mr-1"></i> Testar Configuração</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- LOGIN SOCIAL --}}
            <div class="tab-pane fade" id="tab-social" role="tabpanel">
                <h5 class="text-primary mb-3"><i class="fas fa-users mr-2"></i>Login Social</h5>
                
                <div id="social-accordion">
                    {{-- Google --}}
                    <div class="card card-outline card-danger social-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <button type="button" class="btn btn-link py-0 text-danger" data-toggle="collapse" data-target="#collapseGoogle" aria-expanded="true" aria-controls="collapseGoogle">
                                    <i class="fab fa-google mr-1"></i> Google
                                </button>
                            </h3>
                        </div>
                        <div id="collapseGoogle" class="collapse show" data-parent="#social-accordion">
                            <div class="card-body">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="hidden" name="social_google_enabled" value="0">
                                    <input type="checkbox" class="custom-control-input" id="social_google_enabled" name="social_google_enabled" value="1" {{ ($settings['social_google_enabled'] ?? 0) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="social_google_enabled">Ativar Login com Google</label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group"><label>Client ID</label><input name="social_google_client_id" class="form-control" value="{{ $settings['social_google_client_id'] ?? '' }}"></div>
                                    <div class="col-md-6 form-group"><label>Client Secret</label><input name="social_google_client_secret" class="form-control" value="{{ $settings['social_google_client_secret'] ?? '' }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Facebook --}}
                    <div class="card card-outline card-primary social-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <button type="button" class="btn btn-link py-0" data-toggle="collapse" data-target="#collapseFacebook" aria-expanded="false" aria-controls="collapseFacebook">
                                    <i class="fab fa-facebook mr-1"></i> Facebook
                                </button>
                            </h3>
                        </div>
                        <div id="collapseFacebook" class="collapse" data-parent="#social-accordion">
                            <div class="card-body">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="hidden" name="social_facebook_enabled" value="0">
                                    <input type="checkbox" class="custom-control-input" id="social_facebook_enabled" name="social_facebook_enabled" value="1" {{ ($settings['social_facebook_enabled'] ?? 0) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="social_facebook_enabled">Ativar Login com Facebook</label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group"><label>App ID</label><input name="social_facebook_app_id" class="form-control" value="{{ $settings['social_facebook_app_id'] ?? '' }}"></div>
                                    <div class="col-md-6 form-group"><label>App Secret</label><input name="social_facebook_app_secret" class="form-control" value="{{ $settings['social_facebook_app_secret'] ?? '' }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Twitter / X --}}
                    <div class="card card-outline card-info social-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <button type="button" class="btn btn-link py-0 text-info" data-toggle="collapse" data-target="#collapseTwitter" aria-expanded="false" aria-controls="collapseTwitter">
                                    <i class="fab fa-twitter mr-1"></i> Twitter / X
                                </button>
                            </h3>
                        </div>
                        <div id="collapseTwitter" class="collapse" data-parent="#social-accordion">
                            <div class="card-body">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="hidden" name="social_twitter_enabled" value="0">
                                    <input type="checkbox" class="custom-control-input" id="social_twitter_enabled" name="social_twitter_enabled" value="1" {{ ($settings['social_twitter_enabled'] ?? 0) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="social_twitter_enabled">Ativar Login com Twitter / X</label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group"><label>Client ID (API Key)</label><input name="social_twitter_client_id" class="form-control" value="{{ $settings['social_twitter_client_id'] ?? '' }}"></div>
                                    <div class="col-md-6 form-group"><label>Client Secret (API Secret)</label><input name="social_twitter_client_secret" class="form-control" value="{{ $settings['social_twitter_client_secret'] ?? '' }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LinkedIn --}}
                    <div class="card card-outline card-dark social-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <button type="button" class="btn btn-link py-0 text-dark" data-toggle="collapse" data-target="#collapseLinkedin" aria-expanded="false" aria-controls="collapseLinkedin">
                                    <i class="fab fa-linkedin mr-1"></i> LinkedIn
                                </button>
                            </h3>
                        </div>
                        <div id="collapseLinkedin" class="collapse" data-parent="#social-accordion">
                            <div class="card-body">
                                <div class="custom-control custom-switch mb-3">
                                    <input type="hidden" name="social_linkedin_enabled" value="0">
                                    <input type="checkbox" class="custom-control-input" id="social_linkedin_enabled" name="social_linkedin_enabled" value="1" {{ ($settings['social_linkedin_enabled'] ?? 0) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="social_linkedin_enabled">Ativar Login com LinkedIn</label>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group"><label>Client ID</label><input name="social_linkedin_client_id" class="form-control" value="{{ $settings['social_linkedin_client_id'] ?? '' }}"></div>
                                    <div class="col-md-6 form-group"><label>Client Secret</label><input name="social_linkedin_client_secret" class="form-control" value="{{ $settings['social_linkedin_client_secret'] ?? '' }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                <h5 class="text-primary mb-3"><i class="fas fa-search-plus mr-2"></i>SEO & Analytics</h5>

                <div class="form-group">
                    <label>Descrição do Site (Meta Description)</label>
                    <textarea name="site_description" class="form-control" rows="3"
                        placeholder="Descrição curta do seu site para o Google...">{{ $settings['site_description'] ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label>Palavras-Chave (Keywords)</label>
                    <input name="site_keywords" class="form-control" value="{{ $settings['site_keywords'] ?? '' }}"
                        placeholder="curso, mentoria, comunidade, networking">
                    <small class="text-muted">Separe por vírgulas.</small>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Imagem OpenGraph (Facebook/WhatsApp)</label>
                        <div class="upload-box" data-max-size="{{ 2 * 1024 * 1024 }}"
                            data-existing-url="{{ $getUrl('seo_og_image') }}"
                            data-remove-input="[name='remove_seo_og_image']">
                            <input type="hidden" name="remove_seo_og_image" value="0">
                            <input type="file" name="seo_og_image" accept="image/*" class="d-none">
                            <div class="upload-preview text-center text-muted"></div>
                            <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                            <button type="button"
                                class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                        </div>
                        <small class="text-muted">Recomendado: 1200x630px</small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Imagem Twitter Card</label>
                        <div class="upload-box" data-max-size="{{ 2 * 1024 * 1024 }}"
                            data-existing-url="{{ $getUrl('seo_twitter_image') }}"
                            data-remove-input="[name='remove_seo_twitter_image']">
                            <input type="hidden" name="remove_seo_twitter_image" value="0">
                            <input type="file" name="seo_twitter_image" accept="image/*" class="d-none">
                            <div class="upload-preview text-center text-muted"></div>
                            <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                            <button type="button"
                                class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                        </div>
                        <small class="text-muted">Recomendado: 1200x600px</small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Scripts Header (GTM, GA4, Pixel)</label>
                    <textarea name="tracking_head" class="form-control" rows="4"
                        placeholder="<script>...</script>">{{ $settings['tracking_head'] ?? '' }}</textarea>
                </div>
                <div class="form-group">
                    <label>Scripts Body (Noscript, Chat)</label>
                    <textarea name="tracking_body" class="form-control" rows="4"
                        placeholder="<script>...</script>">{{ $settings['tracking_body'] ?? '' }}</textarea>
                </div>
            </div>

            {{-- SISTEMA --}}
            <div class="tab-pane fade" id="tab-system" role="tabpanel">
                <h5 class="text-primary mb-3"><i class="fas fa-shield-alt mr-2"></i>Segurança (reCAPTCHA v3)</h5>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Site Key</label>
                        <input name="recaptcha_v3_site_key" class="form-control"
                            value="{{ $settings['recaptcha_v3_site_key'] ?? config('services.recaptcha.site_key') }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Secret Key</label>
                        <input name="recaptcha_v3_secret_key" type="password" class="form-control"
                            value="{{ $settings['recaptcha_v3_secret_key'] ?? config('services.recaptcha.v3_secret') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Score Mínimo (0.0 a 1.0)</label>
                        <input name="recaptcha_v3_min_score" class="form-control"
                            value="{{ $settings['recaptcha_v3_min_score'] ?? config('services.recaptcha.v3_min_score', 0.5) }}">
                    </div>
                </div>
                <hr>

                <h5 class="text-primary mb-3"><i class="fas fa-server mr-2"></i>Limites e Armazenamento</h5>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Limite de Upload de Vídeo (MB)</label>
                        <input type="number" name="video_max_mb" class="form-control"
                            value="{{ $settings['video_max_mb'] ?? '1024' }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Limite de Upload de Arquivos (MB)</label>
                        <input type="number" name="document_max_mb" class="form-control"
                            value="{{ $settings['document_max_mb'] ?? '50' }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Formatos de Vídeo Permitidos</label>
                        <input name="allowed_video_formats" class="form-control"
                            value="{{ $settings['allowed_video_formats'] ?? implode(',', config('uploads.allowed_video_formats', [])) }}"
                            placeholder="mp4,webm,mkv">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Formatos de Documento Permitidos</label>
                        <input name="allowed_document_formats" class="form-control"
                            value="{{ $settings['allowed_document_formats'] ?? implode(',', config('uploads.allowed_document_formats', [])) }}"
                            placeholder="pdf,docx,pptx">
                    </div>
                </div>

                <hr>
                <h6 class="font-weight-bold">Armazenamento (S3 / Local)</h6>
                <div class="form-group">
                    <label>Disco de Uploads</label>
                    <select name="uploads_storage_disk" class="form-control">
                        <option value="public" {{ ($settings['uploads_storage_disk'] ?? 'public') === 'public' ? 'selected' : '' }}>Local (Public)</option>
                        <option value="s3" {{ ($settings['uploads_storage_disk'] ?? '') === 's3' ? 'selected' : '' }}>
                            Amazon S3 / Compatível</option>
                    </select>
                </div>

                <div class="card card-body bg-light">
                    <div class="row">
                        <div class="col-md-6 form-group"><label>S3 Key</label><input name="s3_key" class="form-control"
                                value="{{ $settings['s3_key'] ?? '' }}"></div>
                        <div class="col-md-6 form-group"><label>S3 Secret</label><input name="s3_secret"
                                class="form-control" value="{{ $settings['s3_secret'] ?? '' }}"></div>
                        <div class="col-md-4 form-group"><label>S3 Bucket</label><input name="s3_bucket"
                                class="form-control" value="{{ $settings['s3_bucket'] ?? '' }}"></div>
                        <div class="col-md-4 form-group"><label>S3 Region</label><input name="s3_region"
                                class="form-control" value="{{ $settings['s3_region'] ?? '' }}"></div>
                        <div class="col-md-4 form-group"><label>S3 Endpoint</label><input name="s3_endpoint"
                                class="form-control" value="{{ $settings['s3_endpoint'] ?? '' }}"></div>
                        <div class="col-md-4 form-group"><label>S3 Public URL (CDN)</label><input name="s3_url"
                                class="form-control" value="{{ $settings['s3_url'] ?? '' }}"></div>
                        <div class="col-md-12">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="s3_path_style" value="0">
                                <input type="checkbox" class="custom-control-input" id="s3_path_style"
                                    name="s3_path_style" value="1" {{ ($settings['s3_path_style'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="s3_path_style">Usar Endpoint Path-Style
                                    (MinIO/Compatíveis)</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="card-footer text-right">
        <button type="submit" class="btn btn-lg btn-success"><i class="fas fa-save mr-1"></i> Salvar
            Configurações</button>
    </div>
    </div>
</form>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gateway Environment Toggle
            $('.gateway-env-select').change(function() {
                var env = $(this).val();
                var cardBody = $(this).closest('.card-body');
                if(env === 'sandbox') {
                    cardBody.find('.env-sandbox').show();
                    cardBody.find('.env-production').hide();
                } else {
                    cardBody.find('.env-sandbox').hide();
                    cardBody.find('.env-production').show();
                }
            }).trigger('change');

            // Player Controls Sync
            $('.plyr-control-checkbox').change(function() {
                var selected = [];
                $('.plyr-control-checkbox:checked').each(function() {
                    selected.push($(this).val());
                });
                $('[name="video_plyr_controls"]').val(selected.join(','));
            });

            // Test SMTP
            $('#btnTestSmtp').click(function () {
                var btn = $(this);
                var originalText = btn.html();
                var email = $('[name="smtp_test_email"]').val();

                if (!email) {
                    toastr.warning('Digite um e-mail para teste');
                    return;
                }

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testando...');

                // Gather all SMTP fields
                var data = {
                    _token: '{{ csrf_token() }}',
                    smtp_host: $('[name="smtp_host"]').val(),
                    smtp_port: $('[name="smtp_port"]').val(),
                    smtp_encryption: $('[name="smtp_encryption"]').val(),
                    smtp_username: $('[name="smtp_username"]').val(),
                    smtp_password: $('[name="smtp_password"]').val(),
                    smtp_from_email: $('[name="smtp_from_email"]').val(),
                    smtp_from_name: $('[name="smtp_from_name"]').val(),
                    smtp_test_email: email
                };

                $.post('{{ route("admin.settings.test-smtp") }}', data)
                    .done(function (res) {
                        toastr.success(res.message);
                    })
                    .fail(function (xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao enviar e-mail';
                        toastr.error(msg);
                    })
                    .always(function () {
                        btn.prop('disabled', false).html(originalText);
                    });
            });
        });
    </script>
@endpush