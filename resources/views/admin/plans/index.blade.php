@extends('admin.layouts.app')

@section('page_title', 'Planos')
@section('breadcrumb')<li class="breadcrumb-item active">Planos</li>@endsection

@section('content')
    <style>
        .plan-card {
            border: 1px solid #e5e7eb;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
            transition: transform .15s ease, box-shadow .15s ease;
            background: #fff;
            height: 100%;
            display: flex;
            flex-direction: column;
            border-radius: 4px;
            overflow: hidden;
        }

        .plan-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 34px rgba(0, 0, 0, 0.08);
        }

        .plan-header {
            padding: 14px 16px;
            background: linear-gradient(135deg, #1F5EDB, #177FD6);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #dfe3e8;
        }

        .plan-body {
            padding: 16px;
            flex: 1;
        }

        .plan-price {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }

        .plan-cycle {
            font-size: 13px;
            color: #6b7280;
        }

        .plan-badge {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .plan-footer {
            padding: 14px 16px;
            background: #f9fafb;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .benefits li {
            margin-bottom: 4px;
        }

        .featured-ribbon {
            position: absolute;
            top: 10px;
            right: -30px;
            background: #10b981;
            color: #fff;
            padding: 6px 36px;
            transform: rotate(45deg);
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .plan-cover {
            width: 100%;
            height: 140px;
            background: #eef2ff;
            object-fit: cover;
        }
    </style>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap mb-3">
                <h3 class="m-0">Planos / Pacotes</h3>
                <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">Novo plano</a>
            </div>

            {{-- Toastr global --}}

            <div class="row row-cols-1 row-cols-md-3 row-cols-xl-3">
                @foreach($plans as $plan)
                    <div class="col mb-4">
                        <div class="plan-card position-relative">
                            @if($plan->is_featured || ($plan->highlight ?? false))
                                <div class="featured-ribbon">Destaque</div>
                            @endif
                            <div class="plan-header">
                                <div class="font-weight-bold">{{ $plan->name }}</div>
                                <span class="badge plan-badge js-plan-status {{ $plan->is_active ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $plan->is_active ? 'Ativo' : 'Oculto' }}
                                </span>
                            </div>
                            <div class="plan-cover d-flex align-items-center justify-content-center text-muted small"
                                style="position:relative;">
                                @if($plan->image)
                                    <img src="{{ asset('storage/' . $plan->image) }}" class="plan-cover" alt="{{ $plan->name }}">
                                @else
                                    <span>Sem imagem</span>
                                @endif
                            </div>
                            <div class="plan-body">
                                <div class="plan-price">
                                    R$ {{ number_format($plan->price, 2, ',', '.') }}
                                    <div class="plan-cycle">{{ $plan->billing_cycle ?? $plan->period ?? 'mensal' }}</div>
                                </div>
                                @if($plan->description)
                                    <p class="mt-2 mb-2 text-muted">{{ $plan->description }}</p>
                                @endif
                                @if(!empty($plan->benefits))
                                    <ul class="benefits pl-3 text-muted small mb-2">
                                        @foreach(is_array($plan->benefits) ? $plan->benefits : json_decode($plan->benefits, true) as $b)
                                            <li>{{ $b }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <div class="plan-footer">
                                <button type="button"
                                    class="btn btn-sm {{ $plan->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} js-toggle-plan-active"
                                    data-url="{{ route('admin.plans.toggle-active', $plan) }}"
                                    data-active="{{ $plan->is_active ? 1 : 0 }}">
                                    <i class="fas {{ $plan->is_active ? 'fa-eye-slash' : 'fa-eye' }} mr-1"></i>
                                    {{ $plan->is_active ? 'Ocultar' : 'Ativar' }}
                                </button>
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-sm btn-secondary">Editar</a>
                                <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="d-inline mb-0">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" data-confirm-delete
                                        data-confirm-title="Remover plano?"
                                        data-confirm-text="O plano será excluído permanentemente.">Excluir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{ $plans->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $(document)
                .off('click.plansActive', '.js-toggle-plan-active')
                .on('click.plansActive', '.js-toggle-plan-active', function (e) {
                    e.preventDefault();

                    const $btn = $(this);
                    const url = $btn.data('url');
                    const isActive = String($btn.data('active')) === '1';
                    const action = isActive ? 'ocultar' : 'ativar';

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
                            url: url,
                            type: 'POST',
                            dataType: 'json',
                            data: { _token: '{{ csrf_token() }}' }
                        }).done(function (resp) {
                            const newActive = !!(resp && resp.is_active);
                            const $card = $btn.closest('.plan-card');
                            const $badge = $card.find('.js-plan-status');

                            $btn.data('active', newActive ? 1 : 0);

                            if (newActive) {
                                $badge.removeClass('badge-secondary').addClass('badge-success').text('Ativo');
                                $btn.removeClass('btn-outline-success').addClass('btn-outline-warning');
                                $btn.html('<i class="fas fa-eye-slash mr-1"></i> Ocultar');
                            } else {
                                $badge.removeClass('badge-success').addClass('badge-secondary').text('Oculto');
                                $btn.removeClass('btn-outline-warning').addClass('btn-outline-success');
                                $btn.html('<i class="fas fa-eye mr-1"></i> Ativar');
                            }

                            toastr.success((resp && resp.message) ? resp.message : ('Plano ' + action + 'ado com sucesso.'));
                        }).fail(function (xhr) {
                            let msg = 'Não foi possível atualizar o plano.';
                            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            toastr.error(msg);
                        }).always(function () {
                            $btn.prop('disabled', false);
                        });
                    });
                });
        });
    </script>
@endpush
