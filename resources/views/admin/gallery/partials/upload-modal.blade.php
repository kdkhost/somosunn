<div class="modal fade gallery-admin-modal" id="uploadModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 rounded-xl overflow-hidden shadow-2xl">
            <div class="modal-header gallery-admin-modal__header border-0 pt-4 px-4">
                <div class="d-flex flex-column">
                    <h4 class="modal-title font-weight-bold text-dark">Novas Fotos</h4>
                    <p class="text-muted small mb-0">Selecione o evento e carregue as imagens da cobertura com a mesma linguagem visual da galeria premium.</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 2rem; padding: 1.5rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4">
                <form action="{{ route('admin.gallery.upload') }}" method="POST" enctype="multipart/form-data" id="adminUploadForm">
                    @csrf

                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-muted text-uppercase mb-2">Evento associado</label>
                        <select name="event_id" required class="form-control select2-modal" style="width: 100%;">
                            <option value="">Selecione o evento...</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @selected($selectedEventId === (int) $event->id)>{{ $event->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="premium-upload-box mb-4" id="adminDropzone">
                        <div class="drop-zone-area">
                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                            <h5 class="font-weight-bold text-dark mb-2">Clique ou arraste as fotos aqui</h5>
                            <p class="text-muted small mb-0">JPG, PNG e WEBP com ate 10 MB por arquivo.</p>
                        </div>
                        <input type="file" name="files[]" multiple required accept="image/jpeg,image/png,image/jpg,image/webp" id="adminFileInput" class="d-none">
                    </div>

                    <div id="adminSelectedFiles" class="card border-0 bg-light mb-4 d-none">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: .5rem;">
                                <div>
                                    <p class="small font-weight-bold text-uppercase text-muted mb-1">Arquivos prontos</p>
                                    <p id="adminSelectedSummary" class="mb-0 font-weight-bold text-dark"></p>
                                </div>
                                <span id="adminSelectedSize" class="badge badge-pill badge-primary px-3 py-2"></span>
                            </div>
                            <div id="adminSelectedList" class="mt-3"></div>
                        </div>
                    </div>

                    <div class="card border-0 bg-light mb-4">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small font-weight-bold text-uppercase text-muted">Status do envio</span>
                                <span id="adminProgressValue" class="small font-weight-bold text-primary">0%</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 10px;">
                                <div id="adminProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <p id="adminProgressLabel" class="text-muted small mb-0 mt-2">Aguardando selecao dos arquivos.</p>
                        </div>
                    </div>

                    <button type="submit" id="adminSubmitBtn" class="gallery-admin-primary-btn border-0 w-100 justify-content-center">
                        <i class="fas fa-rocket mr-2"></i> PUBLICAR NA GALERIA
                    </button>

                    <p class="text-center small text-muted font-weight-bold mt-3 mb-0">
                        <i class="fas fa-magic text-primary mr-1"></i> Marca d'agua automatica aplicada quando disponivel
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
