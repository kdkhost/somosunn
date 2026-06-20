@extends('panel.layouts.app')

@push('styles')
    <style>
        .panel-users-pagination {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
            align-items: stretch;
        }

        @media (min-width: 640px) {
            .panel-users-pagination {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .panel-users-pagination__summary {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 700;
        }

        .dark .panel-users-pagination__summary {
            color: #94a3b8;
        }

        .panel-users-pagination__nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .panel-users-pagination__item {
            min-width: 2.5rem;
            height: 2.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.875rem;
            background: #ffffff;
            color: #475569;
            font-size: 0.875rem;
            font-weight: 800;
            line-height: 1;
            transition: all 0.15s ease;
        }

        .dark .panel-users-pagination__item {
            border-color: #334155;
            background: #0f172a;
            color: #cbd5e1;
        }

        .panel-users-pagination__item:hover {
            border-color: #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
            transform: translateY(-1px);
        }

        .dark .panel-users-pagination__item:hover {
            border-color: #2563eb;
            background: rgba(37, 99, 235, 0.16);
            color: #93c5fd;
        }

        .panel-users-pagination__item--active {
            border-color: #2563eb;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.22);
        }

        .panel-users-pagination__item--disabled {
            cursor: not-allowed;
            opacity: 0.45;
            pointer-events: none;
        }

        .panel-users-pagination__ellipsis {
            min-width: 2rem;
            height: 2.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-weight: 900;
        }
    </style>
@endpush

@section('title', 'Usuários')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.users.index') }}" class="hover:underline">Usuários</a>
@endsection

@section('panel_content')
    @php
        $marketingUserId = (int) \App\Models\Setting::get('platform_marketing_user_id', 0);
        $marketingUser = $marketingUserId > 0 ? \App\Models\User::find($marketingUserId) : null;
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shrink-0">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-purple-900 dark:text-purple-100">Responsável de Marketing</h3>
                    <p class="text-xs text-purple-700 dark:text-purple-300">
                        {{ $marketingUser ? $marketingUser->name . ' (' . $marketingUser->email . ')' : 'Nenhuma pessoa designada.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Header & Toolbar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white transition-colors">Gerenciar Usuários</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm transition-colors">Controle de acesso, planos e permissões dos membros.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <form action="{{ route('panel.admin.users.index') }}" method="GET" class="relative group w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400 dark:text-slate-500 group-focus-within:text-blue-500 transition-colors"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Buscar por nome ou e-mail..." 
                           class="pl-10 w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                    @if(request('role'))
                        <input type="hidden" name="role" value="{{ request('role') }}">
                    @endif
                </form>
                <a href="{{ route('panel.admin.users.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02] flex items-center justify-center gap-2 whitespace-nowrap">
                    <i class="fas fa-user-plus"></i> Novo Usuário
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex gap-2 overflow-x-auto pb-2 no-scrollbar">
            <a href="{{ route('panel.admin.users.index') }}" 
               class="px-4 py-2 rounded-xl text-sm transition-all whitespace-nowrap
               {{ !request('role') 
                  ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                  : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm' }}">
               Todos
            </a>
            @php
                $roles = [
                    'admin' => 'Administradores',
                    'instrutor' => 'Instrutores',
                    'member' => 'Membros'
                ];
            @endphp
            @foreach($roles as $key => $label)
                <a href="{{ route('panel.admin.users.index', array_merge(request()->all(), ['role' => $key])) }}" 
                   class="px-4 py-2 rounded-xl text-sm transition-all whitespace-nowrap
                   {{ request('role') == $key 
                      ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/40 font-bold border border-blue-500/50 ring-2 ring-blue-500/20' 
                      : 'text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-200 font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm' }}">
                   {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50/50 dark:bg-slate-950 text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Usuário</th>
                            <th class="px-6 py-4">Função / Nível</th>
                            <th class="px-6 py-4">Ingressos</th>
                            <th class="px-6 py-4">Plano Atual</th>
                            <th class="px-6 py-4 text-center">Status do Plano</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($users as $u)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0 transition-colors">
                                            @if($u->profile_photo_url && !str_contains($u->profile_photo_url, 'default-user.svg'))
                                                <img src="{{ $u->profile_photo_url }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-user text-slate-400 dark:text-slate-500"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 dark:text-white transition-colors">{{ $u->name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 transition-colors">{{ $u->email }}</div>
                                            <div class="text-[10px] mt-1 font-bold {{ $u->hasVerifiedEmail() ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                                <i class="fas {{ $u->hasVerifiedEmail() ? 'fa-check-circle' : 'fa-exclamation-circle' }} mr-1"></i>
                                                {{ $u->hasVerifiedEmail() ? 'E-mail validado' : 'E-mail pendente' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1 items-start">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold 
                                            {{ $u->role == 'superadmin' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : 
                                               ($u->role == 'admin' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : 
                                               ($u->role == 'instrutor' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' : 
                                               'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400')) }}">
                                            {{ ['member' => 'Membro', 'membro' => 'Membro', 'instrutor' => 'Instrutor', 'admin' => 'Administrador', 'superadmin' => 'Super Admin'][$u->role] ?? 'Membro' }}
                                        </span>
                                        @if($u->level)
                                            <span class="text-xs text-slate-400 dark:text-slate-500 capitalize">Nível: {{ $u->level }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1 items-start">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                            {{ $u->getCheckedInTicketsCount() }} / {{ $u->getTotalTicketsCount() }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-wider">Check-ins</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                                    @if($u->plan)
                                        <span class="font-medium">{{ $u->plan->name }}</span>
                                        @if($u->plan_expires_at)
                                            <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Expira em: {{ $u->plan_expires_at->format('d/m/Y') }}</div>
                                        @endif
                                    @else
                                        <span class="opacity-50 italic">Sem plano</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($u->plan_id)
                                        @if($u->plan_expires_at && $u->plan_expires_at->isPast())
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Expirado</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Ativo</span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 transition-opacity">
                                        @if(auth()->user()->role === 'superadmin' && $u->id !== auth()->id())
                                            <a href="{{ route('admin.users.impersonate', $u) }}" 
                                               class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition" 
                                               title="Impersonar (Logar como)">
                                                <i class="fas fa-user-secret"></i>
                                            </a>
                                        @endif

                                        <button type="button"
                                            class="btn-toggle-marketing w-8 h-8 rounded-lg flex items-center justify-center transition {{ $marketingUserId === $u->id ? 'text-white bg-purple-600 hover:bg-purple-700' : 'text-slate-400 dark:text-slate-500 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/30' }}"
                                            title="{{ $marketingUserId === $u->id ? 'Remover como Responsável de Marketing' : 'Definir como Responsável de Marketing' }}"
                                            data-url="{{ route('panel.admin.users.marketing-manager', $u) }}"
                                            data-action="{{ $marketingUserId === $u->id ? 'unset' : 'set' }}"
                                            data-name="{{ $u->name }}">
                                            <i class="fas fa-bullhorn"></i>
                                        </button>

                                        @if(!$u->hasVerifiedEmail())
                                            <form action="{{ route('panel.admin.users.verify-email', $u) }}" method="POST"
                                                onsubmit="return confirm('Validar manualmente o e-mail deste membro?');">
                                                @csrf
                                                <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-amber-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition"
                                                    title="Validar e-mail manualmente">
                                                    <i class="fas fa-envelope-circle-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <a href="{{ route('panel.admin.users.edit', $u) }}" 
                                           class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        @if($u->id !== auth()->id() && ($u->role !== 'superadmin' || auth()->user()->role === 'superadmin'))
                                            <form action="{{ route('panel.admin.users.destroy', $u) }}" method="POST" 
                                                  onsubmit="return confirmAction(event, 'Remover usuário?', 'Tem certeza que deseja remover este usuário? Esta ação não pode ser desfeita.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition" title="Remover">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="fas fa-users text-4xl opacity-20"></i>
                                        <p>Nenhum usuário encontrado.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 text-sm text-slate-500 dark:text-slate-400">
                Exibindo todos os <strong>{{ $users->count() }}</strong> usuários encontrados.
            </div>
            @if(false)
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50">
                    @php
                        $users->appends(request()->query());
                        $paginaAtual = $users->currentPage();
                        $ultimaPagina = $users->lastPage();
                        $inicioJanela = max(1, $paginaAtual - 1);
                        $fimJanela = min($ultimaPagina, $paginaAtual + 1);
                    @endphp

                    <div class="panel-users-pagination">
                        <div class="panel-users-pagination__summary">
                            Mostrando {{ $users->firstItem() }} a {{ $users->lastItem() }} de {{ $users->total() }} usuários
                        </div>

                        <nav class="panel-users-pagination__nav" aria-label="Paginação de usuários">
                            @if($users->onFirstPage())
                                <span class="panel-users-pagination__item panel-users-pagination__item--disabled" aria-disabled="true">
                                    <i class="fas fa-chevron-left"></i>
                                </span>
                            @else
                                <a href="{{ $users->previousPageUrl() }}" class="panel-users-pagination__item" rel="prev" aria-label="Página anterior">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            @endif

                            @if($inicioJanela > 1)
                                <a href="{{ $users->url(1) }}" class="panel-users-pagination__item">1</a>
                                @if($inicioJanela > 2)
                                    <span class="panel-users-pagination__ellipsis">...</span>
                                @endif
                            @endif

                            @for($pagina = $inicioJanela; $pagina <= $fimJanela; $pagina++)
                                @if($pagina === $paginaAtual)
                                    <span class="panel-users-pagination__item panel-users-pagination__item--active" aria-current="page">{{ $pagina }}</span>
                                @else
                                    <a href="{{ $users->url($pagina) }}" class="panel-users-pagination__item">{{ $pagina }}</a>
                                @endif
                            @endfor

                            @if($fimJanela < $ultimaPagina)
                                @if($fimJanela < $ultimaPagina - 1)
                                    <span class="panel-users-pagination__ellipsis">...</span>
                                @endif
                                <a href="{{ $users->url($ultimaPagina) }}" class="panel-users-pagination__item">{{ $ultimaPagina }}</a>
                            @endif

                            @if($users->hasMorePages())
                                <a href="{{ $users->nextPageUrl() }}" class="panel-users-pagination__item" rel="next" aria-label="Próxima página">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            @else
                                <span class="panel-users-pagination__item panel-users-pagination__item--disabled" aria-disabled="true">
                                    <i class="fas fa-chevron-right"></i>
                                </span>
                            @endif
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('click', async function (event) {
    const button = event.target.closest('.btn-toggle-marketing');
    if (!button) return;

    const assigning = button.dataset.action === 'set';
    const confirmed = await Swal.fire({
        icon: 'question',
        title: assigning ? 'Definir responsável de marketing?' : 'Remover responsável de marketing?',
        text: assigning
            ? button.dataset.name + ' passará a receber o split de marketing configurado.'
            : button.dataset.name + ' deixará de receber os próximos splits de marketing.',
        showCancelButton: true,
        confirmButtonText: assigning ? 'Definir' : 'Remover',
        cancelButtonText: 'Cancelar'
    });

    if (!confirmed.isConfirmed) return;

    const response = await fetch(button.dataset.url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ action: button.dataset.action })
    });
    const data = await response.json();

    if (!response.ok) {
        await Swal.fire('Erro', data.message || 'Não foi possível atualizar o responsável de marketing.', 'error');
        return;
    }

    await Swal.fire('Concluído', data.message, 'success');
    window.location.reload();
});
</script>
@endpush
