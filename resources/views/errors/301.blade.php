@extends('layouts.app')

@section('title','301 — Movido permanentemente')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-6xl font-bold">301</h1>
        <p class="text-xl mt-4">Conteúdo movido permanentemente.</p>
        <a href="{{ route('home') }}" class="btn-primary text-white px-4 py-2 rounded mt-6 inline-block">Ir para o site</a>
    </div>
</div>
@endsection