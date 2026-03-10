{{-- Partial: premium (Tailwind Version) --}}

<!-- Hero Section -->
<section id="sec-hero"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shadow-lg shadow-red-500/20">
                <i class="fas fa-crown text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Hero / Cabeçalho</h2>
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
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Badge do Hero</label>
            <input type="text" name="hero_badge" value="{{ old('hero_badge', $data['hero_badge'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all font-bold text-red-600 dark:text-red-400 uppercase tracking-widest"
                placeholder="Associação Premium">
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título Principal</label>
            <input type="text" name="hero_title" value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all font-black uppercase tracking-tight"
                placeholder="Invista no seu crescimento">
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo <small
                    class="text-slate-400 font-normal italic">(Suporta HTML básico)</small></label>
            <textarea name="hero_subtitle" rows="3"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-red-500 transition-all font-medium leading-relaxed"
                placeholder="Descrição principal...">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-widest text-slate-400 px-1">Selo de Confiança
                    1</label>
                <input type="text" name="hero_trust_1" value="{{ old('hero_trust_1', $data['hero_trust_1'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all"
                    placeholder="Sem fidelidade">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-widest text-slate-400 px-1">Selo de Confiança
                    2</label>
                <input type="text" name="hero_trust_2" value="{{ old('hero_trust_2', $data['hero_trust_2'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all"
                    placeholder="Cancele quando quiser">
            </div>
        </div>
        <div class="space-y-3 pt-4">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 italic">Imagem Hero <small
                    class="text-slate-400 font-normal">(Fundo/Highlight)</small></label>
            <input type="file" name="hero_image" accept="image/*" class="filepond">
            @if (!empty($data['hero_image']))
                <div
                    class="flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/10 rounded-xl border border-red-100 dark:border-red-900/20 w-fit">
                    <input type="checkbox" id="remove_hero_image" name="remove_hero_image" value="1"
                        class="rounded text-red-600 focus:ring-red-500">
                    <label for="remove_hero_image" class="text-xs font-bold text-red-600 dark:text-red-400">Remover imagem
                        atual</label>
                </div>
                <div
                    class="mt-2 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-800 shadow-sm max-w-sm">
                    <img src="{{ Storage::url($data['hero_image']) }}" class="w-full h-auto">
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Plans Section -->
<section id="sec-planos"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/20">
                <i class="fas fa-tags text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Seção de Planos</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="plans" {{ ($data['plans_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título da Seção</label>
                <input type="text" name="plans_title" value="{{ old('plans_title', $data['plans_title'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-bold focus:ring-2 focus:ring-amber-500 transition-all"
                    placeholder="Escolha seu plano">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo da Seção</label>
                <input type="text" name="plans_subtitle"
                    value="{{ old('plans_subtitle', $data['plans_subtitle'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-medium focus:ring-2 focus:ring-amber-500 transition-all"
                    placeholder="Sem taxa de adesao. Cancele quando quiser.">
            </div>
        </div>
    </div>
</section>