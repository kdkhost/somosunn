<div class="space-y-8">
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i class="fas fa-ad"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white text-lg">Monetização (Anúncios)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 text-sm">Gerencie Adsense e códigos
                        personalizados.</p>
                </div>
            </div>
            <div class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="ads_enabled" value="0">
                <input type="checkbox" name="ads_enabled" id="ads_enabled" value="1" class="sr-only peer" {{ ($settings['ads_enabled'] ?? 0) ? 'checked' : '' }}>
                <div
                    class="w-12 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
            <!-- Google AdSense -->
            <div
                class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 space-y-6">
                <h4 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fab fa-google text-red-500"></i> Google AdSense
                </h4>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Publisher ID
                            (Pub-ID)</label>
                        <input type="text" name="adsense_publisher_id"
                            value="{{ $settings['adsense_publisher_id'] ?? '' }}" placeholder="ca-pub-000000000000"
                            class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Slot
                            ID</label>
                        <input type="text" name="adsense_slot_id" value="{{ $settings['adsense_slot_id'] ?? '' }}"
                            placeholder="1234567890"
                            class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Formato</label>
                        <select name="adsense_format"
                            class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                            @php($adsFormat = $settings['adsense_format'] ?? 'auto')
                            <option value="auto" {{ $adsFormat === 'auto' ? 'selected' : '' }}>Automático (Responsivo)
                            </option>
                            <option value="fluid" {{ $adsFormat === 'fluid' ? 'selected' : '' }}>Fluxo (In-feed)</option>
                            <option value="rectangle" {{ $adsFormat === 'rectangle' ? 'selected' : '' }}>Retângulo
                            </option>
                            <option value="horizontal" {{ $adsFormat === 'horizontal' ? 'selected' : '' }}>Horizontal
                                (Banner)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Custom Code -->
            <div
                class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 space-y-4">
                <h4 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-code text-blue-500"></i> Código Personalizado
                </h4>
                <div class="h-full pb-8">
                    <textarea name="ads_code_html" rows="9" placeholder="<!-- Cole aqui o script do seu ad network -->"
                        class="w-full h-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-mono text-xs text-slate-800 dark:text-slate-300">{{ $settings['ads_code_html'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Feed Ads -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i class="fas fa-stream"></i>
                </div>
                <h3 class="font-bold text-slate-800 dark:text-white text-lg">Anúncios no Feed (Comunidade)</h3>
            </div>
            <div class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="ads_inter_feed_enabled" value="0">
                <input type="checkbox" name="ads_inter_feed_enabled" id="ads_inter_feed_enabled" value="1"
                    class="sr-only peer" {{ ($settings['ads_inter_feed_enabled'] ?? 0) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Frequência (A cada X
                    posts)</label>
                <select name="adsense_frequency"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    @php($adsFreq = (int) ($settings['adsense_frequency'] ?? 5))
                    <option value="3" {{ $adsFreq === 3 ? 'selected' : '' }}>A cada 3 posts</option>
                    <option value="5" {{ $adsFreq === 5 ? 'selected' : '' }}>A cada 5 posts</option>
                    <option value="10" {{ $adsFreq === 10 ? 'selected' : '' }}>A cada 10 posts</option>
                    <option value="15" {{ $adsFreq === 15 ? 'selected' : '' }}>A cada 15 posts</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Código para o Feed
                    (Opcional)</label>
                <textarea name="ads_inter_feed_code" rows="2" placeholder="Se vazio, usará o global configurado acima."
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-mono text-xs text-slate-800 dark:text-slate-300">{{ $settings['ads_inter_feed_code'] ?? '' }}</textarea>
            </div>
        </div>
    </div>
</div>