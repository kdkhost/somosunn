<?php $__env->startSection('title', 'Marketplace'); ?>
<?php $__env->startSection('page_title', 'Marketplace'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $paidTotal = (float) ($paidTotal ?? 0);
        $platformFeeTotal = (float) ($platformFeeTotal ?? 0);
        $netTotal = (float) ($netTotal ?? 0);
        $paidCount = (int) ($paidCount ?? 0);
        $pendingCount = (int) ($pendingCount ?? 0);
        $paymentsConfigured = (bool) ($paymentsConfigured ?? false);
        $platformFeePercent = (float) ($platformFeePercent ?? 0);
    ?>

    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><?php echo e($paidCount); ?></h3>
                    <p>Vendas pagas</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <a href="<?php echo e(route('admin.marketplace.sales')); ?>" class="small-box-footer">Ver vendas <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>R$ <?php echo e(number_format($netTotal, 2, ',', '.')); ?></h3>
                    <p>Total líquido (pagos)</p>
                    <div class="text-white-50" style="font-size: 0.9rem;">
                        Bruto: R$ <?php echo e(number_format($paidTotal, 2, ',', '.')); ?><br>
                        Comissão da plataforma<?php echo e($platformFeePercent > 0 ? (' (' . rtrim(rtrim(number_format($platformFeePercent, 2, '.', ''), '0'), '.') . '%)') : ''); ?>:
                        R$ <?php echo e(number_format($platformFeeTotal, 2, ',', '.')); ?>

                    </div>
                </div>
                <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
                <a href="<?php echo e(route('admin.marketplace.sales')); ?>" class="small-box-footer">Ver detalhes <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><?php echo e($pendingCount); ?></h3>
                    <p>Pedidos pendentes</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                <a href="<?php echo e(route('admin.marketplace.sales')); ?>" class="small-box-footer">Acompanhar <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-store mr-2"></i>Configurações do vendedor</h3>
        </div>
        <div class="card-body">
            <?php if($paymentsConfigured): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i> Pagamentos configurados e habilitados na plataforma.
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Pagamento indisponível: o MercadoPago ainda não foi configurado na plataforma.
                </div>
            <?php endif; ?>

            <a href="<?php echo e(route('admin.marketplace.payments')); ?>" class="btn btn-primary">
                <i class="fas fa-credit-card mr-1"></i> Ver pagamentos
            </a>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\marketplace\index.blade.php ENDPATH**/ ?>