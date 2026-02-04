<!doctype html>
<html lang="pt-BR">
<head>
@php
    use Illuminate\Support\Str;
    if(!isset($settings)){
        try{
            $settings = \App\Models\Setting::all()->pluck('value','key')->toArray();
        }catch(\Throwable $e){
            $settings = [];
        }
    }
    $siteTheme = $settings['site_theme'] ?? 'light';
    $preloaderImage = $settings['preloader_image'] ?? null;
    if ($preloaderImage && !Str::startsWith($preloaderImage, ['http://','https://'])) {
        $candidate = public_path($preloaderImage);
        $preloaderImage = file_exists($candidate) ? asset($preloaderImage) : null;
    }
    if (!$preloaderImage) {
        $preloaderImage = asset('img/logo.svg');
    }
    $preloaderEnabled = (bool)($settings['preloader_enabled'] ?? 1);
    $favicon = $settings['favicon_image'] ?? 'favicon.ico';
    if ($favicon && !Str::startsWith($favicon, ['http://','https://'])) {
        $faviconPath = public_path($favicon);
        $faviconUrl = file_exists($faviconPath) ? asset($favicon) : asset('favicon.ico');
    } else {
        $faviconUrl = $favicon ?: asset('favicon.ico');
    }
@endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Admin - UNN')</title>
    <link rel="icon" href="{{ $faviconUrl }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jqvmap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.4.0/dist/css/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --main-header-height: 3.5rem; /* 56px default AdminLTE */
    }
    /* layout espaçamentos padrão AdminLTE */
    .content-wrapper{
        background:#f4f6f9;
        /* min-height calculation handled by AdminLTE JS */
        transition: margin-left .3s ease-in-out;
    }
    /* AdminLTE 3.2 navbar-fixed handles padding automatically via body class, no need for overriding here unless using custom implementation */
    body.layout-navbar-fixed .content-wrapper {
        margin-top: calc(4.5rem + 1px); /* Increased spacing for breadcrumb overlap fix */
    }
    .content-wrapper>.content{
        padding:0 1rem 1rem 1rem;
    }
    .content-header{
        padding:6px 12px 6px 12px;
        margin:0;
        border-bottom:0;
        background:transparent;
    }
    .content-header .container-fluid{padding:0;}
    .content-header h1{margin:0;font-size:22px;font-weight:600;}
    .content-header .breadcrumb{margin-bottom:0;background:transparent;}
    /* Upload UI */
    .upload-box{
        border:1px dashed #cbd5e0;
        border-radius:12px;
        padding:18px;
        text-align:center;
        cursor:pointer;
        position:relative;
        background: linear-gradient(180deg,#f8fafc,#fff);
        transition:all .2s ease;
        min-height:180px;
        display:flex;
        flex-direction:column;
        justify-content:center;
        align-items:center;
        gap:6px;
    }
    .upload-box:hover{box-shadow:0 8px 24px rgba(0,0,0,.05); border-color:#94a3b8;}
    .upload-box.dragover{border-color:#2563eb;background:#eef2ff;}
    .upload-box .upload-icon{font-size:32px;color:#2563eb;}
    .upload-preview{pointer-events:none;}
    .upload-preview img{
        max-width:100%;
        max-height:180px; /* Bigger height */
        border-radius:8px;
        object-fit:contain; /* Correct proportion */
        box-shadow:0 4px 6px rgba(0,0,0,.1);
        background-color: #f0f0f0; /* See transparent images */
    }
    .upload-meta{font-size:12px;color:#475569; pointer-events:none; margin-top:5px;}
    .upload-help{font-size:12px;color:#6b7280; pointer-events:none;}
    .progress.upload-progress{width:100%;height:6px;}
    .upload-btn{pointer-events:auto;}
    /* input fica oculto, acionado por clique/botão */
</style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed {{ $siteTheme === 'dark' ? 'dark-mode' : '' }}">
<div class="wrapper">
    @if($preloaderEnabled)
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="{{ $preloaderImage }}" alt="UNN" height="80" width="80">
        </div>
    @endif

    {{-- @include('admin.partials.navbar') --}}
    {{-- @include('admin.partials.sidebar') --}}

    <div class="content-wrapper">
        @if(View::hasSection('page_title') || View::hasSection('breadcrumb'))
            <div class="content-header">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <h1 class="m-0 h4">@yield('page_title')</h1>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" data-pjax>Home</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        @endif
        <section class="content">
            <div class="container-fluid pb-4" id="pjax-container">
                @yield('content')
            </div>
        </section>
    </div>

    {{-- @include('admin.partials.footer') --}}
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.8/dist/jquery.inputmask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-knob-chif@1.2.13/dist/jquery.knob.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/jquery.vmap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jqvmap@1.5.1/dist/maps/jquery.vmap.world.js"></script>
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-pjax@2.0.1/jquery.pjax.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.4.0/dist/js/bootstrap-colorpicker.min.js"></script>
@include('admin.partials.notifications')
@stack('scripts')
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
        $('.summernote').summernote({height:180});
        initUploadWidgets();
        initMasks();
        initColorPickers();
    });
    $('.summernote').summernote({height:180});
    initUploadWidgets();
    initMasks();
    initColorPickers();

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

    // Persistência de aba ativa (nav-tabs)
    const tabKey = 'admin-active-tab-'+(location.pathname || 'root');
    $('a[data-toggle="pill"], a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        localStorage.setItem(tabKey, $(e.target).attr('href'));
    });
    const savedTab = localStorage.getItem(tabKey);
    if(savedTab && $(savedTab).length){
        $('a[href="'+savedTab+'"][data-toggle="pill"], a[href="'+savedTab+'"][data-toggle="tab"]').tab('show');
    }

    function initColorPickers(){
        $('.colorpicker-element').colorpicker();
    }

    function initMasks(){
        function lookupCep($input){
            const cep = $input.val().replace(/\D/g,'');
            if(cep.length !== 8) return;
            if($input.data('lastCep') === cep) return;
            $input.data('lastCep', cep);
            const targetNumber = $input.data('target-number');
            const targetComplement = $input.data('target-complement');
            const targetDistrict = $input.data('target-district');
            toastr.info('Buscando CEP...');
            fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(r=>r.json())
                .then(data=>{
                    if(data.erro){ toastr.error('CEP não encontrado'); return; }
                    $('[name="company_address"]').val(data.logradouro || '');
                    if(targetDistrict){ $(targetDistrict).val(data.bairro || ''); } else { $('[name="company_district"]').val(data.bairro || ''); }
                    $('[name="company_city"]').val(data.localidade || '');
                    $('[name="company_state"]').val(data.uf || '');
                    toastr.success('Endereço preenchido pelo CEP');
                    if(targetNumber){ $(targetNumber).focus(); }
                    else if(targetComplement){ $(targetComplement).focus(); }
                })
                .catch(()=>{ toastr.error('Falha ao buscar CEP'); });
        }

        // CEP com viacep + feedback
        $('.mask-cep').inputmask('99999-999', {
            oncomplete: function(){
                lookupCep($(this));
            }
        });
        $('.mask-cep').off('input.cep').on('input.cep', function(){
            const $input = $(this);
            const cep = $input.val().replace(/\D/g,'');
            if(cep.length === 8){
                clearTimeout($input.data('cepTimer'));
                $input.data('cepTimer', setTimeout(()=>lookupCep($input), 250));
            }
        });
        $('.mask-cep').off('blur.cep').on('blur.cep', function(){
            lookupCep($(this));
        });
        $('.mask-cpf').inputmask('999.999.999-99');
        $('.mask-cnpj').inputmask('99.999.999/9999-99');
        $('.mask-date').inputmask('99/99/9999');
        $('.mask-datetime').inputmask('99/99/9999 99:99');
        $('.mask-time').inputmask('99:99');
        $('.mask-phone').inputmask({'mask': ['(99) 9999-9999','(99) 9 9999-9999'], keepStatic:true});
        $('.mask-money').inputmask('currency', {prefix:'R$ ', radixPoint:',', groupSeparator:'.', autoGroup:true, digits:2, rightAlign:false});
        $('.mask-cpf-cnpj').inputmask({mask: ['999.999.999-99','99.999.999/9999-99'], keepStatic:true, placeholder:'_'});
    }

        function initUploadWidgets(){
        $('.upload-box').each(function(){
            const box = $(this);
            if(box.data('uploadInit')) return;
            box.data('uploadInit', true);
            box.attr('tabindex','0');

            const input = box.find('input[type=file]');
            const preview = box.find('.upload-preview');
            const meta = box.find('.upload-meta');
            const help = box.find('.upload-help');
            const removeBtn = box.find('.upload-remove');
            const progress = box.find('.upload-progress');
            const bar = progress.find('.progress-bar');
            const maxSize = parseInt(box.data('max-size') || (5*1024*1024));
            const crop = box.data('crop') === 1 || box.data('crop') === '1';
            const existingUrl = box.data('existing-url');
            const removeInputSelector = box.data('remove-input');
            const accept = (input.attr('accept') || 'image/*').replace(/\./g,'');
            const sizeMb = (maxSize/1024/1024).toFixed(2)+' MB';

            if(help.length){
                help.text('Aceita: ' + accept + ' • Até ' + sizeMb + (crop ? ' • Possível recorte' : ''));
            }

            function renderEmpty(){
                preview.html('<i class="upload-icon fas fa-cloud-upload-alt"></i><div class="text-muted small">Clique ou arraste para enviar</div>');
                meta.text('');
                removeBtn.addClass('d-none');
            }

            function renderExisting(url){
                preview.html('<img src="'+url+'" alt="imagem">');
                meta.text('Arquivo atual');
                removeBtn.removeClass('d-none');
            }

            renderEmpty();
            if(existingUrl){
                renderExisting(existingUrl);
            }

            function setPreview(blobOrFile, name, url){
                const sizeMB = (blobOrFile.size/1024/1024).toFixed(2);
                preview.html('<img src="'+url+'" alt="preview">');
                meta.text((name || 'arquivo') + ' • ' + sizeMB + ' MB • ' + (blobOrFile.type || ''));
                removeBtn.removeClass('d-none');
            }

            function bindFileToInput(file){
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.off('change.upload');
                input[0].files = dataTransfer.files;
                input.on('change.upload', onInputChange);
            }

            function handleFile(file){
                if(!file) return;
                if(file.size > maxSize){ toastr.error('Arquivo excede o limite'); return; }
                const reader = new FileReader();
                bar.css('width','0%');
                progress.removeClass('d-none');
                const start = Date.now();

                reader.onprogress = function(e){
                    if(e.lengthComputable){
                        const pct = (e.loaded/e.total)*100;
                        bar.css('width', pct+'%');
                    }
                };
                reader.onload = function(e){
                    bar.css('width','100%');
                    setTimeout(()=>progress.addClass('d-none'),400);
                    const url = e.target.result;
                    const elapsed = (Date.now()-start)/1000;
                    const speed = file.size/Math.max(elapsed,0.1);
                    const eta = speed>0 ? Math.max((file.size-speed*elapsed)/speed,0) : 0;
                    const extra = ' • ' + (file.size/1024/1024).toFixed(2) + ' MB • ' + (file.type || 'tipo desconhecido') + ' • ~' + eta.toFixed(1) + 's';
                    if(crop){
                        openCropper(url, function(croppedBlob, croppedUrl){
                            const fileExt = (croppedBlob.type && croppedBlob.type.split('/')[1]) ? croppedBlob.type.split('/')[1] : 'png';
                            const croppedFile = new File([croppedBlob], file.name.replace(/\.[^/.]+$/, '') + '.' + fileExt, {type: croppedBlob.type});
                            bindFileToInput(croppedFile);
                            setPreview(croppedFile, croppedFile.name + extra, croppedUrl);
                        });
                    }else{
                        setPreview(file, file.name + extra, url);
                    }
                };
                reader.readAsDataURL(file);
            }

            function openFileDialog(){
                if(box.data('opening')) return;
                box.data('opening', true);
                const reset = () => {
                    box.data('opening', false);
                    $(window).off('focus.upload', reset);
                };
                $(window).one('focus.upload', reset);
                input.trigger('click');
            }

            function onInputChange(){
                const file = this.files && this.files[0] ? this.files[0] : null;
                if(removeInputSelector){ $(removeInputSelector).val('0'); }
                handleFile(file);
            }

            box.off('.upload');
            input.off('.upload');
            removeBtn.off('.upload');
            box.find('.upload-btn').off('.upload');

            box.on('click.upload', function(e){
                if($(e.target).closest('.upload-btn, .upload-remove, input').length) return;
                openFileDialog();
            });
            box.on('keydown.upload', function(e){
                if(!['Enter',' '].includes(e.key)) return;
                e.preventDefault();
                openFileDialog();
            });
            box.on('dragover.upload', function(e){ e.preventDefault(); box.addClass('dragover'); });
            box.on('dragleave.upload drop.upload', function(e){ e.preventDefault(); box.removeClass('dragover'); });
            box.on('drop.upload', function(e){ e.preventDefault(); const f = e.originalEvent.dataTransfer.files[0]; handleFile(f); });

            box.find('.upload-btn').on('click.upload', function(e){
                e.preventDefault();
                e.stopPropagation();
                openFileDialog();
            });
            input.on('click.upload', function(e){ e.stopPropagation(); });
            input.on('change.upload', onInputChange);

            removeBtn.on('click.upload', function(e){
                e.preventDefault();
                e.stopPropagation();
                input.val('');
                renderEmpty();
                if(removeInputSelector){ $(removeInputSelector).val('1'); }
            });
        });
    }
    function openCropper(imageUrl, callback){
        const modalId = 'cropperModal';
        let modal = $('#'+modalId);
        if(!modal.length){
            $('body').append(
                '<div class="modal fade" id="' + modalId + '" tabindex="-1">' +
                    '<div class="modal-dialog modal-lg"><div class="modal-content">' +
                        '<div class="modal-header"><h5 class="modal-title">Cortar imagem</h5>' +
                        '<button type="button" class="close" data-dismiss="modal">&times;</button></div>' +
                        '<div class="modal-body"><div style="max-height:500px;">' +
                        '<img id="' + modalId + '-img" style="max-width:100%;"></div></div>' +
                        '<div class="modal-footer">' +
                        '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>' +
                        '<button type="button" class="btn btn-primary" id="' + modalId + '-apply">Aplicar</button>' +
                        '</div></div></div></div>'
            );
            modal = $('#'+modalId);
        }
        const img = $('#'+modalId+'-img');
        let cropper;
        modal.on('shown.bs.modal', function(){
            img.attr('src', imageUrl);
            cropper = new Cropper(img[0], {aspectRatio: NaN, viewMode: 1});
        }).on('hidden.bs.modal', function(){
            cropper && cropper.destroy();
            cropper = null;
        });
        $('#'+modalId+'-apply').off('click').on('click', function(){
            if(!cropper) return;
            cropper.getCroppedCanvas().toBlob(function(blob){
                const url = URL.createObjectURL(blob);
                callback(blob, url);
                modal.modal('hide');
            });
        });
        modal.modal('show');
    }
});
</script>
</body>
</html>
