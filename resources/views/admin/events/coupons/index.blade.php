@extends('admin.layouts.app')

@section('page_title', 'Cupons do Evento')

@section('content')
@php
    $adminUser = auth()->user();
    $canCreateCoupon = $adminUser && $adminUser->hasPermission('admin.events.coupons.create');
    $canEditCoupon = $adminUser && $adminUser->hasPermission('admin.events.coupons.edit');
    $canDeleteCoupon = $adminUser && $adminUser->hasPermission('admin.events.coupons.delete');
    $canToggleCoupon = $adminUser && $adminUser->hasPermission('admin.events.coupons.toggle');
    $eventDeadline = $event->publicDeadlineAt();
@endphp
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <h3 class="card-title font-weight-bold mb-0">
                    <i class="fas fa-ticket-alt mr-2"></i>Cupons de gratuidade
                </h3>
                <small class="text-muted d-block">{{ $event->title }}</small>
            </div>
            <div class="ml-auto">
                <a href="{{ route($eventsRoutePrefix . '.edit', ['event' => $event, 'tab' => 'coupons']) }}" class="btn btn-outline-secondary btn-sm rounded-pill" data-pjax>
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
                @if($canCreateCoupon)
                    <a href="{{ route($routePrefix . '.create', $event) }}" class="btn btn-primary btn-sm rounded-pill" data-pjax>
                        <i class="fas fa-plus mr-1"></i> Novo cupom
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Codigo</th>
                            <th>Tipo</th>
                            <th>Uso</th>
                            <th>Usos</th>
                            <th>Validade</th>
                            <th>Status</th>
                            <th class="text-right">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coupon)
                            @php
                                $effectiveExpiresAt = $coupon->expires_at;
                                if ($eventDeadline && (!$effectiveExpiresAt || $eventDeadline->lt($effectiveExpiresAt))) {
                                    $effectiveExpiresAt = $eventDeadline;
                                }
                            @endphp
                            <tr>
                                <td><span class="badge badge-dark px-3 py-2">{{ $coupon->code }}</span></td>
                                <td>
                                    @if($coupon->type === \App\Models\EventCoupon::TYPE_FREE)
                                        <span class="badge badge-success">Gratuidade total</span>
                                    @elseif($coupon->type === \App\Models\EventCoupon::TYPE_PERCENT)
                                        <span class="badge badge-info">{{ number_format((float) $coupon->discount_value, 2, ',', '.') }}%</span>
                                    @else
                                        <span class="badge badge-warning">R$ {{ number_format((float) $coupon->discount_value, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ $coupon->appliesToLabel() }}</span>
                                </td>
                                <td>
                                    <strong>{{ (int) $coupon->used_count }}</strong>
                                    <span class="text-muted">/ {{ $coupon->max_uses ? (int) $coupon->max_uses : 'ilimitado' }}</span>
                                </td>
                                <td class="small text-muted">
                                    @if($coupon->starts_at)<div>Inicio: {{ $coupon->starts_at->format('d/m/Y H:i') }}</div>@endif
                                    @if($effectiveExpiresAt)<div>Fim efetivo: {{ $effectiveExpiresAt->format('d/m/Y H:i') }}</div>@endif
                                    @if(!$coupon->starts_at && !$effectiveExpiresAt)<span>Sem periodo definido</span>@endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $coupon->active ? 'success' : 'secondary' }}">
                                        {{ $coupon->active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="btn-group btn-group-sm">
                                        @if($canToggleCoupon)
                                            <form method="POST" action="{{ route($routePrefix . '.toggle', [$event, $coupon]) }}" class="ajax-form d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-{{ $coupon->active ? 'secondary' : 'success' }}" title="{{ $coupon->active ? 'Desativar' : 'Ativar' }}">
                                                    <i class="fas fa-toggle-{{ $coupon->active ? 'on' : 'off' }}"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($canEditCoupon)
                                            <a href="{{ route($routePrefix . '.edit', [$event, $coupon]) }}" class="btn btn-outline-info" title="Editar" data-pjax>
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if($canDeleteCoupon)
                                            <form method="POST" action="{{ route($routePrefix . '.destroy', [$event, $coupon]) }}" class="ajax-form form-delete d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-outline-danger btn-delete" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-ticket-alt fa-2x d-block mb-2"></i>
                                    Nenhum cupom cadastrado para este evento.
                                    @if($canCreateCoupon)
                                        <div class="mt-3">
                                            <a href="{{ route($routePrefix . '.create', $event) }}" class="btn btn-primary btn-sm rounded-pill" data-pjax>
                                                <i class="fas fa-plus mr-1"></i> Criar primeiro cupom
                                            </a>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($coupons->hasPages())
            <div class="card-footer">{{ $coupons->links() }}</div>
        @endif
    </div>
</div>
@endsection
