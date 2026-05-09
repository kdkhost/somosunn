@php
    $items = $media->items();
@endphp

@if(count($items) > 0)
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                    {{ $selectedEvent ? $selectedEvent->title : 'Todas as mídias' }}
                </h2>
                <span id="panel-gallery-total-pill" class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                    <i class="fas fa-layer-group text-[10px]"></i>
                    <span id="panel-gallery-total-value">{{ $media->total() }}</span>
                </span>
            </div>
            <div class="text-xs text-slate-400">
                Página {{ $media->currentPage() }} de {{ $media->lastPage() }}
            </div>
        </div>

        <div id="panel-gallery-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            @foreach($items as $item)
                @php
                    $assetUrl = \App\Support\UploadStorage::url($item->file_path, asset('img/default-user.svg'));
                    $eventTitle = optional($item->event)->title ?: 'Evento sem título';
                    $ownerName = optional($item->user)->name ?: 'Sistema';
                    $isEventOwner = (int) optional($item->event)->user_id === (int) auth()->id();
                    $canDelete = $isAdmin || (int) $item->user_id === (int) auth()->id() || $isEventOwner;
                    $isVideo = $item->type === 'video';
                    $canSetCover = !$isVideo && ($isAdmin || $isEventOwner);
                    $isCover = blank(optional($item->event)->gallery_cover_image)
                        && (int) optional($item->event)->gallery_cover_media_id === (int) $item->id;
                @endphp

                <article class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700/80 dark:bg-slate-900">
                    {{-- Thumbnail --}}
                    <button type="button"
                        data-lightbox-src="{{ $assetUrl }}"
                        data-lightbox-title="{{ $eventTitle }}"
                        data-lightbox-type="{{ $isVideo ? 'video' : 'image' }}"
                        class="relative block w-full aspect-square overflow-hidden bg-slate-100 dark:bg-slate-800">
                        @if($isVideo)
                            <video src="{{ $assetUrl }}" muted playsinline preload="none"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                loading="lazy"></video>
                            <div class="absolute inset-0 flex items-center justify-center bg-black/20">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-slate-900 shadow">
                                    <i class="fas fa-play text-xs ml-0.5"></i>
                                </span>
                            </div>
                        @else
                            <img src="{{ $assetUrl }}" alt="{{ $eventTitle }}"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                loading="lazy" decoding="async">
                        @endif

                        {{-- Overlay badges --}}
                        @if($isCover)
                            <span class="absolute top-1.5 left-1.5 rounded-md bg-amber-500 px-1.5 py-0.5 text-[9px] font-bold uppercase text-white shadow">
                                <i class="fas fa-star mr-0.5"></i> Capa
                            </span>
                        @endif

                        @if($isVideo)
                            <span class="absolute top-1.5 right-1.5 rounded-md bg-slate-900/70 px-1.5 py-0.5 text-[9px] font-bold uppercase text-white backdrop-blur-sm">
                                Vídeo
                            </span>
                        @endif
                    </button>

                    {{-- Info footer --}}
                    <div class="p-2">
                        <p class="truncate text-[11px] font-semibold text-slate-700 dark:text-slate-200" title="{{ $eventTitle }}">
                            {{ \Illuminate\Support\Str::limit($eventTitle, 22) }}
                        </p>
                        <p class="truncate text-[10px] text-slate-400 dark:text-slate-500">
                            {{ $ownerName }} • {{ $item->created_at?->format('d/m') }}
                        </p>
                    </div>

                    {{-- Hover actions --}}
                    @if($canDelete)
                        <div class="absolute top-1.5 right-1.5 flex flex-col gap-1 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                            @if($canSetCover && !$isCover)
                                <form method="POST" action="{{ route('panel.gallery.cover.media', $item) }}">
                                    @csrf
                                    <button type="submit" title="Definir como capa"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/90 text-blue-600 shadow-sm backdrop-blur transition hover:bg-blue-50 dark:bg-slate-800/90 dark:text-blue-400">
                                        <i class="fas fa-star text-[10px]"></i>
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('panel.gallery.destroy', $item) }}" class="gallery-delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Excluir"
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/90 text-rose-600 shadow-sm backdrop-blur transition hover:bg-rose-50 dark:bg-slate-800/90 dark:text-rose-400">
                                    <i class="fas fa-trash text-[10px]"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pt-3 gallery-panel-pagination">
            {{ $media->appends(request()->query())->links() }}
        </div>
    </section>
@else
    <section id="panel-gallery-empty-state" class="rounded-2xl border border-dashed border-slate-300 bg-white/90 p-8 dark:border-slate-700 dark:bg-slate-900">
        <div class="mx-auto max-w-md text-center">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-100 text-3xl text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                <i class="fas fa-camera"></i>
            </div>
            <h2 class="mt-5 text-xl font-bold text-slate-900 dark:text-white">
                {{ $selectedEvent ? 'Nenhuma mídia neste evento' : 'Galeria vazia' }}
            </h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                {{ $selectedEvent ? 'Troque o filtro ou envie novas mídias.' : 'Envie imagens e vídeos para começar.' }}
            </p>
            <div class="mt-5 flex flex-col items-center gap-2 sm:flex-row sm:justify-center">
                <button type="button" data-gallery-open-upload
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow transition hover:bg-blue-500">
                    <i class="fas fa-cloud-upload-alt"></i> Enviar mídias
                </button>
                @if($selectedEventId > 0)
                    <a href="{{ route('panel.gallery.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-3 text-sm font-medium text-slate-600 transition hover:border-slate-300 dark:border-slate-700 dark:text-slate-300">
                        <i class="fas fa-rotate-left"></i> Ver todos
                    </a>
                @endif
            </div>
        </div>
    </section>
@endif
