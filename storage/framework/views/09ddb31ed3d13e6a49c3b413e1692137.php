<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-cogs mr-2"></i> Configurações globais do sistema, segurança e armazenamento de arquivos.
    </div>

    
    <div class="card card-outline card-danger mb-4">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-shield-alt mr-2"></i> Segurança (Google reCAPTCHA
                v3)</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Site Key</label>
                    <input type="text" name="recaptcha_v3_site_key" class="form-control"
                        value="<?php echo e($settings['recaptcha_v3_site_key'] ?? config('services.recaptcha.site_key')); ?>">
                </div>
                <div class="col-md-6 form-group">
                    <label>Secret Key</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                        <input type="password" name="recaptcha_v3_secret_key" class="form-control"
                            value="<?php echo e($settings['recaptcha_v3_secret_key'] ?? config('services.recaptcha.v3_secret')); ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Score Mínimo (0.0 a 1.0)</label>
                    <input type="number" step="0.1" min="0" max="1" name="recaptcha_v3_min_score" class="form-control"
                        value="<?php echo e($settings['recaptcha_v3_min_score'] ?? config('services.recaptcha.v3_min_score', 0.5)); ?>">
                    <small class="text-muted">Recomendado: 0.5</small>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card card-outline card-info mb-4">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-clock mr-2"></i> Agendador Interno (sem cron da hospedagem)</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Executa o <strong>Laravel Scheduler</strong> automaticamente com base em acessos ao site e processa filas (ex: e-mails/faturas).
            </p>

            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="hidden" name="internal_cron_enabled" value="0">
                <input type="checkbox" class="custom-control-input" id="internal_cron_enabled" name="internal_cron_enabled"
                    value="1" <?php echo e(((int) ($settings['internal_cron_enabled'] ?? (config('internal_cron.enabled', true) ? 1 : 0))) ? 'checked' : ''); ?>>
                <label class="custom-control-label" for="internal_cron_enabled">Ativar agendador interno</label>
            </div>

            <div class="row mt-3">
                <div class="col-md-4 form-group mb-0">
                    <label>Intervalo mínimo (segundos)</label>
                    <input type="number" min="10" name="internal_cron_min_interval_seconds" class="form-control"
                        value="<?php echo e($settings['internal_cron_min_interval_seconds'] ?? config('internal_cron.min_interval_seconds', 60)); ?>">
                    <small class="text-muted">Recomendado: 30–60. Mínimo: 10.</small>
                </div>
            </div>

            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-3">
                <input type="hidden" name="internal_cron_run_queue_worker" value="0">
                <input type="checkbox" class="custom-control-input" id="internal_cron_run_queue_worker"
                    name="internal_cron_run_queue_worker" value="1" <?php echo e(((int) ($settings['internal_cron_run_queue_worker'] ?? (config('internal_cron.run_queue_worker', true) ? 1 : 0))) ? 'checked' : ''); ?>>
                <label class="custom-control-label" for="internal_cron_run_queue_worker">Processar fila automaticamente (e-mails/faturas)</label>
            </div>
        </div>
    </div>

    
    <h5 class="text-primary mb-3"><i class="fas fa-server mr-2"></i> Limites e Uploads</h5>
    <div class="row">
        <div class="col-md-6">
            <div class="info-box bg-light">
                <span class="info-box-icon bg-info"><i class="fas fa-video"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Upload de Vídeo</span>
                    <span class="info-box-number">
                        <input type="number" name="video_max_mb"
                            class="form-control form-control-sm d-inline-block w-50"
                            value="<?php echo e($settings['video_max_mb'] ?? '1024'); ?>"> MB
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label>Formatos de Vídeo Permitidos</label>
                <input type="text" name="allowed_video_formats" class="form-control"
                    value="<?php echo e($settings['allowed_video_formats'] ?? implode(',', config('uploads.allowed_video_formats', []))); ?>"
                    placeholder="mp4,webm,mkv">
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box bg-light">
                <span class="info-box-icon bg-warning"><i class="fas fa-file-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Upload de Arquivos</span>
                    <span class="info-box-number">
                        <input type="number" name="document_max_mb"
                            class="form-control form-control-sm d-inline-block w-50"
                            value="<?php echo e($settings['document_max_mb'] ?? '50'); ?>"> MB
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label>Formatos de Documento Permitidos</label>
                <input type="text" name="allowed_document_formats" class="form-control"
                    value="<?php echo e($settings['allowed_document_formats'] ?? implode(',', config('uploads.allowed_document_formats', []))); ?>"
                    placeholder="pdf,docx,pptx,zip,rar">
            </div>
        </div>
    </div>

    <hr class="my-4">

    
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-hdd mr-2"></i> Armazenamento (Filesystem)</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Disco Principal de Uploads</label>
                <select name="uploads_storage_disk" class="form-control" id="storage_disk_select">
                    <option value="public" <?php echo e(($settings['uploads_storage_disk'] ?? 'public') === 'public' ? 'selected' : ''); ?>>
                        Local (Public Storage) - Padrão</option>
                    <option value="s3" <?php echo e(($settings['uploads_storage_disk'] ?? '') === 's3' ? 'selected' : ''); ?>>
                        Amazon S3 / Compatível (MinIO, DigitalOcean Spaces, etc)</option>
                </select>
            </div>

            <div id="s3_config_container" class="mt-4 p-3 bg-light rounded border"
                style="<?php echo e(($settings['uploads_storage_disk'] ?? 'public') === 's3' ? '' : 'display:none;'); ?>">
                <h6 class="text-primary font-weight-bold mb-3"><i class="fab fa-aws mr-1"></i> Configurações S3</h6>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Access Key ID</label>
                        <input type="text" name="s3_key" class="form-control" value="<?php echo e($settings['s3_key'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Secret Access Key</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                            </div>
                            <input type="password" name="s3_secret" class="form-control"
                                value="<?php echo e($settings['s3_secret'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Bucket Name</label>
                        <input type="text" name="s3_bucket" class="form-control"
                            value="<?php echo e($settings['s3_bucket'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Region (Ex: us-east-1)</label>
                        <input type="text" name="s3_region" class="form-control"
                            value="<?php echo e($settings['s3_region'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Endpoint (Opcional)</label>
                        <input type="text" name="s3_endpoint" class="form-control"
                            value="<?php echo e($settings['s3_endpoint'] ?? ''); ?>">
                        <small class="text-muted">Para MinIO ou Spaces.</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label>Public URL / CDN (Opcional)</label>
                        <input type="text" name="s3_url" class="form-control" value="<?php echo e($settings['s3_url'] ?? ''); ?>">
                        <small class="text-muted">URL base para acesso público aos arquivos (ex:
                            https://cdn.meusite.com). Se vazio, usa a URL padrão do S3.</small>
                    </div>
                </div>
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                    <input type="hidden" name="s3_path_style" value="0">
                    <input type="checkbox" class="custom-control-input" id="s3_path_style" name="s3_path_style"
                        value="1" <?php echo e(($settings['s3_path_style'] ?? 0) ? 'checked' : ''); ?>>
                    <label class="custom-control-label" for="s3_path_style">Forçar Path-Style Endpoint (Necessário para
                        MinIO)</label>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#storage_disk_select').change(function () {
                if ($(this).val() === 's3') {
                    $('#s3_config_container').slideDown();
                } else {
                    $('#s3_config_container').slideUp();
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\settings\partials\system.blade.php ENDPATH**/ ?>