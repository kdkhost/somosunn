@extends('layouts.app')

@section('title', 'Checkout - ' . $course->title)

@section('content')
    @php
        $selectedGateway = old('gateway_provider');
        $regularTotal = (float) ($course->price ?? 0);
        $effectiveTotal = (float) ($course->effective_price ?? $regularTotal);
        $flashActive = method_exists($course, 'isFlashSaleActive') ? (bool) $course->isFlashSaleActive() : false;
        $isFreeCheckout = $effectiveTotal <= 0;

        if (!$selectedGateway) {
            $selectedGateway = ($preferredGateway ?? null) ?: 'mercadopago';
        }

        $selectedGatewaySummary = $isFreeCheckout
            ? 'Liberacao imediata sem pagamento.'
            : 'Pagamento via Mercado Pago.';
    @endphp
    <div class="min-h-screen bg-slate-50 pt-28 pb-20 px-4">
        <div class="max-w-4xl mx-auto">
            <a href="{{ route('courses.show', $course->slug ?: $course->id) }}"
                class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-700 mb-6">
                <i class="fas fa-arrow-left"></i> Voltar para o curso
            </a>

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6">
                    <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('error') }}
                </div>
            @endif

            <div class="grid md:grid-cols-3 gap-8">
                <div class="md:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-28">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Resumo</h3>
                        <p class="font-bold text-gray-900">{{ $course->title }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-user-circle mr-1"></i>
                            {{ $course->author_name ?? optional($course->creator)->name ?? 'Instrutor' }}
                        </p>

                        <div class="border-t border-gray-100 mt-6 pt-6">
                            <p class="text-sm text-gray-500">Total</p>
                            <div class="flex items-end gap-3">
                                <p class="text-3xl font-black text-gray-900">
                                    {{ $effectiveTotal > 0 ? 'R$ ' . number_format($effectiveTotal, 2, ',', '.') : 'Gratuito' }}
                                </p>
                                @if($flashActive && $regularTotal > 0 && $effectiveTotal < $regularTotal)
                                    <p class="text-sm text-gray-400 line-through mb-1">
                                        {{ 'R$ ' . number_format($regularTotal, 2, ',', '.') }}
                                    </p>
                                @endif
                            </div>
                            <p id="gateway-summary-text" class="text-xs text-gray-500 mt-2">{{ $selectedGatewaySummary }}</p>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                        <h2 class="text-2xl font-black text-gray-900 mb-6">Finalizar compra</h2>

                        <form action="{{ route('checkout.process', $course) }}" method="POST" class="space-y-6">
                            @csrf

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cupom de desconto
                                    (opcional)</label>
                                <input type="text" name="coupon_code" value="{{ old('coupon_code') }}"
                                    placeholder="Ex: BLACKFRIDAY26"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-2">Se tiver um cupom, aplique antes de continuar.</p>
                            </div>

                            @if(!$isFreeCheckout)
                                <div class="pt-4 border-t border-gray-100">
                                    @php
                                        // Ler métodos MP e SumUp ativos do banco
                                        $mpMethodsActive = [];
                                        if ((int) \App\Models\Setting::get('mercadopago_method_credit_card', 1) === 1) $mpMethodsActive[] = 'Cartão';
                                        if ((int) \App\Models\Setting::get('mercadopago_method_debit_card', 0) === 1) $mpMethodsActive[] = 'Débito';
                                        if ((int) \App\Models\Setting::get('mercadopago_method_pix', 1) === 1) $mpMethodsActive[] = 'Pix';
                                        if ((int) \App\Models\Setting::get('mercadopago_method_ticket', 0) === 1) $mpMethodsActive[] = 'Boleto';

                                        $sumupMethodsActive = [];
                                        if ((int) \App\Models\Setting::get('sumup_method_card', 1) === 1) $sumupMethodsActive[] = 'Cartão';
                                        if ((int) \App\Models\Setting::get('sumup_method_pix', 1) === 1) $sumupMethodsActive[] = 'Pix';

                                        $mpActive    = ($mpEnabled ?? false) && !empty($mpMethodsActive);
                                        $sumupActive = ($sumup['available'] ?? false) && !empty($sumupMethodsActive);
                                        $activeCount = ($mpActive ? 1 : 0) + ($sumupActive ? 1 : 0);
                                        $singleGateway = null;
                                        if ($activeCount === 1) {
                                            $singleGateway = $mpActive ? 'mercadopago' : 'sumup';
                                        }
                                    @endphp

                                    @if($activeCount >= 2)
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Forma de Pagamento</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            @if($mpActive)
                                                <label class="cursor-pointer block">
                                                    <input type="radio" name="gateway_provider" value="mercadopago" class="peer sr-only"
                                                        data-gateway-summary="Pagamento via Mercado Pago."
                                                        {{ $selectedGateway === 'mercadopago' ? 'checked' : '' }}>
                                                    <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-blue-600 peer-checked:ring-2 peer-checked:ring-blue-100 transition-all">
                                                        <div class="flex items-start gap-3">
                                                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0">
                                                                <i class="fas fa-hand-holding-dollar"></i>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="font-bold text-gray-900 text-sm">Mercado Pago</p>
                                                                <div class="flex flex-wrap gap-1 mt-1">
                                                                    @foreach($mpMethodsActive as $method)
                                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-gray-100 text-[10px] font-bold text-gray-600">{{ $method }}</span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            @endif

                                            @if($sumupActive)
                                                <label class="cursor-pointer block">
                                                    <input type="radio" name="gateway_provider" value="sumup" class="peer sr-only"
                                                        data-gateway-summary="Pagamento via SumUp."
                                                        {{ $selectedGateway === 'sumup' ? 'checked' : '' }}>
                                                    <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-slate-900 peer-checked:ring-2 peer-checked:ring-slate-200 transition-all">
                                                        <div class="flex items-start gap-3">
                                                            <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center flex-shrink-0">
                                                                <i class="fas fa-credit-card"></i>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="font-bold text-gray-900 text-sm">SumUp</p>
                                                                <div class="flex flex-wrap gap-1 mt-1">
                                                                    @foreach($sumupMethodsActive as $method)
                                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-gray-100 text-[10px] font-bold text-gray-600">{{ $method }}</span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            @endif
                                        </div>
                                    @elseif($activeCount === 1)
                                        {{-- Apenas um gateway ativo: input hidden, sem seletor --}}
                                        <input type="hidden" name="gateway_provider" value="{{ $singleGateway }}">
                                    @else
                                        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                                            <i class="fas fa-triangle-exclamation mr-2"></i>
                                            Nenhum gateway de pagamento disponivel.
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <button type="submit"
                                class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2">
                                <i class="fas {{ $isFreeCheckout ? 'fa-check-circle' : 'fa-lock' }}"></i>
                                {{ $isFreeCheckout ? 'Concluir pedido gratuito' : 'Continuar para pagamento' }}
                            </button>

                            <p class="text-xs text-gray-500 text-center">
                                Ao continuar, você concorda com os termos de compra e políticas do curso.
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const summaryText = document.getElementById('gateway-summary-text');
            const gatewayInputs = document.querySelectorAll('input[name="gateway_provider"]');

            if (!summaryText || gatewayInputs.length === 0) {
                return;
            }

            const syncGatewaySummary = () => {
                const selectedGateway = document.querySelector('input[name="gateway_provider"]:checked');
                if (!selectedGateway) {
                    return;
                }

                summaryText.textContent = selectedGateway.dataset.gatewaySummary || '';
            };

            gatewayInputs.forEach((input) => input.addEventListener('change', syncGatewaySummary));
            syncGatewaySummary();
        });
    </script>
@endsection
