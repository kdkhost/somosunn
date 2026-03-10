{{-- Partial: sobre (Tailwind Version) --}}

<!-- Hero Section -->
<section id="sec-hero"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                <i class="fas fa-heading text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Hero Section</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="hero" {{ ($data['hero_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título Principal</label>
            <input type="text" name="hero_title" value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                placeholder="Somos a ponte entre quem quer crescer e quem já chegou lá.">
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Missão / Visão <small
                    class="text-slate-400 font-normal italic">(parágrafo de abertura)</small></label>
            <textarea name="vision" rows="4"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                placeholder="Texto descritivo da missão da UNN.">{{ old('vision', $data['vision'] ?? '') }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Botão Principal</label>
                <input type="text" name="cta_btn_primary"
                    value="{{ old('cta_btn_primary', $data['cta_btn_primary'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                    placeholder="Fazer parte">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Botão Secundário</label>
                <input type="text" name="cta_btn_secondary"
                    value="{{ old('cta_btn_secondary', $data['cta_btn_secondary'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                    placeholder="Conhecer a equipe">
            </div>
        </div>
        <div class="space-y-3 pt-4">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 italic">Imagem do Hero (Aparece ao
                lado das estatísticas)</label>
            <input type="file" name="hero_image" accept="image/*" class="filepond">
            @if (!empty($data['hero_image']))
                <div
                    class="flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/10 rounded-xl border border-red-100 dark:border-red-900/20 w-fit">
                    <input type="checkbox" id="remove_hero_image" name="remove_hero_image" value="1"
                        class="rounded text-red-600 focus:ring-red-500">
                    <label for="remove_hero_image" class="text-xs font-bold text-red-600 dark:text-red-400">Remover imagem
                        atual</label>
                </div>
                <div class="mt-2 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-800 w-48 shadow-sm">
                    <img src="{{ Storage::url($data['hero_image']) }}" class="w-full h-auto">
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Stats Section -->
<section id="sec-estatisticas"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-cyan-600 text-white flex items-center justify-center shadow-lg shadow-cyan-500/20">
                <i class="fas fa-chart-bar text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Estatísticas</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="stats" {{ ($data['stats_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
        @foreach ([1, 2, 3, 4] as $i)
            <div
                class="grid grid-cols-1 gap-4 p-4 rounded-3xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50">
                <div class="space-y-2">
                    <label
                        class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Número
                        {{ $i }}</label>
                    <input type="text" name="stat_{{ $i }}_value"
                        value="{{ old('stat_' . $i . '_value', $data['stat_' . $i . '_value'] ?? '') }}"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-bold"
                        placeholder="5.000+">
                </div>
                <div class="space-y-2">
                    <label
                        class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Legenda
                        {{ $i }}</label>
                    <input type="text" name="stat_{{ $i }}_label"
                        value="{{ old('stat_' . $i . '_label', $data['stat_' . $i . '_label'] ?? '') }}"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                        placeholder="Membros ativos">
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- History Section -->
<section id="sec-historia"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-slate-700 text-white flex items-center justify-center shadow-lg shadow-slate-600/20">
                <i class="fas fa-book-open text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Nossa História</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="history" {{ ($data['history_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título da Seção</label>
            <input type="text" name="history_title" value="{{ old('history_title', $data['history_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-bold"
                placeholder="Nossa História">
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Destaque <small
                    class="text-slate-400 font-normal italic">(frase de impacto inicial)</small></label>
            <textarea name="history_lead" rows="2"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">{{ old('history_lead', $data['history_lead'] ?? '') }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Parágrafo 1</label>
                <textarea name="history_p1" rows="6"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">{{ old('history_p1', $data['history_p1'] ?? '') }}</textarea>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Parágrafo 2</label>
                <textarea name="history_p2" rows="6"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">{{ old('history_p2', $data['history_p2'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
</section>

<!-- Differentials Section -->
<section id="sec-diferenciais"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <i class="fas fa-star text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Diferenciais</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="diff" {{ ($data['diff_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-8">
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título da Seção</label>
            <input type="text" name="diff_title" value="{{ old('diff_title', $data['diff_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-bold"
                placeholder="Por que a UNN é diferente">
        </div>

        <div class="grid grid-cols-1 gap-6">
            @foreach ([1, 2, 3] as $i)
                <div
                    class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50">
                    <div class="md:col-span-1 space-y-2">
                        <label
                            class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Título
                            do Card {{ $i }}</label>
                        <input type="text" name="diff_card_{{ $i }}_title"
                            value="{{ old('diff_card_' . $i . '_title', $data['diff_card_' . $i . '_title'] ?? '') }}"
                            class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label
                            class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Texto
                            Descritivo {{ $i }}</label>
                        <textarea name="diff_card_{{ $i }}_text" rows="2"
                            class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500 transition-all">{{ old('diff_card_' . $i . '_text', $data['diff_card_' . $i . '_text'] ?? '') }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA Final Section -->
<section id="sec-cta"
    class="bg-emerald-600 rounded-[2.5rem] border-none shadow-xl shadow-emerald-500/20 overflow-hidden scroll-mt-24">
    <div class="p-8 md:p-12 space-y-8 relative">
        <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none">
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6 text-white">
            <div class="flex items-center gap-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 text-xl">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black uppercase tracking-widest italic leading-tight">CTA Final</h2>
                    <p class="text-emerald-50 opacity-80 text-sm font-medium">Incentive o usuário a tomar o próximo
                        passo.</p>
                </div>
            </div>
            <div
                class="flex items-center gap-3 px-5 py-2.5 bg-emerald-700/50 backdrop-blur-md rounded-2xl border border-white/10 shadow-lg">
                <span class="text-xs font-black text-white uppercase tracking-widest">Exibir CTA</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer section-toggle" data-section="cta" {{ ($data['cta_enabled'] ?? true) ? 'checked' : '' }}>
                    <div
                        class="w-11 h-6 bg-emerald-800/50 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-emerald-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-white after:peer-checked:bg-emerald-600 after:peer-checked:border-none">
                    </div>
                </label>
            </div>
        </div>

        <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-8 text-white">
            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-[11px] font-black uppercase tracking-widest opacity-80 px-1">Título
                        Principal</label>
                    <input type="text" name="cta_title" value="{{ old('cta_title', $data['cta_title'] ?? '') }}"
                        class="w-full bg-emerald-700/40 border-emerald-500/30 text-white placeholder:text-emerald-200/40 rounded-2xl px-5 py-3.5 text-lg font-black italic focus:ring-2 focus:ring-white transition-all shadow-inner"
                        placeholder="Faça parte da nossa história">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-black uppercase tracking-widest opacity-80 px-1">Subtítulo</label>
                    <input type="text" name="cta_subtitle"
                        value="{{ old('cta_subtitle', $data['cta_subtitle'] ?? '') }}"
                        class="w-full bg-emerald-700/40 border-emerald-500/30 text-white placeholder:text-emerald-200/40 rounded-2xl px-5 py-3.5 text-sm font-medium focus:ring-2 focus:ring-white transition-all shadow-inner">
                </div>
            </div>
            <div class="flex flex-col justify-end space-y-4">
                <div class="space-y-1">
                    <label class="text-[11px] font-black uppercase tracking-widest opacity-80 px-1 italic">Texto do
                        botão</label>
                    <input type="text" name="cta_btn" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}"
                        class="w-full bg-white text-emerald-700 border-none rounded-2xl px-6 py-4 text-base font-black uppercase tracking-widest focus:ring-4 focus:ring-white/20 transition-all shadow-xl shadow-emerald-900/40"
                        placeholder="Quero fazer parte">
                </div>
            </div>
        </div>
    </div>
</section>