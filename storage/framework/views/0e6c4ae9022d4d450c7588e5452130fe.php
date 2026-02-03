

<?php $__env->startSection('title','404 — Página não encontrada'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-6xl font-bold">404</h1>
        <p class="text-xl mt-4">Ops — Página não encontrada.</p>
        <a href="<?php echo e(route('home')); ?>" class="btn-primary text-white px-4 py-2 rounded mt-6 inline-block">Voltar ao site</a>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/errors/404.blade.php ENDPATH**/ ?>