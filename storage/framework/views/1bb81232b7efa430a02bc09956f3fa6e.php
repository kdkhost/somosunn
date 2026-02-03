

<?php $__env->startSection('page_title','Eventos'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Eventos</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <a href="<?php echo e(route('admin.events.create')); ?>" class="btn btn-primary mb-3">Novo evento</a>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Início</th>
                    <th>Preço</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($e->id); ?></td>
                        <td><?php echo e($e->title); ?></td>
                        <td><?php echo e($e->start_at); ?></td>
                        <td><?php echo e(number_format($e->price,2,',','.')); ?></td>
                        <td>
                            <a href="<?php echo e(route('admin.events.edit',$e)); ?>" class="btn btn-sm btn-info mr-1">Editar</a>
                            <a href="<?php echo e(route('admin.events.destroy',$e)); ?>" class="btn btn-sm btn-danger btn-delete">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php echo e($events->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/events/index.blade.php ENDPATH**/ ?>