<div class="row">
    {{-- Seção de Identidade e Cores --}}
    <div class="col-md-12 mb-4">
        <div class="card card-outline card-pink shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title text-purple font-weight-bold">
                    <i class="fas fa-palette mr-2"></i> Identidade Visual e Imagens Extras
                </h3>
                <div class="card-tools">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                        <input type="checkbox" class="custom-control-input section-toggle" id="toggle-identity"
                            data-section="identity" {{ ($data['identity_enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="toggle-identity">Ativo</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 border-right">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Cor do Tema (Principal)</label>
                            <div class="input-group">
                                <input type="color" name="theme_color" class="form-control form-control-color w-100"
                                    value="{{ $data['theme_color'] ?? '#6d28d9' }}" style="height: 45px;">
                            </div>
                            <small class="text-muted d-block mt-2">
                                Esta cor define a essência da área Somos Únicas.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-8 d-flex align-items-center">
                        <div class="alert alert-light border mb-0 w-100">
                            <div class="font-weight-bold text-purple mb-1">
                                <i class="fas fa-info-circle mr-1"></i> Imagem da seção "Comunidade"
                            </div>
                            <div class="text-muted small mb-0">
                                A imagem usada na página <strong>/somos-unicas/sobre</strong> é editada somente na
                                manutenção da própria página "Somos Únicas Sobre".
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    value="{{ $data['empty_title'] ?? '' }}">
</div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label class="font-weight-bold">Descrição</label>
        <textarea name="empty_description" class="form-control summernote"
            rows="2">{{ $data['empty_description'] ?? '' }}</textarea>
    </div>
</div>
</div>
</div>
</div>
</div>
</div>

<style>
    .card-purple {
        border-top: 3px solid #6d28d9 !important;
    }

    .text-purple {
        color: #6d28d9 !important;
    }

    .upload-box {
        min-height: 250px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border: 2px dashed #ddd !important;
        transition: all 0.3s;
    }

    .upload-box:hover {
        border-color: #db2777 !important;
        background: #fff5f7 !important;
    }

    .upload-preview img {
        max-width: 100%;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>