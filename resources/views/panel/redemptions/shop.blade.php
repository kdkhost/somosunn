@extends('panel.layouts.app')

@section('title', 'Loja de Resgate UNNBIT')

@php
    $coinName  = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
    $unitValue = (float)  ($exchangeSettings['unit_value_brl'] ?? $exchangeSettings['point_value'] ?? 0.01);
    $userPoints = (int) Auth::user()->points;
@endphp

@section('panel_content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- HERO --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-indigo-600 to-violet-600 p-6 md:p-8 text-white shadow-xl">
        <div class="absolute -top-24 -right-24 w-64 h-64 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-56 h-56 rounded-full bg-fuchsia-400/20 blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex-1 min-w-0">
                <div class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-white/85 mb-2">
                    <i class="fas fa-gem"></i> Loja exclusiva
                </div>
                <h1 class="text-2xl md:text-3xl font-black mb-1">Troque {{ $coinName }} por recompensas</h1>
                <p class="text-sm text-white/85 max-w-xl">
                    Produtos digitais, servicos e experiencias exclusivas. Seus {{ $coinName }} consumidos nao podem ser recuperados.
                </p>
            </div>

            {{-- Saldo --}}
            <div class="flex items-center gap-3 rounded-2xl bg-white/15 backdrop-blur-sm px-5 py-4 border border-white/20">
                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center">
                    <i class="fas fa-coins text-xl"></i>
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-white/75">Seu saldo</div>
                    <div class="text-2xl font-black leading-tight">
                        {{ number_format($userPoints, 0, ',', '.') }}
                        <span class="text-sm font-bold opacity-85">{{ $coinName }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative mt-5 flex flex-wrap gap-3">
            <a href="{{ route('panel.redemptions.history') }}"
                class="inline-flex items-center gap-2 rounded-full bg-white/15 hover:bg-white/25 border border-white/20 px-4 py-2 text-sm font-bold transition-all">
                <i class="fas fa-history"></i> Meus resgates
            </a>
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/15 px-4 py-2 text-sm font-semibold">
                <i class="fas fa-exchange-alt text-xs"></i>
                1 {{ $coinName }} = R$ {{ number_format($unitValue, 4, ',', '.') }}
            </span>
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/15 px-4 py-2 text-sm font-semibold">
                <i class="fas fa-box-open text-xs"></i>
                {{ $items->total() }} {{ Str::plural('item', $items->total()) }} disponivel
            </span>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-800 p-4 text-emerald-800 dark:text-emerald-200 text-sm flex items-start gap-3">
            <i class="fas fa-check-circle mt-0.5"></i><span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 p-4 text-red-800 dark:text-red-200 text-sm flex items-start gap-3">
            <i class="fas fa-triangle-exclamation mt-0.5"></i><span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- GRID --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @forelse($items as $item)
            @php
                $canAfford = $userPoints >= (int) $item->points_cost;
                $hasStock  = (int) $item->stock !== 0;
                $available = $canAfford && $hasStock;
            @endphp

            <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">

                {{-- Imagem + badge preço --}}
                <div class="relative aspect-square w-full overflow-hidden bg-slate-50 dark:bg-slate-950">
                    @if($item->image)
                        <img src="{{ \App\Support\UploadStorage::url($item->image) }}"
                            alt="{{ $item->name }}"
                            loading="lazy"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-slate-300 dark:text-slate-700">
                            <i class="fas fa-gift text-4xl"></i>
                        </div>
                    @endif

                    {{-- Badge custo --}}
                    <div class="absolute top-2 left-2 inline-flex items-center gap-1 rounded-full bg-blue-600/95 backdrop-blur-sm px-2.5 py-1 text-[11px] font-black text-white shadow-md">
                        <i class="fas fa-coins text-[9px] opacity-90"></i>
                        {{ number_format((int) $item->points_cost, 0, ',', '.') }}
                    </div>

                    {{-- Badge estoque --}}
                    @if((int) $item->stock === 0)
                        <div class="absolute top-2 right-2 rounded-full bg-red-600 px-2 py-0.5 text-[9px] font-black text-white uppercase tracking-wider">Esgotado</div>
                    @elseif((int) $item->stock > 0 && (int) $item->stock <= 5)
                        <div class="absolute top-2 right-2 rounded-full bg-amber-500 px-2 py-0.5 text-[9px] font-black text-white uppercase tracking-wider">Ultimas {{ $item->stock }}</div>
                    @endif

                    {{-- Tipo --}}
                    <div class="absolute bottom-2 left-2 rounded-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm border border-slate-200/60 dark:border-slate-700/60 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-slate-700 dark:text-slate-200">
                        {{ $item->item_type_label }}
                    </div>
                </div>

                {{-- Body --}}
                <div class="flex flex-1 flex-col p-3 gap-2">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white leading-tight line-clamp-2" title="{{ $item->name }}">
                        {{ $item->name }}
                    </h3>

                    {{-- provider + entrega + valor --}}
                    <div class="flex flex-wrap gap-1 text-[10px] font-semibold">
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-0.5">
                            <i class="fas fa-store-alt text-[8px] opacity-75"></i>
                            {{ \Illuminate\Support\Str::limit($item->provider_label, 16) }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-0.5">
                            <i class="fas fa-truck text-[8px] opacity-75"></i>
                            {{ (int) ($item->delivery_lead_days ?? 7) }}d
                        </span>
                        @if($item->reference_value !== null)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 px-2 py-0.5">
                                R$ {{ number_format((float) $item->reference_value, 2, ',', '.') }}
                            </span>
                        @endif
                    </div>

                    {{-- Botão --}}
                    <form action="{{ route('panel.redemptions.redeem', $item) }}" method="POST" class="redeem-form mt-auto pt-2">
                        @csrf
                        <button type="button"
                            class="btn-redeem w-full inline-flex items-center justify-center gap-1.5 rounded-xl py-2 text-[12px] font-black transition-all
                                {{ $available
                                    ? 'bg-blue-600 text-white shadow hover:bg-blue-700 active:scale-95'
                                    : 'cursor-not-allowed bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600' }}"
                            {{ !$available ? 'disabled' : '' }}
                            data-name="{{ $item->name }}"
                            data-cost="{{ (int) $item->points_cost }}"
                            data-coin="{{ $coinName }}">
                            @if(!$hasStock)
                                <i class="fas fa-ban text-[10px]"></i> Indisponivel
                            @elseif(!$canAfford)
                                <i class="fas fa-lock text-[10px]"></i> Sem saldo
                            @else
                                <i class="fas fa-exchange-alt text-[10px]"></i> Resgatar
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-12 text-center">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                    <i class="fas fa-store-slash text-2xl text-slate-400"></i>
                </div>
                <h3 class="text-lg font-black text-slate-700 dark:text-slate-200 mb-1">Loja vazia no momento</h3>
                <p class="text-sm text-slate-500">Volte em breve para novas recompensas.</p>
            </div>
        @endforelse
    </div>

    {{-- Paginacao --}}
    @if($items->hasPages())
        <div class="pt-2">
            {{ $items->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.querySelectorAll('.btn-redeem').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) return;

            const name = this.dataset.name || 'este item';
            const cost = this.dataset.cost || '0';
            const coin = this.dataset.coin || 'UNNBIT';

            Swal.fire({
                title: 'Confirmar resgate?',
                html: '<div class="text-sm text-slate-600">Voce vai trocar <strong>' + cost + ' ' + coin + '</strong> por <strong>' + name + '</strong>.<br><span class="text-xs text-slate-400 mt-2 block">Os ' + coin + ' serao consumidos de forma definitiva.</span></div>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sim, trocar agora',
                cancelButtonText: 'Agora nao',
                background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.closest('form').submit();
                }
            });
        });
    });
</script>
@endpush
@endsection
