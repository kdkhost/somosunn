@extends('layouts.app')

@section('content')
<div class="min-h-screen py-12 bg-gray-100">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-4xl font-bold text-center text-primary mb-4">Oportunidades de Carreira</h1>
        <p class="text-center text-lg text-gray-700 mb-8">Confira as vagas abertas em nossa rede de empresas parceiras e na comunidade <span class="font-semibold text-primary">SOMOS UNN</span>.</p>

        <form method="GET" action="" class="mb-8 bg-gray-50 rounded-lg shadow p-6 flex flex-wrap gap-4 justify-center border border-gray-200">
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($vagas as $vaga)
                        <div class="bg-white shadow-lg rounded-lg p-6 border border-gray-200 transition-transform hover:-translate-y-1 hover:shadow-xl duration-200">
                            <div class="flex items-center mb-2">
                                <h2 class="text-2xl font-semibold text-primary mr-2">{{ $vaga->title ?? 'Título da vaga' }}</h2>
                                @if(isset($vaga->partner) && $vaga->partner)
                                    <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1 rounded-full">Empresa Parceira</span>
                                @endif
                            </div>
                            <p class="text-gray-800 mb-2">Empresa: <span class="font-semibold">{{ $vaga->company_name ?? 'Empresa' }}</span></p>
                            <p class="text-gray-800 mb-2">Local: <span class="font-semibold">{{ $vaga->location ?? 'Local' }}</span></p>
                            <p class="text-gray-800 mb-4">Salário: <span class="font-semibold text-green-700">{{ $vaga->salary_range ?? 'A combinar' }}</span></p>
                            <ul class="list-disc ml-5 text-gray-900 mb-4">
                                @if(!empty($vaga->requirements))
                                    @foreach(explode("\n", $vaga->requirements) as $req)
                                        <li>{{ trim($req) }}</li>
                                    @endforeach
                                @else
                                    <li>Requisitos não informados</li>
                                @endif
                            </ul>
                            <div class="flex gap-2 mt-2">
                                <a href="{{ route('jobs.show', $vaga->id) }}" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary-dark transition" aria-label="Ver detalhes da vaga {{ $vaga->title }}">Ver detalhes</a>
                                <form method="POST" action="{{ route('jobs.apply', $vaga->id) }}">
                                    @csrf
                                    <button type="submit" class="bg-success text-white px-4 py-2 rounded hover:bg-success-dark transition" aria-label="Candidatar-se rapidamente à vaga {{ $vaga->title }}">Candidatura rápida</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 flex flex-col items-center justify-center py-16 bg-gradient-to-b from-blue-50 to-white rounded-lg shadow-md">
                            <div class="mb-6">
                                <svg width="64" height="64" fill="none" xmlns="http://www.w3.org/2000/svg" class="animate-bounce">
                                    <rect x="16" y="24" width="32" height="24" rx="6" fill="#2563eb"/>
                                    <rect x="24" y="32" width="16" height="8" rx="2" fill="#fff"/>
                                    <circle cx="32" cy="20" r="6" fill="#2563eb"/>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-blue-900 mb-2">Nenhuma vaga disponível</h2>
                            <p class="text-lg text-gray-600 mb-4">No momento não há oportunidades abertas.<br>Fique atento, novas vagas serão publicadas em breve!</p>
                            <span class="text-sm text-gray-400">Acompanhe a comunidade para novidades.</span>
                        </div>
                    @endforelse
        </div>
        <div class="mt-8 flex justify-center">
            {{ $vagas->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
