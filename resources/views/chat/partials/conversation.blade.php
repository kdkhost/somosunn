@php
    $chatOtherUser = $conversation->users->where('id', '!=', Auth::id())->first() ?? $conversation->users->first();
    $chatOtherUserPhoto = $chatOtherUser?->profile_photo_url ?? asset('img/default-user.svg');
    $chatOtherUserName = $chatOtherUser?->name ?? ($conversation->title ?? 'Conversa');
    $routeNamePrefix = $routeNamePrefix ?? 'chat';
@endphp

<div class="w-full flex-1 flex flex-col bg-slate-50 dark:bg-slate-950 relative h-full">
    <!-- Header -->
    <div
        class="p-3 sm:p-4 border-b border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between shadow-sm z-10 sticky top-0 transition-colors">
        <div class="flex items-center gap-2 sm:gap-3">
            <button @click="activeConversationId = null"
                class="md:hidden text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200 p-1">
                <i class="fas fa-arrow-left text-lg"></i>
            </button>
            <img src="{{ $chatOtherUserPhoto }}" alt="{{ $chatOtherUserName }}"
                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-cover border border-gray-200 dark:border-slate-700"
                onerror="this.onerror=null;this.src='{{ asset('img/default-user.svg') }}'">
            <div>
                <h4 class="font-bold text-gray-900 dark:text-white text-sm sm:text-base line-clamp-1">
                    {{ $chatOtherUserName }}</h4>
                <span class="flex items-center gap-1 text-xs text-green-500 font-medium">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Online
                </span>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <div class="flex-1 overflow-y-auto p-4 space-y-4 no-scrollbar" id="messages-container">
        @foreach($messages->reverse() as $msg)
            <div class="flex {{ $msg->user_id == Auth::id() ? 'justify-end' : 'justify-start' }}">
                <div
                    class="max-w-[85%] sm:max-w-[75%] rounded-2xl px-4 py-2 shadow-sm text-sm {{ $msg->user_id == Auth::id() ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white dark:bg-slate-800 text-gray-800 dark:text-slate-200 rounded-bl-none border border-gray-100 dark:border-slate-700' }}">
                    <p class="whitespace-pre-wrap break-words">{{ $msg->body }}</p>
                    <div
                        class="text-[10px] mt-1 opacity-70 {{ $msg->user_id == Auth::id() ? 'text-blue-100' : 'text-gray-400 dark:text-slate-500' }} text-right flex items-center justify-end gap-1">
                        {{ $msg->created_at->format('H:i') }}
                        @if($msg->user_id == Auth::id())
                            <i class="fas fa-check-double"></i>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Input -->
    <div
        class="p-2 sm:p-4 bg-white dark:bg-slate-900 border-t border-gray-200 dark:border-slate-800 sticky bottom-0 transition-colors">
        <form id="chat-form" class="flex items-center gap-1 sm:gap-2">
            <button type="button"
                class="text-gray-300 dark:text-slate-600 p-1 sm:p-2 cursor-not-allowed hidden sm:block" title="Em breve"
                disabled>
                <i class="fas fa-paperclip"></i>
            </button>
            <div class="relative">
                <button type="button" id="emoji-toggle-btn"
                    class="text-gray-400 dark:text-slate-500 hover:text-yellow-500 p-1 sm:p-2 transition">
                    <i class="fas fa-smile text-lg sm:text-base"></i>
                </button>
                @include('partials.emoji-picker', ['pickerId' => 'chat'])
            </div>
            <input type="text" id="message-input" autocomplete="off"
                class="flex-1 border border-gray-200 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-full px-3 sm:px-4 py-2 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 transition-all"
                placeholder="Digite sua mensagem...">
            <button type="submit"
                class="bg-blue-600 text-white rounded-full w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center hover:bg-blue-700 hover:scale-105 active:scale-95 transition-all shadow-lg shadow-blue-500/20 flex-shrink-0">
                <i class="fas fa-paper-plane text-xs sm:text-sm"></i>
            </button>
        </form>
    </div>
</div>

<script>
    (function () {
        const form = document.getElementById('chat-form');
        const container = document.getElementById('messages-container');
        const input = document.getElementById('message-input');

        if (container) container.scrollTop = container.scrollHeight;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const body = input.value;
            if (!body.trim()) return;

            // Optimistic UI
            const div = document.createElement('div');
            div.className = 'flex justify-end';
            div.innerHTML = `<div class="max-w-[85%] sm:max-w-[75%] rounded-2xl px-4 py-2 shadow-sm text-sm bg-blue-600 text-white rounded-br-none"><p class="whitespace-pre-wrap break-words">${body}</p></div>`;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
            input.value = '';

            fetch('{{ route($routeNamePrefix . ".message.store", $conversation->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ body: body })
            });
        });

        // Polling logic localized for this conversation
        let pollInterval = setInterval(() => {
            // Se o elemento não existir mais no DOM (conversa trocada), para o poll
            if (!document.getElementById('messages-container')) {
                clearInterval(pollInterval);
                return;
            }

            fetch('{{ route($routeNamePrefix . ".messages", $conversation->id) }}')
                .then(response => response.json())
                .then(messages => {
                    const currentCount = container.querySelectorAll('.flex').length;
                    if (messages.length > currentCount) {
                        container.innerHTML = '';
                        messages.reverse().forEach(msg => {
                            const isMe = msg.user_id == {{ Auth::id() }};
                            const div = document.createElement('div');
                            div.className = `flex ${isMe ? 'justify-end' : 'justify-start'}`;
                            div.innerHTML = `
                                <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl px-4 py-2 shadow-sm text-sm ${isMe ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white dark:bg-slate-800 text-gray-800 dark:text-slate-200 rounded-bl-none border border-gray-100 dark:border-slate-700'}">
                                    <p class="whitespace-pre-wrap break-words">${msg.body}</p>
                                    <div class="text-[10px] mt-1 opacity-70 ${isMe ? 'text-blue-100' : 'text-gray-400 dark:text-slate-500'} text-right flex items-center justify-end gap-1">
                                        ${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                        ${isMe ? '<i class="fas fa-check-double"></i>' : ''}
                                    </div>
                                </div>
                            `;
                            container.appendChild(div);
                        });
                        container.scrollTop = container.scrollHeight;
                        if (window.refreshNotifications) window.refreshNotifications();
                    }
                });
        }, 4000);

        // Emoji Picker Logic
        const emojiToggleBtn = document.getElementById('emoji-toggle-btn');
        const emojiPicker = document.getElementById('emoji-picker-chat');
        if (emojiToggleBtn && emojiPicker) {
            emojiToggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                emojiPicker.classList.toggle('hidden');
            });
            document.addEventListener('click', (e) => {
                if (!emojiPicker.contains(e.target) && e.target !== emojiToggleBtn) {
                    emojiPicker.classList.add('hidden');
                }
            });
            emojiPicker.querySelectorAll('.emoji-item').forEach(item => {
                item.addEventListener('click', () => {
                    const emoji = item.getAttribute('data-emoji');
                    const start = input.selectionStart;
                    const end = input.selectionEnd;
                    const text = input.value;
                    input.value = text.substring(0, start) + emoji + text.substring(end);
                    input.selectionStart = input.selectionEnd = start + emoji.length;
                    input.focus();
                });
            });
        }
    })();
</script>