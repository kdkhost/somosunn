
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
            .then((result)=>{ if(result.isConfirmed){ $.post(url, {_method:'DELETE', _token:'TOKEN'}, ()=>{ toastr.success('Excluído'); $.pjax.reload(container);}); }});
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
        // CEP com viacep + feedback
        $('.mask-cep').inputmask('99999-999', {
            oncomplete: function(){
                const $input = $(this);
                const cep = $input.val().replace(/\D/g,'');
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
            box.attr('tabindex','0');
            const input = box.find('input[type=file]');
            const preview = box.find('.upload-preview');
            const meta = box.find('.upload-meta');
            const help = box.find('.upload-help');
            const removeBtn = box.find('.upload-remove');
            const progress = box.find('.progress');
            const bar = progress.find('.progress-bar');
            const maxSize = parseInt(box.data('max-size') || (5*1024*1024));
            const crop = box.data('crop') === 1;
            const existingUrl = box.data('existing-url');
            const removeInputSelector = box.data('remove-input');
            const accept = (input.attr('accept') || 'arquivos permitidos').replace(/\./g,'');
            const sizeMb = (maxSize/1024/1024).toFixed(2)+' MB';
            if(help.length){
                help.text('Aceita: ' + accept + ' • Até ' + sizeMb + (crop ? ' • Possível recorte' : ''));
            }

            box.css('position','relative');
            input.addClass('d-none');

            function resetPreview(){
                preview.html('<i class="upload-icon fas fa-cloud-upload-alt"></i><div class="text-muted small">Clique ou arraste para enviar</div>');
                meta.text('');
                removeBtn.addClass('d-none');
            }
            resetPreview();

            if(existingUrl){
                preview.html('<i class="upload-icon fas fa-cloud-upload-alt"></i><img src="'+existingUrl+'" onerror="this.style.display=\'none\'">');
                meta.text('Arquivo atual');
                removeBtn.removeClass('d-none');
            }

            function handleFile(file){
                if(!file) return;
                if(file.size > maxSize){ toastr.error('Arquivo excede o limite'); return; }
                const reader = new FileReader();
                bar.css('width','0%'); progress.removeClass('d-none');
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
                    const speed = file.size/elapsed;
                    const eta = speed>0 ? (file.size-speed*elapsed)/speed : 0;
                    const extra = ' • ' + (file.size/1024/1024).toFixed(2) + ' MB • ' + (file.type || 'tipo desconhecido') + ' • ~' + eta.toFixed(1) + 's';
                    if(crop){
                        openCropper(url, function(croppedBlob, croppedUrl){
                            setPreview(croppedBlob, file.name+extra, croppedUrl);
                        });
                    }else{
                        setPreview(file, file.name+extra, url);
                    }
                };
                reader.readAsDataURL(file);
            }

            function setPreview(blobOrFile, name, url){
                const sizeMB = (blobOrFile.size/1024/1024).toFixed(2);
                preview.html('<i class="upload-icon fas fa-cloud-upload-alt"></i><img src="'+url+'">');
                meta.text((name || 'arquivo')+' • '+sizeMB+' MB '+(blobOrFile.type || ''));
                removeBtn.removeClass('d-none');
                // preencher input com blob caso tenha vindo do cropper
                if(blobOrFile instanceof Blob && !(blobOrFile instanceof File)){
                    const fileExt = (blobOrFile.type && blobOrFile.type.split('/')[1]) ? blobOrFile.type.split('/')[1] : 'png';
                    const f = new File([blobOrFile], name.replace(/\.[^/.]+$/, '')+'.'+fileExt, {type: blobOrFile.type});
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(f);
                    box.data('settingFiles', true);
                    input[0].files = dataTransfer.files;
                    setTimeout(()=>box.data('settingFiles', false), 0);
                }
            }

            function openFileDialog(){
                if(box.data('opening')) return;
                box.data('opening', true);
                input.trigger('click');
                setTimeout(()=>box.data('opening', false), 800);
            }

            box.off('click keydown dragover dragleave drop');
            input.off('change click');
            removeBtn.off('click');
            box.find('.upload-btn').off('click');

            box.on('click keydown', (e)=>{
                if(e.type === 'keydown' && !['Enter',' '].includes(e.key)) return;
                if(box.data('opening')) return;
                if($(e.target).is('input[type=file]') || $(e.target).hasClass('upload-remove')) return;
                if($(e.target).closest('.upload-btn').length) return;
                openFileDialog();
            });
            box.find('.upload-btn').on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                openFileDialog();
            });
            box.on('dragover', e=>{ e.preventDefault(); box.addClass('dragover'); });
            box.on('dragleave drop', e=>{ e.preventDefault(); box.removeClass('dragover'); });
            box.on('drop', e=>{ e.preventDefault(); const f=e.originalEvent.dataTransfer.files[0]; handleFile(f); });
            input.on('click', function(e){ e.stopPropagation(); });
            input.on('change', function(){
                if(box.data('settingFiles')) return;
                box.data('opening', false);
                handleFile(this.files[0]);
                if(removeInputSelector){ $(removeInputSelector).val('0'); }
            });
            removeBtn.on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                input.val('');
                resetPreview();
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

