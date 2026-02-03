@extends('layouts.app')

@section('title', 'UNN - Networking Premium')

@section('content')
<section id="inicio" class="pt-28 md:pt-40 pb-20 px-6 md:px-12 lg:px-24 bg-gradient-to-b from-slate-50 via-white to-white">
    <div class="max-w-7xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div class="animate-fadeInUp">
                <div class="inline-block bg-blue-100 text-blue-700 px-4 py-2 rounded-full text-sm font-600 mb-6">
                    <i class="fas fa-star mr-2"></i> A maior comunidade de networking do Brasil
                </div>
                <h1 class="text-5xl lg:text-7xl font-900 leading-tight mb-8">Conectando<br /><span class="text-gradient">empreendedores</span></h1>
                <p class="text-lg text-gray-600 mb-10 leading-relaxed max-w-xl">Faça parte de uma comunidade estratégica onde empreendedores compartilham experiências, constroem conexões reais e crescem juntos.</p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('register') }}" class="btn-primary text-white px-10 py-4 rounded-lg font-semibold text-lg relative z-10">Quero fazer parte <i class="fas fa-arrow-right ml-2"></i></a>
                    <a href="#" class="border-2 border-gray-300 text-gray-700 px-10 py-4 rounded-lg font-semibold hover:border-blue-600 hover:text-blue-600 transition">Conheça a UNN</a>
                </div>
            </div>

            <div class="hidden lg:block animate-slideInRight animate-float">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-blue-400 rounded-3xl opacity-20 blur-3xl"></div>
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1080" alt="Networking" class="relative w-full h-96 object-cover rounded-3xl shadow-2xl">
                </div>
            </div>
        </div>
    </div>
</section>

@endsection