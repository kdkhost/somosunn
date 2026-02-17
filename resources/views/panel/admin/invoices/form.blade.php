@extends('panel.layouts.app')

@section('title', $invoice->id ? 'Editar Fatura' : 'Nova Fatura')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400 transition-colors">
                <a href="{{ route('panel.admin.invoices.index') }}"
                    class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Faturas</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span
                    class="text-slate-900 dark:text-white font-medium transition-colors">{{ $invoice->id ? 'Editar Fatura' : 'Nova Fatura' }}</span>
            </div>
        </div>

        <form
            action="{{ $invoice->id ? route('panel.admin.invoices.update', $invoice) : route('panel.admin.invoices.store') }}"
            method="POST" class="space-y-6">
            @csrf
            @if($invoice->id)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Form Column --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Client & General --}}
                    <div
                        class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-6 transition-colors duration-300">
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 transition-colors">
                            <i class="fas fa-user-circle text-slate-400 dark:text-slate-500"></i>
                            Informações Gerais
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Select Cliente --}}
                            <div class="space-y-1.5 md:col-span-2">
                                <label for="user_id"
                                    class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Cliente</label>
                                <select name="user_id" id="user_id"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm select2">
                                    <option value="">Selecione o cliente...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ (old('user_id', $invoice->user_id) == $user->id) ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="issued_at"
                                    class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Data de
                                    Emissão</label>
                                <input type="date" name="issued_at" id="issued_at"
                                    value="{{ old('issued_at', $invoice->issued_at ? $invoice->issued_at->format('Y-m-d') : now()->format('Y-m-d')) }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm">
                            </div>

                            <div class="space-y-1.5">
                                <label for="due_at"
                                    class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Data de
                                    Vencimento</label>
                                <input type="date" name="due_at" id="due_at"
                                    value="{{ old('due_at', $invoice->due_at ? $invoice->due_at->format('Y-m-d') : '') }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div
                        class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
                        <div
                            class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            <h3
                                class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 transition-colors">
                                <i class="fas fa-list-ul text-slate-400 dark:text-slate-500"></i>
                                Itens da Fatura
                            </h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full" id="itemsTable">
                                <thead>
                                    <tr
                                        class="bg-slate-50/50 dark:bg-slate-950 text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 tracking-wider transition-colors">
                                        <th class="px-6 py-3 text-left">Descrição</th>
                                        <th class="px-6 py-3 text-center w-24">Qtd.</th>
                                        <th class="px-6 py-3 text-right w-36">Preço Unit.</th>
                                        <th class="px-6 py-3 text-right w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($rows as $idx => $row)
                                        <tr
                                            class="item-row group hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <input type="text" name="items_description[]" value="{{ $row['description'] }}"
                                                    class="w-full bg-transparent border-none p-0 text-sm font-medium text-slate-900 dark:text-white focus:ring-0 placeholder-slate-300 dark:placeholder-slate-600 transition-colors"
                                                    placeholder="Descrição do item...">
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="number" name="items_quantity[]" value="{{ $row['quantity'] }}"
                                                    min="1"
                                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-700 rounded-lg text-center py-1.5 text-sm text-slate-800 dark:text-white transition-colors">
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="text" name="items_unit_price[]" value="{{ $row['unit_price'] }}"
                                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-700 rounded-lg text-right py-1.5 px-3 text-sm font-mono text-slate-800 dark:text-white money transition-colors">
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <button type="button"
                                                    class="remove-row text-slate-300 dark:text-slate-600 hover:text-red-500 dark:hover:text-red-400 transition-colors opacity-0 group-hover:opacity-100">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div
                            class="p-4 border-t border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/30 transition-colors">
                            <button type="button" id="addRow"
                                class="inline-flex items-center gap-2 text-sm font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                <i class="fas fa-plus-circle text-lg"></i>
                                Adicionar Item
                            </button>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div
                        class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-4 transition-colors duration-300">
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 transition-colors">
                            <i class="fas fa-sticky-note text-slate-400 dark:text-slate-500"></i>
                            Observações
                        </h3>
                        <textarea name="notes" rows="4"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm placeholder-slate-400 dark:placeholder-slate-600"
                            placeholder="Notas internas ou informações para o cliente...">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>
                </div>

                {{-- Sidebar Form Column --}}
                <div class="space-y-6">
                    <div
                        class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-6 sticky top-24 transition-colors duration-300">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white transition-colors">Configurações</h3>

                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label for="status"
                                    class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Status da
                                    Fatura</label>
                                <select name="status" id="status"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm">
                                    <option value="draft" {{ old('status', $invoice->status) == 'draft' ? 'selected' : '' }}>
                                        Rascunho</option>
                                    <option value="issued" {{ old('status', $invoice->status) == 'issued' ? 'selected' : '' }}>Emitida</option>
                                    <option value="paid" {{ old('status', $invoice->status) == 'paid' ? 'selected' : '' }}>
                                        Paga</option>
                                    <option value="cancelled" {{ old('status', $invoice->status) == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                                @error('status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="discount_amount"
                                    class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Desconto
                                    Global</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-sm font-mono transition-colors">R$</span>
                                    <input type="text" name="discount_amount" id="discount_amount"
                                        value="{{ old('discount_amount', $invoice->discount_amount) }}" placeholder="0,00"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl pl-12 pr-4 py-2.5 text-sm font-mono text-right text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm money">
                                </div>
                            </div>

                            <div class="pt-4 flex flex-col gap-3">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="send_email" value="1"
                                        class="w-5 h-5 rounded-md border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-blue-600 focus:ring-blue-500/20 transition-all">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Enviar
                                            por e-mail</span>
                                        <span
                                            class="text-[10px] text-slate-500 dark:text-slate-500 transition-colors">Notificar
                                            cliente após salvar</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div
                            class="pt-6 border-t border-slate-100 dark:border-slate-800 flex flex-col gap-3 transition-colors">
                            <button type="submit"
                                class="w-full px-4 py-3 bg-blue-600 text-white rounded-xl text-sm font-black hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30 transform hover:scale-[1.02]">
                                {{ $invoice->id ? 'Atualizar Fatura' : 'Criar Fatura' }}
                            </button>
                            <a href="{{ route('panel.admin.invoices.index') }}"
                                class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl text-sm font-bold text-center hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

        <script>
            $(document).ready(function () {
                $('.select2').select2({
                    width: '100%',
                    language: 'pt-BR'
                });

                $('.money').mask('#.##0,00', { reverse: true });

                $('#addRow').on('click', function () {
                    const row = `
                            <tr class="item-row group hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <input type="text" name="items_description[]" class="w-full bg-transparent border-none p-0 text-sm font-medium text-slate-900 dark:text-white focus:ring-0 placeholder-slate-300 dark:placeholder-slate-600 transition-colors" placeholder="Descrição do item...">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" name="items_quantity[]" value="1" min="1" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-700 rounded-lg text-center py-1.5 text-sm text-slate-800 dark:text-white transition-colors">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" name="items_unit_price[]" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-700 rounded-lg text-right py-1.5 px-3 text-sm font-mono text-slate-800 dark:text-white money transition-colors">
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button type="button" class="remove-row text-slate-300 dark:text-slate-600 hover:text-red-500 dark:hover:text-red-400 transition-colors opacity-0 group-hover:opacity-100">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    $('#itemsTable tbody').append(row);
                    $('.money').mask('#.##0,00', { reverse: true });
                });

                $(document).on('click', '.remove-row', function () {
                    if ($('#itemsTable tbody tr').length > 1) {
                        $(this).closest('tr').remove();
                    } else {
                        alert('A fatura deve ter pelo menos um item.');
                    }
                });
            });
        </script>

        <style>
            .select2-container--default .select2-selection--single {
                height: 42px;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                background-color: #f8fafc;
                transition: all 0.2s;
            }

            .dark .select2-container--default .select2-selection--single {
                background-color: #020617;
                border-color: #1e293b;
                color: #f8fafc;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 42px;
                padding-left: 12px;
                font-size: 0.875rem;
                color: #1e293b;
            }

            .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #f8fafc;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 42px;
            }

            .dark .select2-dropdown {
                background-color: #0f172a;
                border-color: #1e293b;
                color: #f8fafc;
            }

            .dark .select2-results__option--selectable {
                color: #94a3b8;
            }

            .dark .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
                background-color: #1e293b;
                color: #f8fafc;
            }

            .dark .select2-search--dropdown .select2-search__field {
                background-color: #020617;
                border-color: #1e293b;
                color: #f8fafc;
                border-radius: 8px;
            }
        </style>
    @endpush
@endsection