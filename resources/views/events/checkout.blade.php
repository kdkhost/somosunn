@extends('layouts.app')

@section('title', 'Reserva - ' . $event->title)

@section('content')
@php
    $isDemo = ($event->is_demo ?? false) === true;
    $isPaid = (float) $event->current_price > 0;
    $remaining = $event->remaining_seats;
    $alreadyConfirmed = $registration && in_array($registration->status, \App\Models\EventRegistration::COUNTED_STATUSES, true);
@endphp

<div class="min-h-screen bg-slate-50 pt-28 pb-20 px-4">
    <div class="max-w-5xl mx-auto">
        <a href="{{ route('events.show', $event) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-700 mb-6">
            <i class="fas fa-arrow-left"></i> Voltar para o evento
        </a>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-28">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Resumo</h3>
                    <p class="font-bold text-gray-900">{{ $event->title }}</p>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ optional($event->start_at)->format('d/m/Y H:i') }}
                    </p>

                    @if($event->capacity)
                        <div class="mt-4 text-sm">
                            <div class="flex items-center justify-between text-gray-600">
                                <span>Capacidade</span>
                                <span class="font-semibold text-gray-900">{{ (int) $event->capacity }}</span>
                            </div>
                            <div class="flex items-center justify-between text-gray-600 mt-1">
                                <span>Disponíveis</span>
                                <span class="font-semibold {{ $remaining === 0 ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $remaining }}
                                </span>
                            </div>
                        </div>
                    @endif

                    <div class="border-t border-gray-100 mt-6 pt-6">
                        @if($isPaid)
                            <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-2">
                                {{ $event->current_batch_label }}
                            </span>
                            <p class="text-sm text-gray-500">Valor por pessoa</p>
                            <p class="text-3xl font-black text-gray-900">R$ {{ number_format($event->current_price, 2, ',', '.') }}</p>
                        @else
                            <p class="text-sm text-gray-500">Entrada</p>
                            <p class="text-3xl font-black text-green-600">Gratuita</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6">
                        <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl mb-6">
                        <i class="fas fa-circle-check mr-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if($alreadyConfirmed)
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h2 class="text-2xl font-black text-gray-900 mb-2">Vaga confirmada</h2>
                        <p class="text-gray-600 mb-6">Você já possui inscrição confirmada para este evento.</p>
                        <a href="{{ route('events.show', $event) }}" class="inline-flex items-center gap-2 btn-primary px-6 py-3 rounded-xl font-bold">
                            <i class="fas fa-ticket-alt"></i> Ver detalhes do evento
                        </a>
                    </div>
                @else
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                        <h2 class="text-2xl font-black text-gray-900 mb-6">Finalizar Reserva</h2>

                        @if($isDemo)
                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-xl mb-6">
                                <i class="fas fa-info-circle mr-2"></i> Evento de demonstração: configure um evento real no painel administrativo.
                            </div>
                        @endif

                        <form action="{{ route('events.reserve', $event) }}" method="POST" class="space-y-6">
                            @csrf

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade</label>
                                    <input type="number" name="quantity" min="1" max="10" value="{{ old('quantity', 1) }}"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" {{ $isDemo ? 'disabled' : '' }}>
                                </div>
                                <div class="sm:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                                    <input type="text" value="{{ $isPaid ? 'Pago' : 'Gratuito' }}" disabled
                                        class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-600">
                                </div>
                            </div>

                            @if($isPaid)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Cupom de desconto (opcional)</label>
                                    <input type="text" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="Ex: BLACKFRIDAY26"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" {{ $isDemo ? 'disabled' : '' }}>
                                    <p class="text-xs text-gray-500 mt-2">Se tiver um cupom, aplique aqui antes do pagamento.</p>
                                </div>
                            @endif

                            @guest
                                <div class="border-t border-gray-100 pt-6">
                                    <h3 class="text-lg font-bold text-gray-900 mb-4">Seus dados</h3>

                                    <div class="grid md:grid-cols-2 gap-4">
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome completo</label>
                                            <input type="text" name="name" value="{{ old('name') }}" required
                                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" {{ $isDemo ? 'disabled' : '' }}>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                                            <input type="email" name="email" value="{{ old('email') }}" required
                                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" {{ $isDemo ? 'disabled' : '' }}>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">CPF {{ $isPaid ? '(obrigatório)' : '(opcional)' }}</label>
                                            <input type="text" name="cpf" value="{{ old('cpf') }}" {{ $isPaid ? 'required' : '' }} data-mask="999.999.999-99"
                                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" {{ $isDemo ? 'disabled' : '' }}>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Telefone (opcional)</label>
                                            <input type="text" name="phone" value="{{ old('phone') }}" data-mask="(99) 99999-9999"
                                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" {{ $isDemo ? 'disabled' : '' }}>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                                            <input type="password" name="password" id="password" required
                                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" {{ $isDemo ? 'disabled' : '' }}>
                                            <p id="pw-strength" class="text-xs text-gray-500 mt-1">Força: <span>—</span></p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar senha</label>
                                            <input type="password" name="password_confirmation" required
                                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" {{ $isDemo ? 'disabled' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            @endguest

                            @if($event->capacity && $remaining === 0)
                                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl">
                                    <i class="fas fa-ban mr-2"></i> Evento lotado no momento.
                                </div>
                            @endif

                            <button type="submit"
                                class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
                                {{ ($isDemo || ($event->capacity && $remaining === 0)) ? 'disabled' : '' }}>
                                <i class="fas fa-ticket-alt"></i>
                                {{ $isPaid ? 'Ir para pagamento' : 'Confirmar minha vaga' }}
                            </button>

                            <p class="text-xs text-gray-500 text-center">
                                Ao continuar, você concorda com os termos e políticas do evento.
                            </p>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
