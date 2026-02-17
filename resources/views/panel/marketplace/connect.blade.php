@extends('panel.layouts.app')

@section('title', 'Configurar Pagamentos - UNN')

@section('panel_content')
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white transition-colors">
                    Configuração de Pagamento
                </h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">
                    Configure suas credenciais do MercadoPago para vender seus cursos na plataforma.
                </p>
            </div>
            <a href="{{ route('panel.marketplace.payments') }}"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-800 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 mt-6 transition-colors duration-300">
        <form action="{{ route('settings.payment.update') }}" method="POST">
            @csrf

            <!-- OAuth Connect -->
            <div
                class="mb-8 p-6 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-100 dark:border-blue-800 transition-colors">
                <h3 class="font-bold text-blue-800 dark:text-blue-300 mb-2 text-lg">Conexão Automática (Recomendado)</h3>
                <p class="text-sm text-blue-600 dark:text-blue-400 mb-6">Conecte sua conta do Mercado Pago para receber
                    pagamentos
                    automaticamente e com segurança.</p>

                @if($gateway->access_token && $gateway->provider == 'mercadopago' && $gateway->enabled)
                    <div
                        class="flex items-center gap-3 text-emerald-600 dark:text-emerald-400 font-bold mb-6 bg-emerald-50 dark:bg-emerald-900/20 p-4 rounded-xl border border-emerald-100 dark:border-emerald-800">
                        <i class="fas fa-check-circle text-xl"></i> Conta Conectada
                    </div>
                    <a href="{{ route('gateway.mercadopago.connect') }}"
                        class="inline-flex items-center px-5 py-3 bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 rounded-xl shadow-sm hover:bg-blue-50 dark:hover:bg-slate-700 font-bold transition-all">
                        <i class="fas fa-sync-alt mr-2"></i> Reconectar / Atualizar Permissões
                    </a>
                @else
                    <a href="{{ route('gateway.mercadopago.connect') }}"
                        class="inline-flex items-center px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-500/20 font-bold transition-all hover:scale-105 active:scale-95">
                        <img src="https://http2.mlstatic.com/frontend-assets/ui-navigation/5.18.9/mercadolibre/logo__small.png"
                            class="h-6 w-auto mr-3 bg-white rounded-full p-1" alt="MP">
                        Conectar com Mercado Pago
                    </a>
                @endif
            </div>

            <hr class="my-8 border-slate-100 dark:border-slate-800">

            <h3 class="font-bold text-slate-900 dark:text-white mb-6 text-lg flex items-center gap-2">
                <i class="fas fa-code text-slate-400"></i> Configuração Manual (Avançado)
            </h3>

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Public Key</label>
                    <input type="text" name="public_key" value="{{ $gateway->public_key }}"
                        class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white px-4 py-3 outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                        placeholder="APP_USR-..." required>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Disponível no painel de desenvolvedores do
                        MercadoPago.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Access Token</label>
                    <div class="relative">
                        <input type="password" name="access_token" value="{{ $gateway->access_token }}"
                            class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white px-4 py-3 outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                            placeholder="APP_USR-..." required>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Token de produção.</p>
                </div>
            </div>

            <div class="flex justify-end mt-8">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 dark:bg-blue-600 px-8 py-4 text-sm font-bold text-white hover:bg-slate-800 dark:hover:bg-blue-700 transition-all shadow-lg dark:shadow-blue-500/20 hover:shadow-xl hover:-translate-y-1">
                    <i class="fas fa-save mr-2"></i> Salvar Credenciais
                </button>
            </div>
        </form>
    </div>
@endsection