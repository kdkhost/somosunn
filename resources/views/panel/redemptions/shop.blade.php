@extends('panel.layouts.app')

@section('title', 'Loja de Resgate de Pontos')

@section('content')
<div class="space-y-8">
    {{-- Header & Balance --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white transition-colors">
                Troque seus Pontos
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1 font-medium transition-colors">Use seus pontos acumulados para resgatar prêmios, cursos e mentorias.</p>
        </div>

        <div class="bg-blue-600 rounded-3xl p-6 shadow-2xl shadow-blue-500/30 flex items-center gap-6 min-w-[280px]">
            <div class="w-14 h-14 rounded-2xl bg-white/20 text-white flex items-center justify-center text-2xl">
                <i class="fas fa-coins"></i>
            </div>
            <div>
                <div class="text-blue-100 text-xs font-bold uppercase tracking-wider">Seu Saldo Atual</div>
                <div class="text-white text-3xl font-black">{{ number_format(Auth::user()->points, 0, ',', '.') }} <span class="text-lg font-bold">pts</span></div>
            </div>
        </div>
    </div>

    {{-- Items Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        @forelse($items as $item)
        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-200 dark:border-slate-800 p-2 flex flex-col transition-all hover:shadow-2xl group relative overflow-hidden">
            {{-- Badge Custo --}}
            <div class="absolute top-6 right-6 z-10 bg-white dark:bg-slate-800 px-4 py-1.5 rounded-full shadow-lg border border-slate-100 dark:border-slate-700 text-blue-600 dark:text-blue-400 font-black text-sm transition-colors">
                {{ number_format($item->points_cost, 0, ',', '.') }} pts
            </div>

            {{-- Image --}}
            <div class="aspect-square w-full rounded-[2rem] overflow-hidden bg-slate-50 dark:bg-slate-950 transition-colors">
                @if($item->image)
                    <img src="{{ asset('storage/' . $item->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 dark:text-slate-700">
                        <i class="fas fa-gift text-6xl"></i>
                    </div>
                @endif
            </div>

            <div class="p-6 flex-1 flex flex-col">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2 transition-colors">{{ $item->name }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 mb-6 font-medium transition-colors">
                    {{ $item->description }}
                </p>

                <div class="mt-auto space-y-4">
                    <div class="flex items-center justify-between text-xs font-bold transition-colors">
                        <span class="text-slate-400 dark:text-slate-600 uppercase">Estoque</span>
                        <span class="{{ $item->stock > 0 ? 'text-emerald-500' : 'text-red-500' }}">
                            {{ $item->stock > 0 ? $item->stock . ' unidades' : 'Esgotado' }}
                        </span>
                    </div>

                    <form action="{{ route('redemptions.redeem', $item) }}" method="POST" class="redeem-form">
                        @csrf
                        <button type="button" 
                                class="w-full py-4 rounded-2xl font-black text-sm transition-all flex items-center justify-center gap-2 btn-redeem
                                {{ (Auth::user()->points >= $item->points_cost && $item->stock > 0) 
                                    ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-xl shadow-blue-500/20 active:scale-95' 
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed' }}"
                                {{ (Auth::user()->points < $item->points_cost || $item->stock == 0) ? 'disabled' : '' }}>
                            <span>Resgatar Agora</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center">
            <div class="text-slate-400 dark:text-slate-600 text-6xl mb-4 opacity-10">
                <i class="fas fa-store-slash"></i>
            </div>
            <p class="text-slate-500 dark:text-slate-400 italic text-lg transition-colors">Loja vazia no momento. Volte em breve!</p>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.btn-redeem').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) return;

            Swal.fire({
                title: 'Confirma o Resgate?',
                text: "Os pontos serão descontados do seu saldo.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sim, resgatar!',
                cancelButtonText: 'Agora não',
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
