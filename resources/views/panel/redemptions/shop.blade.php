@extends('panel.layouts.app')

@section('title', 'Loja de Resgate UNNBIT')

@php
    $coinName  = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
    $unitValue = (float)  ($exchangeSettings['unit_value_brl'] ?? $exchangeSettings['point_value'] ?? 0.01);
    $userPoints = (int) Auth::user()->points;
@endphp

@section('panel_content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header limpo --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                Loja de Resgate
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Troque seus {{ $coinName }} por recompensas exclusivas.
            </p>
        </div>

        <div class="flex items-center gap-3">
            {{-- Saldo --}}
            <div class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5">
                <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Saldo</div>
                    <div class="font-black text-slate-900 dark:text-white leading-tight">
                        {{ number_format($userPoints, 0, ',', '.') }}
                        <span class="text-xs font-bold text-slate-500">{{ $coinName }}</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('panel.redemptions.history') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                <i class="fas fa-history text-slate-400"></i>
                <span class="hidden sm:inline">Meus resgates</span>
            </a>
        </div>
    </div>

    {{-- Barra informativa --}}
    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 px-4 py-3 text-xs">
        <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
            <i class="fas fa-exchange-alt text-slate-400"></i>
            <span>1 {{ $coinName }} = <strong class="text-slate-900 dark:text-white">R$ {{ number_format($unitValue, 4, ',', '.') }}</strong></span>
        </div>
        <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
            <i class="fas fa-box-open text-slate-400"></i>
            <span><strong class="text-slate-900 dark:text-white">{{ $items->total() }}</strong> {{ Str::plural('item', $items->total()) }} {{ $items->total() > 1 ? 'disponiveis' : 'disponivel' }}</span>
        </div>
        <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
            <i class="fas fa-info-circle text-slate-400"></i>
            <span>{{ $coinName }} consumidos nao podem ser recuperados</span>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-800 p-3 text-emerald-800 dark:text-emerald-200 text-sm flex items-start gap-2">
            <i class="fas fa-check-circle mt-0.5"></i><span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 p-3 text-red-800 dark:text-red-200 text-sm flex items-start gap-2">
            <i class="fas fa-triangle-exclamation mt-0.5"></i><span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- GRID --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($items as $item)
            @php
                $canAfford = $userPoints >= (int) $item->points_cost;
                $hasStock  = (int) $item->stock !== 0;
                $available = $canAfford && $hasStock;
            @endphp

            <div class="group flex flex-col rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-md transition-all duration-200">

                {{-- Imagem --}}
                <div class="relative aspect-[4/3] w-full overflow-hidden bg-slate-50 dark:bg-slate-950">
                    @if($item->image)
                        <img src="{{ \App\Support\UploadStorage::url($item->image) }}"
                            alt="{{ $item->name }}"
                            loading="lazy"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-slate-300 dark:text-slate-700">
                            <i class="fas fa-gift text-3xl"></i>
                        </div>
                    @endif

                    {{-- Overlays minimos --}}
                    @if((int) $item->stock === 0)
                        <div class="absolute top-2 right-2 rounded-md bg-red-600 px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider shadow">Esgotado</div>
                    @elseif((int) $item->stock > 0 && (int) $item->stock <= 5)
                        <div class="absolute top-2 right-2 rounded-md bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider shadow">Ultimas {{ $item->stock }}</div>
                    @endif
                </div>

                {{-- Body --}}
                <div class="flex flex-1 flex-col p-4">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            {{ $item->item_type_label }}
                        </span>
                        @if($item->reference_value !== null)
                            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                R$ {{ number_format((float) $item->reference_value, 2, ',', '.') }}
                            </span>
                        @endif
                    </div>

                    <h3 class="text-base font-black text-slate-900 dark:text-white leading-snug line-clamp-2 mb-2" title="{{ $item->name }}">
                        {{ $item->name }}
                    </h3>

                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 mb-3 flex-1">
                        {{ \Illuminate\Support\Str::limit(strip_tags((string) $item->description), 90) }}
                    </p>

                    <div class="flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400 mb-3">
                        <span class="inline-flex items-center gap-1" title="Fornecedor">
                            <i class="fas fa-store-alt text-[10px]"></i>
                            {{ \Illuminate\Support\Str::limit($item->provider_label, 20) }}
                        </span>
                        <span class="inline-flex items-center gap-1" title="Prazo de entrega">
                            <i class="fas fa-clock text-[10px]"></i>
                            {{ (int) ($item->delivery_lead_days ?? 7) }}d
                        </span>
                    </div>

                    {{-- Custo + botao --}}
                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <div class="min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Custa</div>
                            <div class="font-black text-blue-600 dark:text-blue-400 text-sm leading-tight">
                                {{ number_format((int) $item->points_cost, 0, ',', '.') }}
                                <span class="text-[10px] font-bold opacity-75">{{ $coinName }}</span>
                            </div>
                        </div>

                        <form action="{{ route('panel.redemptions.redeem', $item) }}" method="POST" class="redeem-form">
                            @csrf
                            <button type="button"
                                class="btn-redeem inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-black transition-all
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
            </div>
        @empty
            <div class="col-span-full rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-12 text-center">
                <div class="mx-auto w-14 h-14 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                    <i class="fas fa-store-slash text-xl text-slate-400"></i>
                </div>
                <h3 class="font-black text-slate-700 dark:text-slate-200 mb-1">Loja vazia no momento</h3>
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
