@extends('admin.layouts.app')
@php
    use Illuminate\Support\Str;
    $getUrl = function($key) use ($settings){
        $value = $settings[$key] ?? '';
        if (!$value) {
            return '';
        }
        if (Str::startsWith($value, ['http://','https://'])) {
            return $value;
        }
        $value = ltrim($value, '/');
        if (Str::startsWith($value, ['tmp/','/tmp'])) {
            return '';
        }
        if (file_exists(public_path($value))) {
            return asset($value);
        }
        return '';
    };
    $pwa192   = $getUrl('pwa_icon_192');
    $pwa512   = $getUrl('pwa_icon_512');
    $pwaSplash= $getUrl('pwa_splash');
    $pwaBanner= $getUrl('pwa_banner');
@endphp

@section('page_title','Configurações')
@section('breadcrumb')<li class="breadcrumb-item active">Configurações</li>@endsection

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card card-primary card-outline">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab-geral" role="tab">Geral</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-appearance" role="tab">Aparência</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-pwa" role="tab">PWA</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-gateway" role="tab">Gateway</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-preloader" role="tab">Preloader</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-smtp" role="tab">SMTP</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-social" role="tab">Login Social</a></li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">

                {{-- GERAL --}}
                <div class="tab-pane fade show active" id="tab-geral" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nome do site</label>
                                <input name="app_name" class="form-control" value="{{ $settings['app_name'] ?? config('app.name') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tema do site</label>
                                <select name="site_theme" class="form-control">
                                    <option value="light" {{ ($settings['site_theme'] ?? 'light')==='light'?'selected':'' }}>Light</option>
                                    <option value="dark" {{ ($settings['site_theme'] ?? '')==='dark'?'selected':'' }}>Dark</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Nome da empresa</label><input name="company_name" class="form-control" value="{{ $settings['company_name'] ?? '' }}"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Telefone</label><input name="company_phone" class="form-control mask-phone" value="{{ $settings['company_phone'] ?? '' }}"></div></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>E-mail</label><input name="company_email" class="form-control" value="{{ $settings['company_email'] ?? '' }}"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>CEP</label><input id="company_zip" name="company_zip" class="form-control mask-cep" data-target-number="#company_number" data-target-complement="#company_complement" data-target-district="#company_district" value="{{ $settings['company_zip'] ?? '' }}"></div></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Endereço</label><input name="company_address" class="form-control" value="{{ $settings['company_address'] ?? '' }}"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Número</label><input id="company_number" name="company_number" class="form-control" value="{{ $settings['company_number'] ?? '' }}"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Complemento</label><input id="company_complement" name="company_complement" class="form-control" value="{{ $settings['company_complement'] ?? '' }}"></div></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Bairro</label><input id="company_district" name="company_district" class="form-control" value="{{ $settings['company_district'] ?? '' }}"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Cidade</label><input name="company_city" class="form-control" value="{{ $settings['company_city'] ?? '' }}"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Estado</label><input name="company_state" class="form-control" value="{{ $settings['company_state'] ?? '' }}"></div></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Logo (principal)</label>
                            <input type="hidden" name="remove_logo_image" value="0">
                            <div class="upload-box" data-max-size="{{ 5*1024*1024 }}" data-existing-url="{{ $getUrl('logo_image') }}" data-remove-input="[name='remove_logo_image']">
                                <input type="file" name="logo_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar arquivo</button>
                                <div class="progress upload-progress d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Favicon</label>
                            <input type="hidden" name="remove_favicon_image" value="0">
                            <div class="upload-box" data-max-size="{{ 2*1024*1024 }}" data-existing-url="{{ $getUrl('favicon_image') }}" data-remove-input="[name='remove_favicon_image']">
                                <input type="file" name="favicon_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar arquivo</button>
                                <div class="progress upload-progress d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label>Logo painel administrativo</label>
                            <input type="hidden" name="remove_logo_admin" value="0">
                            <div class="upload-box" data-max-size="{{ 5*1024*1024 }}" data-existing-url="{{ $getUrl('logo_admin') }}" data-remove-input="[name='remove_logo_admin']">
                                <input type="file" name="logo_admin" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar arquivo</button>
                                <div class="progress upload-progress d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Logo páginas de login/registro</label>
                            <input type="hidden" name="remove_logo_auth" value="0">
                            <div class="upload-box" data-max-size="{{ 5*1024*1024 }}" data-existing-url="{{ $getUrl('logo_auth') }}" data-remove-input="[name='remove_logo_auth']">
                                <input type="file" name="logo_auth" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar arquivo</button>
                                <div class="progress upload-progress d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Logo front-end/site</label>
                            <input type="hidden" name="remove_logo_front" value="0">
                            <div class="upload-box" data-max-size="{{ 5*1024*1024 }}" data-existing-url="{{ $getUrl('logo_front') }}" data-remove-input="[name='remove_logo_front']">
                                <input type="file" name="logo_front" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar arquivo</button>
                                <div class="progress upload-progress d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label>Marca d'água (vídeos de cursos)</label>
                            <input type="hidden" name="remove_watermark_image" value="0">
                            <div class="upload-box" data-max-size="{{ 5*1024*1024 }}" data-existing-url="{{ $getUrl('watermark_image') }}" data-remove-input="[name='remove_watermark_image']">
                                <input type="file" name="watermark_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar arquivo</button>
                                <div class="progress upload-progress d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- APARÊNCIA (NOVO) --}}
                <div class="tab-pane fade" id="tab-appearance" role="tabpanel">
                    <h5 class="text-primary mb-3"><i class="fas fa-home mr-2"></i>Hero (Página Inicial)</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Título Principal</label>
                                <input name="hero_title" class="form-control" value="{{ $settings['hero_title'] ?? 'Transforme sua carreira' }}">
                            </div>
                            <div class="form-group">
                                <label>Subtítulo</label>
                                <textarea name="hero_subtitle" class="form-control" rows="3">{{ $settings['hero_subtitle'] ?? 'Junte-se a milhares de membros e aprenda com os melhores.' }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Imagem de Fundo (Hero)</label>
                            <input type="hidden" name="remove_hero_image" value="0">
                            @php $heroUrl = $getUrl('hero_image'); @endphp
                            <div class="upload-box" data-max-size="{{ 5*1024*1024 }}" data-existing-url="{{ $heroUrl }}" data-remove-input="[name='remove_hero_image']">
                                <input type="file" name="hero_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small">Recomendado: 1920x1080px</div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar arquivo</button>
                                <div class="progress upload-progress d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="text-primary mb-3"><i class="fas fa-palette mr-2"></i>Cores e Identidade</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cor Primária (Botões, Destaques)</label>
                                <div class="input-group colorpicker-element">
                                    <input name="site_color_primary" class="form-control" value="{{ $settings['site_color_primary'] ?? '#007bff' }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-square" style="color: {{ $settings['site_color_primary'] ?? '#007bff' }}"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cor Secundária (Backgrounds, Detalhes)</label>
                                <div class="input-group colorpicker-element">
                                    <input name="site_color_secondary" class="form-control" value="{{ $settings['site_color_secondary'] ?? '#6c757d' }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-square" style="color: {{ $settings['site_color_secondary'] ?? '#6c757d' }}"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="text-primary mb-3"><i class="fas fa-shoe-prints mr-2"></i>Rodapé</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Texto do Rodapé (Sobre)</label>
                                <textarea name="footer_text" class="form-control" rows="3">{{ $settings['footer_text'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label><i class="fab fa-instagram mr-1"></i>Instagram URL</label><input name="social_instagram" class="form-control" value="{{ $settings['social_instagram'] ?? '' }}"></div></div>
                        <div class="col-md-3"><div class="form-group"><label><i class="fab fa-facebook mr-1"></i>Facebook URL</label><input name="social_facebook" class="form-control" value="{{ $settings['social_facebook'] ?? '' }}"></div></div>
                        <div class="col-md-3"><div class="form-group"><label><i class="fab fa-youtube mr-1"></i>Youtube URL</label><input name="social_youtube" class="form-control" value="{{ $settings['social_youtube'] ?? '' }}"></div></div>
                        <div class="col-md-3"><div class="form-group"><label><i class="fab fa-linkedin mr-1"></i>LinkedIn URL</label><input name="social_linkedin" class="form-control" value="{{ $settings['social_linkedin'] ?? '' }}"></div></div>
                    </div>
                </div>

                {{-- PWA --}}
                <div class="tab-pane fade" id="tab-pwa" role="tabpanel">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                        <input type="hidden" name="pwa_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="pwa_enabled" name="pwa_enabled" value="1" {{ ($settings['pwa_enabled'] ?? 0) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="pwa_enabled">PWA habilitado</label>
                    </div>
                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Nome</label><input name="pwa_name" class="form-control" value="{{ $settings['pwa_name'] ?? '' }}"></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Nome curto</label><input name="pwa_short_name" class="form-control" value="{{ $settings['pwa_short_name'] ?? '' }}"></div></div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6"><label>Theme color</label><input name="pwa_theme_color" class="form-control colorpicker-element" value="{{ $settings['pwa_theme_color'] ?? '#0C6BF7' }}"></div>
                        <div class="form-group col-md-6"><label>Background color</label><input name="pwa_background_color" class="form-control colorpicker-element" value="{{ $settings['pwa_background_color'] ?? '#FFFFFF' }}"></div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Icon 192x192</label>
                            <input type="hidden" name="remove_pwa_icon_192" value="0">
                            <div class="upload-box" data-max-size="{{ 2*1024*1024 }}" data-existing-url="{{ $pwa192 }}" data-remove-input="[name='remove_pwa_icon_192']">
                                <input type="file" name="pwa_icon_192" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar arquivo</button>
                                <div class="progress upload-progress d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Icon 512x512</label>
                            <input type="hidden" name="remove_pwa_icon_512" value="0">
                            <div class="upload-box" data-max-size="{{ 3*1024*1024 }}" data-existing-url="{{ $pwa512 }}" data-remove-input="[name='remove_pwa_icon_512']">
                                <input type="file" name="pwa_icon_512" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar arquivo</button>
                                <div class="progress upload-progress d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Splash (full-screen)</label>
                            <input type="hidden" name="remove_pwa_splash" value="0">
                            <div class="upload-box" data-max-size="{{ 5*1024*1024 }}" data-existing-url="{{ $pwaSplash }}" data-remove-input="[name='remove_pwa_splash']">
                                <input type="file" name="pwa_splash" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar arquivo</button>
                                <div class="progress upload-progress d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Banner</label>
                            <input type="hidden" name="remove_pwa_banner" value="0">
                            <div class="upload-box" data-max-size="{{ 5*1024*1024 }}" data-existing-url="{{ $pwaBanner }}" data-remove-input="[name='remove_pwa_banner']">
                                <input type="file" name="pwa_banner" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar arquivo</button>
                                <div class="progress upload-progress d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GATEWAY --}}
                <div class="tab-pane fade" id="tab-gateway" role="tabpanel">
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-mp-tab" data-toggle="pill" href="#pills-mp" role="tab">MercadoPago</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-ps-tab" data-toggle="pill" href="#pills-ps" role="tab">PagSeguro</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        {{-- MERCADO PAGO --}}
                        <div class="tab-pane fade show active" id="pills-mp" role="tabpanel">
                            <h5 class="text-primary"><i class="fas fa-credit-card mr-2"></i>Configurações MercadoPago</h5>
                            
                            <div class="form-group mt-3">
                                <label>Ambiente</label>
                                <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                                    <label class="btn btn-outline-success {{ ($settings['payments_mercadopago_env'] ?? 'sandbox') == 'sandbox' ? 'active' : '' }}">
                                        <input type="radio" name="payments_mercadopago_env" class="gateway-env-toggle" data-gateway="mercadopago" value="sandbox" {{ ($settings['payments_mercadopago_env'] ?? 'sandbox') == 'sandbox' ? 'checked' : '' }}> Sandbox (Testes)
                                    </label>
                                    <label class="btn btn-outline-danger {{ ($settings['payments_mercadopago_env'] ?? '') == 'production' ? 'active' : '' }}">
                                        <input type="radio" name="payments_mercadopago_env" class="gateway-env-toggle" data-gateway="mercadopago" value="production" {{ ($settings['payments_mercadopago_env'] ?? '') == 'production' ? 'checked' : '' }}> Produção
                                    </label>
                                </div>
                            </div>

                            {{-- Sandbox Fields --}}
                            <div class="card card-outline card-success env-section env-mercadopago-sandbox {{ ($settings['payments_mercadopago_env'] ?? 'sandbox') == 'sandbox' ? '' : 'd-none' }}">
                                <div class="card-header"><h3 class="card-title">Credenciais de Teste (Sandbox)</h3></div>
                                <div class="card-body">
                                    <div class="form-group"><label>Public Key (Teste)</label><input name="payments_mercadopago_sandbox_public_key" class="form-control" value="{{ $settings['payments_mercadopago_sandbox_public_key'] ?? '' }}"></div>
                                    <div class="form-group"><label>Access Token (Teste)</label><input name="payments_mercadopago_sandbox_access_token" class="form-control" value="{{ $settings['payments_mercadopago_sandbox_access_token'] ?? '' }}"></div>
                                    <small class="text-muted"><i class="fas fa-info-circle"></i> Use estas credenciais para simular pagamentos sem cobrança real.</small>
                                </div>
                            </div>

                            {{-- Production Fields --}}
                            <div class="card card-outline card-danger env-section env-mercadopago-production {{ ($settings['payments_mercadopago_env'] ?? '') == 'production' ? '' : 'd-none' }}">
                                <div class="card-header"><h3 class="card-title">Credenciais de Produção</h3></div>
                                <div class="card-body">
                                    <div class="form-group"><label>Public Key (Produção)</label><input name="payments_mercadopago_production_public_key" class="form-control" value="{{ $settings['payments_mercadopago_production_public_key'] ?? '' }}"></div>
                                    <div class="form-group"><label>Access Token (Produção)</label><input name="payments_mercadopago_production_access_token" class="form-control" value="{{ $settings['payments_mercadopago_production_access_token'] ?? '' }}"></div>
                                    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Cuidado! Alterações aqui afetam pagamentos reais.</div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">Taxas e Parcelamento</div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>Máximo de Parcelas (Geral)</label>
                                                <select name="payments_mercadopago_max_installments" class="form-control">
                                                    @foreach(range(1, 12) as $i)
                                                        <option value="{{ $i }}" {{ ($settings['payments_mercadopago_max_installments'] ?? 12) == $i ? 'selected' : '' }}>{{ $i }}x</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Parcelas para Comunidade (Associação)</label>
                                                <select name="payments_mercadopago_community_installments" class="form-control">
                                                    @foreach(range(1, 12) as $i)
                                                        <option value="{{ $i }}" {{ ($settings['payments_mercadopago_community_installments'] ?? 1) == $i ? 'selected' : '' }}>{{ $i }}x</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">Defina em quantas vezes a anuidade da comunidade pode ser dividida.</small>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6"><div class="form-group"><label>Taxa Gateway (%)</label><input name="payments_mercadopago_fee_percentage" class="form-control mask-money" value="{{ $settings['payments_mercadopago_fee_percentage'] ?? '' }}"></div></div>
                                                <div class="col-md-6"><div class="form-group"><label>Taxa Fixa (R$)</label><input name="payments_mercadopago_fee_fixed" class="form-control mask-money" value="{{ $settings['payments_mercadopago_fee_fixed'] ?? '' }}"></div></div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch">
                                                    <input type="hidden" name="payments_mercadopago_pass_fee" value="0">
                                                    <input type="checkbox" class="custom-control-input" id="mp_pass_fee" name="payments_mercadopago_pass_fee" value="1" {{ ($settings['payments_mercadopago_pass_fee'] ?? 0) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="mp_pass_fee">Repassar custo da taxa ao cliente (Você recebe o valor cheio)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">Métodos de Pagamento</div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="hidden" name="payments_mercadopago_enable_credit" value="0">
                                                    <input type="checkbox" class="custom-control-input" id="mp_credit" name="payments_mercadopago_enable_credit" value="1" {{ ($settings['payments_mercadopago_enable_credit'] ?? 1) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="mp_credit"><i class="fas fa-credit-card mr-2"></i>Cartão de Crédito</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="hidden" name="payments_mercadopago_enable_debit" value="0">
                                                    <input type="checkbox" class="custom-control-input" id="mp_debit" name="payments_mercadopago_enable_debit" value="1" {{ ($settings['payments_mercadopago_enable_debit'] ?? 0) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="mp_debit"><i class="far fa-credit-card mr-2"></i>Cartão de Débito</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="hidden" name="payments_mercadopago_enable_pix" value="0">
                                                    <input type="checkbox" class="custom-control-input" id="mp_pix" name="payments_mercadopago_enable_pix" value="1" {{ ($settings['payments_mercadopago_enable_pix'] ?? 1) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="mp_pix"><i class="fab fa-pix mr-2"></i>PIX (Aprovação Imediata)</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="hidden" name="payments_mercadopago_enable_boleto" value="0">
                                                    <input type="checkbox" class="custom-control-input" id="mp_boleto" name="payments_mercadopago_enable_boleto" value="1" {{ ($settings['payments_mercadopago_enable_boleto'] ?? 1) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="mp_boleto"><i class="fas fa-barcode mr-2"></i>Boleto Bancário</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PAGSEGURO --}}
                        <div class="tab-pane fade" id="pills-ps" role="tabpanel">
                            <h5 class="text-primary"><i class="fas fa-money-bill-wave mr-2"></i>Configurações PagSeguro</h5>

                            <div class="form-group mt-3">
                                <label>Ambiente</label>
                                <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                                    <label class="btn btn-outline-success {{ ($settings['payments_pagseguro_env'] ?? 'sandbox') == 'sandbox' ? 'active' : '' }}">
                                        <input type="radio" name="payments_pagseguro_env" class="gateway-env-toggle" data-gateway="pagseguro" value="sandbox" {{ ($settings['payments_pagseguro_env'] ?? 'sandbox') == 'sandbox' ? 'checked' : '' }}> Sandbox (Testes)
                                    </label>
                                    <label class="btn btn-outline-danger {{ ($settings['payments_pagseguro_env'] ?? '') == 'production' ? 'active' : '' }}">
                                        <input type="radio" name="payments_pagseguro_env" class="gateway-env-toggle" data-gateway="pagseguro" value="production" {{ ($settings['payments_pagseguro_env'] ?? '') == 'production' ? 'checked' : '' }}> Produção
                                    </label>
                                </div>
                            </div>

                            {{-- Sandbox Fields --}}
                            <div class="card card-outline card-success env-section env-pagseguro-sandbox {{ ($settings['payments_pagseguro_env'] ?? 'sandbox') == 'sandbox' ? '' : 'd-none' }}">
                                <div class="card-header"><h3 class="card-title">Credenciais de Teste (Sandbox)</h3></div>
                                <div class="card-body">
                                    <div class="form-group"><label>E-mail (Teste)</label><input name="payments_pagseguro_sandbox_email" class="form-control" value="{{ $settings['payments_pagseguro_sandbox_email'] ?? '' }}"></div>
                                    <div class="form-group"><label>Token (Teste)</label><input name="payments_pagseguro_sandbox_token" class="form-control" value="{{ $settings['payments_pagseguro_sandbox_token'] ?? '' }}"></div>
                                </div>
                            </div>

                            {{-- Production Fields --}}
                            <div class="card card-outline card-danger env-section env-pagseguro-production {{ ($settings['payments_pagseguro_env'] ?? '') == 'production' ? '' : 'd-none' }}">
                                <div class="card-header"><h3 class="card-title">Credenciais de Produção</h3></div>
                                <div class="card-body">
                                    <div class="form-group"><label>E-mail (Produção)</label><input name="payments_pagseguro_production_email" class="form-control" value="{{ $settings['payments_pagseguro_production_email'] ?? '' }}"></div>
                                    <div class="form-group"><label>Token (Produção)</label><input name="payments_pagseguro_production_token" class="form-control" value="{{ $settings['payments_pagseguro_production_token'] ?? '' }}"></div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">Taxas e Parcelamento</div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>Máximo de Parcelas (Geral)</label>
                                                <select name="payments_pagseguro_max_installments" class="form-control">
                                                    @foreach(range(1, 12) as $i)
                                                        <option value="{{ $i }}" {{ ($settings['payments_pagseguro_max_installments'] ?? 12) == $i ? 'selected' : '' }}>{{ $i }}x</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Parcelas para Comunidade (Associação)</label>
                                                <select name="payments_pagseguro_community_installments" class="form-control">
                                                    @foreach(range(1, 12) as $i)
                                                        <option value="{{ $i }}" {{ ($settings['payments_pagseguro_community_installments'] ?? 1) == $i ? 'selected' : '' }}>{{ $i }}x</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6"><div class="form-group"><label>Taxa Gateway (%)</label><input name="payments_pagseguro_fee_percentage" class="form-control mask-money" value="{{ $settings['payments_pagseguro_fee_percentage'] ?? '' }}"></div></div>
                                                <div class="col-md-6"><div class="form-group"><label>Taxa Fixa (R$)</label><input name="payments_pagseguro_fee_fixed" class="form-control mask-money" value="{{ $settings['payments_pagseguro_fee_fixed'] ?? '' }}"></div></div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch">
                                                    <input type="hidden" name="payments_pagseguro_pass_fee" value="0">
                                                    <input type="checkbox" class="custom-control-input" id="ps_pass_fee" name="payments_pagseguro_pass_fee" value="1" {{ ($settings['payments_pagseguro_pass_fee'] ?? 0) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="ps_pass_fee">Repassar custo da taxa ao cliente</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">Métodos de Pagamento</div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="hidden" name="payments_pagseguro_enable_credit" value="0">
                                                    <input type="checkbox" class="custom-control-input" id="ps_credit" name="payments_pagseguro_enable_credit" value="1" {{ ($settings['payments_pagseguro_enable_credit'] ?? 1) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="ps_credit">Cartão de Crédito</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="hidden" name="payments_pagseguro_enable_debit" value="0">
                                                    <input type="checkbox" class="custom-control-input" id="ps_debit" name="payments_pagseguro_enable_debit" value="1" {{ ($settings['payments_pagseguro_enable_debit'] ?? 0) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="ps_debit">Cartão de Débito</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="hidden" name="payments_pagseguro_enable_pix" value="0">
                                                    <input type="checkbox" class="custom-control-input" id="ps_pix" name="payments_pagseguro_enable_pix" value="1" {{ ($settings['payments_pagseguro_enable_pix'] ?? 1) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="ps_pix">PIX</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                                    <input type="hidden" name="payments_pagseguro_enable_boleto" value="0">
                                                    <input type="checkbox" class="custom-control-input" id="ps_boleto" name="payments_pagseguro_enable_boleto" value="1" {{ ($settings['payments_pagseguro_enable_boleto'] ?? 1) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="ps_boleto">Boleto Bancário</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PRELOADER --}}
                <div class="tab-pane fade" id="tab-preloader" role="tabpanel">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                        <input type="hidden" name="preloader_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="preloader_enabled" name="preloader_enabled" value="1" {{ ($settings['preloader_enabled'] ?? 1) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="preloader_enabled">Preloader habilitado</label>
                    </div>
                    <div class="form-group">
                        <label>Imagem do preloader</label>
                        <input type="hidden" name="remove_preloader_image" value="0">
                        <div class="upload-box" data-max-size="{{ 5*1024*1024 }}" data-existing-url="{{ $getUrl('preloader_image') }}" data-remove-input="[name='remove_preloader_image']">
                            <input type="file" name="preloader_image" accept="image/*" class="d-none">
                            <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                            <div class="upload-help text-muted small"></div>
                            <div class="upload-meta text-muted small"></div>
                            <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar arquivo</button>
                            <div class="progress upload-progress d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                            <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                        </div>
                    </div>
                </div>

                {{-- SMTP --}}
                <div class="tab-pane fade" id="tab-smtp" role="tabpanel">
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Host SMTP</label><input name="smtp_host" class="form-control" value="{{ $settings['smtp_host'] ?? '' }}"></div>
                        <div class="form-group col-md-2"><label>Porta</label><input name="smtp_port" class="form-control" value="{{ $settings['smtp_port'] ?? '' }}"></div>
                        <div class="form-group col-md-4"><label>Criptografia</label><select name="smtp_encryption" class="form-control"><option value="tls" {{ ($settings['smtp_encryption'] ?? '')==='tls'?'selected':'' }}>TLS</option><option value="ssl" {{ ($settings['smtp_encryption'] ?? '')==='ssl'?'selected':'' }}>SSL</option><option value="" {{ empty($settings['smtp_encryption'] ?? '')?'selected':'' }}>Nenhuma</option></select></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Usuário</label><input name="smtp_username" class="form-control" value="{{ $settings['smtp_username'] ?? '' }}"></div>
                        <div class="form-group col-md-6"><label>Senha</label><input name="smtp_password" type="password" class="form-control" value="{{ $settings['smtp_password'] ?? '' }}"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>From nome</label><input name="smtp_from_name" class="form-control" value="{{ $settings['smtp_from_name'] ?? '' }}"></div>
                        <div class="form-group col-md-6"><label>From e-mail</label><input name="smtp_from_email" class="form-control" value="{{ $settings['smtp_from_email'] ?? '' }}"></div>
                    </div>
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-4">
                            <label>Enviar teste para</label>
                            <input name="smtp_test_email" class="form-control" value="">
                        </div>
                        <div class="form-group col-md-6 d-flex align-items-end gap-2">
                            <button type="button" class="btn btn-secondary mr-2" id="btnTestSmtp"><i class="fas fa-paper-plane"></i> Enviar teste</button>
                            
                            @php
                                $smtpTemplate = \App\Models\MailTemplate::where('slug', 'smtp_test')->first();
                            @endphp
                            @if($smtpTemplate)
                                <a href="{{ route('admin.mailtemplates.edit', $smtpTemplate->id) }}" class="btn btn-outline-info"><i class="fas fa-edit"></i> Editar Template de Teste</a>
                            @else
                                <span class="text-muted text-sm">(Salve uma vez para criar o template)</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- LOGIN SOCIAL --}}
                <div class="tab-pane fade" id="tab-social" role="tabpanel">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i> URLs de Callback (Adicione no App):
                        <br>Google: <code>{{ config('app.url').'/auth/callback/google' }}</code>
                        <br>Facebook: <code>{{ config('app.url').'/auth/callback/facebook' }}</code>
                        <br>LinkedIn: <code>{{ config('app.url').'/auth/callback/linkedin' }}</code>
                    </div>

                    {{-- Google --}}
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h3 class="card-title text-danger"><i class="fab fa-google mr-2"></i>Google</h3>
                            <div class="card-tools">
                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                    <input type="hidden" name="social_google_active" value="0">
                                    <input type="checkbox" class="custom-control-input" id="social_google_active" name="social_google_active" value="1" {{ ($settings['social_google_active'] ?? 0) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="social_google_active">Ativo</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light border-danger">
                                <h5><i class="icon fas fa-info-circle text-danger"></i> Configuração Google</h5>
                                <ol class="pl-3 mb-0 text-muted small">
                                    <li>Acesse o <a href="https://console.cloud.google.com/" target="_blank" class="text-danger">Google Cloud Console</a>.</li>
                                    <li>Crie um projeto e vá em <strong>APIs & Services > Credentials</strong>.</li>
                                    <li>Crie uma credencial <strong>OAuth Client ID</strong> (Web Application).</li>
                                    <li>Em <strong>Authorized redirect URIs</strong>, adicione: <code class="user-select-all bg-white p-1 rounded border">{{ url('/auth/callback/google') }}</code></li>
                                    <li>Copie o <strong>Client ID</strong> e <strong>Client Secret</strong> abaixo.</li>
                                </ol>
                            </div>
                            <div class="form-group">
                                <label>Client ID</label>
                                <input name="social_google_client_id" class="form-control" value="{{ $settings['social_google_client_id'] ?? '' }}" placeholder="ex: 123456789-abc...apps.googleusercontent.com">
                            </div>
                            <div class="form-group">
                                <label>Client Secret</label>
                                <input name="social_google_client_secret" class="form-control" value="{{ $settings['social_google_client_secret'] ?? '' }}" placeholder="ex: GOCSPX-...">
                            </div>
                        </div>
                    </div>

                    {{-- Facebook --}}
                    <div class="card card-outline card-primary collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title text-primary"><i class="fab fa-facebook mr-2"></i>Facebook</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success d-inline-block ml-2">
                                    <input type="hidden" name="social_facebook_active" value="0">
                                    <input type="checkbox" class="custom-control-input" id="social_facebook_active" name="social_facebook_active" value="1" {{ ($settings['social_facebook_active'] ?? 0) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="social_facebook_active"></label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="display: none;">
                            <div class="alert alert-light border-primary">
                                <h5><i class="icon fas fa-info-circle text-primary"></i> Configuração Facebook</h5>
                                <ol class="pl-3 mb-0 text-muted small">
                                    <li>Acesse o <a href="https://developers.facebook.com/" target="_blank" class="text-primary">Facebook for Developers</a>.</li>
                                    <li>Crie um App (Tipo: Consumidor ou Nenhum) e vá em <strong>Configurações > Básico</strong>.</li>
                                    <li>Adicione o produto <strong>Login do Facebook</strong>.</li>
                                    <li>Nas configurações do Login, em <strong>Valid OAuth Redirect URIs</strong>, adicione: <code class="user-select-all bg-white p-1 rounded border">{{ url('/auth/callback/facebook') }}</code></li>
                                </ol>
                            </div>
                            <div class="form-group">
                                <label>App ID</label>
                                <input name="social_facebook_client_id" class="form-control" value="{{ $settings['social_facebook_client_id'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>App Secret</label>
                                <input name="social_facebook_client_secret" class="form-control" value="{{ $settings['social_facebook_client_secret'] ?? '' }}">
                            </div>
                        </div>
                    </div>

                    {{-- LinkedIn --}}
                    <div class="card card-outline card-info collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title text-info"><i class="fab fa-linkedin mr-2"></i>LinkedIn</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success d-inline-block ml-2">
                                    <input type="hidden" name="social_linkedin_active" value="0">
                                    <input type="checkbox" class="custom-control-input" id="social_linkedin_active" name="social_linkedin_active" value="1" {{ ($settings['social_linkedin_active'] ?? 0) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="social_linkedin_active"></label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="display: none;">
                            <div class="alert alert-light border-info">
                                <h5><i class="icon fas fa-info-circle text-info"></i> Configuração LinkedIn</h5>
                                <ol class="pl-3 mb-0 text-muted small">
                                    <li>Acesse o <a href="https://www.linkedin.com/developers/" target="_blank" class="text-info">LinkedIn Developers</a>.</li>
                                    <li>Crie um App e vá em <strong>Auth</strong>.</li>
                                    <li>Em <strong>Authorized redirect URLs for your app</strong>, adicione: <code class="user-select-all bg-white p-1 rounded border">{{ url('/auth/callback/linkedin') }}</code></li>
                                    <li>Certifique-se de ter o produto <strong>Sign In with LinkedIn</strong> habilitado.</li>
                                </ol>
                            </div>
                            <div class="form-group">
                                <label>Client ID</label>
                                <input name="social_linkedin_client_id" class="form-control" value="{{ $settings['social_linkedin_client_id'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Client Secret</label>
                                <input name="social_linkedin_client_secret" class="form-control" value="{{ $settings['social_linkedin_client_secret'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right"><button class="btn btn-primary">Salvar</button></div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle Gateway Environment Fields
        $('.gateway-env-toggle').on('change', function() {
            var gateway = $(this).data('gateway');
            var env = $(this).val();
            
            // Hide all env sections for this gateway
            $('.env-' + gateway + '-sandbox').addClass('d-none');
            $('.env-' + gateway + '-production').addClass('d-none');
            
            // Show selected
            $('.env-' + gateway + '-' + env).removeClass('d-none');
        });

        // Test SMTP
        $('#btnTestSmtp').click(function() {
            var btn = $(this);
            var originalText = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

            var data = {
                _token: '{{ csrf_token() }}',
                smtp_host: $('[name="smtp_host"]').val(),
                smtp_port: $('[name="smtp_port"]').val(),
                smtp_username: $('[name="smtp_username"]').val(),
                smtp_password: $('[name="smtp_password"]').val(),
                smtp_encryption: $('[name="smtp_encryption"]').val(),
                smtp_from_email: $('[name="smtp_from_email"]').val(),
                smtp_from_name: $('[name="smtp_from_name"]').val(),
                smtp_test_email: $('[name="smtp_test_email"]').val()
            };

            $.ajax({
                url: '{{ route("admin.settings.test-smtp") }}',
                method: 'POST',
                data: data,
                success: function(resp) {
                    if (resp.success) {
                        toastr.success(resp.message);
                        // Refresh page after 2 seconds to show the edit button if it was created
                        if(resp.message.indexOf('sucesso') !== -1 && !$('a[href*="mailtemplates"]').length){
                             setTimeout(function(){ location.reload(); }, 2000);
                        }
                    } else {
                        toastr.error(resp.message);
                    }
                },
                error: function(xhr) {
                    var msg = 'Erro ao enviar.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg);
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
</script>
@endpush
