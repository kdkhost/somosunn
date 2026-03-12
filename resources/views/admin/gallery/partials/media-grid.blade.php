<div class="row" id="gallery-container">
    @forelse($media as $item)
        @php
            $imageUrl = \App\Support\UploadStorage::url($item->file_path, asset('img/logo.svg'));
            $ownerName = $item->user->name ?? 'Sistema';
            $isCoverFromMedia = blank(optional($item->event)->gallery_cover_image)
                && (int) optional($item->event)->gallery_cover_media_id === (int) $item->id;
        @endphp

        <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm gallery-card border-0 rounded-xl overflow-hidden">
                <div class="position-relative overflow-hidden bg-dark" style="aspect-ratio: 1 / 1;">
                    <img src="{{ $imageUrl }}"
                        class="card-img-top w-100 h-100 object-fit-cover cursor-pointer transition-all"
                        onclick="window.open('{{ $imageUrl }}', '_blank')"
                        alt="Galeria">

                    <div class="overlay-actions">
                        <a href="{{ $imageUrl }}" target="_blank" class="btn btn-light btn-sm shadow-lg rounded-circle mr-2" style="width: 35px; height: 35px;">
                            <i class="fas fa-up-right-from-square"></i>
                        </a>
                        <form action="{{ route('admin.gallery.destroy', $item) }}" method="POST"
                            class="delete-form mb-0"
                            data-confirm-title="Remover da galeria?"
                            data-confirm-text="Esta imagem sera excluida permanentemente.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm shadow-lg rounded-circle" style="width: 35px; height: 35px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>

                    <div class="position-absolute" style="bottom: 10px; left: 10px; right: 10px;">
                        <div class="d-flex flex-wrap" style="gap: 6px;">
                            @if($item->watermarked)
                                <span class="badge badge-primary opacity-75 shadow-sm">
                                    <i class="fas fa-certificate mr-1"></i> Watermark
                                </span>
                            @endif

                            @if($isCoverFromMedia)
                                <span class="badge badge-warning shadow-sm">
                                    <i class="fas fa-star mr-1"></i> Capa do album
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body p-3 bg-white d-flex flex-column">
                    <p class="text-xs text-muted mb-2 truncate font-weight-bold" title="{{ $item->event->title }}">
                        <i class="fas fa-calendar-alt mr-1 text-primary"></i> {{ \Illuminate\Support\Str::limit($item->event->title, 40) }}
                    </p>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle mr-2 d-flex align-items-center justify-content-center overflow-hidden" style="width: 24px; height: 24px;">
                                <i class="fas fa-user text-muted text-xs"></i>
                            </div>
                            <span class="text-xs text-dark font-weight-bold truncate" style="max-width: 100px;">
                                {{ $ownerName }}
                            </span>
                        </div>
                        <small class="text-muted text-xs">{{ $item->created_at->format('d/m/Y') }}</small>
                    </div>

                    <div class="mt-auto d-flex flex-wrap" style="gap: 8px;">
                        <form action="{{ route('admin.gallery.cover.media', $item) }}" method="POST" class="mb-0 flex-fill">
                            @csrf
                            <button type="submit" class="btn {{ $isCoverFromMedia ? 'btn-warning' : 'btn-outline-primary' }} btn-sm btn-block rounded-pill font-weight-bold">
                                <i class="fas fa-star mr-1"></i> {{ $isCoverFromMedia ? 'Capa ativa' : 'Definir capa' }}
                            </button>
                        </form>

                        <form action="{{ route('admin.gallery.destroy', $item) }}" method="POST"
                            class="delete-form mb-0"
                            data-confirm-title="Remover da galeria?"
                            data-confirm-text="Esta imagem sera excluida permanentemente.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill font-weight-bold">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="bg-white rounded-xl p-5 shadow-sm border border-light">
                <i class="fas fa-images fa-5x mb-4 text-light opacity-50"></i>
                <h4 class="text-muted font-weight-bold">Nenhuma midia encontrada na galeria</h4>
                <p class="text-secondary small">Tente ajustar os filtros ou subir novas fotos.</p>
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $media->appends(request()->query())->links() }}
</div>
