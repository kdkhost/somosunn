<?php $__env->startSection('page_title', ($plan->id ? 'Editar' : 'Novo').' plano'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.plans.index')); ?>" data-pjax>Planos</a></li>
    <li class="breadcrumb-item active"><?php echo e($plan->id ? 'Editar' : 'Novo'); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <form class="ajax-form" method="POST" action="<?php echo e($plan->id ? route('admin.plans.update',$plan) : route('admin.plans.store')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <?php if($plan->id): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nome do pacote</label>
                    <input name="name" class="form-control" value="<?php echo e(old('name',$plan->name)); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Preço</label>
                    <input name="price" class="form-control mask-money" value="<?php echo e(old('price',$plan->price)); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Período</label>
                    <select name="period" class="form-control">
                        <?php $__currentLoopData = ['mensal','trimestral','semestral','anual','vitalício']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p); ?>" <?php echo e(old('period',$plan->period)==$p?'selected':''); ?>><?php echo e(ucfirst($p)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="highlight" value="0">
                        <input type="checkbox" class="custom-control-input" id="highlight" name="highlight" value="1" <?php echo e(old('highlight',$plan->highlight) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="highlight">Destacar (ribbon)</label>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="coupons_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="coupons_enabled" name="coupons_enabled" value="1" <?php echo e(old('coupons_enabled',$plan->coupons_enabled) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="coupons_enabled">Permitir cupons</label>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" <?php echo e(old('is_active',$plan->is_active ?? true) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="is_active">Plano ativo</label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Imagem do pacote</label>
                    <div class="upload-box" data-max-size="5242880" data-crop="1">
                        <input type="file" name="image" accept="image/*" class="d-none">
                        <div class="upload-preview mb-2"></div>
                        <div class="upload-meta text-muted"></div>
                        <small class="text-muted upload-help"></small>
                        <div class="progress progress-sm d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                        <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                    </div>
                    <?php if($plan->image): ?><div class="mt-2"><img src="<?php echo e(asset('storage/'.$plan->image)); ?>" class="img-thumbnail" width="120"></div><?php endif; ?>
                </div>
                <div class="form-group col-md-6">
                    <label>Benefícios (um por linha)</label>
                    <?php $benefits = old('benefits',$plan->benefits ?? []); ?>
                    <textarea name="benefits[]" class="form-control" rows="8" placeholder="Ex: Acesso ao portal&#10;Mentorias semanais&#10;Grupo VIP"><?php echo e(implode("\n", $benefits)); ?></textarea>
                    <small class="text-muted">Use uma linha por benefício. Permissões abaixo complementam o acesso.</small>
                </div>
            </div>

            <div class="form-group">
                <label>Permissões liberadas por este plano</label>
                <div class="row">
                    <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-4 col-lg-3">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="perm-<?php echo e($perm->id); ?>" name="permissions[]" value="<?php echo e($perm->id); ?>" <?php echo e(in_array($perm->id, old('permissions',$plan->permissions ?? [])) ? 'checked' : ''); ?>>
                                <label class="custom-control-label" for="perm-<?php echo e($perm->id); ?>"><?php echo e($perm->name); ?></label>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="text-right">
                <button class="btn btn-primary">Salvar</button>
                <a href="<?php echo e(route('admin.plans.index')); ?>" class="btn btn-secondary" data-pjax>Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/plans/form.blade.php ENDPATH**/ ?>