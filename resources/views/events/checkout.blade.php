@extends('layouts.app')

@section('title', 'Reserva - ' . $event->title)

@section('content')
    @php
        $isDemo = ($event->is_demo ?? false) === true;
        $regularUnitPrice = (float) ($event->current_price ?? 0);
        $effectiveUnitPrice = (float) ($event->effective_price ?? $regularUnitPrice);
        $flashActive = method_exists($event, 'isFlashSaleActive') ? (bool) $event->isFlashSaleActive() : false;
        $isPaid = $effectiveUnitPrice > 0;
        $remaining = $event->remaining_seats;
        $alreadyConfirmed = $registration && in_array($registration->status, \App\Models\EventRegistration::COUNTED_STATUSES, true);
        $mpOptionEnabled = (bool) ($mpEnabled ?? false);
        $psOptionEnabled = (bool) ($psEnabled ?? false);
        $selectedGateway = old('gateway_provider');

        if (!$selectedGateway) {
            $selectedGateway = ($preferredGateway ?? null) ?: ($mpOptionEnabled ? 'mercadopago' : 'pagseguro');
        }

        if ($selectedGateway === 'pagseguro' && !$psOptionEnabled) {
            $selectedGateway = 'mercadopago';
        }

        if ($selectedGateway !== 'pagseguro' && !$mpOptionEnabled && $psOptionEnabled) {
            $selectedGateway = 'pagseguro';
        }

        $fieldLabelClasses = 'mb-2 block text-[11px] font-black uppercase tracking-[0.24em] text-slate-500';
        $fieldHintClasses = 'mt-2 text-xs text-slate-500';
        $fieldPanelClasses = 'rounded-3xl border border-slate-100 bg-slate-50/80 p-5';
        $fieldInputClasses = 'w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400';
        $fieldReadonlyClasses = 'w-full cursor-not-allowed rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3.5 text-sm font-semibold text-slate-500';
        $fieldErrorClasses = ' border-red-300 bg-red-50/80 text-red-900 placeholder:text-red-300 focus:border-red-500 focus:ring-red-100';
    @endphp

    <div class="min-h-screen bg-slate-50 px-4 pb-20 pt-28">
        <div class="mx-auto max-w-5xl">
            <a href="{{ route('events.show', $event) }}"
                class="mb-6 inline-flex items-center gap-2 text-gray-600 transition hover:text-blue-700">
                <i class="fas fa-arrow-left"></i> Voltar para o evento
            </a>

            <div class="grid gap-8 lg:grid-cols-3">
                <div class="lg:col-span-1">
                    <div class="sticky top-28 rounded-2xl bg-white p-6 shadow-lg">
                        <h3 class="mb-4 text-lg font-bold text-gray-900">Resumo</h3>
                        <p class="font-bold text-gray-900">{{ $event->title }}</p>
                        <p class="mt-1 text-sm text-gray-500">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ optional($event->start_at)->format('d/m/Y H:i') }}
                        </p>

                        @if($event->capacity)
                            <div class="mt-4 text-sm">
                                <div class="flex items-center justify-between text-gray-600">
                                    <span>Capacidade</span>
                                    <span class="font-semibold text-gray-900">{{ (int) $event->capacity }}</span>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-gray-600">
                                    <span>Disponíveis</span>
                                    <span class="font-semibold {{ $remaining === 0 ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $remaining }}
                                    </span>
                                </div>
                            </div>
                        @endif

                        <div class="mt-6 border-t border-gray-100 pt-6">
                            @if($isPaid)
                                <span
                                    class="mb-2 inline-block rounded-full bg-blue-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-blue-700">
                                    {{ $event->current_batch_label }}
                                </span>
                                <p class="text-sm text-gray-500">Valor por pessoa</p>
                                <div class="flex items-end gap-3">
                                    <p class="text-3xl font-black text-gray-900">
                                        {{ 'R$ ' . number_format($effectiveUnitPrice, 2, ',', '.') }}
                                    </p>
                                    @if($flashActive && $regularUnitPrice > 0 && $effectiveUnitPrice < $regularUnitPrice)
                                        <p class="mb-1 text-sm text-gray-400 line-through">
                                            {{ 'R$ ' . number_format($regularUnitPrice, 2, ',', '.') }}
                                        </p>
                                    @endif
                                </div>
                                @if($flashActive && $event->flash_sale_ends_at)
                                    <div
                                        class="mt-2 inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-black text-rose-800">
                                        <i class="fas fa-bolt"></i> Promoção relâmpago ativa
                                    </div>
                                @endif

                                @if($mpOptionEnabled || $psOptionEnabled)
                                    <div
                                        class="mt-4 flex flex-col gap-2 border-t border-gray-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                                        <span class="text-sm text-gray-500">Você está pagando com:</span>
                                        <span id="display_selected_gateway"
                                            class="rounded-full bg-gray-100 px-3 py-1 text-center text-sm font-bold text-gray-900">
                                            {{ $selectedGateway === 'pagseguro' ? 'PagSeguro' : 'Mercado Pago' }}
                                        </span>
                                    </div>
                                @endif
                            @else
                                <p class="text-sm text-gray-500">Entrada</p>
                                <p class="text-3xl font-black text-green-600">Gratuita</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    @if(session('error'))
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
                            <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    @if($alreadyConfirmed)
                        <div class="rounded-2xl bg-white p-8 shadow-lg">
                            <h2 class="mb-2 text-2xl font-black text-gray-900">Vaga confirmada</h2>
                            <p class="mb-6 text-gray-600">Você já possui inscrição confirmada para este evento.</p>
                            <a href="{{ route('events.show', $event) }}"
                                class="btn-primary inline-flex items-center gap-2 rounded-xl px-6 py-3 font-bold">
                                <i class="fas fa-ticket-alt"></i> Ver detalhes do evento
                            </a>
                        </div>
                    @else
                        <div class="rounded-2xl bg-white p-6 shadow-lg md:p-8">
                            <h2 class="mb-6 text-2xl font-black text-gray-900">Finalizar Reserva</h2>

                            @if($isDemo)
                                <div class="mb-6 rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-yellow-800">
                                    <i class="fas fa-info-circle mr-2"></i> Evento de demonstração: configure um evento real no
                                    painel administrativo.
                                </div>
                            @endif

                            <form action="{{ route('events.reserve', $event) }}" method="POST" class="space-y-6">
                                @csrf

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="{{ $fieldPanelClasses }}">
                                        <label for="event_checkout_quantity" class="{{ $fieldLabelClasses }}">Quantidade</label>
                                        <input id="event_checkout_quantity" type="number" name="quantity" min="1" max="10"
                                            value="{{ old('quantity', 1) }}"
                                            class="{{ $fieldInputClasses }}{{ $errors->has('quantity') ? $fieldErrorClasses : '' }}"
                                            {{ $isDemo ? 'disabled' : '' }}>
                                        <p class="{{ $fieldHintClasses }}">Escolha entre 1 e 10 ingressos por reserva.</p>
                                        @error('quantity')
                                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="{{ $fieldPanelClasses }}">
                                        <label for="event_checkout_type" class="{{ $fieldLabelClasses }}">Tipo</label>
                                        <input id="event_checkout_type" type="text" value="{{ $isPaid ? 'Pago' : 'Gratuito' }}"
                                            disabled class="{{ $fieldReadonlyClasses }}">
                                        <p class="{{ $fieldHintClasses }}">
                                            {{ $isPaid ? 'Reserva com pagamento online.' : 'Reserva gratuita com confirmação imediata.' }}
                                        </p>
                                    </div>
                                </div>

                                @if($isPaid)
                                    <div class="{{ $fieldPanelClasses }}">
                                        <label for="event_checkout_coupon" class="{{ $fieldLabelClasses }}">Cupom de desconto</label>
                                        <input id="event_checkout_coupon" type="text" name="coupon_code"
                                            value="{{ old('coupon_code') }}" placeholder="Ex: BLACKFRIDAY26"
                                            class="{{ $fieldInputClasses }}{{ $errors->has('coupon_code') ? $fieldErrorClasses : '' }}"
                                            {{ $isDemo ? 'disabled' : '' }}>
                                        @error('coupon_code')
                                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                        @enderror
                                        <p class="{{ $fieldHintClasses }}">Opcional. Aplique o cupom antes de seguir para o pagamento.</p>
                                    </div>

                                    @if($mpOptionEnabled || $psOptionEnabled)
                                        <div class="border-t border-gray-100 pt-4">
                                            <div class="{{ $fieldPanelClasses }}">
                                                <label class="{{ $fieldLabelClasses }} mb-3">Forma de pagamento</label>
                                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                    @if($mpOptionEnabled)
                                                        <label class="cursor-pointer">
                                                            <input type="radio" name="gateway_provider" value="mercadopago"
                                                                class="peer sr-only"
                                                                {{ $selectedGateway === 'mercadopago' ? 'checked' : '' }}>
                                                            <div
                                                                class="rounded-2xl border border-slate-200 bg-white p-4 transition-all hover:border-blue-400 hover:bg-blue-50/50 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:ring-4 peer-checked:ring-blue-100">
                                                                <div class="flex items-center gap-3">
                                                                    <div
                                                                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                                                                        <i class="fas fa-hand-holding-dollar"></i>
                                                                    </div>
                                                                    <div>
                                                                        <p class="font-bold text-gray-900">Mercado Pago</p>
                                                                        <p class="text-xs text-gray-500">Cartão, Pix, Boleto</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    @endif

                                                    @if($psOptionEnabled)
                                                        <label class="cursor-pointer">
                                                            <input type="radio" name="gateway_provider" value="pagseguro"
                                                                class="peer sr-only"
                                                                {{ $selectedGateway === 'pagseguro' ? 'checked' : '' }}>
                                                            <div
                                                                class="rounded-2xl border border-slate-200 bg-white p-4 transition-all hover:border-green-400 hover:bg-green-50/50 peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:ring-4 peer-checked:ring-green-100">
                                                                <div class="flex items-center gap-3">
                                                                    <div
                                                                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-green-100 text-green-600">
                                                                        <i class="fas fa-credit-card"></i>
                                                                    </div>
                                                                    <div>
                                                                        <p class="font-bold text-gray-900">PagSeguro</p>
                                                                        <p class="text-xs text-gray-500">Cartão, Pix</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                @guest
                                    <div class="border-t border-gray-100 pt-6">
                                        <div class="mb-5">
                                            <h3 class="text-lg font-bold text-gray-900">Seus dados</h3>
                                            <p class="mt-1 text-sm text-slate-500">Preencha os campos abaixo para concluir sua reserva.</p>
                                        </div>

                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div class="md:col-span-2">
                                                <label for="event_checkout_name" class="{{ $fieldLabelClasses }}">Nome completo</label>
                                                <input id="event_checkout_name" type="text" name="name" value="{{ old('name') }}"
                                                    placeholder="Seu nome completo" autocomplete="name" required
                                                    class="{{ $fieldInputClasses }}{{ $errors->has('name') ? $fieldErrorClasses : '' }}"
                                                    {{ $isDemo ? 'disabled' : '' }}>
                                                @error('name')
                                                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="event_checkout_email" class="{{ $fieldLabelClasses }}">E-mail</label>
                                                <input id="event_checkout_email" type="email" name="email"
                                                    value="{{ old('email') }}" placeholder="voce@exemplo.com"
                                                    autocomplete="email" required
                                                    class="{{ $fieldInputClasses }}{{ $errors->has('email') ? $fieldErrorClasses : '' }}"
                                                    {{ $isDemo ? 'disabled' : '' }}>
                                                @error('email')
                                                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="event_checkout_cpf" class="{{ $fieldLabelClasses }}">CPF</label>
                                                <input id="event_checkout_cpf" type="text" name="cpf" value="{{ old('cpf') }}"
                                                    placeholder="000.000.000-00" inputmode="numeric"
                                                    {{ $isPaid ? 'required' : '' }} data-mask="999.999.999-99"
                                                    class="{{ $fieldInputClasses }}{{ $errors->has('cpf') ? $fieldErrorClasses : '' }}"
                                                    {{ $isDemo ? 'disabled' : '' }}>
                                                <p class="{{ $fieldHintClasses }}">{{ $isPaid ? 'Obrigatório para pagamento.' : 'Opcional para reservas gratuitas.' }}</p>
                                                @error('cpf')
                                                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="event_checkout_phone" class="{{ $fieldLabelClasses }}">Telefone</label>
                                                <input id="event_checkout_phone" type="text" name="phone"
                                                    value="{{ old('phone') }}" placeholder="(00) 00000-0000"
                                                    autocomplete="tel" inputmode="tel" data-mask="(99) 99999-9999"
                                                    class="{{ $fieldInputClasses }}{{ $errors->has('phone') ? $fieldErrorClasses : '' }}"
                                                    {{ $isDemo ? 'disabled' : '' }}>
                                                <p class="{{ $fieldHintClasses }}">Opcional. Usado apenas para contato sobre o evento.</p>
                                                @error('phone')
                                                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="password" class="{{ $fieldLabelClasses }}">Senha</label>
                                                <input type="password" name="password" id="password"
                                                    placeholder="Mínimo de 8 caracteres" autocomplete="new-password" required
                                                    class="{{ $fieldInputClasses }}{{ $errors->has('password') ? $fieldErrorClasses : '' }}"
                                                    {{ $isDemo ? 'disabled' : '' }}>
                                                @error('password')
                                                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                                <p id="pw-strength" class="mt-2 text-xs text-gray-500">Força: <span>-</span></p>
                                            </div>

                                            <div>
                                                <label for="event_checkout_password_confirmation"
                                                    class="{{ $fieldLabelClasses }}">Confirmar senha</label>
                                                <input id="event_checkout_password_confirmation" type="password"
                                                    name="password_confirmation" placeholder="Repita sua senha"
                                                    autocomplete="new-password" required
                                                    class="{{ $fieldInputClasses }}{{ $errors->has('password_confirmation') ? $fieldErrorClasses : '' }}"
                                                    {{ $isDemo ? 'disabled' : '' }}>
                                                @error('password_confirmation')
                                                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                @endguest

                                @if($event->capacity && $remaining === 0)
                                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
                                        <i class="fas fa-ban mr-2"></i> Evento lotado no momento.
                                    </div>
                                @endif

                                <button type="submit"
                                    class="btn-primary flex w-full items-center justify-center gap-2 rounded-xl py-4 text-lg font-bold text-white shadow-lg transition hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60"
                                    {{ ($isDemo || ($event->capacity && $remaining === 0)) ? 'disabled' : '' }}>
                                    <i class="fas fa-ticket-alt"></i>
                                    {{ $isPaid ? 'Ir para pagamento' : 'Confirmar minha vaga' }}
                                </button>

                                <p class="text-center text-xs text-gray-500">
                                    Ao continuar, você concorda com os termos e políticas do evento.
                                </p>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const radios = document.querySelectorAll('input[name="gateway_provider"]');
                const display = document.getElementById('display_selected_gateway');

                if (!display || radios.length === 0) {
                    return;
                }

                function updateGatewayDisplay() {
                    const selected = document.querySelector('input[name="gateway_provider"]:checked');

                    if (!selected) {
                        return;
                    }

                    display.innerText = selected.value === 'pagseguro' ? 'PagSeguro' : 'Mercado Pago';
                }

                radios.forEach((radio) => {
                    radio.addEventListener('change', updateGatewayDisplay);
                });

                updateGatewayDisplay();
            });
        </script>
    @endpush

@endsection
