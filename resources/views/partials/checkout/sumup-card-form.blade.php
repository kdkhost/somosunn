<div id="sumup-checkout-container">
    <div class="space-y-4">
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mx-auto mb-4"></div>
            <p class="text-slate-600">Carregando formulário de pagamento SumUp...</p>
        </div>
    </div>
</div>

<script src="https://gateway.sumup.com/gateway/ecom/card/v2/sdk.js"></script>
<script>
    (function() {
        console.log('=== SumUp Checkout Initialization ===');
        
        const checkoutId = '{{ $checkoutId ?? '' }}';
        const merchantCode = '{{ $publicKey ?? '' }}';
        const orderId = '{{ $order->id ?? '' }}';

        console.log('Checkout ID:', checkoutId);
        console.log('Merchant Code:', merchantCode);
        console.log('Order ID:', orderId);

        if (!checkoutId) {
            console.error('Checkout ID não encontrado!');
            document.getElementById('sumup-checkout-container').innerHTML = 
                '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>Erro: ID do checkout não encontrado.</p></div>';
            return;
        }

        if (typeof SumUpCard === 'undefined') {
            console.error('SumUpCard SDK não carregado!');
            document.getElementById('sumup-checkout-container').innerHTML = 
                '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>Erro: SDK do SumUp não carregou. Verifique sua conexão.</p></div>';
            return;
        }

        console.log('Inicializando SumUp Card Widget...');

        try {
            // Inicializar SumUp Card Widget
            const sumupCard = SumUpCard.mount({
                checkoutId: checkoutId,
                onResponse: function(type, body) {
                    console.log('SumUp Response:', type, body);
                    
                    if (type === 'success') {
                        // Pagamento aprovado
                        toastr.success('Pagamento aprovado com sucesso!');
                        
                        // Redirecionar para página de sucesso
                        setTimeout(function() {
                            window.location.href = '{{ route("events.payment.success", $order->id ?? 0) }}';
                        }, 1500);
                    } else if (type === 'error') {
                        // Erro no pagamento
                        console.error('SumUp Payment Error:', body);
                        toastr.error(body.message || 'Erro ao processar pagamento. Tente novamente.');
                    } else if (type === 'pending') {
                        // Pagamento pendente
                        toastr.info('Pagamento pendente de confirmação.');
                        
                        setTimeout(function() {
                            window.location.href = '{{ route("events.payment.pending", $order->id ?? 0) }}';
                        }, 1500);
                    }
                },
                onLoad: function() {
                    console.log('SumUp Card Widget loaded successfully!');
                },
                onError: function(error) {
                    console.error('SumUp Widget Error:', error);
                    document.getElementById('sumup-checkout-container').innerHTML = 
                        '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>Erro ao carregar formulário de pagamento. Tente novamente.</p><p class="text-sm mt-2">' + (error.message || JSON.stringify(error)) + '</p></div>';
                    toastr.error('Erro ao carregar formulário de pagamento SumUp.');
                }
            });

            console.log('Rendering SumUp Card Widget...');
            // Renderizar o widget no container
            sumupCard.render('#sumup-checkout-container');
            console.log('SumUp Card Widget render called');
        } catch (error) {
            console.error('Exception during SumUp initialization:', error);
            document.getElementById('sumup-checkout-container').innerHTML = 
                '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>Erro ao inicializar SumUp: ' + error.message + '</p></div>';
        }
    })();
</script>

<style>
    #sumup-checkout-container {
        min-height: 400px;
    }
    
    #sumup-checkout-container iframe {
        width: 100% !important;
        border: none !important;
        min-height: 400px !important;
    }
</style>
