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
    @endphp

    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 transition-colors duration-300">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white transition-colors">Marketplace (Vendas)</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 transition-colors">Acompanhe suas vendas e a comissão da plataforma.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('panel.marketplace.payments') }}"
                   class="inline-flex items-center justify-center rounded-full border border-blue-600 dark:border-blue-500 px-5 py-2.5 text-sm font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-600/10 transition-all">
                    <i class="fas fa-credit-card mr-2"></i> Pagamentos
                </a>
                <a href="{{ route('panel.marketplace.sales') }}"
                   class="inline-flex items-center justify-center rounded-full bg-blue-600 text-white px-5 py-2.5 text-sm font-bold hover:brightness-110 transition-all shadow-lg shadow-blue-500/20">
                    <i class="fas fa-receipt mr-2"></i> Minhas vendas
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mt-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-5 transition-colors duration-300">
            <div class="text-sm font-bold text-slate-500 dark:text-slate-500 transition-colors">Vendas pagas</div>
            <div class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1 transition-colors">{{ $paidCount }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-5 transition-colors duration-300">
            <div class="text-sm font-bold text-slate-500 dark:text-slate-500 transition-colors">Pendentes</div>
            <div class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1 transition-colors">{{ $pendingCount }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-5 transition-colors duration-300">
            <div class="text-sm font-bold text-slate-500 dark:text-slate-500 transition-colors">Total líquido</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1 transition-colors">R$ {{ number_format($netTotal, 2, ',', '.') }}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 transition-colors">
                Bruto: R$ {{ number_format($paidTotal, 2, ',', '.') }} • Comissão: R$ {{ number_format($platformFeeTotal, 2, ',', '.') }}
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-5 transition-colors duration-300">
            <div class="text-sm font-bold text-slate-500 dark:text-slate-500 transition-colors">Taxa da plataforma</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1 transition-colors">{{ number_format($platformFeePercent, 2, ',', '.') }}%</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-2 transition-colors">
                Configurada pelo administrador.
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 mt-6 transition-colors duration-300">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl {{ $paymentsConfigured ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400' }} flex items-center justify-center transition-colors">
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

