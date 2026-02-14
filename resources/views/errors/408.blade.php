@extends('layouts.app')

@section('title','408 — Tempo esgotado')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-6xl font-bold">408</h1>
        <p class="text-xl mt-4">Tempo de requisição esgotado.</p>
        <a href="{{ route('home') }}" class="btn-primary text-white px-4 py-2 rounded mt-6 inline-block">Voltar ao site</a>
    </div>
</div>
@endsection