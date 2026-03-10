<!-- Modal Quick Upload -->
<div class="modal fade" id="modalQuickUpload" tabindex="-1" role="dialog" aria-labelledby="modalQuickUploadLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg rounded-xl">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title font-weight-bold" id="modalQuickUploadLabel">
                    <i class="fas fa-camera-retro mr-2"></i> Registro Rápido de Fotos
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <!-- Passo 1: Selecionar Evento -->
                <div id="quickUploadStep1">
                    <label class="font-weight-bold mb-2">1. Selecione o Evento</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i
                                    class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="quickUploadSearch" class="form-control border-left-0"
                            placeholder="Digite o nome do evento para buscar...">
                    </div>
                    <div id="quickUploadResults" class="list-group mb-3 overflow-auto"
                        style="max-height: 250px; display: none;">
                        <!-- Resultados via AJAX -->
                    </div>
                    <div id="quickUploadSelected"
                        class="alert alert-info d-none d-flex align-items-center justify-content-between">
                        <div>
                            <i class="fas fa-calendar-check mr-2"></i>
                            <span id="quickUploadSelectedName">Nenhum evento selecionado</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-info"
                            onclick="window.clearQuickUploadSelection()">Trocar</button>
                    </div>
                </div>

                <!-- Passo 2: Upload Files -->
                <div id="quickUploadStep2" class="mt-4 d-none">
                    <label class="font-weight-bold mb-2">2. Enviar Arquivos</label>
                    <div class="premium-upload-box mb-4" id="quickUploadMediaBox">
                        <div class="drop-zone-area p-5 text-center border-2 border-dashed rounded-lg bg-light position-relative"
                            id="quickUploadDropZone">
                            <input type="file" id="quickUploadInput" multiple accept="image/*,video/*"
                                class="position-absolute w-100 h-100 opacity-0" style="top:0; left:0; cursor:pointer;">
                            <div class="drop-zone-content">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <h5 class="font-weight-bold">Arraste fotos e vídeos aqui</h5>
                                <p class="text-muted mb-0">ou clique para selecionar</p>
                            </div>
                        </div>

                        <!-- Progresso -->
                        <div id="quickUploadProgress" class="mt-3 d-none">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small font-weight-bold text-primary"
                                    id="quickUploadStatus">Enviando...</span>
                                <span class="small font-weight-bold" id="quickUploadPercent">0%</span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                    id="quickUploadProgressBar" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light rounded-bottom-xl">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            let selectedEventId = null;
            let searchTimeout = null;

            window.openQuickUploadModal = function () {
                $('#modalQuickUpload').modal('show');
                clearQuickUploadSelection();
            };

            window.clearQuickUploadSelection = function () {
                selectedEventId = null;
                $('#quickUploadStep2').addClass('d-none');
                $('#quickUploadSelected').addClass('d-none');
                $('#quickUploadSearch').val('').parent().show();
                $('#quickUploadResults').hide().empty();
                $('#quickUploadProgress').addClass('d-none');
            };

            $('#quickUploadSearch').on('input', function () {
                const query = $(this).val();
                if (searchTimeout) clearTimeout(searchTimeout);

                if (query.length < 2) {
                    $('#quickUploadResults').hide().empty();
                    return;
                }

                searchTimeout = setTimeout(() => {
                    const searchUrl = '{{ request()->routeIs("panel.*") ? route("panel.admin.events.index") : route("admin.events.index") }}';
                    $.get(searchUrl, { search: query, ajax: 1 }, function (data) {
                        const results = $('#quickUploadResults');
                        results.empty().show();

                        const events = Array.isArray(data) ? data : (data.data || []);

                        if (events.length > 0) {
                            events.forEach(event => {
                                const date = event.start_formated || event.start || '';
                                $('<button type="button" class="list-group-item list-group-item-action py-2">')
                                    .html(`<strong>${event.title}</strong> <small class="text-muted ml-2">${date}</small>`)
                                    .on('click', () => selectEvent(event))
                                    .appendTo(results);
                            });
                        } else {
                            results.append('<div class="list-group-item text-muted">Nenhum evento encontrado</div>');
                        }
                    });
                }, 300);
            });

            function selectEvent(event) {
                selectedEventId = event.id;
                $('#quickUploadSelectedName').text(event.title);
                $('#quickUploadSelected').removeClass('d-none');
                $('#quickUploadSearch').parent().hide();
                $('#quickUploadResults').hide();
                $('#quickUploadStep2').removeClass('d-none');
            }

            async function handleQuickUpload(files) {
                if (!selectedEventId || !files || files.length === 0) return;

                const formData = new FormData();
                for (let i = 0; i < files.length; i++) {
                    formData.append('files[]', files[i]);
                }

                const progressBox = $('#quickUploadProgress');
                const progressBar = $('#quickUploadProgressBar');
                const percentText = $('#quickUploadPercent');
                const statusText = $('#quickUploadStatus');

                progressBox.removeClass('d-none');
                progressBar.css('width', '0%');
                percentText.text('0%');
                statusText.text('Enviando...');

                try {
                    const baseUrl = '{{ request()->routeIs("panel.*") ? url("/painel/admin/events") : url("/admin/events") }}';
                    const url = `${baseUrl}/${selectedEventId}/media`;
                    const response = await axios.post(url, formData, {
                        headers: { 'Content-Type': 'multipart/form-data', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        onUploadProgress: (progressEvent) => {
                            const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                            progressBar.css('width', percent + '%');
                            percentText.text(percent + '%');
                            if (percent >= 100) statusText.text('Processando...');
                        }
                    });

                    if (response.data.success) {
                        Swal.fire({ icon: 'success', title: 'Sucesso!', text: 'Fotos registradas com sucesso.', timer: 1500, showConfirmButton: false });
                        $('#modalQuickUpload').modal('hide');
                        if (window.location.href.includes('/admin/events/' + selectedEventId)) {
                            location.reload();
                        }
                    } else {
                        throw new Error(response.data.message);
                    }
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Erro', text: error.response?.data?.message || 'Falha no upload' });
                    progressBox.addClass('d-none');
                }
            }

            // Setup Drag & Drop
            const dropZone = document.getElementById('quickUploadDropZone');
            const input = document.getElementById('quickUploadInput');

            if (dropZone && input) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(name => {
                    dropZone.addEventListener(name, e => { e.preventDefault(); e.stopPropagation(); }, false);
                });
                dropZone.addEventListener('drop', e => handleQuickUpload(e.dataTransfer.files));
                input.addEventListener('change', function () { handleQuickUpload(this.files); });
            }
        })();
    </script>
@endpush

<style>
    .rounded-xl {
        border-radius: 1rem !important;
    }

    .rounded-bottom-xl {
        border-bottom-left-radius: 1rem !important;
        border-bottom-right-radius: 1rem !important;
    }

    .bg-primary-light {
        background-color: rgba(0, 123, 255, 0.05) !important;
    }
</style>