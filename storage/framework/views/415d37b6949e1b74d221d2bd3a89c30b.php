

<?php $__env->startSection('title', 'Checkout - UNN'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <h1 class="text-2xl font-bold mb-4">Finalizar Compra</h1>
            <div class="bg-white p-6 rounded-lg shadow mb-4">
                <h3 class="font-bold text-lg mb-2">Resumo do Pedido</h3>
                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between py-2 border-b">
                        <span><?php echo e($item->title); ?></span>
                        <span>R$ <?php echo e(number_format($item->price, 2, ',', '.')); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if(data_get($order->metadata, 'coupon.code') && data_get($order->metadata, 'coupon.discount_amount')): ?>
                    <div class="flex justify-between py-2 text-sm text-green-700">
                        <span>Cupom <?php echo e(data_get($order->metadata, 'coupon.code')); ?></span>
                        <span>- R$ <?php echo e(number_format((float) data_get($order->metadata, 'coupon.discount_amount'), 2, ',', '.')); ?></span>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between py-2 font-bold text-xl mt-2">
                    <span>Total</span>
                    <span>R$ <?php echo e(number_format($order->total_amount, 2, ',', '.')); ?></span>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                Pagamento processado via MercadoPago de forma segura.
            </div>
        </div>

        <div>
            <div id="cardPaymentBrick_container"></div>
        </div>
    </div>
</div>

<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
    const mp = new MercadoPago('<?php echo e($publicKey); ?>', {
        locale: 'pt-BR'
    });
    const bricksBuilder = mp.bricks();
    const renderPaymentBrick = async (bricksBuilder) => {
        const settings = {
            initialization: {
                amount: <?php echo e($order->total_amount); ?>,
                preferenceId: '<?php echo e($preferenceId); ?>',
            },
            customization: {
                paymentMethods: {
                    ticket: "all",
                    bankTransfer: "all", // PIX
                    creditCard: "all",
                    debitCard: "all",
                    mercadoPago: "all",
                },
                visual: {
                    style: {
                        theme: 'bootstrap',
                    }
                }
            },
            callbacks: {
                onReady: () => {
                   // loaded
                },
                onSubmit: ({ selectedPaymentMethod, formData }) => {
                    // processed by preference, usually callbacks handles redirect
                },
                onError: (error) => {
                    console.error(error);
                },
            },
        };
        window.paymentBrickController = await bricksBuilder.create(
            'payment',
            'cardPaymentBrick_container',
            settings
        );
    };
    renderPaymentBrick(bricksBuilder);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\checkout\transparent.blade.php ENDPATH**/ ?>