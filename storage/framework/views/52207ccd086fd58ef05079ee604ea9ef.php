

<?php $__env->startSection('page_title', $mentorship->exists ? 'Editar Mentoria' : 'Nova Mentoria'); ?>

<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
    <form method="POST" action="<?php echo e($mentorship->exists ? route('admin.mentorships.update',$mentorship) : route('admin.mentorships.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php if($mentorship->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="form-group mb-2"><label>Título</label><input name="title" class="form-control" value="<?php echo e(old('title',$mentorship->title)); ?>" required></div>
        <div class="form-group mb-2"><label>Mentor (ID)</label><input name="mentor_id" class="form-control" value="<?php echo e(old('mentor_id',$mentorship->mentor_id)); ?>"></div>
        <div class="form-group mb-2"><label>Preço</label><input name="price" class="form-control" value="<?php echo e(old('price',$mentorship->price)); ?>"></div>
        <div class="form-group mb-2"><label>Vagas</label><input name="slots" class="form-control" value="<?php echo e(old('slots',$mentorship->slots)); ?>"></div>
        <button class="btn btn-primary">Salvar</button>
    </form>
</div></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/mentorships/form.blade.php ENDPATH**/ ?>