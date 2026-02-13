

<?php $__env->startSection('title', 'Sem Conexão - UNN'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-[60vh] flex flex-col items-center justify-center p-6 text-center">
    <div class="w-24 h-24 mb-6 rounded-full bg-slate-100 flex items-center justify-center">
        <i class="fas fa-wifi text-4xl text-slate-400"></i>
    </div>
    <h1 class="text-2xl font-bold text-slate-800 mb-2">Você está offline</h1>
    <p class="text-slate-500 max-w-sm mx-auto mb-8">
        Parece que você perdeu sua conexão com a internet. Verifique seu sinal e tente novamente.
    </p>
    <button onclick="window.location.reload()" class="btn-primary px-8 py-3 rounded-xl font-semibold shadow-lg">
        Tentar Novamente
    </button>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\offline.blade.php ENDPATH**/ ?>