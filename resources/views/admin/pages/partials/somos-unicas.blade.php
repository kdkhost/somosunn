<div class="row">
    {{-- Seção de Identidade e Cores --}}
    <div class="col-md-12 mb-4">
        <div class="card card-outline card-pink shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title text-purple font-weight-bold">
                    <i class="fas fa-palette mr-2"></i> Identidade Visual e Imagens Extras
                </h3>
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

    {{-- Hero Section - Destaque Principal --}}
    <div class="col-md-12 mb-4">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title text-primary font-weight-bold">
                    <i class="fas fa-star mr-2"></i> Banner Principal (Hero)
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group">
                            <label class="font-weight-bold">Título de Impacto</label>
                            <input type="text" name="hero_title" class="form-control form-control-lg"
                                placeholder="Ex: Somos Únicas: o seu espaço..." value="{{ $data['hero_title'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Texto de Apoio (Subtítulo)</label>
                            <textarea name="hero_subtitle" class="form-control summernote" rows="4"
                                placeholder="Descreva o propósito da área...">{{ $data['hero_subtitle'] ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="font-weight-bold">Imagem de Destaque</label>
                            <div class="upload-box border rounded p-3 bg-light text-center" id="heroImageBox"
                                data-max-size="6291456"
                                data-existing-url="{{ isset($data['hero_image']) ? asset('storage/' . $data['hero_image']) : '' }}"
                                data-remove-input="[name='remove_hero_image']">
                                <input type="file" name="hero_image" id="hero_image" accept="image/*" class="d-none">
                                <input type="hidden" name="remove_hero_image" value="0">
                                <div class="upload-preview mb-2 mx-auto"
                                    style="max-width: 100%; height: 200px; overflow: hidden; border-radius: 15px;">
                                </div>
                                <div class="upload-meta text-muted mb-2"></div>
                                <div class="progress upload-progress progress-sm d-none mb-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger upload-remove d-none">
                                    <i class="fas fa-trash mr-1"></i> Remover Imagem
                                </button>
                                <button type="button" class="btn btn-sm btn-primary upload-trigger mt-2">
                                    <i class="fas fa-upload mr-1"></i> Escolher Imagem
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2 text-center">Recomendado: 1200x800px ou similar. Máx:
                                6MB.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cabeçalhos de Seletores --}}
    <div class="col-md-12 mb-4">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title text-info font-weight-bold">
                    <i class="fas fa-heading mr-2"></i> Títulos das Seções Internas
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    {{-- Cursos --}}
                    <div class="col-md-4 border-right">
                        <h5 class="text-muted mb-3"><i class="fas fa-graduation-cap mr-1"></i> Cursos</h5>
                        <div class="form-group">
                            <label>Título da Seção</label>
                            <input type="text" name="courses_title" class="form-control"
                                value="{{ $data['courses_title'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Subtítulo</label>
                            <input type="text" name="courses_subtitle" class="form-control"
                                value="{{ $data['courses_subtitle'] ?? '' }}">
                        </div>
                    </div>
                    {{-- Eventos --}}
                    <div class="col-md-4 border-right">
                        <h5 class="text-muted mb-3"><i class="fas fa-calendar-alt mr-1"></i> Eventos</h5>
                        <div class="form-group">
                            <label>Título da Seção</label>
                            <input type="text" name="events_title" class="form-control"
                                value="{{ $data['events_title'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Subtítulo</label>
                            <input type="text" name="events_subtitle" class="form-control"
                                value="{{ $data['events_subtitle'] ?? '' }}">
                        </div>
                    </div>
                    {{-- Mentorias --}}
                    <div class="col-md-4">
                        <h5 class="text-muted mb-3"><i class="fas fa-users mr-1"></i> Mentorias</h5>
                        <div class="form-group">
                            <label>Título da Seção</label>
                            <input type="text" name="mentorships_title" class="form-control"
                                value="{{ $data['mentorships_title'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Subtítulo</label>
                            <input type="text" name="mentorships_subtitle" class="form-control"
                                value="{{ $data['mentorships_subtitle'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estado Vazio --}}
    <div class="col-md-12">
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title text-muted font-weight-bold">
                    <i class="fas fa-info-circle mr-2"></i> Mensagem para Conteúdo Vazio
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Título do Alerta</label>
                            <input type="text" name="empty_title" class="form-control"
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
