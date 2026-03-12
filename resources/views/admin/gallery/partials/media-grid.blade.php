<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-photo-video mr-2"></i>{{ $selectedEvent ? 'Midias do evento filtrado' : 'Todas as midias da galeria' }}
        </h3>

        <div class="card-tools">
            <span class="badge badge-primary">{{ number_format($media->total(), 0, ',', '.') }} registro(s)</span>
        </div>
    </div>

    <div class="card-body">
        <div class="row" id="gallery-container">
            @forelse($media as $item)
                @php
                    $assetUrl = \App\Support\UploadStorage::url($item->file_path, asset('img/logo.svg'));
                    $eventTitle = optional($item->event)->title ?: 'Evento sem titulo';
                    $ownerName = optional($item->user)->name ?: 'Sistema';
                    $ownerInitial = strtoupper(\Illuminate\Support\Str::substr($ownerName, 0, 1));
                    $avatarUrl = optional($item->user)->profile_photo_url ?? null;
                    $hasAvatar = $avatarUrl && !str_contains((string) $avatarUrl, 'default-user.svg');
                    $isCoverFromMedia = blank(optional($item->event)->gallery_cover_image)
                        && (int) optional($item->event)->gallery_cover_media_id === (int) $item->id;
                    $eventDate = optional($item->event)->start_at
                        ? \Carbon\Carbon::parse($item->event->start_at)->format('d/m/Y')
                        : null;
                    $uploadedDate = $item->created_at?->format('d/m/Y H:i') ?: '--';
                @endphp

                <div class="col-sm-6 col-xl-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="gallery-admin-thumb">
                            @if($item->type === 'video')
                                <a href="{{ $assetUrl }}" target="_blank" rel="noopener" class="gallery-admin-video-link">
                                    <i class="fas fa-play-circle fa-3x"></i>
                                    <span class="font-weight-bold">Abrir video</span>
                                </a>
                            @else
                                <a href="{{ $assetUrl }}" target="_blank" rel="noopener">
                                    <img src="{{ $assetUrl }}" alt="{{ $eventTitle }}">
                                </a>
                            @endif
                        </div>

                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-3">
                                <div class="gallery-admin-avatar mr-2">
                                    @if($hasAvatar)
                                        <img src="{{ $avatarUrl }}"
                                            alt="{{ $ownerName }}"
                                            onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                    @else
                                        <span>{{ $ownerInitial }}</span>
                                    @endif
                                </div>

                                <div class="flex-grow-1">
                                    <div class="font-weight-bold text-truncate">{{ $ownerName }}</div>
                                    <small class="text-muted">Enviado em {{ $uploadedDate }}</small>
                                </div>
                            </div>

                            <h3 class="h6 font-weight-bold mb-2">{{ \Illuminate\Support\Str::limit($eventTitle, 48) }}</h3>

                            <ul class="list-unstyled text-muted small mb-3">
                                <li class="mb-1">
                                    <i class="far fa-calendar-alt mr-1"></i>{{ $eventDate ?: '--/--/----' }}
                                </li>
                                <li class="mb-1">
                                    <i class="fas {{ $item->type === 'video' ? 'fa-film' : 'fa-image' }} mr-1"></i>{{ $item->type === 'video' ? 'Video' : 'Imagem' }}
                                </li>
                                <li>
                                    <i class="fas fa-certificate mr-1"></i>{{ $item->watermarked ? 'Com marca d\'agua' : 'Sem marca d\'agua' }}
                                </li>
                            </ul>

                            <div class="mb-3">
                                @if($isCoverFromMedia)
                                    <span class="badge badge-warning mr-1">
                                        <i class="fas fa-star mr-1"></i>Capa do album
                                    </span>
                                @endif

                                @if($item->watermarked)
                                    <span class="badge badge-primary">Watermark</span>
                                @endif
                            </div>

                            <div class="mt-auto">
                                <div class="row">
                                    @if($item->type === 'image')
                                        <div class="col-8 pr-1">
                                            <form action="{{ route('admin.gallery.cover.media', $item) }}" method="POST" class="mb-0">
                                                @csrf
                                                <button type="submit" class="btn {{ $isCoverFromMedia ? 'btn-warning' : 'btn-outline-warning' }} btn-sm btn-block">
                                                    <i class="fas fa-star mr-1"></i>{{ $isCoverFromMedia ? 'Capa ativa' : 'Definir capa' }}
                                                </button>
                                            </form>
                                        </div>

                                        <div class="col-4 pl-1">
                                            <form action="{{ route('admin.gallery.destroy', $item) }}"
                                                method="POST"
                                                class="delete-form mb-0"
                                                data-confirm-title="Remover da galeria?"
                                                data-confirm-text="Esta midia sera excluida permanentemente.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm btn-block">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="col-12">
                                            <form action="{{ route('admin.gallery.destroy', $item) }}"
                                                method="POST"
                                                class="delete-form mb-0"
                                                data-confirm-title="Remover da galeria?"
                                                data-confirm-text="Esta midia sera excluida permanentemente.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm btn-block">
                                                    <i class="fas fa-trash mr-1"></i>Remover video
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="callout callout-info mb-0">
                        <h5><i class="fas fa-camera mr-2"></i>Nenhuma midia encontrada</h5>
                        <p class="mb-0">Ajuste o filtro ou envie novas imagens para popular a galeria do evento.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    @if($media->hasPages())
        <div class="card-footer clearfix gallery-admin-pagination">
            {{ $media->appends(request()->query())->links() }}
        </div>
    @endif
</div>
