@extends('layouts.app')

@section('title', 'Configurar Pagamentos - UNN')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Configuração de Pagamento (Recebimento)</h1>
    <p class="mb-4 text-gray-600">Configure suas credenciais do MercadoPago para vender seus cursos na plataforma.</p>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('settings.payment.update') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Public Key</label>
                <input type="text" name="public_key" value="{{ $gateway->public_key }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                <p class="text-xs text-gray-500 mt-1">Disponível no painel de desenvolvedores do MercadoPago.</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Access Token</label>
                <input type="password" name="access_token" value="{{ $gateway->access_token }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                <p class="text-xs text-gray-500 mt-1">Token de produção.</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">Salvar Credenciais</button>
            </div>
        </form>
    </div>
</div>
@endsection
