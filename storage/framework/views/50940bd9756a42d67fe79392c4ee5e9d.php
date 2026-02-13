<?php
    $siteName = \App\Models\Setting::get('app_name') ?: config('app.name', 'UNN');
?>
<h2 style="margin: 0 0 20px 0; color: #1a1a1a; font-size: 22px;">Nova mensagem de contato</h2>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
    <tr>
        <td style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold; width: 120px;">Nome</td>
        <td style="padding: 8px 12px; background: #ffffff; border: 1px solid #e2e8f0;"><?php echo e($data['name']); ?></td>
    </tr>
    <tr>
        <td style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold;">E-mail</td>
        <td style="padding: 8px 12px; background: #ffffff; border: 1px solid #e2e8f0;">
            <a href="mailto:<?php echo e($data['email']); ?>" style="color: #1F5EDB;"><?php echo e($data['email']); ?></a>
        </td>
    </tr>
    <?php if(!empty($data['phone'])): ?>
    <tr>
        <td style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold;">Telefone</td>
        <td style="padding: 8px 12px; background: #ffffff; border: 1px solid #e2e8f0;"><?php echo e($data['phone']); ?></td>
    </tr>
    <?php endif; ?>
    <tr>
        <td style="padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; font-weight: bold;">Assunto</td>
        <td style="padding: 8px 12px; background: #ffffff; border: 1px solid #e2e8f0;"><?php echo e($data['subject']); ?></td>
    </tr>
</table>

<div style="margin-bottom: 20px;">
    <strong style="display: block; margin-bottom: 8px; color: #374151;">Mensagem:</strong>
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; line-height: 1.6;">
        <?php echo nl2br(e($data['message'])); ?>

    </div>
</div>

<div style="font-size: 11px; color: #9ca3af; padding-top: 16px; border-top: 1px solid #e5e7eb;">
    <strong>Informações técnicas:</strong><br>
    IP: <?php echo e($data['ip']); ?><br>
    User-Agent: <?php echo e($data['userAgent']); ?>

</div>

<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\emails\contact.blade.php ENDPATH**/ ?>