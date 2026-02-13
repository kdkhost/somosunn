<?php $__env->startSection('title', 'Pagamento pendente'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-slate-50 pt-28 pb-20 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <div class="text-yellow-500 text-5xl mb-4"><i class="fas fa-hourglass-half"></i></div>
            <h1 class="text-2xl font-black text-gray-900 mb-2">Pagamento pendente</h1>
            <p class="text-gray-600 mb-6">Seu pagamento ainda está em processamento. Assim que for aprovado, o acesso ao conteúdo será liberado.</p>
            <a href="<?php echo e(route('marketplace.index')); ?>" class="inline-flex items-center gap-2 btn-primary px-6 py-3 rounded-xl font-bold">
                <i class="fas fa-store"></i> Ir para o marketplace
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\checkout\pending.blade.php ENDPATH**/ ?>