@extends('panel.layouts.app')

@section('title', 'Galeria de Fotos')

@section('content')
    <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
            <div>
                <h1 class="text-3xl font-black text-slate-900 drop-shadow-sm tracking-tight">Galeria Coletiva</h1>
                <p class="text-slate-500 font-medium mt-1">Compartilhe e gerencie as fotos dos eventos da UNN.</p>
            </div>

            <button onclick="openUploadModal()"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-3xl font-black transition-all shadow-xl shadow-blue-200 active:scale-95 group">
                <i class="fas fa-camera group-hover:rotate-12 transition-transform"></i>
                ADICIONAR FOTOS
            </button>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-[40px] p-8 shadow-sm border border-slate-100 mb-10">
            <form action="{{ route('panel.gallery.index') }}" method="GET" class="flex flex-wrap items-end gap-6">
                <div class="flex-1 min-w-[280px]">
                    <label class="block text-sm font-black text-slate-800 mb-3 ml-1 uppercase tracking-wider">EVENTO</label>
                    <div class="relative">
                        <select name="event_id"
                            class="w-full bg-slate-50 border-slate-200 border-2 rounded-2xl px-5 py-4 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all font-bold text-slate-700 appearance-none">
                            <option value="">TODOS OS EVENTOS</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                    {{ $event->title }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="bg-slate-900 text-white px-10 py-4 rounded-2xl font-black hover:bg-slate-800 transition-all shadow-lg active:scale-95">
                        FILTRAR
                    </button>
                    @if(request()->anyFilled(['event_id']))
                        <a href="{{ route('panel.gallery.index') }}"
                            class="px-6 py-4 text-slate-500 font-black hover:text-red-500 transition-all text-sm uppercase">
                            Limpar Filtros
                        </a>
                    @endif
                </div>

                @if(auth()->user()->isAdmin())
                    <div class="ml-auto flex items-center gap-2 px-6 py-3 bg-blue-50 border border-blue-100 rounded-2xl">
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                        <span class="text-blue-700 text-xs font-black uppercase tracking-tighter">Modo Admin Ativo</span>
                    </div>
                @endif
            </form>
        </div>

        <!-- Grid de Fotos -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-8">
            @forelse($media as $item)
                <div
                    class="group relative bg-white p-2 rounded-[32px] overflow-hidden shadow-sm border border-slate-100 transition-all hover:shadow-2xl hover:-translate-y-2">
                    <div class="aspect-square overflow-hidden relative rounded-[24px] bg-slate-100">
                        <img src="{{ asset('storage/' . $item->file_path) }}"
                            class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700 cursor-pointer"
                            onclick="openGalleryLightbox('{{ asset('storage/' . $item->file_path) }}')" alt="Foto Galeria">

                        <!-- Ações -->
                        <div
                            class="absolute top-4 right-4 flex gap-3 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                            <form action="{{ route('panel.gallery.destroy', $item) }}" method="POST" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-12 h-12 bg-white/90 backdrop-blur-md text-red-600 rounded-2xl flex items-center justify-center hover:bg-red-600 hover:text-white transition-all shadow-2xl">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>

                        <div class="absolute bottom-4 left-4 flex gap-2">
                            @if($item->watermarked)
                                <span
                                    class="bg-blue-600/90 backdrop-blur-md text-white text-[10px] uppercase font-black px-3 py-1.5 rounded-xl shadow-lg border border-white/20">
                                    <i class="fas fa-shield-check mr-1 text-white"></i> Original
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-4 pt-5 pb-5">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] truncate mb-2"
                            title="{{ $item->event->title }}">
                            {{ $item->event->title }}
                        </p>
                        <div class="flex items-center justify-between border-t border-slate-50 pt-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center overflow-hidden border border-slate-200">
                                    <i class="fas fa-user-circle text-slate-300 text-sm"></i>
                                </div>
                                <span class="text-[11px] font-black text-slate-800 truncate max-w-[80px]">
                                    {{ (auth()->user()->isAdmin() && $item->user) ? $item->user->name : 'Você' }}
                                </span>
                            </div>
                            <span
                                class="text-[11px] font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">
                                {{ $item->created_at->format('d/m/y') }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-24 text-center">
                    <div
                        class="w-32 h-32 bg-slate-50 rounded-[40px] flex items-center justify-center mx-auto mb-8 shadow-inner">
                        <i class="fas fa-camera-retro text-slate-200 text-5xl"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-3">Sua galeria pessoal está vazia</h3>
                    <p class="text-slate-500 font-medium mb-10 max-w-md mx-auto">Você ainda não compartilhou fotos dos eventos
                        UNN. Comece agora ajudando a construir nossa história!</p>
                    <button onclick="openUploadModal()"
                        class="bg-blue-600 text-white px-10 py-4 rounded-3xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95">
                        SUBIR MINHA PRIMEIRA FOTO
                    </button>
                </div>
            @endforelse
        </div>

        <div class="mt-20">
            {{ $media->links() }}
        </div>
    </div>

    <!-- Modal de Upload -->
    <div id="uploadModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-6">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-md transition-opacity" onclick="closeUploadModal()">
            </div>

            <div
                class="relative bg-white rounded-[50px] shadow-2xl w-full max-w-xl p-10 transform transition-all animate-modal-in">
                <div class="flex items-center justify-between mb-10">
                    <div>
                        <h2 class="text-3xl font-black text-slate-900 tracking-tight">Novas Fotos</h2>
                        <p class="text-slate-500 font-medium text-sm mt-1">Carregue suas mídias para a galeria UNN.</p>
                    </div>
                    <button onclick="closeUploadModal()"
                        class="w-12 h-12 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center hover:bg-slate-100 transition-colors border border-slate-100">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form action="{{ route('panel.gallery.upload') }}" method="POST" enctype="multipart/form-data"
                    id="uploadForm" class="premium-upload-box">
                    @csrf
                    <div class="mb-8">
                        <label class="block text-sm font-black text-slate-800 mb-3 ml-1 uppercase tracking-wider">QUAL É O
                            EVENTO?</label>
                        <div class="relative">
                            <select name="event_id" required
                                class="w-full bg-slate-50 border-slate-200 border-2 rounded-[24px] px-6 py-4 focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all font-bold text-slate-700 appearance-none">
                                <option value="">Selecione o evento associado...</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}">{{ $event->title }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mb-10">
                        <label
                            class="block text-sm font-black text-slate-800 mb-3 ml-1 uppercase tracking-wider text-center md:text-left">SELECIONE
                            AS FOTOS</label>
                        <div class="relative group">
                            <div id="dropzone"
                                class="drop-zone-area w-full h-64 flex flex-col items-center justify-center gap-4 transition-all">
                                <div
                                    class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center shadow-xl text-blue-500 text-3xl group-hover:scale-110 group-hover:rotate-6 transition-all border border-slate-100">
                                    <i class="fas fa-images"></i>
                                </div>
                                <div class="text-center px-4">
                                    <p
                                        class="text-lg font-black text-slate-800 group-hover:text-blue-600 transition-colors">
                                        Clique ou arraste suas fotos</p>
                                    <p class="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-[0.2em]">
                                        Formatos aceitos: JPG, PNG, WEBP (Máx 50MB)</p>
                                </div>
                                <span id="fileCount"
                                    class="absolute top-4 right-4 bg-blue-600 text-white text-[10px] font-black px-4 py-2 rounded-full hidden animate-pulse shadow-lg"></span>
                            </div>
                            <input type="file" name="files[]" multiple required accept="image/*" id="fileInput"
                                class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <button type="submit" id="submitBtn"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-10 py-5 rounded-3xl font-black text-lg shadow-2xl shadow-blue-200 transition-all active:scale-95 flex items-center justify-center gap-3">
                            <i class="fas fa-cloud-upload-alt"></i>
                            PUBLICAR NA GALERIA
                        </button>
                        <p class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">
                            <i class="fas fa-magic mr-1"></i> Marca d'água será aplicada automaticamente
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Lightbox --}}
    <div id="galleryLightbox"
        class="fixed inset-0 z-[100] bg-slate-900/98 backdrop-blur-xl hidden flex items-center justify-center p-6"
        onclick="this.classList.add('hidden')">
        <button
            class="absolute top-8 right-8 w-12 h-12 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-all">
            <i class="fas fa-times text-xl"></i>
        </button>
        <img id="lightboxImg" src=""
            class="max-w-full max-h-full rounded-[32px] shadow-2xl transform scale-95 opacity-0 transition-all duration-500 border-4 border-white/10">
    </div>

    @push('scripts')
        <script>
            const uploadModal = document.getElementById('uploadModal');
            const uploadForm = document.getElementById('uploadForm');
            const fileInput = document.getElementById('fileInput');
            const dropzone = document.getElementById('dropzone');
            const fileCountEl = document.getElementById('fileCount');
            const submitBtn = document.getElementById('submitBtn');

            function openUploadModal() {
                uploadModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeUploadModal() {
                uploadModal.classList.add('hidden');
                document.body.style.overflow = '';
                uploadForm.reset();
                updateFileCount();
            }

            function updateFileCount() {
                const count = fileInput.files.length;
                if (count > 0) {
                    fileCountEl.textContent = `${count} ${count > 1 ? 'FOTOS' : 'FOTO'}`;
                    fileCountEl.classList.remove('hidden');
                } else {
                    fileCountEl.classList.add('hidden');
                }
            }

            // Drag and Drop Logic
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, e => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            dropzone.addEventListener('dragover', () => uploadForm.classList.add('dragover'));
            dropzone.addEventListener('dragleave', () => uploadForm.classList.remove('dragover'));
            dropzone.addEventListener('drop', (e) => {
                uploadForm.classList.remove('dragover');
                fileInput.files = e.dataTransfer.files;
                updateFileCount();
            });

            dropzone.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', updateFileCount);

            // Form Submit with SweetAlert2
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (fileInput.files.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nenhuma foto selecionada',
                        text: 'Por favor, selecione ao menos uma foto para publicar.',
                        confirmButtonColor: '#3b82f6'
                    });
                    return;
                }

                const formData = new FormData(this);
                const originalBtnContent = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> PUBLICANDO...';

                axios.post(this.action, formData, {
                    onUploadProgress: (progressEvent) => {
                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> ENVIANDO ${percentCompleted}%`;
                    }
                })
                .then(response => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Suas fotos foram enviadas e estão sendo processadas.',
                        confirmButtonColor: '#3b82f6',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.reload();
                    });
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                    
                    const errorMsg = error.response?.data?.message || 'Ocorreu um erro ao enviar suas fotos. Tente novamente.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Falha no Upload',
                        text: errorMsg,
                        confirmButtonColor: '#ef4444'
                    });
                });
            });

            function openGalleryLightbox(url) {
                const lb = document.getElementById('galleryLightbox');
                const img = document.getElementById('lightboxImg');
                img.src = url;
                lb.classList.remove('hidden');
                setTimeout(() => {
                    img.classList.remove('scale-95', 'opacity-0');
                    img.classList.add('scale-100', 'opacity-100');
                }, 10);
            }

            // Modal Escape Key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeUploadModal();
            });

            // SweetAlert2 Global Configuration
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Deseja excluir?',
                        text: "Esta imagem será removida da galeria definitivamente.",
                        icon: 'warning',
                        iconColor: '#ef4444',
                        showCancelButton: true,
                        confirmButtonColor: '#1e293b',
                        cancelButtonColor: '#ef4444',
                        confirmButtonText: 'SIM, EXCLUIR',
                        cancelButtonText: 'CANCELAR',
                        customClass: {
                            container: 'p-6',
                            popup: 'rounded-[32px]',
                            confirmButton: 'px-6 py-3 rounded-2xl font-black',
                            cancelButton: 'px-6 py-3 rounded-2xl font-black'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });
        </script>

        <style>
            @keyframes modal-in {
                from {
                    opacity: 0;
                    transform: scale(0.9) translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }

            .animate-modal-in {
                animation: modal-in 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
        </style>
    @endpush
@endsection