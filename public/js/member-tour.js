// member-tour.js - Exemplo mínimo para tour guiado
// Substitua este conteúdo pelo seu tour real se necessário

document.addEventListener('DOMContentLoaded', function () {
    if (typeof Shepherd === 'undefined') return;
    const tour = new Shepherd.Tour({
        defaultStepOptions: {
            scrollTo: true,
            cancelIcon: { enabled: true },
        }
    });
    tour.addStep({
        title: 'Bem-vindo!',
        text: 'Este é um exemplo de tour guiado.',
        attachTo: { element: 'body', on: 'center' },
        buttons: [
            {
                text: 'Fechar',
                action: tour.cancel
            },
            {
                text: 'Próximo',
                action: tour.next
            }
        ]
    });
    // Descomente para iniciar automaticamente
    tour.start();
});
