{{-- ============================================================
     ARMAZENAMENTO — Configuracao S3 multi-provider (Admin Legado / AdminLTE)
     Suporta IDrive e2, Wasabi e AWS S3 com configuracoes independentes.
     Spec: .kiro/specs/multi-provider-s3-storage
     ============================================================ --}}

@php
    use App\Support\StorageProviderRegistry;

    // Resolve provedor ativo via Registry (com fallback robusto).
    $activeProvider = 'public';
    try {
        $registry = app(StorageProviderRegistry::class);
        $stored = (string) ($settings['storage_active_provider'] ?? '');
        $driver = (string) ($settings['storage_driver'] ?? 'public');
        if ($driver === 's3') {
            $activeProvider = in_array($stored, StorageProviderRegistry::PROVIDERS, true)
                ? $stored
                : StorageProviderRegistry::DEFAULT_PROVIDER;
        } else {
            $activeProvider = 'public';
        }
    } catch (\Throwable $e) {
        $activeProvider = 'public';
    }

    $isS3Active = $activeProvider !== 'public';

    // Provedores disponiveis (chave => label).
    $providerOptions = [
        'public' => 'Local (disco publico)',
        'idrive' => 'IDrive e2',
        'wasabi' => 'Wasabi',
        'aws'    => 'AWS S3',
    ];

    // Para cada provedor, monta os valores atuais dos campos.
    $providerData = [];
    foreach (['idrive', 'wasabi', 'aws'] as $p) {
        $sk = (string) ($settings[$p . '_secret_key'] ?? '');
        $providerData[$p] = [
            'access_key' => (string) ($settings[$p . '_access_key'] ?? ''),
            'secret_key' => $sk,
            'masked_secret' => $sk !== '' ? str_repeat('*', max(0, strlen($sk) - 4)) . substr($sk, -4) : '',
            'bucket' => (string) ($settings[$p . '_bucket'] ?? ''),
            'region' => (string) ($settings[$p . '_region'] ?? ''),
            'endpoint' => (string) ($settings[$p . '_endpoint'] ?? ''),
            'url' => (string) ($settings[$p . '_url'] ?? ''),
            'path_style' => (int) ($settings[$p . '_path_style'] ?? ($p === 'aws' ? 0 : 1)) === 1,
        ];
    }

    // Hints / placeholders por provedor.
    $providerHints = [
        'idrive' => [
            'endpoint_placeholder' => 'https://b1l1.la4.idrivee2-XX.com',
            'region_placeholder' => 'us-east-1',
            'help' => 'Path Style ativado e recomendado. Endpoint fornecido no painel IDrive e2.',
            'docs_url' => 'https://www.idrive.com/s3-storage-e2/',
        ],
        'wasabi' => [
            'endpoint_placeholder' => 's3.us-east-1.wasabisys.com',
            'region_placeholder' => 'us-east-1',
            'help' => 'Endpoint regional, ex.: s3.us-east-1.wasabisys.com (sem https://). Path Style ativado.',
            'docs_url' => 'https://wasabi-support.zendesk.com/hc/en-us/articles/360015106031',
        ],
        'aws' => [
            'endpoint_placeholder' => '(deixe vazio para usar o endpoint padrao da AWS)',
            'region_placeholder' => 'us-east-1',
            'help' => 'Endpoint pode ficar vazio (usa endpoint padrao da AWS). Path Style normalmente desativado.',
            'docs_url' => 'https://docs.aws.amazon.com/general/latest/gr/s3.html',
        ],
    ];
@endphp

{{-- HEADER --}}
<div class="card-header bg-gradient-info">
    <div class="d-flex align-items-center">
        <div class="mr-3">
            <i class="fas fa-cloud fa-2x text-white"></i>
        </div>
        <div class="flex-grow-1">
            <h3 class="card-title font-weight-bold text-white mb-0">Armazenamento</h3>
            <p class="text-white-50 mb-0 small">Configure IDrive e2, Wasabi ou AWS S3 (apenas um ativo por vez)</p>
        </div>
        <div>
            @if($isS3Active)
                <span class="badge badge-success px-3 py-2">
                    <i class="fas fa-check-circle mr-1"></i> {{ $providerOptions[$activeProvider] }} Ativo
                </span>
            @else
                <span class="badge badge-secondary px-3 py-2">
                    <i class="fas fa-hdd mr-1"></i> Local
                </span>
            @endif
        </div>
    </div>
</div>

<div class="card-body">

    {{-- Iscas anti-autofill --}}
    <div style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden;" aria-hidden="true">
        <input type="text" name="fake_username" autocomplete="username" tabindex="-1">
        <input type="password" name="fake_password" autocomplete="current-password" tabindex="-1">
    </div>

    {{-- DRIVER + PROVEDOR ATIVO num unico select --}}
    <div class="form-group">
        <label class="font-weight-bold">Driver de Armazenamento</label>
        <select name="storage_active_choice" id="storage_active_choice_legacy" class="form-control">
            @foreach ($providerOptions as $key => $label)
                <option value="{{ $key }}" {{ $activeProvider === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        {{-- Hidden fields que o backend persiste (compativel com fluxo existente) --}}
        <input type="hidden" name="storage_driver" id="storage_driver_hidden" value="{{ $isS3Active ? 's3' : 'public' }}">
        <input type="hidden" name="storage_active_provider" id="storage_active_provider_hidden" value="{{ $isS3Active ? $activeProvider : 'idrive' }}">

        <small class="form-text text-muted">
            <i class="fas fa-info-circle"></i> Cada provedor S3 tem seu proprio conjunto de credenciais. Apenas o selecionado e usado em runtime; os demais ficam salvos para troca futura.
        </small>
    </div>

    {{-- Setup guide condicional --}}
    @foreach (['idrive', 'wasabi', 'aws'] as $p)
        <div class="alert alert-info storage-provider-help" data-provider="{{ $p }}" style="display: {{ $activeProvider === $p ? 'block' : 'none' }};">
            <h6 class="font-weight-bold mb-2"><i class="fas fa-info-circle mr-1"></i> {{ $providerOptions[$p] }}</h6>
            <p class="mb-1 small">{{ $providerHints[$p]['help'] }}</p>
            <small><a href="{{ $providerHints[$p]['docs_url'] }}" target="_blank" rel="noopener" class="font-weight-bold">Ver documentacao</a></small>
        </div>
    @endforeach

    {{-- FORM POR PROVEDOR (renderizado mas escondido se nao for o ativo) --}}
    @foreach (['idrive', 'wasabi', 'aws'] as $p)
        @php $pd = $providerData[$p]; @endphp
        <fieldset class="storage-provider-fields" data-provider="{{ $p }}"
                  style="display: {{ $activeProvider === $p ? 'block' : 'none' }};">

            <legend class="text-info" style="font-size: 1rem; border-bottom: 1px solid #ddd; padding-bottom: 6px;">
                <i class="fas fa-cog"></i> Credenciais {{ $providerOptions[$p] }}
            </legend>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Bucket</label>
                        <input type="text" name="{{ $p }}_bucket" class="form-control"
                            value="{{ $pd['bucket'] }}"
                            placeholder="meu-bucket"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                            data-lpignore="true" data-1p-ignore="true">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Regiao (Region)</label>
                        <input type="text" name="{{ $p }}_region" class="form-control"
                            value="{{ $pd['region'] }}"
                            placeholder="{{ $providerHints[$p]['region_placeholder'] }}"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                            data-lpignore="true" data-1p-ignore="true">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Endpoint</label>
                <input type="text" name="{{ $p }}_endpoint" class="form-control"
                    value="{{ $pd['endpoint'] }}"
                    placeholder="{{ $providerHints[$p]['endpoint_placeholder'] }}"
                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                    data-lpignore="true" data-1p-ignore="true">
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Access Key</label>
                        <input type="text" name="{{ $p }}_access_key" class="form-control text-monospace"
                            value="{{ $pd['access_key'] }}"
                            placeholder="AKIAIOSFODNN7EXAMPLE"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                            data-lpignore="true" data-1p-ignore="true">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Secret Key</label>
                        <div class="input-group">
                            <input type="password" name="{{ $p }}_secret_key" id="{{ $p }}_secret_key_legacy"
                                class="form-control text-monospace"
                                value="{{ $pd['secret_key'] }}"
                                placeholder="{{ $pd['masked_secret'] !== '' ? $pd['masked_secret'] : '••••••••••••••••' }}"
                                autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                                data-lpignore="true" data-1p-ignore="true">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" onclick="stgRevealSecretLegacy('{{ $p }}')">
                                    <i class="fas fa-eye" id="stg-reveal-icon-legacy-{{ $p }}"></i>
                                </button>
                            </div>
                        </div>
                        @if($pd['masked_secret'] !== '')
                            <small class="form-text text-muted"><i class="fas fa-lock"></i> Salvo: {{ $pd['masked_secret'] }}</small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">URL Publica dos Arquivos</label>
                <input type="text" name="{{ $p }}_url" class="form-control text-monospace"
                    value="{{ $pd['url'] }}"
                    placeholder="(opcional)"
                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                    data-lpignore="true" data-1p-ignore="true">
                <small class="form-text text-muted">URL base para acesso publico aos arquivos. Vazio = padrao do provider.</small>
            </div>

            <div class="form-group">
                <input type="hidden" name="{{ $p }}_path_style" value="0">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="{{ $p }}_path_style" value="1" id="{{ $p }}_path_style_legacy"
                        class="custom-control-input" {{ $pd['path_style'] ? 'checked' : '' }}>
                    <label class="custom-control-label font-weight-bold" for="{{ $p }}_path_style_legacy">
                        Path Style Endpoint
                    </label>
                </div>
            </div>

            <button type="button" class="btn btn-success btn-block stg-test-btn-legacy"
                    data-provider="{{ $p }}">
                <i class="fas fa-plug mr-1"></i> Testar Conexao {{ $providerOptions[$p] }}
            </button>
        </fieldset>
    @endforeach

    {{-- TEST RESULTS --}}
    <div id="s3-test-results-legacy" class="mt-3 d-none">
        <div class="card border-info">
            <div class="card-header py-2">
                <i class="fas fa-circle-notch fa-spin text-info mr-2" id="s3-test-spinner-legacy"></i>
                <span class="font-weight-bold" id="s3-test-title-legacy">Testando...</span>
            </div>
            <div class="card-body py-2" id="s3-test-steps-legacy"></div>
        </div>
    </div>

    {{-- MIGRACAO DE ARQUIVOS --}}
    <hr class="my-4">

    <div class="card border-primary">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0 font-weight-bold">
                <i class="fas fa-cloud-upload-alt mr-1 text-primary"></i> Migracao de Arquivos
            </h5>
            <small class="text-muted">Envie arquivos locais para o provedor ativo</small>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-primary btn-block" onclick="stgLoadFoldersLegacy()" id="btn-load-folders-legacy">
                <i class="fas fa-folder-open mr-1"></i> Listar Pastas Disponiveis
            </button>

            <div id="stg-folders-list-legacy" class="d-none mt-3"></div>

            <div id="stg-migration-progress-legacy" class="d-none mt-3 alert alert-info">
                <i class="fas fa-spinner fa-spin mr-2" id="stg-mig-spinner-legacy"></i>
                <span class="font-weight-bold" id="stg-mig-status-legacy">Migrando...</span>
                <div class="small mt-1" id="stg-mig-result-legacy"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    'use strict';

    // 1) Toggle de visibilidade dos fields de cada provedor
    var choiceSelect = document.getElementById('storage_active_choice_legacy');
    var driverHidden = document.getElementById('storage_driver_hidden');
    var providerHidden = document.getElementById('storage_active_provider_hidden');

    function updateActiveProviderUI(value) {
        var fieldsets = document.querySelectorAll('.storage-provider-fields');
        var helps = document.querySelectorAll('.storage-provider-help');

        fieldsets.forEach(function(fs) { fs.style.display = 'none'; });
        helps.forEach(function(h) { h.style.display = 'none'; });

        if (value === 'public' || !value) {
            driverHidden.value = 'public';
        } else {
            driverHidden.value = 's3';
            providerHidden.value = value;

            var fs = document.querySelector('.storage-provider-fields[data-provider="' + value + '"]');
            var h = document.querySelector('.storage-provider-help[data-provider="' + value + '"]');
            if (fs) fs.style.display = 'block';
            if (h) h.style.display = 'block';
        }
    }

    if (choiceSelect) {
        choiceSelect.addEventListener('change', function() {
            updateActiveProviderUI(this.value);
        });
    }

    // 2) Toggle Secret Key
    window.stgRevealSecretLegacy = function(provider) {
        var input = document.getElementById(provider + '_secret_key_legacy');
        var icon = document.getElementById('stg-reveal-icon-legacy-' + provider);
        if (!input || !icon) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    };

    // 3) Test Connection por provedor (AJAX)
    document.querySelectorAll('.stg-test-btn-legacy').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var provider = btn.dataset.provider;
            stgTestProviderLegacy(provider, btn);
        });
    });

    function stgTestProviderLegacy(provider, btn) {
        var resultsDiv = document.getElementById('s3-test-results-legacy');
        var stepsDiv = document.getElementById('s3-test-steps-legacy');
        var titleEl = document.getElementById('s3-test-title-legacy');
        var spinnerEl = document.getElementById('s3-test-spinner-legacy');

        btn.disabled = true;
        var oldText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando...';

        resultsDiv.classList.remove('d-none');
        stepsDiv.innerHTML = '';
        titleEl.textContent = 'Testando conexao com ' + provider.toUpperCase() + '...';
        spinnerEl.className = 'fas fa-circle-notch fa-spin text-info mr-2';

        var formData = new FormData();
        formData.append('provider', provider);

        fetch('{{ route("admin.settings.test-s3-provider") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin',
            body: formData
        })
        .then(function(r) {
            if (!r.ok && r.status === 419) {
                throw new Error('Sessao expirada. Recarregue a pagina (F5).');
            }
            var ct = r.headers.get('content-type') || '';
            if (ct.indexOf('application/json') === -1) {
                throw new Error('Resposta inesperada (HTTP ' + r.status + ').');
            }
            return r.json();
        })
        .then(function(data) {
            if (data.success) {
                titleEl.textContent = data.message || 'Sucesso!';
                spinnerEl.className = 'fas fa-check-circle text-success mr-2';
            } else {
                titleEl.textContent = data.message || 'Falha na conexao';
                spinnerEl.className = 'fas fa-times-circle text-danger mr-2';
            }

            if (data.results && data.results.length > 0) {
                var html = '<ul class="list-unstyled mb-0 small">';
                data.results.forEach(function(r) {
                    var iconClass = r.status === 'ok' ? 'fa-check text-success' : (r.status === 'aviso' ? 'fa-exclamation-triangle text-warning' : 'fa-times text-danger');
                    html += '<li class="mb-1"><i class="fas ' + iconClass + ' mr-2"></i><strong>' + r.step + ':</strong> ' + r.detail + '</li>';
                });
                html += '</ul>';
                stepsDiv.innerHTML = html;
            }
        })
        .catch(function(err) {
            titleEl.textContent = 'Erro: ' + err.message;
            spinnerEl.className = 'fas fa-times-circle text-danger mr-2';
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = oldText;
        });
    }

    // 4) Migracao de arquivos
    var folderLabels = {
        'event-images': 'Imagens de Eventos',
        'course-thumbs': 'Capas de Cursos',
        'course-materials': 'Materiais de Cursos',
        'course-videos': 'Videos de Cursos',
        'uploads': 'Uploads Gerais',
        'magazines': 'Revistas',
        'events': 'Galeria de Eventos',
        'pages': 'Paginas',
        'branding': 'Identidade Visual',
        'certificates': 'Certificados',
        'partners': 'Parceiros',
        'plan-images': 'Imagens de Planos',
        'pwa-icons': 'Icones PWA',
        'resumes': 'Curriculos',
        'jobs': 'Vagas',
        'redemptions': 'Resgates',
    };
    function getFolderLabel(name) { return folderLabels[name] || name; }

    window.stgLoadFoldersLegacy = function() {
        var btn = document.getElementById('btn-load-folders-legacy');
        var list = document.getElementById('stg-folders-list-legacy');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Carregando...';

        fetch('{{ route("admin.settings.storage.folders") }}', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success || !Array.isArray(data.folders) || data.folders.length === 0) {
                list.innerHTML = '<div class="alert alert-warning small">Nenhuma pasta com arquivos para migrar.</div>';
                list.classList.remove('d-none');
                return;
            }

            var html = '<ul class="list-group">';
            data.folders.forEach(function(f) {
                html += '<li class="list-group-item d-flex justify-content-between align-items-center">'
                     +   '<div><strong>' + getFolderLabel(f.name) + '</strong>'
                     +     '<br><small class="text-muted">' + f.name + ' - ' + (f.files_count || 0) + ' arquivo(s) - ' + (f.size_human || '') + '</small></div>'
                     +   '<div class="btn-group btn-group-sm">'
                     +     '<button type="button" class="btn btn-outline-primary" onclick="stgMigrateLegacy(\'' + f.name + '\', \'copy\')"><i class="fas fa-copy"></i> Copiar</button>'
                     +     '<button type="button" class="btn btn-outline-warning" onclick="stgMigrateLegacy(\'' + f.name + '\', \'move\')"><i class="fas fa-arrow-right"></i> Mover</button>'
                     +   '</div>'
                     + '</li>';
            });
            html += '</ul>';
            list.innerHTML = html;
            list.classList.remove('d-none');
        })
        .catch(function(err) {
            list.innerHTML = '<div class="alert alert-danger small">Erro: ' + err.message + '</div>';
            list.classList.remove('d-none');
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-folder-open mr-1"></i> Listar Pastas Disponiveis';
        });
    };

    window.stgMigrateLegacy = function(folder, mode) {
        if (!confirm('Confirma ' + (mode === 'move' ? 'MOVER' : 'COPIAR') + ' a pasta "' + folder + '" para o provedor ativo?')) return;

        var progressDiv = document.getElementById('stg-migration-progress-legacy');
        var spinner = document.getElementById('stg-mig-spinner-legacy');
        var status = document.getElementById('stg-mig-status-legacy');
        var resultDiv = document.getElementById('stg-mig-result-legacy');

        progressDiv.classList.remove('d-none');
        spinner.className = 'fas fa-spinner fa-spin mr-2';
        status.textContent = 'Migrando ' + folder + '...';
        resultDiv.textContent = '';

        var formData = new FormData();
        formData.append('folder', folder);
        formData.append('mode', mode);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("admin.settings.storage.migrate") }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            body: formData
        })
        .then(function(r) {
            var ct = r.headers.get('content-type') || '';
            if (ct.indexOf('application/json') === -1) {
                throw new Error('Resposta inesperada do servidor (HTTP ' + r.status + ').');
            }
            return r.json();
        })
        .then(function(data) {
            if (data.success) {
                spinner.className = 'fas fa-check-circle text-success mr-2';
                status.textContent = 'Concluido!';
                resultDiv.textContent = data.message;
                if (typeof toastr !== 'undefined') toastr.success(data.message);
                setTimeout(stgLoadFoldersLegacy, 1500);
            } else {
                spinner.className = 'fas fa-times-circle text-danger mr-2';
                status.textContent = 'Falha';
                resultDiv.textContent = data.message;
                if (typeof toastr !== 'undefined') toastr.error(data.message);
            }
        })
        .catch(function(err) {
            spinner.className = 'fas fa-times-circle text-danger mr-2';
            status.textContent = 'Erro de conexao';
            resultDiv.textContent = err.message;
        });
    };
})();
</script>
@endpush
