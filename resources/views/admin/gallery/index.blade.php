@extends('admin.layouts.app')

@section('title', 'Galeria de Fotos')

@section('page_title', 'Galeria de Fotos')

@section('breadcrumb_items')
    <li class="breadcrumb-item active">Galeria</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm border-0 rounded-xl overflow-hidden">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <h3 class="card-title text-bold text-dark"><i class="fas fa-filter mr-2 text-primary"></i> Filtrar Galeria</h3>
                            <button type="button" class="btn btn-success rounded-pill px-4 font-weight-bold shadow-sm active-scale" onclick="$('#uploadModal').modal('show')">
                                <i class="fas fa-plus-circle mr-2"></i> ADICIONAR FOTOS
                            </button>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <form action="{{ route('admin.gallery.index') }}" method="GET" class="row align-items-end">
                            <div class="col-md-5">
                                <label class="small font-weight-bold text-muted uppercase tracking-wider">Evento</label>
                                <select name="event_id" class="form-control select2">
                                    <option value="">Todos os Eventos</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                            {{ $event->title }} ({{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex" style="gap: 10px;">
                                    <button type="submit" class="btn btn-primary px-4 font-weight-bold rounded-pill">
                                        <i class="fas fa-search mr-2"></i> Filtrar
                                    </button>
                                    @if(request()->anyFilled(['event_id']))
                                        <a href="{{ route('admin.gallery.index') }}" class="btn btn-default px-4 rounded-pill font-weight-bold">
                                            Limpar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="gallery-container">
            @forelse($media as $item)
                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 shadow-sm gallery-card border-0 rounded-xl overflow-hidden">
                        <div class="position-relative overflow-hidden bg-dark" style="aspect-ratio: 1/1;">
                            <img src="{{ asset('storage/' . $item->file_path) }}" 
                                 class="card-img-top w-100 h-100 object-fit-cover cursor-pointer transition-all"
                                 onclick="window.open('{{ asset('storage/' . $item->file_path) }}', '_blank')"
                                 alt="Galeria">
                            
                            <div class="overlay-actions flex items-center justify-center">
                                <form action="{{ route('admin.gallery.destroy', $item) }}" method="POST"
                                      class="delete-form"
                                      data-confirm-title="Remover da galeria?"
                                      data-confirm-text="Esta imagem será excluída permanentemente.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm shadow-lg rounded-circle" style="width: 35px; height: 35px;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>

                            @if($item->watermarked)
                                <div class="position-absolute" style="bottom: 10px; left: 10px;">
                                    <span class="badge badge-primary opacity-75 shadow-sm">
                                        <i class="fas fa-certificate mr-1"></i> Original
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-3 bg-white">
                            <p class="text-xs text-muted mb-2 truncate font-weight-bold" title="{{ $item->event->title }}">
                                <i class="fas fa-calendar-alt mr-1 text-primary"></i> {{ Str::limit($item->event->title, 40) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle mr-2 d-flex align-items-center justify-content-center overflow-hidden" style="width: 24px; height: 24px;">
                                        <i class="fas fa-user text-muted text-xs"></i>
                                    </div>
                                    <span class="text-xs text-dark font-weight-bold truncate" style="max-width: 100px;">
                                        {{ $item->user->name ?? 'Sistema' }}
                                    </span>
                                </div>
                                <small class="text-muted text-xs">{{ $item->created_at->format('d/m/Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="bg-white rounded-xl p-5 shadow-sm border border-light">
                        <i class="fas fa-images fa-5x mb-4 text-light opacity-50"></i>
                        <h4 class="text-muted font-weight-bold">Nenhuma mídia encontrada na galeria</h4>
                        <p class="text-secondary small">Tente ajustar seus filtros ou subir novas fotos.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $media->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Modal de Upload Premium (Admin) -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 rounded-xl overflow-hidden shadow-2xl">
                <div class="modal-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex flex-column">
                        <h4 class="modal-title font-weight-bold text-dark">Novas Fotos</h4>
                        <p class="text-muted small mb-0">Selecione o evento e carregue suas mídias.</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 2rem; padding: 1.5rem;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('admin.gallery.upload') }}" method="POST" enctype="multipart/form-data" id="adminUploadForm">
                        @csrf
                        <div class="form-group mb-4">
                            <label class="small font-weight-bold text-muted uppercase tracking-wider mb-2">Evento Associado</label>
                            <select name="event_id" required class="form-control select2-modal" style="width: 100%;">
                                <option value="">Selecione o evento...</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}">{{ $event->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="premium-upload-box mb-4" id="adminDropzone">
                            <div class="drop-zone-area">
                                <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                <h5 class="font-weight-bold text-dark mb-1">Clique ou Arraste</h5>
                                <p class="text-muted x-small uppercase tracking-widest mb-0">JPG, PNG, WEBP (MÁX 10MB)</p>
                            </div>
                            <input type="file" name="files[]" multiple required accept="image/*" id="adminFileInput" class="d-none">
                            <div id="adminFileCount" class="badge badge-primary px-3 py-2 rounded-pill d-none mt-2"></div>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <button type="submit" id="adminSubmitBtn" class="btn btn-primary btn-lg btn-block font-weight-bold rounded-pill shadow-lg py-3">
                                <i class="fas fa-rocket mr-2"></i> PUBLICAR NA GALERIA
                            </button>
                            <p class="text-center x-small text-muted font-weight-bold uppercase tracking-tighter mt-3">
                                <i class="fas fa-magic text-primary mr-1"></i> Marca d'água automática garantida
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .gallery-card { transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
    .gallery-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .object-fit-cover { object-fit: cover; }
    .cursor-pointer { cursor: pointer; }
    .overlay-actions {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.3);
        opacity: 0;
        transition: opacity 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .gallery-card:hover .overlay-actions { opacity: 1; }
    .gallery-card:hover img { transform: scale(1.05); }
    .truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar Select2 no modal
        $('.select2-modal').select2({
            dropdownParent: $('#uploadModal')
        });

        // Lógica de Upload (Admin)
        const $form = $('#adminUploadForm');
        const $fileInput = $('#adminFileInput');
        const $dropzone = $('#adminDropzone');
        const $fileCount = $('#adminFileCount');
        const $submitBtn = $('#adminSubmitBtn');

        $dropzone.on('click', () => $fileInput.click());

        $fileInput.on('change', function() {
            const count = this.files.length;
            if (count > 0) {
                $fileCount.text(`${count} ${count > 1 ? 'FOTOS SELECIONADAS' : 'FOTO SELECIONADA'}`).removeClass('d-none');
            } else {
                $fileCount.addClass('d-none');
            }
        });

        // Drag and Drop
        $dropzone.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        }).on('dragleave drop', function() {
            $(this).removeClass('dragover');
        }).on('drop', function(e) {
            e.preventDefault();
            const files = e.originalEvent.dataTransfer.files;
            $fileInput[0].files = files;
            $fileInput.trigger('change');
        });

        $form.on('submit', function(e) {
            e.preventDefault();
            
            if ($fileInput[0].files.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Selecione pelo menos uma foto.' });
                return;
            }

            const formData = new FormData(this);
            const originalText = $submitBtn.html();
            
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> ENVIANDO...');

            axios.post(this.action, formData, {
                onUploadProgress: (p) => {
                    const pct = Math.round((p.loaded * 100) / p.total);
                    $submitBtn.html(`<i class="fas fa-spinner fa-spin mr-2"></i> ENVIANDO ${pct}%`);
                }
            })
            .then(res => {
                Swal.fire({
                    icon: 'success',
                    title: 'Excelente!',
                    text: 'As fotos foram enviadas com sucesso.',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => location.reload());
            })
            .catch(err => {
                $submitBtn.prop('disabled', false).html(originalText);
                const msg = err.response?.data?.message || 'Erro ao realizar upload.';
                Swal.fire({ icon: 'error', title: 'Erro', text: msg });
            });
        });

        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            const form = this;
            const title = $(this).data('confirm-title') || 'Tem certeza?';
            const text = $(this).data('confirm-text') || 'Esta ação não poderá ser desfeita.';

            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1e293b',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar',
                customClass: { popup: 'rounded-[32px]' }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
