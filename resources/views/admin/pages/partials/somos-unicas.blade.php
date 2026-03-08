<div class="row">
    <div class="col-md-12" id="sec-identity">
        <div class="card card-pink card-outline">
            <div class="card-header">
                <h3 class="card-title">Configurações de Identidade</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Cor do Tema (Hexadecimal)</label>
                    <div class="input-group my-colorpicker2">
                        <input type="color" name="theme_color" class="form-control"
                            value="{{ $data['theme_color'] ?? '#db2777' }}">
                    </div>
                    <small class="text-muted">Esta cor será aplicada a botões, títulos e gradientes da área Somos
                        Únicas.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6" id="sec-hero">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Hero Section</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Título do Hero</label>
                    <input type="text" name="hero_title" class="form-control" value="{{ $data['hero_title'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Subtítulo do Hero</label>
                    <textarea name="hero_subtitle" class="form-control summernote"
                        rows="3">{{ $data['hero_subtitle'] ?? '' }}</textarea>
                </div>
                <div class="form-group">
                    <label>Imagem do Hero</label>
                    <div class="upload-box" id="heroImageBox" data-max-size="6291456"
                        data-existing-url="{{ isset($data['hero_image']) ? asset('storage/' . $data['hero_image']) : '' }}"
                        data-remove-input="[name='remove_hero_image']">
                        <input type="file" name="hero_image" id="hero_image" accept="image/*" class="d-none">
                        <input type="hidden" name="remove_hero_image" value="0">
                        <div class="upload-preview mb-2"></div>
                        <div class="upload-meta text-muted"></div>
                        <div class="progress upload-progress progress-sm d-none mt-2">
                            <div class="progress-bar bg-primary" style="width:0%"></div>
                        </div>
                        <button type="button"
                            class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                    </div>
                    <small class="text-muted">Tamanho recomendado: 1920x600px. Máx: 6MB.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6" id="sec-headers">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Cabeçalhos de Seções</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Título (Cursos)</label>
                            <input type="text" name="courses_title" class="form-control"
                                value="{{ $data['courses_title'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Subtítulo (Cursos)</label>
                            <input type="text" name="courses_subtitle" class="form-control"
                                value="{{ $data['courses_subtitle'] ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Título (Eventos)</label>
                            <input type="text" name="events_title" class="form-control"
                                value="{{ $data['events_title'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Subtítulo (Eventos)</label>
                            <input type="text" name="events_subtitle" class="form-control"
                                value="{{ $data['events_subtitle'] ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Título (Mentorias)</label>
                            <input type="text" name="mentorships_title" class="form-control"
                                value="{{ $data['mentorships_title'] ?? '' }}">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Subtítulo (Mentorias)</label>
                            <input type="text" name="mentorships_subtitle" class="form-control"
                                value="{{ $data['mentorships_subtitle'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12" id="sec-empty">
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">Estado Vazio (Sem conteúdos)</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Título de Alerta</label>
                    <input type="text" name="empty_title" class="form-control" value="{{ $data['empty_title'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Mensagem Descritiva</label>
                    <textarea name="empty_description" class="form-control summernote"
                        rows="3">{{ $data['empty_description'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>