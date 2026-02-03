

<?php $__env->startSection('page_title','Mentorias'); ?>

<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
    <a href="<?php echo e(route('admin.mentorships.create')); ?>" class="btn btn-primary mb-3">Nova mentoria</a>
    <table class="table table-striped">
        <thead><tr><th>#</th><th>Título</th><th>Mentor</th><th>Preço</th><th>Ações</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($i->id); ?></td>
                    <td><?php echo e($i->title); ?></td>
                    <td><?php echo e($i->mentor?->name); ?></td>
                    <td><?php echo e(number_format($i->price,2,',','.')); ?></td>
                    <td><a href="<?php echo e(route('admin.mentorships.edit',$i)); ?>" class="btn btn-sm btn-info">Editar</a></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php echo e($items->links()); ?>

</div></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/mentorships/index.blade.php ENDPATH**/ ?>