<?php if(session('error')): ?>
    <div class="max-w-3xl mx-auto bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-8">
        <i class="fas fa-triangle-exclamation mr-2"></i><?php echo e(session('error')); ?>

    </div>
<?php endif; ?>
<?php if(session('success')): ?>
    <div class="max-w-3xl mx-auto bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mb-8">
        <i class="fas fa-circle-check mr-2"></i><?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\site\institucional\partials\contact-alerts.blade.php ENDPATH**/ ?>