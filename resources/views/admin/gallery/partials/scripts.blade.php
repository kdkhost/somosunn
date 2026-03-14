@php
    $galleryUploadPerFileLimitBytes = $galleryUploadPerFileLimitBytes ?? (\App\Support\UploadStorage::effectiveUploadLimitBytes(20 * 1024 * 1024) ?? (20 * 1024 * 1024));
@endphp

<script>
$(function () {
    /* ── refs ── */
    const $modal        = $('#uploadModal');
    const $form         = $('#adminUploadForm');
    const $eventField   = $('#admin-upload-event');
    const $submitBtn    = $('#adminSubmitBtn');
    const $progressBar  = $('#adminProgressBar');
    const $progressLabel= $('#adminProgressLabel');
    const $progressValue= $('#adminProgressValue');
    const $selectedFiles= $('#adminSelectedFiles');
    const $selectedSummary = $('#adminSelectedSummary');
    const $selectedSize = $('#adminSelectedSize');
    const $selectedList = $('#adminSelectedList');
    const $inlineGrid   = $('#adminInlinePreviewGrid');
    const fileInput     = document.getElementById('adminFileInput');
    const dropzoneEmpty = document.getElementById('adminDropzoneEmpty');
    const dropzonePreview = document.getElementById('adminDropzonePreview');
    const $dropzone     = $('#adminDropzone');
    const selectedFilter= document.getElementById('gallery-event-filter');
    const galleryContainer = document.getElementById('gallery-container');
    const visibleTotal  = document.getElementById('adminGalleryVisibleTotal');
    const scopeTotal    = document.getElementById('adminGalleryScopeCount');
    const resultCount   = document.getElementById('adminGalleryResultCount');
    const perFileLimitBytes = Math.max(1, parseInt('{{ $galleryUploadPerFileLimitBytes }}', 10) || (20 * 1024 * 1024));
    const uploadUrl     = '{{ route('admin.gallery.upload') }}';
    const csrfToken     = '{{ csrf_token() }}';

    let queue = [];
    let isUploading = false;
    let seed = 0;

    /* ── select2 ── */
    if ($.fn && typeof $.fn.select2 === 'function') {
        $('.select2-modal').select2({ dropdownParent: $modal });
    }

    /* ── helpers ── */
    function esc(v) {
        return String(v || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }
    function stripHtml(v) { return String(v||'').replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim(); }
    function fmtBytes(b) {
        if (!Number.isFinite(b) || b <= 0) return '0 B';
        const u = ['B','KB','MB','GB'];
        const e = Math.min(Math.floor(Math.log(b)/Math.log(1024)), u.length-1);
        const a = b/Math.pow(1024,e);
        return `${a.toFixed(a>=100||e===0?0:1)} ${u[e]}`;
    }
    function fmtRemaining(s) {
        const v = Number(s||0);
        if (!Number.isFinite(v)||v<=0) return 'calculando tempo restante...';
        const r = Math.round(v);
        if (r<60) return `${r}s restantes`;
        const m = Math.floor(r/60), rs = r%60;
        return m<60 ? `${m}min ${rs}s restantes` : `${Math.floor(m/60)}h ${m%60}min restantes`;
    }
    function fileKind(file) {
        const t = String(file.type||'').toLowerCase(), n = String(file.name||'').toLowerCase();
        if (t.startsWith('image/')||/\.(png|jpe?g|gif|webp|heic|heif)$/i.test(n)) return 'image';
        if (t.startsWith('video/')||/\.(mp4|mov|m4v|webm|mkv)$/i.test(n)) return 'video';
        return 'file';
    }
    function sig(file) { return [file.name,file.size,file.lastModified].join('::'); }
    function notify(icon, title, text) {
        if (typeof Swal !== 'undefined') return Swal.fire({icon,title,text,confirmButtonText:'OK'});
        alert(`${title}\n\n${text}`);
        return Promise.resolve();
    }
    function countVal(el) {
        if (!el) return 0;
        const p = parseInt(String(el.textContent||'').replace(/\./g,''),10);
        return Number.isFinite(p)?p:0;
    }
    function setCount(el, v) { if (el && Number.isFinite(v) && v>=0) el.textContent = Number(v).toLocaleString('pt-BR'); }

    /* ── state ── */
    function toggleDropzone() {
        const has = queue.length > 0;
        dropzoneEmpty && dropzoneEmpty.classList.toggle('d-none', has);
        dropzonePreview && dropzonePreview.classList.toggle('d-none', !has);
    }
    function setProgress(pct, label) {
        const safe = Math.max(0,Math.min(100,Math.round(pct)));
        $progressBar.css('width',`${safe}%`);
        $progressValue.text(`${safe}%`);
        $progressLabel.text(label||'');
    }
    function updateActions() {
        const hasEvent = String($eventField.val()||'').trim() !== '';
        const hasFiles = queue.length > 0;
        $submitBtn.prop('disabled', !hasEvent || !hasFiles || isUploading);
        $submitBtn.html(isUploading
            ? '<i class="fas fa-spinner fa-spin mr-1"></i> Enviando...'
            : '<i class="fas fa-upload mr-1"></i>Publicar na galeria');
        $dropzone.toggleClass('is-disabled', isUploading);
        const $addMore = $('#adminAddMoreFiles');
        $addMore.prop('disabled', isUploading);
    }

    /* ── render queue ── */
    function itemMeta(item) {
        if (item.state==='uploading') return {badge:'badge-primary',label:'enviando',bar:'bg-primary progress-bar-striped progress-bar-animated'};
        if (item.state==='done')      return {badge:'badge-success',label:'concluido',bar:'bg-success'};
        if (item.state==='error')     return {badge:'badge-danger', label:'falhou',   bar:'bg-danger'};
        return {badge: item.kind==='video'?'badge-info':'badge-secondary', label: item.kind==='video'?'video':'imagem', bar:'bg-secondary'};
    }
    function previewMarkup(item) {
        if (item.kind==='image' && item.previewUrl) return `<img src="${item.previewUrl}" alt="${esc(item.file.name)}">`;
        if (item.kind==='video' && item.previewUrl) return `<video src="${item.previewUrl}" muted playsinline preload="metadata"></video>`;
        return `<div class="gallery-admin-preview-fallback"><i class="fas fa-file-alt"></i></div>`;
    }
    function renderQueue() {
        if (queue.length === 0) {
            $selectedFiles.addClass('d-none');
            $selectedSummary.text('0 arquivo(s)');
            $selectedSize.text('0 B');
            $selectedList.html('');
            $inlineGrid.html('');
            toggleDropzone();
            updateActions();
            return;
        }
        const totalBytes = queue.reduce((s,i)=>s+Number(i.file.size||0),0);
        $selectedFiles.removeClass('d-none');
        $selectedSummary.text(`${queue.length} arquivo(s) selecionado(s)`);
        $selectedSize.text(fmtBytes(totalBytes));
        $selectedList.html(queue.map(item => {
            const meta = itemMeta(item);
            const pct  = Math.max(0,Math.min(100,Math.round(Number(item.progress||0))));
            return `
                <div class="gallery-admin-selected-item card card-body mb-2 p-3">
                    <div class="d-flex align-items-start">
                        <div class="gallery-admin-selected-preview ${item.kind==='video'?'is-video':''}">
                            ${previewMarkup(item)}
                        </div>
                        <div class="flex-grow-1 ml-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <span class="badge ${meta.badge} text-uppercase mr-1">${meta.label}</span>
                                    <span class="font-weight-bold text-break">${esc(item.file.name)}</span>
                                    <p class="small text-muted mb-0">${fmtBytes(item.file.size||0)}</p>
                                </div>
                                <button type="button" class="btn btn-link text-danger p-0 admin-remove-file" data-id="${item.id}" ${isUploading?'disabled':''}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="progress gallery-admin-file-progress">
                                <div class="progress-bar ${meta.bar}" role="progressbar" style="width:${pct}%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">${esc(item.remaining||'pronto para iniciar')}</small>
                                <small class="text-muted">${pct}%</small>
                            </div>
                            ${item.error?`<small class="text-danger d-block mt-1">${esc(item.error)}</small>`:''}
                        </div>
                    </div>
                </div>`;
        }).join(''));
        $inlineGrid.html(queue.map(item => {
            const meta = itemMeta(item);
            return `<div class="gallery-admin-inline-preview-item ${item.kind==='video'?'is-video':''}" title="${esc(item.file.name)}">
                ${previewMarkup(item)}
                <span class="badge ${meta.badge} gallery-admin-inline-badge text-uppercase">${meta.label}</span>
            </div>`;
        }).join(''));
        toggleDropzone();
        updateActions();
    }

    /* ── add files ── */
    function addFiles(fileList) {
        const rejected = [];
        Array.from(fileList||[]).forEach(file => {
            const kind = fileKind(file);
            if (kind==='file') { rejected.push(`${file.name} possui formato nao suportado.`); return; }
            if ((file.size||0) > perFileLimitBytes) { rejected.push(`${file.name} excede o limite de ${fmtBytes(perFileLimitBytes)}.`); return; }
            if (!queue.some(i=>i.signature===sig(file))) {
                queue.push({
                    id: `admin-upload-${++seed}`,
                    signature: sig(file),
                    file,
                    kind,
                    previewUrl: URL.createObjectURL(file),
                    progress: 0,
                    state: 'ready',
                    remaining: 'pronto para iniciar',
                    error: ''
                });
            }
        });
        if (fileInput) fileInput.value = '';
        renderQueue();
        if (rejected.length > 0) notify('warning','Arquivo recusado',rejected[0]);
    }

    /* ── upload single ── */
    function extractError(xhr, payload) {
        let msg = payload?.message || '';
        if (payload?.errors) msg = Object.values(payload.errors).flat().join(' ');
        if (!msg && xhr.responseText) msg = stripHtml(xhr.responseText);
        if (!msg) msg = 'Falha ao realizar upload.';
        if (xhr.status===413) msg = `O servidor recusou o arquivo por exceder o limite de ${fmtBytes(perFileLimitBytes)}.`;
        if (xhr.status===419) msg = 'Sua sessao expirou. Recarregue a pagina e tente novamente.';
        if (xhr.status>=500 && !payload?.message) msg = 'O servidor encontrou um erro interno ao processar o upload.';
        return msg;
    }
    function sendSingleFile(item, index, total) {
        const formData = new FormData();
        const startedAt = Date.now();
        formData.append('event_id', String($eventField.val()||'').trim());
        formData.append('files[]', item.file);
        item.state = 'uploading'; item.progress = 0; item.error = ''; item.remaining = 'calculando tempo restante...';
        renderQueue();
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', uploadUrl, true);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.addEventListener('progress', function(e) {
                const tot = Number(e.total||item.file.size||0);
                const loaded = Number(e.loaded||0);
                const elapsed = Math.max((Date.now()-startedAt)/1000, 0.2);
                const speed = loaded/elapsed;
                item.progress = Math.max(0,Math.min(100,Math.round((loaded/Math.max(tot,1))*100)));
                item.remaining = fmtRemaining(speed>0?((tot-loaded)/speed):0);
                const processed = queue.reduce((s,entry)=>s+(entry.state==='done'||entry.state==='error'?1:(entry===item?(item.progress/100):0)),0);
                setProgress((processed/total)*100, `Enviando ${index+1} de ${total}: ${item.file.name}`);
                renderQueue();
            });
            xhr.addEventListener('load', function() {
                let payload = null;
                try { payload = xhr.responseText ? JSON.parse(xhr.responseText) : null; } catch(e) { payload = null; }
                if (xhr.status>=200 && xhr.status<300 && payload && payload.success) { resolve(payload); return; }
                reject(new Error(extractError(xhr, payload)));
            });
            xhr.addEventListener('error', function() { reject(new Error('Falha de conexao durante o upload.')); });
            xhr.send(formData);
        });
    }

    /* ── media card (append after upload) ── */
    function avatarMarkup(item) {
        const name = String(item.owner_name||'Sistema');
        const initial = esc(name.trim().charAt(0)||'S').toUpperCase();
        if (item.owner_avatar) return `<img src="${esc(item.owner_avatar)}" alt="${esc(name)}" onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}';">`;
        return `<span>${initial}</span>`;
    }
    function createMediaCard(item) {
        const eventTitle = esc(item.event_title||'Evento sem titulo');
        const ownerName  = esc(item.owner_name||'Sistema');
        const uploadedAt = esc(item.uploaded_at||'--');
        const assetUrl   = esc(item.url||'{{ asset('img/logo.svg') }}');
        const isCover    = Boolean(item.is_cover);
        const isVideo    = String(item.type||'')==='video';
        return `
            <div class="col-sm-6 col-xl-4 mb-4" data-gallery-card-id="${item.id}">
                <div class="card h-100 shadow-sm">
                    <div class="gallery-admin-thumb">
                        ${isVideo
                            ? `<a href="${assetUrl}" target="_blank" rel="noopener" class="gallery-admin-video-link">
                                <video src="${assetUrl}" muted playsinline preload="metadata"></video>
                                <span class="gallery-admin-video-overlay"><i class="fas fa-play-circle fa-2x mb-2"></i><span class="font-weight-bold">Abrir video</span></span>
                               </a>`
                            : `<a href="${assetUrl}" target="_blank" rel="noopener"><img src="${assetUrl}" alt="${eventTitle}" loading="lazy" decoding="async"></a>`
                        }
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <div class="gallery-admin-avatar mr-2">${avatarMarkup(item)}</div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold text-truncate">${ownerName}</div>
                                <small class="text-muted">Enviado em ${uploadedAt}</small>
                            </div>
                        </div>
                        <h3 class="h6 font-weight-bold mb-2">${eventTitle}</h3>
                        <div class="mb-3">
                            ${isCover?'<span class="badge badge-warning mr-1"><i class="fas fa-star mr-1"></i>Capa do album</span>':''}
                            ${item.watermarked?'<span class="badge badge-primary">Watermark</span>':''}
                            <span class="badge badge-secondary">${isVideo?'Video':'Imagem'}</span>
                        </div>
                        <div class="mt-auto">
                            <div class="row">
                                ${!isVideo
                                    ? `<div class="col-8 pr-1">
                                            <form action="${esc(item.set_cover_url||'')}" method="POST" class="mb-0 gallery-cover-form">
                                                @csrf
                                                <button type="submit" class="btn ${isCover?'btn-warning':'btn-outline-warning'} btn-sm btn-block">
                                                    <i class="fas fa-star mr-1"></i>${isCover?'Capa ativa':'Definir capa'}
                                                </button>
                                            </form>
                                       </div>
                                       <div class="col-4 pl-1">
                                            <form action="${esc(item.delete_url||'')}" method="POST" class="delete-form mb-0" data-confirm-title="Remover da galeria?" data-confirm-text="Esta midia sera excluida permanentemente.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm btn-block"><i class="fas fa-trash"></i></button>
                                            </form>
                                       </div>`
                                    : `<div class="col-12">
                                            <form action="${esc(item.delete_url||'')}" method="POST" class="delete-form mb-0" data-confirm-title="Remover da galeria?" data-confirm-text="Esta midia sera excluida permanentemente.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm btn-block"><i class="fas fa-trash mr-1"></i>Remover video</button>
                                            </form>
                                       </div>`
                                }
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    }
    function bindDeleteForm(form) {
        if (!form) return;
        $(form).off('submit.galleryDelete').on('submit.galleryDelete', function(e) {
            e.preventDefault();
            const f = this;
            const title = $(f).data('confirm-title')||'Tem certeza?';
            const text  = $(f).data('confirm-text')||'Esta acao nao podera ser desfeita.';
            if (typeof Swal==='undefined') { if (window.confirm(text)) f.submit(); return; }
            Swal.fire({title,text,icon:'warning',showCancelButton:true,confirmButtonColor:'#1e293b',cancelButtonColor:'#ef4444',confirmButtonText:'Sim, excluir',cancelButtonText:'Cancelar'})
                .then(r=>{ if(r.isConfirmed) f.submit(); });
        });
    }
    function shouldAppend(item) {
        if (!selectedFilter||!selectedFilter.value) return true;
        return String(selectedFilter.value)===String(item.event_id||'');
    }
    function prependMedia(items) {
        if (!galleryContainer||!Array.isArray(items)||items.length===0) return;
        let appended = 0;
        items.slice().reverse().forEach(item => {
            if (!shouldAppend(item)) return;
            galleryContainer.insertAdjacentHTML('afterbegin', createMediaCard(item));
            const card = galleryContainer.firstElementChild;
            if (card) card.querySelectorAll('.delete-form').forEach(bindDeleteForm);
            appended++;
        });
        if (appended===0) return;
        const emptyState = document.getElementById('adminGalleryEmptyState');
        if (emptyState) emptyState.remove();
        setCount(visibleTotal, countVal(visibleTotal)+appended);
        if (selectedFilter&&selectedFilter.value) setCount(scopeTotal, countVal(scopeTotal)+appended);
        setCount(resultCount, countVal(resultCount)+appended);
    }

    /* ── reset ── */
    function resetQueue() {
        queue.forEach(i=>{ if(i.previewUrl) URL.revokeObjectURL(i.previewUrl); });
        queue = []; isUploading = false;
        if (fileInput) fileInput.value = '';
        setProgress(0,'Aguardando selecao dos arquivos.');
        renderQueue();
    }

    /* ── submit ── */
    $form.on('submit', async function(e) {
        e.preventDefault();
        if (!String($eventField.val()||'').trim()) { notify('warning','Evento obrigatorio','Selecione o evento antes de enviar.'); return; }
        if (queue.length===0) { notify('warning','Arquivos obrigatorios','Selecione pelo menos um arquivo.'); return; }
        isUploading = true;
        updateActions();
        setProgress(0,'Preparando envio...');
        const failures = [];
        const total = queue.length;
        const allMedia = [];
        for (let i=0; i<total; i++) {
            const item = queue[i];
            try {
                const payload = await sendSingleFile(item, i, total);
                item.state='done'; item.progress=100; item.remaining='concluido'; item.error='';
                if (payload.media) allMedia.push(...payload.media);
                renderQueue();
            } catch(err) {
                item.state='error'; item.progress=100; item.remaining='falhou'; item.error=err.message||'Falha no upload.';
                failures.push(`${item.file.name}: ${item.error}`);
                renderQueue();
            }
        }
        isUploading = false;
        updateActions();
        if (failures.length===0) {
            setProgress(100,'Upload concluido');
            prependMedia(allMedia);
            await notify('success','Galeria atualizada',`${total} arquivo(s) publicado(s) com sucesso.`);
            resetQueue();
            $modal.modal('hide');
            return;
        }
        queue = queue.filter(i=>i.state==='error').map(i=>{ i.state='ready'; i.progress=0; i.remaining='pronto para reenviar'; return i; });
        renderQueue();
        setProgress(0,'Envio concluido com pendencias');
        const summary = failures.length>1 ? `${failures[0]} Outros ${failures.length-1} arquivo(s) tambem falharam.` : failures[0];
        await notify(
            failures.length<total?'warning':'error',
            failures.length<total?'Upload concluido com ressalvas':'Upload recusado',
            failures.length<total?`${total-failures.length} arquivo(s) publicado(s). ${summary}`:summary
        );
    });

    /* ── events ── */
    $eventField.on('change', updateActions);

    $('#adminFilePicker, #adminAddMoreFiles').on('click', function(e) {
        e.preventDefault();
        if (!isUploading && fileInput) fileInput.click();
    });

    if (fileInput) {
        $(fileInput).on('change', function() { if (this.files.length>0) addFiles(this.files); });
    }

    $dropzone.on('click', function(e) {
        if (isUploading) return;
        if ($(e.target).closest('.admin-remove-file, #adminAddMoreFiles').length) return;
        if (fileInput) fileInput.click();
    });

    $dropzone.on('dragenter dragover', function(e) {
        e.preventDefault(); e.stopPropagation();
        if (!isUploading) $dropzone.addClass('dragover');
    });
    $dropzone.on('dragleave drop', function(e) {
        e.preventDefault(); e.stopPropagation();
        $dropzone.removeClass('dragover');
    });
    $dropzone.on('drop', function(e) {
        const files = Array.from(e.originalEvent?.dataTransfer?.files||[]);
        if (!isUploading && files.length>0) addFiles(files);
    });

    $(document).on('click', '.admin-remove-file', function() {
        if (isUploading) return;
        const id = String($(this).data('id')||'');
        if (!id) return;
        const item = queue.find(i=>i.id===id);
        if (item && item.previewUrl) URL.revokeObjectURL(item.previewUrl);
        queue = queue.filter(i=>i.id!==id);
        renderQueue();
    });

    $modal.on('show.bs.modal', function() {
        if ($.fn && typeof $.fn.select2==='function') {
            $eventField.select2({ dropdownParent: $modal });
        }
    });
    $modal.on('hidden.bs.modal', function() {
        if (!isUploading) resetQueue();
    });

    /* ── cover form AJAX ── */
    $(document).on('submit', '.gallery-cover-form', function(e) {
        e.preventDefault();
        const form = this;
        $.ajax({
            url: form.action,
            method: 'POST',
            data: $(form).serialize(),
            success: function(res) {
                if (res && res.success) {
                    if (typeof Swal!=='undefined') Swal.fire({icon:'success',title:'Capa definida',text:res.message||'Capa do album atualizada.',timer:2000,showConfirmButton:false});
                    setTimeout(()=>window.location.reload(), 1200);
                }
            },
            error: function() { notify('error','Erro','Nao foi possivel definir a capa.'); }
        });
    });

    /* ── delete forms ── */
    $(document).on('submit', '.delete-form', function(e) {
        e.preventDefault();
        const f = this;
        const title = $(f).data('confirm-title')||'Tem certeza?';
        const text  = $(f).data('confirm-text')||'Esta acao nao podera ser desfeita.';
        if (typeof Swal==='undefined') { if (window.confirm(text)) f.submit(); return; }
        Swal.fire({title,text,icon:'warning',showCancelButton:true,confirmButtonColor:'#1e293b',cancelButtonColor:'#ef4444',confirmButtonText:'Sim, excluir',cancelButtonText:'Cancelar'})
            .then(r=>{ if(r.isConfirmed) f.submit(); });
    });

    /* ── init ── */
    updateActions();
    setProgress(0,'Aguardando selecao dos arquivos.');
});
</script>
