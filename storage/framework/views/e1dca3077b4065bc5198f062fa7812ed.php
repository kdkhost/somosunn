<?php $__env->startSection('page_title','Depoimentos'); ?>
<?php $__env->startSection('breadcrumb'); ?><li class="breadcrumb-item active">Depoimentos</li><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap mb-3">
            <h3 class="m-0">Depoimentos (moderação)</h3>
        </div>

        <form method="GET" class="mb-3">
            <div class="form-row">
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="pending" <?php echo e(($status ?? '') === 'pending' ? 'selected' : ''); ?>>Pendentes</option>
                        <option value="approved" <?php echo e(($status ?? '') === 'approved' ? 'selected' : ''); ?>>Aprovados</option>
                        <option value="rejected" <?php echo e(($status ?? '') === 'rejected' ? 'selected' : ''); ?>>Recusados</option>
                    </select>
                </div>
                <div class="col-md-6 mb-2">
                    <input name="q" class="form-control" placeholder="Buscar por nome, título ou texto" value="<?php echo e($q ?? ''); ?>">
                </div>
                <div class="col-md-3 mb-2 text-right">
                    <button class="btn btn-primary btn-block">Filtrar</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Autor</th>
                        <th>Depoimento</th>
                        <th>Avaliação</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $status = $t->status;
                            $badge = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');
                        ?>
                        <tr>
                            <td>
                                <div class="font-weight-bold"><?php echo e($t->author_name ?: ($t->user->name ?? '—')); ?></div>
                                <div class="text-muted small"><?php echo e($t->author_title ?: '—'); ?></div>
                            </td>
                            <td class="text-muted">
                                <?php echo e(\Illuminate\Support\Str::limit($t->content, 140)); ?>

                                <?php if($t->moderation_notes): ?>
                                    <div class="small text-danger mt-1"><i class="fas fa-comment-dots mr-1"></i><?php echo e($t->moderation_notes); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($t->rating): ?>
                                    <span class="font-weight-bold"><?php echo e($t->rating); ?>/5</span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                                <?php if($t->is_featured): ?>
                                    <span class="badge badge-primary ml-2">Destaque</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo e($badge); ?>">
                                    <?php echo e($status === 'approved' ? 'Aprovado' : ($status === 'rejected' ? 'Recusado' : 'Pendente')); ?>

                                </span>
                            </td>
                            <td class="text-right">
                                <a href="<?php echo e(route('admin.testimonials.edit', $t)); ?>" class="btn btn-sm btn-secondary" data-pjax>Editar</a>

                                <?php if($t->status !== 'approved'): ?>
                                    <form method="POST" action="<?php echo e(route('admin.testimonials.approve', $t)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm btn-success">Aprovar</button>
                                    </form>
                                <?php endif; ?>

                                <?php if($t->status !== 'rejected'): ?>
                                    <button type="button" class="btn btn-sm btn-warning btn-reject-testimonial"
                                        data-action="<?php echo e(route('admin.testimonials.reject', $t)); ?>">
                                        Recusar
                                    </button>
                                <?php endif; ?>

                                <a href="#" class="btn btn-sm btn-danger btn-delete" data-action="<?php echo e(route('admin.testimonials.destroy', $t)); ?>">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Nenhum depoimento encontrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo e($testimonials->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    $(document).on('click', '.btn-reject-testimonial', function () {
        const action = $(this).data('action');
        Swal.fire({
            title: 'Recusar depoimento?',
            input: 'textarea',
            inputLabel: 'Motivo (opcional)',
            inputPlaceholder: 'Ex.: Linguagem agressiva, spam, fora do contexto...',
            showCancelButton: true,
            confirmButtonText: 'Recusar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f0ad4e'
        }).then((result) => {
            if (!result.isConfirmed) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = action;
            form.innerHTML = `
                <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="moderation_notes" value="${(result.value || '').replace(/\"/g,'&quot;')}">
            `;
            document.body.appendChild(form);
            form.submit();
        });
    });
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\testimonials\index.blade.php ENDPATH**/ ?>