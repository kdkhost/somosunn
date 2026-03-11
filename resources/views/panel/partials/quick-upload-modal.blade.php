{{-- Modal Quick Upload - Painel (Tailwind + JS Puro) --}}
<div id="panelQuickUploadModal"
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
    style="display:none !important;"
    role="dialog" aria-modal="true" aria-labelledby="panelQuickUploadTitle">

    {{-- Overlay --}}
    <div id="panelQuickUploadOverlay"
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        onclick="window.closePanelQuickUpload()"></div>

    {{-- Dialog --}}
    <div class="relative z-10 w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <i class="fas fa-camera-retro"></i>
                </div>
                <div>
                    <h3 id="panelQuickUploadTitle" class="font-extrabold text-slate-900 dark:text-white text-lg leading-none">Registro Rápido de Fotos</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Selecione um evento e envie as fotos</p>
                </div>
            </div>
            <button type="button" onclick="window.closePanelQuickUpload()"
                class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-5">

            {{-- Step 1: Buscar evento --}}
            <div id="panelQuickStep1">
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                    1. Selecione o evento
                </label>
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none"></i>
                    <input type="text" id="panelQuickSearch"
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-sm outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all"
                        placeholder="Digite o nome do evento para buscar...">
                </div>

                <div id="panelQuickResults"
                    class="mt-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 overflow-hidden shadow-lg"
                    style="display:none; max-height:200px; overflow-y:auto;"></div>

                <div id="panelQuickSelected"
                    class="mt-3 flex items-center justify-between gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl"
                    style="display:none;">
                    <div class="flex items-center gap-2 text-sm text-blue-700 dark:text-blue-300 font-semibold">
                        <i class="fas fa-calendar-check"></i>
                        <span id="panelQuickSelectedName">—</span>
                    </div>
                    <button type="button" onclick="window.clearPanelQuickSelection()"
                        class="text-xs text-blue-500 hover:text-blue-700 font-bold underline underline-offset-2">
                        Trocar
                    </button>
                </div>
            </div>

            {{-- Step 2: Upload --}}
            <div id="panelQuickStep2" style="display:none;">
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                    2. Enviar arquivos
                </label>

                <input type="file" id="panelQuickFileInput" multiple accept="image/*,video/*" style="display:none;">

                <div id="panelQuickDropZone"
                    onclick="document.getElementById('panelQuickFileInput').click()"
                    class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 p-8 text-center cursor-pointer transition-all hover:border-blue-400 hover:bg-blue-50/50 dark:hover:border-blue-600 dark:hover:bg-blue-900/10">
                    <i class="fas fa-cloud-upload-alt text-3xl text-blue-500 mb-3 block"></i>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300">Arraste fotos/vídeos ou <span class="text-blue-600 underline underline-offset-2">clique para selecionar</span></p>
                    <p class="text-xs text-slate-400 mt-1">Imagens (JPG, PNG) · Vídeos (MP4)</p>
                </div>

                {{-- Progresso --}}
                <div id="panelQuickProgress" class="mt-4" style="display:none;">
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

        {{-- Footer --}}
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

    // ---- Abrir / Fechar ----
    window.openQuickUploadModal = function () {
        modal.style.removeProperty('display');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        window.clearPanelQuickSelection();
        document.getElementById('panelQuickSearch').focus();
    };

    window.closePanelQuickUpload = function () {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    // ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') window.closePanelQuickUpload();
    });

    // ---- Limpar seleção ----
    window.clearPanelQuickSelection = function () {
        selectedEventId = null;
        document.getElementById('panelQuickSearch').value = '';
        document.getElementById('panelQuickSearch').closest('div.relative').style.display = '';
        document.getElementById('panelQuickResults').style.display = 'none';
        document.getElementById('panelQuickResults').innerHTML = '';
        document.getElementById('panelQuickSelected').style.display = 'none';
        document.getElementById('panelQuickStep2').style.display = 'none';
        document.getElementById('panelQuickProgress').style.display = 'none';
        document.getElementById('panelQuickProgressBar').style.width = '0%';
    };

    // ---- Busca de eventos ----
    document.getElementById('panelQuickSearch').addEventListener('input', function () {
        const query = this.value.trim();
        if (searchTimeout) clearTimeout(searchTimeout);

        if (query.length < 2) {
            document.getElementById('panelQuickResults').style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(function () {
            const searchUrl = '{{ route("panel.admin.events.index") }}';
            fetch(searchUrl + '?search=' + encodeURIComponent(query) + '&ajax=1', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    const results = document.getElementById('panelQuickResults');
                    results.innerHTML = '';
                    const events = Array.isArray(data) ? data : (data.data || []);

                    if (events.length > 0) {
                        events.forEach(ev => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'w-full text-left px-4 py-3 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-sm text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 last:border-0 transition-colors';
                            btn.innerHTML = `<span class="font-bold">${ev.title}</span> <span class="text-slate-400 text-xs ml-2">${ev.start_formated || ev.start || ''}</span>`;
                            btn.addEventListener('click', function () { selectPanelEvent(ev); });
                            results.appendChild(btn);
                        });
                        results.style.display = 'block';
                    } else {
                        results.innerHTML = '<p class="px-4 py-3 text-sm text-slate-400">Nenhum evento encontrado</p>';
                        results.style.display = 'block';
                    }
                })
                .catch(() => {});
        }, 300);
    });

    function selectPanelEvent(ev) {
        selectedEventId = ev.id;
        document.getElementById('panelQuickSelectedName').textContent = ev.title;
        document.getElementById('panelQuickSelected').style.display = 'flex';
        document.getElementById('panelQuickSearch').closest('div.relative').style.display = 'none';
        document.getElementById('panelQuickResults').style.display = 'none';
        document.getElementById('panelQuickStep2').style.display = 'block';
    }

    // ---- Upload ----
    const fileInput = document.getElementById('panelQuickFileInput');
    const dropZone = document.getElementById('panelQuickDropZone');

    fileInput.addEventListener('change', function () {
        if (this.files.length) handlePanelUpload(this.files);
    });

    ['dragover', 'dragenter'].forEach(ev => {
        dropZone.addEventListener(ev, function (e) {
            e.preventDefault();
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });
    });

    ['dragleave', 'drop'].forEach(ev => {
        dropZone.addEventListener(ev, function (e) {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });
    });

    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        if (e.dataTransfer.files.length) handlePanelUpload(e.dataTransfer.files);
    });

    async function handlePanelUpload(files) {
        if (!selectedEventId || !files.length) return;

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) formData.append('files[]', files[i]);

        const progressEl = document.getElementById('panelQuickProgress');
        const bar = document.getElementById('panelQuickProgressBar');
        const pct = document.getElementById('panelQuickPercent');
        const status = document.getElementById('panelQuickStatus');

        progressEl.style.display = 'block';
        bar.style.width = '0%';
        pct.textContent = '0%';
        status.textContent = 'Enviando...';

        try {
            const url = `{{ url('/painel/admin/events') }}/${selectedEventId}/media`;

            const response = await axios.post(url, formData, {
                headers: { 'Content-Type': 'multipart/form-data', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                onUploadProgress: function (e) {
                    const p = Math.round((e.loaded * 100) / e.total);
                    bar.style.width = p + '%';
                    pct.textContent = p + '%';
                    if (p >= 100) status.textContent = 'Processando...';
                }
            });

            if (response.data.success) {
                Swal.fire({ icon: 'success', title: 'Fotos registradas!', text: 'As imagens foram enviadas com sucesso.', timer: 2000, showConfirmButton: false });
                window.closePanelQuickUpload();
            } else {
                throw new Error(response.data.message || 'Erro no upload');
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Erro no upload', text: err.response?.data?.message || err.message || 'Falha ao enviar arquivos.' });
            progressEl.style.display = 'none';
        }
    }
})();
</script>
@endpush
