@extends('layouts.app')

@section('title', 'Perfil Privado - UNN')

@section('content')
    <div class="max-w-4xl mx-auto py-20 px-4 text-center">
        <div class="bg-white rounded-3xl shadow-xl p-10 border border-slate-100">
            <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-400">
                <i class="fas fa-lock text-4xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 mb-4">Este perfil é privado</h2>
            <p class="text-slate-600 mb-8 max-w-md mx-auto">
                Você precisa estar conectado com <strong>{{ $user->name }}</strong> para ver as publicações e informações
                detalhadas.
            </p>

            <div class="flex justify-center gap-4">
                <a href="{{ route('social.feed') }}"
                    class="px-6 py-3 bg-slate-100 text-slate-700 rounded-full font-bold hover:bg-slate-200 transition">
                    Voltar ao Feed
                </a>

                @php
                    $pending = auth()->user()->hasPendingConnectionWith($user->id);
                @endphp

                @if($pending)
                    <button class="px-6 py-3 bg-blue-100 text-blue-600 rounded-full font-bold cursor-not-allowed">
                        Solicitação Pendente
                    </button>
                @else
                    <button onclick="requestConnection({{ $user->id }})"
                        class="px-8 py-3 bg-[#1F5EDB] text-white rounded-full font-bold hover:bg-blue-700 transition shadow-lg">
                        Conectar agora
                    </button>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function requestConnection(userId) {
                fetch(`/connect/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Enviado!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Ops!', data.message, 'warning');
                        }
                    });
            }
        </script>
    @endpush
@endsection