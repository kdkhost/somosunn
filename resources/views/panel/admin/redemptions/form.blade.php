@extends('panel.layouts.app')

@section('title', $item->exists ? 'Editar Item' : 'Novo Item de Resgate')

@section('panel_content')
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">
                    {{ $item->exists ? 'Editar Item' : 'Criar Item de Resgate' }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Configure o produto ou serviço
                    para troca de pontos.</p>
            </div>
            <a href="{{ route('panel.admin.redemptions.index') }}"
                class="text-sm font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>

        <form action="{{ $item->exists ? route('panel.admin.redemptions.update', $item) : route('panel.admin.redemptions.store') }}"
            method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($item->exists)
                @method('PUT')
            @endif

            <div
                class="bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 space-y-8 transition-colors">
                <div>
                    <label
                        class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-4 tracking-widest">Imagem
                        do Produto</label>
                    <div class="flex items-center gap-6">
                        <div
                            class="w-32 h-32 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden transition-colors">
                            @if($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-image text-3xl text-slate-300"></i>
                            @endif
                        </div>
                        <div class="flex-1 space-y-2">
                            <input type="file" name="image"
                                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer">
                            <p class="text-[10px] text-slate-400 italic">Recomendado: 500x500px. PNG ou JPG.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Nome
                            do Item</label>
                        <input type="text" name="name" value="{{ old('name', $item->name) }}" required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                            placeholder="Ex: Mentoria Exclusiva (1h)">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Custo
                            em Pontos</label>
                        <input type="number" name="points_cost" value="{{ old('points_cost', $item->points_cost) }}"
                            required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-bold"
                            placeholder="1000">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Estoque
                            Inicial</label>
                        <input type="number" name="stock" value="{{ old('stock', $item->stock) }}" required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium"
                            placeholder="50">
                    </div>
                </div>

                <div>
                    <label
                        class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest">Descrição
                        detalhada</label>
                    <textarea id="redemptionDescription" name="description" rows="5"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-slate-900 dark:text-white font-medium">{{ old('description', $item->description) }}</textarea>
                    <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500">
                        Use formatação rica para detalhar o item. Envio de imagens e arquivos fica desativado neste campo.
                    </p>
                </div>

                <div
                    class="flex items-center justify-between pt-6 border-t border-slate-100 dark:border-slate-800 transition-colors">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))
                            class="w-5 h-5 text-blue-600 border-slate-300 rounded-lg focus:ring-blue-500 bg-white transition-all">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Disponível para Resgate</span>
                    </label>

                    <button type="submit"
                        class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-xl shadow-blue-500/30 transition-all flex items-center gap-2">
                        <i class="fas fa-check"></i>
                        <span>{{ $item->exists ? 'Atualizar Item' : 'Criar Item' }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                const $descriptionField = $('#redemptionDescription');

                if (!(window.jQuery && $.fn && $.fn.summernote) || !$descriptionField.length || $descriptionField.next('.note-editor').length) {
                    return;
                }

                $descriptionField.summernote({
                    height: 260,
                    lang: 'pt-BR',
                    placeholder: 'Detalhe o item de resgate, regras de uso, entrega e condições...',
                    disableDragAndDrop: true,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ],
                    callbacks: {
                        onChange: function (contents) {
                            $descriptionField.val(contents);
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

                $descriptionField.next('.note-editor').find('.note-editable').on('drop', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                });
            });
        </script>
    @endpush
@endsection
