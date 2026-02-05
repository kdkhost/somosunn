@extends('layouts.app')

@section('title', 'Planos Premium - UNN')

@section('content')
    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <!-- Hero Section -->
        <section class="pt-10 md:pt-24 pb-12 md:pb-16 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="inline-block px-4 py-2 rounded-full text-sm font-bold mb-4 md:mb-6"
                            style="background: var(--unn-azul-1); color: white">
                            <i class="fas fa-crown mr-2"></i> Associação Premium
                        </span>
                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black leading-tight mb-4 md:mb-6">
                            Invista no seu <span class="text-gradient">crescimento</span>
                        </h1>
                        <p class="text-xl text-gray-600 leading-relaxed mb-8">
                            Escolha o plano ideal para você e desbloqueie todo o potencial da maior comunidade de networking
                            do Brasil.
                        </p>
                        <div class="flex items-center gap-6 text-sm text-gray-500">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-check-circle" style="color: var(--unn-azul-1)"></i> Sem fidelidade
                            </span>
                            <span class="flex items-center gap-2">
                                <i class="fas fa-check-circle" style="color: var(--unn-azul-1)"></i> Cancele quando quiser
                            </span>
                        </div>
                    </div>
                    <div class="hidden lg:block">
                        <div class="relative">
                            <div class="absolute inset-0 btn-primary rounded-3xl opacity-20 blur-3xl"></div>
                            <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800"
                                alt="Networking" class="relative w-full rounded-3xl shadow-2xl">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section class="py-16 px-6 md:px-12 lg:px-24" id="planos">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-black text-gray-900 mb-4">Escolha seu Plano</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">Todos os planos incluem acesso à comunidade. Quanto maior o
                        plano, mais recursos exclusivos.</p>
                </div>

                <div class="grid md:grid-cols-{{ min(3, $plans->count() ?: 1) }} gap-8">
                    @forelse($plans as $plan)
                        <div class="bg-white rounded-3xl p-6 md:p-8 shadow-lg {{ $plan->highlight ? 'shadow-2xl ring-2 relative' : '' }}"
                            style="{{ $plan->highlight ? '--tw-ring-color: var(--unn-azul-1)' : '' }}">

                            @if($plan->highlight)
                                <span
                                    class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 btn-primary text-white text-sm font-bold rounded-full">
                                    MAIS POPULAR
                                </span>
                            @endif

                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                            <div class="mb-2">
                                @if($plan->price > 0)
                                    <span class="text-5xl font-black" style="color: var(--unn-azul-1)">R$
                                        {{ number_format($plan->price, 0, ',', '.') }}</span>
                                    <span class="text-gray-500">/{{ $plan->period }}</span>
                                @else
                                    <span class="text-5xl font-black text-gray-900">Grátis</span>
                                @endif
                            </div>

                            @if($plan->description)
                                <p class="text-gray-500 mb-8">{{ $plan->description }}</p>
                            @else
                                <p class="text-gray-500 mb-8">&nbsp;</p>
                            @endif

                            <ul class="space-y-4 mb-8">
                                @php $benefits = is_array($plan->benefits) ? $plan->benefits : json_decode($plan->benefits ?? '[]', true); @endphp
                                @foreach($benefits as $benefit)
                                    <li class="flex items-center gap-3 text-gray-600">
                                        <i class="fas fa-check text-green-500"></i>
                                        {{ $benefit }}
                                    </li>
                                @endforeach
                            </ul>

                            <a href="{{ $plan->price > 0 ? route('subscription.checkout', $plan) : route('register') }}"
                                class="block w-full text-center py-4 rounded-xl font-bold transition {{ $plan->highlight ? 'btn-primary text-white shadow-lg hover:shadow-xl' : 'border-2 hover:bg-slate-50' }}"
                                style="{{ !$plan->highlight ? 'border-color: var(--unn-azul-1); color: var(--unn-azul-1)' : '' }}">
                                {{ $plan->price > 0 ? 'Assinar ' . ($plan->name) : 'Começar grátis' }}
                            </a>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-12">
                            <p class="text-gray-500 italic">Nenhum plano disponível no momento.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- Benefícios -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-12 text-center">O que você recebe como Premium</h2>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @php
                        $benefits = [
                            ['icon' => 'users', 'title' => 'Conexões Ilimitadas', 'desc' => 'Conecte-se com quantos membros quiser, sem limites mensais.'],
                            ['icon' => 'graduation-cap', 'title' => 'Biblioteca de Cursos', 'desc' => 'Acesso a mais de 50 cursos exclusivos sobre negócios e gestão.'],
                            ['icon' => 'video', 'title' => 'Lives Exclusivas', 'desc' => 'Participe de transmissões ao vivo com mentores de sucesso.'],
                            ['icon' => 'calendar-check', 'title' => 'Eventos Premium', 'desc' => 'Acesso VIP a eventos presenciais em todo o Brasil.'],
                            ['icon' => 'comments', 'title' => 'Grupos Privados', 'desc' => 'Participe de grupos segmentados por setor e interesse.'],
                            ['icon' => 'certificate', 'title' => 'Certificados', 'desc' => 'Receba certificados de conclusão de cursos e eventos.'],
                        ];
                    @endphp

                    @foreach($benefits as $benefit)
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas fa-{{ $benefit['icon'] }} text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 mb-1">{{ $benefit['title'] }}</h3>
                                <p class="text-sm text-gray-600">{{ $benefit['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Comparativo -->
        <section class="py-16 px-6 md:px-12 lg:px-24">
            <div class="max-w-5xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">Compare os planos</h2>

                <div class="bg-white rounded-3xl shadow-lg overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left p-6 text-gray-900 font-bold">Recurso</th>
                                <th class="text-center p-6 text-gray-500">Comunidade</th>
                                <th class="text-center p-6 font-bold" style="background: var(--unn-azul-1); color: white">
                                    Premium</th>
                                <th class="text-center p-6 text-gray-500">Business</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <tr class="border-b border-gray-50">
                                <td class="p-4 text-gray-700">Perfil na comunidade</td>
                                <td class="text-center p-4"><i class="fas fa-check text-green-500"></i></td>
                                <td class="text-center p-4" style="background: var(--unn-azul-1)10"><i
                                        class="fas fa-check text-green-500"></i></td>
                                <td class="text-center p-4"><i class="fas fa-check text-green-500"></i></td>
                            </tr>
                            <tr class="border-b border-gray-50">
                                <td class="p-4 text-gray-700">Conexões por mês</td>
                                <td class="text-center p-4 text-gray-500">5</td>
                                <td class="text-center p-4 font-bold" style="background: var(--unn-azul-1)10">Ilimitadas
                                </td>
                                <td class="text-center p-4">Ilimitadas</td>
                            </tr>
                            <tr class="border-b border-gray-50">
                                <td class="p-4 text-gray-700">Acesso a cursos</td>
                                <td class="text-center p-4"><i class="fas fa-times text-gray-300"></i></td>
                                <td class="text-center p-4" style="background: var(--unn-azul-1)10"><i
                                        class="fas fa-check text-green-500"></i></td>
                                <td class="text-center p-4"><i class="fas fa-check text-green-500"></i></td>
                            </tr>
                            <tr class="border-b border-gray-50">
                                <td class="p-4 text-gray-700">Eventos exclusivos</td>
                                <td class="text-center p-4"><i class="fas fa-times text-gray-300"></i></td>
                                <td class="text-center p-4" style="background: var(--unn-azul-1)10"><i
                                        class="fas fa-check text-green-500"></i></td>
                                <td class="text-center p-4"><i class="fas fa-check text-green-500"></i></td>
                            </tr>
                            <tr class="border-b border-gray-50">
                                <td class="p-4 text-gray-700">Mentoria em grupo</td>
                                <td class="text-center p-4"><i class="fas fa-times text-gray-300"></i></td>
                                <td class="text-center p-4" style="background: var(--unn-azul-1)10">1/mês</td>
                                <td class="text-center p-4">Ilimitada</td>
                            </tr>
                            <tr class="border-b border-gray-50">
                                <td class="p-4 text-gray-700">Mentoria individual</td>
                                <td class="text-center p-4"><i class="fas fa-times text-gray-300"></i></td>
                                <td class="text-center p-4" style="background: var(--unn-azul-1)10"><i
                                        class="fas fa-times text-gray-300"></i></td>
                                <td class="text-center p-4">1/mês</td>
                            </tr>
                            <tr>
                                <td class="p-4 text-gray-700">Suporte prioritário</td>
                                <td class="text-center p-4"><i class="fas fa-times text-gray-300"></i></td>
                                <td class="text-center p-4" style="background: var(--unn-azul-1)10"><i
                                        class="fas fa-times text-gray-300"></i></td>
                                <td class="text-center p-4"><i class="fas fa-check text-green-500"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Depoimentos -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-12 text-center">O que dizem nossos membros Premium</h2>

                <div class="grid md:grid-cols-3 gap-8">
                    @php
                        $testimonials = [
                            ['name' => 'Carlos Mendes', 'role' => 'CEO, Tech Solutions', 'text' => 'Desde que me tornei Premium, fechei 3 parcerias estratégicas que mudaram meu negócio. O ROI foi absurdo!', 'rating' => 5],
                            ['name' => 'Ana Paula Lima', 'role' => 'Fundadora, EcoModa', 'text' => 'As mentorias exclusivas valem cada centavo. Acesso a conhecimento que eu não encontraria em nenhum outro lugar.', 'rating' => 5],
                            ['name' => 'Roberto Silva', 'role' => 'Diretor, Investimentos RS', 'text' => 'A qualidade das conexões no plano Premium é incomparável. Networking de verdade, com pessoas sérias.', 'rating' => 5],
                        ];
                    @endphp

                    @foreach($testimonials as $testimonial)
                        <div class="bg-slate-50 rounded-3xl p-8">
                            <div class="flex gap-1 mb-4">
                                @for($i = 0; $i < $testimonial['rating']; $i++)
                                    <i class="fas fa-star text-yellow-500"></i>
                                @endfor
                            </div>
                            <p class="text-gray-600 mb-6 italic">"{{ $testimonial['text'] }}"</p>
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 btn-primary rounded-full flex items-center justify-center text-white font-bold">
                                    {{ substr($testimonial['name'], 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ $testimonial['name'] }}</p>
                                    <p class="text-sm text-gray-500">{{ $testimonial['role'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="py-16 px-6 md:px-12 lg:px-24">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">Perguntas Frequentes</h2>

                <div class="space-y-4">
                    @php
                        $faqs = [
                            ['q' => 'Posso cancelar a qualquer momento?', 'a' => 'Sim! Não temos taxa de cancelamento ou fidelidade. Você pode cancelar quando quiser pelo próprio painel.'],
                            ['q' => 'Como funciona o pagamento?', 'a' => 'Aceitamos cartão de crédito, PIX e boleto. O pagamento é recorrente mensal ou anual, conforme sua escolha.'],
                            ['q' => 'O que acontece se eu fizer downgrade?', 'a' => 'Você perde acesso aos benefícios premium imediatamente, mas mantém seu perfil e conexões feitas.'],
                            ['q' => 'Posso migrar do mensal para o anual?', 'a' => 'Sim! A migração é simples e você ganha o desconto proporcional ao período restante.'],
                        ];
                    @endphp

                    @foreach($faqs as $faq)
                        <div class="bg-white rounded-2xl p-6 shadow-lg">
                            <h3 class="font-bold text-gray-900 mb-2">{{ $faq['q'] }}</h3>
                            <p class="text-gray-600">{{ $faq['a'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- CTA Final -->
        <section class="py-16 px-6 md:px-12 lg:px-24"
            style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
            <div class="max-w-4xl mx-auto text-center text-white">
                <h2 class="text-3xl lg:text-4xl font-black mb-4">Pronto para acelerar seu crescimento?</h2>
                <p class="text-lg opacity-90 mb-8">Junte-se a milhares de empreendedores que já transformaram seus negócios.
                </p>

                @php
                    $ctaPlan = ($plans ?? collect())->firstWhere('highlight', true)
                        ?? ($plans ?? collect())->first(fn($p) => (float) $p->price > 0)
                        ?? ($plans ?? collect())->first();

                    $ctaHref = $ctaPlan && (float) $ctaPlan->price > 0
                        ? route('subscription.checkout', $ctaPlan)
                        : route('register');
                @endphp
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ $ctaHref }}"
                        class="inline-flex items-center justify-center gap-2 bg-white px-8 py-4 rounded-full font-bold hover:bg-blue-50 transition"
                        style="color: var(--unn-azul-1)">
                        <i class="fas fa-crown"></i>
                        @if($ctaPlan)
                            @if((float) $ctaPlan->price > 0)
                                Assinar {{ $ctaPlan->name }} - R$ {{ number_format($ctaPlan->price, 0, ',', '.') }}/{{ $ctaPlan->period }}
                            @else
                                Começar grátis
                            @endif
                        @else
                            Criar conta
                        @endif
                    </a>
                    <a href="#planos"
                        class="inline-flex items-center justify-center gap-2 border-2 border-white text-white px-8 py-4 rounded-full font-bold hover:bg-white/10 transition">
                        Ver todos os planos
                    </a>
                </div>
            </div>
        </section>

        <!-- Garantia -->
        <section class="py-8 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-4xl mx-auto text-center">
                <div class="flex items-center justify-center gap-6 flex-wrap text-sm text-gray-500">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-shield-alt" style="color: var(--unn-azul-1)"></i> Pagamento seguro
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="fas fa-undo" style="color: var(--unn-azul-1)"></i> Garantia de 7 dias
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="fas fa-lock" style="color: var(--unn-azul-1)"></i> Dados protegidos
                    </span>
                    <span class="flex items-center gap-2">
                        <i class="fas fa-headset" style="color: var(--unn-azul-1)"></i> Suporte humanizado
                    </span>
                </div>
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
    </style>
@endsection
