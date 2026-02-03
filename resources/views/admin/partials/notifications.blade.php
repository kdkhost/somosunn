<script>
    @if(session('success'))
        toastr.success("{{ session('success') }}");
    @endif

    @if(session('error'))
        toastr.error("{{ session('error') }}");
    @endif

    @if(session('warning'))
        toastr.warning("{{ session('warning') }}");
    @endif

    @if(session('info'))
        toastr.info("{{ session('info') }}");
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            toastr.error("{{ $error }}");
        @endforeach
    @endif

    // Global Delete Confirmation
    $(document).on('click', '.btn-delete, [data-confirm-delete]', function(e){
        e.preventDefault();
        const form = $(this).closest('form');
        const url = $(this).data('action') || $(this).attr('href');
        
        Swal.fire({
            title: 'Tem certeza?',
            text: "Esta ação não pode ser revertida!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                if(form.length > 0) {
                    form.submit();
                } else if(url) {
                    // Create a form dynamically
                    const f = document.createElement('form');
                    f.method = 'POST';
                    f.action = url;
                    f.innerHTML = '<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}">';
                    document.body.appendChild(f);
                    f.submit();
                }
            }
        });
    });
</script>
