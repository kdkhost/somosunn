@extends('panel.layouts.app')

@section('title', 'Ranking de Pontos')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Ranking Geral</h1>
                <p class="text-sm text-slate-500 mt-1">Veja os usuários mais engajados da plataforma com base em suas
                    conquistas.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('panel.admin.points-rules.index') }}"
                    class="px-4 py-2 bg-blue-50 text-blue-600 text-sm font-semibold rounded-xl hover:bg-blue-100 transition-all flex items-center gap-2">
                    <i class="fas fa-cog"></i>
                    <span>Configurar Regras</span>
                </a>
            </div>
        </div>

        {{-- Top 3 Podium --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end pb-8">
            @php $podium = $top->take(3); @endphp

            @if($podium->count() >= 2)
                {{-- Second Place --}}
                <div
                    class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm relative flex flex-col items-center group hover:shadow-xl transition-all duration-500 order-2 md:order-1 h-64 justify-center">
                    <div class="absolute -top-6">
                        <div
                            class="w-16 h-16 rounded-full border-4 border-slate-200 bg-white overflow-hidden shadow-lg group-hover:scale-110 transition-transform">
                            @if($podium[1]->profile_photo_url)
                                <img src="{{ $podium[1]->profile_photo_url }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-slate-50 text-slate-400 font-bold">2º
                                </div>
                            @endif
                        </div>
                        <div
                            class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-6 h-6 bg-slate-300 rounded-full flex items-center justify-center text-[10px] text-white font-bold border-2 border-white shadow-sm">
                            2</div>
                    </div>
                    <h3 class="font-bold text-slate-900 mt-8 text-center line-clamp-1">{{ $podium[1]->name }}</h3>
                    <p class="text-xs text-slate-400 font-medium mb-4">{{ $podium[1]->email }}</p>
                    <div class="text-xl font-black text-slate-400">{{ number_format($podium[1]->points, 0, ',', '.') }} <span
                            class="text-[10px] uppercase tracking-widest ml-1 font-bold">pts</span></div>
                </div>
            @endif

            @if($podium->count() >= 1)
                {{-- First Place --}}
                <div
                    class="bg-gradient-to-br from-blue-600 to-indigo-700 p-8 rounded-[2.5rem] shadow-2xl shadow-blue-500/30 relative flex flex-col items-center group hover:-translate-y-2 transition-all duration-500 order-1 md:order-2 h-80 justify-center overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-20">
                        <i class="fas fa-crown text-6xl text-white"></i>
                    </div>
                    <div class="absolute -top-8 z-10">
                        <div
                            class="w-24 h-24 rounded-full border-4 border-yellow-400 bg-white overflow-hidden shadow-2xl group-hover:scale-110 transition-transform">
                            @if($podium[0]->profile_photo_url)
                                <img src="{{ $podium[0]->profile_photo_url }}" class="w-full h-full object-cover">
                            @else
                                <div
                                    class="w-full h-full flex items-center justify-center bg-blue-50 text-blue-600 font-bold text-2xl">
                                    1º</div>
                            @endif
                        </div>
                        <div
                            class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center text-sm text-yellow-900 font-black border-4 border-white shadow-lg">
                            1</div>
                    </div>
                    <h3 class="font-bold text-white mt-12 text-xl text-center line-clamp-1">{{ $podium[0]->name }}</h3>
                    <p class="text-xs text-blue-100/70 font-medium mb-6">{{ $podium[0]->email }}</p>
                    <div class="text-3xl font-black text-white drop-shadow-sm">
                        {{ number_format($podium[0]->points, 0, ',', '.') }} <span
                            class="text-xs uppercase tracking-widest ml-1 font-bold opacity-80">pts</span></div>
                </div>
            @endif

            @if($podium->count() >= 3)
                {{-- Third Place --}}
                <div
                    class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm relative flex flex-col items-center group hover:shadow-xl transition-all duration-500 order-3 h-56 justify-center">
                    <div class="absolute -top-6">
                        <div
                            class="w-16 h-16 rounded-full border-4 border-orange-50 bg-white overflow-hidden shadow-lg group-hover:scale-110 transition-transform">
                            @if($podium[2]->profile_photo_url)
                                <img src="{{ $podium[2]->profile_photo_url }}" class="w-full h-full object-cover">
                            @else
                                <div
                                    class="w-full h-full flex items-center justify-center bg-orange-50/30 text-orange-300 font-bold">
                                    3º</div>
                            @endif
                        </div>
                        <div
                            class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-6 h-6 bg-orange-400 rounded-full flex items-center justify-center text-[10px] text-white font-bold border-2 border-white shadow-sm">
                            3</div>
                    </div>
                    <h3 class="font-bold text-slate-900 mt-8 text-center line-clamp-1">{{ $podium[2]->name }}</h3>
                    <p class="text-xs text-slate-400 font-medium mb-4">{{ $podium[2]->email }}</p>
                    <div class="text-xl font-black text-orange-900/40 text-orange-400">
                        {{ number_format($podium[2]->points, 0, ',', '.') }} <span
                            class="text-[10px] uppercase tracking-widest ml-1 font-bold">pts</span></div>
                </div>
            @endif
        </div>

        {{-- Rest of the Ranking --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Top 100 Usuários</h3>
                <span class="text-xs font-medium text-slate-400">Exibindo os usuários com mais de 0 pontos</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <tbody class="divide-y divide-slate-50">
                        @foreach($top->skip(3) as $user)
                            <tr class="hover:bg-slate-50/50 transition-all">
                                <td class="px-8 py-4 w-12">
                                    <span class="text-xs font-black text-slate-300">#{{ $loop->iteration + 3 }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center text-xs font-bold overflow-hidden shadow-sm">
                                            @if($user->profile_photo_url)
                                                <img src="{{ $user->profile_photo_url }}" class="w-full h-full object-cover">
                                            @else
                                                {{ substr($user->name, 0, 1) }}
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900">{{ $user->name }}</p>
                                            <p class="text-[10px] text-slate-400 font-medium">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span
                                            class="text-sm font-black text-slate-900">{{ number_format($user->points, 0, ',', '.') }}</span>
                                        <span
                                            class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">pontos</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        @if($top->count() <= 3)
                            <tr>
                                <td colspan="3" class="px-8 py-12 text-center text-slate-400 italic">
                                    Nenhum outro usuário rankeado ainda.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection