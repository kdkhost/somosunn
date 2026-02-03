

<?php $__env->startSection('page_title','Usuários'); ?>
<?php $__env->startSection('breadcrumb'); ?><li class="breadcrumb-item active">Usuários</li><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0"><i class="fas fa-users-cog mr-2"></i>Gerenciar usuários</h3>
    <a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-primary" data-pjax="true">Novo</a>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead><tr><th>Nome</th><th>E-mail</th><th>Papel</th><th>Nível</th><th class="text-right" style="width:140px;">Ações</th></tr></thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <td><?php echo e($user->name); ?></td>
          <td><?php echo e($user->email); ?></td>
          <td><?php echo e($user->role ?? '-'); ?></td>
          <td><?php echo e($user->level ?? '-'); ?></td>
          <td class="text-right">
            <a href="<?php echo e(route('admin.users.edit',$user)); ?>" class="btn btn-sm btn-outline-secondary" data-pjax="true"><i class="fas fa-edit"></i></a>
            <button class="btn btn-sm btn-outline-danger btn-delete" data-action="<?php echo e(route('admin.users.destroy',$user)); ?>"><i class="fas fa-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="5" class="text-center text-muted">Nenhum usuário cadastrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer"><?php echo e($users->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/users/index.blade.php ENDPATH**/ ?>