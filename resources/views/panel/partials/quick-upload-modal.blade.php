{{-- Modal Quick Upload - Painel (Tailwind + JS Puro) --}}
<div id="panelQuickUploadModal"
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
    hidden
    role="dialog" aria-modal="true" aria-labelledby="panelQuickUploadTitle">

    <div id="panelQuickUploadOverlay"
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        onclick="window.closePanelQuickUpload()"></div>

    <div class="relative z-10 w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i class="fas fa-camera-retro"></i>
                </div>
                <div>
                    <h3 id="panelQuickUploadTitle" class="font-extrabold text-slate-900 dark:text-white text-lg leading-none">Registro Rapido de Fotos</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Selecione um evento e envie as fotos</p>
                </div>
            </div>
            <button type="button" onclick="window.closePanelQuickUpload()"
                class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <div class="p-6 space-y-5">
            <div id="panelQuickStep1">
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                    1. Selecione o evento
                </label>

                <div class="relative" id="panelQuickSearchWrap">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
                    <input type="text" id="panelQuickSearch"
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-sm outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                        placeholder="Digite o nome do evento para buscar...">
                </div>

                <div id="panelQuickResults"
                    class="mt-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 overflow-hidden shadow-lg"
                    hidden
                    style="max-height:200px; overflow-y:auto;"></div>

                <div id="panelQuickSelected"
                    class="mt-3 flex items-center justify-between gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl"
                    hidden>
                    <div class="flex items-center gap-2 text-sm text-blue-700 dark:text-blue-300 font-semibold">
                        <i class="fas fa-calendar-check"></i>
                        <span id="panelQuickSelectedName">-</span>
                    </div>
                    <button type="button" onclick="window.clearPanelQuickSelection()"
                        class="text-xs text-blue-500 hover:text-blue-700 font-bold underline underline-offset-2">
                        Trocar
                    </button>
                </div>
            </div>

            <div id="panelQuickStep2" hidden>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                    2. Enviar arquivos
                </label>

                <input type="file" id="panelQuickFileInput" multiple accept="image/*,video/*" hidden>

                <div id="panelQuickDropZone"
                    onclick="document.getElementById('panelQuickFileInput').click()"
                    class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 p-8 text-center cursor-pointer transition-all hover:border-blue-400 hover:bg-blue-50/50 dark:hover:border-blue-600 dark:hover:bg-blue-900/10">
                    <i class="fas fa-cloud-upload-alt text-3xl text-blue-500 mb-3 block"></i>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Arraste fotos e videos ou <span class="text-blue-600 underline underline-offset-2">clique para selecionar</span></p>
                    <p class="text-xs text-slate-400 mt-1">Imagens (JPG, PNG) e videos (MP4)</p>
                </div>

                <div id="panelQuickProgress" class="mt-4" hidden>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400" id="panelQuickStatus">Enviando...</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200" id="panelQuickPercent">0%</span>
                    </div>
                    <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div id="panelQuickProgressBar" class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-300" style="width:0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 pb-6">
            <button type="button" onclick="window.closePanelQuickUpload()"
                class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                Fechar
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    let selectedEventId = null;
    let searchTimeout = null;

    const modal = document.getElementById('panelQuickUploadModal');
    const searchWrap = document.getElementById('panelQuickSearchWrap');
    const searchInput = document.getElementById('panelQuickSearch');
    const results = document.getElementById('panelQuickResults');
    const selected = document.getElementById('panelQuickSelected');
    const selectedName = document.getElementById('panelQuickSelectedName');
    const step2 = document.getElementById('panelQuickStep2');
    const fileInput = document.getElementById('panelQuickFileInput');
    const dropZone = document.getElementById('panelQuickDropZone');
    const progress = document.getElementById('panelQuickProgress');
    const progressBar = document.getElementById('panelQuickProgressBar');
    const progressPercent = document.getElementById('panelQuickPercent');
    const progressStatus = document.getElementById('panelQuickStatus');

    if (!modal || !searchWrap || !searchInput || !results || !selected || !selectedName || !step2 || !fileInput || !dropZone || !progress || !progressBar || !progressPercent || !progressStatus) {
        return;
    }

    function show(element) {
        element.hidden = false;
    }

    function hide(element) {
        element.hidden = true;
    }

    function resetQuickUploadState() {
        selectedEventId = null;
        searchInput.value = '';
        searchInput.disabled = false;
        fileInput.value = '';
        searchWrap.hidden = false;
        hide(results);
        results.innerHTML = '';
        hide(selected);
        hide(step2);
        hide(progress);
        progressBar.style.width = '0%';
        progressPercent.textContent = '0%';
        progressStatus.textContent = 'Enviando...';
    }

    window.openQuickUploadModal = function () {
        resetQuickUploadState();
        show(modal);
        document.body.style.overflow = 'hidden';
        searchInput.focus();
    };

    window.closePanelQuickUpload = function () {
        hide(modal);
        document.body.style.overflow = '';
    };

    window.clearPanelQuickSelection = function () {
        resetQuickUploadState();
        searchInput.focus();
    };

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            window.closePanelQuickUpload();
        }
    });

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();

        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        if (query.length < 2) {
            hide(results);
            results.innerHTML = '';
            return;
        }

        searchTimeout = setTimeout(function () {
            const searchUrl = '{{ route("panel.admin.events.index") }}';

            fetch(searchUrl + '?search=' + encodeURIComponent(query) + '&ajax=1', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    const events = Array.isArray(data) ? data : (data.data || []);
                    results.innerHTML = '';

                    if (events.length === 0) {
                        results.innerHTML = '<p class="px-4 py-3 text-sm text-slate-400">Nenhum evento encontrado</p>';
                        show(results);
                        return;
                    }

                    events.forEach(function (eventItem) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'w-full text-left px-4 py-3 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-sm text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 last:border-0 transition-colors';
                        button.innerHTML = '<span class="font-bold">' + eventItem.title + '</span> <span class="text-slate-400 text-xs ml-2">' + (eventItem.start_formated || eventItem.start || '') + '</span>';
                        button.addEventListener('click', function () {
                            selectedEventId = eventItem.id;
                            selectedName.textContent = eventItem.title;
                            hide(searchWrap);
                            hide(results);
                            show(selected);
                            show(step2);
                        });
                        results.appendChild(button);
                    });

                    show(results);
                })
                .catch(function () {
                    hide(results);
                });
        }, 300);
    });

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            handlePanelUpload(this.files);
        }
    });

    ['dragover', 'dragenter'].forEach(function (eventName) {
        dropZone.addEventListener(eventName, function (event) {
            event.preventDefault();
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });
    });

    ['dragleave', 'drop'].forEach(function (eventName) {
        dropZone.addEventListener(eventName, function (event) {
            event.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });
    });

    dropZone.addEventListener('drop', function (event) {
        event.preventDefault();
        if (event.dataTransfer.files.length > 0) {
            handlePanelUpload(event.dataTransfer.files);
        }
    });

    async function handlePanelUpload(files) {
        if (!selectedEventId || !files.length) {
            return;
        }

        const formData = new FormData();

        for (let index = 0; index < files.length; index++) {
            formData.append('files[]', files[index]);
        }

        show(progress);
        progressBar.style.width = '0%';
        progressPercent.textContent = '0%';
        progressStatus.textContent = 'Enviando...';

        try {
            const url = `{{ url('/painel/admin/events') }}/${selectedEventId}/media`;
            const response = await axios.post(url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                onUploadProgress: function (event) {
                    const total = Number(event.total || 0);
                    const loaded = Number(event.loaded || 0);
                    const percent = total > 0 ? Math.round((loaded * 100) / total) : 0;

                    progressBar.style.width = percent + '%';
                    progressPercent.textContent = percent + '%';

                    if (percent >= 100) {
                        progressStatus.textContent = 'Processando...';
                    }
                }
            });

            if (!response.data.success) {
                throw new Error(response.data.message || 'Erro no upload');
            }

            Swal.fire({
                icon: 'success',
                title: 'Fotos registradas!',
                text: 'As imagens foram enviadas com sucesso.',
                timer: 2000,
                showConfirmButton: false
            });

            window.closePanelQuickUpload();
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro no upload',
                text: error.response?.data?.message || error.message || 'Falha ao enviar arquivos.'
            });
            hide(progress);
        }
    }
})();
</script>
@endpush
