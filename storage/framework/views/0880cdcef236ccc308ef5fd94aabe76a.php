<?php $__env->startSection('page_title','FAQ'); ?>
<?php $__env->startSection('breadcrumb'); ?><li class="breadcrumb-item active">FAQ</li><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-body">
            <div class="alert alert-danger mb-0">
                <div class="font-weight-bold mb-2">FAQ indisponível</div>
                <div><?php echo e($message ?? 'Seu banco de dados está desatualizado.'); ?></div>
                <div class="mt-2">
                    <code class="user-select-all">php artisan migrate --force</code>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\faqs\unavailable.blade.php ENDPATH**/ ?>