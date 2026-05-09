@extends('admin.layouts.app')

@section('page_title','Cupons')
@section('breadcrumb')<li class="breadcrumb-item active">Cupons</li>@endsection

@section('content')
<div class="container-fluid">
    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-purple elevation-1">
                <span class="info-box-icon"><i class="fas fa-tags"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de Cupons</span>
                    <span class="info-box-number">{{ $coupons->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-success elevation-1">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ativos</span>
                    <span class="info-box-number">{{ $coupons->where('is_active', true)->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-info elevation-1">
                <span class="info-box-icon"><i class="fas fa-percent"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Percentuais</span>
                    <span class="info-box-number">{{ $coupons->where('discount_type', 'percent')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-warning elevation-1">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valor Fixo</span>
                    <span class="info-box-number">{{ $coupons->where('discount_type', '!=', 'percent')->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-ticket-alt mr-2"></i>Cupons de Desconto</h3>
            <div class="card-tools">
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1" data-pjax>
                    <i class="fas fa-plus mr-1"></i> Novo cupom
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th><i class="fas fa-barcode text-muted mr-1"></i>Código</th>
                            <th><i class="fas fa-sliders-h text-muted mr-1"></i>Tipo</th>
                            <th><i class="fas fa-coins text-muted mr-1"></i>Valor</th>
                            <th><i class="fas fa-bullseye text-muted mr-1"></i>Escopo</th>
                            <th><i class="fas fa-calendar-alt text-muted mr-1"></i>Validade</th>
                            <th><i class="fas fa-toggle-on text-muted mr-1"></i>Status</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coupon)
                            <tr>
                                <td>
                                    <span class="badge badge-dark font-weight-bold" style="font-size: 13px; letter-spacing: 0.5px;">
                                        <i class="fas fa-tag mr-1"></i>{{ $coupon->code }}
                                    </span>
                                </td>
                                <td>
                                    @if($coupon->discount_type === 'percent')
                                        <span class="badge badge-info"><i class="fas fa-percent mr-1"></i>Percentual</span>
                                    @else
                                        <span class="badge badge-warning"><i class="fas fa-dollar-sign mr-1"></i>Fixo</span>
                                    @endif
                                </td>
                                <td class="font-weight-bold">
                                    @if($coupon->discount_type === 'percent')
                                        {{ rtrim(rtrim(number_format($coupon->discount_value, 2, ',', '.'), '0'), ',') }}%
                                    @else
                                        R$ {{ number_format($coupon->discount_value, 2, ',', '.') }}
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $scopeLabel = match($coupon->applies_to) {
                                            'event' => 'Evento',
                                            'course' => 'Curso',
                                            'mentorship' => 'Mentoria',
                                            default => 'Geral',
                                        };
                                        $scopeColor = match($coupon->applies_to) {
                                            'event' => 'primary',
                                            'course' => 'success',
                                            'mentorship' => 'purple',
                                            default => 'secondary',
                                        };
                                        $scopeIcon = match($coupon->applies_to) {
                                            'event' => 'fa-calendar-check',
                                            'course' => 'fa-graduation-cap',
                                            'mentorship' => 'fa-chalkboard-teacher',
                                            default => 'fa-globe',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $scopeColor }}">
                                        <i class="fas {{ $scopeIcon }} mr-1"></i>{{ $scopeLabel }}
                                    </span>
                                    @if($coupon->applies_to_id)
                                        <small class="text-muted">#{{ $coupon->applies_to_id }}</small>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    @php
                                        $from = $coupon->starts_at ? $coupon->starts_at->format('d/m/Y H:i') : null;
                                        $to = $coupon->ends_at ? $coupon->ends_at->format('d/m/Y H:i') : null;
                                    @endphp
                                    @if($from || $to)
                                        @if($from)<i class="fas fa-play text-success mr-1"></i>{{ $from }}<br>@endif
                                        @if($to)<i class="fas fa-stop text-danger mr-1"></i>{{ $to }}@endif
                                    @else
                                        <span class="text-muted"><i class="fas fa-infinity mr-1"></i>Sem limite</span>
                                    @endif
                                </td>
                                <td>
                                    @if($coupon->is_active)
                                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Ativo</span>
                                    @else
                                        <span class="badge badge-secondary"><i class="fas fa-pause-circle mr-1"></i>Inativo</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-info rounded-pill elevation-1 mr-1" data-pjax title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-pill elevation-1 btn-delete" data-action="{{ route('admin.coupons.destroy', $coupon) }}" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-ticket-alt fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-2">Nenhum cupom cadastrado ainda.</p>
                                    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1">
                                        <i class="fas fa-plus mr-1"></i> Criar primeiro cupom
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $coupons->links() }}</div>
        </div>
    </div>
</div>
@endsection
