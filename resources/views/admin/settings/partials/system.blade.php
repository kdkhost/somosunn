<div class="card-body">
    @php
        $maintenanceManualEnabled = (string) ($settings['maintenance_enabled'] ?? '0') === '1';
        $maintenanceAutoEnabled = (string) ($settings['maintenance_auto_enabled'] ?? '0') === '1';

        $maintenanceStartAt = '';
        $maintenanceEndAt = '';
        $maintenanceStartCarbon = null;
        $maintenanceEndCarbon = null;

        try {
            if (!empty($settings['maintenance_start_at'])) {
                $maintenanceStartCarbon = \Carbon\Carbon::parse($settings['maintenance_start_at']);
                $maintenanceStartAt = $maintenanceStartCarbon->format('Y-m-d\TH:i');
            }
        } catch (\Throwable $e) {
            $maintenanceStartAt = '';
        }

        try {
            if (!empty($settings['maintenance_end_at'])) {
                $maintenanceEndCarbon = \Carbon\Carbon::parse($settings['maintenance_end_at']);
                $maintenanceEndAt = $maintenanceEndCarbon->format('Y-m-d\TH:i');
            }
        } catch (\Throwable $e) {
            $maintenanceEndAt = '';
        }

        $maintenanceScheduledActive = false;
        if ($maintenanceAutoEnabled && $maintenanceStartCarbon) {
            $maintenanceScheduledActive = now()->greaterThanOrEqualTo($maintenanceStartCarbon)
                && (!$maintenanceEndCarbon || now()->lessThanOrEqualTo($maintenanceEndCarbon));
        }

        $maintenanceEffective = $maintenanceManualEnabled || $maintenanceScheduledActive;
    @endphp

    <div class="alert alert-info mb-4">
        <i class="fas fa-cogs me-2"></i> Configurações globais do sistema, segurança e armazenamento de arquivos.
    </div>

    {{-- SEGURANÇA --}}
    <div class="card card-outline card-danger mb-4">
        <div class="card-header">
            <h3 class="card-title fw-bold"><i class="fas fa-shield-alt me-2"></i> Segurança (Google reCAPTCHA
                v3)</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Site Key</label>
                    <input type="text" name="recaptcha_v3_site_key" class="form-control"
                        value="{{ $settings['recaptcha_v3_site_key'] ?? config('services.recaptcha.site_key') }}">
                </div>
                <div class="col-md-6 form-group">
                    <label>Secret Key</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                        <input type="password" name="recaptcha_v3_secret_key" class="form-control"
                            value="{{ $settings['recaptcha_v3_secret_key'] ?? config('services.recaptcha.v3_secret') }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Score Mínimo (0.0 a 1.0)</label>
                    <input type="number" step="0.1" min="0" max="1" name="recaptcha_v3_min_score" class="form-control"
                        value="{{ $settings['recaptcha_v3_min_score'] ?? config('services.recaptcha.v3_min_score', 0.5) }}">
                    <small class="text-muted">Recomendado: 0.5</small>
                </div>
            </div>
        </div>
    </div>

    {{-- LIMITES --}}
    <h5 class="text-primary mb-3"><i class="fas fa-server me-2"></i> Limites e Uploads</h5>
    <div class="row">
        <div class="col-md-6">
            <div class="info-box bg-light">
                <span class="info-box-icon bg-info"><i class="fas fa-video"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Upload de Vídeo</span>
                    <span class="info-box-number">
                        <input type="number" name="video_max_mb"
                            class="form-control form-control-sm d-inline-block w-50"
                            value="{{ $settings['video_max_mb'] ?? '1024' }}"> MB
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label>Formatos de Vídeo Permitidos</label>
                <input type="text" name="allowed_video_formats" class="form-control"
                    value="{{ $settings['allowed_video_formats'] ?? implode(',', config('uploads.allowed_video_formats', [])) }}"
                    placeholder="mp4,webm,mkv">
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box bg-light">
                <span class="info-box-icon bg-warning"><i class="fas fa-file-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Upload de Arquivos</span>
                    <span class="info-box-number">
                        <input type="number" name="document_max_mb"
                            class="form-control form-control-sm d-inline-block w-50"
                            value="{{ $settings['document_max_mb'] ?? '50' }}"> MB
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label>Formatos de Documento Permitidos</label>
                <input type="text" name="allowed_document_formats" class="form-control"
                    value="{{ $settings['allowed_document_formats'] ?? implode(',', config('uploads.allowed_document_formats', [])) }}"
                    placeholder="pdf,docx,pptx,zip,rar">
            </div>
        </div>
    </div>

    <hr class="my-4">

    {{-- ARMAZENAMENTO --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title fw-bold"><i class="fas fa-hdd me-2"></i> Armazenamento (Filesystem)</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <strong>Armazenamento remoto desativado.</strong>
                <div class="mt-2">Todos os uploads do sistema passam a usar somente o armazenamento local em <code>public/storage</code>.</div>
            </div>
        </div>
    </div>

    {{-- MANUTENCAO --}}
    <div class="card card-outline card-warning mt-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title fw-bold mb-0">
                <i class="fas fa-tools me-2"></i> Modo de manutencao
            </h3>
            @if($maintenanceEffective)
                <span class="badge text-bg-warning px-3 py-2">ATIVO NO SITE</span>
            @else
                <span class="badge text-bg-success px-3 py-2">DESATIVADO</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="border rounded p-3 mb-3 h-100">
                        <h6 class="fw-bold">Ativacao manual imediata</h6>
                        <p class="text-muted small mb-3">Forca o site inteiro para manutencao agora.</p>
                        <div class="form-check form-switch custom-switch-off-danger custom-switch-on-success mb-3">
                            <input type="hidden" name="maintenance_enabled" value="0">
                            <input type="checkbox" class="form-check-input" id="maintenance_enabled" name="maintenance_enabled"
                                value="1" {{ $maintenanceManualEnabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="maintenance_enabled">Ativar manutencao manual</label>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-warning btn-sm me-2 mb-2" data-maintenance-action="manual-on">
                                <i class="fas fa-power-off me-1"></i> Ativar agora
                            </button>
                            <button type="button" class="btn btn-success btn-sm mb-2" data-maintenance-action="manual-off">
                                <i class="fas fa-check-circle me-1"></i> Desativar agora
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="border rounded p-3 mb-3 h-100">
                        <h6 class="fw-bold">Ativacao automatica agendada</h6>
                        <p class="text-muted small mb-3">Define uma janela para ativar e desativar automaticamente.</p>
                        <div class="form-check form-switch custom-switch-off-danger custom-switch-on-success mb-3">
                            <input type="hidden" name="maintenance_auto_enabled" value="0">
                            <input type="checkbox" class="form-check-input" id="maintenance_auto_enabled" name="maintenance_auto_enabled"
                                value="1" {{ $maintenanceAutoEnabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="maintenance_auto_enabled">Habilitar agendamento automatico</label>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Inicio</label>
                                <input type="datetime-local" name="maintenance_start_at" class="form-control" value="{{ $maintenanceStartAt }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Fim (opcional)</label>
                                <input type="datetime-local" name="maintenance_end_at" class="form-control" value="{{ $maintenanceEndAt }}">
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary btn-sm me-2 mb-2" data-maintenance-action="auto-on">
                                <i class="fas fa-clock me-1"></i> Habilitar automatico
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm mb-2" data-maintenance-action="auto-off">
                                <i class="fas fa-ban me-1"></i> Desabilitar automatico
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="text-primary mb-3"><i class="fas fa-paint-brush me-2"></i> Personalizacao da pagina de manutencao</h5>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Titulo da pagina</label>
                    <input type="text" name="maintenance_title" class="form-control"
                        value="{{ $settings['maintenance_title'] ?? 'Sistema em manutencao' }}">
                </div>
                <div class="col-md-6 form-group">
                    <label>Subtitulo</label>
                    <input type="text" name="maintenance_subtitle" class="form-control"
                        value="{{ $settings['maintenance_subtitle'] ?? 'Estamos melhorando sua experiencia.' }}">
                </div>
                <div class="col-md-12 form-group">
                    <label>Mensagem principal</label>
                    <textarea name="maintenance_message" class="form-control" rows="3">{{ $settings['maintenance_message'] ?? 'Voltamos em instantes. Obrigado pela paciencia.' }}</textarea>
                </div>
                <div class="col-md-4 form-group">
                    <label>Texto do botao</label>
                    <input type="text" name="maintenance_button_label" class="form-control"
                        value="{{ $settings['maintenance_button_label'] ?? 'Ir para a home' }}">
                </div>
                <div class="col-md-4 form-group">
                    <label>URL do botao</label>
                    <input type="text" name="maintenance_button_url" class="form-control"
                        value="{{ $settings['maintenance_button_url'] ?? route('home') }}">
                </div>
                <div class="col-md-4 form-group">
                    <label>E-mail de suporte</label>
                    <input type="email" name="maintenance_contact_email" class="form-control"
                        value="{{ $settings['maintenance_contact_email'] ?? ($settings['smtp_from_email'] ?? '') }}">
                </div>
            </div>

            <hr class="my-4">

            <h5 class="text-primary mb-3"><i class="fas fa-shield-alt me-2"></i> Liberacao por IP e Dispositivo Autorizado</h5>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="maintenance_allowed_ips">IPs ou faixas CIDR liberados (um por linha ou separados por virgula)</label>
                    <textarea id="maintenance_allowed_ips" name="maintenance_allowed_ips" class="form-control" rows="3"
                        placeholder="Ex.: 187.10.20.30&#10;192.168.1.0/24">{{ $settings['maintenance_allowed_ips'] ?? '' }}</textarea>
                    <small class="form-text text-muted">
                        Seu IP atual detectado: <code>{{ request()->header('CF-Connecting-IP') ?: request()->ip() }}</code>
                        <button type="button" class="btn btn-xs btn-outline-info ms-2" id="btnAddCurrentIpAdmin">Usar meu IP</button>
                    </small>
                </div>
                <div class="col-md-6 form-group">
                    <label for="maintenance_bypass_token">Token de dispositivo autorizado (cookie seguro)</label>
                    <div class="input-group">
                        <input type="text" id="maintenance_bypass_token" name="maintenance_bypass_token" class="form-control"
                            placeholder="Clique em Autorizar ou defina um token secreto"
                            value="{{ $settings['maintenance_bypass_token'] ?? '' }}">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-primary" id="btnAuthorizeMaintenanceDeviceAdmin">
                                <i class="fas fa-laptop-medical me-1"></i> Autorizar este dispositivo
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">
                        Enderecos MAC nao trafegam via HTTP; utilize este token para liberar este navegador via cookie assinado ou acesse via <code>?maintenance_bypass_token=SEU_TOKEN</code>.
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- SUBDOMINIOS, CACHE DE BORDA & CDN --}}
    <div class="card card-outline card-primary mb-4">
        <div class="card-header">
            <h3 class="card-title fw-bold">
                <i class="fas fa-network-wired me-2"></i> Subdominios, CDN e Cache Publico (Cloudflare/LiteSpeed)
            </h3>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="domain_routing_enabled" value="0">
                        <input type="checkbox" class="form-check-input" id="domain_routing_enabled" name="domain_routing_enabled" value="1"
                            {{ !empty($settings['domain_routing_enabled']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="domain_routing_enabled">Ativar isolamento estrito por subdominio</label>
                    </div>
                    <small class="text-muted d-block mt-1">Redireciona /admin, /painel e auth para o subdominio App em producao.</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="cdn_enabled" value="0">
                        <input type="checkbox" class="form-check-input" id="cdn_enabled" name="cdn_enabled" value="1"
                            {{ !empty($settings['cdn_enabled']) ? 'checked' : '' }}>
                        <label class="form-check-label" for="cdn_enabled">Ativar subdominio CDN sem cookies</label>
                    </div>
                    <small class="text-muted d-block mt-1">Serve imagens, CSS/JS e uploads via dominio estatico.</small>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="public_page_cache_enabled" value="0">
                        <input type="checkbox" class="form-check-input" id="public_page_cache_enabled" name="public_page_cache_enabled" value="1"
                            {{ ($settings['public_page_cache_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="public_page_cache_enabled">Cache de borda para visitantes anonimos</label>
                    </div>
                    <small class="text-muted d-block mt-1">Remove cookies nas paginas publicas para cache no Cloudflare/LSCache.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Dominio Principal (Landing)</label>
                    <input type="text" name="domain_main" class="form-control" placeholder="somosunn.com.br"
                        value="{{ $settings['domain_main'] ?? config('domains.main', 'somosunn.com.br') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Subdominio do Sistema (App)</label>
                    <input type="text" name="domain_app" class="form-control" placeholder="app.somosunn.com.br"
                        value="{{ $settings['domain_app'] ?? config('domains.app', 'app.somosunn.com.br') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Subdominio de API/Webhooks</label>
                    <input type="text" name="domain_api" class="form-control" placeholder="api.somosunn.com.br"
                        value="{{ $settings['domain_api'] ?? config('domains.api', 'api.somosunn.com.br') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Subdominio CDN (Assets)</label>
                    <input type="text" name="domain_cdn" class="form-control" placeholder="cdn.somosunn.com.br"
                        value="{{ $settings['domain_cdn'] ?? config('domains.cdn', 'cdn.somosunn.com.br') }}">
                </div>
                <div class="col-md-12 form-group mb-0">
                    <label>URL base personalizada do CDN (opcional, sobrescreve o subdominio)</label>
                    <input type="url" name="cdn_url" class="form-control" placeholder="https://cdn.somosunn.com.br"
                        value="{{ $settings['cdn_url'] ?? '' }}">
                </div>
            </div>
        </div>
    </div>

    {{-- GOOGLE MEU NEGÓCIO --}}
    <div class="card card-outline card-info mb-4">
        <div class="card-header">
            <h3 class="card-title fw-bold">
                <i class="fab fa-google me-2"></i> Google Meu Negócio (Depoimentos)
            </h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Configure o ID do local e a chave da API do Google para importar automaticamente os reviews
                do Google Meu Negócio na seção de <strong>Depoimentos</strong>.
                <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener" class="ms-1">
                    <i class="fas fa-external-link-alt fa-xs"></i> Como encontrar o Place ID
                </a>
            </p>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="google_business_place_id">Place ID do seu negócio</label>
                    <input type="text" class="form-control"
                        id="google_business_place_id"
                        name="google_business_place_id"
                        placeholder="Ex.: ChIJ..."
                        value="{{ $settings['google_business_place_id'] ?? '' }}">
                    <small class="form-text text-muted">
                        Encontre em
                        <a href="https://maps.google.com" target="_blank">maps.google.com</a>
                        → pesquise seu negócio → compartilhe → copie o link (contém o Place ID).
                    </small>
                </div>
                <div class="col-md-6 form-group">
                    <label for="google_places_api_key">Chave da API Google Places</label>
                    <input type="text" class="form-control"
                        id="google_places_api_key"
                        name="google_places_api_key"
                        placeholder="AIza..."
                        value="{{ $settings['google_places_api_key'] ?? '' }}">
                    <small class="form-text text-muted">
                        Crie em <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a>.
                        Ative a API <em>Places (New)</em>.
                    </small>
                </div>
            </div>
            <div class="alert alert-warning py-2 mb-0 small">
                <i class="fas fa-exclamation-triangle me-1"></i>
                A API pública do Google Places retorna os <strong>5 reviews mais recentes</strong>.
                Após salvar as configurações, vá em <strong>Depoimentos → Importar do Google</strong>.
            </div>
        </div>
    </div>

</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('[data-maintenance-action]').on('click', function () {
                const action = $(this).data('maintenance-action');
                const $manual = $('#maintenance_enabled');
                const $auto = $('#maintenance_auto_enabled');
                const $form = $(this).closest('form');

                if (!$form.length) return;

                if (action === 'manual-on') $manual.prop('checked', true);
                if (action === 'manual-off') $manual.prop('checked', false);
                if (action === 'auto-on') $auto.prop('checked', true);
                if (action === 'auto-off') $auto.prop('checked', false);

                $form.trigger('submit');
            });

            $('#btnAddCurrentIpAdmin').on('click', function () {
                const currentIp = @json(request()->header('CF-Connecting-IP') ?: request()->ip());
                const $area = $('#maintenance_allowed_ips');
                if (currentIp && $area.length && !$area.val().includes(currentIp)) {
                    $area.val(($area.val().trim() ? $area.val().trim() + '\n' : '') + currentIp);
                }
            });

            $('#btnAuthorizeMaintenanceDeviceAdmin').on('click', function () {
                const $btn = $(this).prop('disabled', true);
                fetch(@json(route('admin.settings.authorize-device')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.bypass_token) $('#maintenance_bypass_token').val(data.bypass_token);
                    if (typeof toastr !== 'undefined') toastr.success(data.message || 'Dispositivo autorizado.');
                })
                .catch(() => {
                    if (typeof toastr !== 'undefined') toastr.error('Falha ao autorizar dispositivo.');
                })
                .finally(() => { $btn.prop('disabled', false); });
            });
        });
    </script>
@endpush
