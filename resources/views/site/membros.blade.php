@extends('layouts.app')

@section('title', 'Membros - UNN')

@section('content')
<div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <!-- Hero Section -->
    <section class="pt-10 md:pt-24 pb-8 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6">
                <span class="text-gradient">Membros</span> UNN
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                Conheça os empreendedores que fazem parte da nossa comunidade exclusiva de networking empresarial.
            </p>
        </div>
    </section>

    @if(isset($isDemo) && $isDemo)
    <div class="max-w-7xl mx-auto px-6 mb-8">
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 flex items-center gap-3">
            <i class="fas fa-info-circle text-yellow-600 text-xl"></i>
            <p class="text-yellow-800">
                <strong>Dados de Demonstração:</strong> Estes perfis são exemplos. Membros reais aparecerão quando houver cadastros.
            </p>
        </div>
    </div>
    @endif

    <!-- Stats -->
    <section class="pb-8 px-4 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                    <p class="text-2xl sm:text-3xl font-black" style="color: var(--unn-azul-1)">500+</p>
                    <p class="text-xs sm:text-sm text-gray-500 break-words">Empreendedores</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                    <p class="text-2xl sm:text-3xl font-black" style="color: var(--unn-azul-1)">50+</p>
                    <p class="text-xs sm:text-sm text-gray-500">Mentores</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                    <p class="text-2xl sm:text-3xl font-black" style="color: var(--unn-azul-1)">27</p>
                    <p class="text-xs sm:text-sm text-gray-500">Estados</p>
                </div>
                <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                    <p class="text-2xl sm:text-3xl font-black" style="color: var(--unn-azul-1)">1.2k+</p>
                    <p class="text-xs sm:text-sm text-gray-500">Conexões feitas</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Members Grid -->
    <section class="pb-20 px-6 md:px-12 lg:px-24">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($members as $member)
                    @php
                        $isDemo = $member->is_demo ?? false;
                        $initials = collect(explode(' ', $member->name))->take(2)->map(fn($n) => strtoupper(substr($n, 0, 1)))->join('');
                    @endphp
                    <article class="bg-white rounded-3xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 {{ $isDemo ? 'ring-2 ring-yellow-400' : '' }}">
                        <!-- Header with Gradient (mais baixo para não cortar a foto) -->
                        <div class="h-24 btn-primary relative">
                            @if($isDemo)
                                <span class="absolute top-3 right-3 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full font-semibold">DEMO</span>
                            @endif
                        </div>
                        
                        <!-- Avatar (maior e melhor posicionado) -->
                        <div class="flex justify-center -mt-16">
                            @if(isset($member->avatar) && $member->avatar)
                                <img src="{{ $member->avatar }}" alt="{{ $member->name }}" class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
                            @else
                                <div class="w-32 h-32 rounded-full border-4 border-white shadow-lg btn-primary flex items-center justify-center text-white text-3xl font-bold">
                                    {{ $initials }}
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="p-6 text-center">
                            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $member->name }}</h3>
                            
                            @if(isset($member->bio))
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $member->bio }}</p>
                            @endif

                            @if(isset($member->city) && $member->city)
                            <p class="text-sm text-gray-500 mb-4">
                                <i class="fas fa-map-marker-alt mr-1" style="color: var(--unn-azul-1)"></i>
                                {{ $member->city }}
                            </p>
                            @endif

                            <!-- Stats -->
                            <div class="flex justify-center gap-6 py-4 border-t border-gray-100">
                                <div class="text-center">
                                    <p class="text-lg font-bold text-gray-900">{{ $member->connections ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Conexões</p>
                                </div>
                            </div>

                            <!-- Action Button (largura total, sem ícones ao lado) -->
                            @if(!$isDemo)
                            <a href="{{ route('social.profile', $member->id) }}" class="block w-full btn-primary text-white py-3 rounded-xl font-semibold text-center transition hover:shadow-lg">
                                Ver Perfil
                            </a>
                            @else
                            <button onclick="Swal.fire({
                                title: 'Perfil Demo',
                                text: 'Este é um perfil de demonstração.',
                                icon: 'info',
                                confirmButtonColor: '#1F5EDB'
                            })" class="block w-full btn-primary text-white py-3 rounded-xl font-semibold opacity-75 cursor-not-allowed">
                                Ver Perfil
                            </button>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-16">
                        <div class="bg-white rounded-3xl p-12 shadow-lg max-w-md mx-auto">
                            <i class="fas fa-users text-6xl text-gray-300 mb-6"></i>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Nenhum membro ainda</h3>
                            <p class="text-gray-500 mb-6">Seja o primeiro a fazer parte da nossa comunidade!</p>
                            <a href="{{ route('register') }}" class="btn-primary text-white px-8 py-3 rounded-full font-semibold inline-flex items-center gap-2">
                                <i class="fas fa-user-plus"></i> Fazer parte
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 px-6 md:px-12 lg:px-24" style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h2 class="text-3xl lg:text-4xl font-black mb-4">Faça parte desta comunidade</h2>
            <p class="text-lg opacity-90 mb-8">Conecte-se com empreendedores de sucesso e expanda sua rede de negócios.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition" style="color: var(--unn-azul-1)">
                <i class="fas fa-rocket"></i>
                Quero fazer parte
            </a>
        </div>
    </section>
</div>

<style>
.text-gradient {
    background: linear-gradient(135deg, var(--unn-azul-1) 0%, var(--unn-azul-3) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
