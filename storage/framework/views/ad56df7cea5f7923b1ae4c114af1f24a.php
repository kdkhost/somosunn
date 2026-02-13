
<?php $__env->startSection('title', 'Vendas'); ?>
<?php $__env->startSection('page_title', 'Gerenciamento de Vendas'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Vendas</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Listagem de Pedidos</h3>
                <div class="card-tools">
                    <form action="<?php echo e(route('admin.orders.index')); ?>" method="GET" class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" name="search" class="form-control float-right" placeholder="Buscar por cliente/ID" value="<?php echo e(request('search')); ?>">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Valor Total</th>
                            <th>Gateway</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>#<?php echo e($order->id); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php
                                        $photo = trim((string) optional($order->user)->photo);
                                        $avatarUrl = $photo !== ''
                                            ? ((str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) ? $photo : asset($photo))
                                            : asset('img/default-user.svg');
                                    ?>
                                    <img src="<?php echo e($avatarUrl); ?>" class="img-circle elevation-1 mr-2" style="width:30px;height:30px;object-fit:cover" onerror="this.onerror=null;this.src='<?php echo e(asset('img/default-user.svg')); ?>';">
                                    <?php echo e($order->user->name ?? 'Usuário Removido'); ?>

                                </div>
                            </td>
                            <td class="font-weight-bold">R$ <?php echo e(number_format($order->total_amount, 2, ',', '.')); ?></td>
                            <td>
                                <?php if($order->gateway == 'mercadopago'): ?> <span class="badge badge-info mb-0">MercadoPago</span>
                                <?php elseif($order->gateway == 'pagseguro'): ?> <span class="badge badge-success mb-0">PagSeguro</span>
                                <?php else: ?> <span class="badge badge-secondary mb-0"><?php echo e($order->gateway); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($order->status == 'paid'): ?> <span class="badge badge-pill badge-success">Pago</span>
                                <?php elseif($order->status == 'pending'): ?> <span class="badge badge-pill badge-warning">Pendente</span>
                                <?php elseif($order->status == 'refunded'): ?> <span class="badge badge-pill badge-danger">Reembolsado</span>
                                <?php else: ?> <span class="badge badge-pill badge-secondary"><?php echo e($order->status); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td>
                            <td class="text-right">
                                <a href="<?php echo e(route('admin.orders.show', $order->id)); ?>" class="btn btn-sm btn-primary" title="Ver Detalhes">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhuma venda encontrada.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                <?php echo e($orders->links('pagination::bootstrap-4')); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\orders\index.blade.php ENDPATH**/ ?>