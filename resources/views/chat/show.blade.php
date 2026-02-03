@extends('layouts.app')

@section('title', 'Chat - UNN')

@section('content')
<div class="max-w-6xl mx-auto px-0 md:px-4 py-6 h-[calc(100vh-80px)]">
    <div class="bg-white rounded-lg shadow-xl overflow-hidden flex h-full border border-gray-200">
         <!-- Sidebar (hidden on mobile when chat open, visible on md) -->
        <div class="hidden md:flex w-1/3 border-r border-gray-200 flex-col bg-white">
             <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h2 class="font-bold text-xl text-gray-800">Mensagens</h2>
                <a href="{{ route('chat.index') }}" class="text-xs text-blue-600">Voltar</a>
            </div>
            <!-- List logic same as index, abbreviated -->
            <div class="flex-1 overflow-y-auto p-4 text-center text-gray-400 text-sm">
                (Lista de contatos)
            </div>
        </div>

        <!-- Chat Area -->
        <div class="w-full flex-1 flex flex-col bg-slate-50 relative">
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 bg-white flex items-center justify-between shadow-sm z-10">
                <div class="flex items-center gap-3">
                    <a href="{{ route('chat.index') }}" class="md:hidden text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold">
                        {{ substr($conversation->title ?? 'Chat', 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">{{ $conversation->title ?? 'Conversa' }}</h4>
                        <span class="flex items-center gap-1 text-xs text-green-500"><span class="w-2 h-2 bg-green-500 rounded-full"></span> Online</span>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container">
                @foreach($messages->reverse() as $msg)
                    <div class="flex {{ $msg->user_id == Auth::id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[75%] rounded-2xl px-4 py-2 shadow-sm text-sm {{ $msg->user_id == Auth::id() ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white text-gray-800 rounded-bl-none border border-gray-100' }}">
                            <p>{{ $msg->body }}</p>
                            <div class="text-[10px] mt-1 opacity-70 {{ $msg->user_id == Auth::id() ? 'text-blue-100' : 'text-gray-400' }} text-right">
                                {{ $msg->created_at->format('H:i') }}
                                @if($msg->user_id == Auth::id()) 
                                    <i class="fas fa-check-double ml-1"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Input -->
            <div class="p-4 bg-white border-t border-gray-200">
                <form id="chat-form" class="flex items-center gap-2">
                    <button type="button" class="text-gray-400 hover:text-blue-600 p-2"><i class="fas fa-paperclip"></i></button>
                    <input type="text" id="message-input" class="flex-1 border-gray-200 rounded-full px-4 py-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="Digite sua mensagem...">
                    <button type="submit" class="bg-blue-600 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-blue-700 transition shadow">
                        <i class="fas fa-paper-plane text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('chat-form').addEventListener('submit', function(e){
        e.preventDefault();
        const input = document.getElementById('message-input');
        const body = input.value;
        if(!body.trim()) return;
        
        // Optimistic UI
        const container = document.getElementById('messages-container');
        const div = document.createElement('div');
        div.className = 'flex justify-end';
        div.innerHTML = `<div class="max-w-[75%] rounded-2xl px-4 py-2 shadow-sm text-sm bg-blue-600 text-white rounded-br-none"><p>${body}</p></div>`;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
        input.value = '';

        // Ajax send
        fetch('{{ route("chat.message.store", $conversation->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ body: body })
        });
    });
    
    // Scroll to bottom
    const c = document.getElementById('messages-container');
    if(c) c.scrollTop = c.scrollHeight;
</script>
@endsection
