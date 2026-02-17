@extends('panel.layouts.app')

@section('title', 'Mensagens - UNN')


{{-- Remove breadcrumbs/admin context para painel --}}

@section('panel_content')
    @php
        $routeNamePrefix = $routeNamePrefix ?? 'chat';
        // $isAdminContext = ($extends ?? 'layouts.app') === 'admin.layouts.app';
        $isAdminContext = false;
    @endphp

    <div
        class="{{ $isAdminContext ? 'px-0 py-2' : 'max-w-6xl mx-auto px-0 sm:px-4 py-2 sm:py-6' }} h-[calc(100vh-120px)] sm:h-[calc(100vh-160px)] md:h-[calc(100vh-180px)] min-h-[400px]">
        <div
            class="bg-white dark:bg-slate-900 rounded-none sm:rounded-lg shadow-xl overflow-hidden flex h-full border-0 sm:border border-gray-200 dark:border-slate-800 transition-colors duration-300">
            <!-- Sidebar Conversations -->
            <div class="w-full md:w-1/3 border-r border-gray-200 dark:border-slate-800 flex flex-col">
                <div
                    class="p-3 sm:p-4 border-b border-gray-200 dark:border-slate-800 bg-gray-50 dark:bg-slate-950 transition-colors">
                    <h2 class="font-bold text-lg sm:text-xl text-gray-800 dark:text-white">Mensagens</h2>
                </div>
                <div class="flex-1 overflow-y-auto" id="conversations-list">
                    @foreach($conversations as $conv)
                        @php
                            $otherUser = $conv->users->where('id', '!=', Auth::id())->first() ?? $conv->users->first();
                            $otherUserPhoto = $otherUser?->profile_photo_url ?? asset('img/default-user.svg');
                            $otherUserName = $otherUser?->name ?? ($conv->title ?? 'Conversa');
                        @endphp
                        <a href="{{ route($routeNamePrefix . '.show', $conv->id) }}" data-conversation-id="{{ $conv->id }}"
                            class="block p-3 sm:p-4 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition border-b border-gray-50 dark:border-slate-800/50">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <img src="{{ $otherUserPhoto }}" alt="{{ $otherUserName }}"
                                    class="w-10 h-10 rounded-full object-cover flex-shrink-0"
                                    onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}'">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        {{ $otherUserName }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-slate-400 truncate">Ver conversa...</p>
                                </div>
                                @if($conv->unread_count > 0)
                                    <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                                        {{ $conv->unread_count }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                    @if($conversations->isEmpty())
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            Nenhuma conversa iniciada.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Chat Area -->
            <div class="hidden md:flex flex-1 flex-col bg-slate-50 dark:bg-slate-950 transition-colors">
                <div class="flex-1 flex items-center justify-center text-gray-400 dark:text-slate-600 flex-col gap-4">
                    <i class="fas fa-comments text-6xl opacity-20"></i>
                    <p class="font-medium">Selecione uma conversa para começar</p>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            setInterval(() => {
                fetch('{{ route($routeNamePrefix . ".list") }}')
                    .then(r => r.json())
                    .then(conversations => {
                        conversations.forEach(conv => {
                            const link = document.querySelector(`[data-conversation-id="${conv.id}"]`);
                            const badgeContainer = link ? link.querySelector('.flex.items-center') : null;
                            if (badgeContainer) {
                                let badge = badgeContainer.querySelector('span.bg-blue-600');
                                if (conv.unread_count > 0) {
                                    if (!badge) {
                                        badge = document.createElement('span');
                                        badge.className = 'bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded-full';
                                        badgeContainer.appendChild(badge);
                                    }
                                    badge.textContent = conv.unread_count;
                                } else if (badge) {
                                    badge.remove();
                                }
                            }
                        });
                    });
            }, 5000);
        </script>
    @endpush
@endsection