<div class="space-y-6">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Login Social</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Configure as APIs para login rápido dos usuários.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach([
            'google' => ['Google', 'fab fa-google', 'text-red-500', 'bg-red-50 dark:bg-red-900/10'],
            'facebook' => ['Facebook', 'fab fa-facebook', 'text-blue-600', 'bg-blue-50 dark:bg-blue-900/10'],
            'twitter' => ['Twitter / X', 'fab fa-twitter', 'text-sky-500', 'bg-sky-50 dark:bg-sky-900/10'],
            'linkedin' => ['LinkedIn', 'fab fa-linkedin', 'text-blue-700', 'bg-blue-50 dark:bg-blue-900/10']
        ] as $key => $data)
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden hover:border-slate-200 dark:hover:border-slate-700 transition-all group">
            <div class="p-6 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $data[3] }} {{ $data[2] }} flex items-center justify-center transition-transform group-hover:scale-110">
                        <i class="{{ $data[1] }} text-xl"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 dark:text-white">{{ $data[0] }}</h4>
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
                        {{ $key === 'facebook' ? 'App ID' : ($key === 'twitter' ? 'Client ID (API Key)' : 'Client ID') }}
                    </label>
                    <input type="text" name="social_{{ $key }}_{{ ($key === 'facebook' ? 'app_id' : 'client_id') }}" 
                           value="{{ $settings['social_'.$key.'_'.($key === 'facebook' ? 'app_id' : 'client_id')] ?? '' }}"
                           class="w-full px-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-sm font-medium text-slate-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                        {{ $key === 'facebook' ? 'App Secret' : ($key === 'twitter' ? 'Client Secret (API Secret)' : 'Client Secret') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-key text-[10px]"></i>
                        </div>
                        <input type="password" name="social_{{ $key }}_{{ ($key === 'facebook' ? 'app_secret' : 'client_secret') }}" 
                               value="{{ $settings['social_'.$key.'_'.($key === 'facebook' ? 'app_secret' : 'client_secret')] ?? '' }}"
                               class="w-full pl-9 pr-4 py-2.5 rounded-xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-sm font-medium text-slate-800 dark:text-white">
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
