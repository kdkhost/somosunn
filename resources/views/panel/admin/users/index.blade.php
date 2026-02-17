@extends('panel.layouts.app')

@section('title', 'Usuários')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.users.index') }}" class="hover:underline">Usuários</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
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
               class="px-4 py-1.5 rounded-full text-sm font-semibold whitespace-nowrap transition {{ !request('role') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700' }}">
               Todos
            </a>
            @php
                $roles = [
                    'admin' => 'Administradores',
                    'instrutor' => 'Instrutores',
                    'membro' => 'Membros'
                ];
            @endphp
            @foreach($roles as $key => $label)
                <a href="{{ route('panel.admin.users.index', array_merge(request()->all(), ['role' => $key])) }}" 
                   class="px-4 py-1.5 rounded-full text-sm font-semibold whitespace-nowrap transition {{ request('role') == $key ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700' }}">
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
                                        <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 font-bold text-lg overflow-hidden shrink-0 border border-slate-100 dark:border-slate-700">
                                            @if($u->photo)
                                                <img src="{{ asset($u->photo) }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                {{ substr($u->name, 0, 1) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 dark:text-white transition-colors">{{ $u->name }}</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 transition-colors">{{ $u->email }}</div>
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
                                            {{ ucfirst($u->role ?? 'Membro') }}
                                        </span>
                                        @if($u->level)
                                            <span class="text-xs text-slate-400 dark:text-slate-500 capitalize">Nível: {{ $u->level }}</span>
                                        @endif
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
                                    <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                        @if(auth()->user()->role === 'superadmin' && $u->id !== auth()->id())
                                            <a href="{{ route('admin.users.impersonate', $u) }}" 
                                               class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition" 
                                               title="Impersonar (Logar como)">
                                                <i class="fas fa-user-secret"></i>
                                            </a>
                                        @endif
                                        
                                        <a href="{{ route('panel.admin.users.edit', $u) }}" 
                                           class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>

                                        @if($u->id !== auth()->id() && ($u->role !== 'superadmin' || auth()->user()->role === 'superadmin'))
                                            <form action="{{ route('panel.admin.users.destroy', $u) }}" method="POST" 
                                                  onsubmit="return confirm('Tem certeza que deseja remover este usuário? Esta ação não pode ser desfeita.');">
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
                                <td colspan="5" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">
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
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
