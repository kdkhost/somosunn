<?php
    $user = auth()->user();
    $plan = $user ? $user->activePlan() : null;
    $isImpersonatingAdmin = session()->has('impersonator_id') && session()->get('impersonator_is_admin');

    $isAdminUser = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    $isSuperadminUser = $user && (($user->role ?? '') === 'superadmin' || ($user->level ?? '') === 'superadmin');
    $roleLabel = $isSuperadminUser ? 'Super Admin' : ($isAdminUser ? 'Administrador' : null);

    $navItemClass = function (bool $active = false) {
        $base = 'flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition';
        return $active
            ? $base . ' bg-[#1F5EDB]/10 text-[#1F5EDB]'
            : $base . ' text-slate-700 hover:bg-slate-100';
    };
?>

<div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5 sticky top-24">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center shrink-0">
            <img src="<?php echo e($user->profile_photo_url); ?>" alt="Avatar" class="w-full h-full object-cover" onerror="this.style.display='none';">
            <span class="text-slate-500 font-bold" aria-hidden="true"><?php echo e(mb_substr((string) ($user->name ?? ''), 0, 1)); ?></span>
        </div>
        <div class="min-w-0">
            <div class="font-bold text-slate-900 truncate"><?php echo e($user->name); ?></div>
            <div class="text-xs text-slate-500 truncate">
                <?php if($roleLabel): ?>
                    <?php echo e($roleLabel); ?>

                <?php else: ?>
                    <?php echo e($plan?->name ? $plan->name : 'Sem plano ativo'); ?>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="mt-5 space-y-1">
        <a href="<?php echo e(route('panel.dashboard')); ?>" class="<?php echo e($navItemClass(request()->routeIs('panel.dashboard'))); ?>">
            <i class="fas fa-th-large w-5 opacity-80"></i>
            Visão geral
        </a>

        <a href="<?php echo e(route('panel.profile.edit')); ?>" class="<?php echo e($navItemClass(request()->routeIs('panel.profile.*'))); ?>">
            <i class="fas fa-user-circle w-5 opacity-80"></i>
            Meu perfil
        </a>

        <a href="<?php echo e(route('portal')); ?>" class="<?php echo e($navItemClass(false)); ?>">
            <i class="fas fa-home w-5 opacity-80"></i>
            Portal
        </a>

        <?php if($user->canAccessFeature('community') || $isImpersonatingAdmin): ?>
            <a href="<?php echo e(route('social.feed')); ?>" class="<?php echo e($navItemClass(false)); ?>">
                <i class="fas fa-users w-5 opacity-80"></i>
                Comunidade
            </a>
        <?php endif; ?>

        <?php if($user->canAccessFeature('chat') || $isImpersonatingAdmin): ?>
            <a href="<?php echo e(route('chat.index')); ?>" class="<?php echo e($navItemClass(false)); ?>">
                <i class="fas fa-comments w-5 opacity-80"></i>
                Chat
            </a>
        <?php endif; ?>

        <?php if($user->canAccessFeature('marketplace.buy') || $isImpersonatingAdmin): ?>
            <a href="<?php echo e(route('marketplace.index')); ?>" class="<?php echo e($navItemClass(false)); ?>">
                <i class="fas fa-store w-5 opacity-80"></i>
                Marketplace
            </a>
        <?php endif; ?>

        <?php if((method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace()) || $isImpersonatingAdmin): ?>
            <div class="pt-4 mt-4 border-t border-slate-100">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                    Marketplace (Vendas)
                </div>

                <a href="<?php echo e(route('panel.marketplace.index')); ?>" class="<?php echo e($navItemClass(request()->routeIs('panel.marketplace.index'))); ?>">
                    <i class="fas fa-chart-line w-5 opacity-80"></i>
                    Painel de vendas
                </a>
                <a href="<?php echo e(route('panel.marketplace.payments')); ?>" class="<?php echo e($navItemClass(request()->routeIs('panel.marketplace.payments'))); ?>">
                    <i class="fas fa-credit-card w-5 opacity-80"></i>
                    Pagamentos
                </a>
                <a href="<?php echo e(route('panel.marketplace.gateway')); ?>" class="<?php echo e($navItemClass(request()->routeIs('panel.marketplace.gateway'))); ?>">
                    <i class="fas fa-cog w-5 opacity-80"></i>
                    Minhas Configurações de Pagamento
                </a>
                <a href="<?php echo e(route('panel.marketplace.sales')); ?>" class="<?php echo e($navItemClass(request()->routeIs('panel.marketplace.sales'))); ?>">
                    <i class="fas fa-receipt w-5 opacity-80"></i>
                    Minhas vendas
                </a>
            </div>
        <?php endif; ?>

        <?php if(!$isSuperadminUser): ?>
            <div class="pt-4 mt-4 border-t border-slate-100">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                    Workspace
                </div>

                <a href="<?php echo e(route('panel.admin')); ?>" class="<?php echo e($navItemClass(false)); ?>">
                    <i class="fas fa-shield-alt w-5 opacity-80"></i>
                    Painel completo
                </a>
                <?php if($user->isAdmin()): ?>
                <a href="<?php echo e(route('panel.admin', ['to' => 'settings/general'])); ?>" class="<?php echo e($navItemClass(false)); ?>">
                    <i class="fas fa-cogs w-5 opacity-80"></i>
                    Configurações gerais
                </a>
                <a href="<?php echo e(route('panel.admin', ['to' => 'settings/gateway'])); ?>" class="<?php echo e($navItemClass(false)); ?>">
                    <i class="fas fa-credit-card w-5 opacity-80"></i>
                    Gateway / Pagamentos
                </a>
                <a href="<?php echo e(route('panel.admin', ['to' => 'settings/smtp'])); ?>" class="<?php echo e($navItemClass(false)); ?>">
                    <i class="fas fa-envelope w-5 opacity-80"></i>
                    SMTP
                </a>
                <a href="<?php echo e(route('panel.admin', ['to' => 'mailtemplates'])); ?>" class="<?php echo e($navItemClass(false)); ?>">
                    <i class="fas fa-at w-5 opacity-80"></i>
                    Templates de e-mail
                </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\panel\partials\sidebar.blade.php ENDPATH**/ ?>