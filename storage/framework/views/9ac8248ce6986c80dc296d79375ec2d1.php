<?php $__env->startSection('page_title', ($coupon->id ? 'Editar' : 'Novo').' cupom'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.coupons.index')); ?>" data-pjax>Cupons</a></li>
    <li class="breadcrumb-item active"><?php echo e($coupon->id ? 'Editar' : 'Novo'); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <form class="ajax-form" method="POST" action="<?php echo e($coupon->id ? route('admin.coupons.update',$coupon) : route('admin.coupons.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php if($coupon->id): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Código</label>
                    <div class="input-group">
                        <input name="code" class="form-control" value="<?php echo e(old('code',$coupon->code)); ?>" placeholder="EX: BLACKFRIDAY26" required>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnGenCode">Gerar</button>
                        </div>
                    </div>
                    <small class="text-muted">Sem espaços. Será salvo em maiúsculo.</small>
                </div>
                <div class="form-group col-md-4">
                    <label>Tipo de desconto</label>
                    <select name="discount_type" class="form-control" required>
                        <option value="percent" <?php echo e(old('discount_type',$coupon->discount_type)=='percent'?'selected':''); ?>>Percentual (%)</option>
                        <option value="fixed" <?php echo e(old('discount_type',$coupon->discount_type)=='fixed'?'selected':''); ?>>Valor fixo (R$)</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Valor do desconto</label>
                    <input name="discount_value" class="form-control" value="<?php echo e(old('discount_value',$coupon->discount_value)); ?>" required>
                    <small class="text-muted">Ex: 10 (10%) ou 25.90 (R$)</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nome (opcional)</label>
                    <input name="name" class="form-control" value="<?php echo e(old('name',$coupon->name)); ?>" placeholder="Ex: Black Friday 2026">
                </div>
                <div class="form-group col-md-6">
                    <label>Descrição (opcional)</label>
                    <input name="description" class="form-control" value="<?php echo e(old('description',$coupon->description)); ?>" placeholder="Mensagem interna ou observações">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Escopo</label>
                    <select name="applies_to" class="form-control" required>
                        <option value="all" <?php echo e(old('applies_to',$coupon->applies_to ?? 'all')=='all'?'selected':''); ?>>Geral (site todo)</option>
                        <option value="event" <?php echo e(old('applies_to',$coupon->applies_to)=='event'?'selected':''); ?>>Somente eventos</option>
                        <option value="course" <?php echo e(old('applies_to',$coupon->applies_to)=='course'?'selected':''); ?>>Somente cursos</option>
                        <option value="mentorship" <?php echo e(old('applies_to',$coupon->applies_to)=='mentorship'?'selected':''); ?>>Somente mentorias</option>
                    </select>
                </div>
                <div class="form-group col-md-4" data-coupon-item-wrap>
                    <label>Item (opcional)</label>
                    <select name="applies_to_id" class="form-control" id="coupon_applies_to_id">
                        <option value="">Aplicar a todos do escopo</option>
                        <?php $__currentLoopData = ($events ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($event->id); ?>" data-scope="event" <?php echo e((string) old('applies_to_id', $coupon->applies_to_id) === (string) $event->id ? 'selected' : ''); ?>>
                                Evento #<?php echo e($event->id); ?> — <?php echo e($event->title); ?><?php if($event->start_at): ?> (<?php echo e($event->start_at->format('d/m/Y')); ?>)<?php endif; ?>
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = ($courses ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($course->id); ?>" data-scope="course" <?php echo e((string) old('applies_to_id', $coupon->applies_to_id) === (string) $course->id ? 'selected' : ''); ?>>
                                Curso #<?php echo e($course->id); ?> — <?php echo e($course->title); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = ($mentorships ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mentorship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($mentorship->id); ?>" data-scope="mentorship" <?php echo e((string) old('applies_to_id', $coupon->applies_to_id) === (string) $mentorship->id ? 'selected' : ''); ?>>
                                Mentoria #<?php echo e($mentorship->id); ?> — <?php echo e($mentorship->title); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <small class="text-muted" data-coupon-item-help>Selecione o item para criar uma promoção direcionada (opcional).</small>
                </div>
                <div class="form-group col-md-4">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" <?php echo e(old('is_active',$coupon->is_active ?? true) ? 'selected' : ''); ?>>Ativo</option>
                        <option value="0" <?php echo e(!old('is_active', $coupon->is_active ?? true) ? 'selected' : ''); ?>>Inativo</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Valor mínimo (opcional)</label>
                    <input name="min_amount" class="form-control" value="<?php echo e(old('min_amount',$coupon->min_amount)); ?>" placeholder="Ex: 100.00">
                </div>
                <div class="form-group col-md-3">
                    <label>Limite total (opcional)</label>
                    <input name="max_uses" class="form-control" value="<?php echo e(old('max_uses',$coupon->max_uses)); ?>" placeholder="Ex: 200">
                </div>
                <div class="form-group col-md-3">
                    <label>Limite por usuário (opcional)</label>
                    <input name="max_uses_per_user" class="form-control" value="<?php echo e(old('max_uses_per_user',$coupon->max_uses_per_user)); ?>" placeholder="Ex: 1">
                </div>
                <div class="form-group col-md-3">
                    <label>Início (opcional)</label>
                    <input name="starts_at" class="form-control" data-datetime-picker value="<?php echo e(old('starts_at', optional($coupon->starts_at)->format('Y-m-d H:i'))); ?>" placeholder="AAAA-MM-DD HH:MM" autocomplete="off">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Término (opcional)</label>
                    <input name="ends_at" class="form-control" data-datetime-picker value="<?php echo e(old('ends_at', optional($coupon->ends_at)->format('Y-m-d H:i'))); ?>" placeholder="AAAA-MM-DD HH:MM" autocomplete="off">
                </div>
                <div class="form-group col-md-9">
                    <div class="alert alert-info mb-0 mt-4">
                        Dica: para Black Friday, use escopo "Geral" e desconto percentual. Para promoção direcionada, defina o escopo (evento/curso/mentoria) e selecione o item.
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button class="btn btn-primary">Salvar</button>
                <a href="<?php echo e(route('admin.coupons.index')); ?>" class="btn btn-secondary" data-pjax>Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\coupons\form.blade.php ENDPATH**/ ?>