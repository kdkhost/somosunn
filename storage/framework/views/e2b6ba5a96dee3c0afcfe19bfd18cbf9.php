<?php $__env->startSection('content'); ?>
    <h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">
        Parabéns, <?php echo e($user->name); ?>!
    </h2>

    <p style="margin: 0 0 14px 0;">
        Você concluiu <?php echo e($itemTypeLabel); ?> <strong><?php echo e($itemTitle); ?></strong> com sucesso.
    </p>

    <p style="margin: 0 0 22px 0;">
        Seu certificado oficial já está disponível. Você pode baixá-lo clicando no botão abaixo ou acessando sua área de
        membros.
    </p>

    <?php
        $buttonColor = $primaryColor ?? '#1F5EDB';
    ?>

    <p style="text-align: center; margin: 24px 0 26px 0;">
        <a href="<?php echo e($url); ?>"
            style="display: inline-block; background-color: <?php echo e($buttonColor); ?>; color: #ffffff; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 700;">
            Baixar Certificado
        </a>
    </p>

    <p style="margin: 0;">
        Obrigado,<br>
        <?php echo e($siteName ?? config('app.name')); ?>

    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.system', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\emails\certificates\issued.blade.php ENDPATH**/ ?>