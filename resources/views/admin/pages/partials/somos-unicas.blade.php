<div class="row">
    <div class="col-12 mb-4" id="sec-identity">
        <div class="card card-outline card-pink shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title text-pink font-weight-bold mb-0">
                    <i class="fas fa-palette mr-2"></i> Identidade Visual
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
                            <label class="font-weight-bold" for="theme_color">Cor do Tema</label>
                            <input type="color" id="theme_color" name="theme_color"
                                class="form-control form-control-color w-100"
                                value="{{ old('theme_color', $data['theme_color'] ?? '#db2777') }}"
                                style="height: 45px;">
                            <small class="text-muted d-block mt-2">
                                Esta cor controla os gradientes, destaques e elementos decorativos da area Somos Unicas.
                            </small>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="alert alert-light border mb-0 h-100 d-flex align-items-center">
                            <div>
                                <div class="font-weight-bold text-pink mb-1">
                                    <i class="fas fa-info-circle mr-1"></i> Imagem da secao Comunidade
                                </div>
                                <div class="text-muted small mb-0">
                                    A imagem institucional da secao Comunidade da pagina
                                    <strong>/somos-unicas/sobre</strong> e gerenciada no editor
                                    <strong>Somos Unicas (Sobre)</strong>, para manter o mesmo visual em toda a experiencia.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4" id="sec-hero">
        <div class="card card-outline card-purple shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title text-purple font-weight-bold mb-0">
                    <i class="fas fa-star mr-2"></i> Hero Principal
                </h3>
                <div class="card-tools">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                        <input type="checkbox" class="custom-control-input section-toggle" id="toggle-hero"
                            data-section="hero" {{ ($data['hero_enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="toggle-hero">Ativo</label>
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
                                placeholder="Ex: Somos Unicas">
                            @error('hero_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold" for="hero_subtitle">Subtitulo / Introducao</label>
                            <textarea name="hero_subtitle" id="hero_subtitle"
                                class="form-control summernote-sm @error('hero_subtitle') is-invalid @enderror" rows="5"
                                placeholder="Explique o posicionamento da area Somos Unicas...">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
                            @error('hero_subtitle')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-xl-5 mt-4 mt-xl-0">
                        <div class="border rounded p-3 bg-light h-100">
                            <label class="font-weight-bold d-block mb-3" for="hero_image">Imagem do Hero</label>
                            @include('admin.components.upload-global', [
                                'name' => 'hero_image',
                                'path' => $data['hero_image'] ?? null,
                                'preview_url' => !empty($data['hero_image']) ? asset('storage/' . $data['hero_image']) : null,
                                'remove_name' => 'remove_hero_image',
                                'accept' => 'image/*',
                                'max_size' => 6291456,
                                'label' => null,
                                'help' => 'PNG, JPG, WebP, GIF ou SVG - maximo de 6 MB. Esta imagem aparece ao lado do hero.',
                            ])
                            @error('hero_image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4" id="sec-headers">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title text-primary font-weight-bold mb-0">
                    <i class="fas fa-heading mr-2"></i> Titulos das Vitrines
                </h3>
                <div class="card-tools">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                        <input type="checkbox" class="custom-control-input section-toggle" id="toggle-headers"
                            data-section="headers" {{ ($data['headers_enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="toggle-headers">Ativo</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="border rounded p-3 h-100 bg-light">
                            <h4 class="font-weight-bold text-pink mb-3">Cursos</h4>
                            <div class="form-group">
                                <label class="font-weight-bold" for="courses_title">Titulo</label>
                                <input type="text" name="courses_title" id="courses_title"
                                    class="form-control @error('courses_title') is-invalid @enderror"
                                    value="{{ old('courses_title', $data['courses_title'] ?? '') }}"
                                    placeholder="Cursos & Capacitacao">
                                @error('courses_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold" for="courses_subtitle">Subtitulo</label>
                                <input type="text" name="courses_subtitle" id="courses_subtitle"
                                    class="form-control @error('courses_subtitle') is-invalid @enderror"
                                    value="{{ old('courses_subtitle', $data['courses_subtitle'] ?? '') }}"
                                    placeholder="Aperfeicoe suas habilidades">
                                @error('courses_subtitle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mt-4 mt-lg-0">
                        <div class="border rounded p-3 h-100 bg-light">
                            <h4 class="font-weight-bold text-purple mb-3">Eventos</h4>
                            <div class="form-group">
                                <label class="font-weight-bold" for="events_title">Titulo</label>
                                <input type="text" name="events_title" id="events_title"
                                    class="form-control @error('events_title') is-invalid @enderror"
                                    value="{{ old('events_title', $data['events_title'] ?? '') }}"
                                    placeholder="Eventos & Palestras">
                                @error('events_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold" for="events_subtitle">Subtitulo</label>
                                <input type="text" name="events_subtitle" id="events_subtitle"
                                    class="form-control @error('events_subtitle') is-invalid @enderror"
                                    value="{{ old('events_subtitle', $data['events_subtitle'] ?? '') }}"
                                    placeholder="Encontros especiais para mulheres incriveis">
                                @error('events_subtitle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mt-4 mt-lg-0">
                        <div class="border rounded p-3 h-100 bg-light">
                            <h4 class="font-weight-bold text-primary mb-3">Mentorias</h4>
                            <div class="form-group">
                                <label class="font-weight-bold" for="mentorships_title">Titulo</label>
                                <input type="text" name="mentorships_title" id="mentorships_title"
                                    class="form-control @error('mentorships_title') is-invalid @enderror"
                                    value="{{ old('mentorships_title', $data['mentorships_title'] ?? '') }}"
                                    placeholder="Mentorias Exclusivas">
                                @error('mentorships_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold" for="mentorships_subtitle">Subtitulo</label>
                                <input type="text" name="mentorships_subtitle" id="mentorships_subtitle"
                                    class="form-control @error('mentorships_subtitle') is-invalid @enderror"
                                    value="{{ old('mentorships_subtitle', $data['mentorships_subtitle'] ?? '') }}"
                                    placeholder="Aconselhamento com grandes lideres">
                                @error('mentorships_subtitle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12" id="sec-empty">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title text-warning font-weight-bold mb-0">
                    <i class="fas fa-ghost mr-2"></i> Estado Vazio
                </h3>
                <div class="card-tools">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                        <input type="checkbox" class="custom-control-input section-toggle" id="toggle-empty"
                            data-section="empty" {{ ($data['empty_enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="toggle-empty">Ativo</label>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="font-weight-bold" for="empty_title">Titulo de fallback</label>
                    <input type="text" name="empty_title" id="empty_title"
                        class="form-control @error('empty_title') is-invalid @enderror"
                        value="{{ old('empty_title', $data['empty_title'] ?? '') }}"
                        placeholder="Ex: Em breve!">
                    @error('empty_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold" for="empty_description">Descricao de fallback</label>
                    <textarea name="empty_description" id="empty_description" rows="4"
                        class="form-control @error('empty_description') is-invalid @enderror"
                        placeholder="Mensagem exibida quando ainda nao houver cursos, eventos ou mentorias cadastrados.">{{ old('empty_description', $data['empty_description'] ?? '') }}</textarea>
                    @error('empty_description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card-pink {
        border-top: 3px solid #db2777 !important;
    }

    .card-purple {
        border-top: 3px solid #6d28d9 !important;
    }

    .text-pink {
        color: #db2777 !important;
    }

    .text-purple {
        color: #6d28d9 !important;
    }

    @media (min-width: 992px) {
        .border-lg-right {
            border-right: 1px solid #dee2e6;
        }
    }
</style>
