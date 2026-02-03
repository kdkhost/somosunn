

<?php $__env->startSection('page_title', $event->exists ? 'Editar Evento' : 'Novo Evento'); ?>

<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
    <form method="POST" action="<?php echo e($event->exists ? route('admin.events.update',$event) : route('admin.events.store')); ?>">
        <?php echo csrf_field(); ?>
        <?php if($event->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="form-group mb-2"><label>Título</label><input name="title" class="form-control" value="<?php echo e(old('title',$event->title)); ?>" required></div>
        <div class="form-group mb-2"><label>Início</label><input name="start_at" type="datetime-local" class="form-control" value="<?php echo e(old('start_at',$event->start_at)); ?>"></div>
        <div class="form-group mb-2"><label>Preço</label><input name="price" class="form-control" value="<?php echo e(old('price',$event->price)); ?>"></div>
        <div class="form-check mb-2"><input type="checkbox" name="published" value="1" class="form-check-input" <?php echo e($event->published ? 'checked' : ''); ?>><label class="form-check-label">Publicado</label></div>
        <button class="btn btn-primary">Salvar</button>
    </form>
</div></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/events/form.blade.php ENDPATH**/ ?>