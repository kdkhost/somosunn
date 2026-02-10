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

    .upload-box:hover, .upload-box.dragover {
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
    $getUrl = function($key) use ($settings) {
        $val = $settings[$key] ?? null;
        if(!$val) return null;
        if(Str::startsWith($val, 'http')) return $val;
        return asset($val);
    };
@endphp

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="ajax-form">
    @csrf
    <div class="card card-primary card-outline card-outline-tabs">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab-general" role="tab"><i class="fas fa-cogs mr-1"></i> Geral</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-appearance" role="tab"><i class="fas fa-paint-brush mr-1"></i> Aparência</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-images" role="tab"><i class="fas fa-images mr-1"></i> Imagens</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-player" role="tab"><i class="fas fa-play-circle mr-1"></i> Player</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-ads" role="tab"><i class="fas fa-ad mr-1"></i> Anúncios</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-pwa" role="tab"><i class="fas fa-mobile-alt mr-1"></i> PWA</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-gateway" role="tab"><i class="fas fa-credit-card mr-1"></i> Gateway</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-smtp" role="tab"><i class="fas fa-envelope mr-1"></i> SMTP</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-social" role="tab"><i class="fas fa-share-alt mr-1"></i> Login Social</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-seo" role="tab"><i class="fas fa-search mr-1"></i> SEO</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-system" role="tab"><i class="fas fa-server mr-1"></i> Sistema</a></li>
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
                            <input name="app_name" class="form-control" value="{{ $settings['app_name'] ?? config('app.name') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Empresa</label>
                            <input name="company_name" class="form-control" value="{{ $settings['company_name'] ?? '' }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Telefone</label>
                            <input name="company_phone" class="form-control mask-phone" value="{{ $settings['company_phone'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>E-mail de Contato</label>
                            <input name="company_email" class="form-control" value="{{ $settings['company_email'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>CEP</label>
                            <input id="company_zip" name="company_zip" class="form-control mask-cep" value="{{ $settings['company_zip'] ?? '' }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Endereço</label>
                            <input name="company_address" class="form-control" value="{{ $settings['company_address'] ?? '' }}">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Número</label>
                            <input id="company_number" name="company_number" class="form-control" value="{{ $settings['company_number'] ?? '' }}">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Cidade</label>
                            <input name="company_city" class="form-control" value="{{ $settings['company_city'] ?? '' }}">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Estado</label>
                            <input name="company_state" class="form-control" value="{{ $settings['company_state'] ?? '' }}">
                        </div>
                    </div>
                </div>

                {{-- APARÊNCIA --}}
                <div class="tab-pane fade" id="tab-appearance" role="tabpanel">
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
                                <input name="site_color_primary" class="form-control" value="{{ $settings['site_color_primary'] ?? '#007bff' }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-square" style="color: {{ $settings['site_color_primary'] ?? '#007bff' }}"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Cor Secundária</label>
                            <div class="input-group colorpicker-element">
                                <input name="site_color_secondary" class="form-control" value="{{ $settings['site_color_secondary'] ?? '#6c757d' }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-square" style="color: {{ $settings['site_color_secondary'] ?? '#6c757d' }}"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                         <div class="col-md-6 form-group">
                            <label>Font Family</label>
                            <input name="site_font_family" class="form-control" value="{{ $settings['site_font_family'] ?? 'Inter, sans-serif' }}">
                            <small class="text-muted">Ex: 'Inter', sans-serif</small>
                        </div>
                    </div>

                    <hr>
                    <h5 class="text-primary mb-3"><i class="fas fa-window-maximize mr-2"></i>Rodapé</h5>
                    <div class="form-group">
                        <label>Texto do Rodapé</label>
                        <textarea name="footer_text" class="form-control" rows="3">{{ $settings['footer_text'] ?? '' }}</textarea>
                    </div>
                    
                     <hr>
                     <h5 class="text-primary mb-3"><i class="fas fa-spinner mr-2"></i>Preloader</h5>
                     <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                        <input type="hidden" name="preloader_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="preloader_enabled" name="preloader_enabled" value="1" {{ ($settings['preloader_enabled'] ?? 0) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="preloader_enabled">Ativar Preloader</label>
                    </div>
                    <div class="form-group" style="max-width: 300px;">
                        <label>Imagem do Preloader</label>
                         <div class="upload-box" data-max-size="{{ 1 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('preloader_image') }}" data-remove-input="[name='remove_preloader_image']">
                            <input type="hidden" name="remove_preloader_image" value="0">
                            <input type="file" name="preloader_image" accept="image/*" class="d-none">
                            <div class="upload-preview text-center text-muted"></div>
                            <div class="upload-help text-muted small mt-2">GIF, SVG ou PNG</div>
                            <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar arquivo</button>
                             <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                        </div>
                    </div>
                </div>

                {{-- IMAGENS --}}
                <div class="tab-pane fade" id="tab-images" role="tabpanel">
                    <h5 class="text-primary mb-3"><i class="fas fa-images mr-2"></i>Logotipos e Ícones</h5>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Logo Principal</label>
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('logo_image') }}" data-remove-input="[name='remove_logo_image']">
                                <input type="hidden" name="remove_logo_image" value="0">
                                <input type="file" name="logo_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Favicon</label>
                            <div class="upload-box" data-max-size="{{ 1 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('favicon_image') }}" data-remove-input="[name='remove_favicon_image']">
                                <input type="hidden" name="remove_favicon_image" value="0">
                                <input type="file" name="favicon_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                         <div class="col-md-3 form-group">
                            <label>Logo (Painel Admin)</label>
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('logo_admin') }}" data-remove-input="[name='remove_logo_admin']">
                                <input type="hidden" name="remove_logo_admin" value="0">
                                <input type="file" name="logo_admin" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Logo (Login/Auth)</label>
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('logo_auth') }}" data-remove-input="[name='remove_logo_auth']">
                                <input type="hidden" name="remove_logo_auth" value="0">
                                <input type="file" name="logo_auth" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    <h5 class="text-primary mb-3"><i class="fas fa-image mr-2"></i>Backgrounds</h5>
                    <div class="row">
                         <div class="col-md-6 form-group">
                            <label>Hero Image (Frente)</label>
                             <div class="upload-box" data-max-size="{{ 10 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('hero_image') }}" data-remove-input="[name='remove_hero_image']">
                                <input type="hidden" name="remove_hero_image" value="0">
                                <input type="file" name="hero_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                         <div class="col-md-6 form-group">
                            <label>Background do Site</label>
                             <div class="upload-box" data-max-size="{{ 10 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('site_bg_image') }}" data-remove-input="[name='remove_site_bg_image']">
                                <input type="hidden" name="remove_site_bg_image" value="0">
                                <input type="file" name="site_bg_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PLAYER --}}
                <div class="tab-pane fade" id="tab-player" role="tabpanel">
                    <h5 class="text-primary mb-3"><i class="fas fa-play-circle mr-2"></i>Configurações do Player (Plyr)</h5>
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                        <input type="hidden" name="video_player_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="video_player_enabled"
                            name="video_player_enabled" value="1" {{ ($settings['video_player_enabled'] ?? 1) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="video_player_enabled">Ativar Plyr no site</label>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Cor do Player</label>
                             <div class="input-group colorpicker-element">
                                <input name="video_plyr_color" class="form-control" value="{{ $settings['video_plyr_color'] ?? ($settings['site_color_primary'] ?? '#007bff') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-square" style="color: {{ $settings['video_plyr_color'] ?? '#007bff' }}"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Volume Inicial (0.0 a 1.0)</label>
                            <input name="video_plyr_volume" class="form-control" value="{{ $settings['video_plyr_volume'] ?? '0.8' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Tempo de Avanço (seg)</label>
                            <input name="video_plyr_seek_time" class="form-control" value="{{ $settings['video_plyr_seek_time'] ?? '10' }}">
                        </div>
                    </div>

                    <div class="row">
                         <div class="col-md-3">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                                <input type="hidden" name="video_plyr_autoplay" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_autoplay"
                                    name="video_plyr_autoplay" value="1" {{ ($settings['video_plyr_autoplay'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_autoplay">Autoplay</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                                <input type="hidden" name="video_plyr_muted" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_muted"
                                    name="video_plyr_muted" value="1" {{ ($settings['video_plyr_muted'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_muted">Mudo Inicial</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                             <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                                <input type="hidden" name="video_plyr_disable_context_menu" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_disable_context_menu"
                                    name="video_plyr_disable_context_menu" value="1" {{ ($settings['video_plyr_disable_context_menu'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_disable_context_menu">Bloquear Menu</label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5 class="text-primary mb-3"><i class="fas fa-closed-captioning mr-2"></i>Marca d'água no Vídeo</h5>
                     <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                                <input type="hidden" name="video_watermark_enabled" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_watermark_enabled"
                                    name="video_watermark_enabled" value="1" {{ ($settings['video_watermark_enabled'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_watermark_enabled">Exibir Marca d'água</label>
                            </div>
                             <div class="form-group">
                                <label>Imagem da Marca d'água</label>
                                 <div class="upload-box" data-max-size="{{ 1 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('watermark_image') }}" data-remove-input="[name='remove_watermark_image']">
                                    <input type="hidden" name="remove_watermark_image" value="0">
                                    <input type="file" name="watermark_image" accept="image/*" class="d-none">
                                    <div class="upload-preview text-center text-muted"></div>
                                    <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                    <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label>Posição</label>
                                <select name="video_watermark_position" class="form-control">
                                    <option value="top-right" {{ ($settings['video_watermark_position'] ?? 'top-right') === 'top-right' ? 'selected' : '' }}>Topo Direito</option>
                                    <option value="top-left" {{ ($settings['video_watermark_position'] ?? '') === 'top-left' ? 'selected' : '' }}>Topo Esquerdo</option>
                                    <option value="bottom-right" {{ ($settings['video_watermark_position'] ?? '') === 'bottom-right' ? 'selected' : '' }}>Inferior Direito</option>
                                    <option value="bottom-left" {{ ($settings['video_watermark_position'] ?? '') === 'bottom-left' ? 'selected' : '' }}>Inferior Esquerdo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Opacidade (0.1 a 1.0)</label>
                                <input name="video_watermark_opacity" class="form-control" value="{{ $settings['video_watermark_opacity'] ?? '0.5' }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ANÚNCIOS --}}
                <div class="tab-pane fade" id="tab-ads" role="tabpanel">
                    <h5 class="text-primary mb-3"><i class="fas fa-ad mr-2"></i>Monetização e Anúncios</h5>
                    <div class="alert alert-info">
                        Configure aqui os anúncios que aparecem na comunidade e entre lições.
                    </div>
                    
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                        <input type="hidden" name="ads_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="ads_enabled" name="ads_enabled"
                            value="1" {{ ($settings['ads_enabled'] ?? 0) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="ads_enabled">Ativar Anúncios Globais</label>
                    </div>

                    <div class="card card-outline card-warning">
                        <div class="card-header"><h3 class="card-title">Google AdSense</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Publisher ID (ca-pub-...)</label>
                                    <input name="adsense_publisher_id" class="form-control" value="{{ $settings['adsense_publisher_id'] ?? '' }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Slot ID</label>
                                    <input name="adsense_slot_id" class="form-control" value="{{ $settings['adsense_slot_id'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label>HTML/JS Personalizado (Outros Ads)</label>
                        <textarea name="ads_code_html" class="form-control" rows="5" placeholder="Cole aqui o código de embed">{{ $settings['ads_code_html'] ?? '' }}</textarea>
                    </div>
                </div>

                {{-- PWA --}}
                <div class="tab-pane fade" id="tab-pwa" role="tabpanel">
                    <h5 class="text-primary mb-3"><i class="fas fa-mobile-alt mr-2"></i>Progressive Web App</h5>
                     <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                        <input type="hidden" name="pwa_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="pwa_enabled" name="pwa_enabled"
                            value="1" {{ ($settings['pwa_enabled'] ?? 0) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="pwa_enabled">Ativar PWA</label>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nome do App</label>
                            <input name="pwa_name" class="form-control" value="{{ $settings['pwa_name'] ?? config('app.name') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Nome Curto (Short Name)</label>
                            <input name="pwa_short_name" class="form-control" value="{{ $settings['pwa_short_name'] ?? config('app.name') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Ícone 192px</label>
                             <div class="upload-box" data-max-size="{{ 1 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('pwa_icon_192') }}" data-remove-input="[name='remove_pwa_icon_192']">
                                <input type="hidden" name="remove_pwa_icon_192" value="0">
                                <input type="file" name="pwa_icon_192" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                         <div class="col-md-4 form-group">
                            <label>Ícone 512px</label>
                             <div class="upload-box" data-max-size="{{ 1 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('pwa_icon_512') }}" data-remove-input="[name='remove_pwa_icon_512']">
                                <input type="hidden" name="remove_pwa_icon_512" value="0">
                                <input type="file" name="pwa_icon_512" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                         <div class="col-md-4 form-group">
                            <label>Splash Screen</label>
                             <div class="upload-box" data-max-size="{{ 2 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('pwa_splash') }}" data-remove-input="[name='remove_pwa_splash']">
                                <input type="hidden" name="remove_pwa_splash" value="0">
                                <input type="file" name="pwa_splash" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GATEWAY --}}
                <div class="tab-pane fade" id="tab-gateway" role="tabpanel">
                     <h5 class="text-primary mb-3"><i class="fas fa-money-bill-wave mr-2"></i>Pagamentos</h5>
                     <div class="form-group">
                        <label>MercadoPago Access Token</label>
                        <input name="mercadopago_access_token" class="form-control" value="{{ $settings['mercadopago_access_token'] ?? '' }}">
                     </div>
                     <div class="form-group">
                        <label>PagSeguro Token</label>
                        <input name="pagseguro_token" class="form-control" value="{{ $settings['pagseguro_token'] ?? '' }}">
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
                                <option value="tls" {{ ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="null" {{ ($settings['smtp_encryption'] ?? '') === 'null' ? 'selected' : '' }}>Nenhuma</option>
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
                            <input name="smtp_password" type="password" class="form-control" value="{{ $settings['smtp_password'] ?? '' }}">
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 form-group">
                            <label>E-mail Remetente</label>
                            <input name="smtp_from_email" class="form-control" value="{{ $settings['smtp_from_email'] ?? '' }}">
                        </div>
                         <div class="col-md-6 form-group">
                            <label>Nome Remetente</label>
                            <input name="smtp_from_name" class="form-control" value="{{ $settings['smtp_from_name'] ?? config('app.name') }}">
                        </div>
                    </div>
                    
                    <hr>
                    <div class="form-group">
                        <label>Testar envio para:</label>
                        <div class="input-group">
                            <input type="email" name="smtp_test_email" class="form-control" placeholder="seu@email.com">
                            <div class="input-group-append">
                                <button type="button" id="btnTestSmtp" class="btn btn-outline-primary"><i class="fas fa-paper-plane mr-1"></i> Testar Configuração</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- LOGIN SOCIAL --}}
                <div class="tab-pane fade" id="tab-social" role="tabpanel">
                      <h5 class="text-primary mb-3"><i class="fas fa-users mr-2"></i>Social Login</h5>
                      <div class="row">
                          <div class="col-md-6">
                              <label>Google Client ID</label>
                              <input name="social_google_client_id" class="form-control" value="{{ $settings['social_google_client_id'] ?? '' }}">
                          </div>
                          <div class="col-md-6">
                              <label>Google Client Secret</label>
                              <input name="social_google_client_secret" class="form-control" value="{{ $settings['social_google_client_secret'] ?? '' }}">
                          </div>
                      </div>
                      <div class="row mt-3">
                          <div class="col-md-6">
                              <label>Facebook App ID</label>
                              <input name="social_facebook_app_id" class="form-control" value="{{ $settings['social_facebook_app_id'] ?? '' }}">
                          </div>
                          <div class="col-md-6">
                              <label>Facebook App Secret</label>
                              <input name="social_facebook_app_secret" class="form-control" value="{{ $settings['social_facebook_app_secret'] ?? '' }}">
                          </div>
                      </div>
                </div>

                {{-- SEO --}}
                <div class="tab-pane fade" id="tab-seo" role="tabpanel">
                    <h5 class="text-primary mb-3"><i class="fas fa-search-plus mr-2"></i>SEO & Analytics</h5>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Imagem OpenGraph (Facebook/WhatsApp)</label>
                             <div class="upload-box" data-max-size="{{ 2 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('seo_og_image') }}" data-remove-input="[name='remove_seo_og_image']">
                                <input type="hidden" name="remove_seo_og_image" value="0">
                                <input type="file" name="seo_og_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                            <small class="text-muted">Recomendado: 1200x630px</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Imagem Twitter Card</label>
                             <div class="upload-box" data-max-size="{{ 2 * 1024 * 1024 }}" data-existing-url="{{ $getUrl('seo_twitter_image') }}" data-remove-input="[name='remove_seo_twitter_image']">
                                <input type="hidden" name="remove_seo_twitter_image" value="0">
                                <input type="file" name="seo_twitter_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn mt-2">Selecionar</button>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                            <small class="text-muted">Recomendado: 1200x600px</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Scripts Header (GTM, GA4, Pixel)</label>
                        <textarea name="tracking_head" class="form-control" rows="4" placeholder="<script>...</script>">{{ $settings['tracking_head'] ?? '' }}</textarea>
                    </div>
                     <div class="form-group">
                        <label>Scripts Body (Noscript, Chat)</label>
                        <textarea name="tracking_body" class="form-control" rows="4" placeholder="<script>...</script>">{{ $settings['tracking_body'] ?? '' }}</textarea>
                    </div>
                </div>

                {{-- SISTEMA --}}
                <div class="tab-pane fade" id="tab-system" role="tabpanel">
                     <h5 class="text-primary mb-3"><i class="fas fa-server mr-2"></i>Limites e Armazenamento</h5>
                     <div class="row">
                         <div class="col-md-6 form-group">
                             <label>Limite de Upload de Vídeo (MB)</label>
                             <input type="number" name="video_max_mb" class="form-control" value="{{ $settings['video_max_mb'] ?? '1024' }}">
                         </div>
                         <div class="col-md-6 form-group">
                             <label>Limite de Upload de Arquivos (MB)</label>
                             <input type="number" name="document_max_mb" class="form-control" value="{{ $settings['document_max_mb'] ?? '50' }}">
                         </div>
                     </div>
                     
                     <hr>
                     <h6 class="font-weight-bold">Armazenamento (S3 / Local)</h6>
                     <div class="form-group">
                         <label>Disco de Uploads</label>
                         <select name="uploads_storage_disk" class="form-control">
                             <option value="public" {{ ($settings['uploads_storage_disk'] ?? 'public') === 'public' ? 'selected' : '' }}>Local (Public)</option>
                             <option value="s3" {{ ($settings['uploads_storage_disk'] ?? '') === 's3' ? 'selected' : '' }}>Amazon S3 / Compatível</option>
                         </select>
                     </div>
                     
                     <div class="card card-body bg-light">
                         <div class="row">
                             <div class="col-md-6 form-group"><label>S3 Key</label><input name="s3_key" class="form-control" value="{{ $settings['s3_key'] ?? '' }}"></div>
                             <div class="col-md-6 form-group"><label>S3 Secret</label><input name="s3_secret" class="form-control" value="{{ $settings['s3_secret'] ?? '' }}"></div>
                             <div class="col-md-4 form-group"><label>S3 Bucket</label><input name="s3_bucket" class="form-control" value="{{ $settings['s3_bucket'] ?? '' }}"></div>
                             <div class="col-md-4 form-group"><label>S3 Region</label><input name="s3_region" class="form-control" value="{{ $settings['s3_region'] ?? '' }}"></div>
                             <div class="col-md-4 form-group"><label>S3 Endpoint</label><input name="s3_endpoint" class="form-control" value="{{ $settings['s3_endpoint'] ?? '' }}"></div>
                         </div>
                     </div>
                </div>

            </div>
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-lg btn-success"><i class="fas fa-save mr-1"></i> Salvar Configurações</button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Test SMTP
        $('#btnTestSmtp').click(function() {
            var btn = $(this);
            var originalText = btn.html();
            var email = $('[name="smtp_test_email"]').val();
            
            if(!email) {
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
             .done(function(res) {
                 toastr.success(res.message);
             })
             .fail(function(xhr) {
                 var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao enviar e-mail';
                 toastr.error(msg);
             })
             .always(function() {
                 btn.prop('disabled', false).html(originalText);
             });
        });
    });
</script>
@endpush