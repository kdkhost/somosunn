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
        <i class="fas fa-cogs mr-2"></i> Configurações globais do sistema, segurança e armazenamento de arquivos.
    </div>

    {{-- SEGURANÇA --}}
    <div class="card card-outline card-danger mb-4">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-shield-alt mr-2"></i> Segurança (Google reCAPTCHA
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
    <h5 class="text-primary mb-3"><i class="fas fa-server mr-2"></i> Limites e Uploads</h5>
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
            <h3 class="card-title font-weight-bold"><i class="fas fa-hdd mr-2"></i> Armazenamento (Filesystem)</h3>
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
            <h3 class="card-title font-weight-bold mb-0">
                <i class="fas fa-tools mr-2"></i> Modo de manutencao
            </h3>
            @if($maintenanceEffective)
                <span class="badge badge-warning px-3 py-2">ATIVO NO SITE</span>
            @else
                <span class="badge badge-success px-3 py-2">DESATIVADO</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="border rounded p-3 mb-3 h-100">
                        <h6 class="font-weight-bold">Ativacao manual imediata</h6>
                        <p class="text-muted small mb-3">Forca o site inteiro para manutencao agora.</p>
                        <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                            <input type="hidden" name="maintenance_enabled" value="0">
                            <input type="checkbox" class="custom-control-input" id="maintenance_enabled" name="maintenance_enabled"
                                value="1" {{ $maintenanceManualEnabled ? 'checked' : '' }}>
                            <label class="custom-control-label" for="maintenance_enabled">Ativar manutencao manual</label>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-warning btn-sm mr-2 mb-2" data-maintenance-action="manual-on">
                                <i class="fas fa-power-off mr-1"></i> Ativar agora
                            </button>
                            <button type="button" class="btn btn-success btn-sm mb-2" data-maintenance-action="manual-off">
                                <i class="fas fa-check-circle mr-1"></i> Desativar agora
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="border rounded p-3 mb-3 h-100">
                        <h6 class="font-weight-bold">Ativacao automatica agendada</h6>
                        <p class="text-muted small mb-3">Define uma janela para ativar e desativar automaticamente.</p>
                        <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-3">
                            <input type="hidden" name="maintenance_auto_enabled" value="0">
                            <input type="checkbox" class="custom-control-input" id="maintenance_auto_enabled" name="maintenance_auto_enabled"
                                value="1" {{ $maintenanceAutoEnabled ? 'checked' : '' }}>
                            <label class="custom-control-label" for="maintenance_auto_enabled">Habilitar agendamento automatico</label>
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
                            <button type="button" class="btn btn-primary btn-sm mr-2 mb-2" data-maintenance-action="auto-on">
                                <i class="fas fa-clock mr-1"></i> Habilitar automatico
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm mb-2" data-maintenance-action="auto-off">
                                <i class="fas fa-ban mr-1"></i> Desabilitar automatico
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="text-primary mb-3"><i class="fas fa-paint-brush mr-2"></i> Personalizacao da pagina de manutencao</h5>
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
        </div>
    </div>

    {{-- GOOGLE MEU NEGÓCIO --}}
    <div class="card card-outline card-info mb-4">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fab fa-google mr-2"></i> Google Meu Negócio (Depoimentos)
            </h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Configure o ID do local e a chave da API do Google para importar automaticamente os reviews
                do Google Meu Negócio na seção de <strong>Depoimentos</strong>.
                <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" rel="noopener" class="ml-1">
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
                <i class="fas fa-exclamation-triangle mr-1"></i>
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
        });
    </script>
@endpush
