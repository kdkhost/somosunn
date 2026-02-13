<?php $__env->startSection('page_title', $role->exists ? 'Editar papel' : 'Novo papel'); ?>
<?php $__env->startSection('breadcrumb'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('admin.permissions.index')); ?>">Permissões</a></li>
<li class="breadcrumb-item active"><?php echo e($role->exists ? 'Editar' : 'Novo'); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
  <div class="card-body">
    <form method="POST" action="<?php echo e($role->exists ? route('admin.permissions.update',$role) : route('admin.permissions.store')); ?>" class="ajax-form">
        <?php echo csrf_field(); ?>
        <?php if($role->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Nome (slug)</label>
                    <input name="name" class="form-control" value="<?php echo e(old('name',$role->name)); ?>" required placeholder="ex: editor">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Rótulo</label>
                    <input name="label" class="form-control" value="<?php echo e(old('label',$role->label)); ?>" placeholder="ex: Editor de conteúdo">
                </div>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="d-flex justify-content-between align-items-center">
                <span>Permissões</span>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Marcar todas</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">Desmarcar todas</button>
                </div>
            </label>

            <?php
                $categoryColors = [
                    // Nomes de categoria quando migration foi rodada
                    'Dashboard' => 'primary',
                    'Usuários' => 'info',
                    'Cursos' => 'success',
                    'Mentorias' => 'warning',
                    'Eventos' => 'danger',
                    'Planos' => 'secondary',
                    'Vendas' => 'dark',
                    'Faturas' => 'primary',
                    'Cupons' => 'info',
                    'Certificados' => 'success',
                    'Pontuação' => 'warning',
                    'Comunidade' => 'danger',
                    'E-mails' => 'secondary',
                    'Depoimentos' => 'dark',
                    'FAQ' => 'primary',
                    'Uploads' => 'info',
                    'Pagamentos' => 'success',
                    'Relatórios' => 'warning',
                    'Configurações' => 'danger',
                    'Fontes' => 'secondary',
                    'Permissões' => 'dark',
                    'Outros' => 'light',
                    // Prefixos quando migration não foi rodada (fallback)
                    'dashboard' => 'primary',
                    'users' => 'info',
                    'courses' => 'success',
                    'mentorships' => 'warning',
                    'events' => 'danger',
                    'plans' => 'secondary',
                    'orders' => 'dark',
                    'invoices' => 'primary',
                    'coupons' => 'info',
                    'certificates' => 'success',
                    'points' => 'warning',
                    'ranking' => 'warning',
                    'community' => 'danger',
                    'mailtemplates' => 'secondary',
                    'mail' => 'secondary',
                    'testimonials' => 'dark',
                    'faq' => 'primary',
                    'uploads' => 'info',
                    'gateways' => 'success',
                    'reports' => 'warning',
                    'settings' => 'danger',
                    'fonts' => 'secondary',
                    'permissions' => 'dark',
                    'roles' => 'dark',
                ];
            ?>

            <?php $__currentLoopData = $permissionsGrouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $permissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $color = $categoryColors[$category] ?? 'secondary'; ?>
                <div class="card mb-3 border-<?php echo e($color); ?>">
                    <div class="card-header bg-<?php echo e($color); ?> <?php echo e(in_array($color, ['warning', 'light']) ? 'text-dark' : 'text-white'); ?> py-2 d-flex justify-content-between align-items-center">
                        <strong><i class="fas fa-folder mr-2"></i><?php echo e($category); ?></strong>
                        <div>
                            <button type="button" class="btn btn-sm btn-light selectCategory" data-category="<?php echo e($category); ?>">Marcar</button>
                            <button type="button" class="btn btn-sm btn-outline-light deselectCategory" data-category="<?php echo e($category); ?>">Desmarcar</button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <div class="row">
                            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4 col-lg-3 mb-2">
                                    <label class="mb-0 d-flex align-items-start" title="<?php echo e($p->label); ?>">
                                        <input type="checkbox" name="permissions[]" value="<?php echo e($p->id); ?>" 
                                            class="mr-2 mt-1 perm-checkbox" data-category="<?php echo e($category); ?>"
                                            <?php echo e($role->permissions->contains($p->id) ? 'checked' : ''); ?>>
                                        <span>
                                            <code class="text-<?php echo e($color); ?>"><?php echo e($p->name); ?></code>
                                            <small class="d-block text-muted"><?php echo e($p->label); ?></small>
                                        </span>
                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <button class="btn btn-primary"><i class="fas fa-save mr-2"></i>Salvar</button>
        <a href="<?php echo e(route('admin.permissions.index')); ?>" class="btn btn-secondary" data-pjax="true">Voltar</a>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('selectAll').addEventListener('click', function() {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
    });
    document.getElementById('deselectAll').addEventListener('click', function() {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
    });
    document.querySelectorAll('.selectCategory').forEach(btn => {
        btn.addEventListener('click', function() {
            const cat = this.dataset.category;
            document.querySelectorAll('.perm-checkbox[data-category="'+cat+'"]').forEach(cb => cb.checked = true);
        });
    });
    document.querySelectorAll('.deselectCategory').forEach(btn => {
        btn.addEventListener('click', function() {
            const cat = this.dataset.category;
            document.querySelectorAll('.perm-checkbox[data-category="'+cat+'"]').forEach(cb => cb.checked = false);
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\permissions\form.blade.php ENDPATH**/ ?>