@extends('panel.layouts.app')

@section('title', ($coupon->id ? 'Editar' : 'Novo') . ' Cupom')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400 transition-colors">
                <a href="{{ route('panel.admin.coupons.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Cupons</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-900 dark:text-white font-medium transition-colors">{{ $coupon->id ? 'Editar Cupom' : 'Novo Cupom' }}</span>
            </div>
        </div>

        <form action="{{ $coupon->id ? route('panel.admin.coupons.update', $coupon) : route('panel.admin.coupons.store') }}"
            method="POST" class="space-y-6">
            @csrf
            @if($coupon->id)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Form --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Código e Valor --}}
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-6 transition-colors duration-300">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 transition-colors">
                            <i class="fas fa-ticket-alt text-slate-400 dark:text-slate-500"></i>
                            Identificação e Desconto
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1.5 md:col-span-2">
                                <label for="code" class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Código do Cupom</label>
                                <div class="relative">
                                    <input type="text" name="code" id="code" value="{{ old('code', $coupon->code) }}"
                                        placeholder="EX: BLACKFRIDAY2026"
                                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-bold uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm placeholder-slate-300 dark:placeholder-slate-700 text-slate-900 dark:text-white">
                                    <button type="button" id="btnGenCode"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-[10px] font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 dark:hover:text-blue-400 transition-all shadow-sm">
                                        GERAR
                                    </button>
                                </div>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 transition-colors">O código será salvo automaticamente em maiúsculo
                                    e sem espaços.</p>
                                @error('code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="discount_type" class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Tipo de Desconto</label>
                                <select name="discount_type" id="discount_type"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm text-slate-900 dark:text-white">
                                    <option value="percent" {{ old('discount_type', $coupon->discount_type) == 'percent' ? 'selected' : '' }}>Percentual (%)</option>
                                    <option value="fixed" {{ old('discount_type', $coupon->discount_type) == 'fixed' ? 'selected' : '' }}>Valor Fixo (R$)</option>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label for="discount_value" class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Valor</label>
                                <input type="number" step="0.01" name="discount_value" id="discount_value"
                                    value="{{ old('discount_value', $coupon->discount_value) }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm text-slate-900 dark:text-white"
                                    placeholder="0.00">
                                @error('discount_value') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Regras e Escopo --}}
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-6 transition-colors duration-300">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 transition-colors">
                            <i class="fas fa-filter text-slate-400 dark:text-slate-500"></i>
                            Regras de Aplicação
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1.5">
                                <label for="applies_to" class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Onde aplicar?</label>
                                <select name="applies_to" id="applies_to"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm text-slate-900 dark:text-white">
                                    <option value="all" {{ old('applies_to', $coupon->applies_to ?? 'all') == 'all' ? 'selected' : '' }}>Geral (Toda a plataforma)</option>
                                    <option value="event" {{ old('applies_to', $coupon->applies_to) == 'event' ? 'selected' : '' }}>Somente Eventos</option>
                                    <option value="course" {{ old('applies_to', $coupon->applies_to) == 'course' ? 'selected' : '' }}>Somente Cursos</option>
                                    <option value="mentorship" {{ old('applies_to', $coupon->applies_to) == 'mentorship' ? 'selected' : '' }}>Somente Mentorias</option>
                                </select>
                            </div>

                            <div class="space-y-1.5" id="applies_to_id_wrap"
                                style="display: {{ (old('applies_to', $coupon->applies_to) && old('applies_to', $coupon->applies_to) !== 'all') ? 'block' : 'none' }};">
                                <label for="applies_to_id" class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Item Específico
                                    (Opcional)</label>
                                <select name="applies_to_id" id="applies_to_id"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm text-slate-900 dark:text-white">
                                    <option value="">Todos do escopo selecionado</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" data-scope="event" {{ (string) old('applies_to_id', $coupon->applies_to_id) === (string) $event->id ? 'selected' : '' }}>
                                            Evento: {{ $event->title }}
                                        </option>
                                    @endforeach
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" data-scope="course" {{ (string) old('applies_to_id', $coupon->applies_to_id) === (string) $course->id ? 'selected' : '' }}>
                                            Curso: {{ $course->title }}
                                        </option>
                                    @endforeach
                                    @foreach($mentorships as $mentorship)
                                        <option value="{{ $mentorship->id }}" data-scope="mentorship" {{ (string) old('applies_to_id', $coupon->applies_to_id) === (string) $mentorship->id ? 'selected' : '' }}>
                                            Mentoria: {{ $mentorship->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label for="min_amount" class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Valor Mínimo do
                                    Pedido</label>
                                <input type="number" step="0.01" name="min_amount" id="min_amount"
                                    value="{{ old('min_amount', $coupon->min_amount) }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm text-slate-900 dark:text-white"
                                    placeholder="0.00">
                            </div>

                            <div class="space-y-1.5">
                                <label for="max_uses" class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Limite de Usos
                                    (Total)</label>
                                <input type="number" name="max_uses" id="max_uses"
                                    value="{{ old('max_uses', $coupon->max_uses) }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm text-slate-900 dark:text-white"
                                    placeholder="Ilimitado">
                            </div>

                            <div class="space-y-1.5">
                                <label for="max_uses_per_user" class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Limite por
                                    Usuário</label>
                                <input type="number" name="max_uses_per_user" id="max_uses_per_user"
                                    value="{{ old('max_uses_per_user', $coupon->max_uses_per_user) }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm text-slate-900 dark:text-white"
                                    placeholder="Ilimitado">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Status & Validade --}}
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 space-y-6 sticky top-24 transition-colors duration-300">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white border-b border-slate-50 dark:border-slate-800 pb-4 uppercase tracking-wider transition-colors">
                            Publicação</h3>

                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label for="is_active" class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Status</label>
                                <select name="is_active" id="is_active"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm text-slate-900 dark:text-white">
                                    <option value="1" {{ old('is_active', $coupon->is_active ?? 1) == 1 ? 'selected' : '' }}>
                                        Ativo</option>
                                    <option value="0" {{ old('is_active', $coupon->is_active ?? 1) == 0 ? 'selected' : '' }}>
                                        Inativo</option>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label for="starts_at" class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Data de Início</label>
                                <input type="datetime-local" name="starts_at" id="starts_at"
                                    value="{{ old('starts_at', $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-900 dark:text-white">
                            </div>

                            <div class="space-y-1.5">
                                <label for="ends_at" class="text-sm font-bold text-slate-700 dark:text-slate-300 transition-colors">Data de Término</label>
                                <input type="datetime-local" name="ends_at" id="ends_at"
                                    value="{{ old('ends_at', $coupon->ends_at ? $coupon->ends_at->format('Y-m-d\TH:i') : '') }}"
                                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-900 dark:text-white">
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex flex-col gap-3 transition-colors">
                            <button type="submit"
                                class="w-full px-4 py-3 bg-blue-600 text-white rounded-xl text-sm font-black hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30 transform hover:scale-[1.02]">
                                {{ $coupon->id ? 'Salvar Alterações' : 'Criar Cupom' }}
                            </button>
                            <a href="{{ route('panel.admin.coupons.index') }}"
                                class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-bold text-center hover:bg-slate-50 dark:hover:bg-slate-700 transition-all transition-colors">
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const appliesTo = document.getElementById('applies_to');
                const appliesToIdWrap = document.getElementById('applies_to_id_wrap');
                const appliesToId = document.getElementById('applies_to_id');
                const btnGenCode = document.getElementById('btnGenCode');
                const codeInput = document.getElementById('code');

                const updateScope = () => {
                    const scope = appliesTo.value;
                    if (scope === 'all') {
                        appliesToIdWrap.style.display = 'none';
                        appliesToId.value = '';
                    } else {
                        appliesToIdWrap.style.display = 'block';
                        // Filter options
                        Array.from(appliesToId.options).forEach(opt => {
                            if (!opt.value) {
                                opt.style.display = 'block';
                                return;
                            }
                            if (opt.dataset.scope === scope) {
                                opt.style.display = 'block';
                            } else {
                                opt.style.display = 'none';
                            }
                        });

                        // If current selected option is hidden, reset it
                        const currentOpt = appliesToId.options[appliesToId.selectedIndex];
                        if (currentOpt.value && currentOpt.dataset.scope !== scope) {
                            appliesToId.value = '';
                        }
                    }
                };

                appliesTo.addEventListener('change', updateScope);
                updateScope();

                btnGenCode.addEventListener('click', () => {
                    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                    let code = '';
                    for (let i = 0; i < 10; i++) {
                        code += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    codeInput.value = code;
                });
            });
        </script>
    @endpush
@endsection