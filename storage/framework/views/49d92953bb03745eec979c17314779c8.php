

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between mb-3">
        <h3>Regras de Pontuação</h3>
        <a href="<?php echo e(route('admin.points-rules.create')); ?>" class="btn btn-primary">Nova Regra</a>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Key</th>
                <th>Rótulo</th>
                <th>Pontos</th>
                <th>Ativa</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($r->key); ?></td>
                <td><?php echo e($r->label); ?></td>
                <td><?php echo e($r->points); ?></td>
                <td><?php echo e($r->active ? 'Sim' : 'Não'); ?></td>
                <td>
                    <a href="<?php echo e(route('admin.points-rules.edit', $r)); ?>" class="btn btn-sm btn-secondary">Editar</a>
                    <form action="<?php echo e(route('admin.points-rules.destroy', $r)); ?>" method="POST" style="display:inline">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Remover?')">Remover</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <?php echo e($rules->links()); ?>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/points/index.blade.php ENDPATH**/ ?>