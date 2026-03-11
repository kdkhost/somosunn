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
                            <label class="font-weight-bold" for="networking_image">Imagem da secao Comunidade</label>
                            <div class="custom-file">
                                <input type="file" name="networking_image" id="networking_image"
                                    class="custom-file-input @error('networking_image') is-invalid @enderror"
                                    accept="image/*" data-preview="prev-networking_image">
                                <label class="custom-file-label" for="networking_image">Escolher imagem...</label>
                            </div>
                            @error('networking_image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <small class="text-muted d-block mt-2">
                                Esta imagem e usada na secao Comunidade da pagina <strong>/somos-unicas/sobre</strong>.
                            </small>

                            @if(!empty($data['networking_image']))
                                <div class="custom-control custom-checkbox mt-3">
                                    <input type="checkbox" class="custom-control-input" id="remove_networking_image"
                                        name="remove_networking_image" value="1">
                                    <label class="custom-control-label text-danger" for="remove_networking_image">
                                        Remover imagem atual
                                    </label>
                                </div>
                            @endif

                            @php
                                $networkingImageUrl = !empty($data['networking_image'])
                                    ? asset('storage/' . $data['networking_image'])
                                    : '';
                            @endphp
                            <div class="mt-3">
                                <img id="prev-networking_image" src="{{ $networkingImageUrl }}"
                                    class="img-fluid rounded shadow-sm border {{ $networkingImageUrl ? '' : 'd-none' }}"
                                    alt="Preview da imagem da secao Comunidade"
                                    style="max-height: 220px; width: 100%; object-fit: cover;">
                            </div>
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
                            <div class="custom-file">
                                <input type="file" name="hero_image" id="hero_image"
                                    class="custom-file-input @error('hero_image') is-invalid @enderror"
                                    accept="image/*" data-preview="prev-hero_image">
                                <label class="custom-file-label" for="hero_image">Escolher imagem...</label>
                            </div>
                            @error('hero_image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <small class="text-muted d-block mt-2">
                                Esta imagem sera exibida ao lado do titulo principal. Recomendado: 800x600px.
                            </small>

                            @if(!empty($data['hero_image']))
                                <div class="custom-control custom-checkbox mt-3">
                                    <input type="checkbox" class="custom-control-input" id="remove_hero_image"
                                        name="remove_hero_image" value="1">
                                    <label class="custom-control-label text-danger" for="remove_hero_image">
                                        Remover imagem atual
                                    </label>
                                </div>
                            @endif

                            @php
                                $heroImageUrl = !empty($data['hero_image'])
                                    ? asset('storage/' . $data['hero_image'])
                                    : '';
                            @endphp
                            <div class="mt-3">
                                <img id="prev-hero_image" src="{{ $heroImageUrl }}"
                                    class="img-fluid rounded shadow-sm border {{ $heroImageUrl ? '' : 'd-none' }}"
                                    alt="Preview do banner da pagina"
                                    style="max-height: 280px; width: 100%; object-fit: cover;">
                            </div>
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
