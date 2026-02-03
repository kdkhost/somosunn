@extends('layouts.app')

@section('title', 'Portal de Networking - UNN')

@section('content')
<section class="pt-32 pb-20 px-6 md:px-12 lg:px-24 hero-landing">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-20 animate-fadeInUp">
            <h1 class="text-5xl lg:text-6xl font-900 leading-tight mb-6">Portal de <span class="text-gradient">Networking Digital</span></h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">Acesse palestras, mentorias premium e recursos exclusivos para potencializar seu crescimento empreendedor.</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-white rounded-2xl p-8 text-center border border-gray-200">
                <p class="text-4xl font-bold text-blue-600 mb-2">4</p>
                <p class="text-gray-700 font-semibold">Palestras<br><span class="text-sm text-gray-500">Gratuitas</span></p>
            </div>
            <div class="bg-white rounded-2xl p-8 text-center border border-gray-200">
                <p class="text-4xl font-bold text-green-600 mb-2">4</p>
                <p class="text-gray-700 font-semibold">Mentorias<br><span class="text-sm text-gray-500">Premium</span></p>
            </div>
            <div class="bg-white rounded-2xl p-8 text-center border border-gray-200">
                <p class="text-4xl font-bold text-purple-600 mb-2">4</p>
                <p class="text-gray-700 font-semibold">Níveis de<br><span class="text-sm text-gray-500">Networking</span></p>
            </div>
            <div class="bg-white rounded-2xl p-8 text-center border border-gray-200">
                <p class="text-4xl font-bold text-orange-600 mb-2">85%</p>
                <p class="text-gray-700 font-semibold">Taxa de<br><span class="text-sm text-gray-500">Satisfação</span></p>
            </div>
        </div>

        <div class="mt-12 text-center">
            <a href="#" class="btn-primary text-white px-10 py-4 rounded-lg font-semibold">Acessar todas as palestras</a>
        </div>
    </div>
</section>

<section class="py-20 px-6 md:px-12 lg:px-24 bg-slate-50">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <h2 class="section-title text-4xl lg:text-5xl font-900">Mentorias & encontros</h2>
            <span class="text-sm uppercase text-gray-500 tracking-wide">Atualizado em tempo real</span>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($mentorings as $mentorship)
                <article class="card bg-white rounded-3xl p-8 border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center mb-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500">{{ optional($mentorship->mentor)->name ?? 'Mentor UNN' }}</p>
                        <span class="text-purple-600 font-bold">R$ {{ number_format($mentorship->price, 2, ',', '.') }}</span>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-2">{{ $mentorship->title }}</h3>
                    <p class="text-gray-600 mb-4">{{ Str::limit($mentorship->description, 120) }}</p>
                    <p class="text-sm text-gray-500 mb-6">Slots disponíveis: <strong>{{ $mentorship->slots }}</strong></p>
                    <button class="btn-primary text-white px-6 py-3 rounded-full font-semibold">Ficha da mentoria</button>
                </article>
            @empty
                <div class="col-span-1 lg:col-span-3 text-center border border-dashed border-gray-300 rounded-3xl p-10 text-gray-500">
                    <p>O portal precisa de mentorias cadastradas para mostrar essa sessão.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="py-20 px-6 md:px-12 lg:px-24 bg-white">
    <div class="max-w-6xl mx-auto">
        <h2 class="section-title text-4xl lg:text-5xl font-900 text-center mb-12">Comunidade segmentada</h2>
        <div class="grid md:grid-cols-2 gap-8">
            <div class="card rounded-3xl p-8 bg-slate-50 border border-gray-200 text-center">
                <p class="text-sm uppercase font-semibold text-gray-500 mb-3">Iniciantes</p>
                <p class="text-5xl font-bold text-blue-600">{{ $levelSummary['iniciante'] ?? 0 }}</p>
                <p class="text-gray-600 mt-4">Interações restritas ao mesmo nível, garantindo acolhimento e aprendizado seguro.</p>
            </div>
            <div class="card rounded-3xl p-8 bg-slate-50 border border-gray-200 text-center">
                <p class="text-sm uppercase font-semibold text-gray-500 mb-3">Empresários de sucesso</p>
                <p class="text-5xl font-bold text-purple-600">{{ $levelSummary['sucesso'] ?? 0 }}</p>
                <p class="text-gray-600 mt-4">Líderes e mentores conectando oportunidades premium e trocando insights estratégicos.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-20 px-6 md:px-12 lg:px-24 bg-slate-50">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h2 class="section-title text-4xl lg:text-5xl font-900">Ranking de conexões</h2>
            <p class="text-sm text-gray-500">Notas calculadas após cada feedback recebido.</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($topRankings as $rank)
                <article class="card bg-white rounded-3xl border border-gray-200 p-6 shadow-sm hover:shadow-lg transition">
                    <p class="text-xs uppercase text-gray-500">{{ ucfirst($rank->level) }}</p>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ optional($rank->user)->name ?? 'Empreendedor' }}</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ $rank->interactions_count }} conexões avaliadas</p>
                    <div class="text-lg font-bold text-blue-600">Score {{ number_format($rank->score, 2, ',', '.') }}</div>
                    <p class="text-sm text-gray-600 mt-2">Média {{ number_format($rank->average_rating, 1, ',', '.') }}</p>
                </article>
            @empty
                <div class="col-span-1 lg:col-span-3 border border-dashed border-gray-300 rounded-3xl p-10 text-center text-gray-500">
                    <p>Registre conexões e pesquisas de satisfação para ver o ranking.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

@endsection
