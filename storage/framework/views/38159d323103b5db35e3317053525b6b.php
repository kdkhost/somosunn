<?php $__env->startSection('content'); ?>
    <div class="bg-slate-50 min-h-screen pt-24 pb-10">
        <div class="max-w-7xl mx-auto px-4 md:px-10 lg:px-16">
            <div class="flex flex-col lg:flex-row gap-6">
                <aside class="w-full lg:w-80">
                    <?php echo $__env->make('panel.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </aside>
                <main class="flex-1">
                    <?php echo $__env->yieldContent('panel_content'); ?>
                </main>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\panel\layouts\app.blade.php ENDPATH**/ ?>