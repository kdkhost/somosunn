{{-- ============================================================
     ARMAZENAMENTO — Configuracao S3 / IDrive e2
     ============================================================ --}}

@php
    use App\Support\StorageProviderRegistry;

    // Resolve provedor ativo (idrive/wasabi/aws/public).
    $activeProvider = 'public';
    try {
        $stored = (string) ($settings['storage_active_provider'] ?? '');
        $driver = (string) ($settings['storage_driver'] ?? 'public');
        if ($driver === 's3') {
            $activeProvider = in_array($stored, ['idrive', 'wasabi', 'aws'], true)
                ? $stored
                : 'idrive';
        }
    } catch (\Throwable $e) {
        $activeProvider = 'public';
    }

    $storageDriver = $settings['storage_driver'] ?? 'public';
    $isS3Active = $storageDriver === 's3';

    // Resolve credenciais do provedor ativo (com fallback p/ schema legado storage_*).
    $providerKey = $activeProvider !== 'public' ? $activeProvider : 'idrive';
    $accessKey = (string) ($settings[$providerKey . '_access_key'] ?? $settings['storage_access_key'] ?? '');
    $secretKey = (string) ($settings[$providerKey . '_secret_key'] ?? $settings['storage_secret_key'] ?? '');
    $bucket    = (string) ($settings[$providerKey . '_bucket']     ?? $settings['storage_bucket']     ?? '');
    $region    = (string) ($settings[$providerKey . '_region']     ?? $settings['storage_region']    ?? 'us-east-1');
    $endpoint  = (string) ($settings[$providerKey . '_endpoint']   ?? $settings['storage_endpoint']  ?? '');
    $url       = (string) ($settings[$providerKey . '_url']        ?? $settings['storage_url']       ?? '');
    $pathStyle = (int) ($settings[$providerKey . '_path_style'] ?? $settings['storage_path_style'] ?? ($providerKey === 'aws' ? 0 : 1)) === 1;

    $maskedSecret = $secretKey !== '' ? str_repeat('*', max(0, strlen($secretKey) - 4)) . substr($secretKey, -4) : '';

    $providerOptions = [
        'public' => 'Local (disco publico)',
        'idrive' => 'IDrive e2',
        'wasabi' => 'Wasabi',
        'aws'    => 'AWS S3',
    ];
@endphp

<style>
.stg-input,
.stg-select {
    color: #0f172a !important;
    background-color: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
}
.dark .stg-input,
.dark .stg-select {
    color: #f1f5f9 !important;
    background-color: #1e293b !important;
    border-color: #334155 !important;
}
.stg-input:focus,
.stg-select:focus {
    outline: none;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
}
.stg-input::placeholder { color: #94a3b8; }
.dark .stg-input::placeholder { color: #64748b; }

.stg-card {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    padding: 1.25rem;
}
.dark .stg-card {
    background-color: #0f172a;
    border-color: #1e293b;
}

.stg-label {
    display: block;
    font-size: .65rem;
    font-weight: 900;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: .5rem;
}
.dark .stg-label { color: #94a3b8; }
</style>

{{-- HEADER --}}
<div class="relative overflow-hidden rounded-2xl mb-6"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);">
    <div class="relative p-6 flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center border border-white/10">
            <i class="fas fa-cloud text-white text-2xl"></i>
        </div>
        <div class="flex-1">
            <p class="text-slate-300 text-xs font-bold uppercase tracking-widest mb-0.5">Configuracoes</p>
            <h2 class="text-white text-2xl font-black leading-tight">Armazenamento</h2>
            <p class="text-slate-400 text-xs mt-1">Configure S3 / IDrive e2 para armazenar arquivos na nuvem</p>
        </div>
        <div class="flex items-center gap-2">
            @if($isS3Active)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-400/20 text-emerald-200 border border-emerald-400/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    S3 Ativo
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-400/20 text-slate-300 border border-slate-400/30">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    Local
                </span>
            @endif
        </div>
    </div>
</div>

{{-- DRIVER + PROVEDOR num unico select --}}
<div class="stg-card mb-4">
    <label class="stg-label">Driver de Armazenamento</label>
    <select name="storage_active_choice" id="storage_active_choice" class="stg-select w-full px-4 py-3 rounded-xl text-sm font-semibold">
        @foreach ($providerOptions as $key => $label)
            <option value="{{ $key }}" {{ ($isS3Active ? $activeProvider : 'public') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>

    {{-- Hidden fields que o backend persiste (compat fluxo existente) --}}
    <input type="hidden" name="storage_driver" id="storage_driver_hidden_panel" value="{{ $isS3Active ? 's3' : 'public' }}">
    <input type="hidden" name="storage_active_provider" id="storage_active_provider_hidden_panel" value="{{ $isS3Active ? $activeProvider : 'idrive' }}">

    <p class="text-xs text-slate-400 mt-2">
        <i class="fas fa-info-circle"></i>
        Cada provedor S3 tem seu proprio conjunto de credenciais. Apenas o selecionado e usado em runtime.
    </p>
</div>

{{-- IDrive e2 SETUP GUIDE --}}
<div class="rounded-2xl bg-blue-50 border border-blue-200 p-4 mb-4 dark:bg-blue-900/20 dark:border-blue-700">
    <div class="flex items-start gap-3">
        <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-1"></i>
        <div class="flex-1 text-sm">
            <p class="font-bold text-blue-800 dark:text-blue-200 mb-2">Como obter credenciais IDrive e2:</p>
            <ol class="list-decimal ml-5 space-y-1 text-blue-700 dark:text-blue-300">
                <li>Acesse <a href="https://www.idrive.com/s3-storage-e2/" target="_blank" class="underline font-semibold">idrive.com/s3-storage-e2</a> e crie uma conta</li>
                <li>Crie um Bucket no painel IDrive e2</li>
                <li>Em "Access Keys", gere um par de chaves (Access Key + Secret Key)</li>
                <li>Copie o Endpoint URL fornecido (formato: https://xxxx.e2.cloud.idrive.com)</li>
                <li>Cole as credenciais nos campos abaixo e clique em "Testar Conexao"</li>
            </ol>
            <p class="mt-2 text-xs text-blue-600 dark:text-blue-400">
                <i class="fas fa-shield-alt"></i> Suas credenciais sao armazenadas com seguranca no banco de dados.
            </p>
        </div>
    </div>
</div>

{{-- S3 FIELDS --}}
<div id="s3-fields" class="{{ $storageDriver !== 's3' ? 'opacity-50 pointer-events-none' : '' }} transition-all duration-300">
    {{-- Iscas anti-autofill: o Chrome se distrai com estes campos invisiveis e nao bagunca os reais --}}
    <div style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden;" aria-hidden="true">
        <input type="text" name="fake_user_for_chrome" autocomplete="username" tabindex="-1">
        <input type="password" name="fake_pass_for_chrome" autocomplete="current-password" tabindex="-1">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div class="stg-card">
            <label class="stg-label">Bucket</label>
            <input type="text" name="storage_bucket"
                value="{{ $settings['storage_bucket'] ?? '' }}"
                placeholder="meu-bucket"
                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                data-lpignore="true" data-1p-ignore="true" data-form-type="other"
                class="stg-input w-full px-4 py-3 rounded-xl text-sm">
        </div>
        <div class="stg-card">
            <label class="stg-label">Regiao (Region)</label>
            <input type="text" name="storage_region"
                value="{{ $settings['storage_region'] ?? 'us-east-1' }}"
                placeholder="us-east-1"
                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                data-lpignore="true" data-1p-ignore="true" data-form-type="other"
                class="stg-input w-full px-4 py-3 rounded-xl text-sm">
        </div>
    </div>

    <div class="stg-card mb-4">
        <label class="stg-label">Endpoint</label>
        <input type="text" name="storage_endpoint"
            value="{{ $settings['storage_endpoint'] ?? '' }}"
            placeholder="https://xxxx.e2.cloud.idrive.com"
            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
            data-lpignore="true" data-1p-ignore="true" data-form-type="other"
            class="stg-input w-full px-4 py-3 rounded-xl text-sm font-mono">
        <p class="text-xs text-slate-400 mt-1">
            Para IDrive e2, use o endpoint fornecido no painel. Para AWS S3, deixe em branco.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div class="stg-card">
            <label class="stg-label">Access Key</label>
            <input type="text" name="storage_access_key"
                value="{{ $settings['storage_access_key'] ?? '' }}"
                placeholder="AKIAIOSFODNN7EXAMPLE"
                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                data-lpignore="true" data-1p-ignore="true" data-form-type="other"
                class="stg-input w-full px-4 py-3 rounded-xl text-sm font-mono">
        </div>
        <div class="stg-card">
            <label class="stg-label">Secret Key</label>
            <div class="relative">
                <input type="password" name="storage_secret_key" id="storage_secret_key_input"
                    value="{{ $secretKey }}"
                    placeholder="{{ $maskedSecret !== '' ? $maskedSecret : '••••••••••••••••' }}"
                    autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                    data-lpignore="true" data-1p-ignore="true" data-form-type="other"
                    class="stg-input w-full px-4 py-3 pr-11 rounded-xl text-sm font-mono">
                <button type="button" onclick="stgRevealSecret()" id="stg-reveal-btn"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                    <i class="fas fa-eye text-sm" id="stg-reveal-icon"></i>
                </button>
            </div>
            @if($maskedSecret !== '')
                <p class="text-xs text-slate-400 mt-1">
                    <i class="fas fa-lock text-xs"></i> Salvo: {{ $maskedSecret }}
                </p>
            @endif
        </div>
    </div>

    <div class="stg-card mb-4">
        <label class="stg-label">URL Publica dos Arquivos</label>
        <input type="text" name="storage_url"
            value="{{ $settings['storage_url'] ?? '' }}"
            placeholder="https://meu-bucket.s3.us-east-1.amazonaws.com"
            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
            data-lpignore="true" data-1p-ignore="true" data-form-type="other"
            class="stg-input w-full px-4 py-3 rounded-xl text-sm font-mono">
        <p class="text-xs text-slate-400 mt-1">
            URL base para acesso publico aos arquivos. Deixe em branco para usar o padrao do provider.
        </p>
    </div>

    <div class="stg-card mb-6">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="hidden" name="storage_path_style" value="0">
            <input type="checkbox" name="storage_path_style" value="1"
                class="h-5 w-5 rounded border-slate-300 text-blue-600"
                {{ $pathStyle ? 'checked' : '' }}>
            <div>
                <p class="font-black text-sm text-slate-700 dark:text-slate-200">Path Style Endpoint</p>
                <p class="text-xs text-slate-500">Ative para provedores S3-compativeis (IDrive e2, MinIO, DigitalOcean Spaces). Desative para AWS S3 padrao.</p>
            </div>
        </label>
    </div>

    {{-- TEST BUTTON --}}
    <button type="button" onclick="stgTestS3()" id="btn-test-s3"
        class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-xl font-bold text-sm transition-all
               bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-500/30">
        <i class="fas fa-plug"></i> Testar Conexao S3
    </button>

    {{-- TEST RESULTS --}}
    <div id="s3-test-results" class="hidden mt-4 stg-card border-2" style="border-color: transparent;">
        <div id="s3-test-header" class="flex items-center gap-2 mb-3">
            <i class="fas fa-circle-notch fa-spin text-blue-500" id="s3-test-spinner"></i>
            <span class="text-sm font-bold text-slate-700 dark:text-slate-200" id="s3-test-title">Testando...</span>
        </div>
        <div id="s3-test-steps" class="space-y-2"></div>
    </div>
</div>

{{-- MIGRACAO DE ARQUIVOS --}}
<div class="stg-card mt-6" id="migration-section">
    <div class="flex items-center gap-3 mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400">
            <i class="fas fa-cloud-upload-alt"></i>
        </div>
        <div>
            <h3 class="font-black text-slate-800 dark:text-white text-sm">Migracao de Arquivos</h3>
            <p class="text-xs text-slate-400">Envie arquivos locais para o S3 sem sair do painel</p>
        </div>
    </div>

    <div class="space-y-3">
        <button type="button" onclick="stgLoadFolders()" id="btn-load-folders"
            class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-xl font-bold text-sm transition-all bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-500/30">
            <i class="fas fa-folder-open"></i> Listar Pastas Disponiveis
        </button>

        <div id="stg-folders-list" class="hidden space-y-2"></div>

        <div id="stg-migration-progress" class="hidden mt-4 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-spinner fa-spin text-indigo-500" id="stg-mig-spinner"></i>
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200" id="stg-mig-status">Migrando...</span>
            </div>
            <div id="stg-mig-result" class="text-xs text-slate-500 dark:text-slate-400"></div>
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
            'storage_access_key' => (string) ($settings['storage_access_key'] ?? ''),
            'storage_secret_key' => (string) $secretKey,
            'storage_url' => (string) ($settings['storage_url'] ?? ''),
        ];
    @endphp
    // Reforco anti-autofill: o Chrome as vezes limpa values de inputs apos o carregamento.
    // Garantimos que os valores reais (vindos do banco) sejam restaurados se isso ocorrer.
    var stgDefaults = {!! json_encode($stgDefaults, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

    function restoreStgDefaults() {
        Object.keys(stgDefaults).forEach(function(name) {
            var el = document.querySelector('[name="' + name + '"]');
            if (!el) return;
            // So restaura se foi limpo pelo autofill (valor diferente do esperado E nao foi editado pelo usuario)
            if (!el.dataset.userEdited && el.value !== stgDefaults[name]) {
                el.value = stgDefaults[name];
            }
        });
    }

    // Marca campos como editados pelo usuario para nao sobrescrever
    Object.keys(stgDefaults).forEach(function(name) {
        var el = document.querySelector('[name="' + name + '"]');
        if (el) {
            el.addEventListener('input', function() {
                el.dataset.userEdited = '1';
            });
        }
    });

    // Restaura logo apos load + 200ms (autofill costuma rodar tarde)
    setTimeout(restoreStgDefaults, 100);
    setTimeout(restoreStgDefaults, 600);
    setTimeout(restoreStgDefaults, 1500);
})();
</script>
@endpush

@push('scripts')
<script>
(function() {
    // Toggle S3 fields visibility - reage ao select de provedor (idrive/wasabi/aws/public)
    var choiceSelect = document.getElementById('storage_active_choice');
    var driverHidden = document.getElementById('storage_driver_hidden_panel');
    var providerHidden = document.getElementById('storage_active_provider_hidden_panel');
    var s3Fields = document.getElementById('s3-fields');

    function syncProviderUI(value) {
        var isS3 = value && value !== 'public';
        if (driverHidden) driverHidden.value = isS3 ? 's3' : 'public';
        if (isS3 && providerHidden) providerHidden.value = value;
        if (s3Fields) {
            if (isS3) {
                s3Fields.classList.remove('opacity-50', 'pointer-events-none');
            } else {
                s3Fields.classList.add('opacity-50', 'pointer-events-none');
            }
        }
    }

    if (choiceSelect) {
        choiceSelect.addEventListener('change', function() {
            syncProviderUI(this.value);
            // Recarrega para popular os campos com as creds do provedor escolhido.
            // Simples e robusto: o backend renderiza com $providerKey correto.
            // Nota: salve as alteracoes ANTES de trocar o provedor.
            if (this.value !== '@php echo $isS3Active ? $activeProvider : "public"; @endphp') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Trocar de provedor?',
                        html: 'Voce vai trocar para <strong>' + this.options[this.selectedIndex].text + '</strong>.<br>'
                            + '<small class="text-warning">Salve as alteracoes do provedor atual antes de continuar.</small>',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Trocar (descartar mudancas nao salvas)',
                        cancelButtonText: 'Cancelar',
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            // Salva o novo provedor e recarrega
                            var form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '{{ route("panel.admin.settings.update") }}';
                            form.innerHTML = '<input name="_token" value="{{ csrf_token() }}">'
                                + '<input name="current_group" value="storage">'
                                + '<input name="storage_active_provider" value="' + value + '">'
                                + '<input name="storage_driver" value="' + (value === 'public' ? 'public' : 's3') + '">';
                            document.body.appendChild(form);
                            form.submit();
                        } else {
                            // Reverte select
                            choiceSelect.value = '@php echo $isS3Active ? $activeProvider : "public"; @endphp';
                            syncProviderUI(choiceSelect.value);
                        }
                    });
                }
            }
        });
    }

    // Reveal secret key
    window.stgRevealSecret = function() {
        var input = document.getElementById('storage_secret_key_input');
        var icon = document.getElementById('stg-reveal-icon');
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

    // Test S3 connection
    window.stgTestS3 = function() {
        var btn = document.getElementById('btn-test-s3');
        var resultsDiv = document.getElementById('s3-test-results');
        var stepsDiv = document.getElementById('s3-test-steps');
        var titleEl = document.getElementById('s3-test-title');
        var spinnerEl = document.getElementById('s3-test-spinner');

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando...';

        resultsDiv.classList.remove('hidden');
        resultsDiv.style.borderColor = '#3b82f6';
        stepsDiv.innerHTML = '';
        titleEl.textContent = 'Testando conexao...';
        spinnerEl.className = 'fas fa-circle-notch fa-spin text-blue-500';

        fetch('{{ route("panel.admin.settings.test-s3") }}', {
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
        .then(function(response) {
            if (!response.ok && response.status === 419) {
                throw new Error('Sessao expirada. Recarregue a pagina (Ctrl+Shift+R).');
            }
            var ct = response.headers.get('content-type') || '';
            if (ct.indexOf('application/json') === -1) {
                throw new Error('Resposta inesperada (HTTP ' + response.status + '). Recarregue a pagina.');
            }
            return response.json();
        })
        .then(function(data) {
            if (data.success) {
                resultsDiv.style.borderColor = '#10b981';
                titleEl.textContent = data.message || 'Sucesso!';
                spinnerEl.className = 'fas fa-check-circle text-emerald-500';
            } else {
                resultsDiv.style.borderColor = '#ef4444';
                titleEl.textContent = data.message || 'Falha na conexao';
                spinnerEl.className = 'fas fa-times-circle text-red-500';
            }

            if (data.results && data.results.length > 0) {
                var html = '';
                for (var i = 0; i < data.results.length; i++) {
                    var r = data.results[i];
                    var iconClass = r.status === 'ok' ? 'fa-check text-emerald-500' : (r.status === 'aviso' ? 'fa-exclamation-triangle text-amber-500' : 'fa-times text-red-500');
                    html += '<div class="flex items-center gap-2 text-sm">';
                    html += '<i class="fas ' + iconClass + '"></i>';
                    html += '<span class="font-bold text-slate-700 dark:text-slate-200">' + r.step + ':</span>';
                    html += '<span class="text-slate-500 dark:text-slate-400 text-xs break-all">' + r.detail + '</span>';
                    html += '</div>';
                }
                stepsDiv.innerHTML = html;
            }
        })
        .catch(function(err) {
            resultsDiv.style.borderColor = '#ef4444';
            titleEl.textContent = 'Erro de conexao: ' + err.message;
            spinnerEl.className = 'fas fa-times-circle text-red-500';
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plug"></i> Testar Conexao S3';
        });
    };

    // Folder labels mapping (Portuguese friendly names)
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

    function getFolderLabel(name) {
        return folderLabels[name] || name;
    }

    // Load folders for migration
    window.stgLoadFolders = function() {
        var btn = document.getElementById('btn-load-folders');
        var list = document.getElementById('stg-folders-list');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Carregando...';

        fetch('{{ route("panel.admin.settings.storage.folders") }}', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function(r) {
            if (!r.ok && r.status === 419) throw new Error('Sessao expirada. Recarregue a pagina.');
            var ct = r.headers.get('content-type') || '';
            if (ct.indexOf('application/json') === -1) throw new Error('Resposta inesperada (HTTP ' + r.status + '). Recarregue.');
            return r.json();
        })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-folder-open"></i> Atualizar Lista';

            if (!data.success || !data.folders || data.folders.length === 0) {
                list.innerHTML = '<p class="text-sm text-slate-500 text-center py-4">Nenhuma pasta encontrada.</p>';
                list.classList.remove('hidden');
                return;
            }

            var html = '<div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">';
            html += '<div class="px-4 py-2 bg-slate-50 dark:bg-slate-800 text-xs font-black uppercase tracking-wider text-slate-400 flex items-center justify-between"><span>Pasta</span><span>Acoes</span></div>';

            data.folders.forEach(function(folder) {
                var label = getFolderLabel(folder.name);
                html += '<div class="flex items-center justify-between px-4 py-3 border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40">';
                html += '<div class="flex-1 min-w-0">';
                html += '<p class="font-bold text-sm text-slate-800 dark:text-white truncate">' + label + '</p>';
                html += '<p class="text-xs text-slate-400">' + folder.name + ' &bull; ' + folder.files + ' arquivos &bull; ' + folder.size_formatted + '</p>';
                html += '</div>';
                html += '<div class="flex gap-2 shrink-0">';
                html += '<button type="button" onclick="stgMigrateFolder(\'' + folder.name + '\', false)" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition" title="Copiar para S3 (manter local)"><i class="fas fa-copy"></i></button>';
                html += '<button type="button" onclick="stgMigrateFolder(\'' + folder.name + '\', true)" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-red-500 hover:bg-red-600 transition" title="Mover para S3 (apagar local)"><i class="fas fa-cloud-upload-alt"></i></button>';
                html += '</div>';
                html += '</div>';
            });

            // Botao migrar tudo
            html += '<div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">';
            html += '<button type="button" onclick="stgMigrateAll()" class="w-full py-2.5 rounded-xl text-xs font-black text-white bg-red-600 hover:bg-red-700 transition flex items-center justify-center gap-2"><i class="fas fa-cloud-upload-alt"></i> Migrar TUDO para S3 (apagar local)</button>';
            html += '</div>';

            html += '</div>';
            list.innerHTML = html;
            list.classList.remove('hidden');
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-folder-open"></i> Listar Pastas Disponiveis';
            if (typeof toastr !== 'undefined') toastr.error('Erro ao carregar pastas.');
        });
    };

    // Migrate a specific folder
    window.stgMigrateFolder = function(path, deleteLocal) {
        var action = deleteLocal ? 'MOVER (apagar local)' : 'COPIAR (manter local)';
        Swal.fire({
            title: action + ' pasta "' + path + '"?',
            text: deleteLocal ? 'Os arquivos locais serao apagados apos confirmacao no S3.' : 'Os arquivos serao copiados para o S3. O local sera mantido.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: deleteLocal ? '#ef4444' : '#4f46e5',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-cloud-upload-alt mr-1"></i> ' + (deleteLocal ? 'Mover' : 'Copiar'),
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            stgRunMigration(path, deleteLocal);
        });
    };

    // Migrate all
    window.stgMigrateAll = function() {
        Swal.fire({
            title: 'Migrar TODOS os arquivos?',
            html: 'Todos os arquivos locais serao enviados para o S3 e <strong>apagados do servidor</strong>. Esta acao nao pode ser desfeita.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-cloud-upload-alt mr-1"></i> Sim, migrar tudo',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            stgRunMigration('', true);
        });
    };

    // Execute migration
    window.stgRunMigration = function(path, deleteLocal) {
        var progress = document.getElementById('stg-migration-progress');
        var spinner = document.getElementById('stg-mig-spinner');
        var status = document.getElementById('stg-mig-status');
        var resultDiv = document.getElementById('stg-mig-result');

        progress.classList.remove('hidden');
        spinner.className = 'fas fa-spinner fa-spin text-indigo-500';
        status.textContent = 'Migrando ' + (path || 'todos os arquivos') + '...';
        resultDiv.textContent = '';

        fetch('{{ route("panel.admin.settings.storage.migrate") }}', {
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
        .then(function(r) {
            if (!r.ok && r.status === 419) throw new Error('Sessao expirada. Recarregue a pagina.');
            var ct = r.headers.get('content-type') || '';
            if (ct.indexOf('application/json') === -1) throw new Error('Resposta inesperada (HTTP ' + r.status + '). Recarregue.');
            return r.json();
        })
        .then(function(data) {
            if (data.success) {
                spinner.className = 'fas fa-check-circle text-emerald-500';
                status.textContent = 'Concluido!';
                resultDiv.textContent = data.message;
                if (typeof toastr !== 'undefined') toastr.success(data.message);
                // Recarregar lista de pastas
                setTimeout(stgLoadFolders, 1500);
            } else {
                spinner.className = 'fas fa-times-circle text-red-500';
                status.textContent = 'Falha';
                resultDiv.textContent = data.message;
                if (typeof toastr !== 'undefined') toastr.error(data.message);
            }
        })
        .catch(function(err) {
            spinner.className = 'fas fa-times-circle text-red-500';
            status.textContent = 'Erro de conexao';
            resultDiv.textContent = err.message;
            if (typeof toastr !== 'undefined') toastr.error('Erro de conexao.');
        });
    };
})();
</script>
@endpush
