{{-- Partial: portal (Tailwind Version) --}}

<!-- Hero Section -->
<section id="sec-hero"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                <i class="fas fa-home text-sm"></i>
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
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título Principal</label>
            <input type="text" name="hero_title" value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-black uppercase tracking-tight"
                placeholder="Portal de Networking">
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo <small
                    class="text-slate-400 font-normal italic">(Suporta HTML básico)</small></label>
            <textarea name="hero_subtitle" rows="3"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium leading-relaxed"
                placeholder="Descrição do portal...">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
        </div>
        <div class="space-y-3 pt-4">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 italic">Imagem Hero <small
                    class="text-slate-400 font-normal">(Banner Principal)</small></label>
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
                    <label class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Valor
                        {{ $i }}</label>
                    <input type="text" name="stat_{{ $i }}_value"
                        value="{{ old('stat_' . $i . '_value', $data['stat_' . $i . '_value'] ?? '') }}"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-bold"
                        placeholder="120+">
                </div>
                <div class="space-y-2">
                    <label
                        class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Legenda
                        {{ $i }}</label>
                    <input type="text" name="stat_{{ $i }}_label"
                        value="{{ old('stat_' . $i . '_label', $data['stat_' . $i . '_label'] ?? '') }}"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                        placeholder="Palestras">
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Community Levels Section -->
<section id="sec-niveis-da-comunidade"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <i class="fas fa-layer-group text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Niveis da Comunidade</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="community" {{ ($data['community_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Titulo da Secao</label>
            <input type="text" name="community_title" value="{{ old('community_title', $data['community_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-bold"
                placeholder="Niveis da Comunidade">
        </div>

        <div class="grid grid-cols-1 gap-6">
            @foreach ([1, 2, 3, 4] as $i)
                <div class="rounded-3xl border border-slate-100 dark:border-slate-800 p-5 bg-slate-50 dark:bg-slate-800/20 space-y-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Nivel {{ $i }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Nome</label>
                            <input type="text" name="community_level_{{ $i }}_name"
                                value="{{ old('community_level_' . $i . '_name', $data['community_level_' . $i . '_name'] ?? '') }}"
                                class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all"
                                placeholder="Iniciante">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Numero</label>
                            <input type="text" name="community_level_{{ $i }}_count"
                                value="{{ old('community_level_' . $i . '_count', $data['community_level_' . $i . '_count'] ?? '') }}"
                                class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all"
                                placeholder="1.200">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Icone</label>
                            <input type="text" name="community_level_{{ $i }}_icon"
                                value="{{ old('community_level_' . $i . '_icon', $data['community_level_' . $i . '_icon'] ?? '') }}"
                                class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all"
                                placeholder="seedling">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Cor</label>
                            <input type="text" name="community_level_{{ $i }}_color"
                                value="{{ old('community_level_' . $i . '_color', $data['community_level_' . $i . '_color'] ?? '') }}"
                                class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all"
                                placeholder="#10B981">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Descricao</label>
                        <input type="text" name="community_level_{{ $i }}_desc"
                            value="{{ old('community_level_' . $i . '_desc', $data['community_level_' . $i . '_desc'] ?? '') }}"
                            class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all"
                            placeholder="Comecando a jornada">
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Ranking Section -->
<section id="sec-top-networkers"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/20">
                <i class="fas fa-trophy text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Top Networkers</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="ranking" {{ ($data['ranking_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="space-y-4">
            <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Titulo</label>
                <input type="text" name="ranking_title"
                    value="{{ old('ranking_title', $data['ranking_title'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-bold"
                    placeholder="Top Networkers">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Texto Auxiliar</label>
                <input type="text" name="ranking_subtitle"
                    value="{{ old('ranking_subtitle', $data['ranking_subtitle'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all"
                    placeholder="Ranking baseado em conexoes">
            </div>
        </div>
        <div class="rounded-3xl border border-slate-100 dark:border-slate-800 p-5 bg-slate-50 dark:bg-slate-800/20">
            <p class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Avatar dos membros</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                O portal exibe automaticamente o avatar real do usuario no ranking. Quando o membro nao tiver foto, a inicial continua como fallback.
            </p>
        </div>
    </div>
</section>

<!-- Final CTA Section -->
<section id="sec-cta"
    class="bg-amber-500 rounded-[2.5rem] border-none shadow-xl shadow-amber-500/20 overflow-hidden scroll-mt-24">
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
                    <h2 class="text-2xl font-black uppercase tracking-widest italic leading-tight">CTA Final</h2>
                    <p class="text-amber-50 opacity-80 text-sm font-medium italic">Encerre a página com um convite à
                        exploração.</p>
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
                        Impacto</label>
                    <input type="text" name="cta_title" value="{{ old('cta_title', $data['cta_title'] ?? '') }}"
                        class="w-full bg-amber-600/40 border-amber-500/30 text-white placeholder:text-amber-200/40 rounded-2xl px-5 py-3.5 text-lg font-black italic focus:ring-2 focus:ring-white transition-all shadow-inner"
                        placeholder="Pronto para expandir sua rede?">
                </div>
                <div class="space-y-1">
                    <label
                        class="text-[11px] font-black uppercase tracking-widest opacity-80 px-1 italic">Subtítulo</label>
                    <textarea name="cta_subtitle" rows="3"
                        class="w-full bg-amber-600/40 border-amber-500/30 text-white rounded-2xl px-5 py-3 text-sm font-medium focus:ring-2 focus:ring-white transition-all shadow-inner leading-relaxed"
                        placeholder="Descrição do CTA...">{{ old('cta_subtitle', $data['cta_subtitle'] ?? '') }}</textarea>
                </div>
            </div>
            <div class="flex flex-col justify-end space-y-4">
                <div class="space-y-1">
                    <label class="text-[11px] font-black uppercase tracking-widest opacity-80 px-1 italic">Texto do
                        Botão CTA</label>
                    <input type="text" name="cta_btn" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}"
                        class="w-full bg-white text-amber-700 border-none rounded-2xl px-6 py-4 text-base font-black uppercase tracking-widest focus:ring-4 focus:ring-white/20 transition-all shadow-xl shadow-amber-900/40"
                        placeholder="Explorar recursos">
                </div>
            </div>
        </div>
    </div>
</section>
