@if($selectedEvent)
    <section class="overflow-hidden rounded-[2.25rem] border border-slate-200/70 bg-white/95 p-6 shadow-sm shadow-slate-200/60 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 dark:shadow-none">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600 dark:text-blue-400">Capa do album</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white">{{ $selectedEvent->title }}</h2>
                <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                    A capa aparece na listagem publica da galeria e no hero do evento. Voce pode enviar uma capa exclusiva ou usar qualquer imagem ja publicada logo abaixo.
                </p>
            </div>

            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                <i class="fas {{ !blank($selectedEvent->gallery_cover_image) ? 'fa-wand-magic-sparkles text-blue-500' : 'fa-image text-cyan-500' }}"></i>
                {{ !blank($selectedEvent->gallery_cover_image) ? 'Capa personalizada ativa' : 'Capa baseada na galeria ou banner' }}
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-950 shadow-[0_20px_60px_rgba(15,23,42,0.18)] dark:border-slate-800">
                <div class="relative h-full min-h-[20rem]">
                    <img src="{{ $selectedEvent->gallery_cover_url ?: asset('img/logo.svg') }}" alt="Capa do album"
                        class="h-full w-full object-cover">
                    <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,23,42,0.12),rgba(15,23,42,0.78))]"></div>
                    <div class="absolute inset-x-6 bottom-6">
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-cyan-200">Preview da capa</p>
                        <p class="mt-2 text-2xl font-black text-white">Album visual de {{ $selectedEvent->title }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4">
                @if($canManageSelectedEvent)
                    <form method="POST"
                        action="{{ route('panel.gallery.cover.upload', $selectedEvent) }}"
                        enctype="multipart/form-data"
                        class="rounded-[1.8rem] border border-slate-200 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-950">
                        @csrf
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">Upload de capa personalizada</p>
                        <p class="mt-3 text-sm leading-7 text-slate-500 dark:text-slate-400">
                            Use uma imagem exclusiva para destacar esse album na galeria publica.
                        </p>
                        <input type="file" name="cover_image" accept="image/jpeg,image/png,image/jpg,image/webp"
                            required
                            class="mt-4 block w-full rounded-[1.2rem] border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <button type="submit"
                            class="mt-4 inline-flex items-center gap-3 rounded-[1.3rem] bg-blue-600 px-5 py-3 text-sm font-black uppercase tracking-[0.16em] text-white shadow-[0_16px_35px_rgba(37,99,235,0.28)] transition hover:-translate-y-0.5 hover:bg-blue-500">
                            <i class="fas fa-upload"></i>
                            Salvar capa personalizada
                        </button>
                    </form>

                    <form method="POST"
                        action="{{ route('panel.gallery.cover.clear', $selectedEvent) }}"
                        class="rounded-[1.8rem] border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                        @csrf
                        @method('DELETE')
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Acoes rapidas</p>
                        <p class="mt-3 text-sm leading-7 text-slate-500 dark:text-slate-400">
                            Para usar uma imagem ja enviada, clique em <strong>Definir capa</strong> no card correspondente logo abaixo.
                        </p>
                        <button type="submit"
                            class="mt-4 inline-flex items-center gap-3 rounded-[1.3rem] border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:text-white">
                            <i class="fas fa-rotate-left"></i>
                            Limpar capa personalizada
                        </button>
                    </form>
                @else
                    <div class="rounded-[1.8rem] border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/40 dark:bg-amber-900/20">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-700 dark:text-amber-300">Somente leitura</p>
                        <p class="mt-3 text-sm leading-7 text-amber-900 dark:text-amber-100">
                            Apenas o organizador desse evento ou um administrador pode alterar a capa do album.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
