<script>
(function () {
    function notify(type, message) {
        const text = message || (type === 'success' ? 'Operação concluída.' : 'Não foi possível concluir a operação.');

        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](text);
            return;
        }

        if (type === 'success' && typeof window.showSuccess === 'function') {
            window.showSuccess(text);
        } else if (type === 'error' && typeof window.showError === 'function') {
            window.showError(text);
        }
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value
            || '';
    }

    async function responseData(response) {
        const contentType = response.headers.get('content-type') || '';
        return contentType.includes('application/json') ? await response.json() : {};
    }

    async function confirmAction(options) {
        if (!window.Swal || typeof window.Swal.fire !== 'function') {
            notify('error', 'A confirmação segura não está disponível. Recarregue a página.');
            return false;
        }

        const result = await window.Swal.fire({
            title: options.title,
            text: options.text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: options.confirmButtonText || 'Confirmar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            focusCancel: true
        });

        return result.isConfirmed;
    }

    document.addEventListener('click', async function (event) {
        const button = event.target.closest('.js-toggle-magazine-featured');
        if (!button || button.disabled) return;

        const adding = button.dataset.featured !== '1';
        const confirmed = await confirmAction({
            title: adding ? 'Adicionar aos destaques?' : 'Remover dos destaques?',
            text: adding
                ? 'A revista passará a aparecer nas áreas de destaque do site.'
                : 'A revista deixará de aparecer nas áreas de destaque do site.',
            confirmButtonText: adding ? 'Adicionar destaque' : 'Remover destaque'
        });
        if (!confirmed) return;

        const token = csrfToken();
        if (!token) {
            notify('error', 'Sessão inválida. Recarregue a página e tente novamente.');
            return;
        }

        const icon = button.querySelector('i');
        const previousIcon = icon?.className || '';
        button.disabled = true;
        button.style.opacity = '0.65';
        if (icon) icon.className = 'fas fa-spinner fa-spin';

        try {
            const response = await fetch(button.dataset.url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await responseData(response);

            if (!response.ok || !data.success) {
                const fallback = response.status === 419
                    ? 'Sua sessão expirou. Recarregue a página e tente novamente.'
                    : 'Não foi possível salvar a alteração.';
                throw new Error(data.message || fallback);
            }

            const activeClasses = button.dataset.activeClasses.split(' ');
            const inactiveClasses = button.dataset.inactiveClasses.split(' ');
            button.classList.remove(...(data.is_featured ? inactiveClasses : activeClasses));
            button.classList.add(...(data.is_featured ? activeClasses : inactiveClasses));
            button.dataset.featured = data.is_featured ? '1' : '0';
            button.setAttribute('aria-pressed', data.is_featured ? 'true' : 'false');
            button.title = data.is_featured ? 'Remover dos destaques' : 'Adicionar aos destaques';
            if (icon) icon.className = (data.is_featured ? 'fas' : 'far') + ' fa-star';

            notify('success', data.message);
        } catch (error) {
            if (icon) icon.className = previousIcon;
            notify('error', error instanceof Error ? error.message : 'Não foi possível salvar a alteração.');
        } finally {
            button.disabled = false;
            button.style.opacity = '';
        }
    });

    document.addEventListener('submit', async function (event) {
        const form = event.target.closest('.js-confirm-magazine-delete');
        if (!form || form.dataset.confirmed === '1') return;

        event.preventDefault();
        const confirmed = await confirmAction({
            title: 'Remover revista?',
            text: 'Esta ação removerá o cadastro da revista e não poderá ser desfeita.',
            confirmButtonText: 'Remover revista'
        });

        if (confirmed) {
            form.dataset.confirmed = '1';
            form.submit();
        }
    });
})();
</script>
