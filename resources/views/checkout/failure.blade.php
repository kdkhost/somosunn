@extends('layouts.app')

@section('title', 'Pagamento não aprovado')

@section('content')
<div class="min-h-screen bg-slate-50 pt-28 pb-20 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <div class="text-red-600 text-5xl mb-4"><i class="fas fa-circle-xmark"></i></div>
            <h1 class="text-2xl font-black text-gray-900 mb-2">Pagamento não aprovado</h1>
            <p class="text-gray-600 mb-6">Não foi possível confirmar o pagamento. Você pode tentar novamente.</p>
            <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 btn-primary px-6 py-3 rounded-xl font-bold">
                <i class="fas fa-rotate-right"></i> Voltar aos cursos
            </a>
        </div>
    </div>
</div>
@endsection

