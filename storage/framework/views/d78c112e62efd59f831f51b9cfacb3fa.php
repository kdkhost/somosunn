<?php $__env->startSection('page_title', 'Mentorias'); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
                <div>
                    <h5 class="mb-1">Gestao de mentorias</h5>
                    <p class="text-muted mb-0">Cadastre e organize mentorias para exibicao no site.</p>
                </div>
                <a href="<?php echo e(route('admin.mentorships.create')); ?>" class="btn btn-primary mt-2 mt-md-0">
                    <i class="fas fa-plus mr-1"></i> Nova mentoria
                </a>
            </div>

            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por titulo ou descricao"
                        value="<?php echo e($search ?? ''); ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 80px;">#</th>
                            <th>Titulo</th>
                            <th>Mentor</th>
                            <th style="width: 120px;">Vagas</th>
                            <th style="width: 150px;">Preco</th>
                            <th style="width: 200px;">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($item->id); ?></td>
                                <td>
                                    <div class="font-weight-bold"><?php echo e($item->title); ?></div>
                                    <small
                                        class="text-muted"><?php echo e(\Illuminate\Support\Str::limit(strip_tags((string) $item->description), 70)); ?></small>
                                </td>
                                <td><?php echo e($item->mentor?->name ?? 'Nao definido'); ?></td>
                                <td><?php echo e($item->slots ?? '-'); ?></td>
                                <td>R$ <?php echo e(number_format((float) $item->price, 2, ',', '.')); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.mentorships.edit', $item)); ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <form action="<?php echo e(route('admin.mentorships.destroy', $item)); ?>" method="POST"
                                        class="d-inline-block">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-danger" data-confirm-delete
                                            data-confirm-title="Remover esta mentoria?"
                                            data-confirm-text="Esta ação não pode ser desfeita.">
                                            <i class="fas fa-trash"></i> Excluir
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhuma mentoria encontrada.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php echo e($items->links()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\mentorships\index.blade.php ENDPATH**/ ?>