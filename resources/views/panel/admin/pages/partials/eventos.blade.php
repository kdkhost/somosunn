{{-- Partial: eventos (Tailwind Version) --}}

<!-- Hero Section -->
<section id="sec-hero"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                <i class="fas fa-calendar-alt text-sm"></i>
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
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Texto do Badge <small
                    class="text-slate-400 font-normal italic">(Etiqueta acima do título)</small></label>
            <input type="text" name="hero_badge" value="{{ old('hero_badge', $data['hero_badge'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest"
                placeholder="Em destaque">
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título Principal</label>
            <input type="text" name="hero_title" value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-black uppercase tracking-tight"
                placeholder="Próximo Evento UNN">
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo / Descrição</label>
            <textarea name="hero_subtitle" rows="3"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium leading-relaxed"
                placeholder="Não perca a oportunidade de expandir sua rede…">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
        </div>
        <div class="space-y-3 pt-4">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 italic">Imagem do Hero <small
                    class="text-slate-400 font-normal">(Fundo padrão)</small></label>
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

<!-- Final CTA Section -->
<section id="sec-cta"
    class="bg-amber-500 rounded-[2.5rem] border-none shadow-xl shadow-amber-500/20 overflow-hidden scroll-mt-24">
    <div class="p-8 md:p-12 space-y-8 relative">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div
            class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6 text-white text-center md:text-left">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 text-xl">
                    <i class="fas fa-bullhorn font-black italic"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black uppercase tracking-widest italic leading-tight">CTA Final</h2>
                    <p class="text-amber-50 opacity-80 text-sm font-medium italic">Incentive a visualização de todos os
                        eventos.</p>
                </div>
            </div>
            <div
                class="flex items-center gap-3 px-5 py-2.5 bg-amber-600/50 backdrop-blur-md rounded-2xl border border-white/10 shadow-lg mx-auto md:mx-0">
                <span class="text-xs font-black text-white uppercase tracking-widest italic">Exibir CTA</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer section-toggle" data-section="cta" {{ ($data['cta_enabled'] ?? true) ? 'checked' : '' }}>
                    <div
                        class="w-11 h-6 bg-amber-700/50 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-amber-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-white after:peer-checked:bg-amber-600 after:peer-checked:border-none">
                    </div>
                </label>
            </div>
        </div>

        <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-8 text-white">
            <div class="space-y-4">
                <div class="space-y-1">
                    <label class="text-[11px] font-black uppercase tracking-widest opacity-80 px-1 italic">Título de
                        Destaque</label>
                    <input type="text" name="cta_title" value="{{ old('cta_title', $data['cta_title'] ?? '') }}"
                        class="w-full bg-amber-600/40 border-amber-500/30 text-white placeholder:text-amber-200/40 rounded-2xl px-5 py-3.5 text-lg font-black italic focus:ring-2 focus:ring-white transition-all shadow-inner"
                        placeholder="Não perca os próximos eventos">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-black uppercase tracking-widest opacity-80 px-1 italic">Subtítulo /
                        Descrição</label>
                    <textarea name="cta_subtitle" rows="3"
                        class="w-full bg-amber-600/40 border-amber-500/30 text-white rounded-2xl px-5 py-3 text-sm font-medium focus:ring-2 focus:ring-white transition-all shadow-inner leading-relaxed">{{ old('cta_subtitle', $data['cta_subtitle'] ?? '') }}</textarea>
                </div>
            </div>
            <div class="flex flex-col justify-end space-y-4">
                <div class="space-y-1">
                    <label class="text-[11px] font-black uppercase tracking-widest opacity-80 px-1 italic">Texto do
                        Botão</label>
                    <input type="text" name="cta_btn" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}"
                        class="w-full bg-white text-amber-700 border-none rounded-2xl px-6 py-4 text-base font-black uppercase tracking-widest focus:ring-4 focus:ring-white/20 transition-all shadow-xl shadow-amber-900/40"
                        placeholder="Ver todos os eventos">
                </div>
            </div>
        </div>
    </div>
</section>