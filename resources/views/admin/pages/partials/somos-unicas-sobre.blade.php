<div class="row">
    {{-- Seção de Identidade e Cores --}}
    <div class="col-md-12 mb-4">
        <div class="card card-outline card-purple shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title text-purple font-weight-bold">
                    <i class="fas fa-palette mr-2"></i> Identidade Visual e Imagens Extras
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 border-right">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Cor do Tema (Página Sobre)</label>
                            <div class="input-group">
                                <input type="color" name="theme_color" class="form-control form-control-color w-100"
                                    value="{{ $data['theme_color'] ?? '#6d28d9' }}" style="height: 45px;">
                            </div>
                            <small class="text-muted d-block mt-2">
                                Esta cor será aplicada aos gradientes e elementos decorativos da página Sobre.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Imagem da Seção "Comunidade" desta página</label>
                            <div class="upload-box border rounded p-2 bg-light text-center" id="networkingImageBox"
                                style="min-height: 120px;" data-max-size="4194304"
                                data-existing-url="{{ isset($data['networking_image']) ? asset('storage/' . $data['networking_image']) : '' }}"
                                data-remove-input="[name='remove_networking_image']">
                                <input type="file" name="networking_image" id="networking_image" accept="image/*"
                                    class="d-none">
                                <input type="hidden" name="remove_networking_image" value="0">
                                <div class="upload-preview mb-1 mx-auto"
                                    style="max-width: 150px; height: 80px; overflow: hidden; border-radius: 8px;"></div>
                                <button type="button" class="btn btn-xs btn-primary upload-trigger">
                                    <i class="fas fa-upload mr-1"></i> Trocar Imagem da Seção
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Hero & Conteúdo Principal --}}
    <div class="col-md-12 mb-4">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title text-primary font-weight-bold">
                    <i class="fas fa-edit mr-2"></i> Hero e Conteúdo Institucional
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7 border-right">
                        <div class="form-group">
                            <label class="font-weight-bold">Título da Página (Hero)</label>
                            <input type="text" name="hero_title" class="form-control form-control-lg"
                                placeholder="Ex: Sobre a Somos Únicas..." value="{{ $data['hero_title'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Subtítulo / Introdução</label>
                            <textarea name="hero_subtitle" class="form-control summernote" rows="4"
                                placeholder="Breve introdução da página sobre...">{{ $data['hero_subtitle'] ?? '' }}</textarea>
                        </div>

                        <hr class="my-4">

                        <div class="form-group">
                            <label class="font-weight-bold text-lg"><i class="fas fa-file-alt mr-1"></i> Conteúdo
                                Detalhado</label>
                            <div class="form-group mt-2">
                                <label>Título da Seção de Conteúdo</label>
                                <input type="text" name="content_title" class="form-control"
                                    placeholder="Ex: Nossa Jornada" value="{{ $data['content_title'] ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label>Corpo do Texto (Completo)</label>
                                <textarea name="content_body" class="form-control summernote"
                                    rows="12">{{ $data['content_body'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group text-center">
                            <label class="font-weight-bold">Imagem de Destaque (Banner)</label>
                            <div class="upload-box border rounded p-3 bg-light" id="heroAboutImageBox"
                                data-max-size="6291456"
                                data-existing-url="{{ isset($data['hero_image']) ? asset('storage/' . $data['hero_image']) : '' }}"
                                data-remove-input="[name='remove_hero_image']">
                                <input type="file" name="hero_image" id="hero_image" accept="image/*" class="d-none">
                                <input type="hidden" name="remove_hero_image" value="0">
                                <div class="upload-preview mb-2 mx-auto"
                                    style="max-width: 100%; height: 250px; overflow: hidden; border-radius: 15px;">
                                </div>
                                <div class="upload-meta text-muted mb-2"></div>
                                <div class="progress upload-progress progress-sm d-none mb-3">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger upload-remove d-none">
                                        <i class="fas fa-trash mr-1"></i> Remover Imagem
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary upload-trigger">
                                        <i class="fas fa-upload mr-1"></i> Escolher Imagem
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-3 px-4">
                                Esta imagem será exibida ao lado do título principal. Recomendado: 800x600px ou similar.
                            </small>
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
        min-height: 300px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border: 2px dashed #ddd !important;
        transition: all 0.3s;
        background: #fafafa !important;
    }

    .upload-box:hover {
        border-color: #db2777 !important;
        background: #fff5f7 !important;
    }

    .upload-preview img {
        max-width: 100%;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .gap-2 {
        gap: 0.5rem;
    }
</style>
