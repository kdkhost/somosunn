<div class="space-y-6">
    <!-- Player Toggle -->
    <div class="flex items-center justify-between p-4 bg-blue-50/50 dark:bg-blue-900/10 rounded-2xl border border-blue-100 dark:border-blue-900/30">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i class="fas fa-play-circle text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-slate-800 dark:text-white">Player de Vídeo Plyr</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 text-sm">Ative ou desative o player personalizado para as aulas.</p>
            </div>
        </div>
        <label for="video_player_enabled" class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="video_player_enabled" id="video_player_enabled" value="1" class="sr-only peer" {{ ($settings['video_player_enabled'] ?? 1) ? 'checked' : '' }}>
            <div class="w-12 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
        </label>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Appearance & Behavior -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i class="fas fa-palette"></i>
                </div>
                <h3 class="font-bold text-slate-800 dark:text-white">Aparência</h3>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Cor Principal</label>
                @php
                    $videoPlyrColorRaw = (string) ($settings['video_plyr_color'] ?? ($settings['site_color_primary'] ?? '#1F5EDB'));
                    $videoPlyrColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $videoPlyrColorRaw) ? strtoupper($videoPlyrColorRaw) : '#1F5EDB';
                @endphp
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <input type="text" id="video_plyr_color_input" name="video_plyr_color" value="{{ $videoPlyrColor }}"
                               class="w-full px-4 py-3 pr-14 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white colorpicker-input uppercase tracking-wide"
                               autocomplete="off" spellcheck="false">
                        <span id="video_plyr_color_swatch"
                              class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 inline-flex h-7 w-7 rounded-lg border border-slate-200 dark:border-slate-700 shadow-inner"
                              style="background-color: {{ $videoPlyrColor }};"></span>
                    </div>
                    <label for="video_plyr_color_picker"
                           class="relative inline-flex items-center justify-center w-12 h-12 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 hover:border-blue-300 dark:hover:border-blue-700 transition-all cursor-pointer">
                        <input type="color" id="video_plyr_color_picker" value="{{ $videoPlyrColor }}"
                               class="absolute inset-0 opacity-0 cursor-pointer">
                        <i class="fas fa-eye-dropper text-slate-500 dark:text-slate-400"></i>
                    </label>
                </div>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Use o color pick para selecionar a cor.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Seek (Segundos)</label>
                    <input type="number" name="video_plyr_seek_time" value="{{ $settings['video_plyr_seek_time'] ?? '10' }}"
                           class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Volume (0-1)</label>
                    <input type="number" step="0.1" min="0" max="1" name="video_plyr_volume" value="{{ $settings['video_plyr_volume'] ?? '0.8' }}"
                           class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-y-3 pt-2">
                @foreach([
                    'video_plyr_autoplay' => 'Autoplay',
                    'video_plyr_muted' => 'Mudo Inicial',
                    'video_plyr_click_to_play' => 'Clique Tela',
                    'video_plyr_disable_context_menu' => 'Bloquear Menu'
                ] as $name => $label)
                <div class="flex items-center gap-3">
                    <label for="{{ $name }}" class="relative inline-flex items-center cursor-pointer scale-90">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="1" class="sr-only peer" {{ ($settings[$name] ?? 0) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-400 cursor-pointer" for="{{ $name }}">{{ $label }}</label>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Visible Controls -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <h3 class="font-bold text-slate-800 dark:text-white">Controles Visíveis</h3>
            </div>

            <div class="grid grid-cols-2 gap-y-2">
                @php
                    $currentControls = explode(',', $settings['video_plyr_controls'] ?? 'play,progress,current-time,mute,volume,settings,fullscreen');
                    $availableControls = ['play-large', 'restart', 'rewind', 'play', 'fast-forward', 'progress', 'current-time', 'duration', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'download', 'fullscreen'];
                @endphp
                <input type="hidden" name="video_plyr_controls" value="{{ implode(',', $currentControls) }}">
                @foreach($availableControls as $control)
                <div class="flex items-center gap-2">
                    <input type="checkbox" value="{{ $control }}" id="ctrl_{{ $control }}" 
                           class="plyr-control-checkbox w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/20"
                           {{ in_array($control, $currentControls) ? 'checked' : '' }}>
                    <label for="ctrl_{{ $control }}" class="text-[11px] font-bold text-slate-500 uppercase tracking-tight cursor-pointer">{{ str_replace('-', ' ', $control) }}</label>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Security & Watermark -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 flex items-center justify-center">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white">Proteção de Conteúdo</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Configure marca d'água e anti-pirataria.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Image Watermark -->
            <div class="space-y-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800">
                <div class="flex justify-between items-center">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Marca D'água (Imagem)</label>
                    <label for="video_watermark_enabled" class="relative inline-flex items-center cursor-pointer scale-75">
                        <input type="hidden" name="video_watermark_enabled" value="0">
                        <input type="checkbox" name="video_watermark_enabled" id="video_watermark_enabled" value="1" class="sr-only peer" {{ ($settings['video_watermark_enabled'] ?? 0) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div class="flex flex-col items-center gap-4">
                    <div class="w-32 h-32 rounded-2xl bg-white dark:bg-slate-900 border-2 border-dashed border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden relative group">
                        @if($url = $getUrl('watermark_image'))
                            <img id="watermark_preview" src="{{ $url }}" class="w-full h-full object-contain">
                        @else
                            <div id="watermark_placeholder" class="text-center p-4">
                                <i class="fas fa-image text-2xl text-slate-300 mb-1"></i>
                                <p class="text-[10px] text-slate-400">Sem imagem</p>
                            </div>
                        @endif
                    </div>
                    <div class="flex gap-2 w-full">
                        <button type="button" onclick="document.getElementById('input_watermark').click()" 
                                class="flex-1 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Escolher
                        </button>
                        <input type="file" id="input_watermark" name="watermark_image" class="hidden" accept="image/*" onchange="previewImage(this, 'watermark_preview')">
                        <input type="hidden" name="remove_watermark_image" id="remove_watermark_image" value="0">
                        <button type="button" onclick="document.getElementById('remove_watermark_image').value='1'; const wp=document.getElementById('watermark_preview'); const ph=document.getElementById('watermark_placeholder'); if(wp){wp.src='';wp.classList.add('hidden');} if(ph){ph.classList.remove('hidden');}" class="px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dynamic Text -->
            @php
                $watermarkTemplate = trim((string) ($settings['video_watermark_text_template'] ?? ''));
                $watermarkTemplate = $watermarkTemplate !== '' ? $watermarkTemplate : '{name} - {email}';

                $watermarkOpacity = trim((string) ($settings['video_watermark_opacity'] ?? ''));
                $watermarkOpacity = $watermarkOpacity !== '' ? $watermarkOpacity : '0.5';

                $watermarkPositionRaw = trim((string) ($settings['video_watermark_position'] ?? ''));
                $allowedWatermarkPositions = ['top-right', 'top-left', 'bottom-right', 'bottom-left'];
                $watermarkPosition = in_array($watermarkPositionRaw, $allowedWatermarkPositions, true) ? $watermarkPositionRaw : 'top-right';
            @endphp
            <div class="space-y-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800">
                <div class="flex justify-between items-center">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Marca D'água Dinâmica</label>
                    <label for="video_watermark_text_enabled" class="relative inline-flex items-center cursor-pointer scale-75">
                        <input type="hidden" name="video_watermark_text_enabled" value="0">
                        <input type="checkbox" name="video_watermark_text_enabled" id="video_watermark_text_enabled" value="1" class="sr-only peer" {{ ($settings['video_watermark_text_enabled'] ?? 0) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Template</label>
                        <input type="text" name="video_watermark_text_template" value="{{ $watermarkTemplate }}"
                               class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-sm font-medium text-slate-800 dark:text-white">
                        <p class="text-[10px] text-slate-400 mt-1">Tags: {name}, {email}, {cpf}, {id}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Opacidade</label>
                            <input type="number" step="0.1" name="video_watermark_opacity" value="{{ $watermarkOpacity }}"
                                   class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-sm font-medium text-slate-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Posição</label>
                            <select name="video_watermark_position" class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-sm font-medium text-slate-800 dark:text-white">
                                <option value="top-right" {{ $watermarkPosition === 'top-right' ? 'selected' : '' }}>Topo Dir</option>
                                <option value="top-left" {{ $watermarkPosition === 'top-left' ? 'selected' : '' }}>Topo Esq</option>
                                <option value="bottom-right" {{ $watermarkPosition === 'bottom-right' ? 'selected' : '' }}>Inf Dir</option>
                                <option value="bottom-left" {{ $watermarkPosition === 'bottom-left' ? 'selected' : '' }}>Inf Esq</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced JSON Options -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
        <button type="button" onclick="document.getElementById('advancedJson').classList.toggle('hidden')"
            class="flex items-center gap-2 text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
            <i class="fas fa-cogs"></i> Configurações Avançadas (JSON)
        </button>
        <div id="advancedJson" class="hidden mt-4">
            <textarea name="video_plyr_options_json" rows="5"
                class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-sm font-mono text-slate-800 dark:text-white">{{ $settings['video_plyr_options_json'] ?? '' }}</textarea>
            <p class="text-xs text-slate-400 mt-2">Cuidado: o JSON gerado pelos checkboxes acima sobrescreverá este campo ao salvar, a menos que você modifique o script JS.</p>
        </div>
    </div>
</div>

<script>
    (function initColorPick() {
        const textInput = document.getElementById('video_plyr_color_input');
        const pickerInput = document.getElementById('video_plyr_color_picker');
        const swatch = document.getElementById('video_plyr_color_swatch');
        const fallback = '#1F5EDB';

        if (!textInput || !pickerInput) {
            return;
        }

        const normalizeHexColor = (value) => {
            const cleaned = String(value || '').trim().toUpperCase();
            const withHash = cleaned.startsWith('#') ? cleaned : `#${cleaned}`;
            return /^#[0-9A-F]{6}$/.test(withHash) ? withHash : null;
        };

        const applyColor = (value) => {
            const valid = normalizeHexColor(value) || fallback;
            textInput.value = valid;
            pickerInput.value = valid;
            if (swatch) {
                swatch.style.backgroundColor = valid;
            }
        };

        applyColor(textInput.value);

        pickerInput.addEventListener('input', function () {
            applyColor(this.value);
        });

        textInput.addEventListener('input', function () {
            const partial = normalizeHexColor(this.value);
            if (partial) {
                pickerInput.value = partial;
                if (swatch) {
                    swatch.style.backgroundColor = partial;
                }
            }
        });

        textInput.addEventListener('blur', function () {
            applyColor(this.value);
        });
    })();

    function previewImage(input, previewId) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                if (preview) {
                    preview.src = e.target.result;
                } else {
                    // Create preview if placeholder
                    const container = input.closest('.upload-box') || input.parentElement.previousElementSibling;
                    container.innerHTML = `<img id="${previewId}" src="${e.target.result}" class="w-full h-full object-contain">`;
                }
            }
            reader.readAsDataURL(file);
        }
    }

    // Sync Plyr Controls
    document.querySelectorAll('.plyr-control-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
            const controls = Array.from(document.querySelectorAll('.plyr-control-checkbox:checked')).map(c => c.value);
            document.querySelector('input[name="video_plyr_controls"]').value = controls.join(',');
        });
    });
</script>
