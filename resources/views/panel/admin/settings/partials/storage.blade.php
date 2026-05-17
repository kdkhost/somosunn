{{-- ============================================================
     ARMAZENAMENTO — Configuracao S3 multi-provider (Painel Tailwind)
     Suporta IDrive e2, Wasabi e AWS S3 com configuracoes independentes.
     Spec: .kiro/specs/multi-provider-s3-storage
     ============================================================ --}}

@php
    use App\Support\StorageProviderRegistry;

    // Resolve provedor ativo (idrive/wasabi/aws/public).
    $activeProvider = 'public';
    try {
        $stored = (string) ($settings['storage_active_provider'] ?? '');
        $driver = (string) ($settings['storage_driver'] ?? 'public');
        if ($driver === 's3') {
            $activeProvider = in_array($stored, StorageProviderRegistry::PROVIDERS, true)
                ? $stored
                : StorageProviderRegistry::DEFAULT_PROVIDER;
        }
    } catch (\Throwable $e) {
        $activeProvider = 'public';
    }

    $isS3Active = $activeProvider !== 'public';

    $providerOptions = [
        'public' => 'Local (disco publico)',
        'idrive' => 'IDrive e2',
        'wasabi' => 'Wasabi',
        'aws'    => 'AWS S3',
    ];

    // Para cada provedor, monta os valores atuais dos campos
    // (se vazio E for o ativo, faz fallback para o schema legado storage_*).
    $providerData = [];
    foreach (['idrive', 'wasabi', 'aws'] as $p) {
        $isActive = ($p === $activeProvider);
        $sk = (string) ($settings[$p . '_secret_key'] ?? '');
        if ($sk === '' && $isActive) {
            $sk = (string) ($settings['storage_secret_key'] ?? '');
        }
        $providerData[$p] = [
            'access_key' => (string) ($settings[$p . '_access_key'] ?? ($isActive ? ($settings['storage_access_key'] ?? '') : '')),
            'secret_key' => $sk,
            'masked_secret' => $sk !== '' ? str_repeat('*', max(0, strlen($sk) - 4)) . substr($sk, -4) : '',
            'bucket' => (string) ($settings[$p . '_bucket'] ?? ($isActive ? ($settings['storage_bucket'] ?? '') : '')),
            'region' => (string) ($settings[$p . '_region'] ?? ($isActive ? ($settings['storage_region'] ?? '') : '')),
            'endpoint' => (string) ($settings[$p . '_endpoint'] ?? ($isActive ? ($settings['storage_endpoint'] ?? '') : '')),
            'url' => (string) ($settings[$p . '_url'] ?? ($isActive ? ($settings['storage_url'] ?? '') : '')),
            'path_style' => (int) ($settings[$p . '_path_style'] ?? ($isActive ? ($settings['storage_path_style'] ?? ($p === 'aws' ? 0 : 1)) : ($p === 'aws' ? 0 : 1))) === 1,
        ];
    }

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
            <p class="text-slate-400 text-xs mt-1">Configure IDrive e2, Wasabi ou AWS S3 (apenas um ativo por vez)</p>
        </div>
        <div class="flex items-center gap-2">
            @if($isS3Active)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-400/20 text-emerald-200 border border-emerald-400/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ $providerOptions[$activeProvider] }} Ativo
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
    <select name="storage_active_choice" id="storage_active_choice_panel" class="stg-select w-full px-4 py-3 rounded-xl text-sm font-semibold">
        @foreach ($providerOptions as $key => $label)
            <option value="{{ $key }}" {{ ($isS3Active ? $activeProvider : 'public') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>

    {{-- Hidden fields que o backend persiste --}}
    <input type="hidden" name="storage_driver" id="storage_driver_hidden_panel" value="{{ $isS3Active ? 's3' : 'public' }}">
    <input type="hidden" name="storage_active_provider" id="storage_active_provider_hidden_panel" value="{{ $isS3Active ? $activeProvider : 'idrive' }}">

    <p class="text-xs text-slate-400 mt-2">
        <i class="fas fa-info-circle"></i>
        Cada provedor S3 tem seu proprio conjunto de credenciais. Apenas o selecionado e usado em runtime; os demais ficam salvos para troca futura.
    </p>
</div>

{{-- HELP CARD por provedor (visivel apenas para o ativo) --}}
@foreach (['idrive', 'wasabi', 'aws'] as $p)
    <div class="storage-provider-help rounded-2xl bg-blue-50 border border-blue-200 p-4 mb-4 dark:bg-blue-900/20 dark:border-blue-700"
         data-provider="{{ $p }}"
         style="display: {{ $activeProvider === $p ? 'block' : 'none' }};">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-1"></i>
            <div class="flex-1 text-sm">
                <p class="font-bold text-blue-800 dark:text-blue-200 mb-1">{{ $providerOptions[$p] }}</p>
                <p class="text-blue-700 dark:text-blue-300">{{ $providerHints[$p]['help'] }}</p>
                <p class="mt-2 text-xs">
                    <a href="{{ $providerHints[$p]['docs_url'] }}" target="_blank" rel="noopener" class="underline font-semibold text-blue-700 dark:text-blue-300">
                        Ver documentacao
                    </a>
                </p>
            </div>
        </div>
    </div>
@endforeach

{{-- Iscas anti-autofill --}}
<div style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden;" aria-hidden="true">
    <input type="text" name="fake_user_for_chrome" autocomplete="username" tabindex="-1">
    <input type="password" name="fake_pass_for_chrome" autocomplete="current-password" tabindex="-1">
</div>

{{-- FIELDSETS PREFIXADOS por provedor --}}
@foreach (['idrive', 'wasabi', 'aws'] as $p)
    @php $pd = $providerData[$p]; @endphp
    <div class="storage-provider-fields" data-provider="{{ $p }}"
         style="display: {{ $activeProvider === $p ? 'block' : 'none' }};">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="stg-card">
                <label class="stg-label">Bucket</label>
                <input type="text" name="{{ $p }}_bucket"
                    value="{{ $pd['bucket'] }}"
                    placeholder="meu-bucket"
                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                    data-lpignore="true" data-1p-ignore="true" data-form-type="other"
                    class="stg-input w-full px-4 py-3 rounded-xl text-sm">
            </div>
            <div class="stg-card">
                <label class="stg-label">Regiao (Region)</label>
                <input type="text" name="{{ $p }}_region"
                    value="{{ $pd['region'] }}"
                    placeholder="{{ $providerHints[$p]['region_placeholder'] }}"
                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                    data-lpignore="true" data-1p-ignore="true" data-form-type="other"
                    class="stg-input w-full px-4 py-3 rounded-xl text-sm">
            </div>
        </div>

        <div class="stg-card mb-4">
            <label class="stg-label">Endpoint</label>
            <input type="text" name="{{ $p }}_endpoint"
                value="{{ $pd['endpoint'] }}"
                placeholder="{{ $providerHints[$p]['endpoint_placeholder'] }}"
                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                data-lpignore="true" data-1p-ignore="true" data-form-type="other"
                class="stg-input w-full px-4 py-3 rounded-xl text-sm font-mono">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="stg-card">
                <label class="stg-label">Access Key</label>
                <input type="text" name="{{ $p }}_access_key"
                    value="{{ $pd['access_key'] }}"
                    placeholder="AKIAIOSFODNN7EXAMPLE"
                    autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                    data-lpignore="true" data-1p-ignore="true" data-form-type="other"
                    class="stg-input w-full px-4 py-3 rounded-xl text-sm font-mono">
            </div>
            <div class="stg-card">
                <label class="stg-label">Secret Key</label>
                <div class="relative">
                    <input type="password" name="{{ $p }}_secret_key" id="{{ $p }}_secret_key_input_panel"
                        value="{{ $pd['secret_key'] }}"
                        placeholder="{{ $pd['masked_secret'] !== '' ? $pd['masked_secret'] : '••••••••••••••••' }}"
                        autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                        data-lpignore="true" data-1p-ignore="true" data-form-type="other"
                        class="stg-input w-full px-4 py-3 pr-11 rounded-xl text-sm font-mono">
                    <button type="button" onclick="stgRevealSecretPanel('{{ $p }}')" id="stg-reveal-btn-panel-{{ $p }}"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <i class="fas fa-eye text-sm" id="stg-reveal-icon-panel-{{ $p }}"></i>
                    </button>
                </div>
                @if($pd['masked_secret'] !== '')
                    <p class="text-xs text-slate-400 mt-1">
                        <i class="fas fa-lock text-xs"></i> Salvo: {{ $pd['masked_secret'] }}
                    </p>
                @endif
            </div>
        </div>

        <div class="stg-card mb-4">
            <label class="stg-label">URL Publica dos Arquivos</label>
            <input type="text" name="{{ $p }}_url"
                value="{{ $pd['url'] }}"
                placeholder="(opcional)"
                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                data-lpignore="true" data-1p-ignore="true" data-form-type="other"
                class="stg-input w-full px-4 py-3 rounded-xl text-sm font-mono">
            <p class="text-xs text-slate-400 mt-1">
                URL base para acesso publico aos arquivos. Vazio = padrao do provider.
            </p>
        </div>

        <div class="stg-card mb-6">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="{{ $p }}_path_style" value="0">
                <input type="checkbox" name="{{ $p }}_path_style" value="1"
                    class="h-5 w-5 rounded border-slate-300 text-blue-600"
                    {{ $pd['path_style'] ? 'checked' : '' }}>
                <div>
                    <p class="font-black text-sm text-slate-700 dark:text-slate-200">Path Style Endpoint</p>
                    <p class="text-xs text-slate-500">Ative para IDrive e2, Wasabi, MinIO, DigitalOcean Spaces. Desative para AWS S3 padrao.</p>
                </div>
            </label>
        </div>

        {{-- TEST BUTTON por provedor --}}
        <button type="button" onclick="stgTestProviderPanel('{{ $p }}', this)" id="btn-test-s3-panel-{{ $p }}"
            class="w-full flex items-center justify-center gap-2 py-3 px-6 rounded-xl font-bold text-sm transition-all
                   bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-500/30">
            <i class="fas fa-plug"></i> Testar Conexao {{ $providerOptions[$p] }}
        </button>
    </div>
@endforeach

{{-- TEST RESULTS (compartilhado entre os botoes) --}}
<div id="s3-test-results-panel" class="hidden mt-4 stg-card border-2" style="border-color: transparent;">
    <div id="s3-test-header-panel" class="flex items-center gap-2 mb-3">
        <i class="fas fa-circle-notch fa-spin text-blue-500" id="s3-test-spinner-panel"></i>
        <span class="text-sm font-bold text-slate-700 dark:text-slate-200" id="s3-test-title-panel">Testando...</span>
    </div>
    <div id="s3-test-steps-panel" class="space-y-2"></div>
</div>

{{-- MIGRACAO DE ARQUIVOS --}}
<div class="stg-card mt-6" id="migration-section">
    <div class="flex items-center gap-3 mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400">
            <i class="fas fa-cloud-upload-alt"></i>
        </div>
        <div>
            <h3 class="font-black text-slate-800 dark:text-white text-sm">Migracao de Arquivos</h3>
            <p class="text-xs text-slate-400">Envie arquivos locais para o provedor ativo</p>
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
    'use strict';

    // 1) Toggle de visibilidade dos fieldsets por provedor
    var choiceSelect = document.getElementById('storage_active_choice_panel');
    var driverHidden = document.getElementById('storage_driver_hidden_panel');
    var providerHidden = document.getElementById('storage_active_provider_hidden_panel');

    function syncProviderUIPanel(value) {
        var fieldsets = document.querySelectorAll('.storage-provider-fields');
        var helps = document.querySelectorAll('.storage-provider-help');

        fieldsets.forEach(function(fs) { fs.style.display = 'none'; });
        helps.forEach(function(h) { h.style.display = 'none'; });

        var isS3 = value && value !== 'public';
        if (driverHidden) driverHidden.value = isS3 ? 's3' : 'public';

        if (isS3) {
            if (providerHidden) providerHidden.value = value;
            var fs = document.querySelector('.storage-provider-fields[data-provider="' + value + '"]');
            var h = document.querySelector('.storage-provider-help[data-provider="' + value + '"]');
            if (fs) fs.style.display = 'block';
            if (h) h.style.display = 'block';
        }
    }

    if (choiceSelect) {
        choiceSelect.addEventListener('change', function() {
            syncProviderUIPanel(this.value);
        });
    }

    // 2) Reveal secret key por provedor
    window.stgRevealSecretPanel = function(provider) {
        var input = document.getElementById(provider + '_secret_key_input_panel');
        var icon = document.getElementById('stg-reveal-icon-panel-' + provider);
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

    // 3) Test S3 connection por provedor (AJAX)
    window.stgTestProviderPanel = function(provider, btn) {
        var resultsDiv = document.getElementById('s3-test-results-panel');
        var stepsDiv = document.getElementById('s3-test-steps-panel');
        var titleEl = document.getElementById('s3-test-title-panel');
        var spinnerEl = document.getElementById('s3-test-spinner-panel');

        btn.disabled = true;
        var oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando...';

        resultsDiv.classList.remove('hidden');
        resultsDiv.style.borderColor = '#3b82f6';
        stepsDiv.innerHTML = '';
        titleEl.textContent = 'Testando conexao com ' + provider.toUpperCase() + '...';
        spinnerEl.className = 'fas fa-circle-notch fa-spin text-blue-500';

        var formData = new FormData();
        formData.append('provider', provider);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("panel.admin.settings.test-s3-provider") }}', {
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
                throw new Error('Sessao expirada. Recarregue a pagina (Ctrl+Shift+R).');
            }
            var ct = r.headers.get('content-type') || '';
            if (ct.indexOf('application/json') === -1) {
                throw new Error('Resposta inesperada (HTTP ' + r.status + ').');
            }
            return r.json();
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
            btn.innerHTML = oldHtml;
        });
    };

    // 4) Folder labels mapping
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

    window.stgMigrateFolder = function(path, deleteLocal) {
        var action = deleteLocal ? 'MOVER (apagar local)' : 'COPIAR (manter local)';
        if (typeof Swal === 'undefined') return;
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

    window.stgMigrateAll = function() {
        if (typeof Swal === 'undefined') return;
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
