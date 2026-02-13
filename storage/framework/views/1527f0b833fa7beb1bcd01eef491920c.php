
<?php
    $logoFront = \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
    $logoSrc = $logoFront ? asset(ltrim($logoFront, '/')) : asset('img/logo.svg');
    $menuItems = [
        [
            'label' => 'Institucional',
            'href' => route('sobre'),
            'children' => [
                ['label' => 'Sobre a UNN', 'href' => route('sobre')],
                ['label' => 'Manifesto', 'href' => route('manifesto')],
                ['label' => 'Quem somos', 'href' => route('quem-somos')],
                ['label' => 'Como funciona', 'href' => route('como-funciona')],
                ['label' => 'Valores', 'href' => route('valores')],
                ['label' => 'Contato', 'href' => route('contato')],
            ],
        ],
        [
            'label' => 'Comunidade',
            'href' => route('portal'),
            'setting_key' => 'feature_community',
            'children' => [
                ['label' => 'Portal', 'href' => route('portal')],
                ['label' => 'Feed Social', 'href' => route('social.feed'), 'setting_key' => 'feature_social'],
                ['label' => 'Cursos', 'href' => route('courses.index'), 'setting_key' => 'feature_courses'],
                ['label' => 'Eventos', 'href' => route('events.index'), 'setting_key' => 'feature_events'],
                ['label' => 'Marketplace', 'href' => route('marketplace.index')],
                ['label' => 'Membros', 'href' => route('membros')],
            ],
        ],
        ['label' => 'Premium', 'href' => route('premium'), 'setting_key' => 'feature_premium'],
    ];
    $cta = ['label' => 'Fazer parte', 'href' => route('register'), 'class' => 'bg-gradient-to-br from-[#1F5EDB] via-[#177FD6] to-[#1D3FC4] text-white shadow-[0_15px_30px_-10px_rgba(29,63,196,0.45)]'];
?>

<nav class="fixed inset-x-0 top-0 z-30 bg-white shadow-xl border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 md:px-10 lg:px-16 py-4 flex items-center justify-between gap-4">
        <!-- Logo -->
        <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-3 shrink-0">
            <div class="inline-flex h-12 md:h-16 w-auto items-center justify-center overflow-hidden">
                <img src="<?php echo e($logoSrc); ?>" alt="UNN" class="h-full w-auto object-contain" onerror="this.style.display='none';">
            </div>
        </a>

        <!-- Menu + Actions (aligned right) -->
        <div class="flex items-center gap-6">
            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center gap-4 text-sm font-semibold text-gray-800">
                <?php $__currentLoopData = $menuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        // Check if this menu item has a setting key and if it's enabled
                        $settingKey = $item['setting_key'] ?? null;
                        $isEnabled = $settingKey ? \App\Models\Setting::get($settingKey, '1') === '1' : true;
                    ?>
                    <?php if($isEnabled): ?>
                        <?php if(isset($item['children'])): ?>
                            <div class="relative group">
                                <a href="<?php echo e($item['href']); ?>" class="inline-flex items-center gap-1 hover:text-[#1F5EDB] transition-colors py-2">
                                    <?php echo e($item['label']); ?>

                                    <i class="fas fa-chevron-down text-xs ml-0.5 opacity-70"></i>
                                </a>
                                <div class="absolute right-0 top-full pt-2 hidden group-hover:block z-40">
                                    <div class="rounded-2xl bg-white shadow-xl border border-slate-100 min-w-[220px] py-2 overflow-hidden">
                                        <?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $childSettingKey = $child['setting_key'] ?? null;
                                                $childEnabled = $childSettingKey ? \App\Models\Setting::get($childSettingKey, '1') === '1' : true;
                                                $childRequiresAuth = (bool) ($child['requires_auth'] ?? false);
                                                if ($childRequiresAuth && !Auth::check()) {
                                                    $childEnabled = false;
                                                }
                                            ?>
                                            <?php if($childEnabled): ?>
                                                <a href="<?php echo e($child['href']); ?>" class="block px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition bg-white">
                                                    <?php echo e($child['label']); ?>

                                                </a>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="<?php echo e($item['href']); ?>" class="hover:text-[#1F5EDB] transition-colors"><?php echo e($item['label']); ?></a>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Mobile Toggle -->
            <button id="mobile-menu-toggle" type="button" aria-controls="mobile-menu" aria-expanded="false"
                class="lg:hidden inline-flex items-center justify-center rounded-full border-0 sm:border sm:border-[#1F5EDB] px-3 py-2 text-sm font-bold text-[#1F5EDB] hover:bg-[#1F5EDB]/10">
                <span class="sr-only">Abrir menu</span>
                <i class="fas fa-bars text-lg"></i>
            </button>

            <!-- Action Buttons -->
            <div class="hidden lg:flex items-center gap-3">
                <?php if(auth()->guard()->guest()): ?>
                    <a href="<?php echo e(route('login')); ?>" class="inline-flex items-center gap-2 rounded-full border border-[#1F5EDB] px-6 py-2 text-sm font-bold text-[#1F5EDB] hover:bg-[#1F5EDB]/10 transition-all whitespace-nowrap">
                        Entrar
                    </a>
                <?php else: ?>
                    <div class="relative group mr-2" id="connection-notifications-bubble">
                        <a href="<?php echo e(route('social.feed')); ?>" class="text-gray-500 hover:text-blue-600 transition relative">
                            <i class="fas fa-bell text-xl"></i>
                            <span id="connection-notification-count" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1 rounded-full hidden">0</span>
                        </a>
                    </div>

                    <!-- Dropdown Minha Conta -->
                    <div class="relative group">
                        <button class="inline-flex items-center gap-2 rounded-full <?php echo e($cta['class']); ?> px-7 py-3 text-sm font-bold text-white transition-all transform hover:scale-105 active:scale-95 whitespace-nowrap">
                            Minha Conta
                            <i class="fas fa-chevron-down text-xs opacity-70"></i>
                        </button>

                        <div class="absolute right-0 top-full pt-3 hidden group-hover:block z-50 animate-in fade-in slide-in-from-top-2 duration-200">
                            <div class="rounded-2xl bg-white shadow-2xl border border-slate-100 min-w-[240px] py-3 overflow-hidden">
                                 <div class="px-5 py-2 border-b border-slate-50 mb-2">
                                     <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Membro UNN</p>
                                     <p class="text-sm font-bold text-gray-800 truncate"><?php echo e(Auth::user()->name); ?></p>
                                 </div>

                                 <a href="<?php echo e(route('panel.dashboard')); ?>" class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                     <i class="fas fa-th-large w-5 opacity-70"></i>
                                     Painel
                                 </a>

                                 <a href="<?php echo e(route('panel.profile.edit')); ?>" class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                     <i class="fas fa-user-circle w-5 opacity-70"></i>
                                     Meu perfil
                                 </a>

                                 <a href="<?php echo e(route('marketplace.index')); ?>" class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                     <i class="fas fa-store w-5 opacity-70"></i>
                                     Marketplace
                                 </a>

                                 <?php if(Auth::user()->canSellOnMarketplace()): ?>
                                     <a href="<?php echo e(route('panel.marketplace.payments')); ?>" class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                         <i class="fas fa-credit-card w-5 opacity-70"></i>
                                         Configurar pagamentos
                                     </a>
                                 <?php endif; ?>

                                 <?php if(Auth::user()->canSellOnMarketplace()): ?>
                                     <a href="<?php echo e(route('panel.marketplace.sales')); ?>" class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                         <i class="fas fa-receipt w-5 opacity-70"></i>
                                         Minhas vendas
                                     </a>
                                 <?php endif; ?>

                                 <?php if(Auth::user()->isAdmin()): ?>
                                     <a href="<?php echo e(route('panel.admin')); ?>" class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                         <i class="fas fa-th-large w-5 opacity-70"></i>
                                         Painel Administrativo
                                     </a>
                                     <a href="<?php echo e(route('panel.admin', ['to' => 'settings/general'])); ?>" class="flex items-center gap-3 px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition-all">
                                         <i class="fas fa-cogs w-5 opacity-70"></i>
                                         Configurações
                                     </a>
                                 <?php endif; ?>

                                <div class="mt-2 pt-2 border-t border-slate-50">
                                    <form action="<?php echo e(route('logout')); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="flex w-full items-center gap-3 px-5 py-3 text-sm text-red-600 hover:bg-red-50 transition-all font-semibold">
                                            <i class="fas fa-sign-out-alt w-5 opacity-70"></i>
                                            Sair da conta
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(auth()->guard()->guest()): ?>
                    <a href="<?php echo e($cta['href']); ?>" class="inline-flex items-center gap-2 rounded-full <?php echo e($cta['class']); ?> px-7 py-3 text-sm font-bold whitespace-nowrap">
                        <?php echo e($cta['label']); ?>

                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div id="mobile-menu" class="fixed inset-0 hidden" aria-hidden="true" style="z-index:1200;">
    <div id="mobile-menu-overlay" class="absolute inset-0 bg-black/40 opacity-0 pointer-events-none transition-opacity duration-300"></div>
    <div id="mobile-menu-panel" class="relative z-10 w-4/5 max-w-sm h-full bg-white border-r border-white/80 shadow-2xl transform -translate-x-full transition-transform duration-300 ease-out overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div class="flex items-center gap-3">
                <div class="inline-flex h-12 w-auto items-center justify-center overflow-hidden">
                    <img src="<?php echo e($logoSrc); ?>" alt="UNN" class="h-full w-auto object-contain" onerror="this.style.display='none';">
                </div>
            </div>
            <button id="mobile-menu-close" type="button" class="text-gray-500 hover:text-gray-900 text-3xl leading-none">&times;</button>
        </div>
        <nav class="px-6 py-4 flex flex-col gap-1 text-gray-700">
            <?php $__currentLoopData = $menuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <a href="<?php echo e($item['href']); ?>" class="block rounded-2xl px-4 py-2 font-semibold hover:bg-slate-100 transition-colors">
                        <?php echo e($item['label']); ?>

                    </a>
                    <?php if(isset($item['children'])): ?>
                        <div class="pl-4 space-y-1">
                            <?php $__currentLoopData = $item['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $childRequiresAuth = (bool) ($child['requires_auth'] ?? false);
                                ?>
                                <?php if(!$childRequiresAuth || Auth::check()): ?>
                                    <a href="<?php echo e($child['href']); ?>" class="block rounded-xl px-3 py-1.5 text-sm text-gray-600 hover:bg-slate-100 transition-colors">
                                        <?php echo e($child['label']); ?>

                                    </a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>
        <div class="px-6 mt-2 mb-6 space-y-3">
            <?php if(auth()->guard()->guest()): ?>
                <a href="<?php echo e(route('login')); ?>" class="inline-flex w-full items-center justify-center rounded-full border border-[#1F5EDB] px-6 py-3 text-sm font-semibold text-[#1F5EDB] hover:bg-[#1F5EDB]/10 transition">
                    Entrar
                </a>
                <a href="<?php echo e(route('register')); ?>" class="inline-flex w-full items-center justify-center rounded-full <?php echo e($cta['class']); ?> px-6 py-3 text-sm font-bold text-white transition">
                    Fazer parte
                </a>
            <?php else: ?>
                <div class="p-4 bg-slate-50 rounded-2xl mb-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Membro UNN</p>
                    <p class="font-bold text-gray-800"><?php echo e(Auth::user()->name); ?></p>
                </div>
                
                <a href="<?php echo e(route('panel.profile.edit')); ?>" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-slate-100 transition">
                    <i class="fas fa-user-circle w-5 opacity-70"></i> Meu perfil
                </a>

                <a href="<?php echo e(route('marketplace.index')); ?>" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-slate-100 transition">
                    <i class="fas fa-store w-5 opacity-70"></i> Marketplace
                </a>

                <?php if(Auth::user()->canSellOnMarketplace()): ?>
                    <a href="<?php echo e(route('panel.marketplace.payments')); ?>" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-slate-100 transition">
                        <i class="fas fa-credit-card w-5 opacity-70"></i> Configurar pagamentos
                    </a>
                <?php endif; ?>

                <?php if(Auth::user()->canSellOnMarketplace()): ?>
                    <a href="<?php echo e(route('panel.marketplace.sales')); ?>" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-slate-100 transition">
                        <i class="fas fa-receipt w-5 opacity-70"></i> Minhas vendas
                    </a>
                <?php endif; ?>
                
                <?php if(Auth::user()->isAdmin()): ?>
                    <a href="<?php echo e(route('panel.admin')); ?>" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-slate-100 transition">
                        <i class="fas fa-th-large w-5 opacity-70"></i> Painel Administrativo
                    </a>
                    <a href="<?php echo e(route('panel.admin', ['to' => 'settings/general'])); ?>" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-slate-100 transition">
                        <i class="fas fa-cogs w-5 opacity-70"></i> Configurações
                    </a>
                <?php endif; ?>
                
                <form action="<?php echo e(route('logout')); ?>" method="POST" class="mt-4">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 transition">
                        <i class="fas fa-sign-out-alt w-5 opacity-70"></i> Sair da conta
                    </button>
                </form>
            <?php endif; ?>

            <?php if(!empty($pwaEnabled)): ?>
                <button onclick="showInstallModal()" class="w-full mt-3 inline-flex items-center justify-center rounded-full bg-slate-100 px-6 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-200 transition">
                    <i class="fas fa-download mr-2"></i> Instalar App
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('31a195ca-d382-4fda-b547-fbe7d8930298')): $__env->markAsRenderedOnce('31a195ca-d382-4fda-b547-fbe7d8930298'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            (function () {
                const bindMobileMenu = function () {
                    const mobileToggle = document.getElementById('mobile-menu-toggle');
                    const mobileMenu = document.getElementById('mobile-menu');
                    const mobilePanel = document.getElementById('mobile-menu-panel');
                    const mobileOverlay = document.getElementById('mobile-menu-overlay');
                    const mobileClose = document.getElementById('mobile-menu-close');
                    const body = document.body;

                    if (!mobileToggle || !mobileMenu || !mobilePanel || !mobileOverlay || !mobileClose) {
                        return;
                    }

                    if (mobileMenu.dataset.bound === '1' || mobileToggle.dataset.mobileMenuBound === '1') {
                        return;
                    }
                    mobileMenu.dataset.bound = '1';
                    mobileToggle.dataset.mobileMenuBound = '1';

                    const openMenu = function () {
                        mobileMenu.classList.remove('hidden');
                        mobileMenu.setAttribute('aria-hidden', 'false');
                        mobileToggle.setAttribute('aria-expanded', 'true');
                        body.classList.add('overflow-hidden');

                        mobileOverlay.classList.remove('pointer-events-none');
                        mobileOverlay.classList.remove('opacity-0');
                        mobileOverlay.classList.add('opacity-100');
                        mobilePanel.classList.remove('-translate-x-full');
                    };

                    const closeMenu = function () {
                        mobileToggle.setAttribute('aria-expanded', 'false');
                        mobileOverlay.classList.add('opacity-0');
                        mobileOverlay.classList.remove('opacity-100');
                        mobilePanel.classList.add('-translate-x-full');
                        mobileOverlay.classList.add('pointer-events-none');

                        setTimeout(function () {
                            mobileMenu.classList.add('hidden');
                            mobileMenu.setAttribute('aria-hidden', 'true');
                            body.classList.remove('overflow-hidden');
                        }, 320);
                    };

                    mobileToggle.addEventListener('click', function (event) {
                        event.preventDefault();
                        openMenu();
                    });

                    mobileClose.addEventListener('click', function (event) {
                        event.preventDefault();
                        closeMenu();
                    });

                    mobileOverlay.addEventListener('click', closeMenu);

                    mobilePanel.querySelectorAll('a[href]').forEach(function (link) {
                        link.addEventListener('click', function () {
                            closeMenu();
                        });
                    });

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && mobileMenu.getAttribute('aria-hidden') === 'false') {
                            closeMenu();
                        }
                    });

                    window.addEventListener('resize', function () {
                        if (window.innerWidth >= 1024 && mobileMenu.getAttribute('aria-hidden') === 'false') {
                            closeMenu();
                        }
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', bindMobileMenu);
                } else {
                    bindMobileMenu();
                }

                document.addEventListener('pjax:end', bindMobileMenu);
            })();
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\partials\navbar.blade.php ENDPATH**/ ?>