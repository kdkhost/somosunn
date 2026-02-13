<?php $__env->startSection('page_title', 'Avaliações'); ?>
<?php $__env->startSection('breadcrumb'); ?><li class="breadcrumb-item active">Avaliações</li><?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap mb-3">
                <h3 class="m-0">Avaliações (cursos e mentorias)</h3>
            </div>

            <form method="GET" class="mb-3">
                <div class="form-row">
                    <div class="col-md-3 mb-2">
                        <select name="status" class="form-control">
                            <option value="">Todos os status</option>
                            <option value="pending" <?php echo e(($status ?? '') === 'pending' ? 'selected' : ''); ?>>Pendentes</option>
                            <option value="approved" <?php echo e(($status ?? '') === 'approved' ? 'selected' : ''); ?>>Aprovadas</option>
                            <option value="rejected" <?php echo e(($status ?? '') === 'rejected' ? 'selected' : ''); ?>>Recusadas</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="type" class="form-control">
                            <option value="">Todos os tipos</option>
                            <option value="course" <?php echo e(($type ?? '') === 'course' ? 'selected' : ''); ?>>Cursos</option>
                            <option value="mentorship" <?php echo e(($type ?? '') === 'mentorship' ? 'selected' : ''); ?>>Mentorias</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <input name="q" class="form-control" value="<?php echo e($q ?? ''); ?>"
                            placeholder="Buscar por item, usuário ou comentário">
                    </div>
                    <div class="col-md-2 mb-2">
                        <button class="btn btn-primary btn-block">Filtrar</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Usuário</th>
                            <th>Avaliação</th>
                            <th>Comentário</th>
                            <th>Status</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $reviewType = $review->reviewable_type === \App\Models\Course::class ? 'Curso' : 'Mentoria';
                                $reviewTitle = $review->reviewable->title ?? 'Item removido';
                                $statusName = $review->status === 'approved' ? 'Aprovada' : ($review->status === 'rejected' ? 'Recusada' : 'Pendente');
                                $statusClass = $review->status === 'approved' ? 'success' : ($review->status === 'rejected' ? 'danger' : 'warning');
                            ?>
                            <tr>
                                <td>
                                    <div class="font-weight-bold"><?php echo e($reviewTitle); ?></div>
                                    <span class="badge badge-light"><?php echo e($reviewType); ?></span>
                                </td>
                                <td>
                                    <div class="font-weight-bold"><?php echo e($review->user->name ?? 'Usuário removido'); ?></div>
                                    <div class="text-muted small"><?php echo e($review->user->email ?? '-'); ?></div>
                                </td>
                                <td>
                                    <div class="font-weight-bold"><?php echo e($review->rating); ?>/5</div>
                                    <div class="text-warning" style="letter-spacing: 1px;">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="<?php echo e($i <= $review->rating ? 'fas' : 'far'); ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td class="text-muted">
                                    <?php echo e(\Illuminate\Support\Str::limit($review->comment, 140)); ?>

                                    <?php if($review->moderation_notes): ?>
                                        <div class="small text-danger mt-1">
                                            <i class="fas fa-comment-dots mr-1"></i><?php echo e($review->moderation_notes); ?>

                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo e($statusClass); ?>"><?php echo e($statusName); ?></span>
                                    <?php if($review->moderator): ?>
                                        <div class="text-muted small mt-1">por <?php echo e($review->moderator->name); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <?php if($review->status !== 'approved'): ?>
                                        <form method="POST" action="<?php echo e(route('admin.reviews.approve', $review)); ?>" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-sm btn-success">Aprovar</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if($review->status !== 'rejected'): ?>
                                        <button type="button" class="btn btn-sm btn-warning btn-reject-review"
                                            data-action="<?php echo e(route('admin.reviews.reject', $review)); ?>">
                                            Recusar
                                        </button>
                                    <?php endif; ?>

                                    <a href="#" class="btn btn-sm btn-danger btn-delete"
                                        data-action="<?php echo e(route('admin.reviews.destroy', $review)); ?>">
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhuma avaliação encontrada.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php echo e($items->links()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).on('click', '.btn-reject-review', function () {
            const action = $(this).data('action');
            Swal.fire({
                title: 'Recusar avaliação?',
                input: 'textarea',
                inputLabel: 'Motivo (opcional)',
                inputPlaceholder: 'Ex.: comentário fora de contexto...',
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

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\reviews\index.blade.php ENDPATH**/ ?>