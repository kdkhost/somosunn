<section class="rounded-[2.25rem] border border-slate-200/70 bg-white/95 p-6 shadow-sm shadow-slate-200/60 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-none">
    <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
        <div class="max-w-2xl">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600 dark:text-blue-400">Filtro inteligente</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white">Organize a leitura da galeria sem perder contexto</h2>
            <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                Selecione um evento para enxergar somente a cobertura daquela experiencia. O restante da pagina acompanha o filtro automaticamente.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                <span class="inline-flex h-2.5 w-2.5 rounded-full {{ $isAdmin ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
                {{ $isAdmin ? 'Admin ativo' : 'Minha visao' }}
            </div>
            <button type="button"
                data-gallery-open-upload
                class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 dark:border-blue-900/60 dark:bg-blue-900/20 dark:text-blue-300">
                <i class="fas fa-plus"></i>
                Novo envio
            </button>
        </div>
    </div>

    <form method="GET" action="{{ route('panel.gallery.index') }}" class="mt-6 grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto_auto] lg:items-end">
        <div>
            <label for="gallery-event-filter" class="mb-2 block text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">
                Filtrar por evento
            </label>
            <div class="relative">
                <select id="gallery-event-filter" name="event_id"
                    class="w-full appearance-none rounded-[1.6rem] border border-slate-200 bg-slate-50 px-5 py-4 pr-12 text-sm font-bold text-slate-800 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-500 dark:focus:ring-blue-500/10">
                    <option value="">Todos os eventos</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected($selectedEventId === (int) $event->id)>
                            {{ $event->title }}@if($event->start_at) • {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }}@endif
                        </option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-5 flex items-center text-slate-400">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </div>
        </div>

        <button type="submit"
            class="inline-flex items-center justify-center gap-3 rounded-[1.4rem] bg-slate-950 px-6 py-4 text-sm font-black uppercase tracking-[0.16em] text-white shadow-lg shadow-slate-950/10 transition hover:-translate-y-0.5 hover:bg-slate-900 dark:bg-blue-600 dark:hover:bg-blue-500">
            <i class="fas fa-filter"></i>
            Filtrar
        </button>

        @if($selectedEventId > 0)
            <a href="{{ route('panel.gallery.index') }}"
                class="inline-flex items-center justify-center gap-3 rounded-[1.4rem] border border-slate-200 bg-white px-6 py-4 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-white">
                <i class="fas fa-xmark"></i>
                Limpar
            </a>
        @endif
    </form>
</section>
