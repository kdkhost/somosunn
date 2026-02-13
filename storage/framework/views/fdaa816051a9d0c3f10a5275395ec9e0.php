

<?php $__env->startSection('page_title','Ranking'); ?>

<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
    <h4>Top Usuários</h4>
    <table class="table table-striped">
        <thead><tr><th>Pos</th><th>Nome</th><th>Pontos</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $top; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr><td><?php echo e($i+1); ?></td><td><?php echo e($u->name); ?></td><td><?php echo e($u->points); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\ranking\index.blade.php ENDPATH**/ ?>