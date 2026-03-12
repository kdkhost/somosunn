<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter mr-2"></i>Filtrar galeria
        </h3>

        <div class="card-tools">
            <button type="button" class="btn btn-success btn-sm" onclick="$('#uploadModal').modal('show')">
                <i class="fas fa-plus mr-1"></i>Adicionar fotos
            </button>
        </div>
    </div>

    <form action="{{ route('admin.gallery.index') }}" method="GET">
        <div class="card-body">
            <p class="text-muted mb-3">
                Selecione um evento para gerenciar a capa do album e moderar a galeria dentro do padrao visual do admin.
            </p>

            <div class="form-row align-items-end">
                <div class="form-group col-lg-8 mb-lg-0">
                    <label for="gallery-event-filter">Evento</label>
                    <select id="gallery-event-filter" name="event_id" class="form-control select2" style="width: 100%;">
                        <option value="">Todos os eventos</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" @selected($selectedEventId === (int) $event->id)>
                                {{ $event->title }}@if($event->start_at) - {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-lg-4 mb-0">
                    <div class="d-flex flex-wrap gallery-admin-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-1"></i>Filtrar
                        </button>

                        @if($selectedEventId > 0)
                            <a href="{{ route('admin.gallery.index') }}" class="btn btn-default">
                                <i class="fas fa-undo-alt mr-1"></i>Limpar
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
