@extends('layouts.app')

@section('title', ($mentorship->title ?? 'Mentoria') . ' - UNN')

@push('styles')
    <style>
        .unn-mentorship-show-hero {
            background:
                radial-gradient(1200px circle at 15% 20%, rgba(255, 255, 255, 0.18) 0%, transparent 55%),
                radial-gradient(900px circle at 85% 0%, rgba(255, 255, 255, 0.12) 0%, transparent 50%),
                linear-gradient(180deg, var(--unn-azul-3) 0%, var(--unn-azul-1) 55%, var(--unn-azul-3) 100%);
        }

        .unn-mentorship-show-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(rgba(255, 255, 255, 0.35) 1px, transparent 1px),
                radial-gradient(rgba(255, 255, 255, 0.18) 1px, transparent 1px);
            background-size: 36px 36px, 64px 64px;
            background-position: 0 0, 18px 18px;
            opacity: 0.26;
            pointer-events: none;
        }
    </style>
@endpush

@section('content')
    @php
        $mentorName = optional($mentorship->mentor)->name ?? 'Mentor UNN';
        $price = (float) ($mentorship->price ?? 0);
        $slots = $mentorship->slots;
        $slotsLabel = is_null($slots) ? 'A confirmar' : (string) $slots;
        $description = trim((string) ($mentorship->description ?? ''));
    @endphp

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
        <section class="unn-mentorship-show-hero relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-black/0 via-black/0 to-black/25 pointer-events-none"></div>

            <div class="px-4 md:px-12 lg:px-24 pt-10 md:pt-14 pb-16 md:pb-20 relative">
                <div class="max-w-6xl mx-auto">
                    <a href="{{ route('mentorships.index') }}"
                        class="inline-flex items-center gap-2 text-white/85 hover:text-white font-semibold">
                        <i class="fas fa-arrow-left"></i> Voltar para mentorias
                    </a>

                    <div class="mt-8 md:mt-10">
                        <h1 class="text-4xl sm:text-5xl md:text-6xl font-black tracking-tight text-white">
                            {{ $mentorship->title ?? 'Mentoria' }}
                        </h1>
                        <p class="mt-4 text-white/80 text-base sm:text-lg max-w-3xl">
                            Acompanhamento com orientação prática para você acelerar resultados com networking e negócios.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="px-4 md:px-12 lg:px-24 -mt-10 pb-14 md:pb-20">
            <div class="max-w-6xl mx-auto">
                <div class="grid lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white rounded-3xl shadow-xl border border-slate-100 p-8 md:p-10">
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900">Sobre a mentoria</h2>

                        @if($description !== '')
                            <div class="mt-5 text-slate-600 leading-relaxed">
                                {!! $description !!}
                            </div>
                        @else
                            <p class="mt-5 text-slate-600 leading-relaxed">Sem descrição.</p>
                        @endif
                    </div>

                    <aside class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 md:p-10">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Investimento</p>
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-xs font-black uppercase tracking-wider">
                                Premium
                            </span>
                        </div>

                        <p class="mt-3 text-4xl font-black text-slate-900">
                            {{ $price > 0 ? 'R$ '.number_format($price, 2, ',', '.') : 'Gratuito' }}
                        </p>

                        <div class="mt-8 space-y-4">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
                                    <i class="fas fa-user-tie text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">Mentor</div>
                                    <div class="text-sm text-slate-500">{{ $mentorName }}</div>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-green-50 flex items-center justify-center shrink-0">
                                    <i class="fas fa-users text-green-600"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">Vagas</div>
                                    <div class="text-sm text-slate-500">{{ $slotsLabel }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 flex flex-col gap-3">
                            <a href="{{ route('premium') }}"
                                class="btn-primary text-white px-8 py-4 rounded-xl font-bold inline-flex items-center justify-center gap-3 shadow-lg hover:shadow-xl transition">
                                Ver planos Premium <i class="fas fa-crown"></i>
                            </a>
                            <a href="{{ route('mentorships.index') }}"
                                class="px-8 py-4 rounded-xl font-bold border-2 border-slate-200 text-slate-700 hover:bg-slate-50 transition inline-flex items-center justify-center">
                                Ver outras mentorias
                            </a>
                        </div>

                        <p class="mt-6 text-xs text-slate-500 leading-relaxed">
                            Dica: para acesso a mentorias e recursos premium, escolha um plano no menu Premium.
                        </p>
                    </aside>
                </div>
            </div>
        </section>
    </div>
@endsection

