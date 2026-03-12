<div class="row mb-4">
    <div class="col-12">
        <div class="card card-outline card-primary shadow-sm border-0 rounded-xl overflow-hidden">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: 1rem;">
                    <div>
                        <h3 class="card-title text-bold text-dark mb-1">
                            <i class="fas fa-filter mr-2 text-primary"></i> Filtrar Galeria
                        </h3>
                        <p class="text-muted small mb-0">
                            Selecione um evento para gerenciar a capa do album e organizar a galeria com clareza.
                        </p>
                    </div>

                    <button type="button"
                        class="btn btn-success rounded-pill px-4 font-weight-bold shadow-sm active-scale"
                        onclick="$('#uploadModal').modal('show')">
                        <i class="fas fa-plus-circle mr-2"></i> ADICIONAR FOTOS
                    </button>
                </div>
            </div>
            <div class="card-body px-4 pb-4">
                <form action="{{ route('admin.gallery.index') }}" method="GET" class="row align-items-end">
                    <div class="col-md-6">
                        <label class="small font-weight-bold text-muted text-uppercase">Evento</label>
                        <select name="event_id" class="form-control select2">
                            <option value="">Todos os Eventos</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" @selected($selectedEventId === (int) $event->id)>
                                    {{ $event->title }}@if($event->start_at) ({{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex flex-wrap" style="gap: 10px;">
                            <button type="submit" class="btn btn-primary px-4 font-weight-bold rounded-pill">
                                <i class="fas fa-search mr-2"></i> Filtrar
                            </button>

                            @if($selectedEventId > 0)
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
