{{-- Partial: valores (Tailwind Version) --}}

<!-- Header Section -->
<section id="sec-cabecalho"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                <i class="fas fa-heart text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Cabeçalho</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="header" {{ ($data['header_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo do Hero</label>
            <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                placeholder="Os princípios que guiam tudo o que fazemos na UNN.">
        </div>
    </div>
</section>

<!-- Values Section (JSON) -->
<section id="sec-valores"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-slate-800 text-white flex items-center justify-center shadow-lg shadow-slate-900/20">
                <i class="fas fa-list-ul text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Os 6 Valores
                fundamentais</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="values" {{ ($data['values_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 px-1">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400">Configuração dos Valores (Editor
                JSON)</label>
            <span
                class="text-[10px] font-black uppercase text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded italic">Array
                de 6 objetos</span>
        </div>

        <div
            class="p-4 bg-slate-50 dark:bg-slate-800/20 rounded-2xl border border-slate-100 dark:border-slate-800/50 space-y-2 mb-4">
            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                Cada objeto deve conter: <code class="text-blue-600 dark:text-blue-400">icon</code> (ex: fa-heart),
                <code class="text-blue-600 dark:text-blue-400">title</code>,
                <code class="text-blue-600 dark:text-blue-400">text</code> e
                <code class="text-blue-600 dark:text-blue-400">quote</code>.
            </p>
        </div>

        <div class="group relative">
            <textarea name="values_json" rows="15"
                class="w-full bg-slate-950 text-blue-400 font-mono text-xs border-slate-800 rounded-2xl px-6 py-4 focus:ring-2 focus:ring-blue-500/50 transition-all leading-relaxed shadow-inner"
                style="font-family: 'Fira Code', 'Courier New', monospace;">{{ old('values_json', json_encode($data['values'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
            <div class="absolute top-2 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                <i class="fas fa-terminal text-slate-700"></i>
            </div>
        </div>

        @error('values_json')
            <div class="text-red-500 text-xs font-bold px-2 flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
            </div>
        @enderror
    </div>
</section>

<!-- Quote Section -->
<section id="sec-citacao"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/20">
                <i class="fas fa-quote-left text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Citação Central</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="quote" {{ ($data['quote_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="space-y-4">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Texto da Citação</label>
                <textarea name="blockquote_text" rows="3"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-8 py-8 text-xl font-serif italic text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 transition-all leading-relaxed text-center"
                    placeholder="Valores não são apenas palavras bonitas na parede…">{{ old('blockquote_text', $data['blockquote_text'] ?? '') }}</textarea>
            </div>
            <div class="space-y-2 w-full md:w-1/2 mx-auto">
                <label
                    class="text-[10px] font-black uppercase tracking-widest text-slate-400 text-center block mb-1">Autor
                    / Origem</label>
                <input type="text" name="blockquote_author"
                    value="{{ old('blockquote_author', $data['blockquote_author'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-black text-center text-blue-600 dark:text-blue-400"
                    placeholder="— Equipe Fundadora UNN">
            </div>
        </div>
    </div>
</section>

<!-- Final CTA Section -->
<section id="sec-cta"
    class="bg-emerald-600 rounded-[2.5rem] border-none shadow-xl shadow-emerald-500/20 overflow-hidden scroll-mt-24">
    <div class="p-8 md:p-12 space-y-8 relative">
        <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none">
        </div>
        <div
            class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6 text-white text-center md:text-left">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 text-xl">
                    <i class="fas fa-bullhorn font-black italic"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black uppercase tracking-widest italic leading-tight">Chamada Final</h2>
                    <p class="text-emerald-50 opacity-80 text-sm font-medium italic">Encerre a página com um convite aos
                        valores.</p>
                </div>
            </div>
            <div
                class="flex items-center gap-3 px-5 py-2.5 bg-emerald-700/50 backdrop-blur-md rounded-2xl border border-white/10 shadow-lg mx-auto md:mx-0">
                <span class="text-xs font-black text-white uppercase tracking-widest italic">Exibir CTA</span>
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
                    <label class="text-[11px] font-black uppercase tracking-widest opacity-80 px-1 italic">Título de
                        Impacto</label>
                    <input type="text" name="cta_title" value="{{ old('cta_title', $data['cta_title'] ?? '') }}"
                        class="w-full bg-emerald-700/40 border-emerald-500/30 text-white placeholder:text-emerald-200/40 rounded-2xl px-5 py-3.5 text-lg font-black italic focus:ring-2 focus:ring-white transition-all shadow-inner"
                        placeholder="Compartilha desses valores?">
                </div>
                <div class="space-y-1">
                    <label
                        class="text-[11px] font-black uppercase tracking-widest opacity-80 px-1 italic">Subtítulo</label>
                    <input type="text" name="cta_subtitle"
                        value="{{ old('cta_subtitle', $data['cta_subtitle'] ?? '') }}"
                        class="w-full bg-emerald-700/40 border-emerald-500/30 text-white rounded-2xl px-5 py-3.5 text-sm font-medium focus:ring-2 focus:ring-white transition-all shadow-inner">
                </div>
            </div>
            <div class="flex flex-col justify-end space-y-4">
                <div class="space-y-1">
                    <label class="text-[11px] font-black uppercase tracking-widest opacity-80 px-1 italic">Texto do
                        Botão CTA</label>
                    <input type="text" name="cta_btn" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}"
                        class="w-full bg-white text-emerald-700 border-none rounded-2xl px-6 py-4 text-base font-black uppercase tracking-widest focus:ring-4 focus:ring-white/20 transition-all shadow-xl shadow-emerald-900/40"
                        placeholder="Fazer parte">
                </div>
            </div>
        </div>
    </div>
</section>