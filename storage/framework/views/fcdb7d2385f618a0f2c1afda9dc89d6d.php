<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'withDivider' => true,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'withDivider' => true,
]); ?>
<?php foreach (array_filter(([
    'withDivider' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $googleEnabled = (string) \App\Models\Setting::get('social_google_enabled', \App\Models\Setting::get('social_google_active', '0')) === '1';
    $facebookEnabled = (string) \App\Models\Setting::get('social_facebook_enabled', \App\Models\Setting::get('social_facebook_active', '0')) === '1';
    $linkedinEnabled = (string) \App\Models\Setting::get('social_linkedin_enabled', \App\Models\Setting::get('social_linkedin_active', '0')) === '1';

    $providers = [];

    if ($googleEnabled) {
        $providers[] = [
            'key' => 'google',
            'label' => 'Google',
            'icon' => 'fab fa-google',
            'icon_class' => 'text-rose-600',
        ];
    }

    if ($facebookEnabled) {
        $providers[] = [
            'key' => 'facebook',
            'label' => 'Facebook',
            'icon' => 'fab fa-facebook-f',
            'icon_class' => 'text-blue-600',
        ];
    }

    if ($linkedinEnabled) {
        $providers[] = [
            'key' => 'linkedin',
            'label' => 'LinkedIn',
            'icon' => 'fab fa-linkedin-in',
            'icon_class' => 'text-sky-700',
        ];
    }
?>

<?php if(count($providers) > 0): ?>
    <div <?php echo e($attributes->merge(['class' => 'space-y-4'])); ?>>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('social.redirect', ['provider' => $provider['key']])); ?>"
                    class="inline-flex items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    <i class="<?php echo e($provider['icon']); ?> <?php echo e($provider['icon_class']); ?>"></i>
                    <span>Continuar com <?php echo e($provider['label']); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if($withDivider): ?>
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-white px-3 text-slate-400 font-semibold">ou</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\components\social-auth-buttons.blade.php ENDPATH**/ ?>