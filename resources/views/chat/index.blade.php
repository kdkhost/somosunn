@extends('layouts.app')

@section('title', 'Mensagens - UNN')

@section('content')
<div class="max-w-6xl mx-auto px-0 md:px-4 py-6 h-[calc(100vh-80px)]">
    <div class="bg-white rounded-lg shadow-xl overflow-hidden flex h-full border border-gray-200">
        <!-- Sidebar Conversations -->
        <div class="w-full md:w-1/3 border-r border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h2 class="font-bold text-xl text-gray-800">Mensagens</h2>
            </div>
            <div class="flex-1 overflow-y-auto" id="conversations-list">
                @foreach($conversations as $conv)
                    <a href="#" class="block p-4 hover:bg-blue-50 transition border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold">
                                {{ substr($conv->title ?? 'Chat', 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $conv->title ?? 'Conversa #' . $conv->id }}</h4>
                                <p class="text-xs text-gray-500 truncate">Ver conversa...</p>
                            </div>
                        </div>
                    </a>
                @endforeach
                @if($conversations->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        Nenhuma conversa iniciada.
                    </div>
                @endif
            </div>
        </div>

        <!-- Chat Area -->
        <div class="hidden md:flex flex-1 flex-col bg-slate-50">
            <div class="flex-1 flex items-center justify-center text-gray-400 flex-col gap-4">
                <i class="fas fa-comments text-6xl"></i>
                <p>Selecione uma conversa para começar</p>
            </div>
        </div>
    </div>
</div>
@endsection
