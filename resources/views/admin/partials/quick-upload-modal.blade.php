@php
    $quickUploadPerFileLimitBytes = \App\Support\UploadStorage::effectiveUploadLimitBytes(20 * 1024 * 1024) ?? (20 * 1024 * 1024);
    $quickUploadPerFileLimitMb = number_format($quickUploadPerFileLimitBytes / 1024 / 1024, 2, '.', '');
    $quickUploadEvents = \App\Models\Event::query()
        ->select(['id', 'title', 'start_at'])
        ->orderBy('start_at', 'desc')
        ->limit(250)
        ->get()
        ->map(fn ($event) => [
            'id' => $event->id,
            'title' => $event->title,
            'start' => $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') : '',
        ])
        ->values();
@endphp

<div class="modal fade" id="modalQuickUpload" tabindex="-1" aria-labelledby="modalQuickUploadLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 quick-upload-modal-shell">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center" id="modalQuickUploadLabel">
                    <i class="fas fa-camera-retro me-2"></i> Registro Rápido de Mídias
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body p-4 p-lg-5">
                <div id="quickUploadStep1">
                    <label class="fw-bold mb-2 d-block">1. Selecione o evento</label>
                    <div id="quickUploadSearchWrap" class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-search text-secondary"></i></span>
                        <input type="text" id="quickUploadSearch" class="form-control" placeholder="Digite o nome do evento para buscar...">
                    </div>
                    <div id="quickUploadResults" class="list-group mb-3 overflow-auto shadow-sm" style="max-height:260px;display:none;"></div>
                    <div id="quickUploadSelected" class="alert alert-info d-none d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <i class="fas fa-calendar-check me-2"></i>
                            <span id="quickUploadSelectedName" class="fw-bold">Nenhum evento selecionado</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-link fw-bold p-0" onclick="window.clearQuickUploadSelection()">Trocar evento</button>
                    </div>
                </div>

                <div id="quickUploadStep2" class="mt-4 d-none">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-2">
                        <label class="fw-bold mb-1 mb-lg-0">2. Selecione e envie arquivos</label>
                        <small class="text-secondary">Arraste várias imagens e vídeos com preview, progresso e tempo restante por arquivo.</small>
                    </div>

                    <input type="file" id="quickUploadInput" multiple accept="image/*,video/*" class="d-none">

                    <div class="premium-upload-box mb-3">
                        <div class="drop-zone-area p-5 text-center border border-2 border-dashed rounded-3 position-relative quick-upload-dropzone" id="quickUploadDropZone" role="button" tabindex="0">
                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3 d-block"></i>
                            <h5 class="fw-bold mb-2">Arraste fotos e vídeos aqui</h5>
                            <p class="text-secondary mb-3">ou <span class="text-primary text-decoration-underline">clique para selecionar</span></p>
                            <div class="d-flex justify-content-center flex-wrap gap-2 mb-2">
                                <span class="badge rounded-pill text-bg-primary px-3">Imagens</span>
                                <span class="badge rounded-pill text-bg-info px-3">Vídeos</span>
                                <span class="badge rounded-pill text-bg-secondary px-3">Até {{ $quickUploadPerFileLimitMb }} MB por arquivo</span>
                            </div>
                        </div>

                        <div id="quickUploadSelectedFiles" class="card border-0 shadow-sm mt-3 d-none">
                            <div class="card-body p-3">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
                                    <div>
                                        <p class="text-uppercase text-secondary small fw-bold mb-1">Arquivos prontos</p>
                                        <p id="quickUploadSelectedSummary" class="fw-bold mb-0">0 arquivo(s)</p>
                                    </div>
                                    <span id="quickUploadSelectedSize" class="badge rounded-pill text-bg-light px-3 py-2 mt-2 mt-md-0">0 B</span>
                                </div>
                                <div id="quickUploadSelectedList" class="quick-upload-selected-list"></div>
                            </div>
                        </div>

                        <div id="quickUploadProgress" class="mt-3 d-none">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-primary" id="quickUploadStatus">Aguardando envio...</span>
                                <span class="small fw-bold" id="quickUploadPercent">0%</span>
                            </div>
                            <div class="progress" style="height:8px;border-radius:999px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="quickUploadProgressBar" role="progressbar" style="width:0%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-secondary" id="quickUploadDetails">0 / 0 arquivos enviados</small>
                                <small class="text-secondary" id="quickUploadRemaining">pronto para iniciar</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 d-flex flex-column flex-md-row justify-content-between">
                <button type="button" class="btn btn-secondary rounded-pill px-4 mb-2 mb-md-0" data-bs-dismiss="modal">Fechar</button>
                <div class="d-flex flex-column flex-md-row gap-2">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 d-none" id="quickUploadAddFiles"><i class="fas fa-plus me-1"></i> Adicionar arquivos</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="quickUploadSubmit" disabled><i class="fas fa-paper-plane me-1"></i> Publicar na galeria</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    let selectedEventId=null, searchTimeout=null, uploadQueue=[], isUploading=false, queueSeed=0, renderTick=0;
    const modal=$('#modalQuickUpload'), searchWrap=$('#quickUploadSearchWrap'), searchInput=$('#quickUploadSearch'), results=$('#quickUploadResults');
    const selectedBox=$('#quickUploadSelected'), selectedName=$('#quickUploadSelectedName'), step2=$('#quickUploadStep2');
    const fileInput=document.getElementById('quickUploadInput'), dropZone=document.getElementById('quickUploadDropZone');
    const addFilesButton=document.getElementById('quickUploadAddFiles'), submitButton=document.getElementById('quickUploadSubmit');
    const selectedFilesWrap=document.getElementById('quickUploadSelectedFiles'), selectedSummary=document.getElementById('quickUploadSelectedSummary');
    const selectedSize=document.getElementById('quickUploadSelectedSize'), selectedList=document.getElementById('quickUploadSelectedList');
    const progressBox=$('#quickUploadProgress'), progressBar=$('#quickUploadProgressBar'), percentText=$('#quickUploadPercent');
    const statusText=$('#quickUploadStatus'), detailsText=$('#quickUploadDetails'), remainingText=$('#quickUploadRemaining');
    const perFileLimitBytes=Math.max(1, parseInt(window.UNN_ADMIN_UPLOAD_MAX_BYTES || {{ $quickUploadPerFileLimitBytes }}, 10) || {{ $quickUploadPerFileLimitBytes }});
    const availableEvents=@json($quickUploadEvents);

    const fmtBytes=(bytes)=>{const v=Number(bytes||0); if(!Number.isFinite(v)||v<=0) return '0 B'; const u=['B','KB','MB','GB']; const e=Math.min(Math.floor(Math.log(v)/Math.log(1024)),u.length-1); const a=v/Math.pow(1024,e); return a.toFixed(a>=100||e===0?0:1)+' '+u[e];};
    const fmtRemain=(seconds)=>{const v=Number(seconds||0); if(!Number.isFinite(v)||v<=0) return 'calculando tempo restante...'; const r=Math.round(v); if(r<60) return r+'s restantes'; const m=Math.floor(r/60), s=r%60; if(m<60) return m+'min '+s+'s restantes'; const h=Math.floor(m/60), rm=m%60; return h+'h '+rm+'min restantes';};
    const esc=(value)=>String(value||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    const kindOf=(file)=>{const t=String(file.type||'').toLowerCase(), n=String(file.name||'').toLowerCase(); if(t.startsWith('image/')||/\.(png|jpe?g|gif|webp|svg)$/.test(n)) return 'image'; if(t.startsWith('video/')||/\.(mp4|mov|m4v|webm|mkv)$/.test(n)) return 'video'; return 'file';};
    const signOf=(file)=>[file.name,file.size,file.lastModified].join('::');
    const metaOf=(item)=>item.state==='uploading'?{badge:'text-bg-primary',text:'enviando',bar:'bg-primary progress-bar-striped progress-bar-animated',status:'text-primary'}:item.state==='done'?{badge:'text-bg-success',text:'concluído',bar:'bg-success',status:'text-success'}:item.state==='error'?{badge:'text-bg-danger',text:'falhou',bar:'bg-danger',status:'text-danger'}:{badge:item.kind==='video'?'text-bg-info':'text-bg-secondary',text:item.kind==='video'?'vídeo':'imagem',bar:'bg-secondary',status:'text-secondary'};
    const previewOf=(item)=>item.kind==='image'&&item.previewUrl?'<img src="'+item.previewUrl+'" alt="'+esc(item.file.name)+'" class="quick-upload-preview-image" loading="lazy" decoding="async">':item.kind==='video'&&item.previewUrl?'<video src="'+item.previewUrl+'" class="quick-upload-preview-video" muted preload="metadata"></video>':'<div class="quick-upload-preview-fallback"><i class="fas fa-file-alt"></i></div>';

    function buildItem(file){const kind=kindOf(file); return {id:'quick-upload-item-'+(++queueSeed), signature:signOf(file), file, kind, previewUrl:(kind==='image'||kind==='video')?URL.createObjectURL(file):'', progress:0, uploadedBytes:0, state:'ready', remaining:'pronto para iniciar', error:''};}
    function revokeItem(item){if(item&&item.previewUrl){URL.revokeObjectURL(item.previewUrl); item.previewUrl='';}}
    function revokeQueue(){uploadQueue.forEach(revokeItem);}
    function resetProgress(){progressBox.addClass('d-none'); progressBar.css('width','0%'); percentText.text('0%'); statusText.text('Aguardando envio...'); detailsText.text('0 / 0 arquivos enviados'); remainingText.text('pronto para iniciar');}
    function updateActionState(){const hasEvent=Boolean(selectedEventId), hasFiles=uploadQueue.length>0; submitButton.disabled=!hasEvent||!hasFiles||isUploading; submitButton.innerHTML=isUploading?'<i class="fas fa-spinner fa-spin me-1"></i> Enviando...':'<i class="fas fa-paper-plane me-1"></i> Publicar na galeria'; addFilesButton.classList.toggle('d-none',!hasEvent); addFilesButton.disabled=!hasEvent||isUploading; dropZone.classList.toggle('is-disabled',isUploading);}
    function updateGlobal(status, remaining){if(uploadQueue.length===0){resetProgress(); return;} const total=uploadQueue.length; const processed=uploadQueue.reduce((sum,item)=>sum+(item.state==='done'||item.state==='error'?1:(item.state==='uploading'?item.progress/100:0)),0); const done=uploadQueue.filter((item)=>item.state==='done').length; const active=uploadQueue.find((item)=>item.state==='uploading'); const pct=Math.min(100,Math.max(0,Math.round((processed/total)*100))); progressBox.removeClass('d-none'); progressBar.css('width',pct+'%'); percentText.text(pct+'%'); detailsText.text(done+' / '+total+' arquivos enviados'); statusText.text(status|| (active?('Enviando '+active.file.name):'Preparando lote...')); remainingText.text(remaining || (active?active.remaining:'pronto para iniciar'));}
    function renderSelectedFiles(){if(uploadQueue.length===0){selectedFilesWrap.classList.add('d-none'); selectedSummary.textContent='0 arquivo(s)'; selectedSize.textContent='0 B'; selectedList.innerHTML=''; updateActionState(); return;} const totalBytes=uploadQueue.reduce((sum,item)=>sum+Number(item.file.size||0),0); selectedFilesWrap.classList.remove('d-none'); selectedSummary.textContent=uploadQueue.length+' arquivo(s) selecionado(s)'; selectedSize.textContent=fmtBytes(totalBytes); selectedList.innerHTML=uploadQueue.map((item)=>{const meta=metaOf(item), progress=Math.min(100,Math.max(0,Math.round(Number(item.progress||0)))); return '<div class="quick-upload-file-card"><div class="quick-upload-file-preview'+(item.kind==='video'?' is-video':'')+'">'+previewOf(item)+'</div><div class="quick-upload-file-main"><div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between mb-2"><div class="min-w-0 pe-lg-3"><div class="d-flex flex-wrap align-items-center gap-2 mb-1"><span class="badge '+meta.badge+' text-uppercase">'+meta.text+'</span><span class="fw-bold quick-upload-file-name">'+esc(item.file.name)+'</span></div><div class="small text-secondary">'+fmtBytes(item.file.size||0)+'</div></div><button type="button" class="btn btn-link text-danger p-0 quick-upload-remove-file" data-id="'+item.id+'" '+(isUploading?'disabled':'')+'><i class="fas fa-trash"></i></button></div><div class="progress quick-upload-file-progress"><div class="progress-bar '+meta.bar+'" style="width:'+progress+'%"></div></div><div class="d-flex justify-content-between align-items-center mt-2"><small class="'+meta.status+' fw-bold">'+esc(item.remaining||'pronto para iniciar')+'</small><small class="text-secondary">'+progress+'%</small></div>'+(item.error?'<small class="quick-upload-item-error">'+esc(item.error)+'</small>':'')+'</div></div>';}).join(''); updateActionState();}
    function scheduleRender(force){const now=Date.now(); if(force || (now-renderTick)>120){renderTick=now; renderSelectedFiles();}}
    function clearQuickUploadSelection(){selectedEventId=null; isUploading=false; revokeQueue(); uploadQueue=[]; step2.addClass('d-none'); selectedBox.addClass('d-none'); searchWrap.removeClass('d-none'); searchInput.val('').prop('disabled',false); results.hide().empty(); fileInput.value=''; resetProgress(); renderSelectedFiles();}
    function showError(message){Swal.fire({icon:'error', title:'Erro no upload', text:message||'Falha ao enviar os arquivos.'});}
    function mergeFiles(fileList){const invalid=[]; Array.from(fileList||[]).forEach((file)=>{const kind=kindOf(file); if(kind==='file'){invalid.push(file.name+' possui formato não suportado.'); return;} if((file.size||0)>perFileLimitBytes){invalid.push(file.name+' excede o limite de '+fmtBytes(perFileLimitBytes)+' por arquivo.'); return;} if(!uploadQueue.some((item)=>item.signature===signOf(file))){uploadQueue.push(buildItem(file));}}); renderSelectedFiles(); if(invalid.length) showError(invalid[0]);}
    async function sendSingleFile(item, index, total){const formData=new FormData(); formData.append('files[]', item.file); item.state='uploading'; item.progress=0; item.uploadedBytes=0; item.remaining='calculando tempo restante...'; item.error=''; scheduleRender(true); updateGlobal('Enviando '+(index+1)+' de '+total+': '+item.file.name, item.remaining); return new Promise((resolve,reject)=>{const start=Date.now(); const xhr=new XMLHttpRequest(); xhr.open('POST', '{{ url("/admin/events") }}/'+selectedEventId+'/media', true); xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}'); xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); xhr.setRequestHeader('Accept', 'application/json'); xhr.upload.addEventListener('progress', (event)=>{const totalBytes=Number(event.total||item.file.size||0); const loaded=Number(event.loaded||0); const elapsed=Math.max((Date.now()-start)/1000,0.2); const speed=loaded/elapsed; item.uploadedBytes=loaded; item.progress=Math.min(100, Math.round((loaded/Math.max(totalBytes,1))*100)); item.remaining=fmtRemain(speed>0 ? ((totalBytes-loaded)/speed) : 0); scheduleRender(false); updateGlobal('Enviando '+(index+1)+' de '+total+': '+item.file.name, item.remaining);}); xhr.addEventListener('load', ()=>{let payload={}; try{payload=xhr.responseText?JSON.parse(xhr.responseText):{};}catch(error){payload={};} if(xhr.status>=200 && xhr.status<300){resolve({data:payload}); return;} reject({response:{data:payload}, message:payload.message || ('Falha no upload (HTTP '+xhr.status+')')});}); xhr.addEventListener('error', ()=>reject(new Error('Falha de conexão durante o upload.'))); xhr.addEventListener('abort', ()=>reject(new Error('Upload cancelado.'))); xhr.send(formData);});}
    async function handleQuickUpload(){if(!selectedEventId){showError('Selecione um evento antes de enviar os arquivos.'); return;} if(uploadQueue.length===0){showError('Selecione pelo menos um arquivo para publicar.'); return;} isUploading=true; updateGlobal('Preparando lote...','iniciando'); updateActionState(); renderSelectedFiles(); const failures=[]; let successCount=0; const totalFiles=uploadQueue.length; for(let index=0; index<totalFiles; index++){const item=uploadQueue[index]; try{const response=await sendSingleFile(item,index,totalFiles); if(response.data && response.data.success){item.state='done'; item.progress=100; item.uploadedBytes=item.file.size||item.uploadedBytes; item.remaining='concluído'; item.error=''; successCount+=Number(response.data.uploaded_count||1); scheduleRender(true); updateGlobal('Arquivo publicado: '+item.file.name,'concluído'); continue;} throw new Error(response.data?.message || 'Falha no upload');}catch(error){const message=error.response?.data?.message || error.message || 'Falha no upload'; item.state='error'; item.progress=100; item.uploadedBytes=0; item.remaining='falhou'; item.error=message; failures.push(item.file.name+': '+message); scheduleRender(true); updateGlobal('Falha ao enviar '+item.file.name,'falhou');}} isUploading=false; progressBar.css('width','100%'); percentText.text('100%'); detailsText.text(successCount+' / '+totalFiles+' arquivos enviados'); remainingText.text(failures.length?'lote finalizado com ressalvas':'concluído'); statusText.text(failures.length?'Concluído com falhas':'Upload concluído'); updateActionState(); renderSelectedFiles(); if(successCount===0){showError(failures[0] || 'Nenhum arquivo conseguiu ser enviado.'); return;} const successMessage=successCount+' arquivo(s) enviado(s) com sucesso.'+(failures.length?' '+failures.length+' falharam.':''); Swal.fire({icon:failures.length?'warning':'success', title:failures.length?'Upload concluído com ressalvas':'Upload concluído', text:successMessage, confirmButtonText:'OK'}).then(function(){ $('#modalQuickUpload').modal('hide'); if(selectedEventId && window.location.href.indexOf('/admin/events/'+selectedEventId)!==-1){ window.location.reload(); }});}

    window.openQuickUploadModal=function(){modal.modal('show'); clearQuickUploadSelection();};
    window.clearQuickUploadSelection=clearQuickUploadSelection;
    modal.on('hidden.bs.modal', clearQuickUploadSelection);
    searchInput.on('input', function(){const query=String($(this).val()||'').trim(); if(searchTimeout) clearTimeout(searchTimeout); if(query.length<2){results.hide().empty(); return;} searchTimeout=setTimeout(function(){const lowered=query.toLowerCase(); const events=availableEvents.filter((item)=>String(item.title||'').toLowerCase().indexOf(lowered)!==-1).slice(0,30); results.empty().show(); if(!events.length){results.append('<div class="list-group-item text-secondary">Nenhum evento encontrado</div>'); return;} events.forEach(function(item){$('<button type="button" class="list-group-item list-group-item-action py-3">').html('<strong>'+esc(item.title)+'</strong> <small class="text-secondary ms-2">'+esc(item.start||'')+'</small>').on('click', function(){selectedEventId=item.id; selectedName.text(item.title); selectedBox.removeClass('d-none'); searchWrap.addClass('d-none'); results.hide(); step2.removeClass('d-none'); renderSelectedFiles();}).appendTo(results);});},180);});
    ['dragenter','dragover'].forEach((eventName)=>dropZone.addEventListener(eventName,(event)=>{event.preventDefault(); event.stopPropagation(); if(!isUploading) dropZone.classList.add('is-dragover');}));
    ['dragleave','drop'].forEach((eventName)=>dropZone.addEventListener(eventName,(event)=>{event.preventDefault(); event.stopPropagation(); dropZone.classList.remove('is-dragover');}));
    dropZone.addEventListener('click', ()=>{if(!isUploading && selectedEventId) fileInput.click();});
    dropZone.addEventListener('keydown', (event)=>{if((event.key==='Enter'||event.key===' ') && !isUploading && selectedEventId){event.preventDefault(); fileInput.click();}});
    dropZone.addEventListener('drop', (event)=>{if(!isUploading) mergeFiles(event.dataTransfer.files);});
    fileInput.addEventListener('change', function(){mergeFiles(this.files); this.value='';});
    addFilesButton.addEventListener('click', ()=>{if(!isUploading && selectedEventId) fileInput.click();});
    submitButton.addEventListener('click', handleQuickUpload);
    document.addEventListener('click', function(event){const button=event.target.closest('.quick-upload-remove-file'); if(!button || isUploading) return; const id=String(button.getAttribute('data-id')||''); if(!id) return; const next=[]; uploadQueue.forEach((item)=>{if(item.id===id){revokeItem(item); return;} next.push(item);}); uploadQueue=next; renderSelectedFiles(); updateGlobal();});
    clearQuickUploadSelection();
})();
</script>
@endpush

<style>
    .quick-upload-modal-shell .quick-upload-dropzone { border-color: #d0dae8; background: var(--bs-tertiary-bg); cursor: pointer; transition: all .2s ease; }
    .quick-upload-modal-shell .quick-upload-dropzone:hover,
    .quick-upload-modal-shell .quick-upload-dropzone.is-dragover { border-color: #1F5EDB; box-shadow: 0 0 0 4px rgba(31, 94, 219, .12); }
    .quick-upload-modal-shell .quick-upload-dropzone.is-disabled { opacity: .55; pointer-events: none; }
    .quick-upload-selected-list { display: grid; gap: .9rem; max-height: 360px; overflow-y: auto; }
    .quick-upload-file-card { display: flex; align-items: flex-start; gap: 1rem; padding: 1rem; border-radius: .75rem; background: var(--bs-body-bg); border: 1px solid var(--bs-border-color); }
    .quick-upload-file-preview { width: 96px; height: 96px; border-radius: .75rem; overflow: hidden; background: var(--bs-tertiary-bg); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .quick-upload-file-preview.is-video { background: #dbeafe; }
    .quick-upload-preview-image, .quick-upload-preview-video { width: 100%; height: 100%; object-fit: cover; display: block; }
    .quick-upload-preview-fallback { color: #64748b; font-size: 1.6rem; }
    .quick-upload-file-main { flex: 1; min-width: 0; }
    .quick-upload-file-name { word-break: break-word; }
    .quick-upload-file-progress { height: .55rem; border-radius: 999px; overflow: hidden; }
    .quick-upload-item-error { display: block; margin-top: .45rem; color: #dc2626; }
    @media (max-width: 767.98px) { .quick-upload-file-card { flex-direction: column; } .quick-upload-file-preview { width: 100%; height: 180px; } }
</style>
