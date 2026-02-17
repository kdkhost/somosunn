@extends('panel.layouts.app')

@section('title', 'Logs de Atividade')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Logs de Atividade</h1>
                <p class="text-sm text-slate-500 mt-1">Rastreie as ações realizadas por usuários e administradores no
                    sistema.</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-200">
            <form action="{{ route('panel.admin.logs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2 relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por descrição ou IP..."
                        class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-sm">
                </div>

                <div>
                    <select name="type"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all text-sm">
                        <option value="">Todos os Tipos</option>
                        {{-- Assuming we can pluck unique types if needed, or just hardcode some common ones --}}
                        <option value="login" @selected($type === 'login')>Login</option>
                        <option value="update" @selected($type === 'update')>Atualização</option>
                        <option value="create" @selected($type === 'create')>Criação</option>
                        <option value="delete" @selected($type === 'delete')>Exclusão</option>
                    </select>
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-2xl transition-all">
                    Filtrar
                </button>
            </form>
        </div>

        {{-- Logs Table --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-200">
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Data / Hora</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Usuário</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Ação</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr class="group hover:bg-slate-50/50 transition-all">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-xs font-bold text-slate-900">{{ $log->created_at->format('d/m/Y') }}</span>
                                        <span
                                            class="text-[10px] text-slate-400 font-medium">{{ $log->created_at->format('H:i:s') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->user)
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] font-bold">
                                                {{ substr($log->user->name, 0, 1) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-slate-700">{{ $log->user->name }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $log->user->email }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Sistema / Visitante</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-xs font-semibold text-slate-600 leading-relaxed">{{ $log->description }}</span>
                                        @if($log->activity_type)
                                            <span
                                                class="text-[9px] font-bold uppercase text-blue-500 tracking-tighter mt-1">{{ $log->activity_type }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code
                                        class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-md text-slate-500 font-mono">{{ $log->ip_address ?: '0.0.0.0' }}</code>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500 italic">
                                    Nenhum log registrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-200">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection