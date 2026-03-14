@php
    $galleryUploadPerFileLimitBytes = $galleryUploadPerFileLimitBytes ?? (\App\Support\UploadStorage::effectiveUploadLimitBytes(20 * 1024 * 1024) ?? (20 * 1024 * 1024));
    $galleryUploadPerFileLimitMb = number_format($galleryUploadPerFileLimitBytes / 1024 / 1024, 2, '.', '');
@endphp

<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title" id="uploadModalLabel">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>Adicionar midias
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form action="{{ route('admin.gallery.upload') }}" method="POST" enctype="multipart/form-data" id="adminUploadForm" novalidate>
                    @csrf

                    <div class="form-group">
                        <label for="admin-upload-event">1. Selecione o evento</label>
                        <select id="admin-upload-event" name="event_id" required class="form-control select2-modal" style="width: 100%;">
                            <option value="">Selecione o evento...</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @selected($selectedEventId === (int) $event->id)>{{ $event->title }}@if($event->start_at) - {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }}@endif</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>2. Selecione e envie arquivos</label>
                        <small class="float-right text-muted">Arraste varias imagens e videos com preview, progresso e tempo restante por arquivo.</small>

                        <div class="gallery-admin-dropzone" id="adminDropzone" role="button" tabindex="0" aria-label="Zona de upload">
                            <input type="file" name="files[]" multiple accept="image/*,image/heic,image/heif,video/*" id="adminFileInput" class="sr-only">

                            <div id="adminDropzoneEmpty" class="gallery-admin-dropzone-empty text-center p-5">
                                <div class="mb-3 text-primary">
                                    <i class="fas fa-cloud-upload-alt fa-3x"></i>
                                </div>
                                <h5 class="font-weight-bold mb-2">Arraste fotos e videos aqui</h5>
                                <p class="text-muted mb-3">ou <a href="#" id="adminFilePicker" class="text-primary">clique para selecionar</a></p>
                                <div>
                                    <span class="badge badge-primary">Imagens</span>
                                    <span class="badge badge-info">Videos</span>
                                    <span class="badge badge-secondary">JPG PNG WEBP HEIC</span>
                                    <span class="badge badge-secondary">Ate {{ $galleryUploadPerFileLimitMb }} MB por arquivo</span>
                                </div>
                            </div>

                            <div id="adminDropzonePreview" class="d-none p-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted text-uppercase small font-weight-bold">Preview do lote</span>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="adminAddMoreFiles">
                                        <i class="fas fa-plus mr-1"></i>Adicionar mais
                                    </button>
                                </div>
                                <div class="gallery-admin-inline-preview-grid" id="adminInlinePreviewGrid"></div>
                            </div>
                        </div>
                    </div>

                    <div id="adminSelectedFiles" class="card card-outline card-secondary d-none">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-2" style="gap:.5rem;">
                                <div>
                                    <p class="text-muted text-uppercase small font-weight-bold mb-1">Arquivos selecionados</p>
                                    <p id="adminSelectedSummary" class="font-weight-bold mb-0"></p>
                                </div>
                                <span id="adminSelectedSize" class="badge badge-primary"></span>
                            </div>
                            <div id="adminSelectedList" class="gallery-admin-selected-list"></div>
                        </div>
                    </div>

                    <div class="card card-outline card-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted text-uppercase small font-weight-bold">Progresso do envio</span>
                                <span id="adminProgressValue" class="font-weight-bold text-primary">0%</span>
                            </div>
                            <div class="progress progress-sm">
                                <div id="adminProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%"></div>
                            </div>
                            <p id="adminProgressLabel" class="text-muted small mb-0 mt-2">Aguardando selecao dos arquivos.</p>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer justify-content-between">
                <small class="text-muted">
                    <i class="fas fa-magic mr-1"></i>Marca d'agua automatica aplicada nas imagens quando disponivel.
                </small>
                <div>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                    <button type="submit" form="adminUploadForm" id="adminSubmitBtn" class="btn btn-primary" disabled>
                        <i class="fas fa-upload mr-1"></i>Publicar na galeria
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
