<div class="space-y-8">
    <!-- Marketplace Fees & Rules -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div
                class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 flex items-center justify-center">
                <i class="fas fa-percent"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Taxas e Regras</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Comissão do
                        Marketplace (%)</label>
                    <div class="relative">
                        <input type="number" name="marketplace_platform_fee_percent"
                            value="{{ $settings['marketplace_platform_fee_percent'] ?? '0' }}" min="0" max="100"
                            step="0.01"
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-slate-800 dark:text-white">
                        <div
                            class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none font-bold text-slate-400">
                            %</div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Percentual descontado do vendedor em cada venda.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Comportamento da
                        Taxa</label>
                    <select name="marketplace_fee_behavior"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                        @php $behavior = $settings['marketplace_fee_behavior'] ?? 'absorb'; @endphp
                        <option value="absorb" {{ $behavior === 'absorb' ? 'selected' : '' }}>Absorver (Descontar do
                            Vendedor)</option>
                        <option value="pass" {{ $behavior === 'pass' ? 'selected' : '' }}>Repassar (Adicionar ao Cliente)
                        </option>
                    </select>
                </div>
            </div>

            <div
                class="p-6 rounded-3xl bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/20 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-handshake text-blue-500 text-lg"></i>
                        <div>
                            <h4 class="font-bold text-slate-800 dark:text-white">Aprovação Manual (Permuta)</h4>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Aprovar sem pagamento financeiro.
                            </p>
                        </div>
                    </div>
                    <label for="marketplace_manual_approval_enabled" class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="marketplace_manual_approval_enabled" value="0">
                        <input type="checkbox" name="marketplace_manual_approval_enabled"
                            id="marketplace_manual_approval_enabled" value="1" class="sr-only peer" {{ ($settings['marketplace_manual_approval_enabled'] ?? 1) ? 'checked' : '' }}>
                        <div
                            class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Divisão de Vendas (Split) --}}
        <div class="border-t border-slate-100 dark:border-slate-800 pt-6">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-divide text-slate-400"></i>
                <h4 class="font-bold text-slate-700 dark:text-slate-300 text-sm">Divisão de Vendas (Split)</h4>
            </div>
            <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/30 rounded-2xl p-3 mb-4 text-xs text-amber-700 dark:text-amber-400 font-medium">
                <i class="fas fa-exclamation-triangle mr-1"></i> A soma das porcentagens deve ser exatamente <strong>100%</strong>.
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach([
                    'marketplace_split_seller_percent' => ['Vendedor (%)', '70', null, 'Valor líquido'],
                    'marketplace_split_platform_percent' => ['Plataforma (%)', '10', 'marketplace_split_platform_pix', 'Taxas, juros e repasse'],
                    'marketplace_split_traffic_percent' => ['Tráfego Pago (%)', '10', 'marketplace_split_traffic_pix', 'Fundo de marketing'],
                    'marketplace_split_superadmin_percent' => ['Superadmin (%)', '10', null, 'Participação Superadmin'],
                ] as $field => $meta)
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">{{ $meta[0] }}</label>
                    <div class="relative">
                        <input type="number" name="{{ $field }}" min="0" max="100" step="0.01"
                            value="{{ $settings[$field] ?? $meta[1] }}"
                            class="split-input w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-slate-800 dark:text-white">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none font-bold text-slate-400">%</div>
                    </div>
                    @if($meta[2])
                        <input type="text" name="{{ $meta[2] }}" 
                            value="{{ $settings[$meta[2]] ?? '' }}"
                            placeholder="Chave PIX {{ str_replace(' (%)', '', $meta[0]) }}"
                            class="w-full px-3 py-2 rounded-xl border-slate-100 dark:border-slate-800 bg-slate-100/50 dark:bg-slate-800/50 focus:border-blue-500 outline-none transition-all text-[10px] font-medium text-slate-600 dark:text-slate-300">
                    @endif
                    <p class="text-[9px] text-slate-400 italic">
                        {{ $meta[3] }}
                        @if($field === 'marketplace_split_superadmin_percent')
                            (config. no perfil)
                        @endif
                    </p>
                </div>
                @endforeach
            </div>
            <div id="split-total-warning" class="mt-3 text-red-500 text-xs font-bold hidden">
                <i class="fas fa-times-circle mr-1"></i> A soma atual é <span id="split-total-value">0</span>%. Ajuste para 100%.
            </div>
        </div>
    </div>

    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div
                class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/20 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                <i class="fas fa-truck-fast"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white text-lg">Frete Correios</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Credenciais e servicos usados nas lojas dos vendedores.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Token Bearer</label>
                    <input type="password" name="correios_api_token"
                        value="{{ $settings['correios_api_token'] ?? '' }}"
                        placeholder="Token da API Correios"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Base URL</label>
                    <input type="text" name="correios_api_base_url"
                        value="{{ $settings['correios_api_base_url'] ?? 'https://api.correios.com.br' }}"
                        placeholder="https://api.correios.com.br"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Codigo PAC</label>
                    <input type="text" name="correios_service_code_pac"
                        value="{{ $settings['correios_service_code_pac'] ?? '03298' }}"
                        placeholder="03298"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Codigo SEDEX</label>
                    <input type="text" name="correios_service_code_sedex"
                        value="{{ $settings['correios_service_code_sedex'] ?? '03220' }}"
                        placeholder="03220"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                </div>

                <div class="sm:col-span-2 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-800 p-4 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Configure aqui o token da plataforma e os codigos dos servicos usados no checkout de produtos fisicos. O vendedor precisa manter CEP e endereco preenchidos no perfil para que a cotacao funcione.
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Carousel -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                    <i class="fas fa-images"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white text-lg">Banner Rotativo (Marketplace)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Configure os slides da vitrine.</p>
                </div>
            </div>
            <label for="marketplace_hero_enabled" class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="marketplace_hero_enabled" value="0">
                <input type="checkbox" name="marketplace_hero_enabled" id="marketplace_hero_enabled" value="1"
                    class="sr-only peer" {{ ($settings['marketplace_hero_enabled'] ?? 1) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                </div>
            </label>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div
                class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-800">
                <label for="marketplace_hero_autoplay" class="relative inline-flex items-center cursor-pointer scale-90">
                    <input type="hidden" name="marketplace_hero_autoplay" value="0">
                    <input type="checkbox" name="marketplace_hero_autoplay" id="marketplace_hero_autoplay" value="1"
                        class="sr-only peer" {{ ($settings['marketplace_hero_autoplay'] ?? 1) ? 'checked' : '' }}>
                    <div
                        class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                    </div>
                </label>
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 cursor-pointer"
                    for="marketplace_hero_autoplay">Autoplay</label>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Intervalo (Segundos)</label>
                <input type="number" name="marketplace_hero_interval_seconds"
                    value="{{ $settings['marketplace_hero_interval_seconds'] ?? '6' }}" min="2" max="20"
                    class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Animação</label>
                <select name="marketplace_hero_animation"
                    class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    @php $anim = $settings['marketplace_hero_animation'] ?? 'slide'; @endphp
                    <option value="slide" {{ $anim === 'slide' ? 'selected' : '' }}>Slide (Deslizar)</option>
                    <option value="fade" {{ $anim === 'fade' ? 'selected' : '' }}>Fade (Suavizar)</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach([1, 2, 3] as $slide)
                <div
                    class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 space-y-4">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <span
                            class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-[10px]">{{ $slide }}</span>
                        Slide {{ $slide }}
                    </h4>

                    @php $imgKey = "marketplace_hero_slide_{$slide}_image"; @endphp
                    <div
                        class="w-full h-32 rounded-2xl bg-white dark:bg-slate-900 border-2 border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden relative">
                        @if($url = $getUrl($imgKey))
                            <img id="preview_{{ $imgKey }}" src="{{ $url }}" class="w-full h-full object-cover">
                        @else
                            <div class="text-center p-4">
                                <i class="fas fa-image text-2xl text-slate-200"></i>
                            </div>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <button type="button" onclick="document.getElementById('input_{{ $imgKey }}').click()"
                            class="flex-1 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Alterar
                        </button>
                        <input type="file" id="input_{{ $imgKey }}" name="{{ $imgKey }}" class="hidden" accept="image/*"
                            onchange="previewImage(this, 'preview_{{ $imgKey }}')">
                        <input type="hidden" name="remove_{{ $imgKey }}" id="remove_{{ $imgKey }}" value="0">
                        <button type="button" onclick="removeImage('{{ $imgKey }}', 'preview_{{ $imgKey }}')"
                            class="px-2 py-2 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <input type="text" name="marketplace_hero_slide_{{ $slide }}_title"
                            value="{{ $settings["marketplace_hero_slide_{$slide}_title"] ?? '' }}"
                            placeholder="Título do Slide"
                            class="w-full px-4 py-2 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 outline-none transition-all text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                        <input type="text" name="marketplace_hero_slide_{{ $slide }}_subtitle"
                            value="{{ $settings["marketplace_hero_slide_{$slide}_subtitle"] ?? '' }}"
                            placeholder="Subtítulo/Texto"
                            class="w-full px-4 py-2 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 outline-none transition-all text-xs font-medium text-slate-700 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-500">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="marketplace_hero_slide_{{ $slide }}_button_text"
                                value="{{ $settings["marketplace_hero_slide_{$slide}_button_text"] ?? '' }}"
                                placeholder="Botão"
                                class="px-3 py-2 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 outline-none transition-all text-[10px] font-bold text-slate-700 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-500">
                            <input type="text" name="marketplace_hero_slide_{{ $slide }}_button_url"
                                value="{{ $settings["marketplace_hero_slide_{$slide}_button_url"] ?? '' }}"
                                placeholder="URL (ex: /shop)"
                                class="px-3 py-2 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 outline-none transition-all text-[10px] font-medium text-slate-700 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-500">
                        </div>
                        <select name="marketplace_hero_slide_{{ $slide }}_button_target"
                            class="w-full px-3 py-2 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 focus:border-blue-600 outline-none transition-all text-[10px] font-medium">
                            <option value="_self" {{ ($settings["marketplace_hero_slide_{$slide}_button_target"] ?? '_self') == '_self' ? 'selected' : '' }}>Mesma Aba</option>
                            <option value="_blank" {{ ($settings["marketplace_hero_slide_{$slide}_button_target"] ?? '_self') == '_blank' ? 'selected' : '' }}>Nova Aba</option>
                        </select>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Exit Intent Popup -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 flex items-center justify-center">
                    <i class="fas fa-bullhornCounter fa-bullhorn"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white text-lg">Popup de Saída (Exit Intent)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tente reconverter o usuário antes que ele
                        saia.</p>
                </div>
            </div>
            <label for="marketplace_exit_enabled" class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="marketplace_exit_enabled" value="0">
                <input type="checkbox" name="marketplace_exit_enabled" id="marketplace_exit_enabled" value="1"
                    class="sr-only peer" {{ ($settings['marketplace_exit_enabled'] ?? 0) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                </div>
            </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Delay (Segundos)</label>
                        <input type="number" name="marketplace_exit_delay_seconds"
                            value="{{ $settings['marketplace_exit_delay_seconds'] ?? '15' }}" min="0" max="120"
                            class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 outline-none transition-all text-sm font-medium text-slate-800 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Código Cupom</label>
                        <input type="text" name="marketplace_exit_coupon_code"
                            value="{{ $settings['marketplace_exit_coupon_code'] ?? '' }}" placeholder="EX: SAVE10"
                            class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 outline-none transition-all text-sm font-bold text-blue-600 dark:text-blue-300 uppercase text-center placeholder:text-blue-300 dark:placeholder:text-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Título do
                        Popup</label>
                    <input type="text" name="marketplace_exit_title"
                        value="{{ $settings['marketplace_exit_title'] ?? 'Espere! Temos uma oferta pra você' }}"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 outline-none transition-all font-bold text-slate-800 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Texto da
                        Mensagem</label>
                    <textarea name="marketplace_exit_text" rows="3"
                        class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 outline-none transition-all text-sm text-slate-800 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">{{ $settings['marketplace_exit_text'] ?? 'Use um cupom e ganhe desconto agora mesmo.' }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="marketplace_exit_button_text"
                        value="{{ $settings['marketplace_exit_button_text'] ?? 'Ver ofertas' }}"
                        placeholder="Texto Botão"
                        class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 outline-none transition-all text-xs font-bold text-slate-800 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                    <input type="text" name="marketplace_exit_button_url"
                        value="{{ $settings['marketplace_exit_button_url'] ?? '/marketplace' }}"
                        placeholder="Link Botão"
                        class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 outline-none transition-all text-xs text-slate-800 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
                </div>
            </div>

            <div class="space-y-4">
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Imagem do Popup</label>
                <div
                    class="w-full h-48 rounded-3xl bg-slate-50 dark:bg-slate-950 border-2 border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden relative">
                    @if($url = $getUrl('marketplace_exit_banner_image'))
                        <img id="preview_marketplace_exit_banner_image" src="{{ $url }}" class="w-full h-full object-cover">
                    @else
                        <div class="text-center p-8">
                            <i class="fas fa-image text-4xl text-slate-200"></i>
                        </div>
                    @endif
                </div>
                <div class="flex gap-2">
                    <button type="button"
                        onclick="document.getElementById('input_marketplace_exit_banner_image').click()"
                        class="flex-1 px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Escolher Imagem
                    </button>
                    <input type="file" id="input_marketplace_exit_banner_image" name="marketplace_exit_banner_image"
                        class="hidden" accept="image/*"
                        onchange="previewImage(this, 'preview_marketplace_exit_banner_image')">
                    <input type="hidden" name="remove_marketplace_exit_banner_image"
                        id="remove_marketplace_exit_banner_image" value="0">
                    <button type="button"
                        onclick="removeImage('marketplace_exit_banner_image', 'preview_marketplace_exit_banner_image')"
                        class="px-4 py-2 border border-red-100 dark:border-red-900/20 text-red-500 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/10 transition">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Notifications -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white text-lg">Pop-ups de Sugestão (Toast)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Notificações flutuantes com eventos sugeridos.
                    </p>
                </div>
            </div>
            <label for="marketplace_events_popup_enabled" class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="marketplace_events_popup_enabled" value="0">
                <input type="checkbox" name="marketplace_events_popup_enabled" id="marketplace_events_popup_enabled"
                    value="1" class="sr-only peer" {{ ($settings['marketplace_events_popup_enabled'] ?? 1) ? 'checked' : '' }}>
                <div
                    class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                </div>
            </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Intervalo (Segundos)</label>
                <input type="number" name="marketplace_events_popup_interval_seconds"
                    value="{{ $settings['marketplace_events_popup_interval_seconds'] ?? '60' }}" min="20" max="300"
                    class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Máximo por Sessão</label>
                <input type="number" name="marketplace_events_popup_max_per_session"
                    value="{{ $settings['marketplace_events_popup_max_per_session'] ?? '3' }}" min="0" max="10"
                    class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div
                class="bg-amber-50 dark:bg-amber-900/10 p-4 rounded-2xl border border-amber-100 dark:border-amber-900/20">
                <p class="text-[10px] text-amber-800 dark:text-amber-400 font-medium leading-relaxed">
                    <i class="fas fa-info-circle mr-1"></i> Recomendamos intervalos maiores (60s+) para não atrapalhar a
                    navegação do visitante.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.split-input');
    const warning = document.getElementById('split-total-warning');
    const totalSpan = document.getElementById('split-total-value');
    if (!inputs.length || !warning) return;
    function calcSplit() {
        let total = 0;
        inputs.forEach(i => total += parseFloat(i.value || 0));
        total = Math.round(total * 100) / 100;
        if (totalSpan) totalSpan.textContent = total;
        warning.classList.toggle('hidden', total === 100);
    }
    inputs.forEach(i => i.addEventListener('input', calcSplit));
    calcSplit();
});
</script>
@endpush
