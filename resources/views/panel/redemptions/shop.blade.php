@extends('panel.layouts.app')

@section('title', 'Loja de Resgate UNNBIT')

@php
    $coinName  = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
    $unitValue = (float)  ($exchangeSettings['unit_value_brl'] ?? $exchangeSettings['point_value'] ?? 0.01);
    $userPoints = (int) Auth::user()->points;
@endphp

@section('panel_content')
<div class="space-y-6">

    {{-- Card do cabecalho --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Resgate</p>
                <h1 class="mt-2 text-2xl md:text-3xl font-black text-slate-900 dark:text-white">Loja de Resgate</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-xl">
                    Troque seus {{ $coinName }} por recompensas exclusivas. Produtos digitais, servicos e experiencias.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Saldo em destaque --}}
                <div class="flex items-center gap-3 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 px-4 py-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-black text-blue-700 dark:text-blue-300 uppercase block">Seu saldo</span>
                        <span class="text-xl font-extrabold text-blue-900 dark:text-blue-100 leading-tight">
                            {{ number_format($userPoints, 0, ',', '.') }}
                            <span class="text-xs font-bold opacity-80">{{ $coinName }}</span>
                        </span>
                    </div>
                </div>

                <a href="{{ route('panel.redemptions.history') }}"
                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i class="fas fa-history text-slate-400"></i>
                    Meus resgates
                </a>
            </div>
        </div>

        {{-- Info bar interna --}}
        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-xs"></i>
                </div>
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Cotacao</div>
                    <div class="font-bold text-slate-700 dark:text-slate-200">1 {{ $coinName }} = R$ {{ number_format($unitValue, 4, ',', '.') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                    <i class="fas fa-box-open text-xs"></i>
                </div>
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Itens na loja</div>
                    <div class="font-bold text-slate-700 dark:text-slate-200">{{ $items->total() }} {{ $items->total() === 1 ? 'item disponivel' : 'itens disponiveis' }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i class="fas fa-info-circle text-xs"></i>
                </div>
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Importante</div>
                    <div class="font-bold text-slate-700 dark:text-slate-200">{{ $coinName }} consumidos nao voltam</div>
                </div>
            </div>
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

    {{-- Card com grid de produtos --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 md:p-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h2 class="text-lg font-black text-slate-900 dark:text-white">
                <i class="fas fa-store mr-2 text-blue-500"></i> Catalogo
            </h2>
            <span class="text-xs font-bold text-slate-400">
                Pagina {{ $items->currentPage() }} de {{ max(1, $items->lastPage()) }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($items as $item)
                @php
                    $canAfford = $userPoints >= (int) $item->points_cost;
                    $hasStock  = (int) $item->stock !== 0;
                    $available = $canAfford && $hasStock;
                @endphp

                <div class="group flex flex-col rounded-2xl border border-slate-200 dark:border-slate-700/60 bg-white dark:bg-slate-800/50 overflow-hidden hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-lg dark:hover:shadow-blue-900/20 transition-all duration-300">
                    {{-- Imagem --}}
                    <div class="relative aspect-[16/10] w-full overflow-hidden bg-slate-100 dark:bg-slate-900">
                        @if($item->image)
                            <img src="{{ \App\Support\UploadStorage::url($item->image) }}"
                                alt="{{ $item->name }}"
                                loading="lazy"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900">
                                <i class="fas fa-gift text-4xl text-slate-300 dark:text-slate-600"></i>
                            </div>
                        @endif

                        {{-- Badge custo (sempre visivel) --}}
                        <div class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600/90 backdrop-blur-sm px-2.5 py-1.5 shadow-lg">
                            <i class="fas fa-coins text-[10px] text-blue-200"></i>
                            <span class="text-xs font-black text-white">{{ number_format((int) $item->points_cost, 0, ',', '.') }}</span>
                            <span class="text-[9px] font-bold text-blue-200">{{ $coinName }}</span>
                        </div>

                        @if((int) $item->stock === 0)
                            <div class="absolute top-3 right-3 rounded-lg bg-red-600/90 backdrop-blur-sm px-2.5 py-1 text-[10px] font-black text-white uppercase tracking-wider shadow-lg">Esgotado</div>
                        @elseif((int) $item->stock > 0 && (int) $item->stock <= 5)
                            <div class="absolute top-3 right-3 rounded-lg bg-amber-500/90 backdrop-blur-sm px-2.5 py-1 text-[10px] font-black text-white uppercase tracking-wider shadow-lg">Ultimas {{ $item->stock }}</div>
                        @endif
                    </div>

                    {{-- Body --}}
                    <div class="flex flex-1 flex-col p-5">
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 dark:bg-slate-700/50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                {{ $item->item_type_label }}
                            </span>
                            @if($item->reference_value !== null)
                                <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                                    R$ {{ number_format((float) $item->reference_value, 2, ',', '.') }}
                                </span>
                            @endif
                        </div>

                        <h3 class="text-lg font-black text-slate-900 dark:text-white leading-snug line-clamp-2 mb-2" title="{{ $item->name }}">
                            {{ $item->name }}
                        </h3>

                        <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-3 mb-4 flex-1 leading-relaxed">
                            {{ \Illuminate\Support\Str::limit(strip_tags((string) $item->description), 120) }}
                        </p>

                        <div class="flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400 mb-4">
                            <span class="inline-flex items-center gap-1.5" title="Fornecedor">
                                <i class="fas fa-store-alt text-[10px] text-slate-400"></i>
                                <span class="font-semibold">{{ \Illuminate\Support\Str::limit($item->provider_label, 22) }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5" title="Prazo de entrega">
                                <i class="fas fa-clock text-[10px] text-slate-400"></i>
                                <span class="font-semibold">{{ (int) ($item->delivery_lead_days ?? 7) }} dias</span>
                            </span>
                        </div>

                        {{-- Botao --}}
                        <form action="{{ route('panel.redemptions.redeem', $item) }}" method="POST" class="redeem-form mt-auto">
                            @csrf
                            <button type="button"
                                class="btn-redeem w-full inline-flex items-center justify-center gap-2 rounded-xl py-3 text-sm font-black transition-all
                                    {{ $available
                                        ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20 hover:bg-blue-700 hover:shadow-blue-500/30 active:scale-[0.98]'
                                        : 'cursor-not-allowed bg-slate-100 dark:bg-slate-700/50 text-slate-400 dark:text-slate-500' }}"
                                {{ !$available ? 'disabled' : '' }}
                                data-name="{{ $item->name }}"
                                data-cost="{{ (int) $item->points_cost }}"
                                data-coin="{{ $coinName }}">
                                @if(!$hasStock)
                                    <i class="fas fa-ban text-xs"></i> Indisponivel
                                @elseif(!$canAfford)
                                    <i class="fas fa-lock text-xs"></i> Sem saldo
                                @else
                                    <i class="fas fa-exchange-alt text-xs"></i> Resgatar agora
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 p-12 text-center">
                    <div class="mx-auto w-14 h-14 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                        <i class="fas fa-store-slash text-xl text-slate-400"></i>
                    </div>
                    <h3 class="font-black text-slate-700 dark:text-slate-200 mb-1">Loja vazia no momento</h3>
                    <p class="text-sm text-slate-500">Volte em breve para novas recompensas.</p>
                </div>
            @endforelse
        </div>

        @if($items->hasPages())
            <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                {{ $items->links() }}
            </div>
        @endif
    </div>
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
