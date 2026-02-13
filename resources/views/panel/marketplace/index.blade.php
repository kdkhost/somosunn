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

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 md:gap-4 flex-wrap">
            <div>
                <h1 class="text-xl md:text-3xl font-extrabold text-slate-900">Marketplace (Vendas)</h1>
                <p class="text-slate-600 mt-1">Acompanhe suas vendas e a comissão da plataforma.</p>
            </div>
            <div class="flex flex-col sm:flex-row flex-wrap gap-1 sm:gap-3 w-full sm:w-auto mt-3 md:mt-0">
                <a href="{{ route('panel.marketplace.payments') }}"
                    class="inline-flex items-center justify-center rounded-full border border-[#1F5EDB] px-5 py-2.5 text-sm font-bold text-[#1F5EDB] hover:bg-[#1F5EDB]/10 transition">
                    <i class="fas fa-credit-card mr-2"></i> Pagamentos
                </a>
                <a href="{{ route('panel.marketplace.sales') }}"
                    class="inline-flex items-center justify-center rounded-full bg-[#1F5EDB] px-5 py-2.5 text-sm font-bold text-white hover:brightness-110 transition">
                    <i class="fas fa-receipt mr-2"></i> Minhas vendas
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-5 mt-4 sm:mt-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
            <div class="text-xs sm:text-sm font-bold text-slate-500">Vendas pagas</div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $paidCount }}</div>
        </div>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
            <div class="text-xs sm:text-sm font-bold text-slate-500">Pendentes</div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ $pendingCount }}</div>
        </div>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
            <div class="text-xs sm:text-sm font-bold text-slate-500">Total líquido</div>
            <div class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-1">R$ {{ number_format($netTotal, 2, ',', '.') }}</div>
            <div class="text-xs text-slate-500 mt-2">
                Bruto: R$ {{ number_format($paidTotal, 2, ',', '.') }} • Comissão: R$ {{ number_format($platformFeeTotal, 2, ',', '.') }}
            </div>
        </div>
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5">
            <div class="text-sm font-bold text-slate-500">Taxa da plataforma</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($platformFeePercent, 2, ',', '.') }}%</div>
            <div class="text-xs text-slate-500 mt-2">
                Configurada pelo administrador.
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mt-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl {{ $paymentsConfigured ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600' }} flex items-center justify-center">
                <i class="fas {{ $paymentsConfigured ? 'fa-check-circle' : 'fa-exclamation-triangle' }} text-xl"></i>
            </div>
            <div>
                <div class="font-extrabold text-slate-900">
                    {{ $paymentsConfigured ? 'Pagamentos configurados' : 'Pagamentos indisponíveis' }}
                </div>
                <div class="text-slate-600 mt-1">
                    {{ $paymentsConfigured
                        ? 'O gateway está configurado e pronto para receber pagamentos.'
                        : 'O gateway ainda não foi configurado na plataforma. Fale com o administrador.' }}
                </div>
            </div>
        </div>
    </div>
@endsection

