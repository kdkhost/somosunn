<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-photo-video mr-2"></i>{{ $selectedEvent ? 'Mídias do evento filtrado' : 'Todas as mídias' }}
        </h3>
        <div class="card-tools">
            <span class="badge badge-primary">
                <span id="adminGalleryResultCount">{{ $media->total() }}</span> registro(s)
            </span>
        </div>
    </div>

    <div class="card-body p-3">
        @if($media->count() > 0)
            <div class="row" id="gallery-container">
                @foreach($media as $item)
                    @php
                        $assetUrl = \App\Support\UploadStorage::url($item->file_path, asset('img/logo.svg'));
                        $eventTitle = optional($item->event)->title ?: 'Evento sem título';
                        $ownerName = optional($item->user)->name ?: 'Sistema';
                        $isCoverFromMedia = blank(optional($item->event)->gallery_cover_image)
                            && (int) optional($item->event)->gallery_cover_media_id === (int) $item->id;
                        $isVideo = $item->type === 'video';
                    @endphp

                    <div class="col-6 col-sm-4 col-md-3 col-xl-2 mb-3">
                        <div class="card h-100 shadow-sm border position-relative">
                            {{-- Thumbnail --}}
                            <div class="position-relative" style="padding-top:100%; overflow:hidden; background:#f1f5f9;">
                                @if($isVideo)
                                    <a href="{{ $assetUrl }}" target="_blank" rel="noopener"
                                        class="position-absolute d-flex align-items-center justify-content-center"
                                        style="inset:0; background:#0f172a; text-decoration:none;">
                                        <video src="{{ $assetUrl }}" muted playsinline preload="none"
                                            class="position-absolute" style="inset:0; width:100%; height:100%; object-fit:cover; opacity:.5;"
                                            loading="lazy"></video>
                                        <i class="fas fa-play-circle text-white fa-2x position-relative" style="z-index:1;"></i>
                                    </a>
                                @else
                                    <a href="{{ $assetUrl }}" target="_blank" rel="noopener"
                                        class="position-absolute" style="inset:0;">
                                        <img src="{{ $assetUrl }}" alt="{{ $eventTitle }}"
                                            class="w-100 h-100" style="object-fit:cover;"
                                            loading="lazy" decoding="async">
                                    </a>
                                @endif

                                {{-- Cover badge --}}
                                @if($isCoverFromMedia)
                                    <span class="badge badge-warning position-absolute" style="top:4px; left:4px; font-size:10px;">
                                        <i class="fas fa-star"></i> Capa
                                    </span>
                                @endif

                                {{-- Type badge --}}
                                @if($isVideo)
                                    <span class="badge badge-dark position-absolute" style="top:4px; right:4px; font-size:9px;">
                                        Vídeo
                                    </span>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="card-body p-2">
                                <p class="mb-0 text-truncate font-weight-bold" style="font-size:11px;" title="{{ $eventTitle }}">
                                    {{ \Illuminate\Support\Str::limit($eventTitle, 20) }}
                                </p>
                                <p class="mb-0 text-muted text-truncate" style="font-size:10px;">
                                    {{ $ownerName }} • {{ $item->created_at?->format('d/m') }}
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="card-footer p-1 d-flex justify-content-between border-top">
                                @if($item->type === 'image')
                                    <form action="{{ route('admin.gallery.cover.media', $item) }}" method="POST" class="mb-0">
                                        @csrf
                                        <button type="submit" class="btn btn-xs {{ $isCoverFromMedia ? 'btn-warning' : 'btn-outline-secondary' }}" title="Definir como capa">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                @else
                                    <span></span>
                                @endif

                                <form action="{{ route('admin.gallery.destroy', $item) }}" method="POST"
                                    class="delete-form mb-0"
                                    data-confirm-title="Remover?"
                                    data-confirm-text="Esta mídia será excluída permanentemente.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="callout callout-info mb-0" id="adminGalleryEmptyState">
                <h5><i class="fas fa-camera mr-2"></i>Nenhuma mídia encontrada</h5>
                <p class="mb-0">Ajuste o filtro ou envie novas imagens para popular a galeria.</p>
            </div>
        @endif
    </div>

    @if($media->hasPages())
        <div class="card-footer clearfix">
            <div class="d-flex justify-content-center">
                {{ $media->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>
