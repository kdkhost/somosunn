@extends('layouts.app')

@section('content')
<div class="py-20 bg-slate-50">
    <div class="container mx-auto px-4">
        <!-- Banner Premium -->
        <div class="max-w-7xl mx-auto mb-12">
            <div class="bg-gradient-to-r from-blue-600 via-blue-400 to-blue-300 rounded-3xl shadow-xl flex flex-col md:flex-row items-center justify-between px-8 py-10 md:py-16 gap-8 animate-fade-in">
                <div class="flex-1 text-white">
                    <h1 class="text-5xl font-black mb-4 drop-shadow-lg">Oportunidades de Carreira</h1>
                    <p class="text-2xl mb-6 font-medium">Conecte-se com empresas parceiras e impulsione sua carreira na comunidade <span class="font-bold">SOMOS UNN</span>.</p>
                    <a href="/cadastro-curriculo" class="inline-block bg-white text-blue-700 font-bold px-8 py-4 rounded-2xl shadow-lg hover:bg-blue-50 transition text-xl mt-2 animate-bounce">Cadastre seu currículo</a>
                </div>
                <div class="hidden md:block">
                    <img src="/img/banner-career.svg" alt="Banner Oportunidades" class="w-80 h-auto animate-float" />
                </div>
            </div>
        </div>
        <!-- Fim Banner Premium -->
        <div class="max-w-4xl mx-auto text-center mb-16">
            <h2 class="text-3xl font-bold text-slate-900 mb-4">Vagas abertas</h2>
            <p class="text-xl text-slate-600">Confira as vagas disponíveis e filtre por área, local ou empresa.</p>
        </div>

        <form method="GET" action="" class="mb-8 bg-white rounded-2xl shadow p-6 flex flex-wrap gap-4 justify-center border border-slate-100">
            <div>
                <label for="area" class="block text-sm font-medium text-gray-700">Área</label>
                <input type="text" name="area" id="area" value="{{ request('area') }}" class="mt-1 block w-40 rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="local" class="block text-sm font-medium text-gray-700">Local</label>
                <input type="text" name="local" id="local" value="{{ request('local') }}" class="mt-1 block w-40 rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="empresa" class="block text-sm font-medium text-gray-700">Empresa</label>
                <input type="text" name="empresa" id="empresa" value="{{ request('empresa') }}" class="mt-1 block w-40 rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo de vaga</label>
                <input type="text" name="tipo" id="tipo" value="{{ request('tipo') }}" class="mt-1 block w-40 rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition" aria-label="Filtrar vagas">Filtrar</button>
                <a href="{{ url()->current() }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition" aria-label="Limpar filtros">Limpar filtros</a>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @forelse($vagas as $vaga)
                <div class="bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100 flex flex-col transition-all hover:-translate-y-2">
                    <div class="flex items-center justify-between mb-6">
                        <span class="px-4 py-1.5 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider">
                            {{ $vaga->type ?? 'Tipo' }}
                        </span>
                        <span class="text-xs font-bold text-slate-400">
                            <i class="far fa-calendar-alt mr-1"></i> {{ $vaga->created_at ? $vaga->created_at->format('d/m/Y') : '' }}
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">{{ $vaga->title ?? 'Título da vaga' }}</h3>
                    <p class="text-blue-600 font-bold mb-4">{{ $vaga->company_name ?? 'Empresa Confidencial' }}</p>
                    <div class="flex items-center gap-4 text-sm text-slate-500 mb-6 font-medium">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-map-marker-alt text-slate-300"></i> {{ $vaga->location ?? 'Não informado' }}
                        </span>
                        @if($vaga->salary_range)
                            <span class="flex items-center gap-1">
                                <i class="fas fa-coins text-slate-300"></i> {{ $vaga->salary_range }}
                            </span>
                        @endif
                    </div>
                    <p class="text-slate-600 mb-8 line-clamp-3">
                        {{ $vaga->short_description ?? Str::limit(strip_tags($vaga->description), 150) }}
                    </p>
                    <a href="{{ route('jobs.public.show', $vaga->id) }}"
                        class="mt-auto w-full py-4 bg-slate-900 hover:bg-black text-white rounded-2xl font-bold text-center transition-all">
                        Ver Detalhes da Vaga
                    </a>
                </div>
            @empty
                <div class="col-span-full py-20 text-center">
                    <div class="text-slate-300 text-6xl mb-6 opacity-30">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <p class="text-xl text-slate-500 font-medium italic">Nenhuma vaga aberta no momento. Volte em breve!</p>
                </div>
            @endforelse
        </div>
        <div class="mt-8 flex justify-center">
            {{ $vagas->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
