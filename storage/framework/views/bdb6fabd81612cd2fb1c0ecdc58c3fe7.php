<?php $__env->startSection('title', 'Minhas Vendas - Marketplace'); ?>
<?php $__env->startSection('page_title', 'Minhas Vendas - Marketplace'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $orders = $orders ?? null;
        $paidTotal = (float) ($paidTotal ?? 0);
        $platformFeeTotal = (float) ($platformFeeTotal ?? 0);
        $netTotal = (float) ($netTotal ?? 0);
        $paidCount = (int) ($paidCount ?? 0);
    ?>

    <div class="row">
        <div class="col-md-4">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Vendas pagas</span>
                    <span class="info-box-number"><?php echo e($paidCount); ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total líquido (pagos)</span>
                    <span class="info-box-number">R$ <?php echo e(number_format($netTotal, 2, ',', '.')); ?></span>
                    <span class="progress-description">
                        Bruto: R$ <?php echo e(number_format($paidTotal, 2, ',', '.')); ?> | Comissão: R$ <?php echo e(number_format($platformFeeTotal, 2, ',', '.')); ?>

                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-receipt mr-2"></i>Pedidos</h3>
            <div class="card-tools">
                <a href="<?php echo e(route('admin.marketplace.index')); ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-store mr-1"></i> Voltar
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 90px;">Pedido</th>
                            <th>Comprador</th>
                            <th>Itens</th>
                            <th style="width: 140px;">Total</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 170px;">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = ($orders ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $items = $order->items ?? collect();
                                $itemsLabel = $items->pluck('title')->filter()->take(3)->join(', ');
                                $itemsCount = $items->count();
                                if ($itemsCount > 3) {
                                    $itemsLabel .= '…';
                                }

                                $status = (string) ($order->status ?? '');
                                $statusLabel = match ($status) {
                                    'paid' => 'Pago',
                                    'pending' => 'Pendente',
                                    'failed' => 'Falhou',
                                    'refunded' => 'Reembolsado',
                                    default => $status ?: '—',
                                };
                                $statusClass = match ($status) {
                                    'paid' => 'badge-success',
                                    'pending' => 'badge-warning',
                                    'failed' => 'badge-danger',
                                    'refunded' => 'badge-secondary',
                                    default => 'badge-secondary',
                                };
                            ?>
                            <tr>
                                <td class="font-weight-bold">#<?php echo e($order->id); ?></td>
                                <td>
                                    <div class="font-weight-bold"><?php echo e($order->user->name ?? '—'); ?></div>
                                    <div class="text-muted small"><?php echo e($order->user->email ?? ''); ?></div>
                                </td>
                                <td><?php echo e($itemsLabel !== '' ? $itemsLabel : '—'); ?></td>
                                <td class="font-weight-bold">R$ <?php echo e(number_format((float) ($order->total_amount ?? 0), 2, ',', '.')); ?></td>
                                <td><span class="badge <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span></td>
                                <td class="text-muted"><?php echo e(optional($order->created_at)->format('d/m/Y H:i')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted p-4">Nenhuma venda encontrada.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if(method_exists($orders, 'links')): ?>
            <div class="card-footer">
                <?php echo e($orders->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\marketplace\sales.blade.php ENDPATH**/ ?>