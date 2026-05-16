{{--
============================================================
PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
============================================================
@autor marcelo-brad rj
@contato Tel: 21 981325441 - Email: contato@kdkhost.com.br
============================================================
View Tailwind para configuracao de multiplos provedores S3
(IDrive e2, Wasabi, AWS S3) com selecao de provedor ativo
e teste de conexao por provedor.

Spec: .kiro/specs/multi-provider-s3-storage (task 5.2)
Requirements: 4.1-4.5, 9.1-9.4
--}}
@extends('panel.layouts.app')

@section('title', 'Provedores S3 - Configuracoes')

@section('content')
<div class="px-4 py-6 max-w-6xl mx-auto" id="storage-providers-app" data-active-provider="{{ $activeProvider }}">

    <header class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Armazenamento - Multi Provedor S3</h1>
        <p class="text-sm text-gray-500 mt-1">
            Configure credenciais independentes para IDrive e2, Wasabi e AWS S3.
            Apenas um provedor pode estar ativo por vez.
        </p>
    </header>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <i class="fas fa-exclamation-triangle mr-2"></i> Verifique os erros abaixo:
            <ul class="list-disc list-inside mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Bloco: Provedor ativo --}}
    <section class="mb-6 rounded-lg bg-white shadow-sm border border-gray-200">
        <div class="border-b border-gray-100 px-6 py-4">
            <h2 class="text-lg font-medium text-gray-800">
                <i class="fas fa-toggle-on text-[#1F5EDB]"></i> Provedor Ativo
            </h2>
        </div>
        <div class="p-6">
            <p class="mb-4 text-sm text-gray-600">
                Ao alternar, o sistema valida a conexao antes de aplicar.
                Se o teste falhar, o provedor anterior e mantido.
            </p>

            <form action="{{ url('/painel/admin/settings/storage-providers/active') }}"
                  method="POST" id="form-switch-active"
                  class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                @csrf
                <div class="md:col-span-5">
                    <label for="active-provider-select" class="block text-sm font-medium text-gray-700 mb-1">
                        Selecionar provedor ativo
                    </label>
                    <select name="provider" id="active-provider-select"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#1F5EDB] focus:ring focus:ring-[#1F5EDB]/20">
                        @foreach ($displayNames as $key => $name)
                            <option value="{{ $key }}" {{ $activeProvider === $key ? 'selected' : '' }}>
                                {{ $name }} {{ $activeProvider === $key ? '(atual)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="inline-flex items-center text-sm text-gray-600 mt-2">
                        <input type="checkbox" name="skip_test" value="1" id="skip-test"
                               class="rounded border-gray-300 text-[#1F5EDB] focus:ring-[#1F5EDB]">
                        <span class="ml-2">Pular teste de conexao</span>
                    </label>
                </div>
                <div class="md:col-span-4 text-right">
                    <button type="submit"
                            class="w-full md:w-auto inline-flex items-center justify-center rounded-md bg-[#1F5EDB] px-4 py-2 text-sm font-medium text-white shadow hover:bg-[#1D3FC4] focus:outline-none focus:ring-2 focus:ring-[#1F5EDB]/40">
                        <i class="fas fa-power-off mr-2"></i> Ativar Provedor
                    </button>
                </div>
            </form>

            <div class="mt-4 flex items-center text-sm">
                <span class="font-medium text-gray-700 mr-2">Atualmente ativo:</span>
                <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                    <i class="fas fa-check-circle mr-1.5"></i>
                    {{ $displayNames[$activeProvider] ?? $activeProvider }}
                </span>
            </div>
        </div>
    </section>

    {{-- Bloco: Tabs por provedor --}}
    <section class="rounded-lg bg-white shadow-sm border border-gray-200">
        <div class="border-b border-gray-100 px-6 py-3">
            <nav class="flex space-x-2 overflow-x-auto" id="provider-tabs" role="tablist">
                @foreach ($providers as $providerKey => $provider)
                    <button type="button"
                            data-tab="{{ $providerKey }}"
                            class="provider-tab inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border transition-colors
                                   {{ $loop->first ? 'bg-[#1F5EDB] text-white border-[#1F5EDB]' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}"
                            role="tab">
                        <i class="fas fa-cloud mr-2"></i>
                        {{ $provider['name'] }}
                        @if ($provider['key'] === $activeProvider)
                            <span class="ml-2 inline-flex items-center rounded-full bg-green-500 text-white text-[10px] font-bold px-2 py-0.5">ATIVO</span>
                        @elseif ($provider['configured'])
                            <span class="ml-2 inline-flex items-center rounded-full bg-gray-300 text-gray-700 text-[10px] font-medium px-2 py-0.5">configurado</span>
                        @else
                            <span class="ml-2 inline-flex items-center rounded-full bg-yellow-300 text-yellow-900 text-[10px] font-medium px-2 py-0.5">incompleto</span>
                        @endif
                    </button>
                @endforeach
            </nav>
        </div>
        <div class="p-6">
            @foreach ($providers as $providerKey => $provider)
                <div class="provider-pane {{ $loop->first ? '' : 'hidden' }}" data-pane="{{ $providerKey }}">
                    @include('panel.admin.settings.partials.storage-provider-form', [
                        'provider' => $provider,
                        'isActive' => $provider['key'] === $activeProvider,
                        'isPanel' => true,
                    ])
                </div>
            @endforeach
        </div>
    </section>

</div>

{{-- Modal de teste de conexao --}}
<div id="modal-test-result" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-auto">
        <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-800">
                <i class="fas fa-vial text-[#1F5EDB] mr-2"></i> Resultado do Teste de Conexao
            </h3>
            <button type="button" id="modal-test-close" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6 max-h-[60vh] overflow-y-auto">
            <div id="test-result-loading" class="text-center py-8 hidden">
                <i class="fas fa-spinner fa-spin text-3xl text-[#1F5EDB]"></i>
                <p class="mt-3 text-sm text-gray-500">Testando conexao... pode levar ate 30 segundos.</p>
            </div>
            <div id="test-result-content" class="hidden"></div>
        </div>
        <div class="border-t border-gray-100 px-6 py-3 text-right">
            <button type="button" id="modal-test-close-2"
                    class="rounded-md bg-gray-100 hover:bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700">
                Fechar
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // -------------------------------------------------------
    // Tabs
    // -------------------------------------------------------
    var tabs = document.querySelectorAll('.provider-tab');
    var panes = document.querySelectorAll('.provider-pane');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.dataset.tab;

            tabs.forEach(function (t) {
                if (t === tab) {
                    t.classList.add('bg-[#1F5EDB]', 'text-white', 'border-[#1F5EDB]');
                    t.classList.remove('bg-white', 'text-gray-700', 'border-gray-200', 'hover:bg-gray-50');
                } else {
                    t.classList.remove('bg-[#1F5EDB]', 'text-white', 'border-[#1F5EDB]');
                    t.classList.add('bg-white', 'text-gray-700', 'border-gray-200', 'hover:bg-gray-50');
                }
            });
            panes.forEach(function (p) {
                if (p.dataset.pane === key) {
                    p.classList.remove('hidden');
                } else {
                    p.classList.add('hidden');
                }
            });
        });
    });

    // -------------------------------------------------------
    // Toggle visibilidade do Secret Key
    // -------------------------------------------------------
    document.querySelectorAll('.btn-toggle-secret').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.target);
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                btn.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });

    // -------------------------------------------------------
    // Confirm switch active provider via SweetAlert2
    // -------------------------------------------------------
    var formSwitch = document.getElementById('form-switch-active');
    if (formSwitch) {
        formSwitch.addEventListener('submit', function (e) {
            e.preventDefault();
            var sel = document.getElementById('active-provider-select');
            var providerName = sel.options[sel.selectedIndex].textContent.trim();
            var skipTest = document.getElementById('skip-test').checked;

            if (typeof Swal === 'undefined') {
                if (window.confirm('Ativar provedor "' + providerName + '"?')) {
                    formSwitch.submit();
                }
                return;
            }

            Swal.fire({
                title: 'Ativar provedor?',
                html: 'O provedor <strong>' + providerName + '</strong> sera ativado.<br>'
                    + (skipTest
                        ? '<span class="text-yellow-600">Teste de conexao sera <b>pulado</b>.</span>'
                        : 'Uma validacao de conexao sera executada antes da ativacao.'),
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ativar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#1F5EDB',
            }).then(function (result) {
                if (result.isConfirmed) {
                    formSwitch.submit();
                }
            });
        });
    }

    // -------------------------------------------------------
    // Test connection (AJAX) + modal render
    // -------------------------------------------------------
    var modal = document.getElementById('modal-test-result');
    var loading = document.getElementById('test-result-loading');
    var content = document.getElementById('test-result-content');

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    document.getElementById('modal-test-close').addEventListener('click', closeModal);
    document.getElementById('modal-test-close-2').addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    document.querySelectorAll('.btn-test-provider').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var providerKey = btn.dataset.provider;
            var providerName = btn.dataset.name || providerKey;

            openModal();
            loading.classList.remove('hidden');
            content.classList.add('hidden');
            content.innerHTML = '';

            fetch("{{ url('/painel/admin/settings/storage-providers') }}/" + providerKey + "/test", {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (resp) {
                renderResult(providerName, resp.data);
            })
            .catch(function (err) {
                renderResult(providerName, {
                    status: 'failed',
                    error_message: 'Erro de rede: ' + err.message,
                    steps: [],
                    total_latency_ms: 0,
                });
            });
        });
    });

    function renderResult(providerName, data) {
        loading.classList.add('hidden');

        var color = 'red', icon = 'times-circle', label = 'Falha';
        if (data.status === 'success') { color = 'green'; icon = 'check-circle'; label = 'Sucesso'; }
        if (data.status === 'timeout') { color = 'yellow'; icon = 'clock'; label = 'Timeout'; }

        var html = ''
            + '<div class="mb-4 rounded-md border border-' + color + '-200 bg-' + color + '-50 px-4 py-3 text-sm text-' + color + '-800">'
            +   '<i class="fas fa-' + icon + ' mr-2"></i>'
            +   '<strong>' + escapeHtml(providerName) + '</strong>: ' + label
            +   (data.total_latency_ms ? ' <span class="text-xs opacity-70">(' + data.total_latency_ms + ' ms)</span>' : '')
            + '</div>';

        if (data.error_message) {
            html += '<div class="mb-4 rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">'
                  +   '<strong class="text-gray-800">Detalhe:</strong> ' + escapeHtml(data.error_message)
                  + '</div>';
        }

        if (data.steps && data.steps.length > 0) {
            html += '<table class="w-full text-sm"><thead><tr class="border-b text-left">'
                  +   '<th class="w-10 py-2"></th>'
                  +   '<th class="py-2">Etapa</th>'
                  +   '<th class="py-2">Detalhe</th>'
                  +   '<th class="py-2 text-right w-24">Latencia</th>'
                  + '</tr></thead><tbody>';
            data.steps.forEach(function (step) {
                var ok = step.status === 'success';
                html += '<tr class="border-b border-gray-100">'
                     +    '<td class="py-2 text-center"><i class="fas fa-' + (ok ? 'check text-green-600' : 'times text-red-500') + '"></i></td>'
                     +    '<td class="py-2"><code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">' + escapeHtml(step.name) + '</code></td>'
                     +    '<td class="py-2 text-gray-600">' + escapeHtml(step.detail || '') + '</td>'
                     +    '<td class="py-2 text-right text-gray-500 text-xs">' + (step.latency_ms || 0) + ' ms</td>'
                     +  '</tr>';
            });
            html += '</tbody></table>';
        }

        content.innerHTML = html;
        content.classList.remove('hidden');
    }

    function escapeHtml(value) {
        var d = document.createElement('div');
        d.textContent = String(value == null ? '' : value);
        return d.innerHTML;
    }
})();
</script>
@endpush
