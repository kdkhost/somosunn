<div class="space-y-8">
    @php
        $maintenanceManualEnabled = (string) ($settings['maintenance_enabled'] ?? '0') === '1';
        $maintenanceAutoEnabled = (string) ($settings['maintenance_auto_enabled'] ?? '0') === '1';

        $maintenanceStartAt = '';
        $maintenanceEndAt = '';
        $maintenanceStartCarbon = null;
        $maintenanceEndCarbon = null;

        try {
            if (!empty($settings['maintenance_start_at'])) {
                $maintenanceStartCarbon = \Carbon\Carbon::parse($settings['maintenance_start_at']);
                $maintenanceStartAt = $maintenanceStartCarbon->format('Y-m-d\TH:i');
            }
        } catch (\Throwable $e) {
            $maintenanceStartAt = '';
        }

        try {
            if (!empty($settings['maintenance_end_at'])) {
                $maintenanceEndCarbon = \Carbon\Carbon::parse($settings['maintenance_end_at']);
                $maintenanceEndAt = $maintenanceEndCarbon->format('Y-m-d\TH:i');
            }
        } catch (\Throwable $e) {
            $maintenanceEndAt = '';
        }

        $maintenanceScheduledActive = false;
        if ($maintenanceAutoEnabled && $maintenanceStartCarbon) {
            $maintenanceScheduledActive = now()->greaterThanOrEqualTo($maintenanceStartCarbon)
                && (!$maintenanceEndCarbon || now()->lessThanOrEqualTo($maintenanceEndCarbon));
        }

        $maintenanceEffective = $maintenanceManualEnabled || $maintenanceScheduledActive;
    @endphp
    <!-- Security (reCAPTCHA) -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div
                class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 flex items-center justify-center">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Segurança (Google reCAPTCHA v3)</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Site Key</label>
                <input type="text" name="recaptcha_v3_site_key"
                    value="{{ $settings['recaptcha_v3_site_key'] ?? config('services.recaptcha.site_key') }}"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Secret Key</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-key"></i>
                    </div>
                    <input type="password" name="recaptcha_v3_secret_key"
                        value="{{ $settings['recaptcha_v3_secret_key'] ?? config('services.recaptcha.v3_secret') }}"
                        class="w-full pl-11 pr-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                </div>
            </div>
        </div>
        <div class="w-1/3">
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Score Mínimo (0.0 -
                1.0)</label>
            <input type="number" step="0.1" min="0" max="1" name="recaptcha_v3_min_score"
                value="{{ $settings['recaptcha_v3_min_score'] ?? config('services.recaptcha.v3_min_score', 0.5) }}"
                class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
        </div>
    </div>

    <!-- Limits & Uploads -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div
                class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <i class="fas fa-server"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Limites e Uploads</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div
                class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 space-y-4">
                <div class="flex items-center gap-3">
                    <i class="fas fa-video text-blue-500"></i>
                    <h4 class="font-bold text-slate-700 dark:text-slate-300">Upload de Vídeo</h4>
                </div>
                <div class="flex items-center gap-3">
                    <input type="number" name="video_max_mb" value="{{ $settings['video_max_mb'] ?? '1024' }}"
                        class="w-32 px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-slate-800 dark:text-white text-center">
                    <span class="font-bold text-slate-500">MB</span>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Formatos
                        Permitidos</label>
                    <input type="text" name="allowed_video_formats"
                        value="{{ $settings['allowed_video_formats'] ?? implode(',', config('uploads.allowed_video_formats', [])) }}"
                        placeholder="mp4,webm"
                        class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-sm font-medium">
                </div>
            </div>

            <div
                class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 space-y-4">
                <div class="flex items-center gap-3">
                    <i class="fas fa-file-alt text-orange-500"></i>
                    <h4 class="font-bold text-slate-700 dark:text-slate-300">Upload de Documentos</h4>
                </div>
                <div class="flex items-center gap-3">
                    <input type="number" name="document_max_mb" value="{{ $settings['document_max_mb'] ?? '50' }}"
                        class="w-32 px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-slate-800 dark:text-white text-center">
                    <span class="font-bold text-slate-500">MB</span>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Formatos
                        Permitidos</label>
                    <input type="text" name="allowed_document_formats"
                        value="{{ $settings['allowed_document_formats'] ?? implode(',', config('uploads.allowed_document_formats', [])) }}"
                        placeholder="pdf,docx,zip"
                        class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-sm font-medium">
                </div>
            </div>
        </div>
    </div>

    <!-- Storage (Filesystem) -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center gap-3 mb-2">
            <div
                class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i class="fas fa-hdd"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Armazenamento (Filesystem)</h3>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Disco Principal de
                    Uploads</label>
                <select name="uploads_storage_disk" id="storage_disk_select"
                    class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    <option value="public" {{ ($settings['uploads_storage_disk'] ?? 'public') === 'public' ? 'selected' : '' }}>Local (Public Storage) - Padrão</option>
                    <option value="s3" {{ ($settings['uploads_storage_disk'] ?? '') === 's3' ? 'selected' : '' }}>Amazon
                        S3 / Compatível (MinIO, DigitalOcean, etc)</option>
                </select>
            </div>

            <div id="s3_config_container"
                class="space-y-6 pt-6 {{ ($settings['uploads_storage_disk'] ?? 'public') === 's3' ? '' : 'hidden' }}">
                <div
                    class="p-6 rounded-3xl bg-blue-50/30 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/20 space-y-6">
                    <h4 class="font-bold text-blue-800 dark:text-blue-400 flex items-center gap-2">
                        <i class="fab fa-aws"></i> Configurações S3
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Access
                                Key ID</label>
                            <input type="text" name="s3_key" value="{{ $settings['s3_key'] ?? '' }}"
                                class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Secret
                                Access Key</label>
                            <input type="password" name="s3_secret" value="{{ $settings['s3_secret'] ?? '' }}"
                                class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Bucket
                                Name</label>
                            <input type="text" name="s3_bucket" value="{{ $settings['s3_bucket'] ?? '' }}"
                                class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Region</label>
                            <input type="text" name="s3_region" value="{{ $settings['s3_region'] ?? '' }}"
                                placeholder="us-east-1"
                                class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white text-center">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Endpoint
                                (Opcional)</label>
                            <input type="text" name="s3_endpoint" value="{{ $settings['s3_endpoint'] ?? '' }}"
                                class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Public URL /
                            CDN (Opcional)</label>
                        <input type="text" name="s3_url" value="{{ $settings['s3_url'] ?? '' }}"
                            placeholder="https://cdn.seusite.com"
                            class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="s3_path_style" value="0">
                            <input type="checkbox" name="s3_path_style" id="s3_path_style" value="1"
                                class="sr-only peer" {{ ($settings['s3_path_style'] ?? 0) ? 'checked' : '' }}>
                            <div
                                class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </div>
                        <label class="text-xs font-bold text-slate-600 dark:text-slate-400 cursor-pointer"
                            for="s3_path_style">Forçar Path-Style Endpoint (Obrigatório para MinIO)</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Mode -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-8 space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i class="fas fa-tools"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white text-lg">Modo de manutencao</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ativacao manual e agendada com pagina personalizada.</p>
                </div>
            </div>
            <span
                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold {{ $maintenanceEffective ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                <span class="w-2 h-2 rounded-full {{ $maintenanceEffective ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                {{ $maintenanceEffective ? 'ATIVO NO SITE' : 'DESATIVADO' }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div
                class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-slate-700 dark:text-slate-300">Ativacao manual</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Forca o site inteiro para manutencao agora.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="maintenance_enabled" value="0">
                        <input type="checkbox" id="maintenance_enabled" name="maintenance_enabled" value="1"
                            class="sr-only peer" {{ $maintenanceManualEnabled ? 'checked' : '' }}>
                        <div
                            class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500">
                        </div>
                    </label>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" data-maintenance-action="manual-on"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 text-sm font-bold transition">
                        <i class="fas fa-power-off"></i> Ativar agora
                    </button>
                    <button type="button" data-maintenance-action="manual-off"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 text-sm font-bold transition">
                        <i class="fas fa-check-circle"></i> Desativar agora
                    </button>
                </div>
            </div>

            <div
                class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-slate-700 dark:text-slate-300">Ativacao automatica</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Liga e desliga conforme janela de horario.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="maintenance_auto_enabled" value="0">
                        <input type="checkbox" id="maintenance_auto_enabled" name="maintenance_auto_enabled" value="1"
                            class="sr-only peer" {{ $maintenanceAutoEnabled ? 'checked' : '' }}>
                        <div
                            class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                        </div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Inicio</label>
                        <input type="datetime-local" name="maintenance_start_at" value="{{ $maintenanceStartAt }}"
                            class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Fim (opcional)</label>
                        <input type="datetime-local" name="maintenance_end_at" value="{{ $maintenanceEndAt }}"
                            class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" data-maintenance-action="auto-on"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-bold transition">
                        <i class="fas fa-clock"></i> Habilitar automatico
                    </button>
                    <button type="button" data-maintenance-action="auto-off"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 text-sm font-bold transition">
                        <i class="fas fa-ban"></i> Desabilitar automatico
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Titulo da pagina</label>
                <input type="text" name="maintenance_title"
                    value="{{ $settings['maintenance_title'] ?? 'Sistema em manutencao' }}"
                    class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Subtitulo</label>
                <input type="text" name="maintenance_subtitle"
                    value="{{ $settings['maintenance_subtitle'] ?? 'Estamos melhorando sua experiencia.' }}"
                    class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Mensagem principal</label>
                <textarea name="maintenance_message" rows="3"
                    class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">{{ $settings['maintenance_message'] ?? 'Voltamos em instantes. Obrigado pela paciencia.' }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Texto do botao</label>
                <input type="text" name="maintenance_button_label"
                    value="{{ $settings['maintenance_button_label'] ?? 'Ir para a home' }}"
                    class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">URL do botao</label>
                <input type="text" name="maintenance_button_url"
                    value="{{ $settings['maintenance_button_url'] ?? route('home') }}"
                    class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">E-mail de suporte</label>
                <input type="email" name="maintenance_contact_email"
                    value="{{ $settings['maintenance_contact_email'] ?? ($settings['smtp_from_email'] ?? '') }}"
                    class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('storage_disk_select').addEventListener('change', function () {
        const container = document.getElementById('s3_config_container');
        if (this.value === 's3') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    });

    document.querySelectorAll('[data-maintenance-action]').forEach((button) => {
        button.addEventListener('click', function () {
            const form = this.closest('form');
            const manualInput = document.getElementById('maintenance_enabled');
            const autoInput = document.getElementById('maintenance_auto_enabled');
            const action = this.getAttribute('data-maintenance-action');

            if (!form || !manualInput || !autoInput) return;

            if (action === 'manual-on') manualInput.checked = true;
            if (action === 'manual-off') manualInput.checked = false;
            if (action === 'auto-on') autoInput.checked = true;
            if (action === 'auto-off') autoInput.checked = false;

            form.requestSubmit();
        });
    });
</script>
