<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; background-color: #f8fbff; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background-color: #10b981; padding: 30px; text-align: center; color: white; }
        .content { padding: 30px; line-height: 1.6; color: #334155; }
        .info-box { background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; margin: 20px 0; }
        .button { display: inline-block; background-color: #1F5EDB; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
        .footer { background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pagamento Confirmado! ✅</h1>
        </div>
        <div class="content">
            <p>Olá, <strong>{{ $order->user->name }}</strong>!</p>
            <p>Recebemos a confirmação do seu pagamento. Sua assinatura agora está <strong>ATIVA</strong>.</p>
            
            <div class="info-box">
                <p><strong>Pedido:</strong> #{{ $order->id }}</p>
                <p><strong>Plano:</strong> {{ $order->items->first()->title }}</p>
                <p><strong>Valor:</strong> R$ {{ number_format($order->total_amount, 2, ',', '.') }}</p>
            </div>

            <p>Você já tem acesso total a todos os benefícios do seu plano.</p>
            
            <center>
                <a href="{{ route('portal') }}" class="button">Acessar Portal</a>
            </center>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} UNN - Todos os direitos reservados.
        </div>
    </div>
</body>
</html>
