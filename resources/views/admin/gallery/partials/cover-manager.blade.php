<div class="gallery-admin-surface mb-4">
    <div class="p-4 p-lg-5">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between mb-4" style="gap: 1rem;">
            <div class="pr-lg-4">
                <p class="gallery-admin-section-eyebrow">Capa do album</p>
                <h2 class="gallery-admin-section-title mt-2">{{ $selectedEvent->title }}</h2>
                <p class="gallery-admin-subtext mb-0 mt-3">
                    @if($selectedEventDate)
                        Evento em {{ $selectedEventDate }}.
                    @endif
                    Escolha uma capa personalizada ou use qualquer foto publicada no grid abaixo como capa oficial da galeria.
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center" style="gap: .75rem;">
                <span class="badge badge-pill px-3 py-2 border text-uppercase font-weight-bold {{ $selectedHasCustomCover ? 'badge-primary' : 'badge-light text-muted' }}" style="letter-spacing:.12em;">
                    <i class="fas {{ $selectedHasCustomCover ? 'fa-magic' : 'fa-image' }} mr-2"></i>
                    {{ $selectedHasCustomCover ? 'Capa personalizada ativa' : 'Capa por foto ou banner' }}
                </span>
                <span class="badge badge-pill badge-light px-3 py-2 border text-uppercase font-weight-bold" style="letter-spacing:.12em;">
                    <i class="fas fa-images text-primary mr-2"></i>{{ $selectedEvent->media_count ?? 0 }} item(ns)
                </span>
            </div>
        </div>

        <div class="row align-items-stretch" style="row-gap: 1.5rem;">
            <div class="col-xl-5">
                <div class="gallery-cover-preview rounded-xl overflow-hidden border-0 shadow-sm">
                    <img src="{{ $selectedCoverUrl }}" alt="Capa do album" class="w-100 h-100 object-fit-cover">
                    <div class="gallery-cover-overlay">
                        <div>
                            <span class="badge badge-pill badge-dark px-3 py-2 text-uppercase font-weight-bold" style="letter-spacing:.12em;">
                                <i class="fas fa-star mr-2 text-warning"></i>Capa atual do album
                            </span>
                            <p class="mb-0 mt-3 text-white font-weight-bold" style="font-size: 1.2rem;">
                                {{ $selectedEvent->title }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="row h-100" style="row-gap: 1rem;">
                    <div class="col-lg-7">
                        <form action="{{ route('admin.gallery.cover.upload', $selectedEvent) }}" method="POST" enctype="multipart/form-data" class="h-100">
                            @csrf
                            <div class="gallery-admin-surface h-100">
                                <div class="p-4 h-100 d-flex flex-column">
                                    <p class="small font-weight-bold text-uppercase text-primary mb-2" style="letter-spacing:.14em;">Upload de capa personalizada</p>
                                    <p class="text-muted mb-4">
                                        Envie uma imagem exclusiva para destacar esse album na listagem publica e no topo da galeria.
                                    </p>
                                    <input type="file" name="cover_image" accept="image/jpeg,image/png,image/jpg,image/webp" class="form-control-file mb-4" required>
                                    <button type="submit" class="gallery-admin-primary-btn border-0 mt-auto">
                                        <i class="fas fa-upload"></i>
                                        Salvar capa personalizada
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-5">
                        <div class="gallery-admin-surface h-100">
                            <div class="p-4 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <p class="small font-weight-bold text-uppercase text-muted mb-2" style="letter-spacing:.14em;">Acoes rapidas</p>
                                    <p class="text-muted mb-0">
                                        Para usar uma foto ja enviada, clique em <strong>Definir capa</strong> no card correspondente abaixo.
                                    </p>
                                </div>

                                <form action="{{ route('admin.gallery.cover.clear', $selectedEvent) }}" method="POST" class="mt-4">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="gallery-admin-secondary-btn border-0 w-100">
                                        <i class="fas fa-undo-alt"></i>
                                        Limpar capa personalizada
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
