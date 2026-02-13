<?php $__env->startSection('page_title', ($invoice->id ? 'Editar' : 'Nova') . ' fatura'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.invoices.index')); ?>" data-pjax>Faturas</a></li>
    <li class="breadcrumb-item active"><?php echo e($invoice->id ? 'Editar' : 'Nova'); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <form method="POST"
            action="<?php echo e($invoice->id ? route('admin.invoices.update', $invoice) : route('admin.invoices.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php if($invoice->id): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Cliente</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">Selecione...</option>
                        <?php $__currentLoopData = ($users ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($u->id); ?>" <?php echo e((string) old('user_id', $invoice->user_id) === (string) $u->id ? 'selected' : ''); ?>>
                                #<?php echo e($u->id); ?> — <?php echo e($u->name); ?> (<?php echo e($u->email); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <small class="text-muted">Se o usuário não estiver na lista, abra a tela de usuários e crie/edite o
                        cadastro primeiro.</small>
                </div>

                <div class="form-group col-md-3">
                    <label>Status</label>
                    <?php ($status = old('status', $invoice->status ?: 'issued')); ?>
                    <select name="status" class="form-control" required>
                        <option value="draft" <?php echo e($status === 'draft' ? 'selected' : ''); ?>>Rascunho</option>
                        <option value="issued" <?php echo e($status === 'issued' ? 'selected' : ''); ?>>Emitida</option>
                        <option value="paid" <?php echo e($status === 'paid' ? 'selected' : ''); ?>>Paga</option>
                        <option value="cancelled" <?php echo e($status === 'cancelled' ? 'selected' : ''); ?>>Cancelada</option>
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label>Moeda</label>
                    <input name="currency" class="form-control"
                        value="<?php echo e(old('currency', $invoice->currency ?: 'BRL')); ?>" placeholder="BRL">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Emissão</label>
                    <input name="issued_at" class="form-control" data-datetime-picker
                        value="<?php echo e(old('issued_at', optional($invoice->issued_at)->format('Y-m-d H:i'))); ?>"
                        placeholder="AAAA-MM-DD HH:MM" autocomplete="off">
                </div>
                <div class="form-group col-md-3">
                    <label>Vencimento (opcional)</label>
                    <input name="due_at" class="form-control" data-datetime-picker
                        value="<?php echo e(old('due_at', optional($invoice->due_at)->format('Y-m-d H:i'))); ?>"
                        placeholder="AAAA-MM-DD HH:MM" autocomplete="off">
                </div>
                <div class="form-group col-md-3">
                    <label>Desconto (opcional)</label>
                    <input name="discount_amount" class="form-control mask-money"
                        value="<?php echo e(old('discount_amount', $invoice->discount_amount)); ?>" placeholder="R$ 0,00">
                </div>
                <div class="form-group col-md-3 d-flex align-items-end">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mb-2">
                        <input type="hidden" name="send_email" value="0">
                        <input type="checkbox" class="custom-control-input" id="send_email" name="send_email" value="1"
                            <?php echo e(old('send_email') ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="send_email">Enviar por e-mail ao salvar</label>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="m-0">Itens</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddItem">
                    <i class="fas fa-plus mr-1"></i> Adicionar item
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th style="width:90px;">Qtd</th>
                            <th style="width:180px;">Valor</th>
                            <th style="width:70px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        

                        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <input name="items_description[]" class="form-control" value="<?php echo e($r['description']); ?>"
                                        required>
                                </td>
                                <td>
                                    <input name="items_quantity[]" class="form-control" type="number" min="1"
                                        value="<?php echo e($r['quantity'] ?? 1); ?>">
                                </td>
                                <td>
                                    <input name="items_unit_price[]" class="form-control mask-money"
                                        value="<?php echo e($r['unit_price']); ?>" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btnRemoveItem"
                                        title="Remover">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="form-group">
                <label>Observações (opcional)</label>
                <textarea name="notes" class="form-control" rows="4"
                    maxlength="5000"><?php echo e(old('notes', $invoice->notes)); ?></textarea>
            </div>

            <div class="text-right">
                <button class="btn btn-primary">Salvar</button>
                <a href="<?php echo e(route('admin.invoices.index')); ?>" class="btn btn-secondary" data-pjax>Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        (function () {
            function bindRemoveButtons(scope) {
                (scope || document).querySelectorAll('.btnRemoveItem').forEach(function (btn) {
                    if (btn.dataset.bound) return;
                    btn.dataset.bound = '1';
                    btn.addEventListener('click', function () {
                        const tr = btn.closest('tr');
                        const tbody = tr && tr.parentElement;
                        if (!tr || !tbody) return;
                        if (tbody.querySelectorAll('tr').length <= 1) {
                            // Mantém pelo menos 1 linha
                            tr.querySelectorAll('input').forEach(i => i.value = '');
                            const qty = tr.querySelector('input[name="items_quantity[]"]');
                            if (qty) qty.value = 1;
                            return;
                        }
                        tr.remove();
                    });
                });
            }

            function addRow() {
                const tbody = document.querySelector('#itemsTable tbody');
                if (!tbody) return;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><input name="items_description[]" class="form-control" required></td>
                    <td><input name="items_quantity[]" class="form-control" type="number" min="1" value="1"></td>
                    <td><input name="items_unit_price[]" class="form-control mask-money" required></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btnRemoveItem" title="Remover">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);

                // Reaplica máscaras e binds (admin layout já expõe initMask / initDateTimePickers via document ready)
                try {
                    if (window.jQuery && jQuery.fn && jQuery.fn.inputmask) {
                        jQuery(tr).find('.mask-money').inputmask('currency', {
                            prefix: 'R$ ',
                            radixPoint: ',',
                            groupSeparator: '.',
                            autoGroup: true,
                            digits: 2,
                            rightAlign: false,
                            substituteRadixPoint: true
                        });
                    }
                } catch (e) { }

                bindRemoveButtons(tr);
            }

            const btn = document.getElementById('btnAddItem');
            if (btn) btn.addEventListener('click', addRow);

            bindRemoveButtons(document);
        })();
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\invoices\form.blade.php ENDPATH**/ ?>