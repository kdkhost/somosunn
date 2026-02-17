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
                    Configure seus gateways para receber pagamentos de suas vendas.
                </p>
            </div>
            <a href="{{ route('panel.marketplace.payments') }}"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-800 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

        <!-- MERCADO PAGO -->
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300 flex flex-col h-full">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-blue-500/10 rounded-xl flex items-center justify-center p-2">
                    <img src="https://http2.mlstatic.com/frontend-assets/ui-navigation/5.18.9/mercadolibre/logo__small.png"
                        class="w-full h-auto" alt="MP">
                </div>
                <div>
                    <h3 class="font-extrabold text-lg text-slate-900 dark:text-white">Mercado Pago</h3>
                    <div class="flex items-center gap-2 text-sm mt-0.5">
                        @if($mercadopago->enabled && $mercadopago->access_token)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-bold">
                                <i class="fas fa-check-circle mr-1"></i> Ativo
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 dark:text-slate-500">
                                <i class="fas fa-circle mr-1 text-[8px]"></i> Inativo
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex-1 space-y-6">
                <!-- OAuth -->
                <div class="bg-blue-50 dark:bg-blue-900/10 rounded-2xl p-5 border border-blue-100 dark:border-blue-800/50">
                    <h4 class="font-bold text-blue-900 dark:text-blue-300 mb-2 text-sm">Conexão Automática</h4>
                    <p class="text-xs text-blue-700 dark:text-blue-400 mb-4 leading-relaxed">
                        Conecte sua conta do Mercado Pago para receber pagamentos via PIX, Boleto e Cartão com split
                        automático.
                    </p>

                    @if($mercadopago->enabled && $mercadopago->access_token && $mercadopago->provider == 'mercadopago')
                        <a href="{{ route('gateway.mercadopago.connect') }}"
                            class="w-full inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-slate-800 border border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 rounded-xl text-sm font-bold shadow-sm hover:bg-blue-50 dark:hover:bg-slate-700 transition-all">
                            <i class="fas fa-sync-alt mr-2"></i> Atualizar Conexão
                        </a>
                    @else
                        <a href="{{ route('gateway.mercadopago.connect') }}"
                            class="w-full inline-flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20 transition-all hover:scale-[1.02]">
                            Conectar Agora
                        </a>
                    @endif
                </div>

                <!-- Expander Manual Config -->
                <div x-data="{ open: false }">
                    <button @click="open = !open" type="button"
                        class="flex items-center text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors w-full justify-between group">
                        <span>Configuração Manual (Avançado)</span>
                        <i class="fas fa-chevron-down transition-transform duration-300" :class="{'rotate-180': open}"></i>
                    </button>

                    <div x-show="open" x-collapse style="display: none;" class="mt-4">
                        <form action="{{ route('settings.payment.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="provider" value="mercadopago">

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Public
                                        Key</label>
                                    <input type="text" name="public_key" value="{{ $mercadopago->public_key }}"
                                        class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-sm px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                        placeholder="APP_USR-...">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Access
                                        Token</label>
                                    <div class="relative">
                                        <input type="password" name="access_token" value="{{ $mercadopago->access_token }}"
                                            class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-sm px-4 py-2.5 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                            placeholder="APP_USR-...">
                                    </div>
                                </div>
                                <button type="submit"
                                    class="w-full py-2.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-sm hover:opacity-90 transition-all">
                                    Salvar Manualmente
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGSEGURO -->
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300 flex flex-col h-full">
            <div class="flex items-center gap-4 mb-6">
                <div
                    class="w-12 h-12 bg-lime-500/10 rounded-xl flex items-center justify-center p-2 text-lime-600 dark:text-lime-400">
                    <i class="fas fa-hand-holding-usd text-2xl"></i>
                    <!-- Ou logo do PagSeguro se tiver -->
                </div>
                <div>
                    <h3 class="font-extrabold text-lg text-slate-900 dark:text-white">PagSeguro</h3>
                    <div class="flex items-center gap-2 text-sm mt-0.5">
                        @if($pagseguro->enabled && $pagseguro->access_token)
                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-bold">
                                <i class="fas fa-check-circle mr-1"></i> Ativo
                            </span>
                        @else
                            <span class="inline-flex items-center text-slate-500 dark:text-slate-500">
                                <i class="fas fa-circle mr-1 text-[8px]"></i> Inativo
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex-1">
                <form action="{{ route('settings.payment.update') }}" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="provider" value="pagseguro">

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">E-mail da
                            Conta</label>
                        <input type="email" name="email" value="{{ $pagseguro->client_id }}"
                            class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white px-4 py-3 outline-none focus:ring-4 focus:ring-lime-500/10 focus:border-lime-500 transition-all"
                            placeholder="seu@email.com" required>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">E-mail cadastrado no PagSeguro.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Token</label>
                        <div class="relative">
                            <input type="password" name="token" value="{{ $pagseguro->access_token }}"
                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white px-4 py-3 outline-none focus:ring-4 focus:ring-lime-500/10 focus:border-lime-500 transition-all"
                                placeholder="Token gerado no painel" required>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Gere um token no painel do PagSeguro
                            (Preferências > Integrações).</p>
                    </div>

                    <div class="pt-4">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center rounded-xl bg-lime-600 text-white px-6 py-3.5 text-sm font-bold hover:bg-lime-700 transition-all shadow-lg shadow-lime-500/20 hover:shadow-xl hover:-translate-y-1">
                            <i class="fas fa-save mr-2"></i> Salvar PagSeguro
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Alpine.js for collapse (required if not already loaded) -->
    @push('scripts')
        <script src="//unpkg.com/alpinejs" defer></script>
    @endpush
@endsection