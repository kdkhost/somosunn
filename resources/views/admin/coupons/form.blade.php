@extends('admin.layouts.app')

@section('page_title', ($coupon->id ? 'Editar' : 'Novo').' cupom')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.coupons.index') }}" data-pjax>Cupons</a></li>
    <li class="breadcrumb-item active">{{ $coupon->id ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form class="ajax-form" method="POST" action="{{ $coupon->id ? route('admin.coupons.update',$coupon) : route('admin.coupons.store') }}">
            @csrf
            @if($coupon->id) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Código</label>
                    <div class="input-group">
                        <input name="code" class="form-control" value="{{ old('code',$coupon->code) }}" placeholder="EX: BLACKFRIDAY26" required>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnGenCode">Gerar</button>
                        </div>
                    </div>
                    <small class="text-muted">Sem espaços. Será salvo em maiúsculo.</small>
                </div>
                <div class="form-group col-md-4">
                    <label>Tipo de desconto</label>
                    <select name="discount_type" class="form-control" required>
                        <option value="percent" {{ old('discount_type',$coupon->discount_type)=='percent'?'selected':'' }}>Percentual (%)</option>
                        <option value="fixed" {{ old('discount_type',$coupon->discount_type)=='fixed'?'selected':'' }}>Valor fixo (R$)</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Valor do desconto</label>
                    <input name="discount_value" class="form-control" value="{{ old('discount_value',$coupon->discount_value) }}" required>
                    <small class="text-muted">Ex: 10 (10%) ou 25.90 (R$)</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nome (opcional)</label>
                    <input name="name" class="form-control" value="{{ old('name',$coupon->name) }}" placeholder="Ex: Black Friday 2026">
                </div>
                <div class="form-group col-md-6">
                    <label>Descrição (opcional)</label>
                    <input name="description" class="form-control" value="{{ old('description',$coupon->description) }}" placeholder="Mensagem interna ou observações">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Escopo</label>
                    <select name="applies_to" class="form-control" required>
                        <option value="all" {{ old('applies_to',$coupon->applies_to ?? 'all')=='all'?'selected':'' }}>Geral (site todo)</option>
                        <option value="event" {{ old('applies_to',$coupon->applies_to)=='event'?'selected':'' }}>Somente eventos</option>
                        <option value="course" {{ old('applies_to',$coupon->applies_to)=='course'?'selected':'' }}>Somente cursos</option>
                        <option value="mentorship" {{ old('applies_to',$coupon->applies_to)=='mentorship'?'selected':'' }}>Somente mentorias</option>
                    </select>
                </div>
                <div class="form-group col-md-4" data-coupon-item-wrap>
                    <label>Item (opcional)</label>
                    <select name="applies_to_id" class="form-control" id="coupon_applies_to_id">
                        <option value="">Aplicar a todos do escopo</option>
                        @foreach(($events ?? collect()) as $event)
                            <option value="{{ $event->id }}" data-scope="event" {{ (string) old('applies_to_id', $coupon->applies_to_id) === (string) $event->id ? 'selected' : '' }}>
                                Evento #{{ $event->id }} — {{ $event->title }}@if($event->start_at) ({{ $event->start_at->format('d/m/Y') }})@endif
                            </option>
                        @endforeach
                        @foreach(($courses ?? collect()) as $course)
                            <option value="{{ $course->id }}" data-scope="course" {{ (string) old('applies_to_id', $coupon->applies_to_id) === (string) $course->id ? 'selected' : '' }}>
                                Curso #{{ $course->id }} — {{ $course->title }}
                            </option>
                        @endforeach
                        @foreach(($mentorships ?? collect()) as $mentorship)
                            <option value="{{ $mentorship->id }}" data-scope="mentorship" {{ (string) old('applies_to_id', $coupon->applies_to_id) === (string) $mentorship->id ? 'selected' : '' }}>
                                Mentoria #{{ $mentorship->id }} — {{ $mentorship->title }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted" data-coupon-item-help>Selecione o item para criar uma promoção direcionada (opcional).</small>
                </div>
                <div class="form-group col-md-4">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active',$coupon->is_active ?? true) ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ !old('is_active', $coupon->is_active ?? true) ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Valor mínimo (opcional)</label>
                    <input name="min_amount" class="form-control" value="{{ old('min_amount',$coupon->min_amount) }}" placeholder="Ex: 100.00">
                </div>
                <div class="form-group col-md-3">
                    <label>Limite total (opcional)</label>
                    <input name="max_uses" class="form-control" value="{{ old('max_uses',$coupon->max_uses) }}" placeholder="Ex: 200">
                </div>
                <div class="form-group col-md-3">
                    <label>Limite por usuário (opcional)</label>
                    <input name="max_uses_per_user" class="form-control" value="{{ old('max_uses_per_user',$coupon->max_uses_per_user) }}" placeholder="Ex: 1">
                </div>
                <div class="form-group col-md-3">
                    <label>Início (opcional)</label>
                    <input name="starts_at" class="form-control" data-datetime-picker value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d H:i')) }}" placeholder="AAAA-MM-DD HH:MM" autocomplete="off">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Término (opcional)</label>
                    <input name="ends_at" class="form-control" data-datetime-picker value="{{ old('ends_at', optional($coupon->ends_at)->format('Y-m-d H:i')) }}" placeholder="AAAA-MM-DD HH:MM" autocomplete="off">
                </div>
                <div class="form-group col-md-9">
                    <div class="alert alert-info mb-0 mt-4">
                        Dica: para Black Friday, use escopo "Geral" e desconto percentual. Para promoção direcionada, defina o escopo (evento/curso/mentoria) e selecione o item.
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary" data-pjax>Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
