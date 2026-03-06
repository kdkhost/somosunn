@extends('panel.layouts.app')

@section('title', $item->exists ? 'Editar Item' : 'Novo Item de Resgate')

@php
    $basePoints = (int) ($exchangeSettings['base_points'] ?? 100);
    $baseAmount = (float) ($exchangeSettings['base_amount'] ?? 1);
    $pointValue = (float) ($exchangeSettings['point_value'] ?? 0.01);
    $referenceValue = old('reference_value');
    if ($referenceValue === null) {
        $referenceValue = $item->reference_value !== null
            ? number_format((float) $item->reference_value, 2, ',', '.')
            : number_format((float) (($item->points_cost ?? 0) * $pointValue), 2, ',', '.');
    }
@endphp

@section('panel_content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                    {{ $item->exists ? 'Editar Item de Resgate' : 'Criar Item de Resgate' }}
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Cadastre produtos ou serviços para troca de pontos com rastreio, prazo e responsável fixo pela entrega.
                </p>
            </div>
            <a href="{{ route('panel.admin.redemptions.index') }}"
                class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 shadow-sm transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                <i class="fas fa-arrow-left"></i>
                Voltar
            </a>
        </div>

        <form action="{{ $item->exists ? route('panel.admin.redemptions.update', $item) : route('panel.admin.redemptions.store') }}"
            method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($item->exists)
                @method('PUT')
            @endif

            <div class="grid gap-6 xl:grid-cols-[1.35fr_0.85fr]">
                <div class="space-y-6">
                    <section class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="grid gap-6 md:grid-cols-[160px_1fr]">
                            <div class="space-y-4">
                                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950">
                                    <div class="aspect-square w-full">
                                        @if($item->image)
                                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full items-center justify-center text-slate-300 dark:text-slate-700">
                                                <i class="fas fa-image text-4xl"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if($item->image)
                                    <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
                                        <input type="checkbox" name="remove_image" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                        Remover imagem atual
                                    </label>
                                @endif
                            </div>

                            <div class="space-y-6">
                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="md:col-span-2">
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Nome do item</label>
                                        <input type="text" name="name" value="{{ old('name', $item->name) }}" required
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3.5 text-base font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                            placeholder="Ex.: Mentoria Exclusiva (1h)">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Valor de referência</label>
                                        <input type="text" name="reference_value" id="reference_value" value="{{ $referenceValue }}" required
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3.5 text-base font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                            placeholder="0,00">
                                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                                            A pontuação é calculada automaticamente pela cotação definida pelo admin.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Custo em pontos</label>
                                        <input type="text" id="points_cost_preview"
                                            value="{{ number_format((int) old('points_cost', $item->points_cost ?? 0), 0, ',', '.') }} pts"
                                            readonly
                                            class="w-full rounded-2xl border border-blue-200 bg-blue-50 px-5 py-3.5 text-base font-black text-blue-700 outline-none dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-300">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Estoque</label>
                                        <input type="number" name="stock" value="{{ old('stock', $item->stock ?? -1) }}" required min="-1"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3.5 text-base font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                            placeholder="-1">
                                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Use <strong>-1</strong> para estoque ilimitado.</p>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Prazo estimado de entrega</label>
                                        <input type="number" name="delivery_lead_days" value="{{ old('delivery_lead_days', $item->delivery_lead_days ?? 7) }}" required min="1" max="365"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-3.5 text-base font-bold text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white"
                                            placeholder="7">
                                        <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Este prazo entra no acompanhamento e na saúde do membro vendedor.</p>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Imagem do produto</label>
                                        <input type="file" name="image" accept="image/png,image/jpeg,image/jpg"
                                            class="w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-4 text-sm font-semibold text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-white hover:file:bg-blue-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Descrição detalhada</label>
                        <textarea id="redemptionDescription" name="description" rows="5"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-slate-800 dark:bg-slate-950 dark:text-white">{{ old('description', $item->description) }}</textarea>
                        <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500">
                            Use formatação rica para explicar regras, prazos, logística e condições. Envio de imagens e arquivos segue bloqueado neste campo.
                        </p>
                    </section>
                </div>

                <div class="space-y-6">
                    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="inline-flex items-center gap-2 rounded-full border border-sky-100 bg-sky-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-sky-700 dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-300">
                            <i class="fas fa-user-shield"></i>
                            Responsável fixo
                        </div>
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Vendido/distribuído por</p>
                            <p class="mt-2 text-lg font-black text-slate-900 dark:text-white">{{ $providerLabel }}</p>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                Esta informação é travada no cadastro para preservar a responsabilidade sobre a entrega.
                            </p>
                        </div>
                    </section>

                    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-300">
                            <i class="fas fa-coins"></i>
                            Cotação vigente
                        </div>
                        <div class="mt-4 space-y-3">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Regra atual</p>
                                <p class="mt-2 text-lg font-black text-slate-900 dark:text-white">{{ number_format($basePoints, 0, ',', '.') }} pontos = R$ {{ number_format($baseAmount, 2, ',', '.') }}</p>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Cada ponto vale R$ {{ number_format($pointValue, 4, ',', '.') }}.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950">
                                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Como o sistema calcula</p>
                                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                    O vendedor acompanha esta cotação, informa o valor real do item e o sistema converte automaticamente para pontos.
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <label class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))
                                class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            Disponível para resgate
                        </label>
                        <button type="submit"
                            class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-xl shadow-blue-500/20 transition-all hover:bg-blue-700">
                            <i class="fas fa-check"></i>
                            {{ $item->exists ? 'Atualizar item' : 'Criar item' }}
                        </button>
                    </section>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                const $descriptionField = $('#redemptionDescription');

                if (window.jQuery && $.fn && $.fn.summernote && $descriptionField.length && !$descriptionField.next('.note-editor').length) {
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
                }

                const referenceInput = document.getElementById('reference_value');
                const pointsPreview = document.getElementById('points_cost_preview');
                const basePoints = {{ $basePoints }};
                const baseAmount = {{ number_format($baseAmount, 2, '.', '') }};

                const parseMoney = (value) => {
                    let normalized = String(value || '').replace(/[R$\s]/g, '');
                    if (normalized.includes(',')) {
                        normalized = normalized.replace(/\./g, '').replace(',', '.');
                    }
                    return Math.max(0, parseFloat(normalized || '0') || 0);
                };

                const formatPoints = (value) => new Intl.NumberFormat('pt-BR').format(value) + ' pts';

                const refreshPoints = () => {
                    if (!referenceInput || !pointsPreview) {
                        return;
                    }

                    const money = parseMoney(referenceInput.value);
                    const points = money > 0 && baseAmount > 0 ? Math.ceil((money / baseAmount) * basePoints) : 0;
                    pointsPreview.value = formatPoints(points);
                };

                if (referenceInput) {
                    referenceInput.addEventListener('input', refreshPoints);
                    referenceInput.addEventListener('change', refreshPoints);
                    refreshPoints();
                }
            });
        </script>
    @endpush
@endsection
