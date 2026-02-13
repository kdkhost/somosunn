
<?php $__env->startSection('title', 'Detalhes do Pedido'); ?>
<?php $__env->startSection('page_title', 'Detalhes do Pedido #' . $order->id); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.orders.index')); ?>">Vendas</a></li>
    <li class="breadcrumb-item active">Pedido #<?php echo e($order->id); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <?php
                        $photo = trim((string) ($order->user->photo ?? ''));
                        $avatarUrl = $photo !== ''
                            ? ((str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) ? $photo : asset($photo))
                            : asset('img/default-user.svg');
                    ?>
                    <img class="profile-user-img img-fluid img-circle" src="<?php echo e($avatarUrl); ?>" alt="User profile picture" onerror="this.onerror=null;this.src='<?php echo e(asset('img/default-user.svg')); ?>';">
                </div>
                <h3 class="profile-username text-center"><?php echo e($order->user->name); ?></h3>
                <p class="text-muted text-center"><?php echo e($order->user->email); ?></p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Status</b> <a class="float-right">
                            <?php if($order->status == 'paid'): ?> <span class="badge badge-success">Pago</span>
                            <?php elseif($order->status == 'pending'): ?> <span class="badge badge-warning">Pendente</span>
                            <?php elseif($order->status == 'refunded'): ?> <span class="badge badge-danger">Reembolsado</span>
                            <?php else: ?> <span class="badge badge-secondary"><?php echo e($order->status); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="list-group-item">
                        <b>Data</b> <a class="float-right"><?php echo e($order->created_at->format('d/m/Y H:i')); ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Total</b> <a class="float-right">R$ <?php echo e(number_format($order->total_amount, 2, ',', '.')); ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Gateway</b> <a class="float-right"><?php echo e(ucfirst($order->gateway)); ?></a>
                    </li>
                    <?php if($order->transaction_id): ?>
                    <li class="list-group-item">
                        <b>Transação ID</b> <a class="float-right text-monospace text-xs"><?php echo e(Str::limit($order->transaction_id, 20)); ?></a>
                    </li>
                    <?php endif; ?>
                </ul>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Fatura</strong>
                        <?php if($order->invoice): ?>
                            <span class="badge badge-info"><?php echo e($order->invoice->number ?: ('#'.$order->invoice->id)); ?></span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Não emitida</span>
                        <?php endif; ?>
                    </div>

                    <?php if($order->invoice): ?>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="<?php echo e(route('admin.invoices.show', $order->invoice)); ?>" class="btn btn-sm btn-secondary" data-pjax>
                                <i class="fas fa-eye mr-1"></i> Ver
                            </a>
                            <a href="<?php echo e(route('admin.invoices.pdf', $order->invoice)); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                            </a>
                            <form method="POST" action="<?php echo e(route('admin.invoices.send', $order->invoice)); ?>" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="force" value="1">
                                <button class="btn btn-sm btn-outline-success" type="submit">
                                    <i class="fas fa-paper-plane mr-1"></i> Enviar e-mail
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="<?php echo e(route('admin.orders.invoice', $order)); ?>">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-file-invoice mr-1"></i> Emitir e enviar fatura
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if($order->status === 'paid'): ?>
                <form action="<?php echo e(route('admin.orders.refund', $order->id)); ?>" method="POST" class="d-grid gap-2">
                    <?php echo csrf_field(); ?>
                    <button type="button" class="btn btn-danger btn-block btn-delete" data-confirm-delete="true">
                        <i class="fas fa-undo mr-1"></i> Reembolsar Pedido
                    </button>
                    <small class="text-muted text-center mt-2 d-block">Esta ação estornará o pagamento no gateway.</small>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Itens do Pedido</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Tipo</th>
                            <th>Preço</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($item->title); ?></td>
                            <td><?php echo e($item->item_type); ?></td>
                            <td>R$ <?php echo e(number_format($item->price, 2, ',', '.')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($order->refunded_at): ?>
        <div class="alert alert-danger mt-3">
            <h5><i class="icon fas fa-ban"></i> Pedido Reembolsado!</h5>
            Este pedido foi reembolsado em <?php echo e(\Carbon\Carbon::parse($order->refunded_at)->format('d/m/Y H:i')); ?>.
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\orders\show.blade.php ENDPATH**/ ?>