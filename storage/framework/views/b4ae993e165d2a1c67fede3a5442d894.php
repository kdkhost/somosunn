

<?php $__env->startSection('page_title','Permissões'); ?>
<?php $__env->startSection('breadcrumb'); ?><li class="breadcrumb-item active">Permissões</li><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0"><i class="fas fa-user-shield mr-2"></i>Papéis e permissões</h3>
    <a href="<?php echo e(route('admin.permissions.create')); ?>" class="btn btn-primary" data-pjax="true">Novo papel</a>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead><tr><th>Nome</th><th>Rótulo</th><th>Permissões</th><th style="width:140px" class="text-right">Ações</th></tr></thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <td><?php echo e($role->name); ?></td>
          <td><?php echo e($role->label); ?></td>
          <td>
            <?php $__currentLoopData = $role->permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <span class="badge badge-secondary mb-1"><?php echo e($p->name); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </td>
          <td class="text-right">
            <a href="<?php echo e(route('admin.permissions.edit',$role)); ?>" class="btn btn-sm btn-outline-secondary" data-pjax="true"><i class="fas fa-edit"></i></a>
            <button class="btn btn-sm btn-outline-danger btn-delete" data-action="<?php echo e(route('admin.permissions.destroy',$role)); ?>"><i class="fas fa-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="4" class="text-center text-muted">Nenhum papel cadastrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer"><?php echo e($roles->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/permissions/index.blade.php ENDPATH**/ ?>