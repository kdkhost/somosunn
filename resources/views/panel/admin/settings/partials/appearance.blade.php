<div class="space-y-8">
    <!-- Hero Settings -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div
                class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                <i class="fas fa-home"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Seção Hero (Início)</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Título
                        Principal</label>
                    <input type="text" name="hero_title"
                        value="{{ $settings['hero_title'] ?? 'Transforme sua carreira' }}"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Subtítulo</label>
                    <textarea name="hero_subtitle" rows="3"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">{{ $settings['hero_subtitle'] ?? 'Junte-se a milhares de membros e aprenda com os melhores.' }}</textarea>
                </div>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Opacidade Degradê
                            (%)</label>
                        <input type="number" name="site_bg_gradient_opacity"
                            value="{{ $settings['site_bg_gradient_opacity'] ?? 85 }}" min="0" max="100"
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cor
                            Inicial</label>
                        <input type="text" name="site_bg_gradient_start"
                            value="{{ $settings['site_bg_gradient_start'] ?? '#000000' }}"
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white colorpicker-input">
                    </div>
                </div>
                <div
                    class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/20">
                    <div class="flex gap-3">
                        <i class="fas fa-info-circle text-amber-500 mt-1"></i>
                        <p class="text-xs text-amber-800 dark:text-amber-400 leading-relaxed">
                            Estas configurações afetam a sobreposição escura sobre a imagem de fundo da seção inicial
                            (Hero) para garantir a leitura do texto.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Events & Mentorships -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div
                class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Fundo de Eventos e Mentorias</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Desfoque (Blur
                    px)</label>
                <input type="number" name="events_hero_bg_blur_px"
                    value="{{ $settings['events_hero_bg_blur_px'] ?? 64 }}"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                <p class="text-xs text-slate-400 mt-1">Quanto maior o valor, mais desfocada será a imagem de fundo.</p>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Intensidade Película
                    (%)</label>
                <input type="number" name="events_hero_film_strength_percent"
                    value="{{ $settings['events_hero_film_strength_percent'] ?? 100 }}" min="0" max="100"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                <p class="text-xs text-slate-400 mt-1">Controla o quão escuro fica o fundo para melhorar a leitura.</p>
            </div>
        </div>
    </div>

    <!-- Global Colors & Theme -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div
                class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i class="fas fa-palette"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Cores e Tema Global</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tema Padrão</label>
                <select name="site_theme"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    <option value="light" {{ ($settings['site_theme'] ?? 'light') === 'light' ? 'selected' : '' }}>Light
                        (Claro)</option>
                    <option value="dark" {{ ($settings['site_theme'] ?? '') === 'dark' ? 'selected' : '' }}>Dark (Escuro)
                    </option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cor Primária</label>
                <input type="text" name="site_color_primary" value="{{ $settings['site_color_primary'] ?? '#007bff' }}"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white colorpicker-input">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cor Secundária</label>
                <input type="text" name="site_color_secondary"
                    value="{{ $settings['site_color_secondary'] ?? '#6c757d' }}"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white colorpicker-input">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Fonte (Font
                    Family)</label>
                <input type="text" name="site_font_family"
                    value="{{ $settings['site_font_family'] ?? 'Inter, sans-serif' }}"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
        </div>
    </div>

    <!-- Footer & Social -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div
                class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center">
                <i class="fas fa-window-maximize"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Rodapé e Redes Sociais</h3>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Texto do Rodapé</label>
                <textarea name="footer_text" rows="2"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">{{ $settings['footer_text'] ?? '' }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach([
                        'social_instagram' => ['Instagram', 'fab fa-instagram'],
                        'social_facebook' => ['Facebook', 'fab fa-facebook'],
                        'social_youtube' => ['Youtube', 'fab fa-youtube'],
                        'social_linkedin' => ['LinkedIn', 'fab fa-linkedin']
                    ] as $name => $data)

                                        <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="{{ $data[1] }} text-slate-400"></i>
                        </div>
                        <input type="text" name="{{ $name }}" value="{{ $settings[$name] ?? '' }}" placeholder="{{ $data[0] }} URL"
                               class="w-full pl-11 pr-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white text-sm">
                    </div>
                @endforeach
        
           </div>
        </div>
    </div>

               
    <!-- Auth Animations -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-magic-50 dark:bg-magic-900/20 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <i class="fas fa-magic"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white text-lg">Autenticação (Animações)</h3>
                
               <p class="text-xs text-slate-500 dark:text-slate-400">Aparece apenas em desktops/notebooks.</p>
            </div>
        </div>

        <div class="space-y-6">

                           
                                       <div class="flex
                            items-center justify-between p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-800">

                                        <div class="flex items-center gap-3">
                    <div class="relative inline-flex items-center cursor-pointer">

                                               <input type="hidden" name="auth_visual_animation_enabled" value="0">
                        <input type="checkbox" name="auth_visual_animation_enabled" id="auth_visual_animation_enabled" value="1" class="sr-only peer" {{ ($settings['auth_visual_animation_enabled'] ?? 1) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 cursor-pointer" for="auth_visual_animation_enabled">Ativar Animação Global</label>
                </di
                   v>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">

                                           @foreach([
                                                                        'auth_visual_animation_login' => 'Login',
                                                                        'auth_visual_animation_register' => 'Cadastro',
                                                                        'auth_visual_animation_password_email' => 'Recup. Senha',
                                                                        'auth_visual_animation_password_reset' => 'Resetar Senha'
                                                                    ] as $name => $label)
                                            <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950">
                                                <div class="relative inline-flex items-center cursor-pointer scale-75">
                                                    <input type="hidden" name="{{ $name }}" value="0">
                                                    <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="1" class="sr-only peer" {{ ($settings[$name] ?? 1) ? 'checked' : '' }}>
                                                    <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                                </div>
                                                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 cursor-pointer" for="{{ $name }}">{{ $label }}</label>
                                            </div>
                                        @endforeach
            </div>
        </div>
    </div>
</div>
