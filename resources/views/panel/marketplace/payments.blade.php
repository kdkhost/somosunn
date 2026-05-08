@extends('panel.layouts.app')

@section('title', 'Pagamentos do Marketplace - UNN')

@section('panel_content')
    @php
        $isAdmin = auth()->user() && auth()->user()->isAdmin();

        // MercadoPago
        $mpConfigured = (bool) ($paymentsConfigured ?? false);
        $mpEnabled = (int) (\App\Models\Setting::get('mercadopago_enabled', 1)) === 1;

        $mpMethods = [];
        if ((int) \App\Models\Setting::get('mercadopago_method_credit_card', 1) === 1) $mpMethods[] = 'Cartão de crédito';
        if ((int) \App\Models\Setting::get('mercadopago_method_debit_card', 0) === 1) $mpMethods[] = 'Cartão de débito';
        if ((int) \App\Models\Setting::get('mercadopago_method_pix', 1) === 1) $mpMethods[] = 'Pix';
        if ((int) \App\Models\Setting::get('mercadopago_method_ticket', 0) === 1) $mpMethods[] = 'Boleto';

        $mpWebhookUrl = (string) ($webhookUrl ?? route('api.webhooks.mercadopago'));

        // SumUp
        $sumupEnabled = (int) (\App\Models\Setting::get('sumup_enabled', 0)) === 1;
        $sumupApiKey = (string) (\App\Models\Setting::get('sumup_api_key', '') ?? '');
        $sumupMerchantCode = (string) (\App\Models\Setting::get('sumup_merchant_code', '') ?? '');
        $sumupConfigured = $sumupApiKey !== '' && $sumupMerchantCode !== '';

        $sumupMethods = [];
        if ((int) \App\Models\Setting::get('sumup_method_card', 1) === 1) $sumupMethods[] = 'Cartão';
        if ((int) \App\Models\Setting::get('sumup_method_pix', 1) === 1) $sumupMethods[] = 'Pix';

        $sumupWebhookUrl = route('api.webhooks.sumup');

        $totalActive = ($mpEnabled && $mpConfigured ? 1 : 0) + ($sumupEnabled && $sumupConfigured ? 1 : 0);
    @endphp

    {{-- Header --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">Pagamentos</p>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white transition-colors mt-1">
                    Gateways da plataforma
                </h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors text-sm">
                    Configure MercadoPago e/ou SumUp. Ambos podem funcionar em paralelo.
                </p>
            </div>
            <a href="{{ route('panel.marketplace.index') }}"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-800 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    {{-- Status geral --}}
    <div class="mt-6 rounded-2xl p-4 border-2
        {{ $totalActive === 0 ? 'bg-red-50 dark:bg-red-900/10 border-red-200 dark:border-red-800/40' : '' }}
        {{ $totalActive === 1 ? 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800/40' : '' }}
        {{ $totalActive >= 2 ? 'bg-emerald-50 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-800/40' : '' }}">
        <div class="flex items-start gap-3">
            <i class="fas {{ $totalActive === 0 ? 'fa-exclamation-triangle text-red-600' : ($totalActive === 1 ? 'fa-info-circle text-amber-600' : 'fa-check-circle text-emerald-600') }} text-xl mt-0.5"></i>
            <div class="flex-1">
                <p class="font-black {{ $totalActive === 0 ? 'text-red-800 dark:text-red-300' : ($totalActive === 1 ? 'text-amber-800 dark:text-amber-300' : 'text-emerald-800 dark:text-emerald-300') }}">
                    @if($totalActive === 0)
                        Nenhum gateway ativo
                    @elseif($totalActive === 1)
                        1 gateway ativo
                    @else
                        {{ $totalActive }} gateways ativos em paralelo
                    @endif
                </p>
                <p class="text-sm mt-1 {{ $totalActive === 0 ? 'text-red-700' : ($totalActive === 1 ? 'text-amber-700' : 'text-emerald-700') }}">
                    @if($totalActive === 0)
                        Configure ao menos um gateway para aceitar pagamentos na plataforma.
                    @elseif($totalActive === 1)
                        Adicione o segundo gateway para oferecer mais opções aos compradores.
                    @else
                        Os compradores poderão escolher entre {{ implode(' e ', array_filter([($mpEnabled && $mpConfigured ? 'MercadoPago' : null), ($sumupEnabled && $sumupConfigured ? 'SumUp' : null)])) }} no checkout.
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Grid de gateways --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- MercadoPago --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center">
                        <i class="fas fa-hand-holding-dollar text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white">MercadoPago</h3>
                        <p class="text-xs text-slate-500">Cartão, Pix, Boleto</p>
                    </div>
                </div>
                @if($mpEnabled && $mpConfigured)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-black">
                        <i class="fas fa-check-circle text-[10px]"></i> ATIVO
                    </span>
                @elseif($mpConfigured)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-black">
                        <i class="fas fa-pause text-[10px]"></i> PAUSADO
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400 text-xs font-black">
                        <i class="fas fa-times text-[10px]"></i> INATIVO
                    </span>
                @endif
            </div>
            <div class="p-6">
                @if(!empty($mpMethods))
                    <p class="text-xs font-black uppercase tracking-wider text-slate-500 mb-2">Métodos ativos</p>
                    <div class="flex flex-wrap gap-1.5 mb-4">
                        @foreach($mpMethods as $method)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                <i class="fas fa-check text-emerald-500 text-[9px]"></i>
                                {{ $method }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 mb-4">Nenhum método configurado ainda.</p>
                @endif

                <a href="{{ route('panel.marketplace.payments.edit') }}"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 text-sm font-bold transition-all shadow-lg shadow-blue-500/20">
                    <i class="fas fa-cogs mr-2"></i>
                    {{ $mpConfigured ? 'Editar configuração' : 'Configurar MercadoPago' }}
                </a>

                @if($mpWebhookUrl)
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500 mb-2 flex items-center gap-1">
                            <i class="fas fa-link text-slate-400"></i> Webhook URL
                        </p>
                        <div class="flex rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                            <input type="text" readonly value="{{ $mpWebhookUrl }}"
                                class="flex-1 px-3 py-2 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-mono border-0 outline-none">
                            <button type="button" onclick="copyGatewayUrl('{{ $mpWebhookUrl }}')"
                                class="px-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- SumUp --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center">
                        <i class="fas fa-credit-card text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white">SumUp</h3>
                        <p class="text-xs text-slate-500">Cartão, Pix</p>
                    </div>
                </div>
                @if($sumupEnabled && $sumupConfigured)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-black">
                        <i class="fas fa-check-circle text-[10px]"></i> ATIVO
                    </span>
                @elseif($sumupConfigured)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-black">
                        <i class="fas fa-pause text-[10px]"></i> PAUSADO
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400 text-xs font-black">
                        <i class="fas fa-times text-[10px]"></i> INATIVO
                    </span>
                @endif
            </div>
            <div class="p-6">
                @if(!empty($sumupMethods))
                    <p class="text-xs font-black uppercase tracking-wider text-slate-500 mb-2">Métodos ativos</p>
                    <div class="flex flex-wrap gap-1.5 mb-4">
                        @foreach($sumupMethods as $method)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                <i class="fas fa-check text-emerald-500 text-[9px]"></i>
                                {{ $method }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 mb-4">SumUp ainda não foi configurado.</p>
                @endif

                @if($isAdmin)
                    <a href="{{ route('panel.admin.settings', ['group' => 'gateway']) }}"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-3 text-sm font-bold transition-all shadow-lg">
                        <i class="fas fa-cogs mr-2"></i>
                        {{ $sumupConfigured ? 'Editar configuração' : 'Configurar SumUp' }}
                    </a>
                @else
                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800 p-4 text-center">
                        <i class="fas fa-info-circle text-slate-400 mb-2"></i>
                        <p class="text-xs text-slate-500">Configurado globalmente pelo administrador</p>
                    </div>
                @endif

                @if($sumupWebhookUrl)
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-500 mb-2 flex items-center gap-1">
                            <i class="fas fa-link text-slate-400"></i> Webhook URL
                        </p>
                        <div class="flex rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                            <input type="text" readonly value="{{ $sumupWebhookUrl }}"
                                class="flex-1 px-3 py-2 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-mono border-0 outline-none">
                            <button type="button" onclick="copyGatewayUrl('{{ $sumupWebhookUrl }}')"
                                class="px-3 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Info cards --}}
    <div class="mt-6 bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
        <h3 class="text-base font-extrabold text-slate-900 dark:text-white mb-4">Como funciona o multi-gateway</h3>
        <div class="grid gap-4 md:grid-cols-3">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white text-sm">Paralelo ou único</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Você decide: use só um gateway ou ambos ao mesmo tempo.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-hand-pointer"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white text-sm">Escolha do cliente</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Com ambos ativos, o comprador escolhe qual usar no checkout.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white text-sm">Fallback automático</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Se um falhar, o sistema oferece o outro (quando configurado).</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function copyGatewayUrl(url) {
                navigator.clipboard.writeText(url).then(() => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'Copiado!', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                    } else if (typeof toastr !== 'undefined') {
                        toastr.success('Copiado!');
                    } else {
                        alert('Copiado!');
                    }
                });
            }
        </script>
    @endpush
@endsection
