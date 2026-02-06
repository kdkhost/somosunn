@extends('layouts.app')

@section('title', 'Planos Premium - UNN')

@push('styles')
    <style>
        .unn-star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 6px;
        }

        .unn-star-rating input {
            display: none;
        }

        .unn-star-rating label {
            cursor: pointer;
            color: #cbd5e1; /* slate-300 */
            font-size: 28px;
            line-height: 1;
            transition: color 0.15s ease;
        }

        .unn-star-rating input:checked~label,
        .unn-star-rating label:hover,
        .unn-star-rating label:hover~label {
            color: #f59e0b; /* amber-500 */
        }
    </style>
@endpush

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

                @php
                    $plansCollection = ($plans ?? collect())->values();

                    $highlightPlan = $plansCollection->firstWhere('highlight', true);
                    $cheapestPlan = $plansCollection->sortBy('price')->first();
                    $mostExpensivePlan = $plansCollection->sortByDesc('price')->first();

                    $comparePlans = collect([$cheapestPlan, $highlightPlan, $mostExpensivePlan])
                        ->filter()
                        ->unique('id')
                        ->values();

                    foreach ($plansCollection as $candidate) {
                        if ($comparePlans->count() >= min(3, $plansCollection->count())) {
                            break;
                        }
                        if (!$comparePlans->contains('id', $candidate->id)) {
                            $comparePlans->push($candidate);
                        }
                    }

                    $comparisonRows = [
                        ['type' => 'permission', 'label' => 'Perfil na comunidade', 'permission' => 'community'],
                        ['type' => 'text', 'label' => 'Conexões por mês', 'field' => 'connections_per_month'],
                        ['type' => 'permission', 'label' => 'Acesso a cursos', 'permission' => 'courses'],
                        ['type' => 'permission', 'label' => 'Eventos exclusivos', 'permission' => 'events'],
                        ['type' => 'mentorship_group', 'label' => 'Mentoria em grupo', 'field' => 'group_mentorship'],
                        ['type' => 'text', 'label' => 'Mentoria individual', 'field' => 'individual_mentorship'],
                        ['type' => 'boolean', 'label' => 'Suporte prioritário', 'field' => 'priority_support'],
                    ];
                @endphp

                <div class="bg-white rounded-3xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px]">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left p-6 text-gray-900 font-bold">Recurso</th>
                                    @foreach($comparePlans as $plan)
                                        <th class="text-center p-6 {{ $plan->highlight ? 'font-black' : 'text-gray-500 font-bold' }}"
                                            style="{{ $plan->highlight ? 'background: var(--unn-azul-1); color: white' : '' }}">
                                            {{ $plan->name }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                @foreach($comparisonRows as $row)
                                    <tr class="border-b border-gray-50">
                                        <td class="p-4 text-gray-700">{{ $row['label'] }}</td>
                                        @foreach($comparePlans as $plan)
                                            @php
                                                $isHighlighted = (bool) ($plan->highlight ?? false);
                                                $cellStyle = $isHighlighted ? 'background: var(--unn-azul-1)10' : '';
                                            @endphp
                                            <td class="text-center p-4" style="{{ $cellStyle }}">
                                                @if($row['type'] === 'permission')
                                                    @php
                                                        $perm = $row['permission'] ?? null;
                                                        $has = $perm ? (method_exists($plan, 'hasFeature') ? $plan->hasFeature($perm) : in_array($perm, (array) ($plan->permissions ?? []))) : false;
                                                    @endphp
                                                    @if($has)
                                                        <i class="fas fa-check text-green-500"></i>
                                                    @else
                                                        <i class="fas fa-times text-gray-300"></i>
                                                    @endif
                                                @elseif($row['type'] === 'boolean')
                                                    @php
                                                        $flag = (bool) data_get($plan->comparison, $row['field'] ?? '', false);
                                                    @endphp
                                                    @if($flag)
                                                        <i class="fas fa-check text-green-500"></i>
                                                    @else
                                                        <i class="fas fa-times text-gray-300"></i>
                                                    @endif
                                                @elseif($row['type'] === 'mentorship_group')
                                                    @php
                                                        $val = trim((string) data_get($plan->comparison, $row['field'] ?? ''));
                                                        $hasMentorships = method_exists($plan, 'hasFeature') ? $plan->hasFeature('mentorships') : in_array('mentorships', (array) ($plan->permissions ?? []));
                                                        if ($val === '' && $hasMentorships) {
                                                            $val = 'Ilimitada';
                                                        }
                                                    @endphp
                                                    @if($val !== '')
                                                        <span class="{{ $isHighlighted ? 'font-bold text-slate-900' : 'text-gray-700' }}">{{ $val }}</span>
                                                    @else
                                                        <i class="fas fa-times text-gray-300"></i>
                                                    @endif
                                                @else
                                                    @php
                                                        $field = $row['field'] ?? null;
                                                        $val = $field ? trim((string) data_get($plan->comparison, $field)) : '';

                                                        if ($field === 'connections_per_month' && $val === '') {
                                                            $val = (float) $plan->price <= 0 ? '5' : 'Ilimitadas';
                                                        }
                                                    @endphp
                                                    @if($val !== '')
                                                        <span class="{{ $isHighlighted ? 'font-bold text-slate-900' : 'text-gray-700' }}">{{ $val }}</span>
                                                    @else
                                                        <i class="fas fa-times text-gray-300"></i>
                                                    @endif
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-4 text-center">
                    Itens marcados com ✓ são derivados das permissões do plano. Valores como conexões/mentorias e suporte são configuráveis no Admin.
                </p>
            </div>
        </section>

        <!-- Depoimentos -->
        <section class="py-16 px-6 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl font-black text-gray-900 mb-12 text-center">O que dizem nossos membros Premium</h2>

                @if(session('error'))
                    <div class="max-w-3xl mx-auto bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-8">
                        <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="max-w-3xl mx-auto bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mb-8">
                        <i class="fas fa-circle-check mr-2"></i>{{ session('success') }}
                    </div>
                @endif

                @php
                    $approvedTestimonials = ($testimonials ?? collect())->values();

                    $fallbackTestimonials = collect([
                        ['name' => 'Carlos Mendes', 'role' => 'CEO, Tech Solutions', 'text' => 'Desde que me tornei Premium, fechei 3 parcerias estratégicas que mudaram meu negócio. O ROI foi absurdo!', 'rating' => 5],
                        ['name' => 'Ana Paula Lima', 'role' => 'Fundadora, EcoModa', 'text' => 'As mentorias exclusivas valem cada centavo. Acesso a conhecimento que eu não encontraria em nenhum outro lugar.', 'rating' => 5],
                        ['name' => 'Roberto Silva', 'role' => 'Diretor, Investimentos RS', 'text' => 'A qualidade das conexões no plano Premium é incomparável. Networking de verdade, com pessoas sérias.', 'rating' => 5],
                    ]);

                    $displayTestimonials = $approvedTestimonials->isNotEmpty()
                        ? $approvedTestimonials
                        : $fallbackTestimonials;
                @endphp

                <div class="grid md:grid-cols-3 gap-8">
                    @foreach($displayTestimonials as $testimonial)
                        @php
                            $name = data_get($testimonial, 'author_name') ?? data_get($testimonial, 'name') ?? 'Membro UNN';
                            $role = data_get($testimonial, 'author_title') ?? data_get($testimonial, 'role') ?? null;
                            $text = data_get($testimonial, 'content') ?? data_get($testimonial, 'text') ?? '';
                            $rating = data_get($testimonial, 'rating');
                            $isFeatured = (bool) data_get($testimonial, 'is_featured', false);

                            if ($rating !== null) {
                                $rating = (int) $rating;
                                $rating = max(1, min(5, $rating));
                            }
                        @endphp
                        <div class="bg-slate-50 rounded-3xl p-8 relative">
                            @if($isFeatured)
                                <span class="absolute top-5 right-5 text-xs font-bold px-3 py-1 rounded-full text-white"
                                    style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                                    Em destaque
                                </span>
                            @endif

                            <div class="flex gap-1 mb-4">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ ($rating !== null && $i <= $rating) ? 'text-yellow-500' : 'text-slate-300' }}"></i>
                                @endfor
                            </div>
                            <p class="text-gray-600 mb-6 italic">"{{ $text }}"</p>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 btn-primary rounded-full flex items-center justify-center text-white font-bold">
                                    {{ mb_substr($name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ $name }}</p>
                                    @if($role)
                                        <p class="text-sm text-gray-500">{{ $role }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="max-w-3xl mx-auto mt-10 bg-white rounded-3xl shadow-lg p-8 border border-slate-100">
                    <div class="flex items-start justify-between gap-6 flex-wrap">
                        <div>
                            <h3 class="text-xl font-black text-gray-900">Quer deixar seu depoimento?</h3>
                            <p class="text-gray-600 mt-1">Seu depoimento passa por moderação antes de ser publicado.</p>
                        </div>
                    </div>

                    @auth
                        <form action="{{ route('testimonials.store') }}" method="POST" class="mt-6 space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Avaliação (opcional)</label>
                                <div class="flex items-center gap-4 flex-wrap">
                                    @php
                                        $oldRating = old('rating');
                                        $oldRating = is_numeric($oldRating) ? (int) $oldRating : null;
                                        if ($oldRating !== null) {
                                            $oldRating = max(1, min(5, $oldRating));
                                        }
                                    @endphp

                                    <div class="unn-star-rating" role="radiogroup" aria-label="Avaliação por estrelas">
                                        @for($i = 5; $i >= 1; $i--)
                                            <input type="radio" id="testimonial-rating-{{ $i }}" name="rating"
                                                value="{{ $i }}" {{ (string) $oldRating === (string) $i ? 'checked' : '' }}>
                                            <label for="testimonial-rating-{{ $i }}" title="{{ $i }}/5">
                                                <i class="fas fa-star"></i>
                                            </label>
                                        @endfor
                                    </div>

                                    <div class="text-sm text-gray-500">
                                        <input type="radio" id="testimonial-rating-none" name="rating" value=""
                                            {{ $oldRating === null ? 'checked' : '' }} class="sr-only">
                                        <label for="testimonial-rating-none" class="cursor-pointer underline hover:text-gray-700">
                                            Sem avaliação
                                        </label>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Clique nas estrelas para escolher uma nota.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Seu depoimento</label>
                                <textarea name="content" rows="4" required minlength="20" maxlength="2000"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:border-transparent transition resize-none"
                                    style="--tw-ring-color: var(--unn-azul-1)"
                                    placeholder="Conte como a UNN ajudou você a crescer com networking e oportunidades..."></textarea>
                                <p class="text-xs text-gray-500 mt-2">Mínimo: 20 caracteres. Máximo: 2000.</p>
                            </div>

                            <button type="submit" class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2">
                                <i class="fas fa-paper-plane"></i>
                                Enviar depoimento
                            </button>
                        </form>
                    @else
                        <div class="mt-6 flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('login') }}" class="btn-primary text-white px-6 py-3 rounded-xl font-bold inline-flex items-center justify-center gap-2">
                                <i class="fas fa-right-to-bracket"></i>
                                Entrar para enviar
                            </a>
                            <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl font-bold border-2 inline-flex items-center justify-center gap-2"
                                style="border-color: var(--unn-azul-1); color: var(--unn-azul-1)">
                                <i class="fas fa-user-plus"></i>
                                Criar conta
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <x-faq-section context="premium" />

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
