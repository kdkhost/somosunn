
<?php $__env->startSection('title', 'Moderação Comunidade'); ?>
<?php $__env->startSection('page_title', 'Posts da Comunidade'); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Autor</th>
                        <th>Conteúdo</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($post->user->name ?? 'Anon'); ?></td>
                            <td><?php echo e(Str::limit($post->content, 100)); ?></td>
                            <td><?php echo e($post->created_at->format('d/m/Y H:i')); ?></td>
                            <td>
                                <form action="<?php echo e(route('admin.social.destroy', $post->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-danger" data-confirm-delete
                                        data-confirm-title="Excluir este post?"
                                        data-confirm-text="Esta ação não pode ser desfeita."><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhum post encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <?php echo e($posts->links()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\social\index.blade.php ENDPATH**/ ?>