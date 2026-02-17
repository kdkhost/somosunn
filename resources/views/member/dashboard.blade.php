@extends('member.layout')
@section('title', 'Painel do Membro')
@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Bem-vindo ao seu painel!</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        @if(auth()->user()->canAccessFeature('courses'))
            <x-widgets.metric title="Meus Cursos" icon="fas fa-graduation-cap" :value="$coursesCount ?? 0" color="blue" />
        @endif
        @if(auth()->user()->canAccessFeature('events'))
            <x-widgets.metric title="Eventos" icon="fas fa-calendar-alt" :value="$eventsCount ?? 0" color="yellow" />
        @endif
        @if(auth()->user()->canAccessFeature('marketplace'))
            <x-widgets.metric title="Marketplace" icon="fas fa-store" :value="$salesCount ?? 0" color="purple" />
        @endif
        @if(auth()->user()->canAccessFeature('certificates'))
            <x-widgets.metric title="Certificados" icon="fas fa-certificate" :value="$certificatesCount ?? 0" color="lime" />
        @endif
        @if(auth()->user()->canAccessFeature('community'))
            <x-widgets.metric title="Comunidade" icon="fas fa-users" :value="$communityCount ?? 0" color="cyan" />
        @endif
        @if(auth()->user()->canAccessFeature('ranking'))
            <x-widgets.metric title="Ranking" icon="fas fa-star" :value="$rankingPoints ?? 0" color="pink" />
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @if(auth()->user()->canAccessFeature('visits'))
            <x-widgets.visits title="Visitas ao Perfil" :value="$visitsCount ?? 0" color="blue" />
        @endif
        @if(auth()->user()->canAccessFeature('sales'))
            <x-widgets.sales title="Vendas Realizadas" :value="$salesCount ?? 0" color="purple" />
        @endif
        @if(auth()->user()->canAccessFeature('services'))
            <x-widgets.services title="Serviços Prestados" :value="$servicesCount ?? 0" color="teal" />
        @endif
        @if(auth()->user()->canAccessFeature('products'))
            <x-widgets.products title="Produtos Vendidos" :value="$productsCount ?? 0" color="orange" />
        @endif
    </div>

    <div class="mt-8">
        @if(auth()->user()->canAccessFeature('sales.chart'))
            <x-widgets.chart title="Histórico de Vendas" :labels="$salesChartLabels ?? []" :data="$salesChartData ?? []" color="indigo" />
        @endif
    </div>
</div>
@endsection
