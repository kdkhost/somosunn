{{-- Partial: somos-unicas-sobre (Tailwind Version) --}}

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
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Identidade e Imagens
                Extras</h2>
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
    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
        <div class="space-y-4">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Cor do Tema (Página
                    Sobre)</label>
                <div
                    class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-inner">
                    <input type="color" name="theme_color" value="{{ $data['theme_color'] ?? '#6d28d9' }}"
                        class="w-16 h-16 rounded-xl cursor-pointer border-none bg-transparent">
                    <div class="text-sm font-mono text-slate-500 dark:text-slate-400 font-bold uppercase">
                        {{ $data['theme_color'] ?? '#6d28d9' }}</div>
                </div>
                <p class="text-[10px] text-slate-500 italic px-2">Esta cor será aplicada aos gradientes e elementos
                    decorativos da página Sobre.</p>
            </div>
        </div>
        <div class="space-y-4">
            <label
                class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 italic text-purple-600 dark:text-purple-400">Imagem
                da Seção "Networking"</label>
            <input type="file" name="networking_image" accept="image/*" class="filepond">
            @if (!empty($data['networking_image']))
                <div
                    class="flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/10 rounded-xl border border-red-100 dark:border-red-900/20 w-fit">
                    <input type="checkbox" id="remove_networking_image" name="remove_networking_image" value="1"
                        class="rounded text-red-600 focus:ring-red-500">
                    <label for="remove_networking_image" class="text-xs font-bold text-red-600 dark:text-red-400">Remover
                        imagem atual</label>
                </div>
                <div
                    class="mt-2 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-800 shadow-sm max-w-sm">
                    <img src="{{ Storage::url($data['networking_image']) }}" class="w-full h-auto">
                </div>
            @endif
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
    </div>
    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div class="space-y-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título Principal</label>
                <input type="text" name="hero_title" value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-pink-500 transition-all font-black uppercase tracking-tight"
                    placeholder="Sobre a Somos Únicas...">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo / Introdução</label>
                <textarea name="hero_subtitle" rows="6"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-pink-500 transition-all font-medium leading-relaxed"
                    placeholder="Breve introdução da página sobre...">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
            </div>
        </div>
        <div class="space-y-4">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1 italic">Imagem de Destaque
                (Banner)</label>
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
                    class="mt-2 rounded-[2rem] overflow-hidden border border-slate-100 dark:border-slate-800 shadow-xl max-w-md mx-auto">
                    <img src="{{ Storage::url($data['hero_image']) }}" class="w-full h-auto">
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Detailed Content Section -->
<section id="sec-detalhes"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24 mt-8">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-slate-800 text-white flex items-center justify-center shadow-lg shadow-slate-700/20">
                <i class="fas fa-file-alt text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Conteúdo Detalhado
            </h2>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título da Seção de Conteúdo</label>
            <input type="text" name="content_title" value="{{ old('content_title', $data['content_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-bold"
                placeholder="Nossa Jornada">
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Corpo do Texto (Completo)</label>
            <textarea name="content_body" rows="15"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm font-medium leading-relaxed summernote"
                placeholder="Conteúdo completo da página sobre...">{{ old('content_body', $data['content_body'] ?? '') }}</textarea>
        </div>
    </div>
</section>