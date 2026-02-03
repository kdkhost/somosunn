@extends('layouts.app')

@section('title', $user->name . ' - Perfil UNN')

@section('content')
<div class="bg-gray-100 min-h-screen">
    <!-- Cover & Info -->
    <div class="bg-white shadow">
        <div class="h-48 bg-gradient-to-r from-blue-500 to-indigo-600 w-full"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative pb-6">
            <div class="flex flex-col md:flex-row items-end -mt-12 mb-4 gap-6">
                <div class="w-32 h-32 bg-white rounded-full p-1 shadow-lg z-10">
                    <div class="w-full h-full bg-gray-200 rounded-full flex items-center justify-center text-4xl text-gray-500 font-bold overflow-hidden">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                </div>
                <div class="flex-1 pb-2">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <p class="text-gray-600">Membro desde {{ $user->created_at->format('M Y') }}</p>
                </div>
                <div class="flex gap-3 pb-4">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded-full font-medium hover:bg-blue-700 transition shadow">
                        <i class="fas fa-user-plus mr-1"></i> Conectar
                    </button>
                    <a href="{{ route('chat.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-full font-medium hover:bg-gray-200 transition">
                        <i class="fas fa-comment"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold text-gray-900 mb-4">Sobre</h3>
                <p class="text-gray-600 text-sm">Sem descrição.</p>
                
                <hr class="my-4">
                
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-map-marker-alt w-5 text-gray-400"></i> Localização não informada
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-briefcase w-5 text-gray-400"></i> Cargo não informado
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="md:col-span-2 space-y-6">
            <h3 class="font-bold text-xl text-gray-800">Publicações</h3>
            
            @forelse($posts as $post)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-3">
                            <div class="bg-gray-200 text-gray-600 rounded-full w-10 h-10 flex items-center justify-center font-bold">
                                {{ substr($post->user->name, 0, 1) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $post->user->name }}</h4>
                                <p class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="prose max-w-none text-gray-800">
                        {!! nl2br(e($post->content)) !!}
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-gray-500 bg-white rounded-lg shadow">
                    <p>Nenhuma publicação ainda.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
