<div class="card card-outline card-primary" id="media-uploader">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-cloud-upload-alt me-2"></i>Upload de mídias</h3>
        <small class="text-muted">Arraste e solte fotos, vídeos, áudios ou documentos.</small>
    </div>
    <div class="card-body">
        <div class="border border-dashed rounded bg-light p-4 text-center" id="dropzone" style="min-height:140px;">
            <p class="mb-2"><i class="fas fa-cloud-upload-alt fa-2x text-primary"></i></p>
            <p class="mb-1">Solte os arquivos aqui ou clique para selecionar</p>
            <input type="file" id="fileInput" class="d-none" multiple>
            <button class="btn btn-sm btn-primary" id="selectFilesBtn">Escolher arquivos</button>
            <p class="mt-2 text-muted small">Tipos permitidos: imagens, vídeo, áudio, pdf/doc/xls. Tamanho máx.: 50MB.</p>
        </div>

        <div class="mt-3" id="uploadList"></div>
    </div>
</div>

<div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Ajustar imagem</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
      <div class="modal-body">
        <div class="w-100" style="max-height:60vh;overflow:hidden;">
            <img id="cropImage" src="" class="img-fluid" alt="Preview">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="cropConfirmBtn">Cortar & enviar</button>
      </div>
    </div>
  </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
(()=>{
    const dz = document.getElementById('dropzone');
    const input = document.getElementById('fileInput');
    const list = document.getElementById('uploadList');
    const btn = document.getElementById('selectFilesBtn');
    const maxSize = 50 * 1024 * 1024;
    const allowed = ['image/','video/','audio/','application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    let cropper = null;
    let pendingRow = null;

    const renderItem = (file, previewUrl=null) => {
        const row = document.createElement('div');
        row.className = 'mb-2';
        const isImage = file.type.startsWith('image/');
        const preview = isImage && previewUrl ? `<img src="${previewUrl}" class="rounded mr-2" style="width:48px;height:48px;object-fit:cover;">` : `<i class="fas fa-file mr-2"></i>`;
        row.innerHTML = `
            <div class="d-flex align-items-center justify-content-between bg-white border rounded p-2">
                <div class="d-flex align-items-center gap-2">
                    ${preview}
                    <span>${file.name} (${(file.size/1024/1024).toFixed(1)} MB)</span>
                </div>
                <div class="flex-grow-1 mx-3">
                    <div class="progress" style="height:6px;"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small status">Aguardando</span>
                    ${isImage ? '<button class="btn btn-xs btn-outline-secondary crop-btn" title="Cortar"><i class="fas fa-crop"></i></button>' : ''}
                </div>
            </div>`;
        return row;
    };

    const uploadFile = (file, row) => {
        const bar = row.querySelector('.progress-bar');
        const status = row.querySelector('.status');
        const form = new FormData();
        form.append('file', file);
        status.textContent = 'Enviando';
        axios.post('/upload', form, {
            headers: {'Content-Type': 'multipart/form-data'},
            onUploadProgress: (e) => {
                const pct = Math.round((e.loaded * 100) / e.total);
                bar.style.width = pct + '%';
            }
        }).then(() => {
            bar.classList.replace('bg-primary','bg-success');
            status.textContent = 'Concluído';
        }).catch(() => {
            bar.classList.replace('bg-primary','bg-danger');
            status.textContent = 'Erro';
        });
    };

    const openCropper = (file, row, previewUrl) => {
        const img = document.getElementById('cropImage');
        img.src = previewUrl;
        $('#cropModal').modal('show');
        pendingRow = {file,row};
        if(cropper){ cropper.destroy(); }
        cropper = new Cropper(img, {viewMode:1, autoCropArea:1, responsive:true, background:false});
    };

    const handleFiles = (files) => {
        Array.from(files).forEach(file => {
            if(!allowed.some(type => file.type.startsWith(type)) && !allowed.includes(file.type)) return;
            if(file.size > maxSize) return;
            const reader = new FileReader();
            reader.onload = (e)=>{
                const row = renderItem(file, e.target.result);
                list.appendChild(row);
                const cropBtn = row.querySelector('.crop-btn');
                if(cropBtn){
                    cropBtn.addEventListener('click', (ev)=>{
                        ev.preventDefault();
                        openCropper(file, row, e.target.result);
                    });
                } else {
                    uploadFile(file, row);
                }
            };
            reader.readAsDataURL(file);
        });
    };

    dz.addEventListener('dragover', e => {e.preventDefault(); dz.classList.add('border-primary');});
    dz.addEventListener('dragleave', e => {dz.classList.remove('border-primary');});
    dz.addEventListener('drop', e => {e.preventDefault(); dz.classList.remove('border-primary'); handleFiles(e.dataTransfer.files);});
    dz.addEventListener('click', () => input.click());
    btn.addEventListener('click', (e)=>{ e.preventDefault(); input.click(); });
    input.addEventListener('change', () => handleFiles(input.files));

    document.getElementById('cropConfirmBtn').addEventListener('click', ()=>{
        if(!cropper || !pendingRow) return;
        cropper.getCroppedCanvas().toBlob(blob=>{
            uploadFile(new File([blob], pendingRow.file.name, {type: pendingRow.file.type}), pendingRow.row);
            $('#cropModal').modal('hide');
            cropper.destroy(); cropper=null; pendingRow=null;
        }, pendingRow.file.type);
    });
})();
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\partials\media_uploader.blade.php ENDPATH**/ ?>