@extends('panel.layouts.app')

@section('title', 'Seguranca - WAF')

@section('panel_content')
    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-2xl border border-red-300 bg-red-50 p-4 text-red-800">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="rounded-2xl border border-red-300 bg-red-50 p-4 text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h1 class="text-xl font-black text-slate-900 dark:text-white">Seguranca (WAF)</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">As alteracoes sao gravadas e confirmadas diretamente no banco.</p>
        </div>

        @if(!$hasTable)
            <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-800">
                A tabela <code>waf_settings</code> nao esta disponivel.
            </div>
        @else
            <form method="POST" action="{{ route('panel.admin.security.update') }}"
                class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    @foreach([
                        'threshold_monitor' => ['Monitor', 20],
                        'threshold_challenge' => ['Challenge', 50],
                        'threshold_block' => ['Block', 80],
                    ] as $key => [$label, $default])
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Limiar {{ $label }}</span>
                            <input type="number" name="{{ $key }}" min="0" max="100"
                                value="{{ old($key, $settings[$key] ?? $default) }}"
                                class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        </label>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Modo de operacao</span>
                        <select name="mode" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="detection-only" {{ old('mode', $settings['mode']) === 'detection-only' ? 'selected' : '' }}>Detection-only</option>
                            <option value="enforce" {{ old('mode', $settings['mode']) === 'enforce' ? 'selected' : '' }}>Enforce</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Politica em caso de falha</span>
                        <select name="fail_policy" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="allow" {{ old('fail_policy', $settings['fail_policy']) === 'allow' ? 'selected' : '' }}>Permitir</option>
                            <option value="block" {{ old('fail_policy', $settings['fail_policy']) === 'block' ? 'selected' : '' }}>Bloquear</option>
                        </select>
                    </label>
                </div>

                <label class="block">
                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Rotas isentas, uma por linha</span>
                    <textarea name="exempt_routes" rows="7"
                        class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">{{ old('exempt_routes', implode("\n", $settings['exempt_routes'] ?? [])) }}</textarea>
                </label>

                <button type="submit" class="rounded-2xl bg-blue-600 px-6 py-3 font-bold text-white hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Gravar e confirmar no banco
                </button>
            </form>
        @endif
    </div>
@endsection
