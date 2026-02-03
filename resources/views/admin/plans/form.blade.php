@extends('admin.layouts.app')

@section('page_title', ($plan->id ? 'Editar' : 'Novo').' plano')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.plans.index') }}" data-pjax>Planos</a></li>
    <li class="breadcrumb-item active">{{ $plan->id ? 'Editar' : 'Novo' }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form class="ajax-form" method="POST" action="{{ $plan->id ? route('admin.plans.update',$plan) : route('admin.plans.store') }}" enctype="multipart/form-data">
            @csrf
            @if($plan->id) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nome do pacote</label>
                    <input name="name" class="form-control" value="{{ old('name',$plan->name) }}" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Preço</label>
                    <input name="price" class="form-control mask-money" value="{{ old('price',$plan->price) }}" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Período</label>
                    <select name="period" class="form-control">
                        @foreach(['mensal','trimestral','semestral','anual','vitalício'] as $p)
                            <option value="{{ $p }}" {{ old('period',$plan->period)==$p?'selected':'' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="highlight" value="0">
                        <input type="checkbox" class="custom-control-input" id="highlight" name="highlight" value="1" {{ old('highlight',$plan->highlight) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="highlight">Destacar (ribbon)</label>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="coupons_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="coupons_enabled" name="coupons_enabled" value="1" {{ old('coupons_enabled',$plan->coupons_enabled) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="coupons_enabled">Permitir cupons</label>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mt-4">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active',$plan->is_active ?? true) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Plano ativo</label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Imagem do pacote</label>
                    <div class="upload-box" data-max-size="5242880" data-crop="1">
                        <input type="file" name="image" accept="image/*" class="d-none">
                        <div class="upload-preview mb-2"></div>
                        <div class="upload-meta text-muted"></div>
                        <small class="text-muted upload-help"></small>
                        <div class="progress progress-sm d-none mt-2"><div class="progress-bar bg-primary" style="width:0%"></div></div>
                        <button type="button" class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                    </div>
                    @if($plan->image)<div class="mt-2"><img src="{{ asset('storage/'.$plan->image) }}" class="img-thumbnail" width="120"></div>@endif
                </div>
                <div class="form-group col-md-6">
                    <label>Benefícios (um por linha)</label>
                    @php $benefits = old('benefits',$plan->benefits ?? []); @endphp
                    <textarea name="benefits[]" class="form-control" rows="8" placeholder="Ex: Acesso ao portal&#10;Mentorias semanais&#10;Grupo VIP">{{ implode("\n", $benefits) }}</textarea>
                    <small class="text-muted">Use uma linha por benefício. Permissões abaixo complementam o acesso.</small>
                </div>
            </div>

            <div class="form-group">
                <label>Permissões liberadas por este plano</label>
                <div class="row">
                    @foreach($permissions as $perm)
                        <div class="col-md-4 col-lg-3">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="perm-{{ $perm->id }}" name="permissions[]" value="{{ $perm->id }}" {{ in_array($perm->id, old('permissions',$plan->permissions ?? [])) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="perm-{{ $perm->id }}">{{ $perm->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-right">
                <button class="btn btn-primary">Salvar</button>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary" data-pjax>Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection