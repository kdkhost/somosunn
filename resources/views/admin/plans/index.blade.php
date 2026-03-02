@extends('admin.layouts.app')

@section('page_title', 'Planos')
@section('breadcrumb')<li class="breadcrumb-item active">Planos</li>@endsection

@section('content')
<style>
    .plan-kanban-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-start;
        min-height: 180px;
    }

    .plan-card {
        border: 1px solid #e5e7eb;
        box-shadow: 0 6px 18px rgba(0,0,0,.06);
        transition: transform .15s ease, box-shadow .15s ease;
        background: #fff;
        display: flex;
        flex-direction: column;
        border-radius: 6px;
        overflow: hidden;
        width: 230px;
        flex-shrink: 0;
    }

    .plan-card.sortable-ghost { opacity: .35; transform: scale(.97); }
    .plan-card.is-highlighted {
        border-color: #1F5EDB;
        box-shadow: 0 0 0 2px #1F5EDB55, 0 10px 28px rgba(31,94,219,.15);
    }

    .plan-drag-handle {
        cursor: grab;
        color: rgba(255,255,255,.7);
        padding: 0 6px;
        font-size: 13px;
        line-height: 1;
        flex-shrink: 0;
    }
    .plan-drag-handle:hover { color: #fff; }
    .plan-drag-handle:active { cursor: grabbing; }

    .plan-header {
        padding: 10px 12px;
        background: linear-gradient(135deg, #1F5EDB, #177FD6);
        color: #fff;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .plan-body { padding: 12px; flex: 1; }
    .plan-price { font-size: 18px; font-weight: 700; color: #0f172a; }
    .plan-cycle { font-size: 11px; color: #6b7280; margin-top: 2px; }

    .plan-badge { font-size: 10px; padding: 2px 8px; border-radius: 999px; white-space: nowrap; }

    .plan-footer {
        padding: 10px 12px;
        background: #f9fafb;
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        justify-content: flex-end;
        border-top: 1px solid #f3f4f6;
    }

    .period-pills { display: flex; flex-wrap: wrap; gap: 3px; margin-top: 6px; }
    .period-pill {
        font-size: 10px;
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        padding: 2px 6px;
        font-weight: 600;
    }

    .kanban-saving { display: none; font-size: 13px; color: #2563eb; align-items: center; gap: 6px; }
    .kanban-saving.visible { display: inline-flex; }

    .highlight-note {
        font-size: 12px;
        color: #374151;
        background: #eff6ff;
        border: 1px dashed #93c5fd;
        border-radius: 6px;
        padding: 8px 12px;
    }
</style>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between flex-wrap align-items-center mb-2 gap-2">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h3 class="m-0 mr-3">Planos / Pacotes</h3>
                <span id="kanban-saving" class="kanban-saving">
                    <i class="fas fa-circle-notch fa-spin"></i> Salvando ordem...
                </span>
            </div>
            <a href="{{ route('admin.plans.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Novo plano
            </a>
        </div>

        <div class="highlight-note mb-3">
            <i class="fas fa-arrows-alt text-primary mr-1"></i>
            <strong>Arraste</strong> para reordenar os cards. O plano com <i class="fas fa-star" style="color:#f59e0b"></i> <strong>Destaque</strong> sempre ficará <strong>centralizado</strong> na página pública independente da posição aqui.
        </div>

        <div class="plan-kanban-wrap" id="plans-kanban">
            @foreach($plans as $plan)
            @php
                $periods = $plan->getAvailablePeriods();
                $multiPeriod = count(array_filter($periods, fn($v) => $v > 0)) > 1;
            @endphp
            <div class="plan-card {{ ($plan->is_featured || $plan->highlight) ? 'is-highlighted' : '' }}"
                 data-id="{{ $plan->id }}">

                <div class="plan-header">
                    <span class="plan-drag-handle" title="Arrastar"><i class="fas fa-grip-vertical"></i></span>
                    <div class="font-weight-bold flex-grow-1 text-truncate small" title="{{ $plan->name }}">
                        {{ $plan->name }}
                        @if($plan->highlight || $plan->is_featured)
                            <i class="fas fa-star ml-1" title="Destaque" style="color:#fbbf24;font-size:10px"></i>
                        @endif
                    </div>
                    <span class="badge plan-badge js-plan-status {{ $plan->is_active ? 'badge-success' : 'badge-secondary' }}">
                        {{ $plan->is_active ? 'Ativo' : 'Oculto' }}
                    </span>
                </div>

                @if($plan->image)
                    <img src="{{ asset('storage/'.$plan->image) }}" alt="{{ $plan->name }}"
                         style="width:100%;height:90px;object-fit:cover;">
                @endif

                <div class="plan-body">
                    <div class="plan-price">R$ {{ number_format($plan->price, 2, ',', '.') }}</div>
                    <div class="plan-cycle">{{ ucfirst($plan->period ?? 'mensal') }}</div>

                    @if($multiPeriod)
                        <div class="period-pills">
                            @foreach($periods as $pk => $pv)
                                @if($pv > 0)
                                <span class="period-pill">{{ ucfirst($pk) }} R${{ number_format($pv,0,',','.') }}</span>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if($plan->description)
                        <p class="mt-2 mb-0 text-muted" style="font-size:11px;line-height:1.4">
                            {{ Str::limit($plan->description, 65) }}
                        </p>
                    @endif
                </div>

                <div class="plan-footer">
                    <button type="button"
                        class="btn btn-sm {{ $plan->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} js-toggle-plan-active"
                        data-url="{{ route('admin.plans.toggle-active', $plan) }}"
                        data-active="{{ $plan->is_active ? 1 : 0 }}"
                        title="{{ $plan->is_active ? 'Ocultar' : 'Ativar' }}">
                        <i class="fas {{ $plan->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                    </button>
                    <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-sm btn-secondary" title="Editar">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                    <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="d-inline mb-0">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" title="Excluir"
                            data-confirm-delete
                            data-confirm-title="Remover plano?"
                            data-confirm-text="O plano será excluído permanentemente.">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-3">{{ $plans->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
$(function () {
    /* ── Kanban drag-and-drop ──────────────────── */
    var saveTimer = null;
    var $saving = $('#kanban-saving');

    Sortable.create(document.getElementById('plans-kanban'), {
        handle: '.plan-drag-handle',
        animation: 180,
        ghostClass: 'sortable-ghost',
        onEnd: function () {
            clearTimeout(saveTimer);
            $saving.addClass('visible');
            saveTimer = setTimeout(function () {
                var items = [];
                $('#plans-kanban .plan-card').each(function (idx) {
                    items.push({ id: parseInt($(this).data('id')), sort_order: idx });
                });
                $.ajax({
                    url: '{{ route("admin.plans.reorder") }}',
                    method: 'POST',
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}', items: items }
                }).done(function () {
                    $saving.removeClass('visible');
                    toastr.success('Ordem salva com sucesso.');
                }).fail(function () {
                    $saving.removeClass('visible');
                    toastr.error('Não foi possível salvar a ordem.');
                });
            }, 600);
        }
    });

    /* ── Toggle ativo/oculto ──────────────────── */
    $(document)
        .off('click.plansActive', '.js-toggle-plan-active')
        .on('click.plansActive', '.js-toggle-plan-active', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const url  = $btn.data('url');
            const isActive = String($btn.data('active')) === '1';

            Swal.fire({
                title: isActive ? 'Ocultar plano?' : 'Ativar plano?',
                text: isActive
                    ? 'Este plano ficará oculto no site para novos usuários.'
                    : 'Este plano voltará a ser exibido no site.',
                icon: isActive ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: isActive ? 'Ocultar' : 'Ativar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: isActive ? '#d97706' : '#16a34a'
            }).then((result) => {
                if (!result.isConfirmed) return;
                $btn.prop('disabled', true);
                $.ajax({
                    url: url, type: 'POST', dataType: 'json',
                    data: { _token: '{{ csrf_token() }}' }
                }).done(function (resp) {
                    const newActive = !!(resp && resp.is_active);
                    const $card  = $btn.closest('.plan-card');
                    const $badge = $card.find('.js-plan-status');
                    $btn.data('active', newActive ? 1 : 0);
                    if (newActive) {
                        $badge.removeClass('badge-secondary').addClass('badge-success').text('Ativo');
                        $btn.removeClass('btn-outline-success').addClass('btn-outline-warning')
                            .html('<i class="fas fa-eye-slash"></i>').attr('title','Ocultar');
                    } else {
                        $badge.removeClass('badge-success').addClass('badge-secondary').text('Oculto');
                        $btn.removeClass('btn-outline-warning').addClass('btn-outline-success')
                            .html('<i class="fas fa-eye"></i>').attr('title','Ativar');
                    }
                    toastr.success((resp && resp.message) || 'Plano atualizado.');
                }).fail(function (xhr) {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Erro ao atualizar plano.');
                }).always(function () {
                    $btn.prop('disabled', false);
                });
            });
        });
});
</script>
@endpush
