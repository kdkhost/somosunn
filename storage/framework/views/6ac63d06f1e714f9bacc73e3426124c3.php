

<?php $__env->startSection('page_title','Cursos'); ?>

<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
    <a href="<?php echo e(route('admin.courses.create')); ?>" class="btn btn-primary mb-3">Novo curso</a>
    <table class="table table-striped">
        <thead><tr><th>#</th><th>Título</th><th>Preço</th><th>Publicado</th><th>Ações</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($c->id); ?></td>
                    <td><?php echo e($c->title); ?></td>
                    <td><?php echo e(number_format($c->price,2,',','.')); ?></td>
                    <td><?php echo e($c->published ? 'Sim':'Não'); ?></td>
                    <td><a href="<?php echo e(route('admin.courses.edit',$c)); ?>" class="btn btn-sm btn-info">Editar</a>
                        <form method="POST" action="<?php echo e(route('admin.courses.destroy',$c)); ?>" style="display:inline"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-sm btn-danger">Excluir</button></form></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php echo e($courses->links()); ?>

</div></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/courses/index.blade.php ENDPATH**/ ?>