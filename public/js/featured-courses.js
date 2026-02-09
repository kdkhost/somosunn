// featured-courses.js
// Randomiza os cursos em destaque a cada 4 minutos e ao voltar para a página

document.addEventListener('DOMContentLoaded', function () {
    const featuredList = document.getElementById('featured-courses-list');
    if (!featuredList) return;

    // Salva o HTML original para reembaralhar
    const originalHTML = featuredList.innerHTML;
    let courseCards = Array.from(featuredList.children);

    function shuffle(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    function renderRandomCourses() {
        // Embaralha e pega até 6 cards
        const shuffled = shuffle([...courseCards]);
        featuredList.innerHTML = '';
        shuffled.slice(0, 6).forEach(card => featuredList.appendChild(card.cloneNode(true)));
    }

    // Primeira randomização
    renderRandomCourses();

    // A cada 4 minutos (240000 ms)
    setInterval(renderRandomCourses, 240000);

    // Ao voltar para a aba
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            renderRandomCourses();
        }
    });
});
