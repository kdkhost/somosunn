@extends('layouts.app')

@section('title', 'Pagamento aprovado')

@section('content')
<div class="min-h-screen bg-slate-50 pt-28 pb-20 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <div class="text-green-600 text-5xl mb-4"><i class="fas fa-circle-check"></i></div>
            <h1 class="text-2xl font-black text-gray-900 mb-2">Pagamento aprovado</h1>
            <p class="text-gray-600 mb-6">Obrigado! Seu pagamento foi confirmado. Caso o acesso ao curso não libere imediatamente, aguarde alguns minutos.</p>
            <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 btn-primary px-6 py-3 rounded-xl font-bold">
                <i class="fas fa-book-open"></i> Ver cursos
            </a>
        </div>
    </div>
</div>
@endsection

