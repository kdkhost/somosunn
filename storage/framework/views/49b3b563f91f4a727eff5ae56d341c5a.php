<?php
    $company = app(\App\Services\InvoiceService::class)->companyInfo();
?>

<?php $__env->startSection('page_title', 'Fatura ' . ($invoice->number ?: ('#' . $invoice->id))); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.invoices.index')); ?>" data-pjax>Faturas</a></li>
    <li class="breadcrumb-item active"><?php echo e($invoice->number ?: ('#' . $invoice->id)); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <?php
                        $badge = match ($invoice->status) {
                            'paid' => 'success',
                            'draft' => 'secondary',
                            'cancelled' => 'danger',
                            default => 'info',
                        };
                        $label = match ($invoice->status) {
                            'paid' => 'Paga',
                            'draft' => 'Rascunho',
                            'cancelled' => 'Cancelada',
                            default => 'Emitida',
                        };
                    ?>

                    <div class="text-center mb-4">
                        <?php if($company['logo_url']): ?>
                            <img src="<?php echo e($company['logo_url']); ?>" alt="Logo" class="img-fluid mb-2" style="max-height: 50px;">
                        <?php else: ?>
                            <h3 class="text-primary font-weight-bold"><?php echo e($company['name']); ?></h3>
                        <?php endif; ?>
                        <h4 class="mb-2"><?php echo e($invoice->number ?: ('#' . $invoice->id)); ?></h4>
                        <span class="badge badge-<?php echo e($badge); ?>"><?php echo e($label); ?></span>
                    </div>

                    <hr>

                    <div class="small text-muted">Cliente</div>
                    <div class="font-weight-bold"><?php echo e($invoice->user?->name ?? '—'); ?></div>
                    <div class="text-muted"><?php echo e($invoice->user?->email ?? ''); ?></div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Subtotal</span>
                        <span>R$ <?php echo e(number_format((float) $invoice->subtotal, 2, ',', '.')); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Desconto</span>
                        <span>R$ <?php echo e(number_format((float) $invoice->discount_amount, 2, ',', '.')); ?></span>
                    </div>
                    <div class="d-flex justify-content-between font-weight-bold">
                        <span>Total</span>
                        <span>R$ <?php echo e(number_format((float) $invoice->total_amount, 2, ',', '.')); ?></span>
                    </div>

                    <hr>

                    <div class="text-muted small">Emissão</div>
                    <div><?php echo e($invoice->issued_at?->format('d/m/Y H:i') ?? $invoice->created_at?->format('d/m/Y H:i')); ?></div>

                    <?php if($invoice->due_at): ?>
                        <div class="text-muted small mt-2">Vencimento</div>
                        <div><?php echo e($invoice->due_at->format('d/m/Y H:i')); ?></div>
                    <?php endif; ?>

                    <?php if($invoice->email_sent_at): ?>
                        <div class="text-muted small mt-2">E-mail</div>
                        <div>Enviado em <?php echo e($invoice->email_sent_at->format('d/m/Y H:i')); ?></div>
                    <?php elseif($invoice->email_queued_at): ?>
                        <div class="text-muted small mt-2">E-mail</div>
                        <div>Enfileirado em <?php echo e($invoice->email_queued_at->format('d/m/Y H:i')); ?></div>
                    <?php endif; ?>

                    <hr>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?php echo e(route('admin.invoices.edit', $invoice)); ?>" class="btn btn-sm btn-secondary" data-pjax>
                            <i class="fas fa-edit mr-1"></i> Editar
                        </a>
                        <a href="<?php echo e(route('admin.invoices.pdf', $invoice)); ?>" class="btn btn-sm btn-outline-primary"
                            target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i> PDF
                        </a>
                        <form method="POST" action="<?php echo e(route('admin.invoices.send', $invoice)); ?>" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="force" value="1">
                            <button class="btn btn-sm btn-outline-success" type="submit">
                                <i class="fas fa-paper-plane mr-1"></i> Enviar e-mail
                            </button>
                        </form>
                        <a href="#" class="btn btn-sm btn-danger btn-delete"
                            data-action="<?php echo e(route('admin.invoices.destroy', $invoice)); ?>"
                            data-redirect="<?php echo e(route('admin.invoices.index')); ?>">
                            <i class="fas fa-trash mr-1"></i> Excluir
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Itens</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Descrição</th>
                                <th class="text-right">Qtd</th>
                                <th class="text-right">Valor</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $invoice->items->sortBy('sort_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <div class="font-weight-bold"><?php echo e($item->description); ?></div>
                                        <?php if($item->item_type): ?>
                                            <div class="text-muted small">Tipo: <?php echo e($item->item_type); ?><?php if($item->item_id): ?>
                                            #<?php echo e($item->item_id); ?><?php endif; ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right"><?php echo e((int) $item->quantity); ?></td>
                                    <td class="text-right">R$ <?php echo e(number_format((float) $item->unit_price, 2, ',', '.')); ?></td>
                                    <td class="text-right">R$ <?php echo e(number_format((float) $item->total_price, 2, ',', '.')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Nenhum item.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if(!empty($invoice->notes)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Observações</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-muted"><?php echo nl2br(e($invoice->notes)); ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\invoices\show.blade.php ENDPATH**/ ?>