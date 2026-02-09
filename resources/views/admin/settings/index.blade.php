@extends('admin.layouts.app')
@php
    use Illuminate\Support\Str;
    $getUrl = function ($key) use ($settings) {
        $value = $settings[$key] ?? '';
        if (!$value) {
            return '';
        }

        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        $value = str_replace('\\', '/', $value);
        $value = preg_replace('/[?#].*$/', '', $value);

        // If stored as absolute path inside /public, make it relative again
        $publicRoot = str_replace('\\', '/', public_path());
        if (Str::startsWith($value, $publicRoot)) {
            $value = ltrim(substr($value, strlen($publicRoot)), '/');
        }

        $value = ltrim($value, '/');
        if (Str::startsWith($value, 'tmp/')) {
            return '';
        }

        // Legacy prefixes sometimes end up stored in DB
        if (Str::startsWith($value, 'public/')) {
            $value = substr($value, strlen('public/'));
        }
        if (Str::startsWith($value, 'storage/app/public/')) {
            $value = 'storage/' . substr($value, strlen('storage/app/public/'));
        }

        if (file_exists(public_path($value))) {
            return asset($value);
        }

        return '';
    };
    $pwa192 = $getUrl('pwa_icon_192');
    $pwa512 = $getUrl('pwa_icon_512');
    $pwaSplash = $getUrl('pwa_splash');
    $pwaBanner = $getUrl('pwa_banner');
    $seoOg = $getUrl('seo_og_image');
    $seoTwitter = $getUrl('seo_twitter_image');
    $watermarkUrl = $getUrl('watermark_image');
    $heroUrl = $getUrl('hero_image');
    $wmPos = (string) ($settings['video_watermark_position'] ?? 'top-right');
    $recaptchaStatus = $recaptchaStatus ?? [
        'is_configured' => false,
        'site_key_masked' => '-',
        'secret_key_masked' => '-',
        'min_score' => '0.5',
    ];
    $storageStatus = $storageStatus ?? [
        'disk' => (string) ($settings['uploads_storage_disk'] ?? 'public'),
        'is_configured' => false,
        's3_configured' => false,
        's3_bucket' => '-',
        's3_region' => '-',
        's3_endpoint' => '-',
        'uploaded_files' => 0,
        'uploaded_bytes' => 0,
        'uploaded_size_human' => '0 B',
        'stats_error' => '',
    ];
    $uploadLimitsStatus = $uploadLimitsStatus ?? [
        'video_max_mb' => (int) ($settings['video_max_mb'] ?? 1024),
        'document_max_mb' => (int) ($settings['document_max_mb'] ?? 50),
        'allowed_video_formats' => (string) ($settings['allowed_video_formats'] ?? '-'),
        'allowed_document_formats' => (string) ($settings['allowed_document_formats'] ?? '-'),
    ];
@endphp

@section('page_title', 'Configurações')
@section('breadcrumb')<li class="breadcrumb-item active">Configurações</li>@endsection

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card card-primary card-outline">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab-geral"
                        role="tab">Geral</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-appearance"
                        role="tab">Aparência</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-video" role="tab">Vídeo
                        Player</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-pwa" role="tab">PWA</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-gateway" role="tab">Gateway</a>
                </li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-preloader"
                        role="tab">Preloader</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-smtp" role="tab">SMTP</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-social" role="tab">Login
                        Social</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-seo" role="tab">SEO &
                        Analytics</a></li>
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
                                <input name="app_name" class="form-control"
                                    value="{{ $settings['app_name'] ?? config('app.name') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tema do site</label>
                                <select name="site_theme" class="form-control">
                                    <option value="light" {{ ($settings['site_theme'] ?? 'light') === 'light' ? 'selected' : '' }}>Light</option>
                                    <option value="dark" {{ ($settings['site_theme'] ?? '') === 'dark' ? 'selected' : '' }}>
                                        Dark</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>Nome da empresa</label><input name="company_name"
                                    class="form-control" value="{{ $settings['company_name'] ?? '' }}"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group"><label>Telefone</label><input name="company_phone"
                                    class="form-control mask-phone" value="{{ $settings['company_phone'] ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>E-mail</label><input name="company_email"
                                    class="form-control" value="{{ $settings['company_email'] ?? '' }}"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group"><label>CEP</label><input id="company_zip" name="company_zip"
                                    class="form-control mask-cep" data-target-number="#company_number"
                                    data-target-complement="#company_complement"
                                    data-target-district="#company_district"
                                    value="{{ $settings['company_zip'] ?? '' }}"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label>Endereço</label><input name="company_address"
                                    class="form-control" value="{{ $settings['company_address'] ?? '' }}"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group"><label>Número</label><input id="company_number"
                                    name="company_number" class="form-control"
                                    value="{{ $settings['company_number'] ?? '' }}"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group"><label>Complemento</label><input id="company_complement"
                                    name="company_complement" class="form-control"
                                    value="{{ $settings['company_complement'] ?? '' }}"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group"><label>Bairro</label><input id="company_district"
                                    name="company_district" class="form-control"
                                    value="{{ $settings['company_district'] ?? '' }}"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group"><label>Cidade</label><input name="company_city" class="form-control"
                                    value="{{ $settings['company_city'] ?? '' }}"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group"><label>Estado</label><input name="company_state"
                                    class="form-control" value="{{ $settings['company_state'] ?? '' }}"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Logo (principal)</label>
                            <input type="hidden" name="remove_logo_image" value="0">
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('logo_image') }}"
                                data-remove-input="[name='remove_logo_image']">
                                <input type="file" name="logo_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar
                                    arquivo</button>
                                <div class="progress upload-progress d-none mt-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Favicon</label>
                            <input type="hidden" name="remove_favicon_image" value="0">
                            <div class="upload-box" data-max-size="{{ 2 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('favicon_image') }}"
                                data-remove-input="[name='remove_favicon_image']">
                                <input type="file" name="favicon_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar
                                    arquivo</button>
                                <div class="progress upload-progress d-none mt-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label>Logo painel administrativo</label>
                            <input type="hidden" name="remove_logo_admin" value="0">
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('logo_admin') }}"
                                data-remove-input="[name='remove_logo_admin']">
                                <input type="file" name="logo_admin" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar
                                    arquivo</button>
                                <div class="progress upload-progress d-none mt-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Logo páginas de login/registro</label>
                            <input type="hidden" name="remove_logo_auth" value="0">
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('logo_auth') }}"
                                data-remove-input="[name='remove_logo_auth']">
                                <input type="file" name="logo_auth" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar
                                    arquivo</button>
                                <div class="progress upload-progress d-none mt-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>Logo front-end/site</label>
                            <input type="hidden" name="remove_logo_front" value="0">
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('logo_front') }}"
                                data-remove-input="[name='remove_logo_front']">
                                <input type="file" name="logo_front" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar
                                    arquivo</button>
                                <div class="progress upload-progress d-none mt-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label>Marca d'água (vídeos de cursos)</label>
                            <input type="hidden" name="remove_watermark_image" value="0">
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('watermark_image') }}"
                                data-remove-input="[name='remove_watermark_image']">
                                <input type="file" name="watermark_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small"></div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar
                                    arquivo</button>
                                <div class="progress upload-progress d-none mt-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="card card-outline card-secondary collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-shield-alt mr-2"></i>Segurança (reCAPTCHA v3)</h3>
                            <div class="card-tools d-flex align-items-center">
                                <span
                                    class="badge {{ $recaptchaStatus['is_configured'] ? 'badge-success' : 'badge-warning' }} mr-2">
                                    {{ $recaptchaStatus['is_configured'] ? 'Configurado' : 'Nao configurado' }}
                                </span>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="card-body" style="display: none;">
                            <div
                                class="alert {{ $recaptchaStatus['is_configured'] ? 'alert-success' : 'alert-warning' }}">
                                <div class="small">
                                    <strong>Status:</strong>
                                    {{ $recaptchaStatus['is_configured'] ? 'Ativo para uso' : 'Pendente de configuracao' }}<br>
                                    <strong>Site Key:</strong>
                                    <code>{{ $recaptchaStatus['site_key_masked'] }}</code><br>
                                    <strong>Secret:</strong>
                                    <code>{{ $recaptchaStatus['secret_key_masked'] }}</code><br>
                                    <strong>Score minimo:</strong> {{ $recaptchaStatus['min_score'] }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Site Key</label>
                                        <input name="recaptcha_v3_site_key" class="form-control"
                                            value="{{ $settings['recaptcha_v3_site_key'] ?? (string) config('services.recaptcha.site_key', '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Secret Key</label>
                                        <input name="recaptcha_v3_secret_key" type="password" class="form-control"
                                            value="{{ $settings['recaptcha_v3_secret_key'] ?? (string) config('services.recaptcha.v3_secret', '') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Score mínimo (0.0–1.0)</label>
                                        <input name="recaptcha_v3_min_score" class="form-control"
                                            value="{{ $settings['recaptcha_v3_min_score'] ?? (string) config('services.recaptcha.v3_min_score', 0.5) }}"
                                            placeholder="0.5">
                                        <small class="text-muted">Recomendado: 0.5. Quanto maior, mais rígido.</small>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="alert alert-light border mb-0">
                                        <div class="small text-muted">
                                            Usado principalmente em formulários públicos (ex.: <code>/contato</code>).
                                            Se ficar vazio, o sistema usa as variáveis do <code>.env</code>.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-info collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-cloud mr-2"></i>Armazenamento (S3 compatível)</h3>
                            <div class="card-tools d-flex align-items-center">
                                <span
                                    class="badge badge-secondary mr-2">{{ strtoupper((string) $storageStatus['disk']) }}</span>
                                <span
                                    class="badge {{ $storageStatus['is_configured'] ? 'badge-success' : 'badge-warning' }} mr-2">
                                    {{ $storageStatus['is_configured'] ? 'Configurado' : 'Nao configurado' }}
                                </span>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="card-body" style="display: none;">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="small text-muted">Arquivos enviados para o repositorio</div>
                                    <div class="h5 mb-0">
                                        {{ number_format((int) $storageStatus['uploaded_files'], 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-muted">Volume total enviado</div>
                                    <div class="h5 mb-0">{{ $storageStatus['uploaded_size_human'] }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-muted">Bucket/Regiao</div>
                                    <div class="h6 mb-0">
                                        {{ $storageStatus['s3_bucket'] }} / {{ $storageStatus['s3_region'] }}
                                    </div>
                                </div>
                            </div>
                            @if(!empty($storageStatus['stats_error']))
                                <div class="alert alert-warning">
                                    <strong>Aviso:</strong> nao foi possivel ler o uso do repositorio agora.
                                </div>
                            @endif
                            @php($uploadsDisk = $settings['uploads_storage_disk'] ?? (string) config('uploads.disk', 'public'))

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Disco para uploads (vídeos/documentos)</label>
                                        <select name="uploads_storage_disk" class="form-control">
                                            <option value="public" {{ $uploadsDisk === 'public' ? 'selected' : '' }}>Local
                                                (public)</option>
                                            <option value="s3" {{ $uploadsDisk === 's3' ? 'selected' : '' }}>S3</option>
                                        </select>
                                        <small class="text-muted">Ajusta endpoints de upload do sistema (ex.:
                                            <code>/upload</code> e uploads em partes).</small>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="alert alert-light border mb-0">
                                        <div class="small text-muted">
                                            Compatível com AWS S3, Wasabi, DigitalOcean Spaces, MinIO etc. Para S3
                                            “compatível”, preencha <strong>Endpoint</strong> e marque
                                            <strong>Path-style</strong> se necessário.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Access Key ID</label>
                                        <input name="s3_key" class="form-control"
                                            value="{{ $settings['s3_key'] ?? (string) config('filesystems.disks.s3.key', '') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Secret Access Key</label>
                                        <input name="s3_secret" type="password" class="form-control"
                                            value="{{ $settings['s3_secret'] ?? (string) config('filesystems.disks.s3.secret', '') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Região</label>
                                        <input name="s3_region" class="form-control"
                                            value="{{ $settings['s3_region'] ?? (string) config('filesystems.disks.s3.region', '') }}"
                                            placeholder="us-east-1">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Bucket</label>
                                        <input name="s3_bucket" class="form-control"
                                            value="{{ $settings['s3_bucket'] ?? (string) config('filesystems.disks.s3.bucket', '') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>URL pública/CDN (opcional)</label>
                                        <input name="s3_url" class="form-control"
                                            value="{{ $settings['s3_url'] ?? (string) config('filesystems.disks.s3.url', '') }}"
                                            placeholder="https://...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Endpoint (opcional)</label>
                                        <input name="s3_endpoint" class="form-control"
                                            value="{{ $settings['s3_endpoint'] ?? (string) config('filesystems.disks.s3.endpoint', '') }}"
                                            placeholder="https://s3.us-east-1.amazonaws.com">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                @php($s3PathStyle = (int) ($settings['s3_path_style'] ?? (int) config('filesystems.disks.s3.use_path_style_endpoint', 0)))
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="s3_path_style" value="0">
                                    <input type="checkbox" class="custom-control-input" id="s3_path_style"
                                        name="s3_path_style" value="1" {{ $s3PathStyle ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="s3_path_style">Usar endpoint
                                        path-style</label>
                                </div>
                                <small class="text-muted">Alguns provedores/MinIO exigem isso.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card card-outline card-warning collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-upload mr-2"></i>Limites de Upload</h3>
                            <div class="card-tools d-flex align-items-center">
                                <span class="badge badge-info mr-2">Video:
                                    {{ (int) $uploadLimitsStatus['video_max_mb'] }} MB</span>
                                <span class="badge badge-info mr-2">Doc:
                                    {{ (int) $uploadLimitsStatus['document_max_mb'] }} MB</span>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i
                                        class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="card-body" style="display: none;">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>VIDEO_MAX_MB</label>
                                        <input name="video_max_mb" type="number" min="1" max="10240"
                                            class="form-control"
                                            value="{{ $settings['video_max_mb'] ?? (int) config('uploads.video_max_mb', 1024) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>DOCUMENT_MAX_MB</label>
                                        <input name="document_max_mb" type="number" min="1" max="1024"
                                            class="form-control"
                                            value="{{ $settings['document_max_mb'] ?? (int) config('uploads.document_max_mb', 50) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-light border mb-0">
                                        <div class="small text-muted">
                                            Se os campos ficarem vazios, o sistema usa os valores do <code>.env</code>.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>ALLOWED_VIDEO_FORMATS</label>
                                        <input name="allowed_video_formats" class="form-control"
                                            value="{{ $settings['allowed_video_formats'] ?? implode(',', (array) config('uploads.allowed_video_formats', [])) }}"
                                            placeholder="mp4,webm,mkv">
                                        <small class="text-muted">Separar por vírgula. Ex:
                                            <code>mp4,webm,mkv</code></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>ALLOWED_DOCUMENT_FORMATS</label>
                                        <input name="allowed_document_formats" class="form-control"
                                            value="{{ $settings['allowed_document_formats'] ?? implode(',', (array) config('uploads.allowed_document_formats', [])) }}"
                                            placeholder="pdf,docx,pptx">
                                        <small class="text-muted">Separar por vírgula. Ex:
                                            <code>pdf,docx,pptx</code></small>
                                    </div>
                                </div>
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
                            <label>Imagem de Fundo (Hero)</label>
                            <input type="hidden" name="remove_hero_image" value="0">
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                                data-existing-url="{{ $heroUrl ?? '' }}" data-remove-input="[name='remove_hero_image']">
                                <input type="file" name="hero_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-help text-muted small">Recomendado: 1920x1080px</div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar
                                    arquivo</button>
                                <div class="progress upload-progress d-none mt-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="text-primary mb-3"><i class="fas fa-image mr-2"></i>Fundo do Portal</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Imagem de Fundo Global</label>
                            <input type="hidden" name="remove_site_bg_image" value="0">
                            <div class="upload-box" data-max-size="{{ 5 * 1024 * 1024 }}"
                                data-existing-url="{{ $getUrl('site_bg_image') }}"
                                data-remove-input="[name='remove_site_bg_image']">
                                <input type="file" name="site_bg_image" accept="image/*" class="d-none">
                                <div class="upload-preview text-center text-muted">Arraste ou clique para enviar</div>
                                <div class="upload-meta text-muted small"></div>
                                <button type="button" class="btn btn-sm btn-primary upload-btn">Selecionar
                                    imagem</button>
                                <div class="progress upload-progress d-none mt-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Opacidade do Degradê (%)</label>
                                <input name="site_bg_gradient_opacity" type="number" min="0" max="100"
                                    class="form-control" value="{{ $settings['site_bg_gradient_opacity'] ?? 85 }}">
                                <small class="text-muted">Opacidade da camada de degradê sobre a imagem de fundo
                                    (0-100%).</small>
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Desfoque do fundo (Blur: <span
                                        id="val-blur">{{ $settings['events_hero_bg_blur_px'] ?? 64 }}</span>px)</label>
                                <input name="events_hero_bg_blur_px" type="range" min="0" max="150" step="5"
                                    class="custom-range" id="range-blur"
                                    value="{{ $settings['events_hero_bg_blur_px'] ?? 64 }}"
                                    oninput="document.getElementById('val-blur').innerText = this.value">
                                <small class="text-muted d-block">Intensidade do desfoque da imagem de fundo.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Intensidade da Película (<span
                                        id="val-film">{{ $settings['events_hero_film_strength_percent'] ?? 100 }}</span>%)</label>
                                <input name="events_hero_film_strength_percent" type="range" min="0" max="100" step="5"
                                    class="custom-range" id="range-film"
                                    value="{{ $settings['events_hero_film_strength_percent'] ?? 100 }}"
                                    oninput="document.getElementById('val-film').innerText = this.value">
                                <small class="text-muted d-block">Opacidade da camada de cor sobre a imagem.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-info">
                                <div class="small">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Essas configurações afetam as páginas de detalhes de <strong>Eventos</strong> e
                                    <strong>Mentorias</strong> no site.
                                </div>
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
                                    <input name="site_color_primary" class="form-control"
                                        value="{{ $settings['site_color_primary'] ?? '#007bff' }}">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fas fa-square"
                                                style="color: {{ $settings['site_color_primary'] ?? '#007bff' }}"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cor Secundária (Backgrounds, Detalhes)</label>
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
                    </div>

                    <hr>

                    <h5 class="text-primary mb-3"><i class="fas fa-shoe-prints mr-2"></i>Rodapé</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Texto do Rodapé (Sobre)</label>
                                <textarea name="footer_text" class="form-control"
                                    rows="3">{{ $settings['footer_text'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group"><label><i class="fab fa-instagram mr-1"></i>Instagram
                                    URL</label><input name="social_instagram" class="form-control"
                                    value="{{ $settings['social_instagram'] ?? '' }}"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group"><label><i class="fab fa-facebook mr-1"></i>Facebook
                                    URL</label><input name="social_facebook" class="form-control"
                                    value="{{ $settings['social_facebook'] ?? '' }}"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group"><label><i class="fab fa-youtube mr-1"></i>Youtube URL</label><input
                                    name="social_youtube" class="form-control"
                                    value="{{ $settings['social_youtube'] ?? '' }}"></div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group"><label><i class="fab fa-linkedin mr-1"></i>LinkedIn
                                    URL</label><input name="social_linkedin" class="form-control"
                                    value="{{ $settings['social_linkedin'] ?? '' }}"></div>
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
                                <label class="custom-control-label" for="testimonials_carousel_enabled">Ativar carrossel
                                    no site</label>
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
                                    setas</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                                <input type="hidden" name="testimonials_carousel_show_dots" value="0">
                                <input type="checkbox" class="custom-control-input" id="testimonials_carousel_show_dots"
                                    name="testimonials_carousel_show_dots" value="1" {{ ($settings['testimonials_carousel_show_dots'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="testimonials_carousel_show_dots">Exibir
                                    bolinhas</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                                <input type="hidden" name="testimonials_carousel_autoplay" value="0">
                                <input type="checkbox" class="custom-control-input" id="testimonials_carousel_autoplay"
                                    name="testimonials_carousel_autoplay" value="1" {{ ($settings['testimonials_carousel_autoplay'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label"
                                    for="testimonials_carousel_autoplay">Autoplay</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                                <input type="hidden" name="testimonials_carousel_pause_on_hover" value="0">
                                <input type="checkbox" class="custom-control-input"
                                    id="testimonials_carousel_pause_on_hover"
                                    name="testimonials_carousel_pause_on_hover" value="1" {{ ($settings['testimonials_carousel_pause_on_hover'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="testimonials_carousel_pause_on_hover">Pausar ao
                                    passar o mouse</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                                <input type="hidden" name="testimonials_carousel_loop" value="0">
                                <input type="checkbox" class="custom-control-input" id="testimonials_carousel_loop"
                                    name="testimonials_carousel_loop" value="1" {{ ($settings['testimonials_carousel_loop'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="testimonials_carousel_loop">Loop
                                    infinito</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div
                                class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                                <input type="hidden" name="testimonials_carousel_centered" value="0">
                                <input type="checkbox" class="custom-control-input" id="testimonials_carousel_centered"
                                    name="testimonials_carousel_centered" value="1" {{ ($settings['testimonials_carousel_centered'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="testimonials_carousel_centered">Centralizar
                                    slides</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Efeito</label>
                                <select name="testimonials_carousel_effect" class="form-control">
                                    @php($effect = $settings['testimonials_carousel_effect'] ?? 'slide')
                                    <option value="slide" {{ $effect === 'slide' ? 'selected' : '' }}>Slide</option>
                                    <option value="fade" {{ $effect === 'fade' ? 'selected' : '' }}>Fade (1 por vez)
                                    </option>
                                </select>
                                <small class="text-muted">No modo Fade, o carrossel exibe 1 card por vez.</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Velocidade (ms)</label>
                                <input name="testimonials_carousel_speed_ms" type="number" min="100" max="5000"
                                    class="form-control"
                                    value="{{ $settings['testimonials_carousel_speed_ms'] ?? 600 }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Delay autoplay (ms)</label>
                                <input name="testimonials_carousel_delay_ms" type="number" min="1000" max="30000"
                                    class="form-control"
                                    value="{{ $settings['testimonials_carousel_delay_ms'] ?? 4500 }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Espaçamento (px)</label>
                                <input name="testimonials_carousel_space_between" type="number" min="0" max="120"
                                    class="form-control"
                                    value="{{ $settings['testimonials_carousel_space_between'] ?? 24 }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Slides por vez (mobile)</label>
                                <input name="testimonials_carousel_slides_mobile" type="number" min="1" max="3"
                                    class="form-control"
                                    value="{{ $settings['testimonials_carousel_slides_mobile'] ?? 1 }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Slides por vez (tablet)</label>
                                <input name="testimonials_carousel_slides_tablet" type="number" min="1" max="3"
                                    class="form-control"
                                    value="{{ $settings['testimonials_carousel_slides_tablet'] ?? 2 }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Slides por vez (desktop)</label>
                                <input name="testimonials_carousel_slides_desktop" type="number" min="1" max="4"
                                    class="form-control"
                                    value="{{ $settings['testimonials_carousel_slides_desktop'] ?? 3 }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- VÍDEO PLAYER (PLYR) --}}
                <div class="tab-pane fade show active" id="tab-video" role="tabpanel">
                    <h5 class="text-primary mb-3"><i class="fas fa-play-circle mr-2"></i>Player de Vídeo (Plyr)</h5>

                    <div class="alert alert-info">
                        Configure o player de vídeo usado nas aulas/cursos. As opções avançadas aceitam o JSON de configuração do Plyr (qualquer opção suportada pela biblioteca).
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
                                <small class="text-muted">Define a cor dos botões/controles (CSS:
                                    <code>--plyr-color-main</code>).</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Seek (segundos)</label>
                                <input name="video_plyr_seek_time" class="form-control"
                                    value="{{ $settings['video_plyr_seek_time'] ?? '10' }}" placeholder="Ex: 10">
                                <small class="text-muted">Tempo de avanço/retrocesso nos atalhos.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Volume inicial (0 a 1)</label>
                                <input name="video_plyr_volume" class="form-control"
                                    value="{{ $settings['video_plyr_volume'] ?? '0.8' }}" placeholder="Ex: 0.8">
                                <small class="text-muted">Opcional. Use 0.0 a 1.0.</small>
                            </div>
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
                                <label class="custom-control-label" for="video_plyr_muted">Iniciar mudo</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                                <input type="hidden" name="video_plyr_click_to_play" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_click_to_play"
                                    name="video_plyr_click_to_play" value="1" {{ ($settings['video_plyr_click_to_play'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_click_to_play">Clique para reproduzir</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                                <input type="hidden" name="video_plyr_disable_context_menu" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_disable_context_menu"
                                    name="video_plyr_disable_context_menu" value="1" {{ ($settings['video_plyr_disable_context_menu'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_disable_context_menu">Bloquear menu do botão direito</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-3">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                <input type="hidden" name="video_plyr_rewind_enabled" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_rewind_enabled"
                                    name="video_plyr_rewind_enabled" value="1" {{ ($settings['video_plyr_rewind_enabled'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_rewind_enabled">Botão Retroceder</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                <input type="hidden" name="video_plyr_fast_forward_enabled" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_fast_forward_enabled"
                                    name="video_plyr_fast_forward_enabled" value="1" {{ ($settings['video_plyr_fast_forward_enabled'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_fast_forward_enabled">Botão Avançar</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                <input type="hidden" name="video_plyr_volume_enabled" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_plyr_volume_enabled"
                                    name="video_plyr_volume_enabled" value="1" {{ ($settings['video_plyr_volume_enabled'] ?? 1) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_plyr_volume_enabled">Controle de Volume</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Controles (separados por vírgula)</label>
                                <input name="video_plyr_controls" class="form-control"
                                    value="{{ $settings['video_plyr_controls'] ?? 'play,progress,current-time,mute,volume,settings,fullscreen' }}">
                                <small class="text-muted">Ex:
                                    <code>play,progress,current-time,mute,volume,settings,fullscreen</code></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Menu “Configurações” (separado por vírgula)</label>
                                <input name="video_plyr_settings" class="form-control"
                                    value="{{ $settings['video_plyr_settings'] ?? 'captions,quality,speed,loop' }}">
                                <small class="text-muted">Ex: <code>captions,quality,speed,loop</code></small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Velocidades disponíveis (separadas por vírgula)</label>
                                <input name="video_plyr_speed_options" class="form-control"
                                    value="{{ $settings['video_plyr_speed_options'] ?? '0.5,0.75,1,1.25,1.5,2' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Velocidade padrão</label>
                                <input name="video_plyr_speed_selected" class="form-control"
                                    value="{{ $settings['video_plyr_speed_selected'] ?? '1' }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Opções avançadas (JSON)</label>
                        <textarea name="video_plyr_options_json" class="form-control" rows="6"
                            placeholder='{"controls":["play","progress","current-time","mute","volume","settings","fullscreen"],"tooltips":{"controls":true,"seek":true}}'>{{ $settings['video_plyr_options_json'] ?? '' }}</textarea>
                        <small class="text-muted">Se preenchido, o JSON será mesclado às opções acima (o JSON tem prioridade).</small>
                    </div>

                    <hr>

                    <h5 class="text-primary mb-3"><i class="fas fa-water mr-2"></i>Marca d'água (Cursos)</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                                <input type="hidden" name="video_watermark_enabled" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_watermark_enabled"
                                    name="video_watermark_enabled" value="1" {{ ($settings['video_watermark_enabled'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_watermark_enabled">Exibir marca d'água no player</label>
                            </div>

                            @if($watermarkUrl)
                                <div class="mb-2">
                                    <img src="{{ $watermarkUrl }}" alt="Marca d'água"
                                        style="max-height: 72px; max-width: 240px;">
                                </div>
                            @else
                                <p class="text-muted mb-2">Nenhuma imagem configurada. Envie em <strong>Geral</strong> → “Marca d'água (vídeos de cursos)”.</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                                <input type="hidden" name="video_watermark_text_enabled" value="0">
                                <input type="checkbox" class="custom-control-input" id="video_watermark_text_enabled"
                                    name="video_watermark_text_enabled" value="1" {{ ($settings['video_watermark_text_enabled'] ?? 0) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_watermark_text_enabled">Exibir texto dinâmico (anti-pirataria)</label>
                            </div>
                            <div class="form-group">
                                <label>Template do texto</label>
                                <input name="video_watermark_text_template" class="form-control"
                                    value="{{ $settings['video_watermark_text_template'] ?? '{name} • {email} • #{id}' }}">
                                <small class="text-muted">Placeholders: <code>{name}</code>, <code>{email}</code>, <code>{id}</code>.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Opacidade (0 a 1)</label>
                                        <input name="video_watermark_opacity" class="form-control"
                                            value="{{ $settings['video_watermark_opacity'] ?? '0.15' }}"
                                            placeholder="Ex: 0.15">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tamanho (% da largura)</label>
                                        <input name="video_watermark_size_percent" class="form-control"
                                            value="{{ $settings['video_watermark_size_percent'] ?? '18' }}"
                                            placeholder="Ex: 18">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Posição</label>
                                        <select name="video_watermark_position" class="form-control">
                                            <option value="top-left" @selected($wmPos === 'top-left')>Topo esquerdo</option>
                                            <option value="top-right" @selected($wmPos === 'top-right')>Topo direito</option>
                                            <option value="bottom-left" @selected($wmPos === 'bottom-left')>Inferior esquerdo</option>
                                            <option value="bottom-right" @selected($wmPos === 'bottom-right')>Inferior direito</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="fas fa-bullhorn mr-1"></i> Anúncios da comunidade</h5>
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                        <input type="hidden" name="ads_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="ads_enabled" name="ads_enabled"
                            value="1" {{ ($settings['ads_enabled'] ?? 0) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="ads_enabled">Exibir anúncios entre postagens</label>
                    </div>

                    <div class="card card-outline card-warning mt-3 mb-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fab fa-google mr-1"></i> Google AdSense</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Como configurar:</strong>
                                <ol class="pl-3 mb-0 mt-2 small">
                                    <li>Acesse <a href="https://www.google.com/adsense/" target="_blank" rel="noopener">google.com/adsense</a> e crie/acesse sua conta.</li>
                                    <li>Adicione seu site e aguarde aprovação.</li>
                                    <li>Crie um bloco de anúncios (Display, Feed ou In-article).</li>
                                    <li>Copie o <strong>data-ad-client</strong> (ex: ca-pub-1234567890) e <strong>data-ad-slot</strong> (ex: 9876543210).</li>
                                    <li>Cole nos campos abaixo.</li>
                                </ol>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>AdSense Publisher ID (data-ad-client)</label>
                                        <input name="adsense_publisher_id" class="form-control"
                                            value="{{ $settings['adsense_publisher_id'] ?? '' }}"
                                            placeholder="ca-pub-1234567890123456">
                                        <small class="text-muted">Começa com <code>ca-pub-</code></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>AdSense Slot ID (data-ad-slot)</label>
                                        <input name="adsense_slot_id" class="form-control"
                                            value="{{ $settings['adsense_slot_id'] ?? '' }}"
                                            placeholder="1234567890">
                                        <small class="text-muted">Número do bloco de anúncios.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Formato do anúncio</label>
                                        <select name="adsense_format" class="form-control">
                                            @php($adsFormat = $settings['adsense_format'] ?? 'auto')
                                            <option value="auto" {{ $adsFormat === 'auto' ? 'selected' : '' }}>Automático (responsivo)</option>
                                            <option value="fluid" {{ $adsFormat === 'fluid' ? 'selected' : '' }}>Fluido (in-feed)</option>
                                            <option value="rectangle" {{ $adsFormat === 'rectangle' ? 'selected' : '' }}>Retângulo</option>
                                            <option value="horizontal" {{ $adsFormat === 'horizontal' ? 'selected' : '' }}>Horizontal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Posição dos anúncios</label>
                                        <select name="adsense_frequency" class="form-control">
                                            @php($adsFreq = (int) ($settings['adsense_frequency'] ?? 5))
                                            <option value="3" {{ $adsFreq === 3 ? 'selected' : '' }}>A cada 3 postagens</option>
                                            <option value="5" {{ $adsFreq === 5 ? 'selected' : '' }}>A cada 5 postagens</option>
                                            <option value="7" {{ $adsFreq === 7 ? 'selected' : '' }}>A cada 7 postagens</option>
                                            <option value="10" {{ $adsFreq === 10 ? 'selected' : '' }}>A cada 10 postagens</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Código HTML/JS personalizado (opcional)</label>
                        <textarea name="ads_code_html" class="form-control" rows="6"
                            placeholder="Cole aqui código personalizado se não usar AdSense acima">{{ $settings['ads_code_html'] ?? '' }}</textarea>
                        <small class="text-muted">Se preferir, cole código de outras redes de anúncios. Se configurar o AdSense acima, este campo é opcional.</small>
                    </div>

                    <hr>

                    <h5 class="mb-3"><i class="fas fa-chart-line mr-1"></i> Códigos de rastreamento</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Código no &lt;head&gt; (GA/GTM/meta pixels)</label>
                                <textarea name="tracking_head" class="form-control" rows="6"
                                    placeholder="Cole aqui scripts/trechos para o HEAD">{{ $settings['tracking_head'] ?? '' }}</textarea>
                                <small class="text-muted">Inserido no <code>&lt;head&gt;</code> do site público.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Código no &lt;body&gt; (ex.: GTM noscript)</label>
                                <textarea name="tracking_body" class="form-control" rows="6"
                                    placeholder="Cole aqui trechos para o início/final do BODY">{{ $settings['tracking_body'] ?? '' }}</textarea>
                                <small class="text-muted">Inserido no <code>&lt;body&gt;</code> do site público.</small>
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
        $(document).ready(function () {
            // Toggle Gateway Environment Fields
            $('.gateway-env-toggle').on('change', function () {
                var gateway = $(this).data('gateway');
                var env = $(this).val();

                // Hide all env sections for this gateway
                $('.env-' + gateway + '-sandbox').addClass('d-none');
                $('.env-' + gateway + '-production').addClass('d-none');

                // Show selected
                $('.env-' + gateway + '-' + env).removeClass('d-none');
            });

            // Test SMTP
            $('#btnTestSmtp').click(function () {
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
                    success: function (resp) {
                        if (resp.success) {
                            toastr.success(resp.message);
                            // Refresh page after 2 seconds to show the edit button if it was created
                            if (resp.message.indexOf('sucesso') !== -1 && !$('a[href*="mailtemplates"]').length) {
                                setTimeout(function () { location.reload(); }, 2000);
                            }
                        } else {
                            toastr.error(resp.message);
                        }
                    },
                    error: function (xhr) {
                        var msg = 'Erro ao enviar.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    },
                    complete: function () {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
@endpush