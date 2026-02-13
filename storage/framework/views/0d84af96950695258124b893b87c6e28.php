<?php $__env->startSection('title', 'Checkout - ' . ($mentorship->title ?? 'Mentoria')); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-slate-50 pt-28 pb-20 px-4">
    <div class="max-w-4xl mx-auto">
        <a href="<?php echo e(route('mentorships.show', $mentorship)); ?>" class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-700 mb-6">
            <i class="fas fa-arrow-left"></i> Voltar para a mentoria
        </a>

        <?php if(session('error')): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6">
                <i class="fas fa-triangle-exclamation mr-2"></i><?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="md:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-28">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Resumo</h3>
                    <p class="font-bold text-gray-900"><?php echo e($mentorship->title); ?></p>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-user-circle mr-1"></i>
                        <?php echo e(optional($mentorship->mentor)->name ?? 'Mentor'); ?>

                    </p>

                    <div class="border-t border-gray-100 mt-6 pt-6">
                        <?php
                            $regularTotal = (float) ($mentorship->price ?? 0);
                            $effectiveTotal = (float) ($mentorship->effective_price ?? $regularTotal);
                            $flashActive = method_exists($mentorship, 'isFlashSaleActive') ? (bool) $mentorship->isFlashSaleActive() : false;
                        ?>
                        <p class="text-sm text-gray-500">Total</p>
                        <div class="flex items-end gap-3">
                            <p class="text-3xl font-black text-gray-900">
                                <?php echo e($effectiveTotal > 0 ? 'R$ ' . number_format($effectiveTotal, 2, ',', '.') : 'Gratuito'); ?>

                            </p>
                            <?php if($flashActive && $regularTotal > 0 && $effectiveTotal < $regularTotal): ?>
                                <p class="text-sm text-gray-400 line-through mb-1">
                                    <?php echo e('R$ ' . number_format($regularTotal, 2, ',', '.')); ?>

                                </p>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Pagamento via MercadoPago.</p>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                    <h2 class="text-2xl font-black text-gray-900 mb-6">Finalizar compra</h2>

                    <form action="<?php echo e(route('mentorships.checkout.process', $mentorship)); ?>" method="POST" class="space-y-6">
                        <?php echo csrf_field(); ?>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cupom de desconto (opcional)</label>
                            <input type="text" name="coupon_code" value="<?php echo e(old('coupon_code')); ?>" placeholder="Ex: BLACKFRIDAY26"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-2">Se tiver um cupom, aplique antes de continuar.</p>
                        </div>

                        <button type="submit"
                            class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2">
                            <i class="fas fa-lock"></i> Continuar para pagamento
                        </button>

                        <p class="text-xs text-gray-500 text-center">
                            Ao continuar, você concorda com os termos de compra e políticas da mentoria.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\checkout\mentorship.blade.php ENDPATH**/ ?>