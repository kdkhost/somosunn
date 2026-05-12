{{-- SweetAlert2 para confirmações no painel WAF --}}
<script>
$(function() {
    $(document).on('click', '.btn-swal-confirm', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $form = $btn.closest('form');
        var title = $btn.data('swal-title') || 'Confirmar acao?';
        var text = $btn.data('swal-text') || '';
        var icon = $btn.data('swal-icon') || 'warning';

        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#1F5EDB',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, confirmar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                $form.submit();
            }
        });
    });
});
</script>
