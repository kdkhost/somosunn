@if($selectedEvent && $canManageSelectedEvent)
    <section class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-4 md:flex-row md:items-center">
            {{-- Preview --}}
            <div class="shrink-0 overflow-hidden rounded-lg w-full md:w-40 h-24 bg-slate-100 dark:bg-slate-800">
                <img src="{{ $selectedEvent->gallery_cover_url ?: asset('img/logo.svg') }}" alt="Capa"
                    class="h-full w-full object-cover" loading="lazy">
            </div>

            {{-- Info + Actions --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Capa do álbum</span>
                    <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        {{ !blank($selectedEvent->gallery_cover_image) ? 'Personalizada' : 'Automática' }}
                    </span>
                </div>
                <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $selectedEvent->title }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Envie uma capa exclusiva ou use "Definir capa" em qualquer foto abaixo.
                </p>
            </div>

            {{-- Upload form --}}
            <div class="flex items-center gap-2 shrink-0">
                <form method="POST" action="{{ route('panel.gallery.cover.upload', $selectedEvent) }}" enctype="multipart/form-data"
                    class="flex items-center gap-2">
                    @csrf
                    <input type="file" name="cover_image" accept="image/jpeg,image/png,image/jpg,image/webp" required
                        class="block w-40 text-xs file:mr-2 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-300">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-blue-500">
                        <i class="fas fa-upload text-[10px]"></i> Salvar
                    </button>
                </form>

                @if(!blank($selectedEvent->gallery_cover_image))
                    <form method="POST" action="{{ route('panel.gallery.cover.clear', $selectedEvent) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Remover capa personalizada"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-400 transition hover:border-rose-300 hover:text-rose-600 dark:border-slate-700 dark:hover:border-rose-800 dark:hover:text-rose-400">
                            <i class="fas fa-xmark text-xs"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endif
