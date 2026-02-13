<?php $__env->startSection('page_title','Editar depoimento'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.testimonials.index')); ?>">Depoimentos</a></li>
    <li class="breadcrumb-item active">Editar</li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .unn-star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 6px;
        }

        .unn-star-rating input {
            display: none;
        }

        .unn-star-rating label {
            cursor: pointer;
            color: #cbd5e1;
            font-size: 22px;
            line-height: 1;
            transition: color 0.15s ease;
            margin: 0;
        }

        .unn-star-rating input:checked~label,
        .unn-star-rating label:hover,
        .unn-star-rating label:hover~label {
            color: #f59e0b;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo e(route('admin.testimonials.update', $testimonial)); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Autor (nome)</label>
                    <input name="author_name" class="form-control" value="<?php echo e(old('author_name', $testimonial->author_name)); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label>Autor (título)</label>
                    <input name="author_title" class="form-control" value="<?php echo e(old('author_title', $testimonial->author_title)); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Avaliação</label>
                    <?php
                        $oldRating = old('rating', $testimonial->rating);
                        $oldRating = is_numeric($oldRating) ? (int) $oldRating : null;
                        if ($oldRating !== null) {
                            $oldRating = max(1, min(5, $oldRating));
                        }
                    ?>

                    <div class="d-flex align-items-center flex-wrap" style="gap: 14px;">
                        <div class="unn-star-rating" role="radiogroup" aria-label="Avaliação por estrelas">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="admin-testimonial-rating-<?php echo e($i); ?>" name="rating"
                                    value="<?php echo e($i); ?>" <?php echo e((string) $oldRating === (string) $i ? 'checked' : ''); ?>>
                                <label for="admin-testimonial-rating-<?php echo e($i); ?>" title="<?php echo e($i); ?>/5">
                                    <i class="fas fa-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <div class="text-muted small">
                            <input type="radio" id="admin-testimonial-rating-none" name="rating" value=""
                                <?php echo e($oldRating === null ? 'checked' : ''); ?> class="d-none">
                            <label for="admin-testimonial-rating-none" class="mb-0" style="cursor:pointer; text-decoration: underline;">
                                Sem avaliação
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label>Status</label>
                    <input class="form-control" value="<?php echo e($testimonial->status); ?>" disabled>
                </div>
                <div class="form-group col-md-6">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" <?php echo e(old('is_featured', $testimonial->is_featured) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="is_featured">Marcar como destaque</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Depoimento</label>
                <textarea name="content" rows="6" class="form-control" required><?php echo e(old('content', $testimonial->content)); ?></textarea>
            </div>

            <div class="text-right">
                <a href="<?php echo e(route('admin.testimonials.index')); ?>" class="btn btn-secondary" data-pjax>Voltar</a>
                <button class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\testimonials\form.blade.php ENDPATH**/ ?>