@extends('panel.layouts.app')

@section('title', 'Loja de Resgate UNNBIT')

@php
    $coinName = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
    $unitValue = (float) ($exchangeSettings['unit_value_brl'] ?? $exchangeSettings['point_value'] ?? 0.01);
@endphp

@section('panel_content')
<div class="space-y-8">
    <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-center">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white transition-colors">
                    Loja de Resgate {{ $coinName }}
                </h1>
                <p class="mt-1 font-medium text-slate-500 dark:text-slate-400 transition-colors">
                    Use seu saldo em {{ $coinName }} para trocar produtos fisicos, digitais e servicos.
                </p>
            </div>
            <a href="{{ route('panel.redemptions.history') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 px-6 py-3 font-bold text-slate-600 transition-all hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                <i class="fas fa-history"></i>
                Meus Resgates
            </a>
        </div>

        <div class="flex min-w-[280px] items-center gap-6 rounded-3xl bg-blue-600 p-6 shadow-2xl shadow-blue-500/30">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-2xl text-white">
                <i class="fas fa-coins"></i>
            </div>
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-blue-100">Seu saldo atual</div>
                <div class="text-3xl font-black text-white">
                    {{ number_format(Auth::user()->points, 0, ',', '.') }}
                    <span class="text-lg font-bold">{{ $coinName }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        @forelse($items as $item)
            <div class="group relative flex flex-col overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white p-2 shadow-sm transition-all hover:shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="absolute right-6 top-6 z-10 rounded-full border border-slate-100 bg-white px-4 py-1.5 text-sm font-black text-blue-600 shadow-lg dark:border-slate-700 dark:bg-slate-800 dark:text-blue-400">
                    {{ number_format((int) $item->points_cost, 0, ',', '.') }} {{ $coinName }}
                </div>

                <div class="aspect-square w-full overflow-hidden rounded-[2rem] bg-slate-50 dark:bg-slate-950">
                    @if($item->image)
                        <img src="{{ \App\Support\UploadStorage::url($item->image) }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                    @else
                        <div class="flex h-full w-full flex-col items-center justify-center text-slate-300 dark:text-slate-700">
                            <i class="fas fa-gift text-6xl"></i>
                        </div>
                    @endif
                </div>

                <div class="flex flex-1 flex-col p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                            {{ $item->item_type_label }}
                        </span>
                        @if($item->reference_value !== null)
                            <span class="text-sm font-bold text-slate-500 dark:text-slate-400">
                                R$ {{ number_format((float) $item->reference_value, 2, ',', '.') }}
                            </span>
                        @endif
                    </div>

                    <h3 class="mb-2 text-xl font-bold text-slate-900 dark:text-white transition-colors">{{ $item->name }}</h3>
                    <p class="mb-6 line-clamp-2 text-sm font-medium text-slate-500 dark:text-slate-400 transition-colors">
                        {{ \Illuminate\Support\Str::limit(strip_tags((string) $item->description), 110) }}
                    </p>

                    <div class="mt-auto space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                            <div><strong class="text-slate-700 dark:text-slate-200">Fornecedor:</strong> {{ $item->provider_label }}</div>
                            <div class="mt-1"><strong class="text-slate-700 dark:text-slate-200">Entrega:</strong> {{ (int) ($item->delivery_lead_days ?? 7) }} dias</div>
                            @if(filled($item->fulfillment_instructions))
                                <div class="mt-1 line-clamp-2"><strong class="text-slate-700 dark:text-slate-200">Regras:</strong> {{ strip_tags((string) $item->fulfillment_instructions) }}</div>
                            @endif
                        </div>

                        <div class="flex items-center justify-between text-xs font-bold transition-colors">
                            <span class="uppercase text-slate-400 dark:text-slate-600">Estoque</span>
                            <span class="{{ $item->stock > 0 || $item->stock < 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                {{ $item->stock < 0 ? 'Ilimitado' : ($item->stock > 0 ? $item->stock . ' unidades' : 'Esgotado') }}
                            </span>
                        </div>

                        <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs font-semibold text-blue-700 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-300">
                            1 {{ $coinName }} = R$ {{ number_format($unitValue, 4, ',', '.') }}
                        </div>

                        <form action="{{ route('panel.redemptions.redeem', $item) }}" method="POST" class="redeem-form">
                            @csrf
                            <button type="button"
                                class="btn-redeem flex w-full items-center justify-center gap-2 rounded-2xl py-4 text-sm font-black transition-all
                                {{ (Auth::user()->points >= $item->points_cost && $item->stock != 0)
                                    ? 'bg-blue-600 text-white shadow-xl shadow-blue-500/20 hover:bg-blue-700 active:scale-95'
                                    : 'cursor-not-allowed bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600' }}"
                                {{ (Auth::user()->points < $item->points_cost || $item->stock == 0) ? 'disabled' : '' }}>
                                <span>Trocar por {{ $coinName }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center">
                <div class="mb-4 text-6xl text-slate-400 opacity-10 dark:text-slate-600">
                    <i class="fas fa-store-slash"></i>
                </div>
                <p class="text-lg italic text-slate-500 dark:text-slate-400 transition-colors">Loja vazia no momento. Volte em breve.</p>
            </div>
        @endforelse
    </div>

    @if($items->hasPages())
        <div class="rounded-[2rem] border border-slate-200 bg-white px-6 py-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            {{ $items->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.querySelectorAll('.btn-redeem').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) return;

            Swal.fire({
                title: 'Confirma o resgate?',
                text: 'Os UNNBIT serao consumidos de forma definitiva do seu saldo.',
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
