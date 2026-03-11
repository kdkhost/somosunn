<div class="row">
    <div class="col-12 mb-4" id="sec-identity">
        <div class="card card-outline card-purple shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title text-purple font-weight-bold mb-0">
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
                    <div class="col-lg-4 border-lg-right mb-4 mb-lg-0">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Cor do Tema (Pagina Sobre)</label>
                            <input type="color" name="theme_color" class="form-control form-control-color w-100"
                                value="{{ old('theme_color', $data['theme_color'] ?? '#6d28d9') }}"
                                style="height: 45px;">
                            <small class="text-muted d-block mt-2">
                                Esta cor sera aplicada aos gradientes e elementos decorativos da pagina.
                            </small>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold d-block mb-3">Imagem da secao Comunidade</label>
                            @php
                                $networkingImageUrl = !empty($data['networking_image'])
                                    ? asset('storage/' . $data['networking_image'])
                                    : '';
                            @endphp
                            <div class="upload-box premium-upload-box" data-max-size="6291456"
                                data-remove-input="#remove_networking_image"
                                data-preview-image-class="img-fluid rounded shadow-sm border"
                                data-preview-image-style="max-height: 220px; width: 100%; object-fit: cover;"
                                data-existing-url="{{ $networkingImageUrl }}">
                                <input type="file" name="networking_image" id="networking_image"
                                    class="d-none @error('networking_image') is-invalid @enderror" accept="image/*">
                                <input type="hidden" name="remove_networking_image" id="remove_networking_image" value="0">
                                <div class="upload-preview mb-3 drop-zone-area text-center">
                                    @if($networkingImageUrl !== '')
                                        <img src="{{ $networkingImageUrl }}" class="img-fluid rounded shadow-sm border"
                                            style="max-height: 220px; width: 100%; object-fit: cover;">
                                    @else
                                        <div class="text-muted p-4 border-2 border-dashed rounded bg-light d-flex flex-column align-items-center justify-content-center"
                                            style="min-height: 150px;">
                                            <i class="fas fa-image fa-2x mb-2 text-primary opacity-50"></i>
                                            <span class="small font-weight-bold">Imagem da Comunidade</span>
                                            <span class="small opacity-75">Arraste e solte ou clique para selecionar</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="upload-meta text-muted small mb-2 text-center"></div>
                                <small class="text-muted upload-help d-block text-center mb-3"></small>
                                <div class="progress upload-progress progress-sm d-none mb-3">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <div class="upload-actions d-flex justify-content-center gap-2">
                                    <button type="button" class="btn btn-xs btn-primary upload-btn rounded-pill px-3">
                                        <i class="fas fa-upload mr-1"></i> Selecionar imagem
                                    </button>
                                    <button type="button"
                                        class="btn btn-xs btn-danger upload-remove rounded-pill px-2 {{ $networkingImageUrl ? '' : 'd-none' }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @error('networking_image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <small class="text-muted d-block mt-2">
                                Esta imagem e usada na secao Comunidade da pagina <strong>/somos-unicas/sobre</strong>.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12" id="sec-content">
        <div class="card card-outline card-pink shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title text-pink font-weight-bold mb-0">
                    <i class="fas fa-file-alt mr-2"></i> Hero e Conteudo Principal
                </h3>
                <div class="card-tools">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                        <input type="checkbox" class="custom-control-input section-toggle" id="toggle-content"
                            data-section="content" {{ ($data['content_enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="toggle-content">Ativo</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-7">
                        <div class="form-group">
                            <label class="font-weight-bold" for="hero_title">Titulo principal</label>
                            <input type="text" name="hero_title" id="hero_title"
                                class="form-control @error('hero_title') is-invalid @enderror"
                                value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                                placeholder="Ex: Sobre a Somos Unicas...">
                            @error('hero_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold" for="hero_subtitle">Subtitulo / Introducao</label>
                            <textarea name="hero_subtitle" id="hero_subtitle" class="form-control summernote-sm @error('hero_subtitle') is-invalid @enderror"
                                rows="4" placeholder="Breve introducao da pagina sobre...">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
                            @error('hero_subtitle')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="form-group">
                            <label class="font-weight-bold" for="content_title">Titulo da secao de conteudo</label>
                            <input type="text" name="content_title" id="content_title"
                                class="form-control @error('content_title') is-invalid @enderror"
                                value="{{ old('content_title', $data['content_title'] ?? '') }}"
                                placeholder="Ex: Nossa Jornada">
                            @error('content_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold" for="content_body">Corpo do texto</label>
                            <textarea name="content_body" id="content_body" class="form-control summernote @error('content_body') is-invalid @enderror"
                                rows="12">{{ old('content_body', $data['content_body'] ?? '') }}</textarea>
                            @error('content_body')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-xl-5 mt-4 mt-xl-0">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="font-weight-bold d-block" for="hero_image">Imagem de destaque (banner)</label>
                            @php
                                $heroImageUrl = !empty($data['hero_image'])
                                    ? asset('storage/' . $data['hero_image'])
                                    : '';
                            @endphp
                            <div class="upload-box premium-upload-box" data-max-size="6291456"
                                data-remove-input="#remove_hero_image"
                                data-preview-image-class="img-fluid rounded shadow-sm border"
                                data-preview-image-style="max-height: 280px; width: 100%; object-fit: cover;"
                                data-existing-url="{{ $heroImageUrl }}">
                                <input type="file" name="hero_image" id="hero_image"
                                    class="d-none @error('hero_image') is-invalid @enderror" accept="image/*">
                                <input type="hidden" name="remove_hero_image" id="remove_hero_image" value="0">
                                <div class="upload-preview mb-3 drop-zone-area text-center">
                                    @if($heroImageUrl !== '')
                                        <img src="{{ $heroImageUrl }}" class="img-fluid rounded shadow-sm border"
                                            style="max-height: 280px; width: 100%; object-fit: cover;">
                                    @else
                                        <div class="text-muted p-4 border-2 border-dashed rounded bg-white d-flex flex-column align-items-center justify-content-center"
                                            style="min-height: 220px;">
                                            <i class="fas fa-image fa-3x mb-3 text-primary opacity-50"></i>
                                            <span class="font-weight-bold">Banner da pagina</span>
                                            <span class="small opacity-75">Arraste e solte ou clique para selecionar</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="upload-meta text-muted small mb-2 text-center"></div>
                                <small class="text-muted upload-help d-block text-center mb-3"></small>
                                <div class="progress upload-progress progress-sm d-none mb-3">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <div class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary upload-btn rounded-pill px-4 shadow-sm">
                                        <i class="fas fa-images mr-1"></i> Selecionar imagem
                                    </button>
                                    <button type="button"
                                        class="btn btn-sm btn-danger upload-remove rounded-pill px-3 shadow-sm {{ $heroImageUrl ? '' : 'd-none' }}">
                                        <i class="fas fa-trash mr-1"></i> Remover
                                    </button>
                                </div>
                            </div>
                            @error('hero_image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <small class="text-muted d-block mt-2">
                                Esta imagem sera exibida ao lado do titulo principal. Recomendado: 800x600px.
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

    .card-pink {
        border-top: 3px solid #db2777 !important;
    }

    .text-purple {
        color: #6d28d9 !important;
    }

    .text-pink {
        color: #db2777 !important;
    }

    @media (min-width: 992px) {
        .border-lg-right {
            border-right: 1px solid #dee2e6;
        }
    }
</style>
