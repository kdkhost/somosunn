<?php $__env->startSection('title', 'Pagamento - ' . ($event?->title ?? 'Evento')); ?>

<?php $__env->startSection('content'); ?>
<?php
    $isPaid = $order->status === 'paid';
?>

<div class="min-h-screen bg-slate-50 pt-28 pb-20 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center <?php echo e($isPaid ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'); ?>">
                    <i class="fas <?php echo e($isPaid ? 'fa-circle-check' : 'fa-hourglass-half'); ?> text-xl"></i>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-black text-gray-900">
                        <?php echo e($isPaid ? 'Pagamento aprovado' : 'Pagamento em processamento'); ?>

                    </h1>
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

            <?php if(!$isPaid): ?>
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-xl mt-6">
                    <i class="fas fa-info-circle mr-2"></i>
                    Seu pagamento ainda não foi confirmado. Esta página atualiza automaticamente.
                </div>

                <script>
                    setTimeout(() => window.location.reload(), 6000);
                </script>
            <?php else: ?>
                <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mt-6">
                    <i class="fas fa-ticket-alt mr-2"></i>
                    Sua vaga está confirmada. Guarde o número do pedido para referência.
                </div>
            <?php endif; ?>

            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <?php if($event): ?>
                    <a href="<?php echo e(route('events.show', $event)); ?>" class="btn-primary px-6 py-3 rounded-xl font-bold text-center">
                        Ver evento
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


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\events\payment\success.blade.php ENDPATH**/ ?>