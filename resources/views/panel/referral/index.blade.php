@extends('panel.layouts.app')

@section('title', 'Programa de Indicações')

@section('panel_content')
@php
    $pointsRule = \App\Models\PointsRule::where('key', 'referral')->where('active', true)->first();
    $pointsPerReferral = $pointsRule?->points ?? 0;
@endphp

<div class="space-y-8">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Programa de Indicações</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1 font-medium">Indique amigos e ganhe pontos para cada novo membro que se cadastrar.</p>
        </div>
    </div>

    {{-- ===== CARDS RESUMO ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

        {{-- Total indicados --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-3xl p-6 text-white shadow-lg shadow-blue-500/20 flex items-center gap-5">
            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-user-plus text-2xl"></i>
            </div>
            <div>
                <p class="text-white/80 text-sm font-semibold">Indicados</p>
                <p class="text-4xl font-black">{{ $totalReferred }}</p>
            </div>
        </div>

        {{-- Pontos ganhos com indicações --}}
        <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-3xl p-6 text-white shadow-lg shadow-amber-500/20 flex items-center gap-5">
            <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-coins text-2xl"></i>
            </div>
            <div>
                <p class="text-white/80 text-sm font-semibold">Pontos ganhos</p>
                <p class="text-4xl font-black">{{ number_format($totalReferralPoints) }}</p>
            </div>
        </div>

        {{-- Pontos por indicação --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 flex items-center gap-5 shadow-sm">
            <div class="w-14 h-14 bg-green-100 dark:bg-green-900/40 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fas fa-gift text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
            <div>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-semibold">Por indicação</p>
                @if($pointsPerReferral)
                    <p class="text-3xl font-black text-slate-900 dark:text-white">+{{ $pointsPerReferral }} pts</p>
                @else
                    <p class="text-base font-bold text-slate-500 dark:text-slate-400">Regra de pontos não configurada</p>
                @endif
            </div>
        </div>

    </div>

    {{-- ===== SEU LINK DE INDICAÇÃO ===== --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm">
        <h2 class="text-xl font-black text-slate-900 dark:text-white mb-1">Seu link de indicação</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Compartilhe este link. Quando alguém se cadastrar por ele, você ganha pontos automaticamente.</p>

        {{-- Input + Copiar --}}
        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-3 mb-6">
            <i class="fas fa-link text-slate-400 shrink-0"></i>
            <input id="referralLinkInput" type="text" readonly
                   value="{{ $referralLink }}"
                   class="flex-1 bg-transparent text-sm font-mono text-slate-700 dark:text-slate-300 outline-none truncate">
            <button onclick="copyReferralLink()"
                    id="copyBtn"
                    class="shrink-0 flex items-center gap-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-sm font-bold px-4 py-2 rounded-xl transition-all">
                <i class="fas fa-copy" id="copyIcon"></i>
                <span id="copyText">Copiar</span>
            </button>
        </div>

        {{-- Compartilhamento rápido --}}
        <div class="flex flex-wrap gap-3">
            @if($user->referral_code)
                <a href="https://wa.me/?text={{ urlencode('Ei! Faça parte da maior comunidade de empreendedores e networking do Brasil. Use meu link: ' . $referralLink) }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all active:scale-95">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="https://t.me/share/url?url={{ urlencode($referralLink) }}&text={{ urlencode('Entre na plataforma com meu convite e comece a fazer networking!') }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all active:scale-95">
                    <i class="fab fa-telegram"></i> Telegram
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($referralLink) }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all active:scale-95">
                    <i class="fab fa-linkedin"></i> LinkedIn
                </a>
                <a href="mailto:?subject={{ urlencode('Convite para a comunidade UNN') }}&body={{ urlencode('Olá! Quero te convidar para a maior plataforma de networking para empreendedores. Acesse: ' . $referralLink) }}"
                   class="inline-flex items-center gap-2 bg-slate-600 hover:bg-slate-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all active:scale-95">
                    <i class="fas fa-envelope"></i> E-mail
                </a>
            @endif
        </div>

        {{-- Código único --}}
        <p class="mt-6 text-xs text-slate-400 dark:text-slate-500">
            Seu código único: <strong class="font-mono text-slate-600 dark:text-slate-300">{{ $user->referral_code }}</strong>
        </p>
    </div>

    {{-- ===== LISTA DE INDICADOS ===== --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
        <div class="p-6 md:p-8 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-xl font-black text-slate-900 dark:text-white">Pessoas que você indicou</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Membros que se cadastraram usando seu link.</p>
        </div>

        @if($referredUsers->isEmpty())
            <div class="p-10 text-center">
                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-plus text-slate-400 text-2xl"></i>
                </div>
                <p class="text-slate-500 dark:text-slate-400 font-semibold mb-1">Nenhuma indicação ainda</p>
                <p class="text-slate-400 dark:text-slate-500 text-sm">Compartilhe seu link para começar a acumular pontos!</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-widest">
                        <tr>
                            <th class="text-left px-6 py-4">Membro</th>
                            <th class="text-left px-6 py-4">Desde</th>
                            <th class="text-right px-6 py-4">Pontos gerados</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($referredUsers as $referred)
                            @php
                                $pointsFromThisUser = $referralPointsLogs
                                    ->filter(fn($l) => isset(json_decode($l->meta ?? '{}', true)['new_user_id']) && json_decode($l->meta ?? '{}', true)['new_user_id'] == $referred->id)
                                    ->sum('points');
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 flex items-center justify-center">
                                            @if($referred->photo)
                                                <img src="{{ asset($referred->photo) }}" alt="{{ $referred->name }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-user text-slate-400"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900 dark:text-white">{{ $referred->name }}</p>
                                            <p class="text-xs text-slate-400">{{ $referred->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ $referred->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($pointsFromThisUser > 0)
                                        <span class="inline-flex items-center gap-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold px-3 py-1 rounded-full">
                                            <i class="fas fa-coins"></i> +{{ $pointsFromThisUser }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
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

    {{-- ===== COMO FUNCIONA ===== --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm">
        <h2 class="text-xl font-black text-slate-900 dark:text-white mb-6">Como funciona</h2>
        <div class="grid sm:grid-cols-3 gap-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center shrink-0">
                    <span class="text-blue-600 dark:text-blue-400 font-black text-lg">1</span>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Copie seu link</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Cada membro tem um link único de indicação personalizado.</p>
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
                <div class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center shrink-0">
                    <span class="text-amber-600 dark:text-amber-400 font-black text-lg">3</span>
                </div>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white mb-1">Ganhe pontos</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        @if($pointsPerReferral)
                            Você recebe <strong class="text-amber-600">+{{ $pointsPerReferral }} pontos</strong> para cada novo membro que se cadastrar pelo seu link.
                        @else
                            Quando alguém se cadastrar pelo seu link, você recebe pontos automaticamente.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function copyReferralLink() {
    const input = document.getElementById('referralLinkInput');
    const btn = document.getElementById('copyBtn');
    const icon = document.getElementById('copyIcon');
    const text = document.getElementById('copyText');

    navigator.clipboard?.writeText(input.value).then(() => {
        icon.className = 'fas fa-check';
        text.textContent = 'Copiado!';
        btn.classList.replace('bg-blue-600', 'bg-green-600');
        btn.classList.replace('hover:bg-blue-700', 'hover:bg-green-700');
        setTimeout(() => {
            icon.className = 'fas fa-copy';
            text.textContent = 'Copiar';
            btn.classList.replace('bg-green-600', 'bg-blue-600');
            btn.classList.replace('hover:bg-green-700', 'hover:bg-blue-700');
        }, 2000);
    }).catch(() => {
        input.select();
        document.execCommand('copy');
    });
}
</script>
@endpush

@endsection
