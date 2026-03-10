{{-- Partial: somos-unicas (Tailwind Version) --}}

<!-- Identity & Theme Section -->
<section id="sec-identidade"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-lg shadow-purple-500/20">
                <i class="fas fa-palette text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Identidade e Cor do
                Tema</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Ativo</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="identity" {{ ($data['identity_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        <div class="space-y-4">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Cor do Tema (Principal)</label>
                <div
                    class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-inner">
                    <input type="color" name="theme_color" value="{{ $data['theme_color'] ?? '#6d28d9' }}"
                        class="w-16 h-16 rounded-xl cursor-pointer border-none bg-transparent">
                    <div class="text-sm font-mono text-slate-500 dark:text-slate-400 font-bold uppercase">
                        {{ $data['theme_color'] ?? '#6d28d9' }}</div>
                </div>
                <p class="text-[10px] text-slate-500 italic px-2">Esta cor define a essência da área Somos Únicas.</p>
            </div>
        </div>
        <div
            class="p-6 bg-purple-50 dark:bg-purple-900/10 rounded-[1.5rem] border border-purple-100 dark:border-purple-900/20">
            <div class="flex gap-4 items-start">
                <div class="p-2 bg-white dark:bg-slate-900 rounded-lg shadow-sm text-purple-600">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-bold text-purple-900 dark:text-purple-200 uppercase tracking-tight">Dica de
                        Configuração</h4>
                    <p class="text-[11px] text-purple-800 dark:text-purple-300 leading-relaxed font-medium">A imagem
                        desta seção deve ser vibrante e empoderadora para refletir o público feminino da comunidade.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hero Section -->
<section id="sec-hero"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24 mt-8">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-pink-600 text-white flex items-center justify-center shadow-lg shadow-pink-500/20">
                <i class="fas fa-star text-sm"></i>
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
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-pink-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título Principal</label>
            <input type="text" name="hero_title" value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-pink-500 transition-all font-black uppercase tracking-tight"
                placeholder="Somos Únicas">
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo</label>
            <textarea name="hero_subtitle" rows="3"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-pink-500 transition-all font-medium leading-relaxed"
                placeholder="Descrição do manifesto Somos Únicas...">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
        </div>
        <div class="space-y-3 pt-4">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 italic">Imagem do Hero</label>
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

<!-- Content Sections -->
<section id="sec-conteudo"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24 mt-8">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-purple-700 text-white flex items-center justify-center shadow-lg shadow-purple-600/20">
                <i class="fas fa-layer-group text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Conteúdo da Página
            </h2>
        </div>
    </div>
    <div class="p-8 space-y-12">
        <!-- Cursos -->
        <div
            class="space-y-6 p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-purple-600 italic">Seção de Cursos</h3>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer section-toggle" data-section="courses" {{ ($data['courses_enabled'] ?? true) ? 'checked' : '' }}>
                    <div
                        class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600">
                    </div>
                </label>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-500 uppercase px-1">Título</label>
                    <input type="text" name="courses_title"
                        value="{{ old('courses_title', $data['courses_title'] ?? '') }}"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-bold">
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-500 uppercase px-1">Subtítulo</label>
                    <input type="text" name="courses_subtitle"
                        value="{{ old('courses_subtitle', $data['courses_subtitle'] ?? '') }}"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-medium">
                </div>
            </div>
        </div>

        <!-- Eventos -->
        <div
            class="space-y-6 p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-purple-600 italic">Seção de Eventos</h3>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer section-toggle" data-section="events" {{ ($data['events_enabled'] ?? true) ? 'checked' : '' }}>
                    <div
                        class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600">
                    </div>
                </label>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-500 uppercase px-1">Título</label>
                    <input type="text" name="events_title"
                        value="{{ old('events_title', $data['events_title'] ?? '') }}"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-bold">
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-500 uppercase px-1">Subtítulo</label>
                    <input type="text" name="events_subtitle"
                        value="{{ old('events_subtitle', $data['events_subtitle'] ?? '') }}"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-medium">
                </div>
            </div>
        </div>

        <!-- Mentorias -->
        <div
            class="space-y-6 p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/20 border border-slate-100 dark:border-slate-800/50">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-purple-600 italic">Seção de Mentorias</h3>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer section-toggle" data-section="mentorships" {{ ($data['mentorships_enabled'] ?? true) ? 'checked' : '' }}>
                    <div
                        class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600">
                    </div>
                </label>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-500 uppercase px-1">Título</label>
                    <input type="text" name="mentorships_title"
                        value="{{ old('mentorships_title', $data['mentorships_title'] ?? '') }}"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-bold">
                </div>
                <div class="space-y-2">
                    <label class="text-[11px] font-black text-slate-500 uppercase px-1">Subtítulo</label>
                    <input type="text" name="mentorships_subtitle"
                        value="{{ old('mentorships_subtitle', $data['mentorships_subtitle'] ?? '') }}"
                        class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-xl px-4 py-2.5 text-sm font-medium">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Empty State Section -->
<section id="sec-vazio"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24 mt-8">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-slate-600 text-white flex items-center justify-center shadow-lg shadow-slate-500/20">
                <i class="fas fa-ghost text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Empty State (Sem
                Conteúdo)</h2>
        </div>
        <div
            class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <span
                class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Visibilidade</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer section-toggle" data-section="empty" {{ ($data['empty_enabled'] ?? true) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
            </label>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título do Estado Vazio</label>
            <input type="text" name="empty_title" value="{{ old('empty_title', $data['empty_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-bold"
                placeholder="Nenhum conteúdo disponível no momento.">
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Descrição</label>
            <textarea name="empty_description" rows="3"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-medium leading-relaxed"
                placeholder="Explique por que não há conteúdo educacional disponível agora.">{{ old('empty_description', $data['empty_description'] ?? '') }}</textarea>
        </div>
    </div>
</section>