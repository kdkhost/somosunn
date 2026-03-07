{{-- Componente global de upload arrasta-e-solta com barra de progresso, tempo restante e preview --}}
<div class="upload-global-wrapper">
    <label class="font-weight-bold mb-2">Upload de arquivo/imagem/vídeo</label>
    <div class="upload-drop-area" id="uploadDropArea" style="border:2px dashed #1F5EDB;padding:2rem;text-align:center;background:#f8fafc;cursor:pointer">
        <span class="d-block mb-2">Arraste e solte arquivos aqui ou clique para selecionar</span>
        <input type="file" id="uploadGlobalInput" name="{{ $name ?? 'file' }}" accept="{{ $accept ?? '*' }}" style="display:none" multiple>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('uploadGlobalInput').click()">Selecionar arquivo</button>
    </div>
    <div class="upload-preview mt-3" id="uploadPreview"></div>
    <div class="progress mt-2 d-none" id="uploadProgressBar">
        <div class="progress-bar" role="progressbar" style="width:0%"></div>
    </div>
    <div class="upload-time-remaining text-muted mt-1 d-none" id="uploadTimeRemaining"></div>
</div>

<script>
(function(){
    const dropArea = document.getElementById('uploadDropArea');
    const input = document.getElementById('uploadGlobalInput');
    const preview = document.getElementById('uploadPreview');
    const progressBar = document.getElementById('uploadProgressBar');
    const progress = progressBar.querySelector('.progress-bar');
    const timeRemaining = document.getElementById('uploadTimeRemaining');

    dropArea.addEventListener('click', () => input.click());
    dropArea.addEventListener('dragover', e => { e.preventDefault(); dropArea.style.background='#e0e7ff'; });
    dropArea.addEventListener('dragleave', e => { e.preventDefault(); dropArea.style.background='#f8fafc'; });
    dropArea.addEventListener('drop', e => {
        e.preventDefault();
        dropArea.style.background='#f8fafc';
        input.files = e.dataTransfer.files;
        handleFiles(input.files);
    });
    input.addEventListener('change', () => handleFiles(input.files));

    function handleFiles(files) {
        preview.innerHTML = '';
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.style.maxWidth = '180px';
                img.style.maxHeight = '120px';
                img.className = 'rounded shadow-sm mr-2 mb-2';
                preview.appendChild(img);
            } else if (file.type.startsWith('video/')) {
                const vid = document.createElement('video');
                vid.src = URL.createObjectURL(file);
                vid.controls = true;
                vid.style.maxWidth = '180px';
                vid.style.maxHeight = '120px';
                vid.className = 'rounded shadow-sm mr-2 mb-2';
                preview.appendChild(vid);
            } else {
                const div = document.createElement('div');
                div.textContent = file.name;
                div.className = 'badge badge-secondary mr-2 mb-2';
                preview.appendChild(div);
            }
        });
        // Simulação de upload
        simulateUpload(files[0]);
    }

    function simulateUpload(file) {
        progressBar.classList.remove('d-none');
        timeRemaining.classList.remove('d-none');
        let percent = 0;
        let seconds = 10;
        progress.style.width = '0%';
        progress.textContent = '0%';
        timeRemaining.textContent = 'Tempo restante: 10s';
        const interval = setInterval(() => {
            percent += 10;
            seconds -= 1;
            progress.style.width = percent+'%';
            progress.textContent = percent+'%';
            timeRemaining.textContent = 'Tempo restante: '+seconds+'s';
            if(percent >= 100) {
                clearInterval(interval);
                timeRemaining.textContent = 'Upload concluído';
            }
        }, 1000);
    }
})();
</script>
<style>
.upload-global-wrapper img, .upload-global-wrapper video { display:inline-block; margin-right:8px; margin-bottom:8px; }
.upload-drop-area { transition:background .2s; }
</style>
