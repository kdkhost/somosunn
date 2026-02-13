<?php $__env->startSection('page_title', ($faq->id ? 'Editar' : 'Nova').' pergunta'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.faqs.index')); ?>" data-pjax>FAQ</a></li>
    <li class="breadcrumb-item active"><?php echo e($faq->id ? 'Editar' : 'Nova'); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <form class="ajax-form" method="POST" action="<?php echo e($faq->id ? route('admin.faqs.update',$faq) : route('admin.faqs.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php if($faq->id): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Contexto</label>
                    <select name="context" class="form-control" required>
                        <?php $__currentLoopData = $contexts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php echo e(old('context', $faq->context ?: 'general') === $key ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <small class="text-muted">Define onde essa pergunta aparece no site.</small>
                </div>

                <div class="form-group col-md-4">
                    <label>Ordem</label>
                    <input type="number" name="sort_order" class="form-control" value="<?php echo e(old('sort_order', $faq->sort_order ?? 0)); ?>" min="0" max="999999">
                    <small class="text-muted">Menor aparece primeiro.</small>
                </div>

                <div class="form-group col-md-4">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" <?php echo e(old('is_active', $faq->is_active ?? true) ? 'selected' : ''); ?>>Ativo</option>
                        <option value="0" <?php echo e(!old('is_active', $faq->is_active ?? true) ? 'selected' : ''); ?>>Inativo</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Pergunta</label>
                <input type="text" name="question" class="form-control" required maxlength="255" value="<?php echo e(old('question', $faq->question)); ?>" placeholder="Ex: Posso cancelar a qualquer momento?">
            </div>

            <div class="form-group">
                <label>Resposta</label>
                <textarea name="answer" class="form-control" rows="6" required placeholder="Digite a resposta..."><?php echo e(old('answer', $faq->answer)); ?></textarea>
                <small class="text-muted">Dica: quebras de linha são mantidas no site.</small>
            </div>

            <div class="text-right">
                <button class="btn btn-primary">Salvar</button>
                <a href="<?php echo e(route('admin.faqs.index')); ?>" class="btn btn-secondary" data-pjax>Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\faqs\form.blade.php ENDPATH**/ ?>