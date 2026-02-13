<?php $__env->startSection('title', 'Pagamento não concluído - ' . ($event?->title ?? 'Evento')); ?>

<?php $__env->startSection('content'); ?>
<?php
    $retryUrl = data_get($order->metadata, 'mercadopago_init_point') ?? data_get($order->metadata, 'mercadopago_sandbox_init_point');
?>

<div class="min-h-screen bg-slate-50 pt-28 pb-20 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-red-100 text-red-700">
                    <i class="fas fa-circle-xmark text-xl"></i>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-black text-gray-900">Pagamento não concluído</h1>
                    <p class="text-gray-600 mt-1">
                        Pedido #<?php echo e($order->id); ?> • Status: <span class="font-semibold"><?php echo e($order->status); ?></span>
                    </p>
                </div>
            </div>

            <?php if($event): ?>
                <div class="border-t border-gray-100 mt-6 pt-6">
                    <p class="font-bold text-gray-900"><?php echo e($event->title); ?></p>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-calendar-alt mr-1"></i> <?php echo e(optional($event->start_at)->format('d/m/Y H:i')); ?>

                    </p>
                </div>
            <?php endif; ?>

            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mt-6">
                <i class="fas fa-triangle-exclamation mr-2"></i>
                O provedor informou falha ou cancelamento. Você pode tentar novamente.
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <?php if($retryUrl): ?>
                    <a href="<?php echo e($retryUrl); ?>" class="btn-primary px-6 py-3 rounded-xl font-bold text-center">
                        Tentar novamente
                    </a>
                <?php endif; ?>
                <?php if($event): ?>
                    <a href="<?php echo e(route('events.checkout', $event)); ?>" class="px-6 py-3 rounded-xl font-bold text-center border border-gray-200 text-gray-700 hover:bg-gray-50">
                        Voltar ao checkout
                    </a>
                <?php endif; ?>
                <a href="<?php echo e(route('events.index')); ?>" class="px-6 py-3 rounded-xl font-bold text-center border border-gray-200 text-gray-700 hover:bg-gray-50">
                    Ver outros eventos
                </a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\events\payment\failure.blade.php ENDPATH**/ ?>