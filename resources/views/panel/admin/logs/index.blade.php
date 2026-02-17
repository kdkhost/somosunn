@extends('panel.layouts.app')

@section('title', 'Logs de Atividade')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.logs.index') }}" class="hover:underline">Logs</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Logs de
                    Atividade</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Rastreie as ações realizadas
                    por usuários e administradores no
                    sistema.</p>
            </div>
        </div>

        {{-- Filters --}}
        <div
            class="bg-white dark:bg-slate-900 p-4 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 transition-colors">
            <form action="{{ route('panel.admin.logs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2 relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por descrição ou IP..."
                        class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-sm dark:text-white">
                </div>

                <div>
                    <select name="type"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-sm dark:text-white">
                        <option value="">Todos os Tipos</option>
                        {{-- Assuming we can pluck unique types if needed, or just hardcode some common ones --}}
                        <option value="login" @selected($type === 'login')>Login</option>
                        <option value="update" @selected($type === 'update')>Atualização</option>
                        <option value="create" @selected($type === 'create')>Criação</option>
                        <option value="delete" @selected($type === 'delete')>Exclusão</option>
                    </select>
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-bold rounded-2xl transition-all">
                    Filtrar
                </button>
            </form>
        </div>

        {{-- Logs Table --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-950/50 border-b border-slate-100 dark:border-slate-800">
                            <th
                                class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider transition-colors">
                                Data / Hora</th>
                            <th
                                class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider transition-colors">
                                Usuário</th>
                            <th
                                class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider transition-colors">
                                Ação</th>
                            <th
                                class="px-6 py-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider transition-colors">
                                IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($logs as $log)
                            <tr class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-all">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-xs font-bold text-slate-900 dark:text-white transition-colors">{{ $log->created_at->format('d/m/Y') }}</span>
                                        <span
                                            class="text-[10px] text-slate-400 dark:text-slate-500 font-medium transition-colors">{{ $log->created_at->format('H:i:s') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->user)
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-7 h-7 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center text-[10px] font-bold transition-colors">
                                                {{ substr($log->user->name, 0, 1) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-xs font-bold text-slate-700 dark:text-slate-300 transition-colors">{{ $log->user->name }}</span>
                                                <span
                                                    class="text-[10px] text-slate-400 dark:text-slate-500 transition-colors">{{ $log->user->email }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500 italic transition-colors">Sistema /
                                            Visitante</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-xs font-semibold text-slate-600 dark:text-slate-400 leading-relaxed transition-colors">{{ $log->description }}</span>
                                        @if($log->activity_type)
                                            <span
                                                class="text-[9px] font-bold uppercase text-blue-500 dark:text-blue-400 tracking-tighter mt-1 transition-colors">{{ $log->activity_type }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code
                                        class="text-[10px] bg-slate-100 dark:bg-slate-950 px-2 py-0.5 rounded-md text-slate-500 dark:text-slate-400 font-mono transition-colors">{{ $log->ip_address ?: '0.0.0.0' }}</code>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="px-6 py-12 text-center text-slate-500 dark:text-slate-600 italic transition-colors">
                                    Nenhum log registrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div
                    class="px-6 py-4 bg-slate-50/50 dark:bg-slate-950/50 border-t border-slate-200 dark:border-slate-800 transition-colors">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection