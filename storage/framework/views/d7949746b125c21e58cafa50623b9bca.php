

<?php $__env->startSection('page_title', 'Regras de Pontuação'); ?>
<?php $__env->startSection('breadcrumb_items'); ?>
    <li class="breadcrumb-item active">Pontuação</li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        /* Esconder colunas em mobile */
        @media (max-width: 767px) {
            .hide-mobile {
                display: none !important;
            }

            .points-table td,
            .points-table th {
                padding: 0.5rem 0.4rem;
                font-size: 0.85rem;
            }

            .points-table .btn-sm {
                padding: 0.2rem 0.4rem;
            }
        }

        /* Badge de categoria compacto */
        .category-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.8rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .category-badge i {
            margin-right: 0.4rem;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-star mr-2"></i>Regras de Pontuação</h3>
            <div class="card-tools">
                <a href="<?php echo e(route('admin.points-rules.create')); ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i><span class="d-none d-sm-inline">Nova Regra</span><span
                        class="d-sm-none">Novo</span>
                </a>
            </div>
        </div>
        <div class="card-body py-3">
            <p class="text-muted mb-3 small">
                <i class="fas fa-info-circle mr-1"></i>
                Configure as ações que concedem pontos aos usuários.
            </p>

            
            <div class="d-flex flex-wrap gap-2" style="gap: 0.5rem;">
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $categoryRules = $rulesGrouped->get($key, collect());
                        $count = $categoryRules->count();
                    ?>
                    <span
                        class="category-badge bg-<?php echo e($cat['color']); ?> <?php echo e(in_array($cat['color'], ['warning', 'light']) ? 'text-dark' : 'text-white'); ?>">
                        <i class="<?php echo e($cat['icon']); ?>"></i>
                        <span class="d-none d-sm-inline"><?php echo e($cat['label']); ?>:</span> <?php echo e($count); ?>

                    </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catKey => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $rules = $rulesGrouped->get($catKey, collect()); ?>
        <?php if($rules->count() > 0): ?>
            <div class="card mb-3">
                <div
                    class="card-header bg-<?php echo e($cat['color']); ?> <?php echo e(in_array($cat['color'], ['warning', 'light']) ? 'text-dark' : 'text-white'); ?> py-2">
                    <h3 class="card-title mb-0" style="font-size: 1rem;">
                        <i class="<?php echo e($cat['icon']); ?> mr-2"></i><?php echo e($cat['label']); ?>

                        <span class="badge badge-light ml-1"><?php echo e($rules->count()); ?></span>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 points-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="hide-mobile" style="width:35px"></th>
                                    <th>Regra</th>
                                    <th style="width:70px" class="text-center">Pontos</th>
                                    <th style="width:70px" class="text-center hide-mobile">Repetível</th>
                                    <th style="width:60px" class="text-center hide-mobile">Status</th>
                                    <th style="width:80px" class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $rules->sortBy('sort_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="<?php echo e(!$r->active ? 'table-secondary' : ''); ?>">
                                        <td class="text-center hide-mobile">
                                            <i class="<?php echo e($r->icon ?? 'fas fa-star'); ?> text-<?php echo e($cat['color']); ?>"></i>
                                        </td>
                                        <td>
                                            <strong><?php echo e($r->label); ?></strong>
                                            <br><code class="small"><?php echo e($r->key); ?></code>
                                            <?php if($r->description ?? null): ?>
                                                <span class="d-none d-md-inline text-muted"> - <?php echo e(Str::limit($r->description, 40)); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-<?php echo e($r->points > 0 ? 'success' : 'danger'); ?> px-2 py-1">
                                                <?php echo e($r->points > 0 ? '+' : ''); ?><?php echo e($r->points); ?>

                                            </span>
                                        </td>
                                        <td class="text-center hide-mobile">
                                            <?php if($r->repeatable ?? false): ?>
                                                <span class="badge badge-info"
                                                    title="Repetível<?php echo e(($r->max_daily ?? null) ? ' (máx ' . $r->max_daily . '/dia)' : ''); ?>">
                                                    <i class="fas fa-sync-alt"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center hide-mobile">
                                            <?php if($r->active): ?>
                                                <i class="fas fa-check text-success" title="Ativa"></i>
                                            <?php else: ?>
                                                <i class="fas fa-pause text-secondary" title="Inativa"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right text-nowrap">
                                            <a href="<?php echo e(route('admin.points-rules.edit', $r)); ?>"
                                                class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?php echo e(route('admin.points-rules.destroy', $r)); ?>" method="POST" class="d-inline js-confirm-delete" data-confirm="Remover esta regra de pontuação?">
                                                 <?php echo csrf_field(); ?>
                                                 <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <?php 
            $uncategorizedKeys = array_diff($rulesGrouped->keys()->toArray(), array_keys($categories));
        $uncategorized = collect();
        foreach ($uncategorizedKeys as $key) {
            $uncategorized = $uncategorized->merge($rulesGrouped->get($key, collect()));
        }
    ?>
    <?php if($uncategorized->count() > 0): ?>
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white py-2">
                <h3 class="card-title mb-0" style="font-size: 1rem;">
                    <i class="fas fa-folder mr-2"></i>Outras Regras
                    <span class="badge badge-light ml-1"><?php echo e($uncategorized->count()); ?></span>
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 points-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="hide-mobile" style="width:35px"></th>
                                <th>Regra</th>
                                <th style="width:70px" class="text-center">Pontos</th>
                                <th style="width:70px" class="text-center hide-mobile">Repetível</th>
                                <th style="width:60px" class="text-center hide-mobile">Status</th>
                                <th style="width:80px" class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $uncategorized; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="<?php echo e(!$r->active ? 'table-secondary' : ''); ?>">
                                    <td class="text-center hide-mobile">
                                        <i class="<?php echo e($r->icon ?? 'fas fa-star'); ?> text-secondary"></i>
                                    </td>
                                    <td>
                                        <strong><?php echo e($r->label); ?></strong>
                                        <br><code class="small"><?php echo e($r->key); ?></code>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-<?php echo e($r->points > 0 ? 'success' : 'danger'); ?> px-2 py-1">
                                            <?php echo e($r->points > 0 ? '+' : ''); ?><?php echo e($r->points); ?>

                                        </span>
                                    </td>
                                    <td class="text-center hide-mobile">
                                        <?php if($r->repeatable ?? false): ?>
                                            <span class="badge badge-info"><i class="fas fa-sync-alt"></i></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center hide-mobile">
                                        <?php if($r->active): ?>
                                            <i class="fas fa-check text-success"></i>
                                        <?php else: ?>
                                            <i class="fas fa-pause text-secondary"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <a href="<?php echo e(route('admin.points-rules.edit', $r)); ?>"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?php echo e(route('admin.points-rules.destroy', $r)); ?>" method="POST" class="d-inline js-confirm-delete" data-confirm="Remover esta regra?">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if($rulesGrouped->flatten(1)->count() == 0): ?>
        <div class="card">
            <div class="card-body text-center py-4">
                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhuma regra de pontuação cadastrada</h5>
                <p class="text-muted mb-3 small">Crie regras para recompensar a participação dos usuários.</p>
                <a href="<?php echo e(route('admin.points-rules.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i>Criar primeira regra
                </a>
            </div>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(function () {
            $(document)
                .off('submit.pointsDelete', 'form.js-confirm-delete')
                .on('submit.pointsDelete', 'form.js-confirm-delete', function (e) {
                    e.preventDefault();
                    const form = this;
                    const message = (form.getAttribute('data-confirm') || 'Confirma a remoção?').toString();

                    if (typeof Swal === 'undefined') {
                        form.submit();
                        return;
                    }

                    Swal.fire({
                        title: 'Confirmar remoção',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Remover',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#d33'
                    }).then((result) => {
                        if (!result.isConfirmed) return;
                        form.submit();
                    });
                });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\points\index.blade.php ENDPATH**/ ?>