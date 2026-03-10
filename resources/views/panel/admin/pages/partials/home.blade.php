{{-- Partial: home (Tailwind Version) --}}

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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título (linha 1)</label>
                <input type="text" name="hero_title" value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo (linha 2)</label>
                <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">
            </div>
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Descrição / Corpo</label>
            <textarea name="body" rows="3"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">{{ old('body', $data['body'] ?? '') }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Botão Principal (CTA 1)</label>
                <input type="text" name="hero_cta_text" value="{{ old('hero_cta_text', $data['hero_cta_text'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                    placeholder="Quero fazer parte">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Botão Secundário (CTA
                    2)</label>
                <input type="text" name="hero_cta2_text"
                    value="{{ old('hero_cta2_text', $data['hero_cta2_text'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                    placeholder="Conhecer a UNN">
            </div>
        </div>
        <div class="space-y-3">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 italic">Imagem do Hero (Arraste ou
                Clique para fazer upload)</label>
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
                    <label class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Valor
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

<!-- About Section -->
<section id="sec-sobre"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-slate-600 text-white flex items-center justify-center shadow-lg shadow-slate-500/20">
                <i class="fas fa-info-circle text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">O que é a UNN</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="about" {{ ($data['about_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título da seção</label>
                <input type="text" name="about_title" value="{{ old('about_title', $data['about_title'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                    placeholder="O que é a UNN">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo</label>
                <input type="text" name="about_subtitle"
                    value="{{ old('about_subtitle', $data['about_subtitle'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">
            </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800 pt-8">
            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500 mb-6 px-1">Cards
                de Detalhes (4 blocos)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ([1, 2, 3, 4] as $i)
                    <div
                        class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50 space-y-4">
                        <div class="space-y-2">
                            <label
                                class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Card
                                {{ $i }} — Título</label>
                            <input type="text" name="about_card_{{ $i }}_title"
                                value="{{ old('about_card_' . $i . '_title', $data['about_card_' . $i . '_title'] ?? '') }}"
                                class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-bold">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-1">Card
                                {{ $i }} — Texto</label>
                            <input type="text" name="about_card_{{ $i }}_text"
                                value="{{ old('about_card_' . $i . '_text', $data['about_card_' . $i . '_text'] ?? '') }}"
                                class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- Journey Section -->
<section id="sec-journey"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                <i class="fas fa-route text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Onde o network me
                levou</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="journey" {{ ($data['journey_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título da seção</label>
                <input type="text" name="journey_title" value="{{ old('journey_title', $data['journey_title'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo</label>
                <input type="text" name="journey_subtitle"
                    value="{{ old('journey_subtitle', $data['journey_subtitle'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium"
                    placeholder="Conexões que viraram negócios.">
            </div>
        </div>
        <div
            class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 rounded-3xl bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/20">
            <div class="space-y-2">
                <label
                    class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400 px-1">Destaque —
                    Legenda</label>
                <input type="text" name="journey_highlight_label"
                    value="{{ old('journey_highlight_label', $data['journey_highlight_label'] ?? '') }}"
                    class="w-full bg-white dark:bg-slate-900 border-blue-200 dark:border-blue-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-bold">
            </div>
            <div class="space-y-2">
                <label
                    class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400 px-1">Destaque —
                    Texto</label>
                <input type="text" name="journey_highlight_value"
                    value="{{ old('journey_highlight_value', $data['journey_highlight_value'] ?? '') }}"
                    class="w-full bg-white dark:bg-slate-900 border-blue-200 dark:border-blue-800 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium">
            </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800 pt-8 space-y-6">
            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500 px-1">Resultados
                Individuais (3 cards)</h3>
            <div class="space-y-4">
                @foreach ([1, 2, 3] as $i)
                    <div
                        class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-3xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Chamada
                                {{ $i }}</label>
                            <input type="text" name="journey_card_{{ $i }}_title"
                                value="{{ old('journey_card_' . $i . '_title', $data['journey_card_' . $i . '_title'] ?? '') }}"
                                class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2 text-xs font-bold">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Resultado
                                {{ $i }}</label>
                            <input type="text" name="journey_card_{{ $i }}_result"
                                value="{{ old('journey_card_' . $i . '_result', $data['journey_card_' . $i . '_result'] ?? '') }}"
                                class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2 text-xs font-bold text-blue-600 dark:text-blue-400">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">Descrição
                                {{ $i }}</label>
                            <input type="text" name="journey_card_{{ $i }}_text"
                                value="{{ old('journey_card_' . $i . '_text', $data['journey_card_' . $i . '_text'] ?? '') }}"
                                class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2 text-xs font-medium">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 text-center block">Texto do Botão
                CTA</label>
            <input type="text" name="journey_cta_text"
                value="{{ old('journey_cta_text', $data['journey_cta_text'] ?? '') }}"
                class="w-full md:w-1/2 mx-auto block bg-blue-600 text-white border-none rounded-2xl px-5 py-3 text-sm focus:ring-4 focus:ring-blue-500/20 transition-all font-black text-center placeholder:text-blue-200"
                placeholder="Quero viver isso também">
        </div>
    </div>
</section>

<!-- Events & Mentorships Section -->
<section id="sec-eventos"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-slate-800 text-white flex items-center justify-center shadow-lg shadow-slate-900/20">
                <i class="fas fa-calendar text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Eventos & Mentorias
            </h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="events" {{ ($data['events_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div
                class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50 space-y-4">
                <h4 class="text-sm font-black uppercase text-slate-400 dark:text-slate-500 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-blue-500"></i> Eventos
                </h4>
                <div class="space-y-3">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 px-1">Título</label>
                        <input type="text" name="events_title"
                            value="{{ old('events_title', $data['events_title'] ?? '') }}"
                            class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-bold">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo</label>
                        <input type="text" name="events_subtitle"
                            value="{{ old('events_subtitle', $data['events_subtitle'] ?? '') }}"
                            class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-medium">
                    </div>
                </div>
            </div>
            <div
                class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50 space-y-4">
                <h4 class="text-sm font-black uppercase text-slate-400 dark:text-slate-500 flex items-center gap-2">
                    <i class="fas fa-chalkboard-teacher text-blue-500"></i> Mentorias
                </h4>
                <div class="space-y-3">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 px-1">Título</label>
                        <input type="text" name="mentorships_title"
                            value="{{ old('mentorships_title', $data['mentorships_title'] ?? '') }}"
                            class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-bold">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo</label>
                        <input type="text" name="mentorships_subtitle"
                            value="{{ old('mentorships_subtitle', $data['mentorships_subtitle'] ?? '') }}"
                            class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-medium">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Community Section -->
<section id="sec-comunidade"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <i class="fas fa-users text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Seção Comunidade</h2>
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
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título da seção</label>
            <input type="text" name="community_title"
                value="{{ old('community_title', $data['community_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-bold"
                placeholder="Comunidade por níveis">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
                class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50 space-y-4">
                <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest">Bloco Iniciantes</h4>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-400 px-1">Título</label>
                    <input type="text" name="community_beginner_title"
                        value="{{ old('community_beginner_title', $data['community_beginner_title'] ?? '') }}"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-bold">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-400 px-1">Descrição</label>
                    <textarea name="community_beginner_desc" rows="2"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-medium">{{ old('community_beginner_desc', $data['community_beginner_desc'] ?? '') }}</textarea>
                </div>
            </div>
            <div
                class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50 space-y-4">
                <h4 class="text-xs font-black uppercase text-slate-400 tracking-widest">Bloco Sucesso</h4>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-400 px-1">Título</label>
                    <input type="text" name="community_success_title"
                        value="{{ old('community_success_title', $data['community_success_title'] ?? '') }}"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-bold">
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-400 px-1">Descrição</label>
                    <textarea name="community_success_desc" rows="2"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-medium">{{ old('community_success_desc', $data['community_success_desc'] ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Ranking & Testimonials Section -->
<section id="sec-ranking"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/20">
                <i class="fas fa-trophy text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Ranking & Depoimentos
            </h2>
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
    <div class="p-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Ranking — Título</label>
                <input type="text" name="ranking_title" value="{{ old('ranking_title', $data['ranking_title'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-bold">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Ranking — Subtítulo</label>
                <input type="text" name="ranking_subtitle"
                    value="{{ old('ranking_subtitle', $data['ranking_subtitle'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-medium">
            </div>
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400">Depoimentos Personalizados (Editor
                    JSON)</label>
                <span
                    class="text-[10px] font-black uppercase text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded italic">Modo
                    Avançado</span>
            </div>
            <div class="group relative">
                <textarea name="testimonials_json" rows="8"
                    class="w-full bg-slate-950 text-emerald-400 font-mono text-xs border-slate-800 rounded-2xl px-6 py-4 focus:ring-2 focus:ring-emerald-500/50 transition-all leading-relaxed shadow-inner"
                    placeholder='[{"name":"João","role":"CEO","text":"Ótima rede!","rating":5}]'>{{ old('testimonials_json', json_encode($data['testimonials'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
                <div class="absolute top-2 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                    <i class="fas fa-code text-slate-700"></i>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 px-2">Certifique-se de manter a sintaxe correta do JSON para que os
                depoimentos sejam exibidos.</p>
        </div>
    </div>
</section>

<!-- Final CTA Section -->
<section id="sec-cta"
    class="bg-emerald-600 rounded-[2.5rem] border-none shadow-xl shadow-emerald-500/20 overflow-hidden scroll-mt-24">
    <div class="p-8 md:p-12 space-y-8 relative">
        <!-- Decoration -->
        <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none">
        </div>
        <div class="absolute -left-10 -top-10 w-48 h-48 bg-emerald-400/20 rounded-full blur-2xl pointer-events-none">
        </div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4 text-white">
                <div
                    class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30">
                    <i class="fas fa-bullhorn text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black uppercase tracking-widest italic">Chamada Final</h2>
                    <p class="text-emerald-50 opacity-80 text-sm font-medium">Capture a atenção no encerramento da
                        página.</p>
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

        <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-4">
                <div class="space-y-2">
                    <label class="text-xs font-black uppercase tracking-widest text-emerald-100 opacity-80 px-1">Título
                        de Impacto</label>
                    <input type="text" name="cta_section_title"
                        value="{{ old('cta_section_title', $data['cta_section_title'] ?? '') }}"
                        class="w-full bg-emerald-700/40 border-emerald-500/30 text-white placeholder:text-emerald-300/50 rounded-2xl px-6 py-4 text-lg font-black focus:ring-2 focus:ring-white transition-all shadow-inner">
                </div>
                <div class="space-y-2">
                    <label
                        class="text-xs font-black uppercase tracking-widest text-emerald-100 opacity-80 px-1">Subtítulo
                        Descritivo</label>
                    <input type="text" name="cta_section_subtitle"
                        value="{{ old('cta_section_subtitle', $data['cta_section_subtitle'] ?? '') }}"
                        class="w-full bg-emerald-700/40 border-emerald-500/30 text-white placeholder:text-emerald-300/50 rounded-2xl px-6 py-4 text-sm font-medium focus:ring-2 focus:ring-white transition-all shadow-inner">
                </div>
            </div>
            <div class="space-y-4 md:pt-6">
                <div class="grid grid-cols-1 gap-4">
                    <div class="p-6 rounded-3xl bg-white/5 border border-white/10 backdrop-blur-sm space-y-4">
                        <div class="space-y-2">
                            <label
                                class="text-xs font-black uppercase tracking-widest text-emerald-100 opacity-80 px-1 italic">Texto
                                Botão Principal</label>
                            <input type="text" name="cta_section_btn_primary"
                                value="{{ old('cta_section_btn_primary', $data['cta_section_btn_primary'] ?? '') }}"
                                class="w-full bg-white text-emerald-700 border-none rounded-xl px-4 py-3 text-sm font-black focus:ring-4 focus:ring-white/20 transition-all shadow-lg shadow-emerald-900/40">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-xs font-black uppercase tracking-widest text-emerald-100 opacity-80 px-1 italic">Texto
                                Botão Secundário</label>
                            <input type="text" name="cta_section_btn_secondary"
                                value="{{ old('cta_section_btn_secondary', $data['cta_section_btn_secondary'] ?? '') }}"
                                class="w-full bg-emerald-800/40 text-emerald-50 border border-emerald-400/30 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-white transition-all">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>