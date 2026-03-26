@extends('panel.layouts.app')

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

@section('title', ($product->exists ? 'Editar produto' : 'Novo produto') . ' - UNN')

@section('panel_content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Catalogo proprio</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $product->exists ? 'Editar produto' : 'Novo produto' }}</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Cadastre o produto uma vez e defina se ele vende na loja, troca por {{ $coinName }} ou encaminha para um site externo.</p>
            </div>
            <a href="{{ route('panel.marketplace.products.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 transition">
                <i class="fas fa-arrow-left text-slate-400"></i> Voltar
            </a>
        </div>

        <form action="{{ $product->exists ? route('panel.marketplace.products.update', $product) : route('panel.marketplace.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="seller-product-form">
            @csrf
            @if($product->exists)
                @method('PUT')
            @endif

            <div class="grid gap-6 xl:grid-cols-[1.45fr,0.85fr]">
                <div class="space-y-6">
                    <section class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm space-y-6">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Titulo</label>
                                <input type="text" name="title" value="{{ old('title', $product->title) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @error('title')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tipo</label>
                                <select name="type" id="seller-product-type" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                    @foreach(['digital' => 'Digital', 'physical' => 'Fisico'] as $value => $label)
                                        <option value="{{ $value }}" {{ $currentType === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Canal do produto</label>
                                <select name="sales_channel" id="seller-product-channel" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                    @foreach(\App\Models\SellerProduct::SALES_CHANNELS as $value => $label)
                                        <option value="{{ $value }}" {{ $currentSalesChannel === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('sales_channel')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status</label>
                                <select name="status" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                    @foreach(['draft' => 'Rascunho', 'published' => 'Publicado'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', $product->status ?: 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">SKU</label>
                                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @error('sku')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Preco</label>
                                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @error('price')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Preco promocional</label>
                                <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @error('sale_price')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="seller-product-points-box">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Valor para troca em {{ $coinName }}</label>
                                <input type="text" name="points_reference_value" id="seller-product-points-reference" value="{{ $pointsReferenceValue }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white" placeholder="Ex.: 79,90">
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Se deixar em branco, o sistema usa o preco atual do produto como base.</p>
                                @error('points_reference_value')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="seller-product-points-box">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Custo estimado em {{ $coinName }}</label>
                                <input type="text" id="seller-product-points-preview" value="" readonly class="w-full rounded-2xl border-blue-200 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/20 px-4 py-3 text-blue-700 dark:text-blue-300 font-black">
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Conversao baseada na cotacao configurada pela plataforma.</p>
                            </div>

                            <div class="md:col-span-2 seller-product-external-row">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">URL de compra externa</label>
                                <input type="url" name="external_checkout_url" value="{{ old('external_checkout_url', $product->external_checkout_url) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white" placeholder="https://sualojaexterna.com/produto">
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Obrigatoria quando o canal for "Somente venda no site externo".</p>
                                @error('external_checkout_url')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Resumo</label>
                                <textarea name="excerpt" rows="3" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">{{ old('excerpt', $product->excerpt) }}</textarea>
                                @error('excerpt')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Descricao</label>
                                <textarea name="description" id="seller-product-description" rows="10" class="seller-product-editor w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">{{ old('description', $product->description) }}</textarea>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Use o editor rico padrao para montar uma descricao profissional, com listas, links e estrutura visual.</p>
                                @error('description')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="md:col-span-2 flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-800 px-4 py-4">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600">
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Destacar produto na vitrine</span>
                            </div>
                        </div>
                    </section>
                    <section class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Entrega e logistica</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Os campos abaixo se adaptam ao tipo do produto e ao canal escolhido.</p>

                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <div class="seller-product-physical-box">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Estoque</label>
                                <input type="number" min="0" name="stock" value="{{ old('stock', $product->stock) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @error('stock')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="seller-product-physical-box seller-product-store-box">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Peso em gramas</label>
                                <input type="number" min="0" name="weight_grams" value="{{ old('weight_grams', $product->weight_grams) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @error('weight_grams')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="seller-product-physical-box seller-product-store-box">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Altura em cm</label>
                                <input type="number" min="0" name="height_cm" value="{{ old('height_cm', $product->height_cm) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @error('height_cm')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="seller-product-physical-box seller-product-store-box">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Largura em cm</label>
                                <input type="number" min="0" name="width_cm" value="{{ old('width_cm', $product->width_cm) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @error('width_cm')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="seller-product-physical-box seller-product-store-box">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Comprimento em cm</label>
                                <input type="number" min="0" name="length_cm" value="{{ old('length_cm', $product->length_cm) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @error('length_cm')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="seller-product-digital-box seller-product-store-box">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">URL externa do produto digital</label>
                                <input type="url" name="digital_url" value="{{ old('digital_url', $product->digital_url) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white" placeholder="https://...">
                                @error('digital_url')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            <div class="seller-product-digital-box seller-product-store-box">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Arquivo digital protegido</label>
                                <input type="file" name="digital_file" class="block w-full text-sm text-slate-500 dark:text-slate-300">
                                @error('digital_file')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>

                            @if($product->digital_file_name)
                                <div class="seller-product-digital-box seller-product-store-box md:col-span-2 rounded-2xl bg-slate-50 dark:bg-slate-950 p-4 text-sm text-slate-600 dark:text-slate-300">
                                    Arquivo atual: <strong>{{ $product->digital_file_name }}</strong>
                                    <label class="ml-4 inline-flex items-center gap-2 text-red-500">
                                        <input type="hidden" name="remove_digital_file" value="0">
                                        <input type="checkbox" name="remove_digital_file" value="1"> Remover
                                    </label>
                                </div>
                            @endif

                            <div class="seller-product-digital-box md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Instrucoes de entrega digital</label>
                                <textarea name="digital_instructions" id="seller-product-digital-instructions" rows="6" class="seller-product-editor w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">{{ old('digital_instructions', $product->digital_instructions) }}</textarea>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Tambem aceita Summernote para instrucoes, passo a passo ou observacoes de entrega.</p>
                                @error('digital_instructions')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </section>
                </div>

                <div class="space-y-6">
                    <section class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Capa do produto</h2>
                        @if($product->cover_url)
                            <img src="{{ $product->cover_url }}" alt="Capa" class="mt-4 h-44 w-full rounded-3xl object-cover border border-slate-200 dark:border-slate-700">
                        @endif
                        <input type="file" name="cover" accept="image/*" class="mt-4 block w-full text-sm text-slate-500 dark:text-slate-300">
                        @error('cover')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <input type="hidden" name="remove_cover" value="0">
                            <input type="checkbox" name="remove_cover" value="1" class="rounded border-slate-300 text-red-500"> Remover capa atual
                        </label>
                    </section>

                    <section class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Galeria</h2>
                        <input type="file" name="gallery[]" multiple class="mt-4 block w-full text-sm text-slate-500 dark:text-slate-300">
                        @error('gallery.*')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                        @if($product->exists && $product->media->isNotEmpty())
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach($product->media as $media)
                                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-3">
                                        @if($media->media_type === 'video')
                                            <video src="{{ $media->file_url }}" controls class="h-28 w-full rounded-2xl object-cover"></video>
                                        @else
                                            <img src="{{ $media->file_url }}" alt="Midia" class="h-28 w-full rounded-2xl object-cover">
                                        @endif
                                        <form action="{{ route('panel.marketplace.products.media.destroy', [$product, $media]) }}" method="POST" class="mt-3">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-300">Remover</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm space-y-4">
                        <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-black uppercase tracking-[0.22em] text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/20 dark:text-blue-300">
                            <i class="fas fa-store"></i> Distribuicao do produto
                        </div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-950 p-4 text-sm text-slate-600 dark:text-slate-300">
                            <p class="font-black text-slate-900 dark:text-white">Como esse produto pode operar</p>
                            <ul class="mt-3 space-y-2 leading-6">
                                <li><strong>Troca de pontos:</strong> gera ou atualiza automaticamente o item de resgate do vendedor.</li>
                                <li><strong>Loja virtual:</strong> habilita compra direta com o carrinho e checkout do marketplace.</li>
                                <li><strong>Site externo:</strong> usa botao de compra apontando para sua URL externa.</li>
                            </ul>
                        </div>

                        @if($product->redeemableItem)
                            <div class="rounded-2xl border border-emerald-200 dark:border-emerald-900/40 bg-emerald-50 dark:bg-emerald-950/20 p-4 text-sm text-emerald-800 dark:text-emerald-200">
                                Item vinculado para {{ $coinName }}: <strong>{{ number_format((int) $product->redeemableItem->points_cost, 0, ',', '.') }} {{ $coinName }}</strong>
                            </div>
                        @endif
                    </section>
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                @if($product->exists)
                    <button type="submit" formaction="{{ route('panel.marketplace.products.destroy', $product) }}" formmethod="POST" onclick="event.preventDefault(); if(confirm('Deseja remover este produto?')) { const form = this.closest('form'); const temp = document.createElement('input'); temp.type='hidden'; temp.name='_method'; temp.value='DELETE'; form.appendChild(temp); form.submit(); }" class="inline-flex items-center gap-2 rounded-2xl bg-red-50 px-5 py-3 text-sm font-black text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-300 transition">
                        <i class="fas fa-trash"></i> Excluir
                    </button>
                @endif
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition">
                    <i class="fas fa-save"></i> Salvar produto
                </button>
            </div>
        </form>
    </div>
@endsection

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

            function bindSellerProductForm() {
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
                                $field.val($field.summernote('code'));
                            }
                        });
                    });
                }

                const typeField = document.getElementById('seller-product-type');
                const channelField = document.getElementById('seller-product-channel');
                const referenceInput = document.getElementById('seller-product-points-reference');
                const previewInput = document.getElementById('seller-product-points-preview');
                const priceInput = document.querySelector('[name="price"]');
                const salePriceInput = document.querySelector('[name="sale_price"]');
                const unitValue = {{ number_format($unitValue, 4, '.', '') }};
                const coinName = @json($coinName);

                const refreshVisibility = function () {
                    const type = typeField ? typeField.value : 'digital';
                    const channel = channelField ? channelField.value : 'store_only';
                    const storeEnabled = channel === 'store_only' || channel === 'store_and_points';
                    const pointsEnabled = channel === 'points_only' || channel === 'store_and_points';
                    const externalEnabled = channel === 'external_only';

                    document.querySelectorAll('.seller-product-physical-box').forEach((node) => {
                        node.classList.toggle('hidden', type !== 'physical');
                    });

                    document.querySelectorAll('.seller-product-digital-box').forEach((node) => {
                        node.classList.toggle('hidden', type !== 'digital');
                    });

                    document.querySelectorAll('.seller-product-store-box').forEach((node) => {
                        node.classList.toggle('hidden', !storeEnabled);
                    });

                    document.querySelectorAll('.seller-product-points-box').forEach((node) => {
                        node.classList.toggle('hidden', !pointsEnabled);
                    });

                    document.querySelectorAll('.seller-product-external-row').forEach((node) => {
                        node.classList.toggle('hidden', !externalEnabled);
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

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindSellerProductForm);
            } else {
                bindSellerProductForm();
            }
        })();
    </script>
@endpush
