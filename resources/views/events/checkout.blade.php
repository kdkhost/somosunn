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
        $selectedGateway = old('gateway_provider');

        if (!$selectedGateway) {
            $selectedGateway = ($preferredGateway ?? null) ?: 'mercadopago';
        }

        $activeGateways = $activeGateways ?? [];

        $fieldLabelClasses = 'mb-2 block text-[11px] font-black uppercase tracking-[0.24em] text-slate-500';
        $fieldHintClasses = 'mt-2 text-xs text-slate-500';
        $fieldPanelClasses = 'rounded-3xl border border-slate-100 bg-slate-50/80 p-5';
        $fieldInputClasses = 'w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400';
        $fieldReadonlyClasses = 'w-full cursor-not-allowed rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3.5 text-sm font-semibold text-slate-500';
        $fieldErrorClasses = ' border-red-300 bg-red-50/80 text-red-900 placeholder:text-red-300 focus:border-red-500 focus:ring-red-100';
        $canSellExhibitorArea = method_exists($event, 'canSellExhibitorArea')
            && (bool) ($event->exhibitor_show_publicly ?? true)
            && $event->canSellExhibitorArea();
        $exhibitorPrice = $canSellExhibitorArea ? $event->currentExhibitorPriceFor() : null;
        $exhibitorBatchLabel = $canSellExhibitorArea ? $event->currentExhibitorBatchLabelFor() : null;
        $exhibitorRemaining = $canSellExhibitorArea ? (int) $event->remaining_exhibitor_slots : 0;
    @endphp

    <div class="min-h-screen bg-slate-50 px-4 pb-20 pt-6 md:pt-28">
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
                                    <p class="text-3xl font-black text-gray-900" id="checkout-total" data-checkout-total>
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
                        <div class="mb-6 rounded-2xl border border-blue-100 bg-blue-50 p-6">
                            <h2 class="text-lg font-bold text-blue-900">Você já possui ingresso(s)</h2>
                            <p class="text-sm text-blue-700">Sua vaga já está garantida, mas você pode reservar ingressos adicionais abaixo se desejar.</p>
                        </div>
                    @endif

                    <div class="rounded-2xl bg-white p-6 shadow-lg md:p-8">
                            <h2 class="mb-6 text-2xl font-black text-gray-900">Finalizar Reserva</h2>

                            @if($canSellExhibitorArea)
                                <div class="mb-6 rounded-3xl border border-blue-100 bg-blue-50/70 p-4">
                                    <p class="mb-3 text-xs font-black uppercase tracking-[0.22em] text-blue-700">Escolha como deseja participar</p>
                                    <div class="grid gap-3 md:grid-cols-2">
                                        <div class="rounded-2xl border-2 border-blue-600 bg-white p-4 shadow-sm">
                                            <div class="flex items-start gap-3">
                                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-white">
                                                    <i class="fas fa-ticket-alt"></i>
                                                </span>
                                                <div>
                                                    <p class="text-sm font-black text-slate-950">Participar do evento</p>
                                                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">Ingresso normal para assistir, participar da palestra ou evento.</p>
                                                    <span class="mt-3 inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-700">Selecionado</span>
                                                </div>
                                            </div>
                                        </div>

                                        <a href="{{ route('events.exhibitor.show', $event) }}"
                                            class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-300 hover:bg-white hover:shadow-md">
                                            <div class="flex items-start gap-3">
                                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-blue-700 transition group-hover:bg-blue-600 group-hover:text-white">
                                                    <i class="fas fa-store"></i>
                                                </span>
                                                <div>
                                                    <p class="text-sm font-black text-slate-950">Comprar area de expositor</p>
                                                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-500">
                                                        {{ $exhibitorBatchLabel ?: 'Lote ativo' }} -
                                                        {{ 'R$ ' . number_format((float) $exhibitorPrice, 2, ',', '.') }}
                                                        @if($exhibitorRemaining > 0)
                                                            <span class="block text-blue-700">{{ $exhibitorRemaining }} area(s) disponiveis</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($isDemo)
                                <div class="mb-6 rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-yellow-800">
                                    <i class="fas fa-info-circle mr-2"></i> Evento de demonstração: configure um evento real no
                                    painel administrativo.
                                </div>
                            @endif

                            <form action="{{ route('events.reserve', $event) }}" method="POST" class="space-y-6"
                                data-no-ajax="true" data-no-spa="true">
                                @csrf

                                @if($isPaid && isset($activeGateways) && count($activeGateways) > 1)
                                {{-- Gateway Selector: exibido apenas quando há 2 gateways ativos --}}
                                <div id="gateway-selector" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <p class="mb-3 text-xs font-black uppercase tracking-widest text-slate-500">Forma de Pagamento</p>
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        @foreach($activeGateways as $gw)
                                        @php
                                            $gwProvider = $gw['provider'];
                                            $gwLabel = $gwProvider === 'mercadopago' ? 'Mercado Pago' : 'SumUp';
                                            $gwIcon = $gwProvider === 'mercadopago' ? 'fas fa-handshake' : 'fas fa-credit-card';
                                            $gwColor = $gwProvider === 'mercadopago' ? 'blue' : 'slate';
                                            $gwMethods = [];
                                            if ($gwProvider === 'mercadopago') {
                                                if (\App\Models\Setting::get('mercadopago_method_credit_card', 1)) $gwMethods[] = 'Cartão';
                                                if (\App\Models\Setting::get('mercadopago_method_pix', 1)) $gwMethods[] = 'PIX';
                                                if (\App\Models\Setting::get('mercadopago_method_ticket', 0)) $gwMethods[] = 'Boleto';
                                            } else {
                                                if (\App\Models\Setting::get('sumup_method_card', 1)) $gwMethods[] = 'Cartão';
                                                if (\App\Models\Setting::get('sumup_method_pix', 1)) $gwMethods[] = 'PIX';
                                            }
                                        @endphp
                                        <label for="gateway_{{ $gwProvider }}"
                                            class="gateway-option-card flex cursor-pointer items-center gap-4 rounded-2xl border-2 p-4 transition-all
                                                   {{ old('gateway') === $gwProvider ? 'border-blue-500 bg-blue-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300' }}">
                                            <input type="radio" id="gateway_{{ $gwProvider }}" name="gateway"
                                                value="{{ $gwProvider }}"
                                                class="sr-only gateway-radio"
                                                {{ old('gateway') === $gwProvider ? 'checked' : '' }}
                                                onchange="onGatewaySelect(this)">
                                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm border border-slate-100">
                                                <i class="{{ $gwIcon }} text-xl text-slate-600"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-black text-slate-800">{{ $gwLabel }}</p>
                                                <p class="text-xs text-slate-500">{{ implode(' · ', $gwMethods) }}</p>
                                            </div>
                                            <div class="gateway-check hidden h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-blue-600">
                                                <i class="fas fa-check text-[10px] text-white"></i>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                    @error('gateway')
                                        <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                @elseif($isPaid && isset($activeGateways) && count($activeGateways) === 1)
                                {{-- 1 gateway: campo hidden com o provider --}}
                                <input type="hidden" name="gateway" value="{{ $activeGateways[0]['provider'] }}">
                                @endif

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
                                        <label for="event_checkout_coupon" class="{{ $fieldLabelClasses }}">Cupom de desconto ou gratuidade</label>
                                        <input id="event_checkout_coupon" type="text" name="coupon_code"
                                            value="{{ old('coupon_code') }}" placeholder="Ex: CONVIDADO100"
                                            class="{{ $fieldInputClasses }}{{ $errors->has('coupon_code') ? $fieldErrorClasses : '' }}"
                                            {{ $isDemo ? 'disabled' : '' }}>
                                        @error('coupon_code')
                                            <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                        @enderror
                                        <p class="{{ $fieldHintClasses }}">Opcional. Se o cupom liberar gratuidade integral, a inscrição será confirmada sem pagamento.</p>
                                    </div>
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
                    </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Gateway selector
                function onGatewaySelect(radio) {
                    document.querySelectorAll('.gateway-option-card').forEach(function(card) {
                        const r = card.querySelector('.gateway-radio');
                        const check = card.querySelector('.gateway-check');
                        if (r && r.checked) {
                            card.classList.add('border-blue-500', 'bg-blue-50');
                            card.classList.remove('border-slate-200', 'bg-slate-50');
                            check && check.classList.remove('hidden');
                            check && check.classList.add('flex');
                        } else {
                            card.classList.remove('border-blue-500', 'bg-blue-50');
                            card.classList.add('border-slate-200', 'bg-slate-50');
                            check && check.classList.add('hidden');
                            check && check.classList.remove('flex');
                        }
                    });
                }
                window.onGatewaySelect = onGatewaySelect;

                // Init state for pre-selected gateway
                document.querySelectorAll('.gateway-radio').forEach(function(r) {
                    if (r.checked) onGatewaySelect(r);
                });

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

                    display.innerText = 'Mercado Pago';
                }

                radios.forEach((radio) => {
                    radio.addEventListener('change', updateGatewayDisplay);
                });

                updateGatewayDisplay();
            });
        </script>
    @endpush

@endsection
