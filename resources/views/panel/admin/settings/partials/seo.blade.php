<div class="space-y-8">
    <!-- Metadata -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 flex items-center justify-center">
                <i class="fas fa-search-plus"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Metadados (Google & buscadores)</h3>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Descrição do Site (Meta Description)</label>
                <textarea name="site_description" rows="3" placeholder="Breve descrição do seu negócio..."
                          class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">{{ $settings['site_description'] ?? '' }}</textarea>
                <p class="text-[10px] text-slate-400 mt-1">Recomendado: entre 150 e 160 caracteres.</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Palavras-Chave (Meta Keywords)</label>
                <input type="text" name="site_keywords" value="{{ $settings['site_keywords'] ?? '' }}" placeholder="curso, mentoria, comunidade"
                       class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                <p class="text-[10px] text-slate-400 mt-1">Separe as palavras por vírgula.</p>
            </div>
        </div>
    </div>

    <!-- Social Sharing -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i class="fas fa-share-alt"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Imagens de Compartilhamento</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach([
                'seo_og_image' => ['OpenGraph (FB/WA)', '1200x630px', 'fab fa-facebook'],
                'seo_twitter_image' => ['Twitter Card', '1200x600px', 'fab fa-twitter']
            ] as $name => $data)
            <div class="space-y-4">
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">{{ $data[0] }}</label>
                
                <div class="w-full h-40 rounded-3xl bg-slate-50 dark:bg-slate-950 border-2 border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden relative">
                    @if($url = $getUrl($name))
                        <img id="preview_{{ $name }}" src="{{ $url }}" class="w-full h-full object-cover">
                    @else
                        <div class="text-center p-6">
                            <i class="{{ $data[2] }} text-3xl text-slate-300 mb-2"></i>
                            <p class="text-[10px] text-slate-400">{{ $data[1] }}</p>
                        </div>
                    @endif
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('input_{{ $name }}').click()" 
                            class="flex-1 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Selecionar
                    </button>
                    <input type="file" id="input_{{ $name }}" name="{{ $name }}" class="hidden" accept="image/*" onchange="previewImage(this, 'preview_{{ $name }}')">
                    
                    <input type="hidden" name="remove_{{ $name }}" id="remove_{{ $name }}" value="0">
                    <button type="button" onclick="removeImage('{{ $name }}', 'preview_{{ $name }}')"
                            class="px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Tracking Scripts -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center">
                <i class="fas fa-code"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Scripts de Rastreamento</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Scripts do Header (&lt;head&gt;)</label>
                <textarea name="tracking_head" rows="6" placeholder="<!-- Google Tag Manager / Pixel -->"
                          class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-mono text-xs text-slate-800 dark:text-slate-300">{{ $settings['tracking_head'] ?? '' }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Scripts do Body (&lt;body&gt;)</label>
                <textarea name="tracking_body" rows="6" placeholder="<!-- Chat / Analytics -->"
                          class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-mono text-xs text-slate-800 dark:text-slate-300">{{ $settings['tracking_body'] ?? '' }}</textarea>
            </div>
        </div>
    </div>
</div>
