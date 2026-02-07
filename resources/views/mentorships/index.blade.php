@extends('layouts.app')

@section('title', 'Mentorias - UNN')

@push('styles')
    <style>
        .unn-mentorships-hero {
            background:
                radial-gradient(1200px circle at 15% 20%, rgba(255, 255, 255, 0.18) 0%, transparent 55%),
                radial-gradient(900px circle at 85% 0%, rgba(255, 255, 255, 0.12) 0%, transparent 50%),
                linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
        }

        .unn-mentorships-hero::before {
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
        $paginator = $mentorships ?? collect();
        $mentorshipsCollection = method_exists($paginator, 'getCollection') ? $paginator->getCollection() : collect($paginator);
        $totalCount = method_exists($paginator, 'total') ? (int) $paginator->total() : $mentorshipsCollection->count();

        $pluralLabel = $totalCount === 1 ? 'mentoria' : 'mentorias';
    @endphp

    <div class="min-h-screen">
        <section class="unn-mentorships-hero relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/25 pointer-events-none"></div>

            <div class="px-4 md:px-12 lg:px-24 pt-10 md:pt-14 pb-14 md:pb-20 relative">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center">
                        <span
                            class="inline-flex items-center justify-center px-6 py-2 rounded-full text-sm font-bold text-white border border-white/20 bg-white/15 backdrop-blur">
                            Mentorias
                        </span>
                        <h1 class="mt-6 text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-white">
                            Mentorias Premium UNN
                        </h1>
                        <p class="mt-3 text-white/80 text-base sm:text-lg">
                            Conteúdo gravado + acompanhamento de mentores
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-gradient-to-br from-slate-50 to-blue-50 py-12 md:py-16 px-4 md:px-12 lg:px-24">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                    <div>
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-black text-gray-900">Mentorias disponíveis</h2>
                        <p class="text-gray-600 mt-2 max-w-2xl">
                            Escolha uma mentoria e acelere seu crescimento com orientação prática.
                        </p>
                    </div>
                    <span class="text-sm font-bold text-slate-500 uppercase tracking-wider">
                        {{ $totalCount }} {{ $pluralLabel }}
                    </span>
                </div>

                @if($mentorshipsCollection->count() > 0)
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($mentorshipsCollection as $mentorship)
                            @php
                                $mentorName = optional($mentorship->mentor)->name ?? 'Mentor UNN';
                                $price = (float) ($mentorship->price ?? 0);
                                $slots = $mentorship->slots;
                                $slotsLabel = is_null($slots) ? 'A confirmar' : (string) $slots;
                                $showUrl = !empty($mentorship->id) ? route('mentorships.show', $mentorship->id) : '#';
                            @endphp

                            <article class="bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-lg transition overflow-hidden">
                                <div class="p-8">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $mentorName }}</p>
                                            <h3 class="mt-2 text-xl font-black text-slate-900 leading-tight">
                                                {{ $mentorship->title ?? 'Mentoria' }}
                                            </h3>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Investimento</p>
                                            <p class="mt-1 text-lg font-black" style="color: var(--unn-azul-1)">
                                                {{ $price > 0 ? 'R$ '.number_format($price, 2, ',', '.') : 'Gratuito' }}
                                            </p>
                                        </div>
                                    </div>

                                    <p class="mt-4 text-slate-600 text-sm leading-relaxed">
                                        {{ Str::limit($mentorship->description ?? 'Sem descrição.', 140) }}
                                    </p>

                                    <div class="mt-6 grid grid-cols-2 gap-3">
                                        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Vagas</p>
                                            <p class="mt-1 font-black text-slate-900">{{ $slotsLabel }}</p>
                                        </div>
                                        <div class="rounded-2xl bg-slate-50 border border-slate-100 p-4">
                                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Formato</p>
                                            <p class="mt-1 font-black text-slate-900">Premium</p>
                                        </div>
                                    </div>

                                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                                        <a href="{{ $showUrl }}"
                                            class="px-8 py-4 rounded-xl font-bold border-2 border-slate-200 text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center">
                                            Saiba mais
                                        </a>
                                        <a href="{{ route('premium') }}"
                                            class="btn-primary text-white px-8 py-4 rounded-xl font-bold inline-flex items-center justify-center gap-3 shadow-lg hover:shadow-xl transition">
                                            Ver planos Premium <i class="fas fa-crown"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if(method_exists($paginator, 'links'))
                        <div class="mt-10">
                            {{ $paginator->links() }}
                        </div>
                    @endif
                @else
                    <div class="max-w-3xl mx-auto">
                        <div class="bg-white rounded-[32px] shadow-2xl p-10 text-center">
                            <div class="text-slate-400 mb-4"><i class="fas fa-chalkboard-teacher text-5xl"></i></div>
                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900">Nenhuma mentoria disponível</h2>
                            <p class="mt-2 text-slate-600">No momento não temos mentorias abertas. Volte em breve.</p>

                            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                                <a href="{{ route('premium') }}"
                                    class="btn-primary text-white px-8 py-4 rounded-xl font-bold inline-flex items-center justify-center gap-3 shadow-lg hover:shadow-xl transition">
                                    Ver planos Premium <i class="fas fa-crown"></i>
                                </a>
                                <a href="{{ route('home') }}"
                                    class="px-8 py-4 rounded-xl font-bold border-2 border-slate-200 text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center">
                                    Voltar ao início
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
