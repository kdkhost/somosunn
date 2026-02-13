<?php $__env->startSection('title', 'Recuperar senha - UNN'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-16 px-6">
    <div class="max-w-6xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 !px-0">
        
        <?php if (isset($component)) { $__componentOriginalf5f1d7b1d0357ebc52499a3715611bc7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5f1d7b1d0357ebc52499a3715611bc7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-visual','data' => ['title' => 'Recuperar acesso','showSocial' => true,'context' => 'password_email']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('auth-visual'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Recuperar acesso','show-social' => true,'context' => 'password_email']); ?>
            Esqueceu sua senha? Não se preocupe, informe seu e-mail e nós te ajudamos a recuperar.
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
            <div class="mb-6">
                <h3 class="text-3xl font-bold text-slate-900 mb-2">Esqueceu a senha?</h3>
                <p class="text-slate-500">Informe o e-mail cadastrado e enviaremos um link.</p>
            </div>

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

            <form method="POST" action="<?php echo e(route('password.email')); ?>" class="space-y-5">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input name="email" type="email" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:border-purple-500 focus:ring-2 focus:ring-purple-300 transition-all" />
                </div>
                <button type="submit" class="w-full btn-primary text-white py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">Enviar link de recuperação</button>
            </form>
            
            <p class="mt-8 text-center text-sm text-slate-500">
                Lembrou a senha? <a href="<?php echo e(route('login')); ?>" class="text-[#7a5af8] font-bold hover:underline">Voltar ao login</a>
            </p>
            
            <!-- Voltar ao site -->
            <div class="mt-auto pt-6 text-center">
                 <a href="/" class="text-xs text-slate-400 hover:text-slate-600 flex items-center justify-center gap-1"><i class="fas fa-arrow-left"></i> Voltar ao site</a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\auth\passwords\email.blade.php ENDPATH**/ ?>