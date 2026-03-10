@extends('admin.layouts.app')

@section('page_title', $item->exists ? 'Editar Item' : 'Novo Item')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.redemptions.index') }}">Resgates</a></li>
    <li class="breadcrumb-item active">{{ $item->exists ? 'Editar' : 'Novo' }}</li>
@endsection

@php
    $coinName = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
    $basePoints = (int) ($exchangeSettings['base_points'] ?? 100);
    $baseAmount = (float) ($exchangeSettings['base_amount'] ?? 1);
    $unitValue = (float) ($exchangeSettings['unit_value_brl'] ?? $exchangeSettings['point_value'] ?? 0.01);
    $referenceValue = old('reference_value');

    if ($referenceValue === null) {
        $referenceValue = $item->reference_value !== null
            ? number_format((float) $item->reference_value, 2, ',', '.')
            : number_format((float) (($item->points_cost ?? 0) * $unitValue), 2, ',', '.');
    }
@endphp

@section('content')
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-gift mr-2"></i>{{ $item->exists ? 'Editar' : 'Cadastrar' }} Item para Resgate
                    </h3>
                </div>

                <form action="{{ $item->exists ? route('admin.redemptions.update', $item) : route('admin.redemptions.store') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    @if($item->exists)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Cotacao vigente:</strong>
                            {{ number_format($basePoints, 0, ',', '.') }} {{ $coinName }} = R$ {{ number_format($baseAmount, 2, ',', '.') }}
                            <br>
                            <small>Cada {{ $coinName }} vale R$ {{ number_format($unitValue, 4, ',', '.') }}.</small>
                        </div>

                        <div class="form-group">
                            <label for="name">Nome do Item <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control"
                                value="{{ old('name', $item->name) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="item_type">Tipo do Item</label>
                                    <select name="item_type" id="item_type" class="form-control">
                                        @foreach(\App\Models\RedeemableItem::ITEM_TYPES as $typeValue => $typeLabel)
                                            <option value="{{ $typeValue }}" @selected(old('item_type', $item->item_type ?? 'service') === $typeValue)>{{ $typeLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reference_value">Valor de Referencia (R$) <span class="text-danger">*</span></label>
                                    <input type="text" name="reference_value" id="reference_value" class="form-control"
                                        value="{{ $referenceValue }}" required>
                                    <small class="text-muted">O sistema converte automaticamente esse valor em {{ $coinName }}.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="points_cost_preview">Custo em {{ $coinName }}</label>
                                    <input type="text" id="points_cost_preview" class="form-control"
                                        value="{{ number_format((int) old('points_cost', $item->points_cost ?? 0), 0, ',', '.') }} {{ $coinName }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="stock">Estoque Disponivel <span class="text-danger">*</span></label>
                                    <input type="number" name="stock" id="stock" class="form-control"
                                        value="{{ old('stock', $item->stock ?? -1) }}" required min="-1">
                                    <small class="text-muted">Use -1 para estoque ilimitado.</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="delivery_lead_days">Prazo de Entrega (dias) <span class="text-danger">*</span></label>
                                    <input type="number" name="delivery_lead_days" id="delivery_lead_days" class="form-control"
                                        value="{{ old('delivery_lead_days', $item->delivery_lead_days ?? 7) }}" required min="1" max="365">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Descricao</label>
                            <textarea id="description" name="description" class="form-control summernote"
                                rows="5">{{ old('description', $item->description) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="fulfillment_instructions">Regras de Entrega / Fornecimento</label>
                            <textarea id="fulfillment_instructions" name="fulfillment_instructions" class="form-control"
                                rows="4" placeholder="Explique como o produto digital/fisico ou servico sera entregue.">{{ old('fulfillment_instructions', $item->fulfillment_instructions) }}</textarea>
                            <small class="text-muted">Esse texto sera enviado ao fornecedor quando o comprador trocar {{ $coinName }} pelo item.</small>
                        </div>

                        <div class="form-group mb-2">
                            <label for="image">Imagem do Item</label>
                            <input type="hidden" name="remove_image" value="0">
                            <div class="upload-box" data-max-size="5242880"
                                data-existing-url="{{ $item->image ? \App\Support\UploadStorage::url($item->image) : '' }}"
                                data-remove-input="[name='remove_image']">
                                <input type="file" name="image" id="image" accept="image/*" class="d-none">
                                <div class="upload-preview mb-2"></div>
                                <div class="upload-meta text-muted"></div>
                                <small class="text-muted upload-help"></small>
                                <div class="progress upload-progress progress-sm d-none mt-2">
                                    <div class="progress-bar bg-primary" style="width:0%"></div>
                                </div>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger upload-remove d-none mt-2">Remover</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="callout callout-info mb-0">
                                <h5 class="mb-2">Fornecedor fixo</h5>
                                <p class="mb-0">{{ $providerLabel }}</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active"
                                    value="1" @checked(old('is_active', $item->is_active ?? true))>
                                <label class="custom-control-label" for="is_active">Item Disponivel para Resgate</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <a href="{{ route('admin.redemptions.index') }}" class="btn btn-secondary mr-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Salvar Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const referenceInput = document.getElementById('reference_value');
            const pointsPreview = document.getElementById('points_cost_preview');
            const unitValue = {{ number_format($unitValue, 4, '.', '') }};
            const coinName = @json($coinName);

            const parseMoney = (value) => {
                let normalized = String(value || '').replace(/[R$\s]/g, '');
                if (normalized.includes(',')) {
                    normalized = normalized.replace(/\./g, '').replace(',', '.');
                }
                return Math.max(0, parseFloat(normalized || '0') || 0);
            };

            const refreshUnits = () => {
                if (!referenceInput || !pointsPreview) {
                    return;
                }

                const money = parseMoney(referenceInput.value);
                const units = money > 0 && unitValue > 0 ? Math.ceil(money / unitValue) : 0;
                pointsPreview.value = new Intl.NumberFormat('pt-BR').format(units) + ' ' + coinName;
            };

            if (referenceInput) {
                referenceInput.addEventListener('input', refreshUnits);
                referenceInput.addEventListener('change', refreshUnits);
                refreshUnits();
            }
        })();
    </script>
@endpush
