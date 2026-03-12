<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-xl overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 1rem;">
                    <div>
                        <p class="small font-weight-bold text-uppercase text-primary mb-2">Capa do album</p>
                        <h4 class="font-weight-bold text-dark mb-2">{{ $selectedEvent->title }}</h4>
                        <p class="text-muted mb-0">
                            @if($selectedEventDate)
                                Evento em {{ $selectedEventDate }}.
                            @endif
                            Escolha uma capa personalizada ou use qualquer foto do grid abaixo como capa oficial.
                        </p>
                    </div>

                    <div class="d-flex flex-wrap align-items-center" style="gap: .5rem;">
                        <span class="badge badge-pill {{ $selectedHasCustomCover ? 'badge-primary' : 'badge-light' }} px-3 py-2">
                            <i class="fas {{ $selectedHasCustomCover ? 'fa-wand-magic-sparkles' : 'fa-image' }} mr-1"></i>
                            {{ $selectedHasCustomCover ? 'Capa personalizada ativa' : 'Capa usando foto da galeria ou banner' }}
                        </span>
                        <span class="badge badge-pill badge-light px-3 py-2">
                            <i class="fas fa-images mr-1 text-primary"></i> {{ $selectedEvent->media_count ?? 0 }} item(ns)
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row">
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <div class="gallery-cover-preview rounded-xl overflow-hidden border">
                            <img src="{{ $selectedCoverUrl }}" alt="Capa do album" class="w-100 h-100 object-fit-cover">
                            <div class="gallery-cover-overlay">
                                <span class="badge badge-pill badge-dark px-3 py-2">
                                    <i class="fas fa-star mr-1"></i> Capa atual do album
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <form action="{{ route('admin.gallery.cover.upload', $selectedEvent) }}" method="POST" enctype="multipart/form-data" class="h-100">
                                    @csrf
                                    <div class="card border h-100 mb-0">
                                        <div class="card-body">
                                            <label class="small font-weight-bold text-muted text-uppercase mb-2 d-block">Enviar capa personalizada</label>
                                            <p class="text-muted small mb-3">
                                                Envie uma imagem exclusiva para representar esse album na listagem publica e no topo da galeria.
                                            </p>
                                            <input type="file" name="cover_image" accept="image/jpeg,image/png,image/jpg,image/webp" class="form-control-file mb-3" required>
                                            <button type="submit" class="btn btn-primary rounded-pill font-weight-bold">
                                                <i class="fas fa-upload mr-2"></i> Salvar capa personalizada
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="card border h-100 mb-0">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div>
                                            <label class="small font-weight-bold text-muted text-uppercase mb-2 d-block">Acoes rapidas</label>
                                            <p class="text-muted small mb-3">
                                                Para usar uma foto ja enviada, clique em <strong>Definir capa</strong> no card correspondente abaixo.
                                            </p>
                                        </div>

                                        <form action="{{ route('admin.gallery.cover.clear', $selectedEvent) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-secondary rounded-pill font-weight-bold btn-block">
                                                <i class="fas fa-rotate-left mr-2"></i> Limpar capa personalizada
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
    </div>
</div>
