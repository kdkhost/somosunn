
// Shepherd.js via CDN
// Tour dinâmico: navegação automática entre rotas do painel, persistência de etapa, destaques visuais
(function() {
    if (!window.Shepherd) return;

    // Passos do tour: cada um com rota, seletor e explicação
    const steps = [
        {
            route: '/panel/dashboard',
            element: '#painel-dashboard',
            title: 'Visão Geral',
            text: 'Aqui você acompanha seu progresso, novidades e atalhos rápidos.'
        },
        {
            route: '/panel/profile/edit',
            element: '#menu-perfil',
            title: 'Perfil',
            text: 'Edite seus dados, foto e preferências neste menu.'
        },
        {
            route: '/panel/marketplace',
            element: '#menu-marketplace',
            title: 'Marketplace',
            text: 'Acesse cursos, mentorias e eventos disponíveis para você.'
        },
        {
            route: '/panel/marketplace/sales',
            element: '.bg-white:has(.fa-receipt)',
            title: 'Minhas Vendas',
            text: 'Veja todas as suas vendas realizadas na plataforma.'
        },
        {
            route: '/panel/marketplace/payments',
            element: '.bg-white:has(.fa-credit-card)',
            title: 'Pagamentos',
            text: 'Gerencie e acompanhe seus recebimentos.'
        },
        {
            route: '/panel/marketplace/gateway',
            element: '.bg-white:has(.fa-cog)',
            title: 'Configurações de Pagamento',
            text: 'Configure suas credenciais e métodos de recebimento.'
        }
    ];

    // Utilitário para navegar entre rotas do painel
    function goTo(route) {
        if (window.location.pathname !== route) {
            window.location.href = route;
        }
    }

    // Persistência da etapa atual
    function getStep() {
        return parseInt(localStorage.getItem('memberTourStep') || '0', 10);
    }
    function setStep(idx) {
        localStorage.setItem('memberTourStep', idx);
    }
    function clearTour() {
        localStorage.removeItem('memberTourStep');
        localStorage.setItem('memberTourCompleted', '1');
    }
    function canShow() {
        return !localStorage.getItem('memberTourCompleted');
    }

    // Inicia o tour na etapa correta
    function startTour() {
        let idx = getStep();
        if (idx >= steps.length) {
            clearTour();
            return;
        }
        const step = steps[idx];
        if (window.location.pathname !== step.route) {
            goTo(step.route);
            setTimeout(startTour, 800);
            return;
        }
        // Espera o elemento aparecer
        const el = document.querySelector(step.element);
        if (!el) {
            setTimeout(startTour, 400);
            return;
        }
        const tour = new Shepherd.Tour({
            defaultStepOptions: {
                scrollTo: { behavior: 'smooth', block: 'center' },
                cancelIcon: { enabled: true },
                classes: 'shadow-lg bg-white rounded',
                modalOverlayOpeningPadding: 6,
                modalOverlayOpeningRadius: 6
            },
            useModalOverlay: true
        });
        tour.addStep({
            title: step.title,
            text: step.text,
            attachTo: { element: step.element, on: 'right' },
            buttons: [
                idx > 0 ? {
                    text: 'Voltar',
                    action: () => {
                        setStep(idx - 1);
                        startTour();
                    }
                } : null,
                idx < steps.length - 1 ? {
                    text: 'Próximo',
                    action: () => {
                        setStep(idx + 1);
                        startTour();
                    }
                } : {
                    text: 'Finalizar',
                    action: () => {
                        clearTour();
                        tour.complete();
                    }
                }
            ].filter(Boolean)
        });
        tour.start();
    }

    // Botão para reiniciar tour
    window.MemberTour = {
        reset: function() {
            localStorage.removeItem('memberTourCompleted');
            localStorage.setItem('memberTourStep', '0');
            startTour();
        },
        canShow
    };

    // Inicialização automática se nunca foi concluído
    window.addEventListener('DOMContentLoaded', function() {
        if (canShow()) {
            setTimeout(startTour, 800);
        }
    });
})();
