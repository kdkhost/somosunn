<?php $__env->startSection('content'); ?>
    <h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">
        Pagamento confirmado!
    </h2>

    <p style="margin: 0 0 14px 0;">
        Olá, <strong><?php echo e($order->user->name); ?></strong>.
    </p>

    <p style="margin: 0 0 22px 0;">
        Recebemos a confirmação do seu pagamento. Seu acesso já está liberado.
    </p>

    <?php
        $planTitle = optional($order->items->first())->title ?: 'Plano';
        $buttonColor = $primaryColor ?? '#1F5EDB';
        $portalUrl = route('portal');
    ?>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 10px; margin: 0 0 22px 0;">
        <p style="margin: 0 0 6px 0;"><strong>Pedido:</strong> #<?php echo e($order->id); ?></p>
        <p style="margin: 0 0 6px 0;"><strong>Plano:</strong> <?php echo e($planTitle); ?></p>
        <p style="margin: 0;"><strong>Valor:</strong> R$ <?php echo e(number_format((float) $order->total_amount, 2, ',', '.')); ?></p>
    </div>

    <p style="text-align: center; margin: 24px 0 26px 0;">
        <a href="<?php echo e($portalUrl); ?>"
            style="display: inline-block; background-color: <?php echo e($buttonColor); ?>; color: #ffffff; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 700;">
            Acessar Portal
        </a>
    </p>

    <p style="margin: 0;">
        Obrigado,<br>
        <?php echo e($siteName ?? config('app.name')); ?>

    </p>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('emails.layouts.system', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\emails\payment_confirmed.blade.php ENDPATH**/ ?>