

<?php $__env->startSection('page_title','Gerar Certificado'); ?>

<?php $__env->startSection('content'); ?>
<div class="card"><div class="card-body">
    <form method="POST" action="<?php echo e(route('admin.certificates.generate')); ?>">
        <?php echo csrf_field(); ?>
        <div class="form-group mb-2"><label>Usuário</label><select name="user_id" class="form-control"><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?> (<?php echo e($u->email); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div class="form-group mb-2"><label>Curso</label><select name="course_id" class="form-control"><?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->title); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <button class="btn btn-primary">Gerar PDF</button>
    </form>
</div></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/certificates/form.blade.php ENDPATH**/ ?>