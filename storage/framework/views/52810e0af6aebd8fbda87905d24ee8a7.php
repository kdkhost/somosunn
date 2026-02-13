<?php $__env->startSection('title', 'Reserva - ' . $event->title); ?>

<?php $__env->startSection('content'); ?>
<?php
    $isDemo = ($event->is_demo ?? false) === true;
    $regularUnitPrice = (float) ($event->current_price ?? 0);
    $effectiveUnitPrice = (float) ($event->effective_price ?? $regularUnitPrice);
    $flashActive = method_exists($event, 'isFlashSaleActive') ? (bool) $event->isFlashSaleActive() : false;
    $isPaid = $effectiveUnitPrice > 0;
    $remaining = $event->remaining_seats;
    $alreadyConfirmed = $registration && in_array($registration->status, \App\Models\EventRegistration::COUNTED_STATUSES, true);
?>

<div class="min-h-screen bg-slate-50 pt-28 pb-20 px-4">
    <div class="max-w-5xl mx-auto">
        <a href="<?php echo e(route('events.show', $event)); ?>" class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-700 mb-6">
            <i class="fas fa-arrow-left"></i> Voltar para o evento
        </a>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-28">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Resumo</h3>
                    <p class="font-bold text-gray-900"><?php echo e($event->title); ?></p>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        <?php echo e(optional($event->start_at)->format('d/m/Y H:i')); ?>

                    </p>

                    <?php if($event->capacity): ?>
                        <div class="mt-4 text-sm">
                            <div class="flex items-center justify-between text-gray-600">
                                <span>Capacidade</span>
                                <span class="font-semibold text-gray-900"><?php echo e((int) $event->capacity); ?></span>
                            </div>
                            <div class="flex items-center justify-between text-gray-600 mt-1">
                                <span>Disponíveis</span>
                                <span class="font-semibold <?php echo e($remaining === 0 ? 'text-red-600' : 'text-gray-900'); ?>">
                                    <?php echo e($remaining); ?>

                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="border-t border-gray-100 mt-6 pt-6">
                        <?php if($isPaid): ?>
                            <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-2">
                                <?php echo e($event->current_batch_label); ?>

                            </span>
                            <p class="text-sm text-gray-500">Valor por pessoa</p>
                            <div class="flex items-end gap-3">
                                <p class="text-3xl font-black text-gray-900">
                                    <?php echo e('R$ ' . number_format($effectiveUnitPrice, 2, ',', '.')); ?>

                                </p>
                                <?php if($flashActive && $regularUnitPrice > 0 && $effectiveUnitPrice < $regularUnitPrice): ?>
                                    <p class="text-sm text-gray-400 line-through mb-1">
                                        <?php echo e('R$ ' . number_format($regularUnitPrice, 2, ',', '.')); ?>

                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php if($flashActive && $event->flash_sale_ends_at): ?>
                                <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-rose-50 border border-rose-200 px-3 py-1 text-xs font-black text-rose-800">
                                    <i class="fas fa-bolt"></i> Promoção relâmpago ativa
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-500">Entrada</p>
                            <p class="text-3xl font-black text-green-600">Gratuita</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <?php if(session('error')): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6">
                        <i class="fas fa-triangle-exclamation mr-2"></i><?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>
                <?php if(session('success')): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mb-6">
                        <i class="fas fa-circle-check mr-2"></i><?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <?php if($alreadyConfirmed): ?>
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h2 class="text-2xl font-black text-gray-900 mb-2">Vaga confirmada</h2>
                        <p class="text-gray-600 mb-6">Você já possui inscrição confirmada para este evento.</p>
                        <a href="<?php echo e(route('events.show', $event)); ?>" class="inline-flex items-center gap-2 btn-primary px-6 py-3 rounded-xl font-bold">
                            <i class="fas fa-ticket-alt"></i> Ver detalhes do evento
                        </a>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                        <h2 class="text-2xl font-black text-gray-900 mb-6">Finalizar Reserva</h2>

                        <?php if($isDemo): ?>
                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-xl mb-6">
                                <i class="fas fa-info-circle mr-2"></i> Evento de demonstração: configure um evento real no painel administrativo.
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo e(route('events.reserve', $event)); ?>" method="POST" class="space-y-6">
                            <?php echo csrf_field(); ?>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade</label>
                                    <input type="number" name="quantity" min="1" max="10" value="<?php echo e(old('quantity', 1)); ?>"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" <?php echo e($isDemo ? 'disabled' : ''); ?>>
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                                    <input type="text" value="<?php echo e($isPaid ? 'Pago' : 'Gratuito'); ?>" disabled
                                        class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-600">
                                </div>
                            </div>

                            <?php if($isPaid): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Cupom de desconto (opcional)</label>
                                    <input type="text" name="coupon_code" value="<?php echo e(old('coupon_code')); ?>" placeholder="Ex: BLACKFRIDAY26"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" <?php echo e($isDemo ? 'disabled' : ''); ?>>
                                    <p class="text-xs text-gray-500 mt-2">Se tiver um cupom, aplique aqui antes do pagamento.</p>
                                </div>
                            <?php endif; ?>

                            <?php if(auth()->guard()->guest()): ?>
                                <div class="border-t border-gray-100 pt-6">
                                    <h3 class="text-lg font-bold text-gray-900 mb-4">Seus dados</h3>

                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome completo</label>
                                            <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" <?php echo e($isDemo ? 'disabled' : ''); ?>>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                                            <input type="email" name="email" value="<?php echo e(old('email')); ?>" required
                                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" <?php echo e($isDemo ? 'disabled' : ''); ?>>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">CPF <?php echo e($isPaid ? '(obrigatório)' : '(opcional)'); ?></label>
                                            <input type="text" name="cpf" value="<?php echo e(old('cpf')); ?>" <?php echo e($isPaid ? 'required' : ''); ?> data-mask="999.999.999-99"
                                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" <?php echo e($isDemo ? 'disabled' : ''); ?>>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Telefone (opcional)</label>
                                            <input type="text" name="phone" value="<?php echo e(old('phone')); ?>" data-mask="(99) 99999-9999"
                                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" <?php echo e($isDemo ? 'disabled' : ''); ?>>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                                            <input type="password" name="password" id="password" required
                                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" <?php echo e($isDemo ? 'disabled' : ''); ?>>
                                            <p id="pw-strength" class="text-xs text-gray-500 mt-1">Força: <span>—</span></p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar senha</label>
                                            <input type="password" name="password_confirmation" required
                                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" <?php echo e($isDemo ? 'disabled' : ''); ?>>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if($event->capacity && $remaining === 0): ?>
                                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl">
                                    <i class="fas fa-ban mr-2"></i> Evento lotado no momento.
                                </div>
                            <?php endif; ?>

                            <button type="submit"
                                class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
                                <?php echo e(($isDemo || ($event->capacity && $remaining === 0)) ? 'disabled' : ''); ?>>
                                <i class="fas fa-ticket-alt"></i>
                                <?php echo e($isPaid ? 'Ir para pagamento' : 'Confirmar minha vaga'); ?>

                            </button>

                            <p class="text-xs text-gray-500 text-center">
                                Ao continuar, você concorda com os termos e políticas do evento.
                            </p>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\events\checkout.blade.php ENDPATH**/ ?>