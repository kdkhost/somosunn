@extends('layouts.app')

@section('title', 'Próximos Eventos - UNN')

@section('content')
    @php
        $events = $events ?? collect();
    @endphp

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <section class="pt-24 md:pt-28 pb-10 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                    <div>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold"
                            style="background: var(--unn-azul-1); color: white">
                            <i class="fas fa-calendar-check"></i> Agenda
                        </span>
                        <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-gray-900 mt-4">Próximos Eventos</h1>
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

                @if(isset($isDemo) && $isDemo)
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-2xl p-4 mb-6">
                        <div class="flex gap-3">
                            <div class="mt-0.5"><i class="fas fa-exclamation-triangle"></i></div>
                            <div>
                                <p class="font-bold">Dados de demonstração</p>
                                <p class="text-sm">Estes eventos são exemplos. Configure eventos reais no painel administrativo.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900">Agenda de Eventos</h2>
                        <span class="text-sm text-gray-500">
                            {{ $events->count() }} {{ $events->count() === 1 ? 'evento' : 'eventos' }}
                        </span>
                    </div>

                    @if($events->isEmpty())
                        <div class="p-10 text-center">
                            <div class="text-gray-400 mb-4"><i class="fas fa-calendar-times text-4xl"></i></div>
                            <h3 class="text-xl font-black text-gray-900 mb-2">Nenhum evento próximo encontrado</h3>
                            <p class="text-gray-600">Fique atento, novas datas serão liberadas em breve.</p>
                        </div>
                    @else
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
                                                <div class="text-slate-700"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i>{{ $event->location }}</div>
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
                                                <a href="{{ isset($isDemo) && $isDemo ? '#' : route('events.show', $event->id) }}"
                                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-white btn-primary shadow-lg hover:shadow-xl transition {{ isset($isDemo) && $isDemo ? 'pointer-events-none opacity-60' : '' }}">
                                                    <i class="fas fa-ticket-alt"></i> Detalhes
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
