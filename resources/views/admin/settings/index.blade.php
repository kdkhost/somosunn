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
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-pwa" role="tab">PWA</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-gateway" role="tab">Gateway</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-preloader" role="tab">Preloader</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-smtp" role="tab">SMTP</a></li>
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
                    <h5 class="mb-3 text-primary"><i class="fas fa-credit-card mr-2"></i>MercadoPago</h5>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Public Key</label>
                            <input name="payments.mercadopago.public_key" class="form-control" value="{{ $settings['payments.mercadopago.public_key'] ?? '' }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Access Token</label>
                            <input name="payments.mercadopago.access_token" class="form-control" value="{{ $settings['payments.mercadopago.access_token'] ?? '' }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4"><label>Taxa (%)</label><input name="payments.mercadopago.fee_percentage" class="form-control mask-money" value="{{ $settings['payments.mercadopago.fee_percentage'] ?? '' }}"></div>
                        <div class="form-group col-md-4"><label>Taxa fixa</label><input name="payments.mercadopago.fee_fixed" class="form-control mask-money" value="{{ $settings['payments.mercadopago.fee_fixed'] ?? '' }}"></div>
                        <div class="form-group col-md-4"><label>Repassar taxa ao comprador</label><select name="payments.mercadopago.pass_fee" class="form-control"><option value="1" {{ (isset($settings['payments.mercadopago.pass_fee']) && $settings['payments.mercadopago.pass_fee']) ? 'selected' : '' }}>Sim</option><option value="0">Não</option></select></div>
                    </div>

                    <hr>

                    <h5 class="mb-3 mt-4 text-primary"><i class="fas fa-money-bill-wave mr-2"></i>PagSeguro</h5>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>E-mail</label>
                            <input name="payments.pagseguro.email" class="form-control" value="{{ $settings['payments.pagseguro.email'] ?? '' }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Token</label>
                            <input name="payments.pagseguro.token" class="form-control" value="{{ $settings['payments.pagseguro.token'] ?? '' }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4"><label>Taxa (%)</label><input name="payments.pagseguro.fee_percentage" class="form-control mask-money" value="{{ $settings['payments.pagseguro.fee_percentage'] ?? '' }}"></div>
                        <div class="form-group col-md-4"><label>Taxa fixa</label><input name="payments.pagseguro.fee_fixed" class="form-control mask-money" value="{{ $settings['payments.pagseguro.fee_fixed'] ?? '' }}"></div>
                        <div class="form-group col-md-4"><label>Repassar taxa ao comprador</label><select name="payments.pagseguro.pass_fee" class="form-control"><option value="1" {{ (isset($settings['payments.pagseguro.pass_fee']) && $settings['payments.pagseguro.pass_fee']) ? 'selected' : '' }}>Sim</option><option value="0">Não</option></select></div>
                    </div>
                    <div class="form-group mt-2">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="payments.pagseguro.sandbox" value="0">
                            <input type="checkbox" class="custom-control-input" id="ps_sandbox" name="payments.pagseguro.sandbox" value="1" {{ ($settings['payments.pagseguro.sandbox'] ?? 0) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="ps_sandbox">Modo Sandbox (Testes)</label>
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
                        <div class="form-group col-md-6"><label>Enviar teste para</label><input name="smtp_test_email" class="form-control" value="{{ auth()->user()->email ?? '' }}"></div>
                        <div class="form-group col-md-3"><button type="button" class="btn btn-secondary mt-4" id="btnTestSmtp">Enviar teste</button></div>
                    </div>
                </div>

            </div>
        </div>
        <div class="card-footer text-right"><button class="btn btn-primary">Salvar</button></div>
    </div>
</form>
@endsection
