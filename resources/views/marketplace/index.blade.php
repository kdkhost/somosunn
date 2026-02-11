@extends('layouts.app')

@section('title', 'Marketplace - UNN')

@section('content')
@php
    $courses = $courses ?? collect();
    $mentorships = $mentorships ?? collect();
    $events = $events ?? collect();
    $paymentsConfigured = (bool) ($paymentsConfigured ?? false);
    $canSellByUserId = $canSellByUserId ?? [];

    $user = auth()->user();
    $canSell = $user ? $user->canSellOnMarketplace() : false;
    $canBuy = $user ? $user->canAccessFeature('marketplace.buy') : false;

    $q = trim((string) request()->query('q', ''));

    $sellerCanSell = function (?int $userId) use ($canSellByUserId): bool {
        if (!$userId) {
            return false;
        }
        return (bool) ($canSellByUserId[$userId] ?? false);
    };

    $totalResults = $courses->count() + $mentorships->count() + $events->count();

    $chips = [
        ['label' => 'Cursos', 'icon' => 'fas fa-book-open', 'href' => '#marketplace-courses'],
        ['label' => 'Mentorias', 'icon' => 'fas fa-user-tie', 'href' => '#marketplace-mentorships'],
        ['label' => 'Eventos', 'icon' => 'fas fa-calendar-alt', 'href' => '#marketplace-events'],
    ];
@endphp

<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 pt-24 pb-16 px-4 md:px-12 lg:px-24">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 md:p-6">
            <div class="flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl btn-primary text-white flex items-center justify-center shadow-md">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <div class="text-xl md:text-2xl font-black text-slate-900 leading-tight">Marketplace UNN</div>
                        <div class="text-xs md:text-sm text-slate-500 leading-tight">Produtos digitais de membros e criadores</div>
                    </div>
                </div>

                <form method="GET" action="{{ route('marketplace.index') }}" class="flex-1 max-w-3xl">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar cursos, mentorias e eventos..."
                            class="w-full pl-11 pr-28 py-3 rounded-2xl border border-slate-200 bg-slate-50 focus:border-blue-500 focus:ring-blue-500">
                        <button type="submit"
                            class="absolute right-2 top-1/2 -translate-y-1/2 btn-primary text-white px-5 py-2 rounded-xl font-bold text-sm shadow-md">
                            Buscar
                        </button>
                    </div>
                    @if($q !== '')
                        <div class="mt-2 flex items-center justify-between gap-3 text-xs text-slate-500">
                            <span>{{ $totalResults }} resultado(s) para "{{ $q }}"</span>
                            <a href="{{ route('marketplace.index') }}" class="font-bold text-blue-700 hover:text-blue-800">Limpar busca</a>
                        </div>
                    @endif
                </form>

                <div class="flex items-center gap-2">
                    @if($canSell)
                        <a href="{{ route('admin.marketplace.index') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 font-bold text-slate-700 hover:bg-slate-50 transition">
                            <i class="fas fa-chart-line text-slate-400"></i> Painel
                        </a>
                        <a href="{{ route('admin.marketplace.sales') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 font-bold text-slate-700 hover:bg-slate-50 transition">
                            <i class="fas fa-receipt text-slate-400"></i> Vendas
                        </a>
                    @endif
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($chips as $chip)
                    <a href="{{ $chip['href'] }}"
                        class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition">
                        <i class="{{ $chip['icon'] }} text-slate-400"></i> {{ $chip['label'] }}
                    </a>
                @endforeach

                <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold text-white btn-primary">
                    <i class="fas fa-compass"></i> Explorar tudo
                </a>
            </div>

            @if(!$paymentsConfigured)
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                    <div class="font-black">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Pagamento indisponível
                    </div>
                    <div class="text-sm text-amber-800">
                        O MercadoPago ainda não foi configurado na plataforma. Compras pagas ficam indisponíveis até a configuração do gateway.
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-6 grid lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="relative p-6 md:p-8">
                        <div class="absolute inset-0 opacity-60 pointer-events-none"
                            style="background: radial-gradient(900px circle at 20% 10%, rgba(31, 94, 219, 0.20) 0%, transparent 60%), radial-gradient(700px circle at 90% 20%, rgba(23, 127, 214, 0.14) 0%, transparent 55%);">
                        </div>
                        <div class="relative">
                            <div class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-black text-white"
                                style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                                <i class="fas fa-bolt"></i> Ofertas e novidades
                            </div>
                            <h1 class="mt-5 text-3xl sm:text-4xl font-black text-slate-900">
                                Sua loja digital dentro da UNN
                            </h1>
                            <p class="mt-3 text-slate-600 text-base sm:text-lg max-w-2xl">
                                Cursos, mentorias e eventos publicados por membros habilitados. O gateway é multi-tenant e cada venda identifica vendedor e tipo.
                            </p>

                            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                                <a href="#marketplace-courses"
                                    class="btn-primary text-white px-6 py-3 rounded-2xl font-black inline-flex items-center justify-center gap-2 shadow-md">
                                    <i class="fas fa-book-open"></i> Ver cursos
                                </a>
                                <a href="#marketplace-mentorships"
                                    class="px-6 py-3 rounded-2xl font-black border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center gap-2">
                                    <i class="fas fa-user-tie"></i> Ver mentorias
                                </a>
                                <a href="#marketplace-events"
                                    class="px-6 py-3 rounded-2xl font-black border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center gap-2">
                                    <i class="fas fa-calendar-alt"></i> Ver eventos
                                </a>
                            </div>

                            <div class="mt-7 grid sm:grid-cols-3 gap-3">
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="text-sm font-black text-slate-900 mb-1">
                                        <i class="fas fa-shield-alt mr-2 text-slate-500"></i> Pagamento multi-tenant
                                    </div>
                                    <div class="text-sm text-slate-600">
                                        Gateway único na plataforma, com registro de vendedor e tipo de venda.
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="text-sm font-black text-slate-900 mb-1">
                                        <i class="fas fa-download mr-2 text-slate-500"></i> Conteúdo digital
                                    </div>
                                    <div class="text-sm text-slate-600">
                                        Venda focada em produtos e experiências digitais dentro da UNN.
                                    </div>
                                </div>
                                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                    <div class="text-sm font-black text-slate-900 mb-1">
                                        <i class="fas fa-user-check mr-2 text-slate-500"></i> Permissão de venda
                                    </div>
                                    <div class="text-sm text-slate-600">
                                        Apenas membros com permissão de instrutor/mentor/palestrante podem vender.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <div class="text-sm font-black text-slate-900">
                        <i class="fas fa-info-circle mr-2 text-slate-400"></i> Como funciona
                    </div>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li class="flex gap-2">
                            <i class="fas fa-check text-slate-400 mt-0.5"></i>
                            <span>Explore cursos, mentorias e eventos publicados.</span>
                        </li>
                        <li class="flex gap-2">
                            <i class="fas fa-check text-slate-400 mt-0.5"></i>
                            <span>Compras pagas exigem gateway configurado na plataforma.</span>
                        </li>
                        <li class="flex gap-2">
                            <i class="fas fa-check text-slate-400 mt-0.5"></i>
                            <span>Cada pedido registra vendedor e tipo de venda.</span>
                        </li>
                    </ul>
                </div>

                @if($canSell)
                    <div class="rounded-3xl border border-blue-200 bg-blue-50 p-6">
                        <div class="text-sm font-black text-blue-900">Quer vender no marketplace?</div>
                        <div class="text-sm text-blue-800 mt-1">
                            Acesse o painel para acompanhar vendas e gerenciar seu marketplace.
                        </div>
                        <div class="mt-4 flex flex-col sm:flex-row gap-2">
                            <a href="{{ route('admin.marketplace.index') }}"
                                class="btn-primary text-white px-5 py-3 rounded-2xl font-black inline-flex items-center justify-center gap-2 shadow-md">
                                <i class="fas fa-store"></i> Abrir painel
                            </a>
                            <a href="{{ route('admin.marketplace.payments') }}"
                                class="px-5 py-3 rounded-2xl font-black border border-blue-200 bg-white text-blue-800 hover:bg-blue-100 transition inline-flex items-center justify-center gap-2">
                                <i class="fas fa-credit-card"></i> Ver pagamentos
                            </a>
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                        <div class="text-sm font-black text-slate-900">Venda no marketplace</div>
                        <div class="text-sm text-slate-600 mt-1">
                            Para vender, seu usuário precisa estar habilitado como vendedor (instrutor/mentor/palestrante) no plano ou liberado individualmente.
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Cursos --}}
        <section id="marketplace-courses" class="mt-10">
            <div class="flex items-end justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                        <i class="fas fa-book-open mr-2 text-slate-400"></i> Cursos
                    </h2>
                    <p class="text-slate-600 mb-0">Aprenda com conteúdos publicados por membros habilitados.</p>
                </div>
                <a href="{{ route('courses.index') }}" class="text-sm font-black text-blue-700 hover:text-blue-800">Ver todos</a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @forelse($courses as $course)
                    @php
                        $showParam = $course->slug ?: $course->id;
                        $sellerId = (int) ($course->user_id ?? 0);
                        $price = (float) ($course->price ?? 0);
                        $thumb = trim((string) ($course->thumbnail ?? ''));
                        $thumbUrl = $thumb !== '' ? asset(ltrim($thumb, '/')) : '';
                        $hasAccess = $user && ($course instanceof \App\Models\Course) ? $user->hasCourseAccess($course) : false;
                        $buyEnabled = $canBuy && $paymentsConfigured && $price > 0 && $sellerCanSell($sellerId);
                        $sellerName = optional($course->creator)->name ?? 'Criador';
                        $badge = ($course->is_featured ?? false) ? 'DESTAQUE' : 'CURSO';
                        $shareCode = \App\Support\ShortLink::encodeProduct('course', (int) $course->id);
                        $shareUrl = $shareCode ? route('share.product', ['code' => $shareCode]) : '';
                    @endphp

                    <div class="group bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden">
                        <a href="{{ route('courses.show', $showParam) }}" class="block">
                            <div class="aspect-[16/9] bg-slate-100 relative">
                                @if($thumbUrl !== '')
                                    <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <i class="fas fa-image text-4xl"></i>
                                    </div>
                                @endif

                                <div class="absolute top-3 left-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black text-white shadow"
                                    style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                                    {{ $badge }}
                                </div>

                                @if($shareUrl !== '')
                                    <button type="button"
                                        class="absolute top-3 right-3 inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/90 border border-slate-200 text-slate-700 hover:bg-white shadow"
                                        onclick="event.preventDefault(); event.stopPropagation(); navigator.clipboard.writeText(@json($shareUrl)); toastr.success('Link copiado!');">
                                        <i class="fas fa-link text-sm"></i>
                                    </button>
                                @endif
                            </div>

                            <div class="p-4">
                                <div class="font-black text-slate-900 leading-snug truncate">
                                    {{ $course->title }}
                                </div>
                                <div class="text-xs text-slate-500 truncate mt-1">
                                    Produzido e comercializado por: {{ $sellerName }}
                                </div>

                                @if(!empty($course->short_description))
                                    <div class="text-sm text-slate-600 mt-2">
                                        {{ \Illuminate\Support\Str::limit(strip_tags((string) $course->short_description), 90) }}
                                    </div>
                                @endif

                                <div class="mt-3 flex items-end justify-between gap-3">
                                    <div>
                                        <div class="text-xs text-slate-500">Preço</div>
                                        <div class="text-lg font-black text-slate-900">
                                            {{ $price > 0 ? 'R$ '.number_format($price, 2, ',', '.') : 'Gratuito' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <div class="px-4 pb-4 flex gap-2">
                            <a href="{{ route('courses.show', $showParam) }}"
                                class="flex-1 inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50 transition">
                                Ver
                            </a>

                            @if($hasAccess)
                                <a href="{{ route('courses.show', $showParam) }}"
                                    class="flex-1 inline-flex items-center justify-center rounded-2xl btn-primary px-4 py-2 text-sm font-black text-white shadow-md">
                                    Acessar
                                </a>
                            @elseif($buyEnabled)
                                <a href="{{ route('checkout.show', $course->id) }}"
                                    class="flex-1 inline-flex items-center justify-center rounded-2xl btn-primary px-4 py-2 text-sm font-black text-white shadow-md">
                                    Comprar
                                </a>
                            @else
                                <span class="flex-1 inline-flex items-center justify-center rounded-2xl bg-slate-100 px-4 py-2 text-sm font-black text-slate-400 cursor-not-allowed">
                                    Indisponível
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-3xl border border-slate-100 p-6 text-slate-600">
                        Nenhum curso encontrado no momento.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Mentorias --}}
        <section id="marketplace-mentorships" class="mt-12">
            <div class="flex items-end justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                        <i class="fas fa-user-tie mr-2 text-slate-400"></i> Mentorias
                    </h2>
                    <p class="text-slate-600 mb-0">Atendimento e acompanhamento com mentores da comunidade.</p>
                </div>
                <a href="{{ route('mentorships.index') }}" class="text-sm font-black text-blue-700 hover:text-blue-800">Ver todos</a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @forelse($mentorships as $mentorship)
                    @php
                        $sellerId = (int) ($mentorship->mentor_id ?? 0);
                        $price = (float) ($mentorship->price ?? 0);
                        $buyEnabled = $canBuy && $paymentsConfigured && $price > 0 && $sellerCanSell($sellerId);
                        $mentorName = optional($mentorship->mentor)->name ?? 'Mentor';
                        $desc = \Illuminate\Support\Str::limit(strip_tags((string) ($mentorship->description ?? '')), 90);
                        $image = trim((string) ($mentorship->image ?? ''));
                        $imageUrl = $image !== '' ? asset(ltrim($image, '/')) : '';
                        $shareCode = \App\Support\ShortLink::encodeProduct('mentorship', (int) $mentorship->id);
                        $shareUrl = $shareCode ? route('share.product', ['code' => $shareCode]) : '';
                    @endphp

                    <div class="group bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden">
                        <a href="{{ route('mentorships.show', $mentorship) }}" class="block">
                            <div class="aspect-[16/9] bg-slate-100 relative">
                                @if($imageUrl !== '')
                                    <img src="{{ $imageUrl }}" alt="{{ $mentorship->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="absolute inset-0 opacity-80"
                                        style="background: radial-gradient(900px circle at 20% 10%, rgba(31, 94, 219, 0.18) 0%, transparent 60%), radial-gradient(700px circle at 90% 20%, rgba(23, 127, 214, 0.12) 0%, transparent 55%);">
                                    </div>
                                    <div class="relative w-full h-full flex items-center justify-center">
                                        <div class="w-16 h-16 rounded-2xl bg-white shadow flex items-center justify-center text-slate-400">
                                            <i class="fas fa-user-tie text-2xl"></i>
                                        </div>
                                    </div>
                                @endif

                                <div class="absolute top-3 left-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black text-white shadow"
                                    style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                                    MENTORIA
                                </div>

                                @if($shareUrl !== '')
                                    <button type="button"
                                        class="absolute top-3 right-3 inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/90 border border-slate-200 text-slate-700 hover:bg-white shadow"
                                        onclick="event.preventDefault(); event.stopPropagation(); navigator.clipboard.writeText(@json($shareUrl)); toastr.success('Link copiado!');">
                                        <i class="fas fa-link text-sm"></i>
                                    </button>
                                @endif
                            </div>

                            <div class="p-4">
                                <div class="font-black text-slate-900 leading-snug truncate">
                                    {{ $mentorship->title }}
                                </div>
                                <div class="text-xs text-slate-500 truncate mt-1">
                                    Produzido e comercializado por: {{ $mentorName }}
                                </div>

                                @if($desc !== '')
                                    <div class="text-sm text-slate-600 mt-2">
                                        {{ $desc }}
                                    </div>
                                @endif

                                <div class="mt-3 flex items-end justify-between gap-3">
                                    <div>
                                        <div class="text-xs text-slate-500">Preço</div>
                                        <div class="text-lg font-black text-slate-900">
                                            {{ $price > 0 ? 'R$ '.number_format($price, 2, ',', '.') : 'Gratuito' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <div class="px-4 pb-4 flex gap-2">
                            <a href="{{ route('mentorships.show', $mentorship) }}"
                                class="flex-1 inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50 transition">
                                Ver
                            </a>

                            @if($buyEnabled)
                                <a href="{{ route('mentorships.checkout.show', $mentorship) }}"
                                    class="flex-1 inline-flex items-center justify-center rounded-2xl btn-primary px-4 py-2 text-sm font-black text-white shadow-md">
                                    Comprar
                                </a>
                            @else
                                <span class="flex-1 inline-flex items-center justify-center rounded-2xl bg-slate-100 px-4 py-2 text-sm font-black text-slate-400 cursor-not-allowed">
                                    Indisponível
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-3xl border border-slate-100 p-6 text-slate-600">
                        Nenhuma mentoria encontrada no momento.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Eventos --}}
        <section id="marketplace-events" class="mt-12">
            <div class="flex items-end justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                        <i class="fas fa-calendar-alt mr-2 text-slate-400"></i> Eventos
                    </h2>
                    <p class="text-slate-600 mb-0">Experiências, palestras e encontros digitais e presenciais.</p>
                </div>
                <a href="{{ route('events.index') }}" class="text-sm font-black text-blue-700 hover:text-blue-800">Ver todos</a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @forelse($events as $event)
                    @php
                        $sellerId = (int) ($event->user_id ?? 0);
                        $price = (float) ($event->current_price ?? $event->price ?? 0);
                        $dateLabel = $event->start_at ? (is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at)->format('d/m/Y') : null;
                        $image = trim((string) ($event->image ?? ''));
                        $imageUrl = $image !== '' ? asset(ltrim($image, '/')) : '';
                        $buyEnabled = $price <= 0 ? true : ($canBuy && $paymentsConfigured && $sellerCanSell($sellerId));
                        $sellerName = optional($event->user)->name ?? 'Organizador';
                        $shareCode = \App\Support\ShortLink::encodeProduct('event', (int) $event->id);
                        $shareUrl = $shareCode ? route('share.product', ['code' => $shareCode]) : '';
                    @endphp

                    <div class="group bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden">
                        <a href="{{ route('events.show', $event) }}" class="block">
                            <div class="aspect-[16/9] bg-slate-100 relative">
                                @if($imageUrl !== '')
                                    <img src="{{ $imageUrl }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <i class="fas fa-image text-4xl"></i>
                                    </div>
                                @endif

                                <div class="absolute top-3 left-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black text-white shadow"
                                    style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                                    EVENTO
                                </div>

                                @if($shareUrl !== '')
                                    <button type="button"
                                        class="absolute top-3 right-3 inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/90 border border-slate-200 text-slate-700 hover:bg-white shadow"
                                        onclick="event.preventDefault(); event.stopPropagation(); navigator.clipboard.writeText(@json($shareUrl)); toastr.success('Link copiado!');">
                                        <i class="fas fa-link text-sm"></i>
                                    </button>
                                @endif
                            </div>

                            <div class="p-4">
                                <div class="font-black text-slate-900 leading-snug truncate">
                                    {{ $event->title }}
                                </div>
                                <div class="text-xs text-slate-500 truncate mt-1">
                                    {{ $dateLabel ? ('Data: ' . $dateLabel) : ($event->location ?? 'Evento') }}
                                </div>
                                <div class="text-xs text-slate-500 truncate mt-1">
                                    Produzido e comercializado por: {{ $sellerName }}
                                </div>

                                <div class="mt-3 flex items-end justify-between gap-3">
                                    <div>
                                        <div class="text-xs text-slate-500">Preço</div>
                                        <div class="text-lg font-black text-slate-900">
                                            {{ $price > 0 ? 'R$ '.number_format($price, 2, ',', '.') : 'Gratuito' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <div class="px-4 pb-4 flex gap-2">
                            <a href="{{ route('events.show', $event) }}"
                                class="flex-1 inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50 transition">
                                Ver
                            </a>

                            @if($buyEnabled)
                                <a href="{{ route('events.checkout', $event) }}"
                                    class="flex-1 inline-flex items-center justify-center rounded-2xl btn-primary px-4 py-2 text-sm font-black text-white shadow-md">
                                    {{ $price > 0 ? 'Comprar' : 'Reservar' }}
                                </a>
                            @else
                                <span class="flex-1 inline-flex items-center justify-center rounded-2xl bg-slate-100 px-4 py-2 text-sm font-black text-slate-400 cursor-not-allowed">
                                    Indisponível
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-3xl border border-slate-100 p-6 text-slate-600">
                        Nenhum evento encontrado no momento.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
