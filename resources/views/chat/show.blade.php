@extends($extends ?? 'layouts.app')

@section('title', 'Chat - UNN')

@if(($extends ?? 'layouts.app') === 'admin.layouts.app')
@section('page_title', 'Chat')
@section('breadcrumb_items')
    <li class="breadcrumb-item active">Chat</li>
@endsection
@endif

@section('content')
    @php
        $routeNamePrefix = $routeNamePrefix ?? 'chat';
        $isAdminContext = ($extends ?? 'layouts.app') === 'admin.layouts.app';
    @endphp

    <div
        class="{{ $isAdminContext ? 'px-0 py-2' : 'max-w-6xl mx-auto px-0 sm:px-4 py-2 sm:py-6' }} h-[calc(100vh-120px)] sm:h-[calc(100vh-160px)] md:h-[calc(100vh-180px)] min-h-[400px]">
        <div
            class="bg-white rounded-none sm:rounded-lg shadow-xl overflow-hidden flex h-full border-0 sm:border border-gray-200">
            <!-- Sidebar (hidden on mobile when chat open, visible on md) -->
            <div class="hidden md:flex w-1/3 border-r border-gray-200 flex-col bg-white">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h2 class="font-bold text-xl text-gray-800">Mensagens</h2>
                    <a href="{{ route($routeNamePrefix . '.index') }}" class="text-xs text-blue-600">Voltar</a>
                </div>
                <!-- List logic same as index, abbreviated -->
                <div class="flex-1 overflow-y-auto" id="conversations-list">
                    @foreach($conversations as $conv)
                        @php
                            $otherUser = $conv->users->where('id', '!=', Auth::id())->first() ?? $conv->users->first();
                            $otherUserPhoto = $otherUser?->profile_photo_url ?? asset('img/default-user.svg');
                            $otherUserName = $otherUser?->name ?? ($conv->title ?? 'Conversa');
                        @endphp
                        <a href="{{ route($routeNamePrefix . '.show', $conv->id) }}" data-conversation-id="{{ $conv->id }}"
                            class="block p-3 hover:bg-blue-50 transition border-b border-gray-100 {{ isset($conversation) && $conversation->id == $conv->id ? 'bg-blue-50 border-blue-200' : '' }}">
                            <div class="flex items-center gap-2">
                                <img src="{{ $otherUserPhoto }}" alt="{{ $otherUserName }}"
                                    class="w-9 h-9 rounded-full object-cover flex-shrink-0"
                                    onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}'">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $otherUserName }}
                                    </h4>
                                    <p class="text-xs text-gray-500 truncate">Ver conversa</p>
                                </div>
                                @if($conv->unread_count > 0)
                                    <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded-full">
                                        {{ $conv->unread_count }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Chat Area -->
            @include('chat.partials.conversation')
        </div>
    </div>
    @push('scripts')
        <script>
            document.getElementById('chat-form').addEventListener('submit', function (e) {
                e.preventDefault();
                const input = document.getElementById('message-input');
                const body = input.value;
                if (!body.trim()) return;

                // Optimistic UI
                const container = document.getElementById('messages-container');
                const div = document.createElement('div');
                div.className = 'flex justify-end';
                div.innerHTML = `<div class="max-w-[75%] rounded-2xl px-4 py-2 shadow-sm text-sm bg-blue-600 text-white rounded-br-none"><p>${body}</p></div>`;
                container.appendChild(div);
                container.scrollTop = container.scrollHeight;
                input.value = '';

                // Ajax send
                fetch('{{ route($routeNamePrefix . ".message.store", $conversation->id) }}', {
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
            if (c) c.scrollTop = c.scrollHeight;

            // Polling logic
            let lastPollTime = Date.now();
            let isTabActive = true;

            document.addEventListener('visibilitychange', () => {
                isTabActive = !document.hidden;
            });

            setInterval(() => {
                if (!isTabActive) return;

                fetch('{{ route($routeNamePrefix . ".messages", $conversation->id) }}')
                    .then(response => response.json())
                    .then(messages => {
                        const container = document.getElementById('messages-container');
                        const currentCount = container.querySelectorAll('.flex').length;
                        if (messages.length > currentCount) {
                            container.innerHTML = '';
                            messages.reverse().forEach(msg => {
                                const isMe = msg.user_id == {{ Auth::id() }};
                                const div = document.createElement('div');
                                div.className = `flex ${isMe ? 'justify-end' : 'justify-start'}`;
                                div.innerHTML = `
                                                                <div class="max-w-[75%] rounded-2xl px-4 py-2 shadow-sm text-sm ${isMe ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white text-gray-800 rounded-bl-none border border-gray-100'}">
                                                                    <p>${msg.body}</p>
                                                                    <div class="text-[10px] mt-1 opacity-70 ${isMe ? 'text-blue-100' : 'text-gray-400'} text-right">
                                                                        ${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                                                    </div>
                                                                </div>
                                                            `;
                                container.appendChild(div);
                            });
                            container.scrollTop = container.scrollHeight;

                            // Force refresh global notification badge since messages were marked read
                            if (window.refreshNotifications) window.refreshNotifications();
                        }
                    });

                // Poll conversation list for sidebar badges
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
            }, 4000);

            // Immediate refresh on load since backend marked this conversation as read
            if (window.refreshNotifications) window.refreshNotifications();

            // Emoji Picker Logic
            const emojiToggleBtn = document.getElementById('emoji-toggle-btn');
            const emojiPicker = document.getElementById('emoji-picker-chat');
            const messageInput = document.getElementById('message-input');

            if (emojiToggleBtn && emojiPicker) {
                emojiToggleBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    emojiPicker.classList.toggle('hidden');
                });

                // Close emoji picker when clicking outside
                document.addEventListener('click', (e) => {
                    if (!emojiPicker.contains(e.target) && e.target !== emojiToggleBtn) {
                        emojiPicker.classList.add('hidden');
                    }
                });

                // Emoji tab filtering
                emojiPicker.querySelectorAll('.emoji-tab').forEach(tab => {
                    tab.addEventListener('click', () => {
                        emojiPicker.querySelectorAll('.emoji-tab').forEach(t => t.classList.remove('is-active', 'bg-blue-100'));
                        tab.classList.add('is-active', 'bg-blue-100');

                        const category = tab.getAttribute('data-category');
                        emojiPicker.querySelectorAll('.emoji-item').forEach(item => {
                            if (category === 'all' || item.getAttribute('data-category') === category) {
                                item.classList.remove('hidden');
                            } else {
                                item.classList.add('hidden');
                            }
                        });
                    });
                });

                // Insert emoji into input
                emojiPicker.querySelectorAll('.emoji-item').forEach(item => {
                    item.addEventListener('click', () => {
                        const emoji = item.getAttribute('data-emoji');
                        if (messageInput) {
                            const start = messageInput.selectionStart;
                            const end = messageInput.selectionEnd;
                            const text = messageInput.value;
                            messageInput.value = text.substring(0, start) + emoji + text.substring(end);
                            messageInput.selectionStart = messageInput.selectionEnd = start + emoji.length;
                            messageInput.focus();
                        }
                    });
                });
            }
        </script>
    @endpush
@endsection