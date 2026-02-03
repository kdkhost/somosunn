@extends('layouts.app')

@section('title', 'Checkout - UNN')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <h1 class="text-2xl font-bold mb-4">Finalizar Compra</h1>
            <div class="bg-white p-6 rounded-lg shadow mb-4">
                <h3 class="font-bold text-lg mb-2">Resumo do Pedido</h3>
                @foreach($order->items as $item)
                    <div class="flex justify-between py-2 border-b">
                        <span>{{ $item->title }}</span>
                        <span>R$ {{ number_format($item->price, 2, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between py-2 font-bold text-xl mt-2">
                    <span>Total</span>
                    <span>R$ {{ number_format($order->total_amount, 2, ',', '.') }}</span>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                Pagamento processado via MercadoPago de forma segura.
            </div>
        </div>

        <div>
            <div id="cardPaymentBrick_container"></div>
        </div>
    </div>
</div>

<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
    const mp = new MercadoPago('{{ $publicKey }}', {
        locale: 'pt-BR'
    });
    const bricksBuilder = mp.bricks();
    const renderPaymentBrick = async (bricksBuilder) => {
        const settings = {
            initialization: {
                amount: {{ $order->total_amount }},
                preferenceId: '{{ $preferenceId }}',
            },
            customization: {
                paymentMethods: {
                    ticket: "all",
                    bankTransfer: "all", // PIX
                    creditCard: "all",
                    debitCard: "all",
                    mercadoPago: "all",
                },
                visual: {
                    style: {
                        theme: 'bootstrap',
                    }
                }
            },
            callbacks: {
                onReady: () => {
                   // loaded
                },
                onSubmit: ({ selectedPaymentMethod, formData }) => {
                    // processed by preference, usually callbacks handles redirect
                },
                onError: (error) => {
                    console.error(error);
                },
            },
        };
        window.paymentBrickController = await bricksBuilder.create(
            'payment',
            'cardPaymentBrick_container',
            settings
        );
    };
    renderPaymentBrick(bricksBuilder);
</script>
@endsection
