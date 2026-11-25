/* ==========================================================================
   SOMOS UNN — Runtime Oficial AdminLTE 4 (Bootstrap 5.3 + OverlayScrollbars v2)
   + Motor de Animações Intuitivas em Tempo Real (Scroll, KPIs e Gráficos)
   ========================================================================== */
(function () {
    'use strict';

    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let lastScrollY = window.scrollY || 0;
    let scrollDirection = 'down';
    let revealObserver = null;
    let countObserver = null;
    let chartObserver = null;

    // 1. Inicializa OverlayScrollbars v2 na sidebar (padrão oficial AdminLTE 4)
    function initSidebarScrollbars() {
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
    }

    // 2. Barra de progresso de rolagem em tempo real + sombra dinâmica da navbar
    function initScrollProgress() {
        if (prefersReducedMotion) return;

        let bar = document.getElementById('unnScrollProgressBar');
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'unnScrollProgressBar';
            bar.className = 'unn-scroll-progress';
            document.body.appendChild(bar);
        }

        const header = document.querySelector('.app-header.navbar');
        let ticking = false;

        const update = function () {
            ticking = false;
            const currentY = window.scrollY || document.documentElement.scrollTop || 0;
            scrollDirection = currentY >= lastScrollY ? 'down' : 'up';
            lastScrollY = currentY;

            const docHeight = Math.max(
                document.documentElement.scrollHeight - window.innerHeight,
                1
            );
            const progress = Math.min(100, Math.max(0, (currentY / docHeight) * 100));
            bar.style.width = progress + '%';

            if (header) {
                header.classList.toggle('is-scrolled', currentY > 12);
            }
        };

        window.addEventListener('scroll', function () {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(update);
            }
        }, { passive: true });

        update();
    }

    // 3. Scroll Reveal bidirecional inteligente (ao rolar para baixo e para cima)
    function initScrollReveal(root) {
        const scope = root || document.getElementById('pjax-container') || document;
        const targets = scope.querySelectorAll('.small-box, .info-box, .card, .unn-section-box');

        if (prefersReducedMotion || typeof IntersectionObserver === 'undefined') {
            targets.forEach(function (el) {
                el.classList.remove('unn-reveal', 'unn-reveal-up');
                el.classList.add('is-visible');
            });
            return;
        }

        if (revealObserver) {
            revealObserver.disconnect();
        }

        revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                const el = entry.target;
                if (entry.isIntersecting) {
                    el.classList.add('is-visible');
                    el.classList.remove('unn-reveal-up');
                } else {
                    const rect = entry.boundingClientRect;
                    // Re-prepara animação suave se o elemento sair completamente da viewport
                    if (rect.top > window.innerHeight + 60) {
                        el.classList.remove('is-visible', 'unn-reveal-up');
                    } else if (rect.bottom < -60) {
                        el.classList.remove('is-visible');
                        el.classList.add('unn-reveal-up');
                    }
                }
            });
        }, {
            threshold: 0.06,
            rootMargin: '0px 0px -24px 0px'
        });

        targets.forEach(function (el, idx) {
            // Não anima elementos dentro de modais, dropdowns, offcanvas ou abas internas ocultas
            if (el.closest('.modal, .dropdown-menu, .offcanvas, .tab-pane, .gateway-pane, .main-gateway-pane')) {
                el.classList.remove('unn-reveal', 'unn-reveal-up');
                el.classList.add('is-visible');
                return;
            }

            const rect = el.getBoundingClientRect();
            const isAboveFold = rect.top < window.innerHeight * 0.95;

            el.classList.add('unn-reveal');
            el.style.transitionDelay = isAboveFold ? Math.min(idx * 35, 220) + 'ms' : '0ms';

            if (isAboveFold) {
                requestAnimationFrame(function () {
                    el.classList.add('is-visible');
                });
            }
            revealObserver.observe(el);
        });
    }

    // 4. Contador numérico animado em tempo real nos KPIs (.small-box h3 e .info-box-number)
    function animateCounterElement(el) {
        if (el.dataset.unnCountAnimated === '1') return;
        const raw = (el.textContent || '').trim();
        if (!raw) return;

        // Suporta formatos: "R$ 12.345,67", "1.250", "98,5%", "42"
        const match = raw.match(/^(R\$\s*)?(-?[\d\.]+(?:,\d+)?)(%?)$/);
        if (!match) return;

        const prefix = match[1] || '';
        const numberPart = match[2];
        const suffix = match[3] || '';
        const hasDecimals = numberPart.includes(',');
        const decimals = hasDecimals ? numberPart.split(',')[1].length : 0;
        const target = parseFloat(numberPart.replace(/\./g, '').replace(',', '.'));

        if (!Number.isFinite(target) || target === 0) return;

        el.dataset.unnCountAnimated = '1';
        const duration = 850;
        const startTime = performance.now();

        const formatNumber = function (val) {
            return val.toLocaleString('pt-BR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        };

        const step = function (now) {
            const elapsed = now - startTime;
            const t = Math.min(1, elapsed / duration);
            // easeOutExpo
            const eased = t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
            const current = target * eased;
            el.textContent = prefix + formatNumber(current) + suffix;
            if (t < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = raw;
            }
        };

        requestAnimationFrame(step);
    }

    function initKpiCounters(root) {
        if (prefersReducedMotion) return;
        const scope = root || document.getElementById('pjax-container') || document;
        const nodes = scope.querySelectorAll('.small-box h3, .info-box-number');

        if (typeof IntersectionObserver === 'undefined') {
            nodes.forEach(animateCounterElement);
            return;
        }

        if (countObserver) countObserver.disconnect();
        countObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounterElement(entry.target);
                    countObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.25 });

        nodes.forEach(function (el) {
            countObserver.observe(el);
        });
    }

    // 5. Defaults modernos + animação em tempo real para gráficos Chart.js ao entrar na tela
    function initChartAnimations(root) {
        if (typeof window.Chart !== 'undefined' && window.Chart.defaults) {
            window.Chart.defaults.animation = Object.assign({}, window.Chart.defaults.animation, {
                duration: prefersReducedMotion ? 0 : 950,
                easing: 'easeOutQuart'
            });
            if (window.Chart.defaults.plugins && window.Chart.defaults.plugins.tooltip) {
                window.Chart.defaults.plugins.tooltip.cornerRadius = 10;
                window.Chart.defaults.plugins.tooltip.padding = 10;
            }
        }

        if (prefersReducedMotion || typeof IntersectionObserver === 'undefined') return;
        const scope = root || document.getElementById('pjax-container') || document;
        const canvases = scope.querySelectorAll('canvas');
        if (!canvases.length) return;

        if (chartObserver) chartObserver.disconnect();
        chartObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                const canvas = entry.target;
                if (canvas.dataset.unnChartPlayed === '1') return;

                if (typeof window.Chart !== 'undefined' && typeof window.Chart.getChart === 'function') {
                    const chart = window.Chart.getChart(canvas);
                    if (chart && typeof chart.reset === 'function' && typeof chart.update === 'function') {
                        canvas.dataset.unnChartPlayed = '1';
                        chart.reset();
                        chart.update();
                    }
                }
            });
        }, { threshold: 0.2 });

        canvases.forEach(function (canvas) {
            chartObserver.observe(canvas);
        });
    }

    // 6. Tooltips Bootstrap 5
    function initBootstrapTooltips(root) {
        if (!(window.bootstrap && window.bootstrap.Tooltip)) return;
        const scope = root || document;
        scope.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            window.bootstrap.Tooltip.getOrCreateInstance(el);
        });
    }

    // 6.5. Hover com detalhes reais (nome, peso, dimensões px, formato) em todos os campos arrasta-e-solta
    const headSizeCache = new Map();

    function formatFileWeight(bytes) {
        const n = Number(bytes || 0);
        if (!Number.isFinite(n) || n <= 0) return null;
        const units = ['B', 'KB', 'MB', 'GB'];
        const exp = Math.min(Math.floor(Math.log(n) / Math.log(1024)), units.length - 1);
        const val = n / Math.pow(1024, exp);
        return val.toFixed(val >= 100 || exp === 0 ? 0 : 2) + ' ' + units[exp];
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function ensureHoverCard(container) {
        let card = container.querySelector(':scope > .unn-upload-hover-card');
        if (!card) {
            card = document.createElement('div');
            card.className = 'unn-upload-hover-card';
            container.appendChild(card);
        }
        return card;
    }

    function renderHoverCard(container, info) {
        const card = ensureHoverCard(container);
        if (!info || (!info.name && !info.dimensions && !info.size)) {
            card.classList.remove('has-details');
            card.innerHTML = '';
            return;
        }

        const rows = [];
        if (info.dimensions) rows.push('<span>Dimensões:</span><strong>' + escapeHtml(info.dimensions) + '</strong>');
        if (info.size) rows.push('<span>Peso:</span><strong>' + escapeHtml(info.size) + '</strong>');
        if (info.type) rows.push('<span>Formato:</span><strong>' + escapeHtml(info.type) + '</strong>');

        card.innerHTML =
            '<div class="unn-upload-hover-title"><i class="fas fa-circle-info me-1"></i>' + escapeHtml(info.name || 'Arquivo carregado') + '</div>' +
            '<div class="unn-upload-hover-grid">' + rows.join('') + '</div>';
        card.classList.add('has-details');
    }

    function inspectExistingUrl(container, url) {
        if (!url) {
            renderHoverCard(container, null);
            return;
        }

        const cleanUrl = String(url).split('?')[0].split('#')[0];
        const fileName = cleanUrl.split('/').pop() || 'Arquivo atual';
        const ext = (fileName.includes('.') ? fileName.split('.').pop() : '').toUpperCase();
        const info = { name: fileName, type: ext || 'Arquivo', size: null, dimensions: null };

        // Dimensões via <img> já renderizada ou preload leve
        const imgEl = container.querySelector('.upload-preview img, .unn-dz-preview-img, .unn-dz-current img');
        if (imgEl && imgEl.naturalWidth > 0) {
            info.dimensions = imgEl.naturalWidth + ' × ' + imgEl.naturalHeight + ' px';
            renderHoverCard(container, info);
        } else if (/\.(png|jpe?g|gif|webp|svg|ico)$/i.test(cleanUrl)) {
            const probe = new Image();
            probe.onload = function () {
                if (probe.naturalWidth > 0) {
                    info.dimensions = probe.naturalWidth + ' × ' + probe.naturalHeight + ' px';
                    renderHoverCard(container, info);
                }
            };
            probe.src = url;
        }

        // Peso real via HEAD Content-Length (com cache em memória)
        if (headSizeCache.has(url)) {
            info.size = headSizeCache.get(url);
            renderHoverCard(container, info);
        } else if (typeof window.fetch === 'function') {
            fetch(url, { method: 'HEAD', credentials: 'same-origin' })
                .then(function (resp) {
                    const len = resp.headers.get('Content-Length');
                    const formatted = len ? formatFileWeight(parseInt(len, 10)) : null;
                    headSizeCache.set(url, formatted);
                    if (formatted) {
                        info.size = formatted;
                        renderHoverCard(container, info);
                    }
                })
                .catch(function () { /* ignora CORS/erro silenciosamente */ });
        }

        renderHoverCard(container, info);
    }

    function inspectLocalFile(container, file) {
        if (!file) return;
        const ext = (file.name && file.name.includes('.') ? file.name.split('.').pop() : '').toUpperCase();
        const info = {
            name: file.name,
            size: formatFileWeight(file.size),
            type: file.type || ext || 'Arquivo',
            dimensions: null
        };
        renderHoverCard(container, info);

        if (file.type && file.type.startsWith('image/')) {
            const objUrl = URL.createObjectURL(file);
            const img = new Image();
            img.onload = function () {
                if (img.naturalWidth > 0) {
                    info.dimensions = img.naturalWidth + ' × ' + img.naturalHeight + ' px';
                    renderHoverCard(container, info);
                }
                URL.revokeObjectURL(objUrl);
            };
            img.onerror = function () { URL.revokeObjectURL(objUrl); };
            img.src = objUrl;
        }
    }

    function initUploadHoverDetails(root) {
        const scope = root || document;
        scope.querySelectorAll('.upload-box, .unn-dz-wrap').forEach(function (container) {
            if (container.dataset.unnHoverBound === '1') return;
            container.dataset.unnHoverBound = '1';

            const existingUrl = container.getAttribute('data-existing-url')
                || container.querySelector('.unn-dz-current-link')?.getAttribute('href')
                || container.querySelector('.upload-preview img')?.getAttribute('src')
                || '';

            if (existingUrl) {
                inspectExistingUrl(container, existingUrl);
            }

            const input = container.querySelector('input[type="file"]');
            if (input) {
                input.addEventListener('change', function () {
                    const f = this.files && this.files[0] ? this.files[0] : null;
                    if (f) inspectLocalFile(container, f);
                });
            }

            const removeBtn = container.querySelector('.upload-remove, .unn-dz-remove');
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    renderHoverCard(container, null);
                });
            }
        });
    }

    // API pública para refresh em PJAX ou carregamentos dinâmicos
    window.UNNAdminMotion = {
        refresh: function (root) {
            initScrollReveal(root);
            initKpiCounters(root);
            initChartAnimations(root);
            initBootstrapTooltips(root);
            initUploadHoverDetails(root);
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        initSidebarScrollbars();
        initScrollProgress();
        window.UNNAdminMotion.refresh(document.getElementById('pjax-container') || document);
    });

    if (window.jQuery) {
        window.jQuery(document).on('pjax:end', function () {
            window.UNNAdminMotion.refresh(document.getElementById('pjax-container') || document);
        });
    }

    // 7. Adapter jQuery -> Bootstrap 5 Modal (para chamadas legadas $('#meuModal').modal('show'|'hide'))
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

    // 8. Sincronizador de tema Dark / Light no padrão Bootstrap 5.3 (data-bs-theme)
    window.UNNAdminTheme = {
        apply: function (theme) {
            const normalized = theme === 'dark' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', normalized);
            document.body.setAttribute('data-bs-theme', normalized);
            document.body.classList.toggle('dark-mode', normalized === 'dark');
            document.documentElement.classList.toggle('dark', normalized === 'dark');
        }
    };

    // 9. Feriados Nacionais do Brasil para FullCalendar (fixos + móveis pela Páscoa)
    window.UNNBrazilianHolidays = (function () {
        function pad(n) {
            return String(n).padStart(2, '0');
        }

        function formatYmd(date) {
            return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
        }

        function addDays(baseDate, days) {
            const d = new Date(baseDate.getFullYear(), baseDate.getMonth(), baseDate.getDate());
            d.setDate(d.getDate() + days);
            return d;
        }

        // Algoritmo de Meeus/Jones/Butcher para Páscoa gregoriana
        function getEasterDate(year) {
            const a = year % 19;
            const b = Math.floor(year / 100);
            const c = year % 100;
            const d = Math.floor(b / 4);
            const e = b % 4;
            const f = Math.floor((b + 8) / 25);
            const g = Math.floor((b - f + 1) / 3);
            const h = (19 * a + b - d - g + 15) % 30;
            const i = Math.floor(c / 4);
            const k = c % 4;
            const l = (32 + 2 * e + 2 * i - h - k) % 7;
            const m = Math.floor((a + 11 * h + 22 * l) / 451);
            const month = Math.floor((h + l - 7 * m + 114) / 31);
            const day = ((h + l - 7 * m + 114) % 31) + 1;
            return new Date(year, month - 1, day);
        }

        function getEventsForYear(year) {
            const easter = getEasterDate(year);
            const items = [
                { date: year + '-01-01', title: 'Confraternização Universal' },
                { date: formatYmd(addDays(easter, -47)), title: 'Carnaval' },
                { date: formatYmd(addDays(easter, -2)), title: 'Sexta-feira Santa' },
                { date: formatYmd(easter), title: 'Páscoa' },
                { date: year + '-04-21', title: 'Tiradentes' },
                { date: year + '-05-01', title: 'Dia do Trabalho' },
                { date: formatYmd(addDays(easter, 60)), title: 'Corpus Christi' },
                { date: year + '-09-07', title: 'Independência do Brasil' },
                { date: year + '-10-12', title: 'Nossa Sra. Aparecida' },
                { date: year + '-11-02', title: 'Finados' },
                { date: year + '-11-15', title: 'Proclamação da República' },
                { date: year + '-11-20', title: 'Dia da Consciência Negra' },
                { date: year + '-12-25', title: 'Natal' }
            ];

            return items.map(function (h, idx) {
                return {
                    id: 'br-holiday-' + year + '-' + idx,
                    title: '★ ' + h.title,
                    start: h.date,
                    allDay: true,
                    editable: false,
                    startEditable: false,
                    durationEditable: false,
                    classNames: ['unn-holiday-event'],
                    extendedProps: {
                        isHoliday: true,
                        holidayName: h.title
                    }
                };
            });
        }

        return {
            getEventsForYear: getEventsForYear,
            eventSource: function (fetchInfo, successCallback) {
                const startYear = fetchInfo.start.getFullYear();
                const endYear = fetchInfo.end.getFullYear();
                const all = [];
                for (let y = startYear; y <= endYear; y++) {
                    all.push.apply(all, getEventsForYear(y));
                }
                successCallback(all);
            }
        };
    })();
})();
