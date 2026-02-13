<?php $__env->startSection('page_title','Cupons'); ?>
<?php $__env->startSection('breadcrumb'); ?><li class="breadcrumb-item active">Cupons</li><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap mb-3">
            <h3 class="m-0">Cupons de desconto</h3>
            <a href="<?php echo e(route('admin.coupons.create')); ?>" class="btn btn-primary" data-pjax>Novo cupom</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Escopo</th>
                        <th>Validade</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $coupons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coupon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-weight-bold"><?php echo e($coupon->code); ?></td>
                            <td><?php echo e($coupon->discount_type === 'percent' ? 'Percentual' : 'Fixo'); ?></td>
                            <td>
                                <?php if($coupon->discount_type === 'percent'): ?>
                                    <?php echo e(rtrim(rtrim(number_format($coupon->discount_value, 2, ',', '.'), '0'), ',')); ?>%
                                <?php else: ?>
                                    R$ <?php echo e(number_format($coupon->discount_value, 2, ',', '.')); ?>

                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $scopeLabel = match($coupon->applies_to) {
                                        'event' => 'Evento',
                                        'course' => 'Curso',
                                        'mentorship' => 'Mentoria',
                                        default => 'Geral',
                                    };
                                ?>
                                <?php echo e($scopeLabel); ?><?php if($coupon->applies_to_id): ?> #<?php echo e($coupon->applies_to_id); ?><?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?php
                                    $from = $coupon->starts_at ? $coupon->starts_at->format('d/m/Y H:i') : null;
                                    $to = $coupon->ends_at ? $coupon->ends_at->format('d/m/Y H:i') : null;
                                ?>
                                <?php if($from || $to): ?>
                                    <?php echo e($from ? 'De '.$from : ''); ?><?php echo e(($from && $to) ? ' • ' : ''); ?><?php echo e($to ? 'Até '.$to : ''); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo e($coupon->is_active ? 'success' : 'secondary'); ?>">
                                    <?php echo e($coupon->is_active ? 'Ativo' : 'Inativo'); ?>

                                </span>
                            </td>
                            <td class="text-right">
                                <a href="<?php echo e(route('admin.coupons.edit', $coupon)); ?>" class="btn btn-sm btn-secondary" data-pjax>Editar</a>
                                <a href="#" class="btn btn-sm btn-danger btn-delete" data-action="<?php echo e(route('admin.coupons.destroy', $coupon)); ?>">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Nenhum cupom cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo e($coupons->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\coupons\index.blade.php ENDPATH**/ ?>