@extends('layouts.app')

@section('title', 'Checkout com SumUp')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/sumup-styles.css') }}">
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Finalizar Compra</h1>
        
        {{-- Resumo do pedido --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Resumo do Pedido</h2>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>{{ $product->name ?? 'Produto' }}</span>
                    <span>R$ {{ number_format($amount, 2, ',', '.') }}</span>
                </div>
                @if(isset($fees) && $fees['pass_fee_to_customer'])
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Taxa de processamento</span>
                    <span>R$ {{ number_format($fees['total_fee'], 2, ',', '.') }}</span>
                </div>
                @endif
                <div class="border-t pt-2 flex justify-between font-semibold">
                    <span>Total</span>
                    <span>R$ {{ number_format($totalAmount, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Seletor de Gateway --}}
        @if(count($availableGateways) > 1)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Escolha a forma de pagamento</h2>
            <div class="gateway-selector">
                @foreach($availableGateways as $gatewayKey => $gateway)
                <div class="gateway-option {{ $loop->first ? 'selected' : '' }}" 
                     data-gateway="{{ $gatewayKey }}">
                    <div class="gateway-logo">
                        @if($gatewayKey === 'mercadopago')
                            <i class="fas fa-handshake"></i>
                        @elseif($gatewayKey === 'sumup')
                            <i class="fas fa-credit-card"></i>
                        @endif
                    </div>
                    <div class="gateway-name">{{ $gateway['name'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Container para opções de pagamento --}}
        <div id="payment-container">
            {{-- Será preenchido via JavaScript --}}
        </div>

        {{-- Informações de segurança --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
            <div class="flex items-start">
                <i class="fas fa-shield-alt text-blue-500 mt-1 mr-3"></i>
                <div>
                    <h3 class="font-semibold text-blue-900">Pagamento Seguro</h3>
                    <p class="text-sm text-blue-700 mt-1">
                        Seus dados são protegidos com criptografia SSL e não armazenamos informações do cartão.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/sumup-integration.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuração dos gateways disponíveis
    const gateways = @json($availableGateways);
    const sumupConfig = @json($sumup ?? []);
    const amount = {{ $amount }};
    const productType = '{{ $productType ?? 'course' }}';
    
    // Inicializar SumUp se disponível
    let sumupInstance = null;
    if (sumupConfig.available) {
        sumupInstance = new SumUpIntegration({
            apiBaseUrl: '/api/v1/sumup',
            ...sumupConfig.config
        });
        
        sumupInstance.init().then(initialized => {
            if (initialized) {
                console.log('SumUp inicializado');
            }
        });
    }

    // Gerenciar seleção de gateway
    const gatewayOptions = document.querySelectorAll('.gateway-option');
    const paymentContainer = document.getElementById('payment-container');
    let currentGateway = '{{ $preferredGateway ?? 'mercadopago' }}';

    function switchGateway(gateway) {
        currentGateway = gateway;
        
        // Atualizar seleção visual
        gatewayOptions.forEach(option => {
            option.classList.toggle('selected', option.dataset.gateway === gateway);
        });

        // Renderizar opções de pagamento
        renderPaymentOptions(gateway);
    }

    function renderPaymentOptions(gateway) {
        paymentContainer.innerHTML = '';

        if (gateway === 'sumup' && sumupInstance && sumupConfig.available) {
            renderSumUpOptions();
        } else if (gateway === 'mercadopago') {
            renderMercadoPagoOptions();
        }
    }

    function renderSumUpOptions() {
        const config = {
            methods: sumupConfig.config.methods || ['card', 'pix'],
            installment_options: sumupConfig.config.installment_options || [],
            paymentData: {
                amount: amount,
                description: '{{ $product->name ?? "Produto" }}',
                reference: 'order_' + Date.now(),
                return_url: '{{ route("checkout.success") }}',
                customer_email: '{{ auth()->user()->email ?? "" }}',
                product_type: productType,
                user_type: '{{ $userType ?? "member" }}'
            }
        };

        sumupInstance.renderPaymentOptions(paymentContainer, config);

        // Mostrar informações de taxas se aplicável
        if (sumupConfig.config.fees && sumupConfig.config.fees.pass_fee_to_customer) {
            const feesInfo = document.createElement('div');
            feesInfo.className = 'fees-info';
            feesInfo.innerHTML = `
                <div class="fee-item">
                    <span>Subtotal:</span>
                    <span>R$ ${amount.toFixed(2).replace('.', ',')}</span>
                </div>
                <div class="fee-item">
                    <span>Taxa de processamento:</span>
                    <span>R$ ${sumupConfig.config.fees.total_fee.toFixed(2).replace('.', ',')}</span>
                </div>
                <div class="fee-item">
                    <span>Total:</span>
                    <span>R$ ${sumupConfig.config.fees.gross_amount.toFixed(2).replace('.', ',')}</span>
                </div>
            `;
            paymentContainer.appendChild(feesInfo);
        }
    }

    function renderMercadoPagoOptions() {
        // Implementar opções do MercadoPago
        paymentContainer.innerHTML = `
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Pagar com MercadoPago</h3>
                <p class="text-gray-600 mb-4">Você será redirecionado para o MercadoPago para finalizar o pagamento.</p>
                <form action="{{ route('checkout.process', $product ?? '') }}" method="POST">
                    @csrf
                    <input type="hidden" name="gateway_provider" value="mercadopago">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                        Continuar com MercadoPago
                    </button>
                </form>
            </div>
        `;
    }

    // Event listeners para seleção de gateway
    gatewayOptions.forEach(option => {
        option.addEventListener('click', () => {
            switchGateway(option.dataset.gateway);
        });
    });

    // Inicializar com gateway padrão
    switchGateway(currentGateway);
});
</script>
@endpush