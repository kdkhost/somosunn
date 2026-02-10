<div class="card-body">
    <h5 class="text-primary mb-3"><i class="fas fa-shield-alt mr-2"></i>Segurança (reCAPTCHA v3)</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Site Key</label>
            <input name="recaptcha_v3_site_key" class="form-control"
                value="{{ $settings['recaptcha_v3_site_key'] ?? config('services.recaptcha.site_key') }}">
        </div>
        <div class="col-md-6 form-group">
            <label>Secret Key</label>
            <input name="recaptcha_v3_secret_key" type="password" class="form-control"
                value="{{ $settings['recaptcha_v3_secret_key'] ?? config('services.recaptcha.v3_secret') }}">
        </div>
        <div class="col-md-4 form-group">
            <label>Score Mínimo (0.0 a 1.0)</label>
            <input name="recaptcha_v3_min_score" class="form-control"
                value="{{ $settings['recaptcha_v3_min_score'] ?? config('services.recaptcha.v3_min_score', 0.5) }}">
        </div>
    </div>
    <hr>

    <h5 class="text-primary mb-3"><i class="fas fa-server mr-2"></i>Limites e Armazenamento</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Limite de Upload de Vídeo (MB)</label>
            <input type="number" name="video_max_mb" class="form-control"
                value="{{ $settings['video_max_mb'] ?? '1024' }}">
        </div>
        <div class="col-md-6 form-group">
            <label>Limite de Upload de Arquivos (MB)</label>
            <input type="number" name="document_max_mb" class="form-control"
                value="{{ $settings['document_max_mb'] ?? '50' }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label>Formatos de Vídeo Permitidos</label>
            <input name="allowed_video_formats" class="form-control"
                value="{{ $settings['allowed_video_formats'] ?? implode(',', config('uploads.allowed_video_formats', [])) }}"
                placeholder="mp4,webm,mkv">
        </div>
        <div class="col-md-6 form-group">
            <label>Formatos de Documento Permitidos</label>
            <input name="allowed_document_formats" class="form-control"
                value="{{ $settings['allowed_document_formats'] ?? implode(',', config('uploads.allowed_document_formats', [])) }}"
                placeholder="pdf,docx,pptx">
        </div>
    </div>

    <hr>
    <h6 class="font-weight-bold">Armazenamento (S3 / Local)</h6>
    <div class="form-group">
        <label>Disco de Uploads</label>
        <select name="uploads_storage_disk" class="form-control">
            <option value="public" {{ ($settings['uploads_storage_disk'] ?? 'public') === 'public' ? 'selected' : '' }}>
                Local (Public)</option>
            <option value="s3" {{ ($settings['uploads_storage_disk'] ?? '') === 's3' ? 'selected' : '' }}>
                Amazon S3 / Compatível</option>
        </select>
    </div>

    <div class="card card-body bg-light">
        <div class="row">
            <div class="col-md-6 form-group"><label>S3 Key</label><input name="s3_key" class="form-control"
                    value="{{ $settings['s3_key'] ?? '' }}"></div>
            <div class="col-md-6 form-group"><label>S3 Secret</label><input name="s3_secret" class="form-control"
                    value="{{ $settings['s3_secret'] ?? '' }}"></div>
            <div class="col-md-4 form-group"><label>S3 Bucket</label><input name="s3_bucket" class="form-control"
                    value="{{ $settings['s3_bucket'] ?? '' }}"></div>
            <div class="col-md-4 form-group"><label>S3 Region</label><input name="s3_region" class="form-control"
                    value="{{ $settings['s3_region'] ?? '' }}"></div>
            <div class="col-md-4 form-group"><label>S3 Endpoint</label><input name="s3_endpoint" class="form-control"
                    value="{{ $settings['s3_endpoint'] ?? '' }}"></div>
            <div class="col-md-4 form-group"><label>S3 Public URL (CDN)</label><input name="s3_url" class="form-control"
                    value="{{ $settings['s3_url'] ?? '' }}"></div>
            <div class="col-md-12">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="s3_path_style" value="0">
                    <input type="checkbox" class="custom-control-input" id="s3_path_style" name="s3_path_style"
                        value="1" {{ ($settings['s3_path_style'] ?? 0) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="s3_path_style">Usar Endpoint Path-Style
                        (MinIO/Compatíveis)</label>
                </div>
            </div>
        </div>
    </div>
</div>