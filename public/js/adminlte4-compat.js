/**
 * AdminLTE 4 & Bootstrap 5 Compatibility Bridge (JavaScript)
 * SOMOS UNN - Garante funcionamento de modais, dropdowns, treeview e tooltips legados.
 */
(function ($) {
    'use strict';

    // 1. Bridge de Modais do Bootstrap 4 -> Bootstrap 5
    $(document).on('click', '[data-dismiss="modal"]', function (e) {
        e.preventDefault();
        const modalEl = $(this).closest('.modal')[0];
        if (modalEl && window.bootstrap && window.bootstrap.Modal) {
            const inst = window.bootstrap.Modal.getInstance(modalEl) || window.bootstrap.Modal.getOrCreateInstance(modalEl);
            inst.hide();
        }
    });

    $(document).on('click', '[data-toggle="modal"]', function (e) {
        e.preventDefault();
        const targetSelector = $(this).attr('data-target') || $(this).attr('href');
        if (targetSelector && targetSelector.startsWith('#')) {
            const targetEl = document.querySelector(targetSelector);
            if (targetEl && window.bootstrap && window.bootstrap.Modal) {
                const inst = window.bootstrap.Modal.getOrCreateInstance(targetEl);
                inst.show();
            }
        }
    });

    // 2. Bridge de Dropdowns do Bootstrap 4 -> Bootstrap 5
    $(document).on('click', '[data-toggle="dropdown"]', function (e) {
        if ($(this).attr('data-bs-toggle') !== 'dropdown' && window.bootstrap && window.bootstrap.Dropdown) {
            e.preventDefault();
            e.stopPropagation();
            const inst = window.bootstrap.Dropdown.getOrCreateInstance(this);
            inst.toggle();
        }
    });

    // 3. Bridge de Pushmenu / Sidebar Toggle (AdminLTE 4 + 3)
    $(document).on('click', '[data-widget="pushmenu"], [data-lte-toggle="sidebar"]', function (e) {
        e.preventDefault();
        const body = document.body;
        const isMobile = window.innerWidth < 992;

        if (isMobile) {
            body.classList.toggle('sidebar-open');
            body.classList.remove('sidebar-collapse');
        } else {
            body.classList.toggle('sidebar-collapse');
            body.classList.remove('sidebar-open');
        }
    });

    // Fechar sidebar no mobile ao clicar no overlay
    $(document).on('click', '.sidebar-overlay', function () {
        document.body.classList.remove('sidebar-open');
    });

    // 4. Sidebar Treeview (Menus Retráteis)
    $(document).on('click', '.app-sidebar .nav-item > .nav-link', function (e) {
        const item = $(this).parent('.nav-item');
        const treeview = item.children('.nav-treeview');

        if (treeview.length) {
            e.preventDefault();
            const isOpen = item.hasClass('menu-open');

            if (isOpen) {
                treeview.slideUp(200, function () {
                    item.removeClass('menu-open');
                });
            } else {
                // Se accordion estiver ativo, fecha os irmãos abertos
                const parentNav = item.parent();
                if (parentNav.data('accordion') !== false) {
                    parentNav.children('.nav-item.menu-open').not(item).each(function () {
                        $(this).children('.nav-treeview').slideUp(200);
                        $(this).removeClass('menu-open');
                    });
                }

                treeview.slideDown(200, function () {
                    item.addClass('menu-open');
                });
            }
        }
    });

    // 5. jQuery Plugin Adapters para Bootstrap 5 (Garante $(el).modal('show') etc)
    if (window.bootstrap) {
        if ($.fn && !$.fn.modal) {
            $.fn.modal = function (action, ...args) {
                return this.each(function () {
                    const inst = window.bootstrap.Modal.getOrCreateInstance(this);
                    if (typeof action === 'string' && typeof inst[action] === 'function') {
                        inst[action](...args);
                    } else {
                        inst.show();
                    }
                });
            };
        }

        if ($.fn && !$.fn.dropdown) {
            $.fn.dropdown = function (action, ...args) {
                return this.each(function () {
                    const inst = window.bootstrap.Dropdown.getOrCreateInstance(this);
                    if (typeof action === 'string' && typeof inst[action] === 'function') {
                        inst[action](...args);
                    }
                });
            };
        }

        if ($.fn && !$.fn.tooltip) {
            $.fn.tooltip = function (action, ...args) {
                return this.each(function () {
                    const inst = window.bootstrap.Tooltip.getOrCreateInstance(this);
                    if (typeof action === 'string' && typeof inst[action] === 'function') {
                        inst[action](...args);
                    }
                });
            };
        }
    }

    // 6. Inicialização de tooltips após carregamento e após PJAX
    function initTooltips() {
        if (window.bootstrap && window.bootstrap.Tooltip) {
            document.querySelectorAll('[data-bs-toggle="tooltip"], [data-toggle="tooltip"]').forEach(function (el) {
                window.bootstrap.Tooltip.getOrCreateInstance(el);
            });
        }
    }

    $(document).ready(initTooltips);
    $(document).on('pjax:complete', initTooltips);

})(window.jQuery);
