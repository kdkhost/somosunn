@extends('layouts.app')

@section('title', ($pageData['seo_title'] ?? null) ?: 'Próximos Eventos - UNN')

@push('styles')
    <style>
        .unn-events-hero {
            background:
                radial-gradient(1200px circle at 15% 20%, rgba(255, 255, 255, 0.18) 0%, transparent 55%),
                radial-gradient(900px circle at 85% 0%, rgba(255, 255, 255, 0.12) 0%, transparent 50%),
                linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
        }

        .unn-events-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(rgba(255, 255, 255, 0.35) 1px, transparent 1px),
                radial-gradient(rgba(255, 255, 255, 0.18) 1px, transparent 1px);
            background-size: 36px 36px, 64px 64px;
            background-position: 0 0, 18px 18px;
            opacity: 0.28;
            pointer-events: none;
        }

        .unn-events-cta {
            background: linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
        }

        .events-table-head,
        .events-table-row {
            display: grid;
            gap: 1rem;
        }

        .events-table-head > div,
        .events-table-row > div {
            min-width: 0;
        }

        @media (min-width: 1400px) {
            .events-table-head,
            .events-table-row {
                grid-template-columns:
                    0.78fr
                    1.65fr
                    1.85fr
                    0.62fr
                    0.74fr
                    0.96fr;
                align-items: center;
            }
        }

        @media (min-width: 1200px) and (max-width: 1399.98px) {
            .events-table-head,
            .events-table-row {
                grid-template-columns:
                    0.68fr
                    1.35fr
                    1.45fr
                    0.56fr
                    0.64fr
                    0.72fr;
                gap: 0.65rem;
                align-items: center;
            }

            .events-table-head {
                font-size: 0.68rem;
            }

            .events-col-action .events-action-btn {
                max-width: 138px;
                padding-left: 0.7rem;
                padding-right: 0.7rem;
                font-size: 0.93rem;
            }

            .events-col-event .font-black,
            .events-col-location .text-slate-800 {
                font-size: 0.94rem;
            }
        }

        @media (min-width: 1200px) {
            .events-col-action {
                display: flex;
                align-items: center;
                justify-content: flex-end;
            }

            .events-col-action .events-action-btn {
                width: 100%;
                max-width: 170px;
                min-width: 0;
            }

            .events-col-location,
            .events-col-event {
                overflow: hidden;
            }

            .events-col-location .text-slate-800 {
                line-height: 1.2;
                word-break: break-word;
            }
        }

        @media (max-width: 1199.98px) {
            .events-table-head {
                display: none !important;
            }

            .events-table-row {
                grid-template-columns: 1fr;
            }

            .events-col-action .events-action-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 767.98px) {
            .events-col-action .events-action-btn {
                padding-left: 0.95rem;
                padding-right: 0.95rem;
                font-size: 0.95rem;
            }
        }

        .events-total-badge {
            white-space: nowrap;
        }

        @media (max-width: 640px) {
            .events-headbar {
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .events-total-badge {
                margin-left: auto;
                padding: 0.35rem 0.65rem;
                font-size: 0.74rem;
                gap: 0.35rem;
            }
        }

        @media (max-width: 1024px) {
            .events-top-cta {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            .events-main-title {
                font-size: clamp(1.85rem, 4.4vw, 2.5rem);
                line-height: 1.2;
            }

            .events-subtitle {
                font-size: clamp(1rem, 2.4vw, 1.2rem);
            }
        }
    </style>
@endpush

@section('content')
    @php
        $pageData = $pageData ?? [];
        $events = $events ?? collect();
        $featuredEvent = $featuredEvent ?? ($events->first() ?: null);
        $pastEvents = $pastEvents ?? collect();
        $isDemo = (bool) ($isDemo ?? false);

        $featuredImageSetting =
            \App\Models\Setting::get('events_featured_image')
            ?: \App\Models\Setting::get('hero_image')
            ?: \App\Models\Setting::get('pwa_banner');

        $featuredImage = $featuredImageSetting
            ? asset(ltrim($featuredImageSetting, '/'))
            : 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1400';

        $featuredEventImage = $featuredEvent && $featuredEvent->image
            ? asset('storage/' . $featuredEvent->image)
            : $featuredImage;
    @endphp

    <div class="min-h-screen">
        <section class="unn-events-hero relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/20 pointer-events-none"></div>

            <div class="px-4 md:px-12 lg:px-24 pt-10 md:pt-14 pb-14 md:pb-20 relative">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center">
                        <span class="inline-flex items-center justify-center px-6 py-2 rounded-full text-sm font-bold text-white border border-white/20 bg-white/15 backdrop-blur">
                            {{ ($pageData['hero_badge'] ?? null) ?: 'Em destaque' }}
                        </span>
                        <h1 class="mt-6 text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-white">
                            {{ ($pageData['hero_title'] ?? null) ?: 'Próximo Evento UNN' }}
                        </h1>
                        <p class="mt-3 text-white/80 text-base sm:text-lg">
                            {!! ($pageData['hero_subtitle'] ?? null) ?: 'Não perca a oportunidade de expandir sua rede' !!}
                        </p>
                    </div>

                    @if($featuredEvent)
                        @php
                            $startDate = is_string($featuredEvent->start_at) ? \Carbon\Carbon::parse($featuredEvent->start_at) : $featuredEvent->start_at;
                            $endDate = $featuredEvent->end_at ? (is_string($featuredEvent->end_at) ? \Carbon\Carbon::parse($featuredEvent->end_at) : $featuredEvent->end_at) : null;

                            $prettyDate = $startDate ? $startDate->translatedFormat('d \\d\\e F, Y') : '';
                            $prettyWeekday = $startDate ? ucfirst($startDate->translatedFormat('l')) : '';

                            $startTime = $startDate ? $startDate->format('H\\hi') : '';
                            $endTime = $endDate ? $endDate->format('H\\hi') : null;

                            $durationMinutes = ($startDate && $endDate) ? $startDate->diffInMinutes($endDate) : null;
                            $durationText = null;
                            if ($durationMinutes) {
                                $hours = intdiv($durationMinutes, 60);
                                $minutes = $durationMinutes % 60;
                                $durationText = $minutes === 0
                                    ? ($hours . ' ' . ($hours === 1 ? 'hora' : 'horas') . ' de duração')
                                    : ($hours . 'h' . str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) . ' de duração');
                            }

                            $locationName = $featuredEvent->location ?: ($featuredEvent->address ? 'Local do evento' : 'Local a confirmar');
                            $addressLine = $featuredEvent->address ?: null;
                        @endphp

                        <div class="mt-10 md:mt-14">
                            <div class="rounded-[32px] overflow-hidden border border-white/20 bg-white/10 backdrop-blur shadow-[0_40px_120px_-60px_rgba(0,0,0,0.65)]">
                                <div class="grid lg:grid-cols-2">
                                    <div class="relative min-h-[260px] lg:min-h-[460px]">
                                        <img src="{{ $featuredEventImage }}" alt="Evento UNN" class="absolute inset-0 w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-black/0"></div>

                                        @if($featuredEvent->capacity)
                                            <span class="absolute top-4 sm:top-6 left-1/2 -translate-x-1/2 px-4 py-2 sm:px-6 sm:py-3 text-sm sm:text-base rounded-full font-bold text-white shadow-lg"
                                                style="background: var(--unn-azul-1)">
                                                Vagas limitadas
                                            </span>
                                        @endif
                                    </div>

                                    <div class="bg-white p-7 sm:p-8 md:p-10">
                                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 leading-tight">
                                            {{ $featuredEvent->title }}
                                        </h2>
                                        <p class="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed">
                                            {{ $featuredEvent->description ?: 'Participe de um encontro exclusivo para empreendedores que buscam crescimento através de conexões estratégicas.' }}
                                        </p>

                                        <div class="mt-8 space-y-5">
                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-calendar-alt text-blue-600"></i>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900">{{ $prettyDate }}</div>
                                                    <div class="text-sm text-slate-500">{{ $prettyWeekday }}</div>
                                                </div>
                                            </div>

                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-clock text-blue-600"></i>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900">
                                                        @if($featuredEvent->all_day)
                                                            Dia todo
                                                        @elseif($endTime)
                                                            {{ $startTime }} às {{ $endTime }}
                                                        @else
                                                            {{ $startTime }}
                                                        @endif
                                                    </div>
                                                    <div class="text-sm text-slate-500">
                                                        {{ $durationText ?: 'Horário sujeito a confirmação' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-start gap-4">
                                                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900">{{ $locationName }}</div>
                                                    @if($addressLine)
                                                        <div class="text-sm text-slate-500">{{ $addressLine }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-10 flex flex-col sm:flex-row gap-4">
                                            <a href="{{ $isDemo ? '#' : route('events.checkout', $featuredEvent) }}"
                                                class="btn-primary text-white px-8 py-4 rounded-xl font-bold inline-flex items-center justify-center gap-3 shadow-lg hover:shadow-xl transition {{ $isDemo ? 'pointer-events-none opacity-60' : '' }}">
                                                Inscreva-se agora <i class="fas fa-arrow-right"></i>
                                            </a>
                                            <a href="{{ $isDemo ? '#' : route('events.show', $featuredEvent) }}"
                                                class="px-8 py-4 rounded-xl font-bold border-2 border-slate-200 text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center {{ $isDemo ? 'pointer-events-none opacity-60' : '' }}">
                                                Saiba mais
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($isDemo)
                                <div class="mt-6 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-2xl p-4">
                                    <div class="flex gap-3">
                                        <div class="mt-0.5"><i class="fas fa-exclamation-triangle"></i></div>
                                        <div>
                                            <p class="font-bold">Dados de demonstração</p>
                                            <p class="text-sm">Estes eventos são exemplos. Configure eventos reais no painel administrativo.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="mt-10 md:mt-14 max-w-3xl mx-auto">
                            <div class="bg-white rounded-[32px] shadow-2xl p-10 text-center">
                                <div class="text-slate-400 mb-4"><i class="fas fa-calendar-times text-5xl"></i></div>
                                <h2 class="text-2xl sm:text-3xl font-black text-slate-900">Nenhum evento programado</h2>
                                <p class="mt-2 text-slate-600">Fique atento, novas datas serão liberadas em breve.</p>
                                <p class="mt-4 text-sm text-slate-500">
                                    Visualização é pública. A confirmação de vaga pode exigir pagamento conforme o evento.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="bg-gradient-to-br from-slate-50 to-blue-50 py-12 md:py-16 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold"
                            style="background: var(--unn-azul-1); color: white">
                            <i class="fas fa-calendar-check"></i> Agenda
                        </span>
                        <h2 class="events-main-title text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 mt-4">Agenda de Eventos</h2>
                        <p class="events-subtitle text-gray-600 mt-2 max-w-2xl">
                            Visualização é pública. A confirmação de vaga pode exigir pagamento conforme o evento.
                        </p>
                    </div>

                    <a href="{{ route('premium') }}"
                        class="events-top-cta inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full font-bold bg-white border hover:bg-slate-50 transition whitespace-nowrap"
                        style="border-color: var(--unn-azul-1); color: var(--unn-azul-1)">
                        <i class="fas fa-crown"></i> Ver planos Premium
                    </a>
                </div>

                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
                    <div class="events-headbar px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
                        <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center btn-primary shadow-sm">
                                <i class="fas fa-calendar-check text-white"></i>
                            </span>
                            Próximos eventos
                        </h3>
                        <span class="events-total-badge inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-white border border-slate-200 text-slate-600 shadow-sm">
                            <i class="fas fa-layer-group text-slate-400"></i>
                            {{ $events->count() }} {{ $events->count() === 1 ? 'evento' : 'eventos' }}
                        </span>
                    </div>

                    @if($events->isEmpty())
                        <div class="p-10 text-center">
                            <div class="text-slate-400 mb-4"><i class="fas fa-calendar-times text-4xl"></i></div>
                            <h4 class="text-xl font-black text-slate-900 mb-2">Nenhum próximo evento encontrado</h4>
                            <p class="text-slate-600">Confira os últimos eventos abaixo e fique atento às próximas datas.</p>
                        </div>
                    @else
                        <div class="bg-slate-50/40 p-4 sm:p-6">
                            <div class="events-table-head px-4 pb-3 text-xs font-black uppercase tracking-wider text-slate-500">
                                <div>Data</div>
                                <div>Evento</div>
                                <div>Localização</div>
                                <div>Vagas</div>
                                <div>Valor</div>
                                <div class="text-right">Ação</div>
                            </div>

                            <div class="space-y-3">
                                @foreach($events as $event)
                                    @php
                                        $startDate = is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at;
                                        $isEventClosed = method_exists($event, 'isClosedForPublic') && $event->isClosedForPublic();
                                        $locationLine = $event->location ?: ($event->address ? 'Local do evento' : 'A confirmar');

                                        $relativeLabel = $startDate && method_exists($startDate, 'isToday') && $startDate->isToday()
                                            ? 'Hoje'
                                            : ($startDate && method_exists($startDate, 'isTomorrow') && $startDate->isTomorrow()
                                                ? 'Amanhã'
                                                : ($startDate ? ucfirst($startDate->translatedFormat('D')) : ''));

                                        $imageValue = trim((string) ($event->image ?? ''));
                                        $eventImageUrl = '';
                                        if ($imageValue !== '') {
                                            if (\Illuminate\Support\Str::startsWith($imageValue, ['http://', 'https://'])) {
                                                $eventImageUrl = $imageValue;
                                            } else {
                                                $normalized = ltrim(str_replace('\\', '/', $imageValue), '/');
                                                $eventImageUrl = asset($normalized);
                                            }
                                        }

                                        $hasPrice = ((float) ($event->current_price ?: $event->price) > 0);
                                    @endphp

                                    <div class="group rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-[1px] transition will-change-transform">
                                        <div class="events-table-row p-5 md:p-6">
                                            <div class="events-col-date min-w-0">
                                                <div class="flex md:block items-center justify-between gap-3">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-14 text-center rounded-2xl p-2 border border-slate-200 bg-gradient-to-br from-white to-slate-50 shadow-inner">
                                                            <span class="block font-black text-lg leading-none text-slate-900">{{ $startDate ? $startDate->format('d') : '--' }}</span>
                                                            <span class="block uppercase text-xs font-black text-slate-500">{{ $startDate ? $startDate->translatedFormat('M') : '' }}</span>
                                                        </div>
                                                        <div class="md:hidden">
                                                            <div class="text-xs font-bold text-slate-700">
                                                                {{ $startDate ? ucfirst($startDate->translatedFormat('l')) : '' }}
                                                            </div>
                                                            <div class="text-xs text-slate-500 mt-0.5">
                                                                <i class="far fa-clock mr-1"></i>{{ $startDate ? $startDate->format('H:i') : '' }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if($relativeLabel)
                                                        <span class="md:mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-slate-100 text-slate-700 border border-slate-200">
                                                            {{ $relativeLabel }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="hidden md:block text-xs text-slate-500 mt-3">
                                                    <div class="font-bold text-slate-700">{{ $startDate ? ucfirst($startDate->translatedFormat('l')) : '' }}</div>
                                                    <div class="mt-1"><i class="far fa-clock mr-1"></i>{{ $startDate ? $startDate->format('H:i') : '' }}</div>
                                                </div>
                                            </div>

                                            <div class="events-col-event min-w-0">
                                                <div class="flex items-start gap-4">
                                                    <div class="relative w-16 h-16 rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 flex-shrink-0">
                                                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-indigo-500/10"></div>
                                                        <div class="absolute inset-0 flex items-center justify-center text-slate-400">
                                                            <i class="fas fa-calendar-alt"></i>
                                                        </div>
                                                        @if($eventImageUrl !== '')
                                                            <img src="{{ $eventImageUrl }}" alt="Imagem do evento" class="absolute inset-0 w-full h-full object-cover z-10" loading="lazy" onerror="this.remove();">
                                                            <div class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/0 to-black/0 z-20"></div>
                                                        @endif
                                                    </div>

                                                    <div class="min-w-0">
                                                        <div class="font-black text-slate-900 leading-tight truncate">
                                                            {{ $event->title }}
                                                        </div>

                                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                                            @if($event->speaker)
                                                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                                    <i class="fas fa-user-tie text-slate-400"></i>{{ $event->speaker }}
                                                                </span>
                                                            @endif

                                                            @if($hasPrice && $event->current_batch_label)
                                                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black text-white shadow-sm"
                                                                    style="background: var(--unn-azul-1)">
                                                                    <i class="fas fa-tag"></i>{{ $event->current_batch_label }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="events-col-location min-w-0">
                                                <div class="text-slate-800 font-bold">
                                                    <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>{{ $locationLine }}
                                                </div>
                                                @if($event->address)
                                                    <div class="text-xs text-slate-500 mt-1 leading-snug">
                                                        {{ $event->address }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="events-col-capacity min-w-0">
                                                @if($event->capacity)
                                                    <div class="text-sm font-black text-slate-900">{{ $event->capacity }}</div>
                                                    <div class="text-xs text-slate-500 mt-1">vagas</div>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-green-100 text-green-700">Ilimitado</span>
                                                @endif
                                            </div>

                                            <div class="events-col-price min-w-0">
                                                @if($event->current_price > 0 || $event->price > 0)
                                                    <div class="font-black text-slate-900">R$ {{ number_format($event->current_price ?: $event->price, 2, ',', '.') }}</div>
                                                    <div class="text-xs text-slate-500 mt-1">por pessoa</div>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-green-100 text-green-700">GRÁTIS</span>
                                                @endif
                                            </div>

                                            <div class="events-col-action md:text-right">
                                                @if($isDemo)
                                                    <a href="#"
                                                        class="events-action-btn inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full text-sm font-black text-white btn-primary shadow-lg hover:shadow-xl transition whitespace-nowrap w-full md:w-auto pointer-events-none opacity-60">
                                                        <i class="fas fa-ticket-alt"></i>
                                                        Detalhes
                                                    </a>
                                                @elseif($isEventClosed)
                                                    <span
                                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full text-sm font-black bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed whitespace-nowrap w-full md:w-auto">
                                                        <i class="fas fa-ban"></i>
                                                        Encerrado
                                                    </span>
                                                @else
                                                    <a href="{{ route('events.show', $event->id) }}"
                                                        class="events-action-btn inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full text-sm font-black text-white btn-primary shadow-lg hover:shadow-xl transition whitespace-nowrap w-full md:w-auto">
                                                        <i class="fas fa-ticket-alt"></i>
                                                        Detalhes
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="py-12 md:py-16 px-4 md:px-12 lg:px-24 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-10">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold"
                        style="background: var(--unn-azul-1); color: white">
                        <i class="fas fa-info-circle"></i> Informações
                    </span>
                    <h2 class="mt-4 text-3xl sm:text-4xl font-black text-slate-900">Como funcionam os eventos</h2>
                    <p class="mt-2 text-slate-600 max-w-3xl mx-auto">
                        A agenda é pública. Para garantir vaga, o sistema pode exigir pagamento da taxa/consumação conforme o evento.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 shadow-sm">
                        <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center">
                            <i class="fas fa-globe text-white"></i>
                        </div>
                        <h3 class="mt-4 text-lg font-black text-slate-900">Visualização pública</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                            Qualquer pessoa pode ver os próximos eventos e acessar os detalhes — sem precisar de login.
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 shadow-sm">
                        <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-white"></i>
                        </div>
                        <h3 class="mt-4 text-lg font-black text-slate-900">Reserva de vaga</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                            Ao clicar em <span class="font-bold">Inscreva-se</span>, você confirma sua participação (e a quantidade de vagas, quando aplicável).
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 shadow-sm">
                        <div class="w-12 h-12 btn-primary rounded-xl flex items-center justify-center">
                            <i class="fas fa-credit-card text-white"></i>
                        </div>
                        <h3 class="mt-4 text-lg font-black text-slate-900">Pagamento e confirmação</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                            Se houver taxa, o pagamento é processado com segurança e sua reserva é confirmada automaticamente após a aprovação.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-gradient-to-br from-slate-50 to-blue-50 py-12 md:py-16 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold bg-white border"
                            style="border-color: var(--unn-azul-1); color: var(--unn-azul-1)">
                            <i class="fas fa-history"></i> Histórico
                        </span>
                        <h2 class="mt-4 text-3xl sm:text-4xl font-black text-slate-900">Últimos eventos</h2>
                        <p class="mt-2 text-slate-600 max-w-2xl">
                            Confira os 6 eventos mais recentes que já aconteceram.
                        </p>
                    </div>
                </div>

                @if($pastEvents->isEmpty())
                    <div class="bg-white rounded-3xl shadow-xl p-10 text-center">
                        <div class="text-slate-400 mb-4"><i class="fas fa-calendar-minus text-4xl"></i></div>
                        <h3 class="text-xl font-black text-slate-900 mb-2">Ainda não há eventos passados</h3>
                        <p class="text-slate-600">Assim que os primeiros eventos acontecerem, eles aparecerão aqui automaticamente.</p>
                    </div>
                @else
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($pastEvents as $event)
                            @php
                                $startDate = is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at;
                                $endDate = $event->end_at ? (is_string($event->end_at) ? \Carbon\Carbon::parse($event->end_at) : $event->end_at) : null;
                                $locationLine = $event->location ?: ($event->address ? 'Local do evento' : 'A confirmar');
                            @endphp
                            <div class="bg-white rounded-3xl p-7 shadow-lg border border-slate-100">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="text-sm font-bold text-slate-500">
                                            {{ $startDate ? $startDate->translatedFormat('d \\d\\e M, Y') : 'Data a confirmar' }}
                                        </div>
                                        <div class="mt-1 text-xl font-black text-slate-900 leading-snug">
                                            {{ $event->title }}
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                        Encerrado
                                    </span>
                                </div>

                                <div class="mt-4 space-y-2 text-sm text-slate-600">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-map-marker-alt text-red-500"></i>
                                        <span class="truncate">{{ $locationLine }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i class="far fa-clock text-slate-400"></i>
                                        <span>
                                            @if($startDate)
                                                {{ $startDate->format('H:i') }}
                                                @if($endDate) às {{ $endDate->format('H:i') }} @endif
                                            @else
                                                Horário a confirmar
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                                    <div>
                                        @if($event->current_price > 0 || $event->price > 0)
                                            <div class="font-black text-slate-900">R$ {{ number_format($event->current_price ?: $event->price, 2, ',', '.') }}</div>
                                            <div class="text-xs text-slate-500 mt-0.5">{{ $event->current_batch_label ?? 'Ingresso' }}</div>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">GRÁTIS</span>
                                        @endif
                                    </div>
                                    <span
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full font-bold bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed w-full sm:w-auto sm:min-w-[150px]">
                                        Evento encerrado <i class="fas fa-ban text-sm"></i>
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <section class="py-12 md:py-16 px-4 md:px-12 lg:px-24 bg-white">
            <div class="max-w-6xl mx-auto">
                <div class="unn-events-cta rounded-[32px] px-6 md:px-14 py-14 md:py-16 text-center shadow-2xl relative overflow-hidden">
                    <div class="absolute inset-0 opacity-20"
                        style="background-image: radial-gradient(rgba(255,255,255,0.45) 1px, transparent 1px); background-size: 42px 42px;"></div>

                    <div class="relative">
                        @guest
                            <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white">
                                Pronto para transformar sua rede?
                            </h2>
                            <p class="mt-4 text-white/80 text-lg sm:text-xl">
                                Junte-se a milhares de empreendedores que já estão crescendo juntos.
                            </p>

                            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                                <a href="{{ route('register') }}"
                                    class="inline-flex items-center justify-center gap-3 px-5 py-3 sm:px-10 sm:py-4 rounded-full font-black text-sm sm:text-base bg-white shadow-lg hover:shadow-xl transition whitespace-nowrap"
                                    style="color: var(--unn-azul-1)">
                                    <i class="fas fa-rocket"></i>
                                    <span class="sm:hidden">Começar grátis</span>
                                    <span class="hidden sm:inline">Começar agora - É grátis</span>
                                </a>
                                <a href="{{ route('premium') }}"
                                    class="inline-flex items-center justify-center gap-3 px-6 py-3 sm:px-10 sm:py-4 rounded-full font-black border-2 border-white text-white bg-white/10 hover:bg-white/15 transition whitespace-nowrap">
                                    <i class="fas fa-crown"></i> Ver planos Premium
                                </a>
                            </div>
                        @else
                            @php
                                $userName = trim((string) (auth()->user()->name ?? ''));
                                $firstName = $userName !== '' ? explode(' ', $userName)[0] : 'Membro';
                                $eventsCount = method_exists($events, 'total') ? (int) $events->total() : (is_countable($events) ? count($events) : 0);
                                $ctaTarget = $featuredEvent ? route('events.show', $featuredEvent) : route('events.index');
                            @endphp

                            <div class="max-w-5xl mx-auto text-left">
                                <div class="grid grid-cols-1 lg:grid-cols-[1.35fr_0.95fr] gap-4 md:gap-5">
                                    <div class="rounded-3xl border border-white/25 bg-white/10 backdrop-blur-md p-6 md:p-8 shadow-[0_20px_55px_rgba(0,0,0,.18)]">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-[0.16em] bg-white/20 text-white/90">
                                            <i class="fas fa-sparkles"></i> Seu acesso está ativo
                                        </span>

                                        <h2 class="mt-4 text-3xl md:text-4xl font-black text-white leading-tight">
                                            Bem-vindo de volta, {{ $firstName }}.
                                        </h2>
                                        <p class="mt-3 text-white/85 text-base md:text-lg leading-relaxed">
                                            Sua agenda de networking está pronta. Entre em um evento agora ou avance para o próximo nível.
                                        </p>

                                        <div class="mt-7 flex flex-col sm:flex-row gap-3">
                                            <a href="{{ $ctaTarget }}"
                                                class="inline-flex items-center justify-center gap-3 px-5 py-3 md:px-7 md:py-3.5 rounded-2xl font-black text-sm md:text-base bg-white shadow-xl hover:shadow-2xl transition"
                                                style="color: var(--unn-azul-1)">
                                                <i class="fas fa-ticket-alt"></i>
                                                Quero meu ingresso
                                            </a>
                                            <a href="{{ route('premium') }}"
                                                class="inline-flex items-center justify-center gap-3 px-5 py-3 md:px-7 md:py-3.5 rounded-2xl font-black text-sm md:text-base border-2 border-white text-white bg-white/10 hover:bg-white/20 transition">
                                                <i class="fas fa-crown"></i> Upgrade Premium
                                            </a>
                                        </div>
                                    </div>

                                    <div class="rounded-3xl border border-white/20 bg-[#0f3ddf]/55 backdrop-blur-md p-6 shadow-[0_16px_36px_rgba(0,0,0,.18)]">
                                        <h3 class="text-sm font-bold uppercase tracking-[0.14em] text-white/80">Radar UNN</h3>
                                        <div class="mt-4 space-y-3">
                                            <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3">
                                                <p class="text-[11px] uppercase tracking-[0.12em] font-semibold text-white/70">Eventos disponíveis</p>
                                                <p class="mt-1 text-2xl font-black text-white">{{ $eventsCount }}</p>
                                            </div>
                                            <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3">
                                                <p class="text-[11px] uppercase tracking-[0.12em] font-semibold text-white/70">Destaque atual</p>
                                                <p class="mt-1 text-sm font-bold text-white leading-snug">
                                                    {{ $featuredEvent ? \Illuminate\Support\Str::limit($featuredEvent->title, 42) : 'Novos eventos em breve' }}
                                                </p>
                                            </div>
                                        </div>

                                        <a href="{{ route('panel.dashboard') }}"
                                            class="mt-4 inline-flex w-full items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/20 border border-white/30 text-white font-bold transition">
                                            <i class="fas fa-chart-line"></i> Abrir meu painel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endguest
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
