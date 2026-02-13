<?php $__env->startSection('title', 'Entrar - UNN'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        // $logoAuth logic moved to component
    ?>
    <div class="min-h-screen flex items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2 !px-0">
            <!-- Coluna visual -->
            <?php if (isset($component)) { $__componentOriginalf5f1d7b1d0357ebc52499a3715611bc7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf5f1d7b1d0357ebc52499a3715611bc7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-visual','data' => ['title' => 'Bem-vindo de volta!','showSocial' => true,'context' => 'login']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('auth-visual'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Bem-vindo de volta!','show-social' => true,'context' => 'login']); ?>
                Acesse seu painel, cursos, palestras e mentorias com as suas credenciais ou login social.
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

            <!-- Coluna formulário -->
            <div class="p-10">
                <h3 class="text-3xl font-bold mb-8">Entrar na sua conta</h3>

                <?php if (isset($component)) { $__componentOriginalc5c8faf71232fc6a89bab5571bc4015c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc5c8faf71232fc6a89bab5571bc4015c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.social-auth-buttons','data' => ['class' => 'mb-8']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('social-auth-buttons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-8']); ?>
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

                <form method="POST" action="<?php echo e(route('login')); ?>" id="loginForm" class="space-y-5">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input name="email" type="email" required autocomplete="email"
                            class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Senha</label>
                        <input name="password" type="password" required autocomplete="current-password"
                            class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                    </div>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <label class="inline-flex items-center gap-2"><input type="checkbox" name="remember"
                                class="rounded" /> Lembrar-me</label>
                        <a href="<?php echo e(route('password.request')); ?>" class="text-[#7a5af8] font-semibold">Esqueci minha
                            senha</a>
                    </div>
                    <button type="submit"
                        class="w-full btn-primary text-white py-3 rounded-xl font-semibold shadow-lg">Entrar</button>
                </form>

                <p class="mt-6 text-center text-sm">Não tem conta? <a href="<?php echo e(route('register')); ?>"
                        class="text-[#7a5af8] font-semibold">Crie uma</a></p>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\auth\login.blade.php ENDPATH**/ ?>