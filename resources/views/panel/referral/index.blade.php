@extends('panel.layouts.app')

@section('title', 'Programa de Indicações')

@section('panel_content')
@php
    $pointsRule = \App\Models\PointsRule::where('key', 'referral')->where('active', true)->first();
    $pointsPerReferral = $pointsRule?->points ?? 0;
    $conversionRate = $totalReferred > 0 ? round(($convertedCount / $totalReferred) * 100) : 0;
    $potentialPoints = $pendingCount * $pointsPerReferral;
@endphp

<div class="space-y-8">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Programa de Indicações</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1 font-medium">
                Compartilhe seu link. Quando alguém que você indicou <strong class="text-slate-700 dark:text-slate-300">assinar um plano pago</strong>, você recebe pontos automaticamente.
            </p>
        </div>
        @if($pointsPerReferral && $pendingCount > 0)
            <div class="shrink-0 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl px-5 py-3 text-center">
                <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold uppercase tracking-wide">Potencial pendente</p>
                <p class="text-2xl font-black text-amber-700 dark:text-amber-300">+{{ number_format($potentialPoints) }} pts</p>
                <p class="text-xs text-amber-600/80 dark:text-amber-500">de {{ $pendingCount }} indicado{{ $pendingCount != 1 ? 's' : '' }} sem plano</p>
            </div>
        @endif
    </div>

    {{-- ===== CARDS RESUMO ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

        {{-- Total indicados --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl sm:rounded-3xl p-4 sm:p-5 text-white shadow-lg shadow-blue-500/20 flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-user-plus text-lg sm:text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-white/80 text-xs font-semibold truncate">Total indicados</p>
                <p class="text-2xl sm:text-3xl font-black leading-tight">{{ $totalReferred }}</p>
            </div>
        </div>

        {{-- Convertidos (pagaram) --}}
        <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl sm:rounded-3xl p-4 sm:p-5 text-white shadow-lg shadow-emerald-500/20 flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-check-circle text-lg sm:text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-white/80 text-xs font-semibold truncate">Convertidos</p>
                <p class="text-2xl sm:text-3xl font-black leading-tight">{{ $convertedCount }}</p>
                @if($totalReferred > 0)
                    <p class="text-white/70 text-xs">{{ $conversionRate }}%</p>
                @endif
            </div>
        </div>

        {{-- Pontos ganhos com indicações --}}
        <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl sm:rounded-3xl p-4 sm:p-5 text-white shadow-lg shadow-amber-500/20 flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-coins text-lg sm:text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-white/80 text-xs font-semibold truncate">Pontos ganhos</p>
                <p class="text-2xl sm:text-3xl font-black leading-tight">{{ number_format($totalReferralPoints) }}</p>
            </div>
        </div>

        {{-- Pontos por indicação --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl sm:rounded-3xl p-4 sm:p-5 flex items-center gap-3 shadow-sm">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 dark:bg-green-900/40 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-gift text-green-600 dark:text-green-400 text-lg sm:text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-slate-500 dark:text-slate-400 text-xs font-semibold truncate">Por indicação</p>
                @if($pointsPerReferral)
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white leading-tight">+{{ $pointsPerReferral }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">pontos</p>
                @else
                    <p class="text-sm font-bold text-slate-400 dark:text-slate-500 leading-tight">Não config.</p>
                @endif
            </div>
        </div>

    </div>

    {{-- ===== SEU LINK DE INDICAÇÃO ===== --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm overflow-hidden">
        <h2 class="text-xl font-black text-slate-900 dark:text-white mb-1">Seu link de indicação</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Compartilhe este link. O sistema registra automaticamente quem entrou pelo seu convite.</p>

        {{-- Input + Copiar --}}
        <div class="flex items-center gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-2.5 mb-6 overflow-hidden">
            <i class="fas fa-link text-slate-400 shrink-0 text-sm"></i>
            <input id="referralLinkInput" type="text" readonly
                   value="{{ $referralLink }}"
                   class="flex-1 min-w-0 bg-transparent text-sm font-mono text-slate-700 dark:text-slate-300 outline-none truncate">
            <button onclick="copyReferralLink()"
                    id="copyBtn"
                    class="shrink-0 flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-sm font-bold px-3 py-2 rounded-xl transition-all whitespace-nowrap">
                <i class="fas fa-copy" id="copyIcon"></i>
                <span id="copyText" class="hidden sm:inline">Copiar</span>
            </button>
        </div>

        {{-- Compartilhamento rápido --}}
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-3 mb-6">
            <a href="https://wa.me/?text={{ urlencode('Ei! Faça parte da maior comunidade de empreendedores e networking do Brasil. Use meu link: ' . $referralLink) }}"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2 bg-green-500 hover:bg-green-600 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-all active:scale-95">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </a>
            <a href="https://t.me/share/url?url={{ urlencode($referralLink) }}&text={{ urlencode('Entre na plataforma com meu convite e comece a fazer networking!') }}"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2 bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-all active:scale-95">
                <i class="fab fa-telegram"></i> Telegram
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($referralLink) }}"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-all active:scale-95">
                <i class="fab fa-linkedin"></i> LinkedIn
            </a>
            <a href="mailto:?subject={{ urlencode('Convite para a comunidade UNN') }}&body={{ urlencode('Olá! Quero te convidar para a maior plataforma de networking para empreendedores. Acesse: ' . $referralLink) }}"
               class="inline-flex items-center justify-center gap-2 bg-slate-600 hover:bg-slate-700 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-all active:scale-95">
                <i class="fas fa-envelope"></i> E-mail
            </a>
        </div>

        {{-- Código + aviso --}}
        <div class="flex flex-wrap items-center gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
            <div>
                <p class="text-xs text-slate-400 dark:text-slate-500 mb-1">Seu código único</p>
                <code class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono text-sm px-3 py-1 rounded-lg">{{ $user->referral_code }}</code>
            </div>
            <div class="h-8 w-px bg-slate-200 dark:bg-slate-700 hidden sm:block"></div>
            <p class="text-xs text-slate-400 dark:text-slate-500">
                <i class="fas fa-info-circle mr-1 text-blue-400"></i>
                Pontos são creditados somente após o indicado <strong class="text-slate-600 dark:text-slate-400">assinar um plano pago</strong>.
            </p>
        </div>
    </div>

    {{-- ===== LISTA DE INDICADOS ===== --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
        <div class="p-6 md:p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white">Pessoas que você indicou</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                    Cadastros realizados com seu link. &nbsp;
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $convertedCount }} convertido{{ $convertedCount != 1 ? 's' : '' }}</span>
                    &nbsp;·&nbsp;
                    <span class="text-slate-500">{{ $pendingCount }} aguardando pagamento</span>
                </p>
            </div>
            @if($totalReferred > 0)
                <div class="shrink-0 text-right hidden sm:block">
                    <p class="text-xs text-slate-400 mb-1">Taxa de conversão</p>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $conversionRate }}%"></div>
                        </div>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $conversionRate }}%</span>
                    </div>
                </div>
            @endif
        </div>

        @if($referredUsers->isEmpty())
            <div class="p-10 text-center">
                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-plus text-slate-400 text-2xl"></i>
                </div>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mb-1">Nenhuma indicação ainda</p>
                <p class="text-slate-400 dark:text-slate-500 text-sm">Compartilhe seu link para que amigos se cadastrem e você comece a ganhar pontos!</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-widest">
                        <tr>
                            <th class="text-left px-3 py-3 sm:px-6 sm:py-4">Membro indicado</th>
                            <th class="text-left px-3 py-3 sm:px-6 sm:py-4 hidden md:table-cell">Cadastro</th>
                            <th class="text-left px-3 py-3 sm:px-6 sm:py-4 hidden sm:table-cell">Plano / Status</th>
                            <th class="text-right px-3 py-3 sm:px-6 sm:py-4 whitespace-nowrap">Pontos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($referredUsers as $referred)
                            @php
                                $logsFromUser = $referralPointsLogs->filter(function($l) use ($referred) {
                                    $meta = json_decode($l->meta ?? '{}', true);
                                    return ($meta['new_user_id'] ?? null) == $referred->id;
                                });
                                $pointsFromThisUser = $logsFromUser->sum('points');

                                // Status do plano
                                if ($referred->plan_id) {
                                    if (!$referred->plan_expires_at) {
                                        $planStatus = ['label' => 'Vitalício', 'dot' => 'bg-purple-500', 'class' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400'];
                                    } elseif (\Carbon\Carbon::parse($referred->plan_expires_at)->isFuture()) {
                                        $planStatus = ['label' => 'Assinante ativo', 'dot' => 'bg-emerald-500', 'class' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'];
                                    } else {
                                        $planStatus = ['label' => 'Plano expirado', 'dot' => 'bg-yellow-500', 'class' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'];
                                    }
                                    $planName = $plansMap[$referred->plan_id] ?? 'Plano';
                                } else {
                                    $planStatus = ['label' => 'Sem plano', 'dot' => 'bg-slate-400', 'class' => 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'];
                                    $planName = null;
                                }
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-3 py-3 sm:px-6 sm:py-4 max-w-[40vw] sm:max-w-none">
                                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 flex items-center justify-center">
                                            @if($referred->photo)
                                                <img src="{{ asset($referred->photo) }}" alt="{{ $referred->name }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-user text-slate-400 text-xs"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 dark:text-white truncate text-xs sm:text-sm">{{ $referred->name }}</p>
                                            <p class="text-xs text-slate-400 truncate">{{ $referred->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 sm:px-6 sm:py-4 text-slate-500 dark:text-slate-400 hidden md:table-cell">
                                    <p class="text-xs">{{ $referred->created_at->format('d/m/Y') }}</p>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">{{ $referred->created_at->diffForHumans() }}</p>
                                </td>
                                <td class="px-3 py-3 sm:px-6 sm:py-4 hidden sm:table-cell">
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex w-fit items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $planStatus['class'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $planStatus['dot'] }} shrink-0"></span>
                                            {{ $planStatus['label'] }}
                                        </span>
                                        @if($planName)
                                            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $planName }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 sm:px-6 sm:py-4 text-right">
                                    @if($pointsFromThisUser > 0)
                                        <div class="flex flex-col items-end gap-1">
                                            <span class="inline-flex items-center gap-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold px-2 sm:px-3 py-1 rounded-full whitespace-nowrap">
                                                <i class="fas fa-coins"></i> +{{ number_format($pointsFromThisUser) }}
                                            </span>
                                            <span class="text-xs text-slate-400 hidden sm:block">{{ $logsFromUser->count() }} pag.</span>
                                        </div>
                                    @elseif($referred->plan_id)
                                        <span class="text-xs text-yellow-600 dark:text-yellow-500 font-medium whitespace-nowrap">Aguardando</span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-600 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($referredUsers->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $referredUsers->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- ===== HISTÓRICO DE GANHOS ===== --}}
    @if($referralPointsLogs->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
        <div class="p-6 md:p-8 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-xl font-black text-slate-900 dark:text-white">Histórico de ganhos</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Cada linha corresponde a um pagamento de plano confirmado de um indicado seu.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-widest">
                    <tr>
                        <th class="text-left px-3 py-3 sm:px-6 sm:py-4">Indicado</th>
                        <th class="text-left px-3 py-3 sm:px-6 sm:py-4 hidden md:table-cell">Plano</th>
                        <th class="text-left px-3 py-3 sm:px-6 sm:py-4 hidden lg:table-cell">Pedido</th>
                        <th class="text-left px-3 py-3 sm:px-6 sm:py-4 hidden sm:table-cell">Data</th>
                        <th class="text-right px-3 py-3 sm:px-6 sm:py-4">Pontos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($referralPointsLogs as $log)
                        @php $meta = json_decode($log->meta ?? '{}', true); @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-3 py-3 sm:px-6 sm:py-4 max-w-[40vw] sm:max-w-none">
                                <p class="font-semibold text-slate-900 dark:text-white truncate text-xs sm:text-sm">{{ $meta['new_user_name'] ?? '—' }}</p>
                                <p class="text-xs text-slate-400 sm:hidden">{{ optional($log->created_at)->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-slate-500 dark:text-slate-400 hidden md:table-cell text-xs">
                                {{ $meta['plan_name'] ?? '—' }}
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 hidden lg:table-cell">
                                @if(isset($meta['order_id']))
                                    <code class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs px-2 py-0.5 rounded">#{{ $meta['order_id'] }}</code>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-slate-500 dark:text-slate-400 hidden sm:table-cell">
                                <p class="text-xs whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                                <p class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-right">
                                <span class="inline-flex items-center gap-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold px-2 sm:px-3 py-1 rounded-full whitespace-nowrap">
                                    <i class="fas fa-coins"></i> +{{ number_format($log->points) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 dark:bg-slate-800 border-t border-slate-100 dark:border-slate-700">
                        <td colspan="2" class="px-3 py-3 sm:px-6 sm:py-4 text-sm font-bold text-slate-700 dark:text-slate-300">Total acumulado</td>
                        <td class="px-3 py-3 sm:px-6 sm:py-4 text-right">
                            <span class="inline-flex items-center gap-1 bg-amber-200 dark:bg-amber-800/50 text-amber-800 dark:text-amber-300 text-sm font-black px-3 py-1 rounded-full whitespace-nowrap">
                                <i class="fas fa-coins"></i> +{{ number_format($totalReferralPoints) }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    {{-- ===== COMO FUNCIONA ===== --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm">
        <h2 class="text-xl font-black text-slate-900 dark:text-white mb-6">Como funciona</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center shrink-0">
                    <span class="text-blue-600 dark:text-blue-400 font-black text-lg">1</span>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Copie seu link</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Cada membro tem um link e código únicos de indicação.</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center shrink-0">
                    <span class="text-purple-600 dark:text-purple-400 font-black text-lg">2</span>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Compartilhe</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Envie para amigos, colegas ou publique em suas redes sociais.</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center shrink-0">
                    <span class="text-teal-600 dark:text-teal-400 font-black text-lg">3</span>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Indicado assina</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">O indicado se cadastra pelo seu link e <strong class="text-teal-600 dark:text-teal-400">paga um plano pago</strong>.</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center shrink-0">
                    <span class="text-amber-600 dark:text-amber-400 font-black text-lg">4</span>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Você ganha pontos</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        @if($pointsPerReferral)
                            <strong class="text-amber-600 dark:text-amber-400">+{{ $pointsPerReferral }} pontos</strong> são creditados automaticamente após confirmação do pagamento.
                        @else
                            Pontos são creditados automaticamente na confirmação do pagamento do plano.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Regras --}}
        <div class="mt-6 bg-slate-50 dark:bg-slate-800 rounded-2xl px-5 py-4 flex items-start gap-3 text-sm text-slate-500 dark:text-slate-400">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 shrink-0"></i>
            <div>
                <strong class="text-slate-700 dark:text-slate-300">Regras do programa: </strong>
                Planos gratuitos não geram pontos · Pontos são creditados somente após confirmação do pagamento pelo sistema · Não há limite de indicações
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function copyReferralLink() {
    const input = document.getElementById('referralLinkInput');
    const btn   = document.getElementById('copyBtn');
    const icon  = document.getElementById('copyIcon');
    const text  = document.getElementById('copyText');

    const doCopy = () => {
        icon.className = 'fas fa-check';
        text.textContent = 'Copiado!';
        btn.classList.replace('bg-blue-600', 'bg-green-600');
        btn.classList.replace('hover:bg-blue-700', 'hover:bg-green-700');
        setTimeout(() => {
            icon.className = 'fas fa-copy';
            text.textContent = 'Copiar';
            btn.classList.replace('bg-green-600', 'bg-blue-600');
            btn.classList.replace('hover:bg-green-700', 'hover:bg-blue-700');
        }, 2500);
    };

    if (navigator.clipboard) {
        navigator.clipboard.writeText(input.value).then(doCopy).catch(() => {
            input.select();
            document.execCommand('copy');
            doCopy();
        });
    } else {
        input.select();
        document.execCommand('copy');
        doCopy();
    }
}
</script>
@endpush

@endsection
