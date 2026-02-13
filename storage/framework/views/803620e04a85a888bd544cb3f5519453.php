<?php $__env->startSection('page_title', ($plan->id ? 'Editar' : 'Novo').' plano'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.plans.index')); ?>" data-pjax>Planos</a></li>
    <li class="breadcrumb-item active"><?php echo e($plan->id ? 'Editar' : 'Novo'); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <form class="ajax-form" method="POST" action="<?php echo e($plan->id ? route('admin.plans.update',$plan) : route('admin.plans.store')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <?php if($plan->id): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nome do pacote</label>
                    <input name="name" class="form-control" value="<?php echo e(old('name',$plan->name)); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Preço</label>
                    <input name="price" class="form-control mask-money" value="<?php echo e(old('price',$plan->price)); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Período</label>
                    <select name="period" class="form-control">
                        <?php $__currentLoopData = ['mensal','trimestral','semestral','anual','vitalício']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p); ?>" <?php echo e(old('period',$plan->period)==$p?'selected':''); ?>><?php echo e(ucfirst($p)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Slug (URL)</label>
                    <input name="slug" class="form-control" value="<?php echo e(old('slug',$plan->slug)); ?>" placeholder="ex: pro, elite">
                    <small class="text-muted">Se vazio, será gerado automaticamente.</small>
                </div>
                <div class="form-group col-md-8">
                    <label>Descrição</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Resumo do plano (aparece no site)"><?php echo e(old('description',$plan->description)); ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="highlight" value="0">
                        <input type="checkbox" class="custom-control-input" id="highlight" name="highlight" value="1" <?php echo e(old('highlight',$plan->highlight) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="highlight">Destacar (ribbon)</label>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="coupons_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="coupons_enabled" name="coupons_enabled" value="1" <?php echo e(old('coupons_enabled',$plan->coupons_enabled) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="coupons_enabled">Permitir cupons</label>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" <?php echo e(old('is_active',$plan->is_active ?? true) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="is_active">Plano ativo</label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Imagem do pacote</label>
                    <input type="hidden" name="remove_image" value="0">
                    <div class="upload-box" data-max-size="5242880" data-crop="1" data-existing-url="<?php echo e($plan->image ? asset('storage/'.$plan->image) : ''); ?>" data-remove-input="[name='remove_image']">
                        <input type="file" name="image" accept="image/*" class="d-none">
                        <div class="upload-preview mb-2"></div>
                        <div class="upload-meta text-muted"></div>
                        <small class="text-muted upload-help"></small>
                        <div class="progress upload-progress progress-sm d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                        <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label>Benefícios (um por linha)</label>
                    <?php 
                        $benefits = old('benefits', $plan->benefits ?? []);
                        $benefitsText = is_array($benefits) ? implode("\n", $benefits) : $benefits;
                    ?>
                    <textarea name="benefits" class="form-control" rows="8" placeholder="Ex: Acesso ao portal&#10;Mentorias semanais&#10;Grupo VIP"><?php echo e($benefitsText); ?></textarea>
                    <small class="text-muted">Use uma linha por benefício. Permissões abaixo complementam o acesso.</small>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Comparativo (exibição no Site)</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Conexões por mês</label>
                            <input name="comparison[connections_per_month]" class="form-control" placeholder="Ex: 5 / Ilimitadas" value="<?php echo e(old('comparison.connections_per_month', data_get($plan->comparison, 'connections_per_month'))); ?>">
                            <small class="text-muted">Se vazio: plano grátis → 5, pagos → Ilimitadas.</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mentoria em grupo</label>
                            <input name="comparison[group_mentorship]" class="form-control" placeholder="Ex: 1/mês / Ilimitada" value="<?php echo e(old('comparison.group_mentorship', data_get($plan->comparison, 'group_mentorship'))); ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Mentoria individual</label>
                            <input name="comparison[individual_mentorship]" class="form-control" placeholder="Ex: 1/mês" value="<?php echo e(old('comparison.individual_mentorship', data_get($plan->comparison, 'individual_mentorship'))); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-2">
                                <input type="hidden" name="comparison[priority_support]" value="0">
                                <input type="checkbox" class="custom-control-input" id="priority_support" name="comparison[priority_support]" value="1" <?php echo e(old('comparison.priority_support', (bool) data_get($plan->comparison, 'priority_support')) ? 'checked' : ''); ?>>
                                <label class="custom-control-label" for="priority_support">Suporte prioritário</label>
                            </div>
                        </div>
                        <div class="form-group col-md-8">
                            <small class="text-muted d-block mt-2">
                                Dica: itens como <strong>Acesso a cursos</strong>, <strong>Eventos</strong> e <strong>Comunidade</strong> são derivados das permissões do plano.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <?php
                $planFeatures = $planFeatures ?? [];
                $selectedFeatures = old('permissions', $plan->permissions ?? []);
                if (!is_array($selectedFeatures)) {
                    $selectedFeatures = [];
                }
            ?>

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Recursos do Plano (Site)</h3>
                </div>
                <div class="card-body">
                    <?php
                        // Agrupar features por categoria
                        $featureGroups = [
                            'Acesso Básico' => ['community', 'chat', 'connections', 'connections.unlimited'],
                            'Cursos' => ['courses', 'courses.certificates', 'courses.downloads'],
                            'Eventos' => ['events', 'events.recordings', 'events.vip'],
                            'Mentorias' => ['mentorships', 'mentorships.group', 'mentorships.individual'],
                            'Extras' => ['rankings', 'support.priority', 'early.access'],
                        ];
                    ?>

                    <?php $__currentLoopData = $featureGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $groupKeys): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <h6 class="font-weight-bold text-primary mb-2 <?php echo e(!$loop->first ? 'mt-3' : ''); ?>">
                            <i class="fas fa-folder mr-1"></i> <?php echo e($groupName); ?>

                        </h6>
                        <div class="row mb-2">
                            <?php $__currentLoopData = $groupKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featureKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(isset($planFeatures[$featureKey])): ?>
                                    <div class="col-md-4 col-lg-3">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="feature-<?php echo e($featureKey); ?>"
                                                name="permissions[]" value="<?php echo e($featureKey); ?>"
                                                <?php echo e(in_array($featureKey, $selectedFeatures, true) ? 'checked' : ''); ?>>
                                            <label class="custom-control-label" for="feature-<?php echo e($featureKey); ?>"><?php echo e($planFeatures[$featureKey]); ?></label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <small class="text-muted d-block mt-2">
                        Esses recursos controlam o acesso no site (ex.: Comunidade/Chat) e alimentam o comparativo em <code>/premium</code>.
                    </small>
                </div>
            </div>

            <div class="text-right">
                <button class="btn btn-primary">Salvar</button>
                <a href="<?php echo e(route('admin.plans.index')); ?>" class="btn btn-secondary" data-pjax>Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\plans\form.blade.php ENDPATH**/ ?>