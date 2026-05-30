@extends('admin.layouts.app')

@php
    $coinName = (string) ($exchangeSettings['coin_name'] ?? 'UNNBIT');
    $unitValue = (float) ($exchangeSettings['unit_value_brl'] ?? $exchangeSettings['point_value'] ?? 0.01);
    $currentType = old('type', $product->type ?: 'digital');
    $currentSalesChannel = old('sales_channel', $product->sales_channel ?: 'store_only');
    $pointsReferenceValue = old('points_reference_value');

    if ($pointsReferenceValue === null) {
        $pointsReferenceValue = $product->points_reference_value !== null
            ? number_format((float) $product->points_reference_value, 2, ',', '.')
            : '';
    }
@endphp

@section('title', ($product->exists ? 'Editar produto' : 'Novo produto') . ' - Marketplace')
@section('page_title', $product->exists ? 'Editar produto' : 'Novo produto')

@section('content')
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 font-weight-bold">{{ $product->exists ? 'Editar produto' : 'Novo produto' }}</h4>
                <p class="text-muted mb-0">Cadastre o produto uma vez e defina se ele vende na loja, troca por {{ $coinName }} ou encaminha para um site externo.</p>
            </div>
            <a href="{{ route('admin.marketplace.products.index') }}" class="btn btn-outline-secondary mt-3 mt-md-0">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>
    </div>

    <form action="{{ $product->exists ? route('admin.marketplace.products.update', $product) : route('admin.marketplace.products.store') }}" method="POST" enctype="multipart/form-data" id="seller-product-form">
        @csrf
        @if($product->exists)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-xl-8">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-edit mr-2 text-primary"></i>Dados principais</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="title">Titulo</label>
                                <input type="text" name="title" id="title" value="{{ old('title', $product->title) }}" class="form-control @error('title') is-invalid @enderror">
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="seller-product-type">Tipo</label>
                                <select name="type" id="seller-product-type" class="form-control @error('type') is-invalid @enderror">
                                    @foreach(['digital' => 'Digital', 'physical' => 'Fisico'] as $value => $label)
                                        <option value="{{ $value }}" {{ $currentType === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="seller-product-channel">Canal do produto</label>
                                <select name="sales_channel" id="seller-product-channel" class="form-control @error('sales_channel') is-invalid @enderror">
                                    @foreach(\App\Models\SellerProduct::SALES_CHANNELS as $value => $label)
                                        <option value="{{ $value }}" {{ $currentSalesChannel === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('sales_channel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                    @foreach(['draft' => 'Rascunho', 'published' => 'Publicado'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', $product->status ?: 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="sku">SKU</label>
                                <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" class="form-control @error('sku') is-invalid @enderror">
                                @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="price">Preco</label>
                                <input type="number" step="0.01" min="0" name="price" id="price" value="{{ old('price', $product->price) }}" class="form-control @error('price') is-invalid @enderror">
                                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="sale_price">Preco promocional</label>
                                <input type="number" step="0.01" min="0" name="sale_price" id="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="form-control @error('sale_price') is-invalid @enderror">
                                @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6 seller-product-points-box">
                                <label for="seller-product-points-reference">Valor para troca em {{ $coinName }}</label>
                                <input type="text" name="points_reference_value" id="seller-product-points-reference" value="{{ $pointsReferenceValue }}" class="form-control @error('points_reference_value') is-invalid @enderror" placeholder="Ex.: 79,90">
                                <small class="form-text text-muted">Se deixar em branco, o sistema usa o preco atual do produto como base.</small>
                                @error('points_reference_value')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6 seller-product-points-box">
                                <label for="seller-product-points-preview">Custo estimado em {{ $coinName }}</label>
                                <input type="text" id="seller-product-points-preview" value="" readonly class="form-control font-weight-bold bg-light">
                                <small class="form-text text-muted">Conversao baseada na cotacao configurada pela plataforma.</small>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="cancellation_period_days">Periodo de cancelamento (dias)</label>
                                <input type="number" name="cancellation_period_days" id="cancellation_period_days" value="{{ old('cancellation_period_days', $product->cancellation_period_days) }}" class="form-control @error('cancellation_period_days') is-invalid @enderror" min="0" placeholder="Ex.: 7">
                                <small class="form-text text-muted">Dias apos a compra em que o cliente pode solicitar cancelamento. 0 = nao permite cancelamento.</small>
                                @error('cancellation_period_days')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-12 seller-product-external-row">
                                <label for="external_checkout_url">URL de compra externa</label>
                                <input type="url" name="external_checkout_url" id="external_checkout_url" value="{{ old('external_checkout_url', $product->external_checkout_url) }}" class="form-control @error('external_checkout_url') is-invalid @enderror" placeholder="https://sualojaexterna.com/produto">
                                <small class="form-text text-muted">Obrigatoria quando o canal for somente venda no site externo.</small>
                                @error('external_checkout_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                <label for="excerpt">Resumo</label>
                                <textarea name="excerpt" id="excerpt" rows="3" class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt', $product->excerpt) }}</textarea>
                                @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-12">
                                <label for="seller-product-description">Descricao</label>
                                <textarea name="description" id="seller-product-description" rows="10" class="form-control seller-product-editor @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                                <small class="form-text text-muted">Use o editor rico padrao para montar uma descricao profissional, com listas, links e estrutura visual.</small>
                                @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-12 mb-0">
                                <div class="custom-control custom-checkbox">
                                    <input type="hidden" name="is_featured" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                                    <label class="custom-control-label" for="is_featured">Destacar produto na vitrine</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-shipping-fast mr-2 text-secondary"></i>Entrega e logística</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6 seller-product-physical-box">
                                <label for="stock">Estoque</label>
                                <input type="number" min="0" name="stock" id="stock" value="{{ old('stock', $product->stock) }}" class="form-control @error('stock') is-invalid @enderror">
                                @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6 seller-product-physical-box seller-product-store-box">
                                <label for="weight_grams">Peso em gramas</label>
                                <input type="number" min="0" name="weight_grams" id="weight_grams" value="{{ old('weight_grams', $product->weight_grams) }}" class="form-control @error('weight_grams') is-invalid @enderror">
                                @error('weight_grams')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4 seller-product-physical-box seller-product-store-box">
                                <label for="height_cm">Altura em cm</label>
                                <input type="number" min="0" name="height_cm" id="height_cm" value="{{ old('height_cm', $product->height_cm) }}" class="form-control @error('height_cm') is-invalid @enderror">
                                @error('height_cm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4 seller-product-physical-box seller-product-store-box">
                                <label for="width_cm">Largura em cm</label>
                                <input type="number" min="0" name="width_cm" id="width_cm" value="{{ old('width_cm', $product->width_cm) }}" class="form-control @error('width_cm') is-invalid @enderror">
                                @error('width_cm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-4 seller-product-physical-box seller-product-store-box">
                                <label for="length_cm">Comprimento em cm</label>
                                <input type="number" min="0" name="length_cm" id="length_cm" value="{{ old('length_cm', $product->length_cm) }}" class="form-control @error('length_cm') is-invalid @enderror">
                                @error('length_cm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6 seller-product-digital-box seller-product-store-box">
                                <label for="digital_url">URL externa do produto digital</label>
                                <input type="url" name="digital_url" id="digital_url" value="{{ old('digital_url', $product->digital_url) }}" class="form-control @error('digital_url') is-invalid @enderror" placeholder="https://...">
                                @error('digital_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6 seller-product-digital-box seller-product-store-box">
                                <label for="digital_file">Arquivo digital protegido</label>
                                <input type="file" name="digital_file" id="digital_file" class="form-control-file @error('digital_file') is-invalid @enderror">
                                @error('digital_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            @if($product->digital_file_name)
                                <div class="form-group col-md-12 seller-product-digital-box seller-product-store-box">
                                    <div class="alert alert-secondary mb-0">
                                        Arquivo atual: <strong>{{ $product->digital_file_name }}</strong>
                                        <div class="custom-control custom-checkbox mt-2">
                                            <input type="hidden" name="remove_digital_file" value="0">
                                            <input type="checkbox" class="custom-control-input" id="remove_digital_file" name="remove_digital_file" value="1">
                                            <label class="custom-control-label" for="remove_digital_file">Remover arquivo atual</label>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group col-md-12 seller-product-digital-box">
                                <label for="seller-product-digital-instructions">Instrucoes de entrega digital</label>
                                <textarea name="digital_instructions" id="seller-product-digital-instructions" rows="6" class="form-control seller-product-editor @error('digital_instructions') is-invalid @enderror">{{ old('digital_instructions', $product->digital_instructions) }}</textarea>
                                <small class="form-text text-muted">Tambem aceita Summernote para instrucoes, passo a passo ou observacoes de entrega.</small>
                                @error('digital_instructions')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-image mr-2 text-primary"></i>Capa do produto</h3>
                    </div>
                    <div class="card-body">
                        @if($product->cover_url)
                            <img src="{{ $product->cover_url }}" alt="Capa" class="img-fluid rounded border mb-3" style="max-height:160px; width:100%; object-fit:cover;">
                        @endif
                        <div class="dropzone-area" id="cover-dropzone">
                            <input type="file" name="cover" accept="image/*" class="dropzone-input" id="cover-input">
                            <div class="dropzone-placeholder">
                                <i class="fas fa-cloud-upload-alt text-primary mb-2" style="font-size:2rem;"></i>
                                <p class="font-weight-bold mb-1">Arraste a imagem aqui</p>
                                <p class="text-muted small mb-0">ou clique para selecionar (JPG, PNG, WEBP)</p>
                            </div>
                            <div class="dropzone-preview d-none">
                                <img src="" alt="Preview" class="img-fluid rounded border" style="max-height:120px; width:100%; object-fit:cover;">
                                <div class="dropzone-file-info mt-2 text-sm text-muted"></div>
                            </div>
                        </div>
                        @error('cover')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        @if($product->cover_url)
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="hidden" name="remove_cover" value="0">
                                <input type="checkbox" class="custom-control-input" id="remove_cover" name="remove_cover" value="1">
                                <label class="custom-control-label" for="remove_cover">Remover capa atual</label>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-images mr-2 text-primary"></i>Galeria</h3>
                    </div>
                    <div class="card-body">
                        <div class="dropzone-area" id="gallery-dropzone">
                            <input type="file" name="gallery[]" multiple accept="image/*,video/*" class="dropzone-input" id="gallery-input">
                            <div class="dropzone-placeholder">
                                <i class="fas fa-photo-video text-info mb-2" style="font-size:2rem;"></i>
                                <p class="font-weight-bold mb-1">Arraste imagens ou vídeos</p>
                                <p class="text-muted small mb-0">Múltiplos arquivos permitidos</p>
                            </div>
                            <div class="dropzone-preview d-none">
                                <div class="dropzone-gallery-grid"></div>
                                <div class="dropzone-file-info mt-2 text-sm text-muted"></div>
                            </div>
                        </div>
                        @error('gallery.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        @if($product->exists && $product->media->isNotEmpty())
                            <hr class="my-3">
                            <p class="text-muted small font-weight-bold mb-2">Mídias atuais:</p>
                            <div class="row">
                                @foreach($product->media as $media)
                                    <div class="col-4 mb-2">
                                        <div class="border rounded overflow-hidden position-relative" style="aspect-ratio:1;">
                                            @if($media->media_type === 'video')
                                                <video src="{{ $media->file_url }}" class="w-100 h-100" style="object-fit:cover;" muted></video>
                                                <span class="position-absolute badge badge-dark" style="top:4px;right:4px;font-size:9px;"><i class="fas fa-play"></i></span>
                                            @else
                                                <img src="{{ $media->file_url }}" alt="Mídia" class="w-100 h-100" style="object-fit:cover;">
                                            @endif
                                            <form action="{{ route('admin.marketplace.products.media.destroy', [$product, $media]) }}" method="POST"
                                                class="position-absolute" style="bottom:4px;right:4px;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger rounded-circle" style="width:22px;height:22px;padding:0;" title="Remover">
                                                    <i class="fas fa-times" style="font-size:9px;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Distribuicao do produto</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light mb-3">
                            <p class="font-weight-bold mb-2">Como esse produto pode operar</p>
                            <ul class="mb-0 pl-3">
                                <li><strong>Troca de pontos:</strong> gera ou atualiza o item de resgate do vendedor.</li>
                                <li><strong>Loja virtual:</strong> habilita compra direta com carrinho e checkout do marketplace.</li>
                                <li><strong>Site externo:</strong> usa um botao de compra apontando para sua URL externa.</li>
                            </ul>
                        </div>
                        @if($product->redeemableItem)
                            <div class="alert alert-success mb-0">
                                Item vinculado para {{ $coinName }}: <strong>{{ number_format((int) $product->redeemableItem->points_cost, 0, ',', '.') }} {{ $coinName }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.marketplace.products.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
                <div>
                    @if($product->exists)
                        <button type="submit" formaction="{{ route('admin.marketplace.products.destroy', $product) }}" formmethod="POST" onclick="event.preventDefault(); if(confirm('Deseja remover este produto?')) { const form = this.closest('form'); const temp = document.createElement('input'); temp.type='hidden'; temp.name='_method'; temp.value='DELETE'; form.appendChild(temp); form.submit(); }" class="btn btn-outline-danger rounded-pill px-4 mr-2">
                            <i class="fas fa-trash mr-1"></i> Excluir
                        </button>
                    @endif
                    <button type="submit" class="btn btn-primary rounded-pill px-4 elevation-1">
                        <i class="fas fa-save mr-1"></i> Salvar produto
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
<style>
    .dropzone-area {
        position: relative;
        border: 2px dashed #dee2e6;
        border-radius: 0.75rem;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8f9fa;
    }
    .dropzone-area:hover,
    .dropzone-area.dragover {
        border-color: #007bff;
        background: #eff6ff;
    }
    .dropzone-area .dropzone-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
    }
    .dropzone-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
        gap: 6px;
    }
    .dropzone-gallery-grid img {
        width: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }
</style>
@endpush

@push('scripts')
    <script>
        (function () {
            function parseMoney(value) {
                let normalized = String(value || '').replace(/[R$\s]/g, '');
                if (normalized.includes(',')) {
                    normalized = normalized.replace(/\./g, '').replace(',', '.');
                }
                return Math.max(0, parseFloat(normalized || '0') || 0);
            }

            function encodeEditorContent(value) {
                try {
                    return btoa(unescape(encodeURIComponent(String(value || ''))));
                } catch (error) {
                    return String(value || '');
                }
            }

            function bindAdminSellerProductForm() {
                if (window.jQuery && $.fn && $.fn.summernote) {
                    $('.seller-product-editor').each(function () {
                        const $field = $(this);

                        if ($field.next('.note-editor').length) {
                            return;
                        }

                        $field.summernote({
                            height: $field.attr('id') === 'seller-product-description' ? 320 : 220,
                            lang: 'pt-BR',
                            placeholder: 'Detalhe seu produto com uma apresentacao profissional.',
                            disableDragAndDrop: true,
                            toolbar: [
                                ['style', ['style']],
                                ['font', ['bold', 'italic', 'underline', 'clear']],
                                ['color', ['color']],
                                ['para', ['ul', 'ol', 'paragraph']],
                                ['table', ['table']],
                                ['insert', ['link']],
                                ['view', ['fullscreen', 'codeview', 'help']]
                            ],
                            callbacks: {
                                onChange: function (contents) {
                                    $field.val(contents);
                                },
                                onImageUpload: function () {
                                    return false;
                                },
                                onPaste: function (event) {
                                    const clipboardEvent = event.originalEvent || event;
                                    const items = clipboardEvent.clipboardData ? clipboardEvent.clipboardData.items || [] : [];
                                    const hasFile = Array.from(items).some(function (item) {
                                        return item.kind === 'file';
                                    });

                                    if (hasFile) {
                                        event.preventDefault();
                                    }
                                }
                            }
                        });

                        $field.next('.note-editor').find('.note-editable').on('drop', function (event) {
                            event.preventDefault();
                            event.stopPropagation();
                        });
                    });

                    $('#seller-product-form').off('submit.productEditors').on('submit.productEditors', function () {
                        $('.seller-product-editor').each(function () {
                            const $field = $(this);
                            if ($field.next('.note-editor').length) {
                                $field.val(encodeEditorContent($field.summernote('code')));
                            }
                        });
                    });
                }

                const typeField = document.getElementById('seller-product-type');
                const channelField = document.getElementById('seller-product-channel');
                const referenceInput = document.getElementById('seller-product-points-reference');
                const previewInput = document.getElementById('seller-product-points-preview');
                const priceInput = document.getElementById('price');
                const salePriceInput = document.getElementById('sale_price');
                const unitValue = {{ number_format($unitValue, 4, '.', '') }};
                const coinName = @json($coinName);

                const refreshVisibility = function () {
                    const type = typeField ? typeField.value : 'digital';
                    const channel = channelField ? channelField.value : 'store_only';
                    const storeEnabled = channel === 'store_only' || channel === 'store_and_points';
                    const pointsEnabled = channel === 'points_only' || channel === 'store_and_points';
                    const externalEnabled = channel === 'external_only';

                    document.querySelectorAll('.seller-product-physical-box').forEach((node) => {
                        node.classList.toggle('d-none', type !== 'physical');
                    });

                    document.querySelectorAll('.seller-product-digital-box').forEach((node) => {
                        node.classList.toggle('d-none', type !== 'digital');
                    });

                    document.querySelectorAll('.seller-product-store-box').forEach((node) => {
                        node.classList.toggle('d-none', !storeEnabled);
                    });

                    document.querySelectorAll('.seller-product-points-box').forEach((node) => {
                        node.classList.toggle('d-none', !pointsEnabled);
                    });

                    document.querySelectorAll('.seller-product-external-row').forEach((node) => {
                        node.classList.toggle('d-none', !externalEnabled);
                    });
                };

                const refreshPointsPreview = function () {
                    if (!previewInput) {
                        return;
                    }

                    const channel = channelField ? channelField.value : 'store_only';
                    if (!(channel === 'points_only' || channel === 'store_and_points')) {
                        previewInput.value = 'Nao se aplica';
                        return;
                    }

                    const customReference = parseMoney(referenceInput ? referenceInput.value : '');
                    const salePrice = parseMoney(salePriceInput ? salePriceInput.value : '');
                    const basePrice = parseMoney(priceInput ? priceInput.value : '');
                    const reference = customReference > 0 ? customReference : (salePrice > 0 ? salePrice : basePrice);
                    const points = reference > 0 && unitValue > 0 ? Math.ceil(reference / unitValue) : 0;

                    previewInput.value = new Intl.NumberFormat('pt-BR').format(points) + ' ' + coinName;
                };

                [typeField, channelField, referenceInput, priceInput, salePriceInput].forEach((element) => {
                    if (!element) {
                        return;
                    }

                    element.addEventListener('change', function () {
                        refreshVisibility();
                        refreshPointsPreview();
                    });

                    element.addEventListener('input', refreshPointsPreview);
                });

                refreshVisibility();
                refreshPointsPreview();
            }

            $(function () {
                bindAdminSellerProductForm();
            });
        })();

        // Dropzone preview para uploads
        document.querySelectorAll('.dropzone-area').forEach(function(zone) {
            const input = zone.querySelector('.dropzone-input');
            const placeholder = zone.querySelector('.dropzone-placeholder');
            const preview = zone.querySelector('.dropzone-preview');
            const fileInfo = zone.querySelector('.dropzone-file-info');
            const isGallery = input && input.hasAttribute('multiple');

            ['dragenter', 'dragover'].forEach(evt => {
                zone.addEventListener(evt, function(e) { e.preventDefault(); zone.classList.add('dragover'); });
            });
            ['dragleave', 'drop'].forEach(evt => {
                zone.addEventListener(evt, function(e) { e.preventDefault(); zone.classList.remove('dragover'); });
            });

            if (input) {
                input.addEventListener('change', function() {
                    const files = Array.from(this.files || []);
                    if (files.length === 0) return;

                    placeholder.classList.add('d-none');
                    preview.classList.remove('d-none');

                    if (isGallery) {
                        const grid = preview.querySelector('.dropzone-gallery-grid');
                        grid.innerHTML = '';
                        files.forEach(function(file) {
                            if (file.type.startsWith('image/')) {
                                const img = document.createElement('img');
                                img.src = URL.createObjectURL(file);
                                grid.appendChild(img);
                            }
                        });
                        fileInfo.textContent = files.length + ' arquivo(s) selecionado(s)';
                    } else {
                        const img = preview.querySelector('img');
                        if (img && files[0].type.startsWith('image/')) {
                            img.src = URL.createObjectURL(files[0]);
                        }
                        fileInfo.textContent = files[0].name + ' (' + (files[0].size / 1024 / 1024).toFixed(1) + ' MB)';
                    }
                });
            }
        });
    </script>
@endpush
