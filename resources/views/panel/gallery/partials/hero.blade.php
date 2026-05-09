<section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white shadow-lg">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(59,130,246,0.15),_transparent_50%)]"></div>

    <div class="relative p-6 md:p-8">
        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/20 text-blue-300">
                    <i class="fas fa-camera-retro text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight md:text-2xl">Galeria</h1>
                    <p class="mt-0.5 text-sm text-slate-300">
                        {{ $isAdmin ? 'Todas as mídias da plataforma' : 'Seus uploads e eventos que organiza' }}
                    </p>
                </div>
            </div>

            <button type="button" data-gallery-open-upload
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/25 transition hover:-translate-y-0.5 hover:bg-blue-500">
                <i class="fas fa-cloud-upload-alt"></i> Adicionar mídias
            </button>
        </div>

        {{-- Stats compactos --}}
        <div class="mt-5 grid grid-cols-3 gap-3">
            <div class="rounded-xl border border-white/10 bg-white/5 p-3 backdrop-blur-sm">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Mídias</p>
                <p class="mt-1 text-2xl font-black">{{ number_format($stats['visible_total'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3 backdrop-blur-sm">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Eventos</p>
                <p class="mt-1 text-2xl font-black">{{ number_format($stats['event_coverage'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-white/5 p-3 backdrop-blur-sm">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Meus envios</p>
                <p class="mt-1 text-2xl font-black">{{ number_format($stats['my_uploads'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</section>
