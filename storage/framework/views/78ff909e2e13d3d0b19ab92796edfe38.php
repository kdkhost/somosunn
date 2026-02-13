<?php
    $quickLinks = [];

    if (auth()->check()) {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $quickLinks = [
                ['label' => 'Configurações', 'icon' => 'fas fa-cogs', 'route' => route('admin.settings')],
                ['label' => 'FAQ (Perguntas)', 'icon' => 'fas fa-question-circle', 'route' => route('admin.faqs.index')],
                ['label' => 'Fontes (Site)', 'icon' => 'fas fa-font', 'route' => route('admin.fonts.index')],
                ['label' => 'Cupons', 'icon' => 'fas fa-ticket-alt', 'route' => route('admin.coupons.index')],
                ['label' => 'Vendas', 'icon' => 'fas fa-shopping-cart', 'route' => route('admin.orders.index')],
                ['label' => 'Usuários', 'icon' => 'fas fa-users-cog', 'route' => route('admin.users.index')],
            ];
        }

        $canMarketplaceSeller = $user->canSellOnMarketplace();

        if ($canMarketplaceSeller) {
            $quickLinks[] = ['label' => 'Marketplace', 'icon' => 'fas fa-store', 'route' => route('admin.marketplace.index')];
            $quickLinks[] = ['label' => 'Pagamentos', 'icon' => 'fas fa-credit-card', 'route' => route('admin.marketplace.payments')];
            $quickLinks[] = ['label' => 'Minhas vendas', 'icon' => 'fas fa-receipt', 'route' => route('admin.marketplace.sales')];
        }
    }
?>

<aside class="control-sidebar control-sidebar-dark">
    <div class="p-3">
        <h5 class="mb-2">Painel rápido</h5>
        <p class="text-muted small mb-3">Ajustes de layout e atalhos para reduzir o tamanho do menu lateral.</p>

        <div class="mb-4">
            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" class="custom-control-input" id="unn_toggle_nav_compact">
                <label class="custom-control-label" for="unn_toggle_nav_compact">Menu compacto</label>
            </div>
            <div class="custom-control custom-switch mb-2">
                <input type="checkbox" class="custom-control-input" id="unn_toggle_sidebar_collapse">
                <label class="custom-control-label" for="unn_toggle_sidebar_collapse">Recolher sidebar</label>
            </div>
        </div>

        <?php if(!empty($quickLinks)): ?>
            <h6 class="text-uppercase text-muted small mb-2">Atalhos</h6>
            <ul class="nav nav-pills nav-sidebar flex-column">
                <?php $__currentLoopData = $quickLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item">
                        <a href="<?php echo e($link['route']); ?>" class="nav-link" data-pjax="true">
                            <i class="nav-icon <?php echo e($link['icon']); ?>"></i>
                            <p><?php echo e($link['label']); ?></p>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        <?php endif; ?>
    </div>
</aside>
<div class="control-sidebar-bg"></div>

<?php $__env->startPush('scripts'); ?>
    <script>
        (function () {
            const storageKey = 'unn_admin_ui';
            const state = { navCompact: false, sidebarCollapsed: false };

            try {
                const stored = JSON.parse(localStorage.getItem(storageKey) || '{}');
                if (stored && typeof stored === 'object') {
                    if (typeof stored.navCompact === 'boolean') state.navCompact = stored.navCompact;
                    if (typeof stored.sidebarCollapsed === 'boolean') state.sidebarCollapsed = stored.sidebarCollapsed;
                }
            } catch (e) { /* ignore */ }

            function persist() {
                try { localStorage.setItem(storageKey, JSON.stringify(state)); } catch (e) { /* ignore */ }
            }

            function applyNavCompact() {
                // AdminLTE: `text-sm` reduz espaçamentos e tipografia (mais compacto)
                document.body.classList.toggle('text-sm', !!state.navCompact);
                const input = document.getElementById('unn_toggle_nav_compact');
                if (input) input.checked = !!state.navCompact;
            }

            function setSidebarCollapsed(collapsed) {
                state.sidebarCollapsed = !!collapsed;
                persist();

                const input = document.getElementById('unn_toggle_sidebar_collapse');
                if (input) input.checked = !!state.sidebarCollapsed;

                const isCollapsed = document.body.classList.contains('sidebar-collapse');
                if (state.sidebarCollapsed === isCollapsed) return;

                const btn = document.querySelector('[data-widget="pushmenu"]');
                if (btn) {
                    btn.click();
                    return;
                }

                document.body.classList.toggle('sidebar-collapse', state.sidebarCollapsed);
            }

            // initial apply
            applyNavCompact();
            setSidebarCollapsed(state.sidebarCollapsed);

            // bind
            const navCompactToggle = document.getElementById('unn_toggle_nav_compact');
            if (navCompactToggle) {
                navCompactToggle.addEventListener('change', function () {
                    state.navCompact = !!this.checked;
                    persist();
                    applyNavCompact();
                });
            }

            const sidebarToggle = document.getElementById('unn_toggle_sidebar_collapse');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('change', function () {
                    setSidebarCollapsed(!!this.checked);
                });
            }
        })();
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\partials\control-sidebar.blade.php ENDPATH**/ ?>