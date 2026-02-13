@extends('panel.layouts.app')

@section('title', 'Configurações de Pagamento - UNN')

@section('panel_content')
    @php
        $mercadoPagoAccount = $mercadoPagoAccount ?? null;
        $pagSeguroAccount = $pagSeguroAccount ?? null;
    @endphp

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mb-2">Configurações de Pagamento</h1>
        <p class="text-slate-600 mb-4">Configure suas credenciais para receber pagamentos diretamente pelas vendas dos seus cursos e mentorias.</p>
        <form method="POST" action="{{ route('panel.marketplace.gateway.update') }}" class="space-y-6">
            @csrf
            <div class="mb-6">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="gateway_env" value="production" {{ old('gateway_env', Setting::get('gateway_env', 'production')) == 'production' ? 'checked' : '' }}> Produção
                </label>
                <label class="inline-flex items-center gap-2 ml-6">
                    <input type="radio" name="gateway_env" value="sandbox" {{ old('gateway_env', Setting::get('gateway_env', 'production')) == 'sandbox' ? 'checked' : '' }}> Sandbox (teste)
                </label>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="font-bold text-slate-800 mb-2">MercadoPago</h2>
                    <div id="mp_prod_fields">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Public Key (Produção)</label>
                        <input type="text" name="mp_public_key" maxlength="255" placeholder="Public Key do MercadoPago" title="Chave pública da sua conta MercadoPago. Use ambiente correto (produção ou sandbox)." class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm" value="{{ old('mp_public_key', $mercadoPagoAccount->public_key ?? '') }}">
                        <label class="block text-sm font-medium text-slate-700 mt-3 mb-1">Access Token (Produção)</label>
                        <input type="text" name="mp_access_token" maxlength="255" placeholder="Access Token do MercadoPago" title="Token de acesso da sua conta MercadoPago. Use ambiente correto (produção ou sandbox)." class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm" value="{{ old('mp_access_token', $mercadoPagoAccount->access_token ?? '') }}">
                    </div>
                    <div id="mp_sandbox_fields" style="display:none">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Public Key (Sandbox)</label>
                        <input type="text" name="mp_public_key_sandbox" maxlength="255" placeholder="Public Key Sandbox" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm" value="{{ old('mp_public_key_sandbox', $mercadoPagoAccount->public_key_sandbox ?? '') }}">
                        <label class="block text-sm font-medium text-slate-700 mt-3 mb-1">Access Token (Sandbox)</label>
                        <input type="text" name="mp_access_token_sandbox" maxlength="255" placeholder="Access Token Sandbox" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm" value="{{ old('mp_access_token_sandbox', $mercadoPagoAccount->access_token_sandbox ?? '') }}">
                    </div>
                    <div class="flex items-center mt-3">
                        <input type="checkbox" name="mp_enabled" value="1" id="mp_enabled" {{ old('mp_enabled', $mercadoPagoAccount->enabled ?? false) ? 'checked' : '' }}>
                        <label for="mp_enabled" class="ml-2 text-sm text-slate-700">Ativar MercadoPago</label>
                    </div>
                    <button type="button" class="mt-3 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition" onclick="testarConexao('mercadopago')">
                        <i class="fas fa-plug mr-2"></i> Testar Conexão
                    </button>
                </div>
                <div>
                    <h2 class="font-bold text-slate-800 mb-2">PagSeguro</h2>
                    <div id="ps_prod_fields">
                        <label class="block text-sm font-medium text-slate-700 mb-1">E-mail (Produção)</label>
                        <input type="email" name="ps_email" maxlength="255" placeholder="E-mail do PagSeguro" title="E-mail cadastrado na sua conta PagSeguro." class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm" value="{{ old('ps_email', $pagSeguroAccount->client_id ?? '') }}">
                        <label class="block text-sm font-medium text-slate-700 mt-3 mb-1">Access Token (Produção)</label>
                        <input type="text" name="ps_access_token" maxlength="255" placeholder="Access Token do PagSeguro" title="Token de acesso da sua conta PagSeguro. Use ambiente correto (produção ou sandbox)." class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm" value="{{ old('ps_access_token', $pagSeguroAccount->access_token ?? '') }}">
                    </div>
                    <div id="ps_sandbox_fields" style="display:none">
                        <label class="block text-sm font-medium text-slate-700 mb-1">E-mail (Sandbox)</label>
                        <input type="email" name="ps_email_sandbox" maxlength="255" placeholder="E-mail Sandbox" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm" value="{{ old('ps_email_sandbox', $pagSeguroAccount->client_id_sandbox ?? '') }}">
                        <label class="block text-sm font-medium text-slate-700 mt-3 mb-1">Access Token (Sandbox)</label>
                        <input type="text" name="ps_access_token_sandbox" maxlength="255" placeholder="Access Token Sandbox" class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm" value="{{ old('ps_access_token_sandbox', $pagSeguroAccount->access_token_sandbox ?? '') }}">
                    </div>
                    <div class="flex items-center mt-3">
                        <input type="checkbox" name="ps_enabled" value="1" id="ps_enabled" {{ old('ps_enabled', $pagSeguroAccount->enabled ?? false) ? 'checked' : '' }}>
                        <label for="ps_enabled" class="ml-2 text-sm text-slate-700">Ativar PagSeguro</label>
                    </div>
                    <button type="button" class="mt-3 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition" onclick="testarConexao('pagseguro')">
                        <i class="fas fa-plug mr-2"></i> Testar Conexão
                    </button>
                </div>
            </div>
            <div class="pt-6">
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white rounded-xl font-bold text-base hover:bg-emerald-700 transition">
                    <i class="fas fa-save mr-2"></i> Salvar configurações
                </button>
            </div>
        </form>
        <div id="gateway-feedback" class="mt-4"></div>
    </div>
@endsection

@push('scripts')
<script>
function testarConexao(provider) {
    let env = document.querySelector('input[name="gateway_env"]:checked')?.value || 'production';
    let data = {};
    if (provider === 'mercadopago') {
        data = {
            provider: 'mercadopago',
            access_token: env === 'sandbox' ? document.querySelector('[name="mp_access_token_sandbox"]').value : document.querySelector('[name="mp_access_token"]').value,
            public_key: env === 'sandbox' ? document.querySelector('[name="mp_public_key_sandbox"]').value : document.querySelector('[name="mp_public_key"]').value,
            env: env
        };
    } else {
        data = {
            provider: 'pagseguro',
            access_token: env === 'sandbox' ? document.querySelector('[name="ps_access_token_sandbox"]').value : document.querySelector('[name="ps_access_token"]').value,
            email: env === 'sandbox' ? document.querySelector('[name="ps_email_sandbox"]').value : document.querySelector('[name="ps_email"]').value,
            env: env
        };
    }
    fetch("{{ route('panel.marketplace.gateway.test') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
        },
        body: JSON.stringify(data)
    })
    .then(resp => resp.json())
    .then(json => {
        let el = document.getElementById('gateway-feedback');
        if (json.ok) {
            el.innerHTML = `<div class='rounded-xl bg-emerald-100 text-emerald-800 px-4 py-3 mt-4'><i class='fas fa-check-circle mr-2'></i> ${json.message}</div>`;
        } else {
            el.innerHTML = `<div class='rounded-xl bg-amber-100 text-amber-800 px-4 py-3 mt-4'><i class='fas fa-exclamation-triangle mr-2'></i> ${json.message}</div>`;
        }
    })
    .catch(err => {
        let el = document.getElementById('gateway-feedback');
        el.innerHTML = `<div class='rounded-xl bg-red-100 text-red-800 px-4 py-3 mt-4'><i class='fas fa-times-circle mr-2'></i> Erro ao testar conexão.</div>`;
    });
}

// Alternar campos conforme ambiente
document.addEventListener('DOMContentLoaded', function() {
    function toggleGatewayFields() {
        var env = document.querySelector('input[name="gateway_env"]:checked')?.value || 'production';
        document.getElementById('mp_prod_fields').style.display = env === 'production' ? '' : 'none';
        document.getElementById('mp_sandbox_fields').style.display = env === 'sandbox' ? '' : 'none';
        document.getElementById('ps_prod_fields').style.display = env === 'production' ? '' : 'none';
        document.getElementById('ps_sandbox_fields').style.display = env === 'sandbox' ? '' : 'none';
    }
    document.querySelectorAll('input[name="gateway_env"]').forEach(function(radio) {
        radio.addEventListener('change', toggleGatewayFields);
    });
    toggleGatewayFields();
});
</script>
@endpush
