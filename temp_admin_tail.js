<script>
$(function(){
    const container = '#pjax-container';
    $(document).pjax('a[data-pjax]', container, {timeout: 8000});
    $('.nav-sidebar a, .navbar a').each(function(){
        const h = $(this).attr('href') || '';
        if(h.startsWith('http') || h === '#' || $(this).attr('target')) return;
        $(this).attr('data-pjax','true');
    });
    $(document).on('pjax:end', function(){
        $('[data-widget="treeview"]').Treeview && $('[data-widget="treeview"]').Treeview('init');
        $('.summernote').summernote({height:180});
    });
    $('.summernote').summernote({height:180});

    toastr.options = {positionClass:'toast-top-right', timeOut:3500, progressBar:true};

    $(document).on('submit','.ajax-form', function(e){
        e.preventDefault();
        const form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: form.attr('method') || 'POST',
            data: new FormData(this),
            processData:false,
            contentType:false,
            success: function(resp){
                toastr.success('Salvo com sucesso');
                if(resp && resp.redirect){ $.pjax({url:resp.redirect, container:container}); }
            },
            error: function(){ toastr.error('Erro ao salvar'); }
        });
    });

    $(document).on('click','.btn-delete', function(e){
        e.preventDefault();
        const url = $(this).data('action') || $(this).attr('href');
        Swal.fire({title:'Excluir?', text:'Confirme para apagar definitivamente.', icon:'warning', showCancelButton:true, confirmButtonText:'Sim, excluir', cancelButtonText:'Cancelar'})
            .then((result)=>{ if(result.isConfirmed){ $.post(url, {_method:'DELETE', _token:'{{ csrf_token() }}'}, ()=>{ toastr.success('Excluído'); $.pjax.reload(container);}); }});
    });

    $('#themeToggleBtn').on('click', function(){
        const input = $('#site_theme_input');
        input.val(input.val()==='dark' ? 'light' : 'dark');
        $('#themeToggleForm').submit();
    });

    const logo = $('.brand-logo-img');
    const favicon = $('.brand-favicon-img');
    $(document).on('collapsed.lte.pushmenu', function(){ logo.addClass('d-none'); favicon.removeClass('d-none'); });
    $(document).on('expanded.lte.pushmenu', function(){ favicon.addClass('d-none'); logo.removeClass('d-none'); });
});
</script>
