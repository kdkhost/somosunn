@extends('layouts.app')

@section('title', 'Pagamento via Pix')

@section('content')
<div class="min-h-screen bg-slate-50 pt-8 md:pt-32 pb-20 px-4">
    <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-xl p-8 text-center">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6 text-blue-600">
            <i class="fa-brands fa-pix text-3xl"></i>
        </div>
        
        <h1 class="text-2xl font-black text-gray-900 mb-2">Pague com Pix</h1>
        <p class="text-gray-600 mb-8">Escaneie o QR Code abaixo ou copie o código para finalizar sua assinatura.</p>
        
        <div class="mb-8 p-4 border-2 border-dashed border-gray-200 rounded-xl inline-block bg-slate-50">
            <img src="data:image/png;base64,{{ $qr_code_base64 }}" alt="QR Code Pix" class="w-64 h-64 mx-auto">
        </div>

        <div class="max-w-md mx-auto mb-8">
            <label class="block text-sm font-bold text-gray-700 mb-2">Pix Copia e Cola</label>
            <div class="flex gap-2">
                <input type="text" value="{{ $qr_code }}" id="pixCode" readonly class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-600 focus:outline-none">
                <button onclick="copyPix()" class="bg-blue-600 text-white px-4 rounded-lg hover:bg-blue-700 transition" title="Copiar">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
            <p id="copyMsg" class="text-green-600 text-sm mt-2 hidden"><i class="fas fa-check"></i> Código copiado!</p>
        </div>

        <div class="bg-yellow-50 text-yellow-800 p-4 rounded-xl mb-8 text-sm">
            <i class="fas fa-clock mr-2"></i> Assim que você pagar, o sistema identificará automaticamente e liberará seu acesso em alguns segundos.
        </div>

        <a href="{{ route('subscription.success', $order->id) }}" class="inline-flex items-center gap-2 text-blue-600 font-bold hover:underline">
            Já fiz o pagamento <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>

<script>
function copyPix() {
    var copyText = document.getElementById("pixCode");
    copyText.select();
    copyText.setSelectionRange(0, 99999); 
    navigator.clipboard.writeText(copyText.value);
    
    document.getElementById("copyMsg").classList.remove('hidden');
    setTimeout(() => {
        document.getElementById("copyMsg").classList.add('hidden');
    }, 3000);
}

// Auto-check payment status (Simple polling)
setInterval(() => {
    // In a real implementation, call an API endpoint to check order status
    // fetch('/api/orders/{{ $order->id }}/status')...
}, 5000);
</script>
@endsection
