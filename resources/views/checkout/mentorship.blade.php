@extends('layouts.app')

@section('title', 'Checkout - ' . ($mentorship->title ?? 'Mentoria'))

@section('content')
    @php
        $selectedGateway = old('gateway_provider');
        if (!$selectedGateway) {
            $selectedGateway = ($preferredGateway ?? null) ?: (($mpEnabled ?? true) ? 'mercadopago' : 'pagseguro');
        }

        $selectedGatewaySummary = $selectedGateway === 'pagseguro'
            ? 'Pagamento via PagSeguro.'
            : 'Pagamento via Mercado Pago.';
    @endphp
    <div class="min-h-screen bg-slate-50 pt-28 pb-20 px-4">
        <div class="max-w-4xl mx-auto">
            <a href="{{ route('mentorships.show', $mentorship) }}"
                class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-700 mb-6">
                <i class="fas fa-arrow-left"></i> Voltar para a mentoria
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
                        <p class="font-bold text-gray-900">{{ $mentorship->title }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-user-circle mr-1"></i>
                            {{ optional($mentorship->mentor)->name ?? 'Mentor' }}
                        </p>

                        <div class="border-t border-gray-100 mt-6 pt-6">
                            @php
                                $regularTotal = (float) ($mentorship->price ?? 0);
                                $effectiveTotal = (float) ($mentorship->effective_price ?? $regularTotal);
                                $flashActive = method_exists($mentorship, 'isFlashSaleActive') ? (bool) $mentorship->isFlashSaleActive() : false;
                            @endphp
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

                        <form action="{{ route('mentorships.checkout.process', $mentorship) }}" method="POST"
                            class="space-y-6">
                            @csrf

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cupom de desconto
                                    (opcional)</label>
                                <input type="text" name="coupon_code" value="{{ old('coupon_code') }}"
                                    placeholder="Ex: BLACKFRIDAY26"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-2">Se tiver um cupom, aplique antes de continuar.</p>
                            </div>

                            <div class="pt-4 border-t border-gray-100">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Forma de Pagamento</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @if($mpEnabled ?? true)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="gateway_provider" value="mercadopago" class="peer sr-only"
                                                data-gateway-summary="Pagamento via Mercado Pago."
                                                {{ $selectedGateway === 'mercadopago' ? 'checked' : '' }}>
                                            <div
                                                class="p-4 rounded-xl border-2 border-gray-200 hover:border-blue-500 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition-all">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
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

                                    @if($psEnabled ?? false)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="gateway_provider" value="pagseguro" class="peer sr-only"
                                                data-gateway-summary="Pagamento via PagSeguro."
                                                {{ $selectedGateway === 'pagseguro' ? 'checked' : '' }}>
                                            <div
                                                class="p-4 rounded-xl border-2 border-gray-200 hover:border-green-500 peer-checked:border-green-600 peer-checked:bg-green-50 transition-all">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
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

                            <button type="submit"
                                class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2">
                                <i class="fas fa-lock"></i> Continuar para pagamento
                            </button>

                            <p class="text-xs text-gray-500 text-center">
                                Ao continuar, você concorda com os termos de compra e políticas da mentoria.
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
