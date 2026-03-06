@extends('panel.layouts.app')

@section('title', 'Ranking de Pontos')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.ranking.index') }}" class="hover:underline">Ranking</a>
@endsection

@section('panel_content')
    @php
        $rankingRows = $rankedUsers->currentPage() === 1
            ? $rankedUsers->getCollection()->skip(3)->values()
            : $rankedUsers->getCollection();

        $rankOffset = $rankedUsers->currentPage() === 1
            ? 4
            : (($rankedUsers->currentPage() - 1) * $rankedUsers->perPage()) + 1;
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Ranking Geral</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Veja os usuários mais engajados da plataforma com base em pontos acumulados.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.points-rules.index') }}"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-all flex items-center gap-2 shadow-lg shadow-blue-500/20">
                    <i class="fas fa-cog"></i>
                    <span>Configurar Regras</span>
                </a>
            </div>
        </div>

        @if($podium->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end pb-2">
                @if($podium->count() >= 2)
                    <div
                        class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm relative flex flex-col items-center group hover:shadow-xl transition-all duration-500 order-2 md:order-1 h-64 justify-center">
                        <div class="absolute -top-6">
                            <div
                                class="w-16 h-16 rounded-full border-4 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden shadow-lg group-hover:scale-110 transition-transform">
                                @if($podium[1]->profile_photo_url && !str_contains($podium[1]->profile_photo_url, 'default-user.svg'))
                                    <img src="{{ $podium[1]->profile_photo_url }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-slate-50 dark:bg-slate-950 text-slate-400 dark:text-slate-500">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                            </div>
                            <div
                                class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-6 h-6 bg-slate-300 dark:bg-slate-600 rounded-full flex items-center justify-center text-[10px] text-white font-bold border-2 border-white dark:border-slate-900 shadow-sm">
                                2
                            </div>
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white mt-8 text-center line-clamp-1 transition-colors">{{ $podium[1]->name }}</h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium mb-4 transition-colors">{{ $podium[1]->email }}</p>
                        <div class="text-xl font-black text-slate-400 dark:text-slate-500 transition-colors">
                            {{ number_format($podium[1]->points, 0, ',', '.') }}
                            <span class="text-[10px] uppercase tracking-widest ml-1 font-bold">pts</span>
                        </div>
                    </div>
                @endif

                @if($podium->count() >= 1)
                    <div
                        class="bg-gradient-to-br from-blue-600 to-indigo-700 p-8 rounded-[2.5rem] shadow-2xl shadow-blue-500/30 relative flex flex-col items-center group hover:-translate-y-2 transition-all duration-500 order-1 md:order-2 h-80 justify-center overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-20">
                            <i class="fas fa-crown text-6xl text-white"></i>
                        </div>
                        <div class="absolute -top-8 z-10">
                            <div
                                class="w-24 h-24 rounded-full border-4 border-yellow-400 bg-white dark:bg-slate-900 overflow-hidden shadow-2xl group-hover:scale-110 transition-transform">
                                @if($podium[0]->profile_photo_url && !str_contains($podium[0]->profile_photo_url, 'default-user.svg'))
                                    <img src="{{ $podium[0]->profile_photo_url }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                            </div>
                            <div
                                class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center text-sm text-yellow-900 font-black border-4 border-white dark:border-blue-900 shadow-lg">
                                1
                            </div>
                        </div>
                        <h3 class="font-bold text-white mt-12 text-xl text-center line-clamp-1">{{ $podium[0]->name }}</h3>
                        <p class="text-xs text-blue-100/70 font-medium mb-6">{{ $podium[0]->email }}</p>
                        <div class="text-3xl font-black text-white drop-shadow-sm">
                            {{ number_format($podium[0]->points, 0, ',', '.') }}
                            <span class="text-xs uppercase tracking-widest ml-1 font-bold opacity-80">pts</span>
                        </div>
                    </div>
                @endif

                @if($podium->count() >= 3)
                    <div
                        class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm relative flex flex-col items-center group hover:shadow-xl transition-all duration-500 order-3 h-56 justify-center">
                        <div class="absolute -top-6">
                            <div
                                class="w-16 h-16 rounded-full border-4 border-orange-50 dark:border-orange-900/20 bg-white dark:bg-slate-900 overflow-hidden shadow-lg group-hover:scale-110 transition-transform">
                                @if($podium[2]->profile_photo_url && !str_contains($podium[2]->profile_photo_url, 'default-user.svg'))
                                    <img src="{{ $podium[2]->profile_photo_url }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-orange-50/30 dark:bg-orange-950/30 text-orange-300 dark:text-orange-500">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                            </div>
                            <div
                                class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-6 h-6 bg-orange-400 rounded-full flex items-center justify-center text-[10px] text-white font-bold border-2 border-white dark:border-slate-900 shadow-sm">
                                3
                            </div>
                        </div>
                        <h3 class="font-bold text-slate-900 dark:text-white mt-8 text-center line-clamp-1 transition-colors">{{ $podium[2]->name }}</h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 font-medium mb-4 transition-colors">{{ $podium[2]->email }}</p>
                        <div class="text-xl font-black text-orange-400 dark:text-orange-500 transition-colors">
                            {{ number_format($podium[2]->points, 0, ',', '.') }}
                            <span class="text-[10px] uppercase tracking-widest ml-1 font-bold">pts</span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between transition-colors">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">Ranking paginado</h3>
                    <span class="text-xs font-medium text-slate-400 dark:text-slate-500">
                        Página {{ $rankedUsers->currentPage() }} de {{ max($rankedUsers->lastPage(), 1) }} · {{ $rankedUsers->total() }} usuários com pontuação
                    </span>
                </div>
                <span class="text-xs font-medium text-slate-400 dark:text-slate-500">
                    Top 3 fixo + lista completa paginada
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                        @forelse($rankingRows as $user)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-all">
                                <td class="px-8 py-4 w-12">
                                    <span class="text-xs font-black text-slate-300 dark:text-slate-700 transition-colors">
                                        #{{ $rankOffset + $loop->index }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-950 text-slate-400 dark:text-slate-600 flex items-center justify-center text-xs font-bold overflow-hidden shadow-sm transition-colors border border-transparent dark:border-slate-800">
                                            @if($user->profile_photo_url && !str_contains($user->profile_photo_url, 'default-user.svg'))
                                                <img src="{{ $user->profile_photo_url }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="fas fa-user"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white transition-colors">{{ $user->name }}</p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium transition-colors">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-sm font-black text-slate-900 dark:text-white transition-colors">
                                            {{ number_format($user->points, 0, ',', '.') }}
                                        </span>
                                        <span
                                            class="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors">
                                            pontos
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-8 py-12 text-center text-slate-400 dark:text-slate-600 italic transition-colors">
                                    {{ $podium->isNotEmpty() ? 'Nenhum outro usuário além do pódio nesta faixa.' : 'Nenhum usuário rankeado ainda.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($rankedUsers->hasPages())
                <div class="px-8 py-5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50">
                    {{ $rankedUsers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
