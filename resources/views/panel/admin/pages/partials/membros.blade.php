{{-- Partial: membros (Tailwind Version) --}}

<!-- Hero Section -->
<section id="sec-hero"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                <i class="fas fa-users text-sm"></i>
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
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Título Principal <small
                    class="text-slate-400 font-normal italic">(Heading H1)</small></label>
            <input type="text" name="hero_title" value="{{ old('hero_title', $data['hero_title'] ?? '') }}"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-black uppercase tracking-tight"
                placeholder="Membros UNN">
        </div>
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Subtítulo / Descrição</label>
            <textarea name="hero_subtitle" rows="4"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all font-medium leading-relaxed"
                placeholder="Conheça os empreendedores que fazem parte da nossa comunidade exclusiva de networking empresarial.">{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}</textarea>
        </div>
    </div>
</section>

<!-- Regras de Exibição Section -->
<section id="sec-regras"
    class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden scroll-mt-24 mt-8">
    <div
        class="p-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/20">
                <i class="fas fa-sliders-h text-sm"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white uppercase tracking-wider">Regras de Exibição de Membros</h2>
        </div>
    </div>
    <div class="p-8 space-y-6">
        <div class="space-y-2">
            <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Quem deve aparecer na lista pública de membros?</label>
            <select name="members_display_mode"
                class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-amber-500 transition-all font-semibold text-slate-800 dark:text-slate-200">
                <option value="all" {{ old('members_display_mode', $data['members_display_mode'] ?? 'all') === 'all' ? 'selected' : '' }}>
                    Todos os membros cadastrados ativos (Recomendado para fortalecer a comunidade)
                </option>
                <option value="with_plan" {{ old('members_display_mode', $data['members_display_mode'] ?? '') === 'with_plan' ? 'selected' : '' }}>
                    Apenas membros vinculados a qualquer plano (Starter, Pro, Elite)
                </option>
                <option value="paid_only" {{ old('members_display_mode', $data['members_display_mode'] ?? '') === 'paid_only' ? 'selected' : '' }}>
                    Apenas assinantes com plano pago ativo (Pro / Elite)
                </option>
            </select>
            <p class="text-xs text-slate-400 px-1">Define a visibilidade geral do diretório público de membros.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Exibir Administradores na lista pública?</label>
                <select name="members_show_admins"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-amber-500 transition-all font-semibold text-slate-800 dark:text-slate-200">
                    <option value="0" {{ old('members_show_admins', $data['members_show_admins'] ?? '0') == '0' ? 'selected' : '' }}>Não (Apenas membros comuns)</option>
                    <option value="1" {{ old('members_show_admins', $data['members_show_admins'] ?? '0') == '1' ? 'selected' : '' }}>Sim (Exibir administradores)</option>
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-600 dark:text-slate-400 px-1">Exibir somente perfis com foto cadastrada?</label>
                <select name="members_only_with_photo"
                    class="w-full bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-3 text-sm focus:ring-2 focus:ring-amber-500 transition-all font-semibold text-slate-800 dark:text-slate-200">
                    <option value="0" {{ old('members_only_with_photo', $data['members_only_with_photo'] ?? '0') == '0' ? 'selected' : '' }}>Não (Exibir membros com foto ou iniciais)</option>
                    <option value="1" {{ old('members_only_with_photo', $data['members_only_with_photo'] ?? '0') == '1' ? 'selected' : '' }}>Sim (Apenas membros com foto real)</option>
                </select>
            </div>
        </div>
    </div>
</section>
