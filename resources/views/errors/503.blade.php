@extends('layouts.app')

@section('title','503 — Serviço indisponível')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-6xl font-bold">503</h1>
        <p class="text-xl mt-4">Serviço temporariamente indisponível. Volte mais tarde.</p>
        <a href="{{ route('home') }}" class="btn-primary text-white px-4 py-2 rounded mt-6 inline-block">Voltar ao site</a>
    </div>
</div>
@endsection