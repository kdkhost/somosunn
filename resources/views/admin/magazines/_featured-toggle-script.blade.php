<script>
document.addEventListener('click', async function (event) {
    const button = event.target.closest('.js-toggle-magazine-featured');
    if (!button || button.disabled) return;

    const icon = button.querySelector('i');
    const previousIcon = icon.className;
    button.disabled = true;
    button.style.opacity = '0.65';
    icon.className = 'fas fa-spinner fa-spin';

    try {
        const response = await fetch(button.dataset.url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Não foi possível salvar.');

        const activeClasses = button.dataset.activeClasses.split(' ');
        const inactiveClasses = button.dataset.inactiveClasses.split(' ');
        button.classList.remove(...(data.is_featured ? inactiveClasses : activeClasses));
        button.classList.add(...(data.is_featured ? activeClasses : inactiveClasses));
        button.dataset.featured = data.is_featured ? '1' : '0';
        button.setAttribute('aria-pressed', data.is_featured ? 'true' : 'false');
        button.title = data.is_featured ? 'Remover dos destaques' : 'Adicionar aos destaques';
        icon.className = (data.is_featured ? 'fas' : 'far') + ' fa-star';

        if (window.Toastify) {
            Toastify({ text: data.message, duration: 3000, gravity: 'top', position: 'right' }).showToast();
        }
    } catch (error) {
        icon.className = previousIcon;
        if (window.Toastify) {
            Toastify({ text: error.message, duration: 4000, gravity: 'top', position: 'right', style: { background: '#dc2626' } }).showToast();
        } else {
            window.alert(error.message);
        }
    } finally {
        button.disabled = false;
        button.style.opacity = '';
    }
});
</script>
