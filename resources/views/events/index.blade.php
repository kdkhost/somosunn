@extends('layouts.app')

@section('title', 'Próximos Eventos - UNN')

@push('styles')
    <style>
        .unn-events-hero {
            background:
                radial-gradient(1200px circle at 15% 20%, rgba(255, 255, 255, 0.18) 0%, transparent 55%),
                radial-gradient(900px circle at 85% 0%, rgba(255, 255, 255, 0.12) 0%, transparent 50%),
                linear-gradient(120deg, #2563eb 0%, #6d28d9 48%, #c026d3 100%);
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
    </style>
@endpush

@section('content')
    @php
        $events = $events ?? collect();
        $featuredEvent = $featuredEvent ?? ($events->first() ?: null);
        $isDemo = (bool) ($isDemo ?? false);

        $featuredImageSetting =
            \App\Models\Setting::get('events_featured_image')
            ?: \App\Models\Setting::get('hero_image')
            ?: \App\Models\Setting::get('pwa_banner');

        $featuredImage = $featuredImageSetting
            ? asset(ltrim($featuredImageSetting, '/'))
            : 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1400';
    @endphp

    <div class="min-h-screen">
        <section class="unn-events-hero relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/20 pointer-events-none"></div>

            <div class="px-4 md:px-12 lg:px-24 pt-10 md:pt-14 pb-14 md:pb-20 relative">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center">
                        <span class="inline-flex items-center justify-center px-6 py-2 rounded-full text-sm font-bold text-white border border-white/20 bg-white/15 backdrop-blur">
                            Em destaque
                        </span>
                        <h1 class="mt-6 text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-white">
                            Próximo Evento UNN
                        </h1>
                        <p class="mt-3 text-white/80 text-base sm:text-lg">
                            Não perca a oportunidade de expandir sua rede
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
                                        <img src="{{ $featuredImage }}" alt="Evento UNN" class="absolute inset-0 w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-black/0"></div>

                                        @if($featuredEvent->capacity)
                                            <span class="absolute top-6 left-1/2 -translate-x-1/2 px-6 py-3 rounded-full font-bold text-white shadow-lg"
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
                                                <div class="w-12 h-12 rounded-2xl bg-purple-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-clock text-purple-600"></i>
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
                                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-map-marker-alt text-indigo-600"></i>
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

        @if($events->isNotEmpty())
            <section class="bg-gradient-to-br from-slate-50 to-blue-50 py-12 md:py-16 px-4 md:px-12 lg:px-24">
                <div class="max-w-7xl mx-auto">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                        <div>
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold"
                                style="background: var(--unn-azul-1); color: white">
                                <i class="fas fa-calendar-check"></i> Agenda
                            </span>
                            <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-gray-900 mt-4">Agenda de Eventos</h2>
                            <p class="text-gray-600 mt-2 max-w-2xl">
                                Visualização é pública. A confirmação de vaga pode exigir pagamento conforme o evento.
                            </p>
                        </div>

                        <a href="{{ route('premium') }}"
                            class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full font-bold bg-white border hover:bg-slate-50 transition"
                            style="border-color: var(--unn-azul-1); color: var(--unn-azul-1)">
                            <i class="fas fa-crown"></i> Ver planos Premium
                        </a>
                    </div>

                    <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900">Todos os eventos</h3>
                            <span class="text-sm text-gray-500">
                                {{ $events->count() }} {{ $events->count() === 1 ? 'evento' : 'eventos' }}
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-slate-50">
                                    <tr class="text-left text-sm text-gray-600">
                                        <th class="px-6 py-4 font-bold">Data</th>
                                        <th class="px-6 py-4 font-bold">Evento</th>
                                        <th class="px-6 py-4 font-bold">Localização</th>
                                        <th class="px-6 py-4 font-bold">Vagas</th>
                                        <th class="px-6 py-4 font-bold">Valor</th>
                                        <th class="px-6 py-4 font-bold text-right">Ação</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($events as $event)
                                        @php
                                            $startDate = is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at;
                                            $locationLine = $event->location ?: ($event->address ? 'Local do evento' : 'A confirmar');
                                        @endphp
                                        <tr class="hover:bg-slate-50/60 transition">
                                            <td class="px-6 py-5 align-top">
                                                <div class="w-14 text-center bg-slate-50 rounded-xl p-2 border border-slate-200">
                                                    <span class="block font-black text-lg leading-none text-slate-900">{{ $startDate->format('d') }}</span>
                                                    <span class="block uppercase text-xs font-bold text-slate-500">{{ $startDate->translatedFormat('M') }}</span>
                                                </div>
                                                <div class="text-xs text-slate-500 mt-2 text-center">
                                                    <i class="far fa-clock mr-1"></i>{{ $startDate->format('H:i') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="font-black text-slate-900">{{ $event->title }}</div>
                                                @if($event->speaker)
                                                    <div class="text-sm text-slate-500 mt-1"><i class="fas fa-user-tie mr-1"></i>{{ $event->speaker }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="text-slate-700"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i>{{ $locationLine }}</div>
                                                @if($event->address)
                                                    <div class="text-xs text-slate-500 mt-1 max-w-sm truncate">{{ $event->address }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-5">
                                                @if($event->capacity)
                                                    <div class="text-sm font-bold text-slate-900">{{ $event->capacity }} vagas</div>
                                                    <div class="text-xs text-slate-500 mt-1">sujeito a lotação</div>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Ilimitado</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-5">
                                                @if($event->current_price > 0 || $event->price > 0)
                                                    <div class="font-black text-slate-900">R$ {{ number_format($event->current_price ?: $event->price, 2, ',', '.') }}</div>
                                                    <div class="text-xs text-slate-500 mt-1">{{ $event->current_batch_label ?? 'Ingresso' }}</div>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">GRÁTIS</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-5 text-right">
                                                <a href="{{ $isDemo ? '#' : route('events.show', $event->id) }}"
                                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-white btn-primary shadow-lg hover:shadow-xl transition {{ $isDemo ? 'pointer-events-none opacity-60' : '' }}">
                                                    <i class="fas fa-ticket-alt"></i> Detalhes
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection
