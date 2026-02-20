@extends('layouts.app')

@section('content')
<div class="bg-white min-h-screen py-12">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold text-center text-blue-800 mb-4">Oportunidades de Carreira</h1>
        <p class="text-center text-lg text-gray-800 mb-8">Confira as vagas abertas em nossa rede de empresas parceiras e na comunidade SOMOS UNN.</p>

        <form method="GET" action="" class="mb-8 bg-white rounded-lg shadow p-6 flex flex-wrap gap-4 justify-center">
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
                        <div class="bg-gray-100 shadow-lg rounded-lg p-6 border border-gray-300">
                            <div class="flex items-center mb-2">
                                <h2 class="text-2xl font-semibold text-blue-800 mr-2">{{ $vaga->title ?? 'Título da vaga' }}</h2>
                                @if(isset($vaga->partner) && $vaga->partner)
                                    <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1 rounded-full">Empresa Parceira</span>
                                @endif
                            </div>
                            <p class="text-gray-800 mb-2">Empresa: {{ $vaga->company_name ?? 'Empresa' }}</p>
                            <p class="text-gray-800 mb-2">Local: {{ $vaga->location ?? 'Local' }}</p>
                            <p class="text-gray-800 mb-4">Salário: {{ $vaga->salary_range ?? 'A combinar' }}</p>
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
                                <a href="{{ route('jobs.show', $vaga->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition" aria-label="Ver detalhes da vaga {{ $vaga->title }}">Ver detalhes</a>
                                <form method="POST" action="{{ route('jobs.apply', $vaga->id) }}">
                                    @csrf
                                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition" aria-label="Candidatar-se rapidamente à vaga {{ $vaga->title }}">Candidatura rápida</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center text-gray-500 py-12">
                            <i class="fas fa-briefcase text-4xl mb-4"></i>
                            <p>Nenhuma vaga aberta no momento. Volte em breve!</p>
                        </div>
                    @endforelse
        </div>
        <div class="mt-8 flex justify-center">
            {{ $vagas->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
