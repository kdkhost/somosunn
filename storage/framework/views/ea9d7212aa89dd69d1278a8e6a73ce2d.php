

<?php $__env->startSection('page_title', $user->exists ? 'Editar usuário' : 'Novo usuário'); ?>
<?php $__env->startSection('breadcrumb'); ?>
<li class="breadcrumb-item"><a href="<?php echo e(route('admin.users.index')); ?>">Usuários</a></li>
<li class="breadcrumb-item active"><?php echo e($user->exists ? 'Editar' : 'Novo'); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
  <div class="card-body">
    <form method="POST" action="<?php echo e($user->exists ? route('admin.users.update',$user) : route('admin.users.store')); ?>" class="ajax-form">
        <?php echo csrf_field(); ?>
        <?php if($user->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="form-group mb-3"><label>Nome</label><input name="name" class="form-control" value="<?php echo e(old('name',$user->name)); ?>" required></div>
        <div class="form-group mb-3"><label>E-mail</label><input name="email" type="email" class="form-control" value="<?php echo e(old('email',$user->email)); ?>" required></div>
        <div class="form-row">
            <div class="form-group col-md-6"><label>Senha <?php if($user->exists): ?><small class="text-muted">(deixe em branco para não alterar)</small><?php endif; ?></label><input name="password" type="password" class="form-control"></div>
            <div class="form-group col-md-3"><label>Papel</label><input name="role" class="form-control" value="<?php echo e(old('role',$user->role)); ?>" placeholder="admin / user / superadmin"></div>
            <div class="form-group col-md-3"><label>Nível</label><input name="level" class="form-control" value="<?php echo e(old('level',$user->level)); ?>" placeholder="sucesso / iniciante"></div>
        </div>
        <button class="btn btn-primary">Salvar</button>
        <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-secondary" data-pjax="true">Voltar</a>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/users/form.blade.php ENDPATH**/ ?>