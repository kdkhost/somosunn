@extends('panel.layouts.app')

@section('title', 'Marketplace (Vendas) - UNN')

@section('panel_content')
    @php
        $paidTotal = (float) ($paidTotal ?? 0);
        $platformFeeTotal = (float) ($platformFeeTotal ?? 0);
        $netTotal = (float) ($netTotal ?? 0);
        $paidCount = (int) ($paidCount ?? 0);
        $pendingCount = (int) ($pendingCount ?? 0);
        $paymentsConfigured = (bool) ($paymentsConfigured ?? false);
        $platformFeePercent = (float) ($platformFeePercent ?? 0);
        $storefrontModuleInstalled = (bool) ($storefrontModuleInstalled ?? false);
    @endphp

    <div
        class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl rounded-[2.5rem] shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] border border-white/50 dark:border-slate-800/60 p-6 md:p-8 transition-all duration-500 overflow-hidden relative group/header">
        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-3xl group-hover/header:bg-blue-500/10 transition-all duration-700 pointer-events-none"></div>
        <div class="relative z-10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white transition-colors">Marketplace
                    (Vendas)</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">Acompanhe suas vendas e a comissão da
                    plataforma.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if($storefrontModuleInstalled)
                    <a href="{{ route('panel.marketplace.store.edit') }}"
                        class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-800 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <i class="fas fa-store mr-2"></i> Minha loja
                    </a>
                    <a href="{{ route('panel.marketplace.products.index') }}"
                        class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-800 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <i class="fas fa-box-open mr-2"></i> Produtos
                    </a>
                    <a href="{{ route('panel.marketplace.orders.index') }}"
                        class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-800 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        <i class="fas fa-truck mr-2"></i> Pedidos
                    </a>
                @endif
                <a href="{{ route('panel.marketplace.payments') }}"
                    class="inline-flex items-center justify-center rounded-full border border-blue-600 dark:border-blue-500 px-5 py-2.5 text-sm font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-600/10 transition-all">
                    <i class="fas fa-credit-card mr-2"></i> Pagamentos
                </a>
                <a href="{{ route('panel.marketplace.sales') }}"
                    class="inline-flex items-center justify-center rounded-full bg-blue-600 text-white px-5 py-2.5 text-sm font-bold hover:brightness-110 transition-all shadow-lg shadow-blue-500/20">
                    <i class="fas fa-receipt mr-2"></i> Minhas vendas
                </a>
                <a href="{{ route('panel.marketplace.accounting') }}"
                    class="inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-800 px-5 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> Contabilidade
                </a>
            </div>
        </div>
        </div>
    </div>

    @unless($storefrontModuleInstalled)
        <div class="mt-6 rounded-[2rem] border border-amber-200 bg-amber-50/90 p-6 text-amber-950 shadow-[0_12px_35px_-20px_rgba(245,158,11,0.45)] dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-100">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-600 dark:text-amber-300">
                    <i class="fas fa-triangle-exclamation text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black">Loja virtual pendente de instalacao</h2>
                    <p class="mt-1 text-sm font-medium text-amber-900/80 dark:text-amber-100/80">
                        Os atalhos de Minha loja, Produtos proprios e Pedidos da loja ficam disponiveis somente depois que a migration do modulo for executada neste servidor.
                    </p>
                    <div class="mt-4 rounded-2xl border border-amber-200/80 bg-white/80 px-4 py-3 text-sm font-bold text-slate-700 dark:border-amber-500/15 dark:bg-slate-950/40 dark:text-slate-100">
                        Execute no servidor: <code class="font-black text-amber-700 dark:text-amber-300">php artisan migrate --force</code>
                    </div>
                </div>
            </div>
        </div>
    @endunless

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mt-6">
        {{-- Saldo Mercado Pago --}}
        <div class="bg-gradient-to-br from-blue-700 via-indigo-800 to-slate-900 dark:from-blue-900/80 dark:via-indigo-950 dark:to-slate-950 rounded-[2.5rem] p-6 text-white shadow-[0_20px_60px_-15px_rgba(37,99,235,0.4)] dark:shadow-none border border-blue-400/20 dark:border-white/5 xl:col-span-2 relative overflow-hidden group">
            <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-48 h-48 bg-white/10 dark:bg-blue-500/10 rounded-full blur-[60px] group-hover:bg-white/15 transition-all duration-1000 pointer-events-none"></div>
            <div class="relative z-10">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-xs font-bold opacity-80 uppercase tracking-wider">Saldo Total Mercado Pago</div>
                    <div class="text-3xl font-extrabold mt-1">R$
                        {{ number_format($balance['total_amount'] ?? 0, 2, ',', '.') }}</div>
                </div>
                <div class="bg-white/20 p-2 rounded-xl">
                    <i class="fas fa-wallet text-xl"></i>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-white/10">
                <div>
                    <div class="text-[10px] font-bold opacity-70 uppercase">Disponível</div>
                    <div class="text-lg font-bold">R$ {{ number_format($balance['available_balance'] ?? 0, 2, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div class="text-[10px] font-bold opacity-70 uppercase">A liberar</div>
                    <div class="text-lg font-bold">R$ {{ number_format($balance['unavailable_balance'] ?? 0, 2, ',', '.') }}
                    </div>
                </div>
            </div>
            </div>
        </div>

        <div
            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl rounded-[2rem] border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] hover:-translate-y-1 transition-all duration-300 p-6 relative overflow-hidden group/card1">
            <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover/card1:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            <div class="relative z-10">
            <div class="text-sm font-bold text-slate-500 dark:text-slate-500 transition-colors">Vendas pagas</div>
            <div class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1 transition-colors">{{ $paidCount }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-2">Total de transações</div>
            </div>
        </div>
        <div
            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-3xl rounded-[2rem] border border-white/60 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:shadow-[0_20px_50px_rgba(37,99,235,0.1)] hover:-translate-y-1 transition-all duration-300 p-6 relative overflow-hidden group/card2">
            <div class="absolute inset-0 bg-gradient-to-br from-transparent to-black/5 dark:to-white/5 opacity-0 group-hover/card2:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            <div class="relative z-10">
            <div class="text-sm font-bold text-slate-500 dark:text-slate-500 transition-colors">Total líquido</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1 transition-colors">R$
                {{ number_format($netTotal, 2, ',', '.') }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 transition-colors">
                Bruto: R$ {{ number_format($paidTotal, 2, ',', '.') }} • Comissão: R$
                {{ number_format($platformFeeTotal, 2, ',', '.') }}
            </div>
            </div>
        </div>
    </div>

    <div
        class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-3xl rounded-[2.5rem] border border-white/50 dark:border-slate-800/60 shadow-[0_15px_40px_-15px_rgba(0,0,0,0.05)] dark:shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] hover:-translate-y-1 hover:shadow-[0_25px_50px_-12px_rgba(0,0,0,0.1)] transition-all duration-500 p-6 md:p-8 mt-6 relative overflow-hidden group/notice">
        <div class="absolute bottom-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full -mr-16 -mb-16 blur-3xl group-hover/notice:bg-amber-500/10 transition-all duration-700 pointer-events-none"></div>
        <div class="relative z-10">
        <div class="flex items-start gap-4">
            <div
                class="w-12 h-12 rounded-2xl {{ $paymentsConfigured ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400' }} flex items-center justify-center transition-colors">
                <i class="fas {{ $paymentsConfigured ? 'fa-check-circle' : 'fa-exclamation-triangle' }} text-xl"></i>
            </div>
            <div>
                <div class="font-extrabold text-slate-900 dark:text-white transition-colors">
                    {{ $paymentsConfigured ? 'Pagamentos configurados' : 'Pagamentos indisponíveis' }}
                </div>
                <div class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">
                    {{ $paymentsConfigured
        ? 'O gateway está configurado e pronto para receber pagamentos.'
        : 'O gateway ainda não foi configurado na plataforma. Fale com o administrador.' }}
                </div>
            </div>
        </div>
    </div>
@endsection
