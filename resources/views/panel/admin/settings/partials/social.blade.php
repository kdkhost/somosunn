@php
    $supportedProviders = ['google', 'facebook', 'linkedin'];
    $socialProviders = [
        'google' => [
            'label' => 'Google',
            'icon' => 'fab fa-google',
            'iconColor' => 'text-red-500',
            'iconBg' => 'bg-red-50 dark:bg-red-900/10',
            'idLabel' => 'Client ID',
            'secretLabel' => 'Client Secret',
            'consoleUrl' => 'https://console.cloud.google.com/apis/credentials',
        ],
        'facebook' => [
            'label' => 'Facebook',
            'icon' => 'fab fa-facebook',
            'iconColor' => 'text-blue-600',
            'iconBg' => 'bg-blue-50 dark:bg-blue-900/10',
            'idLabel' => 'App ID',
            'secretLabel' => 'App Secret',
            'consoleUrl' => 'https://developers.facebook.com/apps/',
        ],
        'twitter' => [
            'label' => 'Twitter / X',
            'icon' => 'fab fa-twitter',
            'iconColor' => 'text-sky-500',
            'iconBg' => 'bg-sky-50 dark:bg-sky-900/10',
            'idLabel' => 'Client ID (API Key)',
            'secretLabel' => 'Client Secret (API Secret)',
            'consoleUrl' => 'https://developer.x.com/en/portal/dashboard',
        ],
        'linkedin' => [
            'label' => 'LinkedIn',
            'icon' => 'fab fa-linkedin',
            'iconColor' => 'text-blue-700',
            'iconBg' => 'bg-blue-50 dark:bg-blue-900/10',
            'idLabel' => 'Client ID',
            'secretLabel' => 'Client Secret',
            'consoleUrl' => 'https://www.linkedin.com/developers/apps',
        ],
    ];
@endphp

<div class="space-y-6">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Login Social</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Configure as APIs para login rapido dos usuarios.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h4 class="font-bold text-slate-800 dark:text-white">Instrucoes e callback URL</h4>
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">OAuth 2.0</span>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Para cada provedor: crie um app no portal oficial, adicione a callback URL abaixo, copie as credenciais e cole nos campos.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($socialProviders as $key => $provider)
                @php
                    $callbackUrl = route('social.callback', ['provider' => $key]);
                    $isSupported = in_array($key, $supportedProviders, true);
                @endphp
                <div class="rounded-2xl border border-slate-100 dark:border-slate-800 p-4 bg-slate-50/40 dark:bg-slate-950/40 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <i class="{{ $provider['icon'] }} {{ $provider['iconColor'] }}"></i>
                            <span class="text-xs font-black text-slate-700 dark:text-slate-300">{{ $provider['label'] }}</span>
                        </div>
                        @if($isSupported)
                            <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Suportado</span>
                        @else
                            <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">Nao ativo no backend</span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Redirect URI / Callback URL</label>
                        <div class="flex gap-2">
                            <input type="text" readonly value="{{ $callbackUrl }}"
                                class="w-full px-3 py-2 rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-medium text-slate-700 dark:text-slate-300">
                            <button type="button" data-copy-social-url="{{ $callbackUrl }}"
                                class="px-3 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                                Copiar
                            </button>
                        </div>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer text-[11px] font-bold text-blue-600 dark:text-blue-400">Como configurar {{ $provider['label'] }}</summary>
                        <ol class="mt-2 list-decimal pl-4 space-y-1 text-xs text-slate-600 dark:text-slate-400">
                            <li>Acesse o portal do desenvolvedor: <a href="{{ $provider['consoleUrl'] }}" target="_blank" rel="noopener" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">{{ $provider['consoleUrl'] }}</a>.</li>
                            <li>Crie um app OAuth 2.0 (tipo Web).</li>
                            <li>No app, adicione a callback URL exibida neste bloco.</li>
                            <li>Copie {{ $provider['idLabel'] }} e {{ $provider['secretLabel'] }} para os campos abaixo.</li>
                            <li>Ative o switch do provedor e salve as alteracoes.</li>
                        </ol>
                        @if(!$isSupported)
                            <p class="mt-2 text-[11px] text-amber-600 dark:text-amber-400 font-semibold">
                                Observacao: este provedor aparece na configuracao, mas nao esta ativo na autenticacao atual do backend.
                            </p>
                        @endif
                    </details>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($socialProviders as $key => $provider)
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden hover:border-slate-200 dark:hover:border-slate-700 transition-all group">
                <div class="p-6 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $provider['iconBg'] }} {{ $provider['iconColor'] }} flex items-center justify-center transition-transform group-hover:scale-110">
                            <i class="{{ $provider['icon'] }} text-xl"></i>
                        </div>
                        <h4 class="font-bold text-slate-800 dark:text-white">{{ $provider['label'] }}</h4>
                    </div>
                    <div class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="social_{{ $key }}_enabled" value="0">
                        <input type="checkbox" name="social_{{ $key }}_enabled" id="social_{{ $key }}_enabled" value="1" class="sr-only peer" {{ ($settings['social_'.$key.'_enabled'] ?? 0) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                            {{ $provider['idLabel'] }}
                        </label>
                        <input type="text" name="social_{{ $key }}_client_id"
                            value="{{ $settings['social_'.$key.'_client_id'] ?? ($key === 'facebook' ? ($settings['social_facebook_app_id'] ?? '') : '') }}"
                            class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-sm font-medium text-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                            {{ $provider['secretLabel'] }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-key text-[10px]"></i>
                            </div>
                            <input type="password" name="social_{{ $key }}_client_secret"
                                value="{{ $settings['social_'.$key.'_client_secret'] ?? ($key === 'facebook' ? ($settings['social_facebook_app_secret'] ?? '') : '') }}"
                                class="w-full pl-9 pr-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-sm font-medium text-slate-800 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-copy-social-url]').forEach(function (button) {
                if (button.dataset.bound === '1') {
                    return;
                }
                button.dataset.bound = '1';

                button.addEventListener('click', async function () {
                    const text = this.getAttribute('data-copy-social-url') || '';
                    if (!text) {
                        return;
                    }

                    const original = this.textContent;
                    try {
                        await navigator.clipboard.writeText(text);
                        this.textContent = 'Copiado!';
                        this.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        this.classList.add('bg-emerald-600');
                        setTimeout(() => {
                            this.textContent = original;
                            this.classList.remove('bg-emerald-600');
                            this.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        }, 1200);
                    } catch (e) {
                        if (typeof window.toastr !== 'undefined') {
                            window.toastr.error('Nao foi possivel copiar a URL.');
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Nao foi possivel copiar a URL.'
                            });
                        }
                    }
                });
            });
        });
    </script>
@endpush
