@extends('member.layout')
@section('title', 'Painel do Membro')
@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Bem-vindo ao seu painel!</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Cards de funções administrativas -->
        <a href="/painel/cursos" class="block bg-white rounded-lg shadow p-6 hover:bg-blue-50 transition">
            <div class="flex items-center gap-3">
                <i class="fas fa-graduation-cap text-blue-700 text-2xl"></i>
                <span class="font-semibold text-lg">Meus Cursos</span>
            </div>
            <p class="text-gray-600 mt-2">Gerencie seus cursos, acesse conteúdos e acompanhe seu progresso.</p>
        </a>
        <a href="/painel/eventos" class="block bg-white rounded-lg shadow p-6 hover:bg-blue-50 transition">
            <div class="flex items-center gap-3">
                <i class="fas fa-calendar-alt text-blue-700 text-2xl"></i>
                <span class="font-semibold text-lg">Eventos</span>
            </div>
            <p class="text-gray-600 mt-2">Veja eventos disponíveis, participe e acompanhe certificados.</p>
        </a>
        <a href="/painel/marketplace" class="block bg-white rounded-lg shadow p-6 hover:bg-blue-50 transition">
            <div class="flex items-center gap-3">
                <i class="fas fa-store text-blue-700 text-2xl"></i>
                <span class="font-semibold text-lg">Marketplace</span>
            </div>
            <p class="text-gray-600 mt-2">Gerencie vendas, pagamentos e configurações de recebimento.</p>
        </a>
        <a href="/painel/certificados" class="block bg-white rounded-lg shadow p-6 hover:bg-blue-50 transition">
            <div class="flex items-center gap-3">
                <i class="fas fa-certificate text-blue-700 text-2xl"></i>
                <span class="font-semibold text-lg">Certificados</span>
            </div>
            <p class="text-gray-600 mt-2">Visualize e baixe seus certificados de cursos e eventos.</p>
        </a>
    </div>
</div>
@endsection
