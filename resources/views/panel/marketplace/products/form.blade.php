@extends('panel.layouts.app')

@section('title', ($product->exists ? 'Editar produto' : 'Novo produto') . ' - UNN')

@section('panel_content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Catalogo proprio</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $product->exists ? 'Editar produto' : 'Novo produto' }}</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Preencha os dados comerciais, a entrega e a galeria do seu produto.</p>
            </div>
            <a href="{{ route('panel.marketplace.products.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 transition">
                <i class="fas fa-arrow-left text-slate-400"></i> Voltar
            </a>
        </div>

        <form action="{{ $product->exists ? route('panel.marketplace.products.update', $product) : route('panel.marketplace.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($product->exists)
                @method('PUT')
            @endif

            <div class="grid gap-6 xl:grid-cols-[1.4fr,0.8fr]">
                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Titulo</label>
                            <input type="text" name="title" value="{{ old('title', $product->title) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                            @error('title')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tipo</label>
                            <select name="type" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @foreach(['digital' => 'Digital', 'physical' => 'Fisico'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', $product->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Status</label>
                            <select name="status" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                                @foreach(['draft' => 'Rascunho', 'published' => 'Publicado'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $product->status ?: 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Preco</label>
                            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Preco promocional</label>
                            <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                        </div>
                        <div class="flex items-center gap-3 pt-8">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Destacar produto na vitrine</span>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Resumo</label>
                            <input type="text" name="excerpt" value="{{ old('excerpt', $product->excerpt) }}" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Descricao</label>
                            <textarea name="description" rows="8" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Entrega e logistica</h2>
                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <div><input type="number" min="0" name="stock" value="{{ old('stock', $product->stock) }}" placeholder="Estoque" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white"></div>
                            <div><input type="number" min="0" name="weight_grams" value="{{ old('weight_grams', $product->weight_grams) }}" placeholder="Peso em gramas" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white"></div>
                            <div><input type="number" min="0" name="height_cm" value="{{ old('height_cm', $product->height_cm) }}" placeholder="Altura em cm" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white"></div>
                            <div><input type="number" min="0" name="width_cm" value="{{ old('width_cm', $product->width_cm) }}" placeholder="Largura em cm" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white"></div>
                            <div><input type="number" min="0" name="length_cm" value="{{ old('length_cm', $product->length_cm) }}" placeholder="Comprimento em cm" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white"></div>
                            <div><input type="url" name="digital_url" value="{{ old('digital_url', $product->digital_url) }}" placeholder="URL externa do produto digital" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white"></div>
                            <div class="md:col-span-2"><input type="file" name="digital_file" class="block w-full text-sm text-slate-500 dark:text-slate-300"></div>
                            @if($product->digital_file_name)
                                <div class="md:col-span-2 rounded-2xl bg-slate-50 dark:bg-slate-950 p-4 text-sm text-slate-600 dark:text-slate-300">
                                    Arquivo atual: <strong>{{ $product->digital_file_name }}</strong>
                                    <label class="ml-4 inline-flex items-center gap-2 text-red-500"><input type="hidden" name="remove_digital_file" value="0"><input type="checkbox" name="remove_digital_file" value="1"> Remover</label>
                                </div>
                            @endif
                            <div class="md:col-span-2">
                                <textarea name="digital_instructions" rows="4" placeholder="Instrucoes para entrega digital" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">{{ old('digital_instructions', $product->digital_instructions) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Capa do produto</h2>
                        @if($product->cover_url)
                            <img src="{{ $product->cover_url }}" alt="Capa" class="mt-4 h-44 w-full rounded-3xl object-cover border border-slate-200 dark:border-slate-700">
                        @endif
                        <input type="file" name="cover" accept="image/*" class="mt-4 block w-full text-sm text-slate-500 dark:text-slate-300">
                        <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                            <input type="hidden" name="remove_cover" value="0">
                            <input type="checkbox" name="remove_cover" value="1" class="rounded border-slate-300 text-red-500"> Remover capa atual
                        </label>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">Galeria</h2>
                        <input type="file" name="gallery[]" multiple class="mt-4 block w-full text-sm text-slate-500 dark:text-slate-300">
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
                    </div>
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
