

<?php $__env->startSection('page_title', $course->exists ? 'Editar Curso' : 'Novo Curso'); ?>

<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
    <form method="POST" action="<?php echo e($course->exists ? route('admin.courses.update',$course) : route('admin.courses.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php if($course->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="form-group mb-2"><label>Título</label><input name="title" class="form-control" value="<?php echo e(old('title',$course->title)); ?>" required></div>
        <div class="form-group mb-2"><label>Descrição</label><textarea name="description" class="form-control summernote"><?php echo e(old('description',$course->description)); ?></textarea></div>
        <div class="form-group mb-2"><label>Preço</label><input name="price" class="form-control" value="<?php echo e(old('price',$course->price)); ?>"></div>
        <div class="form-check mb-2"><input type="checkbox" name="published" value="1" class="form-check-input" <?php echo e($course->published ? 'checked' : ''); ?>><label class="form-check-label">Publicado</label></div>
        <button class="btn btn-primary">Salvar</button>
    </form>
</div></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/courses/form.blade.php ENDPATH**/ ?>