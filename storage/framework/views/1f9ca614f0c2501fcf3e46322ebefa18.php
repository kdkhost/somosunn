<?php $__env->startSection('page_title','Faturas'); ?>
<?php $__env->startSection('breadcrumb'); ?><li class="breadcrumb-item active">Faturas</li><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap mb-3">
            <h3 class="m-0">Faturas (PDF)</h3>
            <a href="<?php echo e(route('admin.invoices.create')); ?>" class="btn btn-primary" data-pjax>Nova fatura</a>
        </div>

        <form method="GET" class="mb-3">
            <div class="input-group">
                <input name="q" class="form-control" placeholder="Buscar por número, nome ou e-mail" value="<?php echo e($q ?? ''); ?>">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary">Buscar</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Pedido</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Emissão</th>
                        <th>E-mail</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-weight-bold"><?php echo e($inv->number ?: ('#'.$inv->id)); ?></td>
                            <td>
                                <div class="font-weight-bold"><?php echo e($inv->user?->name ?? '—'); ?></div>
                                <div class="text-muted small"><?php echo e($inv->user?->email ?? ''); ?></div>
                            </td>
                            <td class="text-muted">
                                <?php if($inv->order_id): ?>
                                    #<?php echo e($inv->order_id); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $badge = match($inv->status){
                                        'paid' => 'success',
                                        'draft' => 'secondary',
                                        'cancelled' => 'danger',
                                        default => 'info',
                                    };
                                    $label = match($inv->status){
                                        'paid' => 'Paga',
                                        'draft' => 'Rascunho',
                                        'cancelled' => 'Cancelada',
                                        default => 'Emitida',
                                    };
                                ?>
                                <span class="badge badge-<?php echo e($badge); ?>"><?php echo e($label); ?></span>
                            </td>
                            <td>R$ <?php echo e(number_format((float) $inv->total_amount, 2, ',', '.')); ?></td>
                            <td class="text-muted small">
                                <?php echo e($inv->issued_at ? $inv->issued_at->format('d/m/Y H:i') : ($inv->created_at?->format('d/m/Y H:i') ?? '—')); ?>

                            </td>
                            <td class="text-muted small">
                                <?php if($inv->email_sent_at): ?>
                                    Enviado em <?php echo e($inv->email_sent_at->format('d/m/Y H:i')); ?>

                                <?php elseif($inv->email_queued_at): ?>
                                    Enfileirado em <?php echo e($inv->email_queued_at->format('d/m/Y H:i')); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <a href="<?php echo e(route('admin.invoices.show', $inv)); ?>" class="btn btn-sm btn-secondary" data-pjax>Ver</a>
                                <a href="<?php echo e(route('admin.invoices.pdf', $inv)); ?>" class="btn btn-sm btn-outline-primary" target="_blank">PDF</a>
                                <form method="POST" action="<?php echo e(route('admin.invoices.send', $inv)); ?>" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="force" value="1">
                                    <button class="btn btn-sm btn-outline-success" type="submit">Enviar</button>
                                </form>
                                <a href="#" class="btn btn-sm btn-danger btn-delete" data-action="<?php echo e(route('admin.invoices.destroy', $inv)); ?>">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma fatura encontrada.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo e($invoices->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\invoices\index.blade.php ENDPATH**/ ?>