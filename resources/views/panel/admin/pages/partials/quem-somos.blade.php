{{-- Partial: quem-somos (Tailwind Version) --}}

<!-- Header Section -->
<section id="sec-cabecalho"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                <i class="fas fa-users text-sm"></i>
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
                placeholder="Conheça as pessoas por trás da maior comunidade de networking do Brasil.">
        </div>
        <div class="space-y-3 pt-4">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 italic">Imagem de Capa <small
                    class="text-slate-400 font-normal">(Banner abaixo do título)</small></label>
            <input type="file" name="cover_image" accept="image/*" class="filepond">
            @if (!empty($data['cover_image']))
                <div
                    class="flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/10 rounded-xl border border-red-100 dark:border-red-900/20 w-fit">
                    <input type="checkbox" id="remove_cover_image" name="remove_cover_image" value="1"
                        class="rounded text-red-600 focus:ring-red-500">
                    <label for="remove_cover_image" class="text-xs font-bold text-red-600 dark:text-red-400">Remover imagem
                        atual</label>
                </div>
                <div
                    class="mt-2 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-800 shadow-sm max-w-lg">
                    <img src="{{ Storage::url($data['cover_image']) }}" class="w-full h-auto">
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Founders Section (JSON) -->
<section id="sec-fundadores"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-slate-800 text-white flex items-center justify-center shadow-lg shadow-slate-900/20">
                <i class="fas fa-crown text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Fundadores</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="founders" {{ ($data['founders_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título da Seção</label>
            <input type="text" name="founders_title" value="{{ old('founders_title', $data['founders_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all"
                placeholder="Fundadores">
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <label class="text-xs font-black uppercase tracking-widest text-slate-400">Dados dos Fundadores
                    (JSON)</label>
            </div>
            <textarea name="founders_json" rows="12"
                class="w-full bg-slate-950 text-emerald-400 font-mono text-xs border-slate-800 rounded-2xl px-6 py-4 focus:ring-2 focus:ring-emerald-500/50 transition-all leading-relaxed shadow-inner">{{ old('founders_json', json_encode($data['founders'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
            <p class="text-[10px] text-slate-500 px-2 italic">Campos requeridos: name, role, bio, initials.</p>
        </div>
    </div>
</section>

<!-- Team Section (JSON) -->
<section id="sec-time"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-slate-700 text-white flex items-center justify-center shadow-lg shadow-slate-600/20">
                <i class="fas fa-user-friends text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Nossa Equipe</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="team" {{ ($data['team_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título da Seção</label>
            <input type="text" name="team_title" value="{{ old('team_title', $data['team_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-bold focus:ring-2 focus:ring-blue-500 transition-all"
                placeholder="Nossa Equipe">
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <label class="text-xs font-black uppercase tracking-widest text-slate-400">Membros da Equipe
                    (JSON)</label>
            </div>
            <textarea name="team_json" rows="12"
                class="w-full bg-slate-950 text-blue-400 font-mono text-xs border-slate-800 rounded-2xl px-6 py-4 focus:ring-2 focus:ring-blue-500/50 transition-all leading-relaxed shadow-inner">{{ old('team_json', json_encode($data['team'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
            <p class="text-[10px] text-slate-500 px-2 italic">Campos requeridos: name, role, initials.</p>
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
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">UNN em Números</h2>
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
    <div class="p-8 space-y-8">
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título da Seção</label>
            <input type="text" name="stats_title" value="{{ old('stats_title', $data['stats_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-bold focus:ring-2 focus:ring-cyan-500 transition-all"
                placeholder="UNN em Números">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach ([1, 2, 3, 4] as $i)
                <div
                    class="grid grid-cols-1 gap-4 p-4 rounded-3xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50">
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Número
                            {{ $i }}</label>
                        <input type="text" name="stat_{{ $i }}_value"
                            value="{{ old('stat_' . $i . '_value', $data['stat_' . $i . '_value'] ?? '') }}"
                            class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-bold">
                    </div>
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Legenda
                            {{ $i }}</label>
                        <input type="text" name="stat_{{ $i }}_label"
                            value="{{ old('stat_' . $i . '_label', $data['stat_' . $i . '_label'] ?? '') }}"
                            class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Final CTA Section -->
<section id="sec-cta"
    class="bg-emerald-600 rounded-[2.5rem] border-none shadow-xl shadow-emerald-500/20 overflow-hidden scroll-mt-24">
    <div class="p-8 md:p-12 space-y-8 relative">
        <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none">
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6 text-white">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30 text-xl">
                    <i class="fas fa-bullhorn font-black italic"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black uppercase tracking-widest italic leading-tight">Chamada Final</h2>
                    <p class="text-emerald-50 opacity-80 text-sm font-medium italic">Convide novos membros a se juntarem
                        ao time.</p>
                </div>
            </div>
            <div
                class="flex items-center gap-3 px-5 py-2.5 bg-emerald-700/50 backdrop-blur-md rounded-2xl border border-white/10 shadow-lg">
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
                    <label
                        class="text-[11px] font-black uppercase tracking-widest opacity-80 px-1 italic">Título</label>
                    <input type="text" name="cta_title" value="{{ old('cta_title', $data['cta_title'] ?? '') }}"
                        class="w-full bg-emerald-700/40 border-emerald-500/30 text-white placeholder:text-emerald-200/40 rounded-2xl px-5 py-3.5 text-lg font-black italic focus:ring-2 focus:ring-white transition-all shadow-inner"
                        placeholder="Quer fazer parte do time?">
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
                        Botão</label>
                    <input type="text" name="cta_btn" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}"
                        class="w-full bg-white text-emerald-700 border-none rounded-2xl px-6 py-4 text-base font-black uppercase tracking-widest focus:ring-4 focus:ring-white/20 transition-all shadow-xl shadow-emerald-900/40"
                        placeholder="Entre em contato">
                </div>
            </div>
        </div>
    </div>
</section>