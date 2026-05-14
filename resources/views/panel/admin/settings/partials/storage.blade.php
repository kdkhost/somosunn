{{-- ============================================================
     ARMAZENAMENTO — Configuracao S3 / IDrive e2
     ============================================================ --}}

@php
    $storageDriver = $settings['storage_driver'] ?? 'public';
    $isS3Active = $storageDriver === 's3';
    $secretKey = $settings['storage_secret_key'] ?? '';
    $maskedSecret = $secretKey !== '' ? str_repeat('*', max(0, strlen($secretKey) - 4)) . substr($secretKey, -4) : '';
    $pathStyle = (int) ($settings['storage_path_style'] ?? 1) === 1;
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

{{-- DRIVER --}}
<div class="stg-card mb-4">
    <label class="stg-label">Driver de Armazenamento</label>
    <select name="storage_driver" id="storage_driver" class="stg-select w-full px-4 py-3 rounded-xl text-sm font-semibold">
        <option value="public" {{ $storageDriver !== 's3' ? 'selected' : '' }}>Local (disco publico)</option>
        <option value="s3" {{ $storageDriver === 's3' ? 'selected' : '' }}>S3 / IDrive e2</option>
    </select>
    <p class="text-xs text-slate-400 mt-2">
        <i class="fas fa-info-circle"></i>
        Ao selecionar S3, os uploads passarao a usar o bucket configurado abaixo.
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div class="stg-card">
            <label class="stg-label">Bucket</label>
            <input type="text" name="storage_bucket"
                value="{{ $settings['storage_bucket'] ?? '' }}"
                placeholder="meu-bucket"
                class="stg-input w-full px-4 py-3 rounded-xl text-sm">
        </div>
        <div class="stg-card">
            <label class="stg-label">Regiao (Region)</label>
            <input type="text" name="storage_region"
                value="{{ $settings['storage_region'] ?? 'us-east-1' }}"
                placeholder="us-east-1"
                class="stg-input w-full px-4 py-3 rounded-xl text-sm">
        </div>
    </div>

    <div class="stg-card mb-4">
        <label class="stg-label">Endpoint</label>
        <input type="text" name="storage_endpoint"
            value="{{ $settings['storage_endpoint'] ?? '' }}"
            placeholder="https://xxxx.e2.cloud.idrive.com"
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
                class="stg-input w-full px-4 py-3 rounded-xl text-sm font-mono">
        </div>
        <div class="stg-card">
            <label class="stg-label">Secret Key</label>
            <div class="relative">
                <input type="password" name="storage_secret_key" id="storage_secret_key_input"
                    value="{{ $secretKey }}"
                    placeholder="{{ $maskedSecret !== '' ? $maskedSecret : '••••••••••••••••' }}"
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

@push('scripts')
<script>
(function() {
    // Toggle S3 fields visibility
    var driverSelect = document.getElementById('storage_driver');
    var s3Fields = document.getElementById('s3-fields');

    if (driverSelect && s3Fields) {
        driverSelect.addEventListener('change', function() {
            if (this.value === 's3') {
                s3Fields.classList.remove('opacity-50', 'pointer-events-none');
            } else {
                s3Fields.classList.add('opacity-50', 'pointer-events-none');
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
        .then(function(response) { return response.json(); })
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
})();
</script>
@endpush
