<?php $__env->startSection('title', 'Criar conta - UNN'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $logoAuth = \App\Models\Setting::get('logo_auth') ?: \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
    $logoAuthSrc = $logoAuth ? asset(ltrim($logoAuth, '/')) : asset('img/logo.svg');
?>
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-16 px-6">
    <div class="max-w-6xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
        <div class="hidden md:flex flex-col items-center justify-center gap-6 p-12 bg-gradient-to-br from-[#7a5af8] via-[#6a40e6] to-[#4cc3ff] text-white">
            <img src="<?php echo e($logoAuthSrc); ?>" class="h-16" alt="UNN" onerror="this.style.display='none';">
            <h2 class="text-2xl font-bold">Crie sua conta</h2>
            <p class="max-w-xs text-sm text-white/90">Faça parte da comunidade e tenha acesso às mentorias e eventos.</p>
        </div>
        <div class="p-10">
            <h3 class="text-3xl font-bold mb-8">Criar conta</h3>
            <form method="POST" action="<?php echo e(route('register')); ?>" class="space-y-5">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome completo</label>
                    <input name="name" type="text" required class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input name="email" type="email" required autocomplete="email" class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Senha</label>
                    <input name="password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Confirmar senha</label>
                    <input name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-[#7a5af8]" />
                </div>
                <button type="submit" class="w-full btn-primary text-white py-3 rounded-xl font-semibold shadow-lg">Criar conta</button>
            </form>
            <p class="mt-6 text-center text-sm">Já tem conta? <a href="<?php echo e(route('login')); ?>" class="text-[#7a5af8] font-semibold">Entrar</a></p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/auth/register.blade.php ENDPATH**/ ?>