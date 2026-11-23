{{-- Partial Global: Scroll Reveal Bidirecional UNN --}}
<style>
    /* ==========================================================================
       ANIMAÇÕES GLOBAIS DE SCROLL BIDIRECIONAIS (Scroll Down & Scroll Up)
       ========================================================================== */
    .scroll-reveal,
    .reveal-on-scroll,
    .reveal-up,
    .reveal-left,
    .reveal-right,
    .reveal-scale,
    .step-block,
    .plan-block,
    .cta-block {
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
    .cta-block:not(.is-visible) {
        transform: translateY(32px);
    }

    .reveal-left:not(.is-visible) {
        transform: translateX(-36px);
    }

    .reveal-right:not(.is-visible) {
        transform: translateX(36px);
    }

    .reveal-scale:not(.is-visible) {
        transform: scale(0.92) translateY(24px);
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
    .cta-block.is-visible {
        opacity: 1 !important;
        transform: translateY(0) translateX(0) scale(1) !important;
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
        .cta-block {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }
    }
</style>

<script>
    (function () {
        const selector = '.scroll-reveal, .reveal-on-scroll, .reveal-up, .reveal-left, .reveal-right, .reveal-scale, .step-block, .plan-block, .cta-block';

        let scrollObserver = null;

        function initScrollReveal() {
            const elements = document.querySelectorAll(selector);
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
                        rootMargin: '0px 0px -15px 0px',
                        threshold: 0.05
                    });
                }

                elements.forEach(function (el) {
                    if (!el.dataset.scrollObserverBound) {
                        el.dataset.scrollObserverBound = '1';
                        scrollObserver.observe(el);
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
            refresh: initScrollReveal
        };
    })();
</script>
