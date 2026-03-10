@php
    $apiTokens = $apiTokens ?? collect();
    $apiTokensEnabled = $apiTokensEnabled ?? false;
    $apiTokenIpTrackingEnabled = $apiTokenIpTrackingEnabled ?? false;
    $apiTokenPlainText = $apiTokenPlainText ?? null;
    $apiTokenDeviceName = $apiTokenDeviceName ?? null;
@endphp

<section id="affiliateApiTokensSection" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white">Acesso API pessoal</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Gere tokens por dispositivo, copie na hora, renomeie integrações e acompanhe último uso e IP.
            </p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
            <i class="fas fa-key text-[10px]"></i>
            Tokens pessoais
        </div>
    </div>

    @if(session('success'))
        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->has('api_tokens'))
        <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-300">
            {{ $errors->first('api_tokens') }}
        </div>
    @endif

    @if(!$apiTokensEnabled)
        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300">
            A tabela de tokens da API ainda não está disponível neste ambiente. Rode as migrations para liberar esta área.
        </div>
    @else
        @if($apiTokenPlainText)
            <div class="mt-5 rounded-3xl border border-blue-200 bg-blue-50/80 p-5 dark:border-blue-900/60 dark:bg-blue-950/30">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">Copie agora</p>
                        <h3 class="mt-2 text-base font-black text-slate-900 dark:text-white">Token gerado para {{ $apiTokenDeviceName ?: 'integração' }}</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Este token aparece uma única vez. Se perder, gere outro e revogue o antigo.
                        </p>
                    </div>
                    <button type="button"
                        onclick="copyReferralMaterial(this)"
                        data-copy-text="{{ e($apiTokenPlainText) }}"
                        data-track-channel="api-token"
                        data-target-url="{{ url('/api/v1/affiliate/overview') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700">
                        <i class="fas fa-copy"></i>
                        Copiar token
                    </button>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <pre class="overflow-x-auto whitespace-pre-wrap break-all text-sm leading-6 text-slate-800 dark:text-slate-200">{{ $apiTokenPlainText }}</pre>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Exemplo de uso</p>
                    <pre class="mt-3 overflow-x-auto whitespace-pre-wrap rounded-2xl bg-slate-950 px-4 py-3 text-xs leading-6 text-slate-200">Authorization: Bearer {{ $apiTokenPlainText }}</pre>
                </div>
            </div>
        @endif

        @if(!$apiTokenIpTrackingEnabled)
            <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300">
                O campo de IP ainda não existe neste ambiente. Depois de rodar `php artisan migrate`, esta tela também mostrará o último IP de uso.
            </div>
        @endif

        <div class="mt-6 grid gap-6 xl:grid-cols-[0.9fr,1.1fr]">
            <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                <h3 class="text-base font-black text-slate-900 dark:text-white">Gerar novo token</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Use um nome claro para o dispositivo, blog, CRM ou página privada que vai consumir sua API.
                </p>

                <form action="{{ route('panel.referral.tokens.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="device_name" class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Dispositivo / integração</label>
                        <input
                            id="device_name"
                            name="device_name"
                            type="text"
                            maxlength="120"
                            value="{{ old('device_name') }}"
                            placeholder="Ex.: blog-marce, painel-privado, crm-comercial"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-blue-400 dark:focus:ring-blue-900/40"
                        >
                        @error('device_name')
                            <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700">
                        <i class="fas fa-key"></i>
                        Gerar token agora
                    </button>
                </form>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Tokens ativos</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Renomeie por dispositivo, acompanhe o último uso e revogue o que não precisa mais.
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-slate-200 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        {{ $apiTokens->count() }} ativo{{ $apiTokens->count() !== 1 ? 's' : '' }}
                    </span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($apiTokens as $token)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="font-black text-slate-900 dark:text-white">{{ $token->name }}</h4>
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                            Token #{{ $token->id }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        Criado em {{ optional($token->created_at)->format('d/m/Y H:i') ?: '—' }}
                                    </p>
                                    <div class="flex flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold dark:bg-slate-800">
                                            Último uso: {{ optional($token->last_used_at)->format('d/m/Y H:i') ?: 'Nunca usado' }}
                                        </span>
                                        <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold dark:bg-slate-800">
                                            IP: {{ $token->last_used_ip ?: 'Indisponível' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex w-full flex-col gap-2 xl:w-auto xl:min-w-[18rem]">
                                    <form action="{{ route('panel.referral.tokens.update', $token->id) }}" method="POST" class="flex flex-col gap-2 sm:flex-row">
                                        @csrf
                                        @method('PUT')
                                        <input
                                            type="text"
                                            name="device_name"
                                            maxlength="120"
                                            value="{{ old('device_name', $token->name) }}"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-400 dark:focus:ring-blue-900/40"
                                        >
                                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                                            <i class="fas fa-pen"></i>
                                            Renomear
                                        </button>
                                    </form>

                                    <form action="{{ route('panel.referral.tokens.destroy', $token->id) }}" method="POST"
                                        data-confirm-title="Revogar token?"
                                        data-confirm-text="Revogar este token agora?"
                                        data-confirm-icon="warning">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-bold text-white transition-all hover:bg-rose-700">
                                            <i class="fas fa-ban"></i>
                                            Revogar token
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-6 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                            Nenhum token ativo ainda. Gere o primeiro token ao lado para usar a API em blog, página privada ou painel próprio.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</section>
