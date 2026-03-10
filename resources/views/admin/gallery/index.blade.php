@extends('admin.layouts.app')

@section('title', 'Galeria de Fotos')

@section('page_title', 'Galeria de Fotos')

@section('breadcrumb_items')
    <li class="breadcrumb-item active">Galeria</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12 text-right mb-3">
                 <p class="text-muted"><i class="fas fa-info-circle mr-1"></i> Para subir novas fotos, utilize o gerenciamento de mídias dentro da edição de cada evento ou o novo painel do membro.</p>
            </div>
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title text-bold"><i class="fas fa-filter mr-2"></i> Filtrar Galeria</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.gallery.index') }}" method="GET" class="row">
                            <div class="col-md-5">
                                <label>Evento</label>
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
                                <label>&nbsp;</label>
                                <div class="d-flex" style="gap: 10px;">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-search mr-2"></i> Filtrar
                                    </button>
                                    @if(request()->anyFilled(['event_id']))
                                        <a href="{{ route('admin.gallery.index') }}" class="btn btn-default px-4">
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
                    <div class="card h-100 shadow-sm gallery-card border-0">
                        <div class="position-relative overflow-hidden bg-dark rounded-top" style="aspect-ratio: 1/1;">
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
                        <div class="card-body p-3 bg-white rounded-bottom">
                            <p class="text-xs text-muted mb-2 truncate font-weight-bold" title="{{ $item->event->title }}">
                                <i class="fas fa-calendar-alt mr-1 text-primary"></i> {{ Str::limit($item->event->title, 40) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle mr-2 d-flex align-items-center justify-center overflow-hidden" style="width: 24px; height: 24px;">
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
                    <div class="bg-white rounded-lg p-5 shadow-sm border">
                        <i class="fas fa-images fa-5x mb-4 text-light"></i>
                        <h4 class="text-muted">Nenhuma mídia encontrada na galeria</h4>
                        <p class="text-slate-400">Tente ajustar seus filtros ou subir novas fotos.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $media->appends(request()->query())->links() }}
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
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
