@php
    $items = $media->items();
@endphp

@if(count($items) > 0)
    <section class="space-y-5">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600 dark:text-blue-400">Colecao</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                    {{ $selectedEvent ? 'Cobertura filtrada do evento' : 'Painel de fotos publicadas' }}
                </h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    {{ $selectedEvent ? 'Cada card traz contexto do evento, autor e data do envio.' : 'Uma visao consolidada da galeria, pronta para moderacao e consulta rapida.' }}
                </p>
            </div>

            <div id="panel-gallery-total-pill" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                <i class="fas fa-layer-group text-blue-500"></i>
                <span id="panel-gallery-total-value">{{ $media->total() }}</span> registro(s)
            </div>
        </div>

        <div id="panel-gallery-grid" class="grid gap-6 lg:grid-cols-2 2xl:grid-cols-3">
            @foreach($items as $item)
                @php
                    $imageUrl = \App\Support\UploadStorage::url($item->file_path, asset('img/default-user.svg'));
                    $eventTitle = optional($item->event)->title ?: 'Evento sem titulo';
                    $ownerName = optional($item->user)->name ?: 'Sistema';
                    $ownerInitial = strtoupper(\Illuminate\Support\Str::substr($ownerName, 0, 1));
                    $avatarUrl = optional($item->user)->profile_photo_url ?? null;
                    $showAvatar = $avatarUrl && !str_contains((string) $avatarUrl, 'default-user.svg');
                    $canDelete = $isAdmin || (int) $item->user_id === (int) auth()->id();
                    $eventDate = optional(optional($item->event)->start_at)?->format('d/m/Y');
                @endphp

                <article class="group overflow-hidden rounded-[2rem] border border-slate-200/70 bg-white shadow-sm shadow-slate-200/60 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                    <div class="relative aspect-[4/3] overflow-hidden bg-slate-950">
                        <button type="button"
                            data-lightbox-src="{{ $imageUrl }}"
                            data-lightbox-title="{{ $eventTitle }}"
                            class="h-full w-full text-left">
                            <img src="{{ $imageUrl }}" alt="{{ $eventTitle }}"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-950/10 to-transparent opacity-90"></div>
                            <div class="absolute left-5 right-5 top-5 flex items-start justify-between gap-4">
                                <span class="rounded-full border border-white/10 bg-slate-950/55 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-white backdrop-blur">
                                    {{ \Illuminate\Support\Str::limit($eventTitle, 28) }}
                                </span>
                                @if($item->watermarked)
                                    <span class="rounded-full border border-cyan-400/25 bg-cyan-400/12 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.18em] text-cyan-100 backdrop-blur">
                                        Watermark
                                    </span>
                                @endif
                            </div>

                            <div class="absolute inset-x-5 bottom-5 flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-200">Abrir foto</p>
                                    <p class="mt-1 text-lg font-black text-white">{{ $ownerName }}</p>
                                </div>
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white backdrop-blur transition group-hover:bg-white/20">
                                    <i class="fas fa-up-right-and-down-left-from-center"></i>
                                </span>
                            </div>
                        </button>
                    </div>

                    <div class="p-5">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-cyan-400 text-white shadow-lg shadow-blue-500/20">
                                    @if($showAvatar)
                                        <img src="{{ $avatarUrl }}" alt="{{ $ownerName }}" class="h-full w-full object-cover"
                                            onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                    @else
                                        <span class="flex h-full w-full items-center justify-center text-sm font-black uppercase">{{ $ownerInitial }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-slate-900 dark:text-white">{{ $ownerName }}</p>
                                    <p class="mt-1 truncate text-xs font-medium text-slate-500 dark:text-slate-400">
                                        Enviado em {{ $item->created_at?->format('d/m/Y H:i') ?? '--' }}
                                    </p>
                                </div>
                            </div>

                            @if($canDelete)
                                <form method="POST" action="{{ route('panel.gallery.destroy', $item) }}" class="gallery-delete-form shrink-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:border-rose-300 hover:bg-rose-100 dark:border-rose-900/40 dark:bg-rose-900/20 dark:text-rose-300">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-[1.4rem] bg-slate-50 px-4 py-3 dark:bg-slate-950">
                                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Evento</p>
                                <p class="mt-1 line-clamp-2 font-bold text-slate-800 dark:text-slate-100">{{ $eventTitle }}</p>
                            </div>
                            <div class="rounded-[1.4rem] bg-slate-50 px-4 py-3 dark:bg-slate-950">
                                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Data base</p>
                                <p class="mt-1 font-bold text-slate-800 dark:text-slate-100">{{ $eventDate ?: '--/--/----' }}</p>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="pt-2">
            {{ $media->appends(request()->query())->links() }}
        </div>
    </section>
@else
    <section id="panel-gallery-empty-state" class="overflow-hidden rounded-[2.5rem] border border-dashed border-slate-300 bg-white/90 p-8 shadow-sm shadow-slate-200/50 dark:border-slate-700 dark:bg-slate-900">
        <div class="mx-auto max-w-3xl text-center">
            <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-[2rem] bg-slate-100 text-4xl text-slate-300 shadow-inner dark:bg-slate-800 dark:text-slate-600">
                <i class="fas fa-camera"></i>
            </div>

            <h2 class="mt-8 text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                {{ $selectedEvent ? 'Nenhuma foto encontrada para este evento' : 'Sua galeria ainda nao tem registros para exibir' }}
            </h2>
            <p class="mx-auto mt-4 max-w-2xl text-base leading-8 text-slate-500 dark:text-slate-400">
                {{ $selectedEvent ? 'Troque o filtro ou publique novas fotos para construir a narrativa visual desse evento.' : 'Assim que voce subir imagens, esta area passa a mostrar os cards com preview, autor, contexto do evento e acoes rapidas.' }}
            </p>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <button type="button"
                    data-gallery-open-upload
                    class="inline-flex items-center justify-center gap-3 rounded-[1.6rem] bg-blue-600 px-7 py-4 text-sm font-black uppercase tracking-[0.16em] text-white shadow-[0_18px_40px_rgba(37,99,235,0.28)] transition hover:-translate-y-0.5 hover:bg-blue-500">
                    <i class="fas fa-cloud-upload-alt"></i>
                    Subir minhas fotos
                </button>

                @if($selectedEventId > 0)
                    <a href="{{ route('panel.gallery.index') }}"
                        class="inline-flex items-center justify-center gap-3 rounded-[1.6rem] border border-slate-200 bg-white px-7 py-4 text-sm font-bold text-slate-600 transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-white">
                        <i class="fas fa-rotate-left"></i>
                        Ver todos os eventos
                    </a>
                @endif
            </div>
        </div>
    </section>
@endif
