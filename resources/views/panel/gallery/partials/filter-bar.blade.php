<section class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <form method="GET" action="{{ route('panel.gallery.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="flex-1">
            <div class="relative">
                <select id="gallery-event-filter" name="event_id"
                    class="w-full appearance-none rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 pr-10 text-sm font-medium text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-blue-500 dark:focus:ring-blue-900/30">
                    <option value="">Todos os eventos</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected($selectedEventId === (int) $event->id)>
                            {{ $event->title }}@if($event->media_count) ({{ $event->media_count }})@endif @if($event->start_at) • {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }}@endif
                        </option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                    <i class="fas fa-chevron-down text-xs"></i>
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                <i class="fas fa-filter text-xs"></i> Filtrar
            </button>

            @if($selectedEventId > 0)
                <a href="{{ route('panel.gallery.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:text-white">
                    <i class="fas fa-xmark text-xs"></i> Limpar
                </a>
            @endif
        </div>
    </form>
</section>
