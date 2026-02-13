<?php
    $company = $company ?? [];
    $number = $invoice->number ?: ('#' . $invoice->id);
    $amount = 'R$ ' . number_format((float) $invoice->total_amount, 2, ',', '.');
    $issuedAt = $invoice->issued_at ? $invoice->issued_at->format('d/m/Y') : now()->format('d/m/Y');
?>

<?php $__env->startSection('content'); ?>
    <h2 style="margin: 0 0 14px 0; font-size: 22px; line-height: 1.2; color: #111827;">
        Fatura <?php echo e($number); ?>

    </h2>

    <p style="margin: 0 0 14px 0;">
        Olá, <strong><?php echo e(explode(' ', (string) ($invoice->user->name ?? 'Cliente'))[0]); ?></strong>.
    </p>

    <p style="margin: 0 0 22px 0;">
        Segue em anexo o PDF da sua fatura para conferência e registro.
    </p>

    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px 16px; border-radius: 10px; margin: 0 0 22px 0;">
        <p style="margin: 0 0 6px 0;"><strong>Número:</strong> <?php echo e($number); ?></p>
        <p style="margin: 0 0 6px 0;"><strong>Data:</strong> <?php echo e($issuedAt); ?></p>
        <p style="margin: 0;"><strong>Valor:</strong> <?php echo e($amount); ?></p>
    </div>

    <p style="margin: 0;">
        Obrigado,<br>
        <?php echo e($company['name'] ?? ($siteName ?? config('app.name'))); ?>

    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('emails.layouts.system', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\emails\invoice.blade.php ENDPATH**/ ?>