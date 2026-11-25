/* ==========================================================================
   SOMOS UNN — Runtime Oficial AdminLTE 4 (Bootstrap 5.3 + OverlayScrollbars v2)
   ========================================================================== */
(function () {
    'use strict';

    // 1. Inicializa OverlayScrollbars v2 na sidebar (padrão oficial AdminLTE 4)
    document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector('.sidebar-wrapper');
        if (sidebarWrapper && window.OverlayScrollbarsGlobal && window.OverlayScrollbarsGlobal.OverlayScrollbars) {
            window.OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                scrollbars: {
                    theme: 'os-theme-light',
                    autoHide: 'leave',
                    clickScroll: true
                }
            });
        }

        // Inicializa tooltips Bootstrap 5
        if (window.bootstrap && window.bootstrap.Tooltip) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new window.bootstrap.Tooltip(el);
            });
        }
    });

    // 2. Adapter jQuery -> Bootstrap 5 Modal (para chamadas legadas $('#meuModal').modal('show'|'hide'))
    if (window.jQuery && window.bootstrap && window.bootstrap.Modal) {
        window.jQuery.fn.modal = function (action) {
            return this.each(function () {
                const instance = window.bootstrap.Modal.getOrCreateInstance(this);
                if (action === 'hide') {
                    instance.hide();
                } else if (action === 'toggle') {
                    instance.toggle();
                } else {
                    instance.show();
                }
            });
        };
    }

    // 3. Sincronizador de tema Dark / Light no padrão Bootstrap 5.3 (data-bs-theme)
    window.UNNAdminTheme = {
        apply: function (theme) {
            const normalized = theme === 'dark' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', normalized);
            document.body.setAttribute('data-bs-theme', normalized);
            document.body.classList.toggle('dark-mode', normalized === 'dark');
            document.documentElement.classList.toggle('dark', normalized === 'dark');
        }
    };
})();
