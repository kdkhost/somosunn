<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle mr-2"></i> Gerencie os logotipos e imagens de fundo do seu sistema. Recomenda-se
        usar imagens otimizadas (WebP ou PNG transparente).
    </div>

    <h5 class="text-primary mb-3"><i class="fas fa-id-card mr-2"></i> Identidade Visual</h5>
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="form-group text-center">
                <label class="font-weight-bold">Logo Principal (Header)</label>
                <div class="upload-box" data-remove-input="#remove_logo_image"
                    data-existing-url="<?php echo e($getUrl('logo_image')); ?>">
                    <input type="file" name="logo_image" class="d-none" accept="image/*">
                    <input type="hidden" name="remove_logo_image" id="remove_logo_image" value="0">
                    <div class="upload-preview mb-2">
                        <?php if($url = $getUrl('logo_image')): ?>
                            <img src="<?php echo e($url); ?>" class="img-fluid rounded border" style="max-height: 80px;">
                        <?php else: ?>
                            <div class="text-muted p-3 border rounded bg-light">
                                <i class="fas fa-image fa-2x mb-1"></i><br>Logo Site
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i
                            class="fas fa-upload"></i> Alterar</button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger upload-remove <?php echo e($getUrl('logo_image') ? '' : 'd-none'); ?>"><i
                            class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="form-group text-center">
                <label class="font-weight-bold">Favicon (Navegador)</label>
                <div class="upload-box" data-remove-input="#remove_favicon_image"
                    data-existing-url="<?php echo e($getUrl('favicon_image')); ?>">
                    <input type="file" name="favicon_image" class="d-none" accept="image/*">
                    <input type="hidden" name="remove_favicon_image" id="remove_favicon_image" value="0">
                    <div class="upload-preview mb-2">
                        <?php if($url = $getUrl('favicon_image')): ?>
                            <img src="<?php echo e($url); ?>" class="img-fluid rounded border" style="width: 32px; height: 32px;">
                        <?php else: ?>
                            <div class="text-muted p-3 border rounded bg-light">
                                <i class="fas fa-globe fa-2x mb-1"></i><br>Favicon
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i
                            class="fas fa-upload"></i> Alterar</button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger upload-remove <?php echo e($getUrl('favicon_image') ? '' : 'd-none'); ?>"><i
                            class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="form-group text-center">
                <label class="font-weight-bold">Logo Admin (Sidebar)</label>
                <div class="upload-box" data-remove-input="#remove_logo_admin"
                    data-existing-url="<?php echo e($getUrl('logo_admin')); ?>">
                    <input type="file" name="logo_admin" class="d-none" accept="image/*">
                    <input type="hidden" name="remove_logo_admin" id="remove_logo_admin" value="0">
                    <div class="upload-preview mb-2">
                        <?php if($url = $getUrl('logo_admin')): ?>
                            <img src="<?php echo e($url); ?>" class="img-fluid rounded border"
                                style="max-height: 80px; background: #343a40; padding: 5px;">
                        <?php else: ?>
                            <div class="text-muted p-3 border rounded bg-light">
                                <i class="fas fa-cogs fa-2x mb-1"></i><br>Logo Admin
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i
                            class="fas fa-upload"></i> Alterar</button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger upload-remove <?php echo e($getUrl('logo_admin') ? '' : 'd-none'); ?>"><i
                            class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="form-group text-center">
                <label class="font-weight-bold">Logo Login/Auth</label>
                <div class="upload-box" data-remove-input="#remove_logo_auth"
                    data-existing-url="<?php echo e($getUrl('logo_auth')); ?>">
                    <input type="file" name="logo_auth" class="d-none" accept="image/*">
                    <input type="hidden" name="remove_logo_auth" id="remove_logo_auth" value="0">
                    <div class="upload-preview mb-2">
                        <?php if($url = $getUrl('logo_auth')): ?>
                            <img src="<?php echo e($url); ?>" class="img-fluid rounded border" style="max-height: 80px;">
                        <?php else: ?>
                            <div class="text-muted p-3 border rounded bg-light">
                                <i class="fas fa-lock fa-2x mb-1"></i><br>Logo Auth
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i
                            class="fas fa-upload"></i> Alterar</button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger upload-remove <?php echo e($getUrl('logo_auth') ? '' : 'd-none'); ?>"><i
                            class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-laptop-code mr-2"></i> Backgrounds e Capas</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label class="font-weight-bold">Hero Image (Home)</label>
            <div class="upload-box" data-remove-input="#remove_hero_image"
                data-existing-url="<?php echo e($getUrl('hero_image')); ?>" style="height: auto;">
                <input type="file" name="hero_image" class="d-none" accept="image/*">
                <input type="hidden" name="remove_hero_image" id="remove_hero_image" value="0">
                <div class="upload-preview mb-2 text-center">
                    <?php if($url = $getUrl('hero_image')): ?>
                        <img src="<?php echo e($url); ?>" class="img-fluid rounded border"
                            style="max-height: 200px; width: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div class="text-muted p-5 border rounded bg-light">
                            <i class="fas fa-image fa-3x mb-2"></i><br>Banner Principal (1920x600)
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i
                            class="fas fa-upload"></i> Selecionar Imagem</button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger upload-remove <?php echo e($getUrl('hero_image') ? '' : 'd-none'); ?>"><i
                            class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>

        <div class="col-md-6 form-group">
            <label class="font-weight-bold">Background Geral do Site</label>
            <div class="upload-box" data-remove-input="#remove_site_bg_image"
                data-existing-url="<?php echo e($getUrl('site_bg_image')); ?>" style="height: auto;">
                <input type="file" name="site_bg_image" class="d-none" accept="image/*">
                <input type="hidden" name="remove_site_bg_image" id="remove_site_bg_image" value="0">
                <div class="upload-preview mb-2 text-center">
                    <?php if($url = $getUrl('site_bg_image')): ?>
                        <img src="<?php echo e($url); ?>" class="img-fluid rounded border"
                            style="max-height: 200px; width: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div class="text-muted p-5 border rounded bg-light">
                            <i class="fas fa-fill-drip fa-3x mb-2"></i><br>Fundo Padrão (Pattern ou Imagem)
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-primary upload-btn"><i
                            class="fas fa-upload"></i> Selecionar Imagem</button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger upload-remove <?php echo e($getUrl('site_bg_image') ? '' : 'd-none'); ?>"><i
                            class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\settings\partials\images.blade.php ENDPATH**/ ?>