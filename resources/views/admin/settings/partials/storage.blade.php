{{-- ============================================================
     ARMAZENAMENTO — Configuracao S3 / IDrive e2 (Admin Legado / AdminLTE)
     ============================================================ --}}

@php
    $storageDriver = $settings['storage_driver'] ?? 'public';
    $isS3Active = $storageDriver === 's3';
    $secretKey = $settings['storage_secret_key'] ?? '';
    $accessKey = $settings['storage_access_key'] ?? '';
    $maskedSecret = $secretKey !== '' ? str_repeat('*', max(0, strlen($secretKey) - 4)) . substr($secretKey, -4) : '';
    $pathStyle = (int) ($settings['storage_path_style'] ?? 1) === 1;
@endphp

{{-- HEADER --}}
<div class="card-header bg-gradient-info">
    <div class="d-flex align-items-center">
        <div class="mr-3">
            <i class="fas fa-cloud fa-2x text-white"></i>
        </div>
        <div class="flex-grow-1">
            <h3 class="card-title font-weight-bold text-white mb-0">Armazenamento</h3>
            <p class="text-white-50 mb-0 small">Configure S3 / IDrive e2 para armazenar arquivos na nuvem</p>
        </div>
        <div>
            @if($isS3Active)
                <span class="badge badge-success px-3 py-2">
                    <i class="fas fa-check-circle mr-1"></i> S3 Ativo
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

    {{-- Iscas anti-autofill (Chrome ignora se nao tiver) --}}
    <div style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden;" aria-hidden="true">
        <input type="text" name="fake_username" autocomplete="username" tabindex="-1">
        <input type="password" name="fake_password" autocomplete="current-password" tabindex="-1">
    </div>

    {{-- DRIVER --}}
    <div class="form-group">
        <label class="font-weight-bold">Driver de Armazenamento</label>
        <select name="storage_driver" id="storage_driver_legacy" class="form-control">
            <option value="public" {{ $storageDriver !== 's3' ? 'selected' : '' }}>Local (disco publico)</option>
            <option value="s3" {{ $storageDriver === 's3' ? 'selected' : '' }}>S3 / IDrive e2</option>
        </select>
        <small class="form-text text-muted">
            <i class="fas fa-info-circle"></i> Ao selecionar S3, os uploads passarao a usar o bucket configurado abaixo.
        </small>
    </div>

    {{-- IDrive e2 SETUP GUIDE --}}
    <div class="alert alert-info">
        <h6 class="font-weight-bold mb-2"><i class="fas fa-info-circle mr-1"></i> Como obter credenciais IDrive e2:</h6>
        <ol class="mb-2 pl-3 small">
            <li>Acesse <a href="https://www.idrive.com/s3-storage-e2/" target="_blank" class="font-weight-bold">idrive.com/s3-storage-e2</a> e crie uma conta</li>
            <li>Crie um Bucket no painel IDrive e2</li>
            <li>Em "Access Keys", gere um par de chaves (Access Key + Secret Key)</li>
            <li>Copie o Endpoint URL fornecido (formato: https://xxxx.e2.cloud.idrive.com)</li>
            <li>Cole as credenciais nos campos abaixo e clique em "Testar Conexao"</li>
        </ol>
        <small><i class="fas fa-shield-alt mr-1"></i> Suas credenciais sao armazenadas com seguranca no banco de dados.</small>
    </div>

    {{-- S3 FIELDS --}}
    <fieldset id="s3-fields-legacy" {{ $storageDriver !== 's3' ? 'disabled' : '' }} style="{{ $storageDriver !== 's3' ? 'opacity:0.6' : '' }}">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Bucket</label>
                    <input type="text" name="storage_bucket" class="form-control"
                        value="{{ $settings['storage_bucket'] ?? '' }}"
                        placeholder="meu-bucket"
                        autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                        data-lpignore="true" data-1p-ignore="true">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Regiao (Region)</label>
                    <input type="text" name="storage_region" class="form-control"
                        value="{{ $settings['storage_region'] ?? 'us-east-1' }}"
                        placeholder="us-east-1"
                        autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                        data-lpignore="true" data-1p-ignore="true">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold">Endpoint</label>
            <input type="text" name="storage_endpoint" class="form-control"
                value="{{ $settings['storage_endpoint'] ?? '' }}"
                placeholder="https://xxxx.e2.cloud.idrive.com"
                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                data-lpignore="true" data-1p-ignore="true">
            <small class="form-text text-muted">Para IDrive e2, use o endpoint fornecido no painel. Para AWS S3, deixe em branco.</small>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Access Key</label>
                    <input type="text" name="storage_access_key" class="form-control text-monospace"
                        value="{{ $accessKey }}"
                        placeholder="AKIAIOSFODNN7EXAMPLE"
                        autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                        data-lpignore="true" data-1p-ignore="true">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold">Secret Key</label>
                    <div class="input-group">
                        <input type="password" name="storage_secret_key" id="storage_secret_key_legacy"
                            class="form-control text-monospace"
                            value="{{ $secretKey }}"
                            placeholder="{{ $maskedSecret !== '' ? $maskedSecret : '••••••••••••••••' }}"
                            autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                            data-lpignore="true" data-1p-ignore="true">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" onclick="stgRevealSecretLegacy()">
                                <i class="fas fa-eye" id="stg-reveal-icon-legacy"></i>
                            </button>
                        </div>
                    </div>
                    @if($maskedSecret !== '')
                        <small class="form-text text-muted"><i class="fas fa-lock"></i> Salvo: {{ $maskedSecret }}</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="font-weight-bold">URL Publica dos Arquivos</label>
            <input type="text" name="storage_url" class="form-control text-monospace"
                value="{{ $settings['storage_url'] ?? '' }}"
                placeholder="https://meu-bucket.s3.us-east-1.amazonaws.com"
                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                data-lpignore="true" data-1p-ignore="true">
            <small class="form-text text-muted">URL base para acesso publico aos arquivos. Deixe em branco para usar o padrao do provider.</small>
        </div>

        <div class="form-group">
            <input type="hidden" name="storage_path_style" value="0">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="storage_path_style" value="1" id="storage_path_style_legacy"
                    class="custom-control-input" {{ $pathStyle ? 'checked' : '' }}>
                <label class="custom-control-label font-weight-bold" for="storage_path_style_legacy">
                    Path Style Endpoint
                </label>
                <small class="form-text text-muted d-block">Ative para provedores S3-compativeis (IDrive e2, MinIO, DigitalOcean Spaces). Desative para AWS S3 padrao.</small>
            </div>
        </div>

        {{-- TEST BUTTON --}}
        <button type="button" class="btn btn-success btn-block" onclick="stgTestS3Legacy()" id="btn-test-s3-legacy">
            <i class="fas fa-plug mr-1"></i> Testar Conexao S3
        </button>

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
    </fieldset>

    {{-- MIGRACAO DE ARQUIVOS --}}
    <hr class="my-4">

    <div class="card border-primary">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0 font-weight-bold">
                <i class="fas fa-cloud-upload-alt mr-1 text-primary"></i> Migracao de Arquivos
            </h5>
            <small class="text-muted">Envie arquivos locais para o S3 sem sair do painel</small>
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
    @php
        $stgDefaults = [
            'storage_bucket' => (string) ($settings['storage_bucket'] ?? ''),
            'storage_region' => (string) ($settings['storage_region'] ?? 'us-east-1'),
            'storage_endpoint' => (string) ($settings['storage_endpoint'] ?? ''),
            'storage_access_key' => (string) $accessKey,
            'storage_secret_key' => (string) $secretKey,
            'storage_url' => (string) ($settings['storage_url'] ?? ''),
        ];
    @endphp
    var stgDefaults = {!! json_encode($stgDefaults, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

    function restoreStgDefaults() {
        Object.keys(stgDefaults).forEach(function(name) {
            var el = document.querySelector('[name="' + name + '"]');
            if (!el) return;
            if (!el.dataset.userEdited && el.value !== stgDefaults[name]) {
                el.value = stgDefaults[name];
            }
        });
    }

    Object.keys(stgDefaults).forEach(function(name) {
        var el = document.querySelector('[name="' + name + '"]');
        if (el) {
            el.addEventListener('input', function() { el.dataset.userEdited = '1'; });
        }
    });

    setTimeout(restoreStgDefaults, 100);
    setTimeout(restoreStgDefaults, 600);
    setTimeout(restoreStgDefaults, 1500);
})();
</script>
@endpush

@push('scripts')
<script>
(function() {
    var driverSelect = document.getElementById('storage_driver_legacy');
    var s3Fields = document.getElementById('s3-fields-legacy');

    if (driverSelect && s3Fields) {
        driverSelect.addEventListener('change', function() {
            if (this.value === 's3') {
                s3Fields.removeAttribute('disabled');
                s3Fields.style.opacity = '1';
            } else {
                s3Fields.setAttribute('disabled', 'disabled');
                s3Fields.style.opacity = '0.6';
            }
        });
    }

    window.stgRevealSecretLegacy = function() {
        var input = document.getElementById('storage_secret_key_legacy');
        var icon = document.getElementById('stg-reveal-icon-legacy');
        if (!input) return;
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

    window.stgTestS3Legacy = function() {
        var btn = document.getElementById('btn-test-s3-legacy');
        var resultsDiv = document.getElementById('s3-test-results-legacy');
        var stepsDiv = document.getElementById('s3-test-steps-legacy');
        var titleEl = document.getElementById('s3-test-title-legacy');
        var spinnerEl = document.getElementById('s3-test-spinner-legacy');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando...';

        resultsDiv.classList.remove('d-none');
        stepsDiv.innerHTML = '';
        titleEl.textContent = 'Testando conexao...';
        spinnerEl.className = 'fas fa-circle-notch fa-spin text-info mr-2';

        fetch('{{ route("admin.settings.test-s3") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin',
            body: JSON.stringify({})
        })
        .then(function(r) { return r.json(); })
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
            titleEl.textContent = 'Erro de conexao: ' + err.message;
            spinnerEl.className = 'fas fa-times-circle text-danger mr-2';
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plug mr-1"></i> Testar Conexao S3';
        });
    };

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
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-folder-open mr-1"></i> Atualizar Lista';

            if (!data.success || !data.folders || data.folders.length === 0) {
                list.innerHTML = '<div class="alert alert-warning small mb-0">Nenhuma pasta encontrada.</div>';
                list.classList.remove('d-none');
                return;
            }

            var html = '<div class="list-group">';
            data.folders.forEach(function(folder) {
                var label = getFolderLabel(folder.name);
                html += '<div class="list-group-item d-flex justify-content-between align-items-center">';
                html += '<div><div class="font-weight-bold">' + label + '</div>';
                html += '<small class="text-muted">' + folder.name + ' &bull; ' + folder.files + ' arquivos &bull; ' + folder.size_formatted + '</small></div>';
                html += '<div>';
                html += '<button type="button" class="btn btn-sm btn-info mr-1" onclick="stgMigrateFolderLegacy(\'' + folder.name + '\', false)" title="Copiar para S3"><i class="fas fa-copy"></i></button>';
                html += '<button type="button" class="btn btn-sm btn-danger" onclick="stgMigrateFolderLegacy(\'' + folder.name + '\', true)" title="Mover para S3 (apagar local)"><i class="fas fa-cloud-upload-alt"></i></button>';
                html += '</div></div>';
            });
            html += '</div>';
            html += '<button type="button" class="btn btn-danger btn-block mt-3" onclick="stgMigrateAllLegacy()"><i class="fas fa-cloud-upload-alt mr-1"></i> Migrar TUDO para S3 (apagar local)</button>';

            list.innerHTML = html;
            list.classList.remove('d-none');
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-folder-open mr-1"></i> Listar Pastas Disponiveis';
            if (typeof toastr !== 'undefined') toastr.error('Erro ao carregar pastas.');
        });
    };

    window.stgMigrateFolderLegacy = function(path, deleteLocal) {
        var action = deleteLocal ? 'MOVER (apagar local)' : 'COPIAR (manter local)';
        var msg = deleteLocal
            ? 'Os arquivos locais serao apagados apos confirmacao no S3.'
            : 'Os arquivos serao copiados para o S3. O local sera mantido.';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: action + ' pasta "' + path + '"?',
                text: msg, icon: 'question',
                showCancelButton: true,
                confirmButtonColor: deleteLocal ? '#dc3545' : '#17a2b8',
                cancelButtonColor: '#6c757d',
                confirmButtonText: deleteLocal ? 'Mover' : 'Copiar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) stgRunMigrationLegacy(path, deleteLocal);
            });
        } else if (confirm(action + ' pasta "' + path + '"? ' + msg)) {
            stgRunMigrationLegacy(path, deleteLocal);
        }
    };

    window.stgMigrateAllLegacy = function() {
        var msg = 'Todos os arquivos locais serao enviados para o S3 e apagados do servidor. Esta acao nao pode ser desfeita.';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Migrar TODOS os arquivos?',
                text: msg, icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#dc3545',
                confirmButtonText: 'Sim, migrar tudo', cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) stgRunMigrationLegacy('', true);
            });
        } else if (confirm('Migrar TODOS os arquivos? ' + msg)) {
            stgRunMigrationLegacy('', true);
        }
    };

    window.stgRunMigrationLegacy = function(path, deleteLocal) {
        var progress = document.getElementById('stg-migration-progress-legacy');
        var spinner = document.getElementById('stg-mig-spinner-legacy');
        var status = document.getElementById('stg-mig-status-legacy');
        var resultDiv = document.getElementById('stg-mig-result-legacy');

        progress.classList.remove('d-none');
        spinner.className = 'fas fa-spinner fa-spin mr-2';
        status.textContent = 'Migrando ' + (path || 'todos os arquivos') + '...';
        resultDiv.textContent = '';

        fetch('{{ route("admin.settings.storage.migrate") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ path: path, delete_local: deleteLocal })
        })
        .then(function(r) { return r.json(); })
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
