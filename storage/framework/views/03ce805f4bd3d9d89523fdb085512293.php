<?php $__env->startSection('page_title','FAQ'); ?>
<?php $__env->startSection('breadcrumb'); ?><li class="breadcrumb-item active">FAQ</li><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap mb-3">
            <div>
                <h3 class="m-0">Perguntas Frequentes</h3>
                <div class="text-muted small">Controle o FAQ exibido no site (Premium/Contato/Geral).</div>
            </div>
            <a href="<?php echo e(route('admin.faqs.create')); ?>" class="btn btn-primary" data-pjax>Nova pergunta</a>
        </div>

        <form method="GET" action="<?php echo e(route('admin.faqs.index')); ?>" class="mb-3">
            <div class="form-row align-items-end">
                <div class="form-group col-md-3">
                    <label class="mb-1">Contexto</label>
                    <select name="context" class="form-control">
                        <option value="">Todos</option>
                        <?php $__currentLoopData = $contexts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php echo e($context === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label class="mb-1">Status</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="active" <?php echo e($status === 'active' ? 'selected' : ''); ?>>Ativo</option>
                        <option value="inactive" <?php echo e($status === 'inactive' ? 'selected' : ''); ?>>Inativo</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label class="mb-1">Buscar</label>
                    <input type="text" name="q" class="form-control" value="<?php echo e($q); ?>" placeholder="Pergunta ou resposta">
                </div>
                <div class="form-group col-md-2">
                    <button class="btn btn-secondary btn-block">Filtrar</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Pergunta</th>
                        <th>Contexto</th>
                        <th>Ordem</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="font-weight-bold"><?php echo e($faq->question); ?></div>
                                <div class="text-muted small"><?php echo e(\Illuminate\Support\Str::limit($faq->answer, 120)); ?></div>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo e($contexts[$faq->context] ?? $faq->context); ?>

                                </span>
                            </td>
                            <td class="text-muted"><?php echo e((int) $faq->sort_order); ?></td>
                            <td>
                                <span class="badge badge-<?php echo e($faq->is_active ? 'success' : 'secondary'); ?>">
                                    <?php echo e($faq->is_active ? 'Ativo' : 'Inativo'); ?>

                                </span>
                            </td>
                            <td class="text-right">
                                <a href="<?php echo e(route('admin.faqs.edit', $faq)); ?>" class="btn btn-sm btn-secondary" data-pjax>Editar</a>
                                <a href="#" class="btn btn-sm btn-danger btn-delete" data-action="<?php echo e(route('admin.faqs.destroy', $faq)); ?>">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhuma pergunta cadastrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo e($faqs->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\faqs\index.blade.php ENDPATH**/ ?>