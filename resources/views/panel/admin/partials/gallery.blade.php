@php
    $galleryMedia = $model->media()->orderBy('created_at', 'desc')->get();
@endphp

<div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center">
            <svg class="w-5 h-5 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Galeria de Fotos e Vídeos
        </h3>
    </div>
    
    <div class="p-6">
        <!-- Upload Box -->
        <div id="gallery-uploader" 
             class="relative border-2 border-dashed border-primary-300 dark:border-primary-800 rounded-xl p-8 transition-all hover:bg-primary-50/50 dark:hover:bg-primary-900/10 group cursor-pointer"
             data-url="{{ $uploadUrl }}">
            
            <input type="file" id="file-input" multiple class="hidden" accept="image/*,video/*">
            
            <div class="text-center" onclick="document.getElementById('file-input').click()">
                <div class="mx-auto w-16 h-16 mb-4 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                </div>
                <h5 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-1">Arraste arquivos aqui ou clique para selecionar</h5>
                <p class="text-sm text-gray-500 dark:text-gray-400">Imagens (JPG, PNG, WEBP) e Vídeos (MP4, MOV) até 50MB</p>
                <button type="button" class="mt-4 px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium shadow-lg shadow-primary-500/30 transition-all active:scale-95">
                    Selecionar Arquivos
                </button>
            </div>

            <!-- Progress Bar -->
            <div id="upload-progress" class="hidden absolute inset-0 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm flex flex-col items-center justify-center p-6 rounded-xl">
                <div class="w-full max-w-xs">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium text-primary-700 dark:text-primary-400">Enviando arquivos...</span>
                        <span id="upload-percentage" class="text-sm font-bold text-primary-700 dark:text-primary-400">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                        <div id="progress-bar-fill" class="bg-primary-600 h-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gallery Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mt-8" id="gallery-items">
            @forelse($galleryMedia as $item)
                <div class="relative group aspect-square rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-900 shadow-sm border border-gray-100 dark:border-gray-700 gallery-item" data-id="{{ $item->id }}">
                    @if($item->type === 'image')
                        <img src="{{ $item->url }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-900">
                            <svg class="w-12 h-12 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif

                    <div class="absolute top-2 left-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $item->type === 'image' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }} shadow-sm">
                            {{ ucfirst($item->type) }}
                        </span>
                    </div>

                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <button type="button" 
                                class="p-2 bg-red-600 hover:bg-red-700 text-white rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300 btn-delete-media"
                                data-url="{{ str_replace(':media', $item->id, $deleteUrlPattern) }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 empty-gallery-message">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">Nenhuma mídia enviada ainda.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('gallery-uploader');
    const fileInput = document.getElementById('file-input');
    const progressContainer = document.getElementById('upload-progress');
    const progressBar = document.getElementById('progress-bar-fill');
    const percentageText = document.getElementById('upload-percentage');
    
    // Ensure axios is available (fixes "axios is not defined")
    if (typeof axios === 'undefined' && typeof window.axios === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js';
        script.onload = () => { window.axios = axios; };
        document.head.appendChild(script);
    } else if (typeof window.axios === 'undefined') {
        window.axios = axios;
    }

    const uploadUrl = dropzone.dataset.url;

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('bg-primary-50/50', 'dark:bg-primary-900/10');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('bg-primary-50/50', 'dark:bg-primary-900/10');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('bg-primary-50/50', 'dark:bg-primary-900/10');
        handleFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', () => {
        handleFiles(fileInput.files);
    });

    function handleFiles(files) {
        if (files.length === 0) return;

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        progressContainer.classList.remove('hidden');
        progressBar.style.width = '0%';
        percentageText.innerText = '0%';

        axios.post(uploadUrl, formData, {
            onUploadProgress: (progressEvent) => {
                const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                progressBar.style.width = percentCompleted + '%';
                percentageText.innerText = percentCompleted + '%';
            }
        })
        .then((response) => {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: response.data.message,
                timer: 3000,
                toast: true,
                position: 'top-end',
                showConfirmButton: false
            });
            window.location.reload();
        })
        .catch((error) => {
            let message = 'Erro ao enviar arquivos.';
            if (error.response && error.response.data && error.response.data.message) {
                message = error.response.data.message;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: message
            });
        })
        .finally(() => {
            progressContainer.classList.add('hidden');
            fileInput.val = '';
        });
    }

    document.querySelectorAll('.btn-delete-media').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const url = this.dataset.url;
            const item = this.closest('.gallery-item');

            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, deletar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.delete(url)
                        .then(() => {
                            item.remove();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deletado!',
                                text: 'Mídia removida com sucesso.',
                                timer: 2000,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false
                            });
                        })
                        .catch(() => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: 'Não foi possível deletar o arquivo.'
                            });
                        });
                }
            });
        });
    });
});
</script>
