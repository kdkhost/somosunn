<div class="gallery-admin-surface mb-4">
    <div class="p-4 p-lg-5">
        <div class="d-flex flex-column flex-xl-row align-items-xl-end justify-content-between mb-4" style="gap: 1.25rem;">
            <div class="pr-xl-4">
                <p class="gallery-admin-section-eyebrow">Filtro inteligente</p>
                <h2 class="gallery-admin-section-title mt-2">Organize a leitura da galeria sem perder contexto</h2>
                <p class="gallery-admin-subtext mb-0 mt-3">
                    Selecione um evento para concentrar a moderacao em um album especifico. A capa, o grid e o contador da pagina acompanham esse filtro automaticamente.
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center" style="gap: .75rem;">
                <span class="badge badge-pill badge-light px-3 py-2 border text-uppercase font-weight-bold" style="letter-spacing:.12em;">
                    <i class="fas fa-calendar-check text-primary mr-2"></i>{{ $events->count() }} albuns
                </span>
                <button type="button"
                    class="gallery-admin-primary-btn border-0"
                    onclick="$('#uploadModal').modal('show')">
                    <i class="fas fa-cloud-upload-alt"></i>
                    Adicionar fotos
                </button>
            </div>
        </div>

        <form action="{{ route('admin.gallery.index') }}" method="GET" class="row align-items-end" style="row-gap: 1rem;">
            <div class="col-lg-8">
                <label class="small font-weight-bold text-muted text-uppercase mb-2 d-block" style="letter-spacing:.14em;">Evento</label>
                <select name="event_id" class="form-control select2" style="min-height: 54px; border-radius: 1.2rem;">
                    <option value="">Todos os eventos</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected($selectedEventId === (int) $event->id)>
                            {{ $event->title }}@if($event->start_at) • {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }}@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-4">
                <div class="d-flex flex-wrap align-items-center" style="gap: .75rem;">
                    <button type="submit" class="gallery-admin-primary-btn border-0">
                        <i class="fas fa-filter"></i>
                        Filtrar
                    </button>

                    @if($selectedEventId > 0)
                        <a href="{{ route('admin.gallery.index') }}" class="gallery-admin-secondary-btn text-decoration-none">
                            <i class="fas fa-undo-alt"></i>
                            Limpar
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
