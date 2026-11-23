{{-- Partial Global: Scroll Reveal Bidirecional Padronizado em Todo o Site Público UNN --}}
<style>
    /* ==========================================================================
       ANIMAÇÕES GLOBAIS DE SCROLL BIDIRECIONAIS (Scroll Down & Scroll Up)
       Aplicadas automaticamente em todo o site público sem exceção de página
       ========================================================================== */
    .scroll-reveal,
    .reveal-on-scroll,
    .reveal-up,
    .reveal-left,
    .reveal-right,
    .reveal-scale,
    .step-block,
    .plan-block,
    .cta-block,
    [data-scroll-reveal] {
        opacity: 0;
        transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        will-change: opacity, transform;
    }

    /* Estados Iniciais (fora da viewport) */
    .scroll-reveal:not(.is-visible),
    .reveal-on-scroll:not(.is-visible),
    .reveal-up:not(.is-visible),
    .step-block:not(.is-visible),
    .plan-block:not(.is-visible),
    .cta-block:not(.is-visible),
    [data-scroll-reveal]:not(.is-visible) {
        transform: translateY(28px);
    }

    .reveal-left:not(.is-visible) {
        transform: translateX(-32px);
    }

    .reveal-right:not(.is-visible) {
        transform: translateX(32px);
    }

    .reveal-scale:not(.is-visible) {
        transform: scale(0.93) translateY(20px);
    }

    /* Estados Visíveis (ao entrar na viewport em qualquer direção) */
    .scroll-reveal.is-visible,
    .reveal-on-scroll.is-visible,
    .reveal-up.is-visible,
    .reveal-left.is-visible,
    .reveal-right.is-visible,
    .reveal-scale.is-visible,
    .step-block.is-visible,
    .plan-block.is-visible,
    .cta-block.is-visible,
    [data-scroll-reveal].is-visible {
        opacity: 1 !important;
        transform: translateY(0) translateX(0) scale(1) !important;
    }

    /* Animação específica dos elementos internos dos passos (como-funciona) */
    .step-block .step-badge {
        transition: transform 0.65s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.5s ease-out;
        will-change: transform, opacity;
    }

    .step-block .step-card {
        transition: transform 0.65s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.6s ease-out, box-shadow 0.3s ease;
        will-change: transform, opacity;
    }

    .step-block:not(.is-visible) .step-badge {
        opacity: 0;
        transform: scale(0.65) rotate(-10deg);
    }

    .step-block.step-normal:not(.is-visible) .step-card {
        opacity: 0;
        transform: translateX(35px);
    }

    .step-block.step-reverse:not(.is-visible) .step-card {
        opacity: 0;
        transform: translateX(-35px);
    }

    .step-block.is-visible .step-badge {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }

    .step-block.is-visible .step-card {
        opacity: 1;
        transform: translateX(0);
    }

    .step-block.is-visible .step-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -15px rgba(31, 94, 219, 0.15), 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    /* Cascata (stagger) automática para grupos de elementos filhos */
    .reveal-stagger > *:nth-child(1) { transition-delay: 60ms; }
    .reveal-stagger > *:nth-child(2) { transition-delay: 130ms; }
    .reveal-stagger > *:nth-child(3) { transition-delay: 200ms; }
    .reveal-stagger > *:nth-child(4) { transition-delay: 270ms; }
    .reveal-stagger > *:nth-child(5) { transition-delay: 340ms; }
    .reveal-stagger > *:nth-child(6) { transition-delay: 410ms; }

    /* Acessibilidade: Desabilitar para usuários que solicitaram redução de movimento */
    @media (prefers-reduced-motion: reduce) {
        .scroll-reveal,
        .reveal-on-scroll,
        .reveal-up,
        .reveal-left,
        .reveal-right,
        .reveal-scale,
        .step-block,
        .plan-block,
        .cta-block,
        [data-scroll-reveal] {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }
    }
</style>

<script>
    (function () {
        const explicitSelector = '.scroll-reveal, .reveal-on-scroll, .reveal-up, .reveal-left, .reveal-right, .reveal-scale, .step-block, .plan-block, .cta-block, [data-scroll-reveal]';

        let scrollObserver = null;

        // Auto-descobre e marca todas as seções e blocos do site público dentro de <main>
        function autoTagPublicBlocks() {
            const main = document.querySelector('main');
            if (!main) return;

            const candidates = main.querySelectorAll('section, article, .home-journey-lead, .home-journey-card');
            candidates.forEach(function (el) {
                if (
                    el.closest('#floating-chat-dock') ||
                    el.closest('#navbar') ||
                    el.closest('.modal') ||
                    el.closest('.dropdown') ||
                    el.classList.contains('no-scroll-reveal') ||
                    el.id === 'preloader' ||
                    el.id === 'unn-preloader' ||
                    el.closest('#unn-preloader')
                ) {
                    return;
                }

                if (!el.classList.contains('scroll-reveal') && !el.matches(explicitSelector)) {
                    el.classList.add('scroll-reveal');
                }
            });
        }

        function initScrollReveal() {
            autoTagPublicBlocks();

            const elements = document.querySelectorAll(explicitSelector);
            if (!elements.length) return;

            if ('IntersectionObserver' in window) {
                if (!scrollObserver) {
                    scrollObserver = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('is-visible');
                            } else {
                                // Ao sair da tela, remove a classe permitindo reanimar em qualquer direção (scroll up e down)
                                entry.target.classList.remove('is-visible');
                            }
                        });
                    }, {
                        root: null,
                        rootMargin: '0px 0px -20px 0px',
                        threshold: 0.05
                    });
                }

                elements.forEach(function (el) {
                    if (!el.dataset.scrollObserverBound) {
                        el.dataset.scrollObserverBound = '1';
                        scrollObserver.observe(el);

                        // Se o elemento já estiver na viewport inicial, exibe suavemente
                        const rect = el.getBoundingClientRect();
                        if (rect.top < window.innerHeight && rect.bottom > 0) {
                            el.classList.add('is-visible');
                        }
                    }
                });
            } else {
                elements.forEach(function (el) {
                    el.classList.add('is-visible');
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initScrollReveal);
        } else {
            initScrollReveal();
        }

        // Interoperabilidade global para chamadas via AJAX, paginação e modais
        window.UNNScrollReveal = {
            refresh: function () {
                initScrollReveal();
            }
        };
    })();
</script>
