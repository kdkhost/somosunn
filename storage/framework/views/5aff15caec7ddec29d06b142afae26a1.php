<?php $__env->startSection('title', 'Resetar senha - UNN'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-16 px-6">
    <div class="max-w-6xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 !px-0">
        
        <?php if (isset($component)) { $__componentOriginalf5f1d7b1d0357ebc52499a3715611bc7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5f1d7b1d0357ebc52499a3715611bc7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-visual','data' => ['title' => 'Nova senha','showSocial' => true,'context' => 'password_reset']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('auth-visual'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Nova senha','show-social' => true,'context' => 'password_reset']); ?>
            Defina uma senha forte e segura para proteger sua conta e seus dados.
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf5f1d7b1d0357ebc52499a3715611bc7)): ?>
<?php $attributes = $__attributesOriginalf5f1d7b1d0357ebc52499a3715611bc7; ?>
<?php unset($__attributesOriginalf5f1d7b1d0357ebc52499a3715611bc7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf5f1d7b1d0357ebc52499a3715611bc7)): ?>
<?php $component = $__componentOriginalf5f1d7b1d0357ebc52499a3715611bc7; ?>
<?php unset($__componentOriginalf5f1d7b1d0357ebc52499a3715611bc7); ?>
<?php endif; ?>

        <div class="p-10 flex flex-col justify-center">
            <h3 class="text-3xl font-bold mb-8 text-slate-900">Redefinir Senha</h3>

            <?php if (isset($component)) { $__componentOriginalc5c8faf71232fc6a89bab5571bc4015c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc5c8faf71232fc6a89bab5571bc4015c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.social-auth-buttons','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('social-auth-buttons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc5c8faf71232fc6a89bab5571bc4015c)): ?>
<?php $attributes = $__attributesOriginalc5c8faf71232fc6a89bab5571bc4015c; ?>
<?php unset($__attributesOriginalc5c8faf71232fc6a89bab5571bc4015c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc5c8faf71232fc6a89bab5571bc4015c)): ?>
<?php $component = $__componentOriginalc5c8faf71232fc6a89bab5571bc4015c; ?>
<?php unset($__componentOriginalc5c8faf71232fc6a89bab5571bc4015c); ?>
<?php endif; ?>
             
            <?php if($errors->any()): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('password.update')); ?>" class="space-y-5">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="token" value="<?php echo e($token ?? ''); ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input name="email" type="email" value="<?php echo e($email ?? old('email')); ?>" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#7a5af8] transition-all" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nova senha</label>
                    <input name="password" type="password" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" placeholder="Mínimo 8 caracteres" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirmar senha</label>
                    <input name="password_confirmation" type="password" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                </div>
                
                <button type="submit" class="w-full btn-primary text-white py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">Alterar senha</button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\auth\passwords\reset.blade.php ENDPATH**/ ?>