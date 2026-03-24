<section class="relative overflow-hidden rounded-[2.5rem] bg-slate-950 text-white shadow-[0_24px_80px_rgba(15,23,42,0.28)]">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.35),_transparent_36%),radial-gradient(circle_at_80%_10%,_rgba(14,165,233,0.22),_transparent_28%),linear-gradient(135deg,_#020617_0%,_#0f172a_48%,_#111827_100%)]"></div>
    <div class="absolute -right-10 top-10 h-40 w-40 rounded-full bg-blue-500/20 blur-3xl"></div>
    <div class="absolute bottom-0 left-20 h-32 w-32 rounded-full bg-cyan-400/10 blur-3xl"></div>

    <div class="relative p-8 md:p-10 xl:p-12">
        <div class="flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-[11px] font-black uppercase tracking-[0.25em] text-blue-100">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400 shadow-[0_0_0_6px_rgba(74,222,128,0.12)]"></span>
                    Curadoria visual da comunidade
                </div>

                <div class="mt-6 flex flex-wrap items-start gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-[1.6rem] bg-white/10 text-2xl text-blue-200 shadow-lg shadow-blue-900/20 ring-1 ring-white/10">
                        <i class="fas fa-camera-retro"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-white md:text-5xl">Galeria coletiva para reviver cada encontro</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300 md:text-base">
                            Centralize os registros dos eventos, acompanhe a cobertura por album e publique novas midias em uma experiencia coerente com a vitrine publica da galeria.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if($selectedEvent)
                        <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/8 px-4 py-3 text-sm text-slate-200">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-blue-500/20 text-blue-200">
                                <i class="fas fa-calendar-day"></i>
                            </span>
                            <span>
                                <strong class="font-bold text-white">{{ $selectedEvent->title }}</strong>
                                @if($selectedEventDate)
                                    <span class="text-slate-400"> • {{ $selectedEventDate }}</span>
                                @endif
                            </span>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/8 px-4 py-3 text-sm text-slate-200">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-cyan-400/20 text-cyan-200">
                                <i class="fas fa-images"></i>
                            </span>
                            Visualizando toda a cobertura disponivel no painel
                        </div>
                    @endif

                    <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/8 px-4 py-3 text-sm text-slate-200">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full {{ $isAdmin ? 'bg-emerald-400/20 text-emerald-200' : 'bg-blue-500/20 text-blue-200' }}">
                            <i class="fas {{ $isAdmin ? 'fa-shield-halved' : 'fa-user-circle' }}"></i>
                        </span>
                        {{ $isAdmin ? 'Modo admin ativo: voce enxerga toda a galeria.' : 'Modo painel: voce ve seus uploads e as midias dos eventos que organiza.' }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row xl:flex-col xl:min-w-[16rem]">
                <button type="button"
                    data-gallery-open-upload
                    class="inline-flex items-center justify-center gap-3 rounded-[1.5rem] bg-blue-600 px-6 py-4 text-sm font-black uppercase tracking-[0.18em] text-white shadow-[0_20px_45px_rgba(37,99,235,0.35)] transition hover:-translate-y-0.5 hover:bg-blue-500">
                    <i class="fas fa-cloud-upload-alt text-base"></i>
                    Adicionar midias
                </button>

                @if($selectedEventId > 0)
                    <a href="{{ route('panel.gallery.index') }}"
                        class="inline-flex items-center justify-center gap-3 rounded-[1.5rem] border border-white/10 bg-white/5 px-6 py-4 text-sm font-bold text-slate-200 transition hover:border-white/20 hover:bg-white/10">
                        <i class="fas fa-rotate-left"></i>
                        Limpar filtro
                    </a>
                @endif
            </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-[1.8rem] border border-white/10 bg-white/8 p-5 backdrop-blur">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">{{ $visibleLabel }}</p>
                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-4xl font-black text-white">{{ number_format($stats['visible_total'] ?? 0, 0, ',', '.') }}</p>
                        <p class="mt-2 text-sm text-slate-300">Midias renderizadas na busca atual do painel.</p>
                    </div>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500/15 text-blue-200">
                        <i class="fas fa-image"></i>
                    </span>
                </div>
            </div>

            <div class="rounded-[1.8rem] border border-white/10 bg-white/8 p-5 backdrop-blur">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-400">Cobertura por eventos</p>
                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-4xl font-black text-white">{{ number_format($stats['event_coverage'] ?? 0, 0, ',', '.') }}</p>
                        <p class="mt-2 text-sm text-slate-300">Eventos com ao menos um registro visivel.</p>
                    </div>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400/15 text-cyan-200">
                        <i class="fas fa-calendar-check"></i>
                    </span>
                </div>
            </div>

            <div class="rounded-[1.8rem] border border-white/10 bg-gradient-to-br from-blue-600/22 to-cyan-400/12 p-5 backdrop-blur">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-slate-300">Minhas contribuicoes</p>
                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-4xl font-black text-white">{{ number_format($stats['my_uploads'] ?? 0, 0, ',', '.') }}</p>
                        <p class="mt-2 text-sm text-slate-200">Uploads associados a sua conta na galeria.</p>
                    </div>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/12 text-white">
                        <i class="fas fa-user-astronaut"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
