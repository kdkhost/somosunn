@php
    $coverSource = $selectedHasCustomCover
        ? 'Capa personalizada'
        : ((int) ($selectedEvent->gallery_cover_media_id ?? 0) > 0 ? 'Foto da galeria' : 'Banner do evento ou fallback');
@endphp

<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-image mr-2"></i>Capa do album
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <img src="{{ $selectedCoverUrl }}" alt="Capa do album" class="img-fluid shadow-sm gallery-admin-cover-preview">

                <div class="mt-3">
                    <span class="badge badge-{{ $selectedHasCustomCover ? 'primary' : 'secondary' }}">
                        {{ $selectedHasCustomCover ? 'Capa personalizada ativa' : 'Usando capa automatica' }}
                    </span>
                </div>
            </div>

            <div class="col-lg-4 mb-4 mb-lg-0">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Evento</dt>
                    <dd class="col-sm-7">{{ $selectedEvent->title }}</dd>

                    <dt class="col-sm-5">Data</dt>
                    <dd class="col-sm-7">{{ $selectedEventDate ?: '--' }}</dd>

                    <dt class="col-sm-5">Fonte atual</dt>
                    <dd class="col-sm-7">{{ $coverSource }}</dd>

                    <dt class="col-sm-5">Itens no album</dt>
                    <dd class="col-sm-7">{{ (int) ($selectedEvent->media_count ?? 0) }}</dd>

                    <dt class="col-sm-5">Tamanho ideal</dt>
                    <dd class="col-sm-7">1600 x 900 px</dd>
                </dl>

                <div class="callout callout-info mt-3 mb-0">
                    <h5 class="mb-2">Recomendacao</h5>
                    <p class="mb-0">
                        Use imagens em proporcao 16:9, preferencialmente em JPG ou WEBP, para manter a capa nitida e leve.
                    </p>
                </div>
            </div>

            <div class="col-lg-4">
                <form action="{{ route('admin.gallery.cover.upload', $selectedEvent) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="gallery-cover-image">Subir capa personalizada</label>
                        <input id="gallery-cover-image"
                            type="file"
                            name="cover_image"
                            accept="image/jpeg,image/png,image/jpg,image/webp"
                            class="form-control-file"
                            required>
                        <small class="form-text text-muted">Tamanho ideal: 1600 x 900 px (16:9). Maximo de 10 MB.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-upload mr-1"></i>Salvar capa personalizada
                    </button>
                </form>

                <form action="{{ route('admin.gallery.cover.clear', $selectedEvent) }}" method="POST" class="mt-2">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-default btn-block" @disabled(!$selectedHasCustomCover)>
                        <i class="fas fa-undo-alt mr-1"></i>Remover capa personalizada
                    </button>
                </form>

                <p class="text-muted small mt-3 mb-0">
                    Para usar uma foto ja enviada como capa, clique em <strong>Definir capa</strong> no card da imagem abaixo.
                </p>
            </div>
        </div>
    </div>
</div>
