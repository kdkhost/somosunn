@extends('layouts.app')

@section('title', 'Configurar Pagamentos - UNN')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-bold mb-6">Configuração de Pagamento (Recebimento)</h1>
        <p class="mb-4 text-gray-600">Configure suas credenciais do Mercado Pago ou da SumUp para vender seus cursos na plataforma.</p>

        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ route('settings.payment.update') }}" method="POST">
                @csrf
                @php
                    $sumupMerchantCode = data_get($sumupAccount?->extra ?? [], 'merchant_code', '');
                    $activeGatewayProvider = old('provider', ($sumupAccount?->enabled ?? false) ? 'sumup' : 'mercadopago');
                @endphp

                <!-- OAuth Connect -->
                <div class="mb-8 p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <h3 class="font-bold text-blue-800 mb-2">Conexão Automática (Recomendado)</h3>
                    <p class="text-sm text-blue-600 mb-4">Conecte sua conta do Mercado Pago para receber pagamentos
                        automaticamente e com segurança.</p>

                    @if($gateway->access_token && $gateway->provider == 'mercadopago' && $gateway->enabled)
                        <div class="flex items-center gap-2 text-green-600 font-bold mb-4">
                            <i class="fas fa-check-circle"></i> Conta Conectada
                        </div>
                        <a href="{{ route('gateway.mercadopago.connect') }}"
                            class="inline-flex items-center px-4 py-2 bg-white text-blue-600 border border-blue-200 rounded shadow-sm hover:bg-blue-50 font-medium">
                            <i class="fas fa-sync-alt mr-2"></i> Reconectar / Atualizar Permissões
                        </a>
                    @else
                        <a href="{{ route('gateway.mercadopago.connect') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 font-bold">
                            <img src="https://http2.mlstatic.com/frontend-assets/ui-navigation/5.18.9/mercadolibre/logo__small.png"
                                class="h-6 w-auto mr-2 bg-white rounded-full p-1" alt="MP">
                            Conectar com Mercado Pago
                        </a>
                    @endif
                </div>

                <hr class="my-6 border-gray-200">

                <h3 class="font-bold text-gray-800 mb-4">Configuração Manual (Avançado)</h3>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                    <div class="flex items-center gap-4">
                        <input type="radio" name="provider" value="mercadopago" id="provider_mercadopago" {{ $activeGatewayProvider === 'mercadopago' ? 'checked' : '' }}>
                        <label for="provider_mercadopago" class="font-bold text-gray-700">Ativar Mercado Pago como meu Gateway</label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Public Key</label>
                    <input type="text" name="public_key" value="{{ $gateway->public_key }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Obrigatório apenas se você usar Mercado Pago manualmente.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Access Token</label>
                    <input type="password" name="access_token" value="{{ $gateway->access_token }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Token privado do Mercado Pago. Não preencha este campo se for usar somente SumUp.</p>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2">
                        <i class="fas fa-credit-card text-blue-600"></i> SumUp
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">Caso prefira usar a SumUp como seu gateway de recebimento.</p>

                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-4 text-sm text-blue-900">
                        <p class="font-bold mb-2">Onde pegar as credenciais da SumUp</p>
                        <ol class="list-decimal ml-5 space-y-1 text-blue-800">
                            <li>Acesse <a href="https://me.sumup.com" target="_blank" rel="noopener noreferrer" class="underline font-semibold">me.sumup.com</a>, abra seu perfil e entre em <strong>Settings &gt; For Developers &gt; Toolkit &gt; API Keys</strong>.</li>
                            <li>Crie uma API Key secreta e cole no campo <strong>SumUp API Key</strong>. Não use a SumUp Public Key.</li>
                            <li>Informe o <strong>Merchant Code</strong> da mesma conta SumUp. Se não souber, o administrador da plataforma pode testar a API Key no painel global e a SumUp retorna esse código.</li>
                            <li>Use credenciais de sandbox somente para testes e credenciais de produção apenas quando a conta SumUp estiver validada.</li>
                        </ol>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                        <div class="flex items-center gap-4">
                            <input type="radio" name="provider" value="sumup" id="provider_sumup" {{ $activeGatewayProvider === 'sumup' ? 'checked' : '' }}>
                            <label for="provider_sumup" class="font-bold text-gray-700">Ativar SumUp como meu Gateway</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">SumUp API Key</label>
                        <input type="password" name="sumup_access_token" value="{{ old('sumup_access_token', $sumupAccount->access_token ?? '') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="sup_sk_...">
                        <p class="text-xs text-gray-500 mt-1">Chave secreta criada em API Keys. Ela autentica as chamadas server-to-server da SumUp.</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Merchant Code</label>
                        <input type="text" name="sumup_merchant_code" value="{{ old('sumup_merchant_code', $sumupMerchantCode) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono uppercase"
                            placeholder="MXXXXXXX">
                        <p class="text-xs text-gray-500 mt-1">Código curto da conta de lojista SumUp. Use o código da mesma conta que gerou a API Key.</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">Salvar
                        Credenciais</button>
                </div>
            </form>
        </div>
    </div>
@endsection
