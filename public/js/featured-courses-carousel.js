// featured-courses-carousel.js
// Carrossel de cursos em destaque: 3 visíveis, avança 1 por vez, looping infinito

document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('featured-courses-list');
    if (!wrapper) return;
    const cards = Array.from(wrapper.children);
    if (cards.length <= 3) return; // Não faz carrossel se <=3

    let start = 0;
    let interval = null;

    function renderCarousel() {
        wrapper.innerHTML = '';
        for (let i = 0; i < 3; i++) {
            const idx = (start + i) % cards.length;
            wrapper.appendChild(cards[idx].cloneNode(true));
        }
    }

    function nextSlide() {
        start = (start + 1) % cards.length;
        renderCarousel();
    }

    renderCarousel();
    interval = setInterval(nextSlide, 3500); // Troca a cada 3.5s

    // (Opcional: pausar ao passar mouse)
    wrapper.addEventListener('mouseenter', () => clearInterval(interval));
    wrapper.addEventListener('mouseleave', () => interval = setInterval(nextSlide, 3500));
});
