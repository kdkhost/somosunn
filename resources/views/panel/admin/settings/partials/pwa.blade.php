<div class="space-y-8">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white text-lg">Progressive Web App (PWA)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Transforme seu site em um app instalável.</p>
                </div>
            </div>
            <div class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="pwa_enabled" value="0">
                <input type="checkbox" name="pwa_enabled" id="pwa_enabled" value="1" class="sr-only peer" {{ ($settings['pwa_enabled'] ?? 0) ? 'checked' : '' }}>
                <div class="w-12 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nome do App (Curto)</label>
                <input type="text" name="pwa_short_name" value="{{ $settings['pwa_short_name'] ?? config('app.name') }}" placeholder="Ex: UNN"
                       class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                <p class="text-[10px] text-slate-400 mt-1">Nome exibido na tela inicial do dispositivo.</p>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Display Mode</label>
                <select name="pwa_display" class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    <option value="standalone" {{ ($settings['pwa_display'] ?? 'standalone') == 'standalone' ? 'selected' : '' }}>Standalone (App Nativo)</option>
                    <option value="fullscreen" {{ ($settings['pwa_display'] ?? 'standalone') == 'fullscreen' ? 'selected' : '' }}>Fullscreen (Tela Cheia)</option>
                    <option value="minimal-ui" {{ ($settings['pwa_display'] ?? 'standalone') == 'minimal-ui' ? 'selected' : '' }}>Minimal UI</option>
                    <option value="browser" {{ ($settings['pwa_display'] ?? 'standalone') == 'browser' ? 'selected' : '' }}>Browser (Aba Normal)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cor do Tema (Status Bar)</label>
                <input type="text" name="pwa_theme_color" value="{{ $settings['pwa_theme_color'] ?? '#0C6BF7' }}"
                       class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white colorpicker-input">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cor de Fundo (Splash)</label>
                <input type="text" name="pwa_background_color" value="{{ $settings['pwa_background_color'] ?? '#FFFFFF' }}"
                       class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white colorpicker-input">
            </div>
        </div>
    </div>

    <!-- PWA Icons -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                <i class="fas fa-images"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Ícones e Imagens PWA</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([
                'pwa_icon_192' => ['Ícone (192x192)', 'PNG obrigatório'],
                'pwa_icon_512' => ['Ícone (512x512)', 'PNG obrigatório (Splash)']
            ] as $name => $data)
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 space-y-4 flex flex-col items-center">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-center h-8 flex items-center">{{ $data[0] }}</label>
                
                <div class="w-32 h-32 rounded-2xl bg-white dark:bg-slate-900 border-2 border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden relative">
                    @if($url = $getUrl($name))
                        <img id="preview_{{ $name }}" src="{{ $url }}" class="w-full h-full object-contain">
                    @else
                        <div class="text-center p-4">
                            <i class="fas fa-image text-3xl text-slate-300 mb-1"></i>
                            <p class="text-[9px] text-slate-400">{{ $data[1] }}</p>
                        </div>
                    @endif
                </div>

                <div class="flex gap-2 w-full">
                    <button type="button" onclick="document.getElementById('input_{{ $name }}').click()" 
                            class="flex-1 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Selecionar
                    </button>
                    <input type="file" id="input_{{ $name }}" name="{{ $name }}" class="hidden" accept="image/png" onchange="previewImage(this, 'preview_{{ $name }}')">
                    
                    <input type="hidden" name="remove_{{ $name }}" id="remove_{{ $name }}" value="0">
                    <button type="button" onclick="removeImage('{{ $name }}', 'preview_{{ $name }}')"
                            class="px-2 py-2 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            </div>
            @endforeach

            <div class="sm:col-span-2 p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 space-y-4">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Banner de Instalação (Opcional)</label>
                
                <div class="w-full h-32 rounded-2xl bg-white dark:bg-slate-900 border-2 border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden relative">
                    @if($url = $getUrl('pwa_banner'))
                        <img id="preview_pwa_banner" src="{{ $url }}" class="w-full h-full object-cover">
                    @else
                        <div class="text-center p-4">
                            <i class="fas fa-image text-3xl text-slate-300 mb-1"></i>
                            <p class="text-[10px] text-slate-400">Banner Promocional</p>
                        </div>
                    @endif
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('input_pwa_banner').click()" 
                            class="flex-1 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Selecionar Imagem
                    </button>
                    <input type="file" id="input_pwa_banner" name="pwa_banner" class="hidden" accept="image/*" onchange="previewImage(this, 'preview_pwa_banner')">
                    
                    <input type="hidden" name="remove_pwa_banner" id="remove_pwa_banner" value="0">
                    <button type="button" onclick="removeImage('pwa_banner', 'preview_pwa_banner')"
                            class="px-2 py-2 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
