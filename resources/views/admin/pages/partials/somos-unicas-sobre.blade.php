<div class="row">
    <div class="col-md-12" id="sec-identity">
        <div class="card card-pink card-outline">
            <div class="card-header">
                <h3 class="card-title">Configurações de Identidade</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Cor do Tema (Hexadecimal)</label>
                    <input type="color" name="theme_color" class="form-control"
                        value="{{ $data['theme_color'] ?? '#db2777' }}">
                    <small class="text-muted">A cor será aplicada aos gradientes e elementos desta página.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12" id="sec-content">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Hero & Conteúdo Principal</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Título do Hero</label>
                            <input type="text" name="hero_title" class="form-control"
                                value="{{ $data['hero_title'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Subtítulo do Hero</label>
                            <textarea name="hero_subtitle" class="form-control summernote"
                                rows="3">{{ $data['hero_subtitle'] ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Imagem do Hero (Fundo)</label>
                            <div class="upload-box" id="heroAboutImageBox" data-max-size="6291456"
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
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label>Título da Seção de Conteúdo</label>
                    <input type="text" name="content_title" class="form-control"
                        value="{{ $data['content_title'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Corpo do Texto</label>
                    <textarea name="content_body" class="form-control summernote"
                        rows="10">{{ $data['content_body'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>