<div class="gallery-admin-surface">
    <div class="p-4 p-lg-5">
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-4" style="gap: 1rem;">
            <div class="pr-md-4">
                <p class="gallery-admin-section-eyebrow">Colecao</p>
                <h2 class="gallery-admin-section-title mt-2">
                    {{ $selectedEvent ? 'Cobertura filtrada do evento' : 'Painel de fotos publicadas' }}
                </h2>
                <p class="gallery-admin-subtext mb-0 mt-3">
                    {{ $selectedEvent
                        ? 'Cada card entrega contexto do evento, autor, data de envio e acoes rapidas para manter o album organizado.'
                        : 'Uma visao consolidada da galeria, pronta para moderacao, definicao de capa e leitura rapida dos registros publicados.' }}
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center" style="gap: .75rem;">
                <span class="badge badge-pill badge-light px-3 py-2 border text-uppercase font-weight-bold" style="letter-spacing:.12em;">
                    <i class="fas fa-layer-group text-primary mr-2"></i>{{ $media->total() }} registro(s)
                </span>
            </div>
        </div>

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
                    $eventDate = optional(optional($item->event)->start_at)?->format('d/m/Y');
                    $uploadedDate = $item->created_at?->format('d/m/Y H:i') ?: '--';
                @endphp

                <div class="col-sm-6 col-xl-4 mb-4">
                    <article class="gallery-admin-media-card h-100">
                        <div class="gallery-admin-media-card__preview">
                            @if($item->type === 'video')
                                <a href="{{ $assetUrl }}" target="_blank" rel="noopener" class="gallery-admin-media-card__preview-link">
                                    <div class="gallery-admin-video-preview">
                                        <span class="gallery-admin-video-preview__icon">
                                            <i class="fas fa-play"></i>
                                        </span>
                                        <span class="gallery-admin-video-preview__label">Video</span>
                                    </div>
                                </a>
                            @else
                                <a href="{{ $assetUrl }}" target="_blank" rel="noopener" class="gallery-admin-media-card__preview-link">
                                    <img src="{{ $assetUrl }}" alt="{{ $eventTitle }}" class="gallery-admin-media-card__image">
                                </a>
                            @endif

                            <div class="gallery-admin-media-card__overlay"></div>

                            <div class="gallery-admin-media-card__badges">
                                @if($item->watermarked)
                                    <span class="badge badge-pill badge-primary px-3 py-2 text-uppercase font-weight-bold" style="letter-spacing:.12em;">
                                        <i class="fas fa-certificate mr-2"></i>Watermark
                                    </span>
                                @endif

                                @if($isCoverFromMedia)
                                    <span class="badge badge-pill badge-warning px-3 py-2 text-uppercase font-weight-bold" style="letter-spacing:.12em;">
                                        <i class="fas fa-star mr-2"></i>Capa do album
                                    </span>
                                @endif

                                @if($item->type === 'video')
                                    <span class="badge badge-pill badge-dark px-3 py-2 text-uppercase font-weight-bold" style="letter-spacing:.12em;">
                                        <i class="fas fa-film mr-2"></i>Video
                                    </span>
                                @endif
                            </div>

                            <div class="gallery-admin-media-card__preview-cta">
                                <span class="gallery-admin-preview-chip">
                                    <i class="fas fa-external-link-alt mr-2"></i>Abrir {{ $item->type === 'video' ? 'arquivo' : 'imagem' }}
                                </span>
                            </div>
                        </div>

                        <div class="gallery-admin-media-card__body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between" style="gap: .9rem;">
                                <div class="media align-items-center min-w-0">
                                    <div class="gallery-admin-avatar mr-3">
                                        @if($hasAvatar)
                                            <img src="{{ $avatarUrl }}" alt="{{ $ownerName }}"
                                                onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">
                                        @else
                                            <span>{{ $ownerInitial }}</span>
                                        @endif
                                    </div>
                                    <div class="media-body min-w-0">
                                        <p class="mb-1 font-weight-bold text-dark text-truncate">{{ $ownerName }}</p>
                                        <p class="mb-0 text-muted small">Enviado em {{ $uploadedDate }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="gallery-admin-media-card__meta-grid mt-4">
                                <div class="gallery-admin-meta-box">
                                    <p class="gallery-admin-meta-box__label">Evento</p>
                                    <p class="gallery-admin-meta-box__value">{{ \Illuminate\Support\Str::limit($eventTitle, 48) }}</p>
                                </div>
                                <div class="gallery-admin-meta-box">
                                    <p class="gallery-admin-meta-box__label">Data base</p>
                                    <p class="gallery-admin-meta-box__value">{{ $eventDate ?: '--/--/----' }}</p>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap align-items-center mt-4" style="gap: .75rem;">
                                @if($item->type === 'image')
                                    <form action="{{ route('admin.gallery.cover.media', $item) }}" method="POST" class="mb-0 flex-fill">
                                        @csrf
                                        <button type="submit" class="gallery-admin-primary-btn border-0 w-100 {{ $isCoverFromMedia ? 'gallery-admin-primary-btn--cover' : '' }}">
                                            <i class="fas fa-star"></i>
                                            {{ $isCoverFromMedia ? 'Capa ativa' : 'Definir capa' }}
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.gallery.destroy', $item) }}" method="POST"
                                    class="delete-form mb-0 {{ $item->type === 'image' ? '' : 'flex-fill' }}"
                                    data-confirm-title="Remover da galeria?"
                                    data-confirm-text="Esta midia sera excluida permanentemente.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="gallery-admin-delete-btn {{ $item->type === 'image' ? '' : 'w-100 justify-content-center' }}">
                                        <i class="fas fa-trash"></i>
                                        <span class="ml-2">{{ $item->type === 'image' ? 'Excluir' : 'Remover arquivo' }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="gallery-admin-empty-state">
                        <span class="gallery-admin-empty-state__icon">
                            <i class="fas fa-camera-retro"></i>
                        </span>
                        <h3 class="mt-4 font-weight-bold text-dark">Nenhuma midia encontrada na galeria</h3>
                        <p class="text-muted mb-0">Ajuste os filtros ou publique novas fotos para construir a cobertura visual do evento.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="gallery-admin-pagination d-flex justify-content-center mt-3">
            {{ $media->appends(request()->query())->links() }}
        </div>
    </div>
</div>
