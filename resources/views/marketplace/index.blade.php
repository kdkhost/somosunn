@extends('layouts.app')

@section('title', 'Marketplace - UNN')

@section('content')
    @php
        $courses = $courses ?? collect();
        $mentorships = $mentorships ?? collect();
        $events = $events ?? collect();
        $gatewayEnabledUserIds = $gatewayEnabledUserIds ?? [];
        $canSellByUserId = $canSellByUserId ?? [];

        $user = auth()->user();
        $canSell = $user ? $user->canAccessFeature('marketplace.sell') : false;
        $canBuy = $user ? $user->canAccessFeature('marketplace.buy') : false;

        $hasGateway = function (int $userId) use ($gatewayEnabledUserIds): bool {
            return in_array($userId, $gatewayEnabledUserIds, true);
        };

        $sellerCanSell = function (?int $userId) use ($canSellByUserId): bool {
            if (!$userId) {
                return false;
            }
            return (bool) ($canSellByUserId[$userId] ?? false);
        };
    @endphp

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
        <section class="pt-24 md:pt-28 pb-10 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white rounded-[32px] shadow-xl border border-slate-100 p-10 md:p-12 overflow-hidden relative">
                    <div class="absolute inset-0 pointer-events-none opacity-60"
                        style="background: radial-gradient(900px circle at 20% 10%, rgba(31, 94, 219, 0.18) 0%, transparent 60%), radial-gradient(700px circle at 90% 20%, rgba(23, 127, 214, 0.12) 0%, transparent 55%);">
                    </div>
                    <div class="relative">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold text-white"
                            style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3));">
                            <i class="fas fa-store"></i> Marketplace UNN
                        </span>
                        <h1 class="mt-5 text-3xl sm:text-4xl md:text-5xl font-black text-slate-900">
                            Produtos digitais de membros e criadores
                        </h1>
                        <p class="mt-3 text-slate-600 text-base sm:text-lg max-w-3xl">
                            Cursos, mentorias e experiências digitais dentro da plataforma, com pagamento direto no meio de cobrança do criador.
                        </p>

                        <div class="mt-8 flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('courses.index') }}"
                                class="btn-primary text-white px-7 py-3 rounded-xl font-bold inline-flex items-center justify-center gap-2">
                                <i class="fas fa-book-open"></i> Ver cursos
                            </a>
                            <a href="{{ route('mentorships.index') }}"
                                class="px-7 py-3 rounded-xl font-bold border-2 border-slate-200 text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center gap-2">
                                <i class="fas fa-user-tie"></i> Ver mentorias
                            </a>
                            <a href="{{ route('events.index') }}"
                                class="px-7 py-3 rounded-xl font-bold border-2 border-slate-200 text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center gap-2">
                                <i class="fas fa-calendar-alt"></i> Ver eventos
                            </a>
                        </div>

                        <div class="mt-8 grid lg:grid-cols-3 gap-4">
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                                <div class="text-sm font-black text-slate-900 mb-1"><i class="fas fa-shield-alt mr-2 text-slate-500"></i>Pagamento do criador</div>
                                <p class="text-sm text-slate-600 mb-0">Cada vendedor usa a própria conta de cobrança configurada no painel.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                                <div class="text-sm font-black text-slate-900 mb-1"><i class="fas fa-download mr-2 text-slate-500"></i>Conteúdo digital</div>
                                <p class="text-sm text-slate-600 mb-0">A venda é focada em produtos digitais dentro da plataforma.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-5">
                                <div class="text-sm font-black text-slate-900 mb-1"><i class="fas fa-user-check mr-2 text-slate-500"></i>Permissão de venda</div>
                                <p class="text-sm text-slate-600 mb-0">Somente membros habilitados podem vender no marketplace.</p>
                            </div>
                        </div>

                        <div class="mt-8">
                            @if($canSell)
                                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                        <div>
                                            <div class="text-sm font-black text-blue-900">Quer vender no marketplace?</div>
                                            <div class="text-sm text-blue-800">Configure seu meio de cobrança e publique seus produtos digitais.</div>
                                        </div>
                                        <div class="flex flex-col sm:flex-row gap-2">
                                            <a href="{{ route('settings.payment') }}"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-700 hover:bg-blue-800 text-white px-5 py-2.5 font-bold text-sm transition">
                                                <i class="fas fa-credit-card"></i> Configurar pagamentos
                                            </a>
                                            <a href="{{ route('admin.dashboard') }}"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-blue-200 bg-white text-blue-800 px-5 py-2.5 font-bold text-sm hover:bg-blue-50 transition">
                                                <i class="fas fa-chart-line"></i> Ir para o painel
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                    <div class="text-sm font-black text-slate-900">Venda no marketplace</div>
                                    <div class="text-sm text-slate-600">
                                        Para vender, seu usuário precisa estar com a permissão <strong>marketplace.sell</strong> habilitada no seu plano ou liberada individualmente.
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="pb-16 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-end justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900">Destaques</h2>
                        <p class="text-slate-600 mb-0">Alguns produtos e serviços disponíveis agora.</p>
                    </div>
                    <a href="{{ route('marketplace.sales') }}" class="text-sm font-bold text-slate-600 hover:text-blue-700 transition">
                        <i class="fas fa-receipt mr-1"></i> Minhas vendas
                    </a>
                </div>

                <div class="grid lg:grid-cols-3 gap-6">
                    <!-- Cursos -->
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-black text-slate-900"><i class="fas fa-book-open mr-2 text-slate-500"></i>Cursos</h3>
                                <a href="{{ route('courses.index') }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">Ver todos</a>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            @forelse($courses as $course)
                                @php
                                    $sellerId = (int) ($course->user_id ?? 0);
                                    $price = (float) ($course->price ?? 0);
                                    $hasAccess = $user && ($course instanceof \App\Models\Course) ? $user->hasCourseAccess($course) : false;
                                    $buyEnabled = $price > 0 && $sellerCanSell($sellerId) && $hasGateway($sellerId);
                                    $showParam = $course->slug ?: $course->id;
                                @endphp
                                <div class="rounded-2xl border border-slate-100 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="font-black text-slate-900 truncate">{{ $course->title }}</div>
                                            <div class="text-xs text-slate-500 truncate">{{ optional($course->creator)->name ?? 'Criador' }}</div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="text-xs text-slate-500">Preço</div>
                                            <div class="font-black text-slate-900">{{ $price > 0 ? 'R$ '.number_format($price, 2, ',', '.') : 'Gratuito' }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ route('courses.show', $showParam) }}"
                                            class="flex-1 inline-flex items-center justify-center rounded-xl border-2 border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                                            Ver
                                        </a>
                                        @if($hasAccess)
                                            <a href="{{ route('courses.show', $showParam) }}"
                                                class="flex-1 inline-flex items-center justify-center rounded-xl btn-primary px-4 py-2 text-sm font-bold text-white">
                                                Acessar
                                            </a>
                                        @elseif($buyEnabled)
                                            <a href="{{ route('checkout.show', $course->id) }}"
                                                class="flex-1 inline-flex items-center justify-center rounded-xl btn-primary px-4 py-2 text-sm font-bold text-white">
                                                Comprar
                                            </a>
                                        @else
                                            <span class="flex-1 inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400 cursor-not-allowed">
                                                Indisponível
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-slate-500">Nenhum curso disponível no momento.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Mentorias -->
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-black text-slate-900"><i class="fas fa-user-tie mr-2 text-slate-500"></i>Mentorias</h3>
                                <a href="{{ route('mentorships.index') }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">Ver todas</a>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            @forelse($mentorships as $mentorship)
                                @php
                                    $sellerId = (int) ($mentorship->mentor_id ?? 0);
                                    $price = (float) ($mentorship->price ?? 0);
                                    $buyEnabled = $price > 0 && $sellerCanSell($sellerId) && $hasGateway($sellerId);
                                @endphp
                                <div class="rounded-2xl border border-slate-100 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="font-black text-slate-900 truncate">{{ $mentorship->title }}</div>
                                            <div class="text-xs text-slate-500 truncate">{{ optional($mentorship->mentor)->name ?? 'Mentor' }}</div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="text-xs text-slate-500">Preço</div>
                                            <div class="font-black text-slate-900">{{ $price > 0 ? 'R$ '.number_format($price, 2, ',', '.') : 'Gratuito' }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ route('mentorships.show', $mentorship) }}"
                                            class="flex-1 inline-flex items-center justify-center rounded-xl border-2 border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                                            Ver
                                        </a>
                                        @if($buyEnabled)
                                            <a href="{{ route('mentorships.checkout.show', $mentorship) }}"
                                                class="flex-1 inline-flex items-center justify-center rounded-xl btn-primary px-4 py-2 text-sm font-bold text-white">
                                                Comprar
                                            </a>
                                        @else
                                            <span class="flex-1 inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400 cursor-not-allowed">
                                                Indisponível
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-slate-500">Nenhuma mentoria disponível no momento.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Eventos -->
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-100">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-black text-slate-900"><i class="fas fa-calendar-alt mr-2 text-slate-500"></i>Eventos</h3>
                                <a href="{{ route('events.index') }}" class="text-sm font-bold text-blue-700 hover:text-blue-800">Ver todos</a>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            @forelse($events as $event)
                                @php
                                    $price = (float) ($event->current_price ?? $event->price ?? 0);
                                    $dateLabel = $event->start_at ? (is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at)->format('d/m/Y') : null;
                                    $sellerId = (int) ($event->user_id ?? 0);
                                    $buyEnabled = $price <= 0 ? true : ($sellerCanSell($sellerId) && $hasGateway($sellerId));
                                @endphp
                                <div class="rounded-2xl border border-slate-100 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="font-black text-slate-900 truncate">{{ $event->title }}</div>
                                            <div class="text-xs text-slate-500 truncate">
                                                {{ $dateLabel ? ('Data: ' . $dateLabel) : ($event->location ?? 'Evento') }}
                                            </div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="text-xs text-slate-500">Preço</div>
                                            <div class="font-black text-slate-900">{{ $price > 0 ? 'R$ '.number_format($price, 2, ',', '.') : 'Gratuito' }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ route('events.show', $event) }}"
                                            class="flex-1 inline-flex items-center justify-center rounded-xl border-2 border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                                            Ver
                                        </a>
                                        @if($buyEnabled)
                                            <a href="{{ route('events.checkout', $event) }}"
                                                class="flex-1 inline-flex items-center justify-center rounded-xl btn-primary px-4 py-2 text-sm font-bold text-white">
                                                Inscrever-se
                                            </a>
                                        @else
                                            <span class="flex-1 inline-flex items-center justify-center rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-400 cursor-not-allowed">
                                                Indisponível
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-slate-500">Nenhum evento disponível no momento.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
