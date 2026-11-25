@php
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

        $canAccessMarketplace = $user->isAdmin() || $user->canSellOnMarketplace();
        $canManageOwnMarketplace = $user->isSuperAdmin() || $user->canSellOnMarketplace();
        $storefrontModuleInstalled = \App\Models\SellerStore::tableAvailable() && \App\Models\SellerProduct::tableAvailable();

        if ($canAccessMarketplace) {
            $quickLinks[] = ['label' => 'Marketplace', 'icon' => 'fas fa-store', 'route' => route('admin.marketplace.index')];
            $quickLinks[] = ['label' => 'Pagamentos', 'icon' => 'fas fa-credit-card', 'route' => route('admin.marketplace.payments')];
            if ($canManageOwnMarketplace && $storefrontModuleInstalled) {
                $quickLinks[] = ['label' => $user->isSuperAdmin() ? 'Loja da plataforma' : 'Minha loja', 'icon' => 'fas fa-store-alt', 'route' => route('admin.marketplace.store.edit')];
                $quickLinks[] = ['label' => 'Produtos próprios', 'icon' => 'fas fa-box-open', 'route' => route('admin.marketplace.products.index')];
                $quickLinks[] = ['label' => 'Pedidos da loja', 'icon' => 'fas fa-truck', 'route' => route('admin.marketplace.orders.index')];
            }
            if ($canManageOwnMarketplace) {
                $quickLinks[] = ['label' => 'Minhas vendas', 'icon' => 'fas fa-receipt', 'route' => route('admin.marketplace.sales')];
            }
            if ($user->isAdmin() && $storefrontModuleInstalled) {
                $quickLinks[] = ['label' => 'Lojas marketplace', 'icon' => 'fas fa-store-alt-slash', 'route' => route('admin.marketplace.stores.index')];
                $quickLinks[] = ['label' => 'Produtos marketplace', 'icon' => 'fas fa-boxes-stacked', 'route' => route('admin.marketplace.catalog.index')];
            }
        }
    }
@endphp

{{-- Painel Rápido (Offcanvas nativo Bootstrap 5 / AdminLTE 4) --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="adminQuickPanelOffcanvas" aria-labelledby="adminQuickPanelLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="adminQuickPanelLabel">
            <i class="fas fa-sliders-h me-2 text-primary"></i>Painel rápido
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body">
        <p class="text-secondary small mb-3">Ajustes de layout e atalhos rápidos do painel administrativo.</p>

        {{-- Atalho de Upload Rápido --}}
        <div class="mb-4">
            <button type="button" class="btn btn-primary w-100 btn-lg shadow-sm rounded-pill fw-bold"
                data-bs-dismiss="offcanvas" onclick="window.openQuickUploadModal()">
                <i class="fas fa-camera-retro me-2"></i> Registrar Fotos
            </button>
        </div>

        <div class="mb-4">
            <div class="form-check form-switch mb-2">
                <input type="checkbox" class="form-check-input" id="unn_toggle_nav_compact">
                <label class="form-check-label" for="unn_toggle_nav_compact">Interface compacta</label>
            </div>
            <div class="form-check form-switch mb-2">
                <input type="checkbox" class="form-check-input" id="unn_toggle_sidebar_collapse">
                <label class="form-check-label" for="unn_toggle_sidebar_collapse">Recolher menu lateral</label>
            </div>
        </div>

        @if(!empty($quickLinks))
            <h6 class="text-uppercase text-secondary small fw-bold mb-2">Atalhos</h6>
            <div class="list-group list-group-flush">
                @foreach($quickLinks as $link)
                    <a href="{{ $link['route'] }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 rounded-2 border-0" data-pjax="true">
                        <i class="{{ $link['icon'] }} text-primary" style="width: 18px; text-align: center;"></i>
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
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
            } catch (e) { }

            function persist() {
                try { localStorage.setItem(storageKey, JSON.stringify(state)); } catch (e) { }
            }

            function applyNavCompact() {
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

                const btn = document.querySelector('[data-lte-toggle="sidebar"]');
                if (btn) {
                    btn.click();
                    return;
                }

                document.body.classList.toggle('sidebar-collapse', state.sidebarCollapsed);
            }

            applyNavCompact();
            setSidebarCollapsed(state.sidebarCollapsed);

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
@endpush
