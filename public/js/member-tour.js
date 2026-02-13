// Shepherd.js via CDN
// https://shepherdjs.dev/
// Este arquivo inicializa e controla o tour guiado do painel do membro

window.MemberTour = {
    tour: null,
    start: function() {
        if (!window.Shepherd) return;
        this.tour = new Shepherd.Tour({
            defaultStepOptions: {
                scrollTo: { behavior: 'smooth', block: 'center' },
                cancelIcon: { enabled: true },
                classes: 'shadow-lg bg-white rounded',
                modalOverlayOpeningPadding: 6,
                modalOverlayOpeningRadius: 6
            },
            useModalOverlay: true
        });
        // Passos do tour (exemplo, ajuste conforme as features reais)
        this.tour.addStep({
            title: 'Bem-vindo ao Painel!',
            text: 'Aqui você acompanha seu progresso, cursos, mentorias e novidades.',
            attachTo: { element: '#painel-dashboard', on: 'bottom' },
            buttons: [
                { text: 'Próximo', action: this.tour.next }
            ]
        });
        this.tour.addStep({
            title: 'Menu Lateral',
            text: 'Use o menu para navegar entre cursos, eventos, marketplace e perfil.',
            attachTo: { element: '#sidebar-menu', on: 'right' },
            buttons: [
                { text: 'Voltar', action: this.tour.back },
                { text: 'Próximo', action: this.tour.next }
            ]
        });
        this.tour.addStep({
            title: 'Perfil',
            text: 'Edite seu perfil, foto e preferências aqui.',
            attachTo: { element: '#menu-perfil', on: 'right' },
            buttons: [
                { text: 'Voltar', action: this.tour.back },
                { text: 'Próximo', action: this.tour.next }
            ]
        });
        this.tour.addStep({
            title: 'Marketplace',
            text: 'Acesse cursos, mentorias e eventos disponíveis para você.',
            attachTo: { element: '#menu-marketplace', on: 'right' },
            buttons: [
                { text: 'Voltar', action: this.tour.back },
                { text: 'Próximo', action: this.tour.next }
            ]
        });
        this.tour.addStep({
            title: 'Pronto!',
            text: 'Você pode reiniciar este tour a qualquer momento pelo menu de ajuda.',
            buttons: [
                { text: 'Finalizar', action: () => { this.tour.complete(); this.setCompleted(); } }
            ]
        });
        this.tour.start();
    },
    setCompleted: function() {
        // Salva no localStorage que o tour foi concluído
        localStorage.setItem('memberTourCompleted', '1');
    },
    canShow: function() {
        return !localStorage.getItem('memberTourCompleted');
    },
    reset: function() {
        localStorage.removeItem('memberTourCompleted');
        this.start();
    }
};

// Inicialização automática se nunca foi concluído
window.addEventListener('DOMContentLoaded', function() {
    if (window.MemberTour.canShow()) {
        setTimeout(() => window.MemberTour.start(), 800);
    }
});
