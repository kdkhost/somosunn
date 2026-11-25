{{-- Gaveta Lateral Global de Instruções (Slide-over da direita para a esquerda) --}}
<div id="unn-help-drawer-backdrop" class="unn-help-backdrop" aria-hidden="true"></div>
<aside id="unn-help-drawer" class="unn-help-drawer" role="dialog" aria-modal="true" aria-labelledby="unn-help-drawer-title" aria-hidden="true">
    <div class="unn-help-drawer-header">
        <div class="unn-help-drawer-title-wrap">
            <span id="unn-help-drawer-badge" class="unn-help-badge">Guia de Configuração</span>
            <h3 id="unn-help-drawer-title" class="unn-help-title">Instruções do Campo</h3>
        </div>
        <button type="button" id="unn-help-drawer-close" class="unn-help-close" aria-label="Fechar instruções">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="unn-help-drawer-body">
        <div id="unn-help-drawer-summary" class="unn-help-summary"></div>

        <div id="unn-help-drawer-steps-wrap" class="unn-help-section" style="display:none;">
            <h4 class="unn-help-section-title">
                <i class="fas fa-list-ol"></i> Passo a passo
            </h4>
            <ol id="unn-help-drawer-steps" class="unn-help-steps"></ol>
        </div>

        <div id="unn-help-drawer-links-wrap" class="unn-help-section" style="display:none;">
            <h4 class="unn-help-section-title">
                <i class="fas fa-arrow-up-right-from-square"></i> Links diretos e credenciais
            </h4>
            <div id="unn-help-drawer-links" class="unn-help-links"></div>
        </div>

        <div id="unn-help-drawer-tip-wrap" class="unn-help-tip" style="display:none;">
            <div class="unn-help-tip-icon"><i class="fas fa-lightbulb"></i></div>
            <div id="unn-help-drawer-tip" class="unn-help-tip-text"></div>
        </div>
    </div>
</aside>

<style>
    /* Botão gatilho discreto ao lado de cada label de input */
    .unn-help-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        margin-left: 6px;
        border-radius: 9999px;
        border: 1px solid rgba(31, 94, 219, 0.28);
        background: rgba(31, 94, 219, 0.08);
        color: #1F5EDB;
        font-size: 11px;
        cursor: pointer;
        vertical-align: middle;
        transition: all 0.2s ease;
        padding: 0;
        line-height: 1;
    }

    .unn-help-trigger:hover,
    .unn-help-trigger:focus {
        background: #1F5EDB;
        color: #ffffff;
        border-color: #1F5EDB;
        transform: scale(1.08);
        outline: none;
    }

    .dark-mode .unn-help-trigger,
    .dark .unn-help-trigger {
        background: rgba(96, 165, 250, 0.14);
        border-color: rgba(96, 165, 250, 0.35);
        color: #93c5fd;
    }

    .dark-mode .unn-help-trigger:hover,
    .dark .unn-help-trigger:hover {
        background: #2563eb;
        border-color: #3b82f6;
        color: #ffffff;
    }

    /* Backdrop */
    .unn-help-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(2px);
        z-index: 10540;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.28s ease, visibility 0.28s ease;
    }

    .unn-help-backdrop.is-open {
        opacity: 1;
        visibility: visible;
    }

    /* Gaveta deslizando da direita para a esquerda */
    .unn-help-drawer {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        max-width: 440px;
        background: #ffffff;
        color: #1e293b;
        border-left: 1px solid #e2e8f0;
        box-shadow: -12px 0 36px rgba(15, 23, 42, 0.16);
        z-index: 10550;
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .unn-help-drawer.is-open {
        transform: translateX(0);
    }

    .unn-help-drawer-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 20px 22px 16px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .unn-help-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #1F5EDB;
        background: rgba(31, 94, 219, 0.1);
        padding: 3px 9px;
        border-radius: 9999px;
        margin-bottom: 6px;
    }

    .unn-help-title {
        margin: 0;
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.3;
    }

    .unn-help-close {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }

    .unn-help-close:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .unn-help-drawer-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px 22px 28px;
    }

    .unn-help-summary {
        font-size: 14px;
        line-height: 1.6;
        color: #334155;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 18px;
    }

    .unn-help-section {
        margin-bottom: 20px;
    }

    .unn-help-section-title {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .unn-help-steps {
        margin: 0;
        padding-left: 0;
        list-style: none;
        counter-reset: unn-step;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .unn-help-steps li {
        counter-increment: unn-step;
        position: relative;
        padding: 10px 12px 10px 40px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        font-size: 13.5px;
        line-height: 1.5;
        color: #334155;
    }

    .unn-help-steps li::before {
        content: counter(unn-step);
        position: absolute;
        left: 10px;
        top: 10px;
        width: 22px;
        height: 22px;
        border-radius: 9999px;
        background: #1F5EDB;
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .unn-help-links {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .unn-help-link-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 11px 14px;
        border-radius: 10px;
        border: 1px solid rgba(31, 94, 219, 0.25);
        background: rgba(31, 94, 219, 0.06);
        color: #1d4ed8 !important;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none !important;
        transition: all 0.2s ease;
    }

    .unn-help-link-btn:hover {
        background: #1F5EDB;
        color: #ffffff !important;
        border-color: #1F5EDB;
    }

    .unn-help-tip {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 13px 15px;
        border-radius: 12px;
        border: 1px solid #fde68a;
        background: #fffbeb;
        color: #92400e;
        font-size: 13px;
        line-height: 1.5;
    }

    .unn-help-tip-icon {
        font-size: 15px;
        margin-top: 2px;
        flex-shrink: 0;
    }

    /* Dark Mode harmonizado (AdminLTE .dark-mode e Tailwind .dark) */
    .dark-mode .unn-help-drawer,
    .dark .unn-help-drawer {
        background: #0f172a;
        color: #e2e8f0;
        border-left-color: #334155;
        box-shadow: -14px 0 40px rgba(0, 0, 0, 0.55);
    }

    .dark-mode .unn-help-drawer-header,
    .dark .unn-help-drawer-header {
        background: #1e293b;
        border-bottom-color: #334155;
    }

    .dark-mode .unn-help-badge,
    .dark .unn-help-badge {
        background: rgba(96, 165, 250, 0.16);
        color: #93c5fd;
    }

    .dark-mode .unn-help-title,
    .dark .unn-help-title {
        color: #f8fafc;
    }

    .dark-mode .unn-help-close,
    .dark .unn-help-close {
        background: #0f172a;
        border-color: #334155;
        color: #94a3b8;
    }

    .dark-mode .unn-help-close:hover,
    .dark .unn-help-close:hover {
        background: #334155;
        color: #f8fafc;
    }

    .dark-mode .unn-help-summary,
    .dark .unn-help-summary {
        background: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }

    .dark-mode .unn-help-section-title,
    .dark .unn-help-section-title {
        color: #94a3b8;
    }

    .dark-mode .unn-help-steps li,
    .dark .unn-help-steps li {
        background: #1e293b;
        border-color: #334155;
        color: #e2e8f0;
    }

    .dark-mode .unn-help-steps li::before,
    .dark .unn-help-steps li::before {
        background: #2563eb;
    }

    .dark-mode .unn-help-link-btn,
    .dark .unn-help-link-btn {
        background: rgba(37, 99, 235, 0.16);
        border-color: rgba(96, 165, 250, 0.35);
        color: #93c5fd !important;
    }

    .dark-mode .unn-help-link-btn:hover,
    .dark .unn-help-link-btn:hover {
        background: #2563eb;
        border-color: #3b82f6;
        color: #ffffff !important;
    }

    .dark-mode .unn-help-tip,
    .dark .unn-help-tip {
        background: rgba(245, 158, 11, 0.12);
        border-color: rgba(245, 158, 11, 0.32);
        color: #fcd34d;
    }
</style>

<script>
(function () {
    if (window.UNNHelpDrawer) return;

    const registry = {};

    function getElements() {
        return {
            drawer: document.getElementById('unn-help-drawer'),
            backdrop: document.getElementById('unn-help-drawer-backdrop'),
            closeBtn: document.getElementById('unn-help-drawer-close'),
            badge: document.getElementById('unn-help-drawer-badge'),
            title: document.getElementById('unn-help-drawer-title'),
            summary: document.getElementById('unn-help-drawer-summary'),
            stepsWrap: document.getElementById('unn-help-drawer-steps-wrap'),
            steps: document.getElementById('unn-help-drawer-steps'),
            linksWrap: document.getElementById('unn-help-drawer-links-wrap'),
            links: document.getElementById('unn-help-drawer-links'),
            tipWrap: document.getElementById('unn-help-drawer-tip-wrap'),
            tip: document.getElementById('unn-help-drawer-tip')
        };
    }

    function openDrawer(config) {
        const els = getElements();
        if (!els.drawer || !config) return;

        els.badge.textContent = config.badge || 'Guia de Configuração';
        els.title.textContent = config.title || 'Instruções do Campo';
        els.summary.textContent = config.summary || '';

        els.steps.innerHTML = '';
        if (Array.isArray(config.steps) && config.steps.length > 0) {
            config.steps.forEach(function (stepText) {
                const li = document.createElement('li');
                li.textContent = stepText;
                els.steps.appendChild(li);
            });
            els.stepsWrap.style.display = 'block';
        } else {
            els.stepsWrap.style.display = 'none';
        }

        els.links.innerHTML = '';
        if (Array.isArray(config.links) && config.links.length > 0) {
            config.links.forEach(function (item) {
                const a = document.createElement('a');
                a.href = item.url;
                a.className = 'unn-help-link-btn';
                if (item.external !== false) {
                    a.target = '_blank';
                    a.rel = 'noopener noreferrer';
                }
                const span = document.createElement('span');
                span.textContent = item.label;
                const icon = document.createElement('i');
                icon.className = item.external === false ? 'fas fa-arrow-right' : 'fas fa-external-link-alt';
                a.appendChild(span);
                a.appendChild(icon);
                els.links.appendChild(a);
            });
            els.linksWrap.style.display = 'block';
        } else {
            els.linksWrap.style.display = 'none';
        }

        if (config.tip) {
            els.tip.textContent = config.tip;
            els.tipWrap.style.display = 'flex';
        } else {
            els.tipWrap.style.display = 'none';
        }

        els.backdrop.classList.add('is-open');
        els.drawer.classList.add('is-open');
        els.backdrop.setAttribute('aria-hidden', 'false');
        els.drawer.setAttribute('aria-hidden', 'false');
    }

    function closeDrawer() {
        const els = getElements();
        if (!els.drawer) return;
        els.backdrop.classList.remove('is-open');
        els.drawer.classList.remove('is-open');
        els.backdrop.setAttribute('aria-hidden', 'true');
        els.drawer.setAttribute('aria-hidden', 'true');
    }

    function findLabelForField(field) {
        if (!field) return null;

        if (field.id) {
            const explicitLabel = document.querySelector('label[for="' + CSS.escape(field.id) + '"]');
            if (explicitLabel) return explicitLabel;
        }

        const container = field.closest('.form-group, div');
        if (container) {
            const directLabel = container.querySelector(':scope > label');
            if (directLabel) return directLabel;

            const parentContainer = container.parentElement;
            if (parentContainer) {
                const parentLabel = parentContainer.querySelector(':scope > label');
                if (parentLabel) return parentLabel;
            }
        }

        return null;
    }

    function autoAttach(root) {
        const scope = root || document;
        const fields = scope.querySelectorAll('input[name]:not([type="hidden"]), select[name], textarea[name]');

        fields.forEach(function (field) {
            const rawName = (field.getAttribute('name') || '').replace(/\[\]$/, '').trim();
            if (!rawName || !registry[rawName]) return;

            const label = findLabelForField(field);
            if (!label) return;
            if (label.querySelector('.unn-help-trigger')) return;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'unn-help-trigger';
            btn.setAttribute('data-help-drawer', rawName);
            btn.setAttribute('title', 'Ver instruções passo a passo');
            btn.setAttribute('aria-label', 'Ver instruções de ' + rawName);
            btn.innerHTML = '<i class="fas fa-question"></i>';

            label.appendChild(btn);
        });
    }

    window.UNNHelpDrawer = {
        register: function (items) {
            Object.assign(registry, items || {});
        },
        open: function (keyOrConfig) {
            if (typeof keyOrConfig === 'string') {
                if (registry[keyOrConfig]) {
                    openDrawer(registry[keyOrConfig]);
                }
                return;
            }
            openDrawer(keyOrConfig);
        },
        close: closeDrawer,
        autoAttach: autoAttach
    };

    document.addEventListener('DOMContentLoaded', function () {
        autoAttach(document);
    });

    document.addEventListener('pjax:complete', function () {
        autoAttach(document);
    });

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-help-drawer]');
        if (trigger) {
            event.preventDefault();
            event.stopPropagation();
            const key = trigger.getAttribute('data-help-drawer');
            window.UNNHelpDrawer.open(key);
            return;
        }

        if (event.target.closest('#unn-help-drawer-close') || event.target.id === 'unn-help-drawer-backdrop') {
            closeDrawer();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDrawer();
        }
    });
})();
</script>
<script src="{{ asset('js/unn-help-catalog.js') }}?v=2.8.35" defer></script>
