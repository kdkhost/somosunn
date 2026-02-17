{{--
Floating Chat Component
Similar to Facebook Messenger popup
Persists across pages using localStorage
--}}
@auth
    @php
        $currentRoute = request()->route() ? request()->route()->getName() : '';
        $isMessagesPage = in_array($currentRoute, ['social.feed', 'chat.index', 'chat.show']);
    @endphp

    @if(!$isMessagesPage)
        <div id="floating-chat-container"
            class="fixed bottom-4 right-4 z-[9990] flex flex-col-reverse items-end gap-2 pointer-events-none">
            {{-- Chat boxes will be inserted here dynamically --}}
        </div>

        <style>
            .floating-chat-box {
                pointer-events: auto;
                width: 328px;
                height: 455px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                transition: all 0.2s ease;
            }

            .floating-chat-box.minimized {
                height: 48px;
            }

            .floating-chat-header {
                background: #1F5EDB;
                color: white;
                padding: 10px 12px;
                display: flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
                flex-shrink: 0;
            }

            .floating-chat-header img {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid rgba(255, 255, 255, 0.3);
            }

            .floating-chat-header-info {
                flex: 1;
                min-width: 0;
            }

            .floating-chat-header-name {
                font-weight: 600;
                font-size: 14px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .floating-chat-header-status {
                font-size: 11px;
                opacity: 0.85;
            }

            .floating-chat-actions {
                display: flex;
                gap: 6px;
            }

            .floating-chat-actions button {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: white;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s;
            }

            .floating-chat-actions button:hover {
                background: rgba(255, 255, 255, 0.3);
            }

            .floating-chat-messages {
                flex: 1;
                overflow-y: auto;
                padding: 12px;
                background: #f0f2f5;
                display: flex;
                flex-direction: column-reverse;
                gap: 6px;
            }

            .floating-chat-box.minimized .floating-chat-messages,
            .floating-chat-box.minimized .floating-chat-input-area {
                display: none;
            }

            .floating-chat-message {
                max-width: 80%;
                padding: 8px 12px;
                border-radius: 18px;
                font-size: 14px;
                line-height: 1.4;
                word-wrap: break-word;
            }

            .floating-chat-message.mine {
                background: #1F5EDB;
                color: white;
                align-self: flex-end;
                border-bottom-right-radius: 4px;
            }

            .floating-chat-message.theirs {
                background: white;
                color: #1c1e21;
                align-self: flex-start;
                border-bottom-left-radius: 4px;
            }

            .floating-chat-input-area {
                padding: 10px;
                background: white;
                border-top: 1px solid #e4e6eb;
                display: flex;
                gap: 8px;
                align-items: center;
                flex-shrink: 0;
            }

            .floating-chat-input {
                flex: 1;
                border: none;
                background: #f0f2f5;
                padding: 10px 14px;
                border-radius: 20px;
                font-size: 14px;
                outline: none;
            }

            .floating-chat-input:focus {
                background: #e4e6eb;
            }

            .floating-chat-send {
                background: #1F5EDB;
                border: none;
                color: white;
                width: 36px;
                height: 36px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s;
            }

            .floating-chat-send:hover {
                background: #1a4fc7;
            }

            .floating-chat-send:disabled {
                background: #a0a0a0;
                cursor: not-allowed;
            }

            .floating-chat-loading {
                text-align: center;
                padding: 20px;
                color: #65676b;
            }

            .floating-chat-empty {
                text-align: center;
                padding: 40px 20px;
                color: #65676b;
                font-size: 13px;
            }

            @media (max-width: 640px) {
                .floating-chat-box {
                    width: 100vw;
                    height: 100vh;
                    border-radius: 0;
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                }

                .floating-chat-box.minimized {
                    display: none;
                }

                #floating-chat-container {
                    position: fixed;
                    inset: 0;
                    flex-direction: column;
                    gap: 0;
                }
            }
        </style>

        <script>
            (function () {
                const STORAGE_KEY = 'unn_floating_chats';
                const MAX_VISIBLE_CHATS = 3;
                let openChats = {};
                let pollingIntervals = {};

                function loadFromStorage() {
                    try {
                        const data = localStorage.getItem(STORAGE_KEY);
                        if (data) {
                            const parsed = JSON.parse(data);
                            Object.entries(parsed).forEach(([id, chat]) => {
                                if (chat && chat.userId) {
                                    doOpenChat(parseInt(chat.userId), chat.userName, chat.userPhoto, chat.minimized);
                                }
                            });
                        }
                    } catch (e) {
                        console.warn('Failed to load floating chats from storage:', e);
                    }
                }

                function saveToStorage() {
                    try {
                        const data = {};
                        Object.entries(openChats).forEach(([id, chat]) => {
                            data[id] = {
                                userId: chat.userId,
                                userName: chat.userName,
                                userPhoto: chat.userPhoto,
                                minimized: chat.minimized
                            };
                        });
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                    } catch (e) {
                        console.warn('Failed to save floating chats to storage:', e);
                    }
                }

                function createChatBox(userId, userName, userPhoto) {
                    const box = document.createElement('div');
                    box.className = 'floating-chat-box';
                    box.dataset.userId = userId;
                    box.id = `floating-chat-${userId}`;

                    box.innerHTML = `
                    <div class="floating-chat-header" onclick="window.toggleFloatingChat(${userId})">
                        <img src="${userPhoto || '/img/default-user.svg'}" alt="${userName}" onerror="this.src='/img/default-user.svg'">
                        <div class="floating-chat-header-info">
                            <div class="floating-chat-header-name">${escapeHtml(userName)}</div>
                            <div class="floating-chat-header-status">Conversando agora</div>
                        </div>
                        <div class="floating-chat-actions" onclick="event.stopPropagation()">
                            <button type="button" onclick="window.minimizeFloatingChat(${userId})" title="Minimizar">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" onclick="window.closeFloatingChat(${userId})" title="Fechar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="floating-chat-messages" id="floating-chat-messages-${userId}">
                        <div class="floating-chat-loading">
                            <i class="fas fa-spinner fa-spin"></i> Carregando...
                        </div>
                    </div>
                    <form class="floating-chat-input-area" onsubmit="return window.sendFloatingMessage(event, ${userId})">
                        <input type="text" class="floating-chat-input" id="floating-chat-input-${userId}" placeholder="Digite uma mensagem..." autocomplete="off">
                        <button type="submit" class="floating-chat-send" title="Enviar">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                `;

                    return box;
                }

                function escapeHtml(text) {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                }

                function renderMessages(userId, messages) {
                    const container = document.getElementById(`floating-chat-messages-${userId}`);
                    if (!container) return;

                    if (!messages || messages.length === 0) {
                        container.innerHTML = '<div class="floating-chat-empty">Nenhuma mensagem ainda.<br>Diga olá! 👋</div>';
                        return;
                    }

                    container.innerHTML = messages.map(msg => `
                    <div class="floating-chat-message ${msg.is_mine ? 'mine' : 'theirs'}">
                        ${escapeHtml(msg.content || msg.body || '')}
                    </div>
                `).join('');
                }

                async function loadConversation(userId) {
                    try {
                        const response = await fetch(`/chat/with/${userId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) throw new Error('Failed to load conversation');

                        const data = await response.json();
                        openChats[userId].conversationId = data.conversation?.id;
                        renderMessages(userId, data.messages);
                    } catch (e) {
                        console.error('Error loading conversation:', e);
                        const container = document.getElementById(`floating-chat-messages-${userId}`);
                        if (container) {
                            container.innerHTML = '<div class="floating-chat-empty">Erro ao carregar. Clique para tentar novamente.</div>';
                            container.onclick = () => loadConversation(userId);
                        }
                    }
                }

                function startPolling(userId) {
                    if (pollingIntervals[userId]) return;

                    pollingIntervals[userId] = setInterval(async () => {
                        if (!openChats[userId] || openChats[userId].minimized) return;

                        try {
                            const response = await fetch(`/chat/with/${userId}`, {
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            if (response.ok) {
                                const data = await response.json();
                                renderMessages(userId, data.messages);
                            }
                        } catch (e) {
                            // Silent fail for polling
                        }
                    }, 5000);
                }

                function stopPolling(userId) {
                    if (pollingIntervals[userId]) {
                        clearInterval(pollingIntervals[userId]);
                        delete pollingIntervals[userId];
                    }
                }

                function doOpenChat(userId, userName, userPhoto, minimized = false) {
                    userId = parseInt(userId);

                    // Already open
                    if (openChats[userId]) {
                        const existing = document.getElementById(`floating-chat-${userId}`);
                        if (existing) {
                            existing.classList.remove('minimized');
                            openChats[userId].minimized = false;
                            saveToStorage();
                            const input = document.getElementById(`floating-chat-input-${userId}`);
                            if (input) input.focus();
                        }
                        return;
                    }

                    // Limit visible chats
                    const chatIds = Object.keys(openChats);
                    if (chatIds.length >= MAX_VISIBLE_CHATS) {
                        // Minimize oldest
                        const oldestId = chatIds[0];
                        window.minimizeFloatingChat(parseInt(oldestId));
                    }

                    const container = document.getElementById('floating-chat-container');
                    if (!container) return;

                    const box = createChatBox(userId, userName, userPhoto);
                    if (minimized) {
                        box.classList.add('minimized');
                    }
                    container.appendChild(box);

                    openChats[userId] = {
                        userId,
                        userName,
                        userPhoto,
                        minimized
                    };

                    saveToStorage();
                    loadConversation(userId);
                    startPolling(userId);

                    if (!minimized) {
                        setTimeout(() => {
                            const input = document.getElementById(`floating-chat-input-${userId}`);
                            if (input) input.focus();
                        }, 100);
                    }
                }

                window.openChatBox = function (userId, userName, userPhoto) {
                    doOpenChat(userId, userName, userPhoto, false);
                };

                window.closeFloatingChat = function (userId) {
                    userId = parseInt(userId);
                    stopPolling(userId);

                    const box = document.getElementById(`floating-chat-${userId}`);
                    if (box) box.remove();

                    delete openChats[userId];
                    saveToStorage();
                };

                window.minimizeFloatingChat = function (userId) {
                    userId = parseInt(userId);
                    const box = document.getElementById(`floating-chat-${userId}`);
                    if (box) {
                        box.classList.add('minimized');
                        if (openChats[userId]) {
                            openChats[userId].minimized = true;
                            saveToStorage();
                        }
                    }
                };

                window.toggleFloatingChat = function (userId) {
                    userId = parseInt(userId);
                    const box = document.getElementById(`floating-chat-${userId}`);
                    if (box) {
                        const isMinimized = box.classList.contains('minimized');
                        if (isMinimized) {
                            box.classList.remove('minimized');
                            if (openChats[userId]) {
                                openChats[userId].minimized = false;
                                saveToStorage();
                            }
                            const input = document.getElementById(`floating-chat-input-${userId}`);
                            if (input) input.focus();
                        } else {
                            window.minimizeFloatingChat(userId);
                        }
                    }
                };

                window.sendFloatingMessage = async function (event, userId) {
                    event.preventDefault();
                    userId = parseInt(userId);

                    const input = document.getElementById(`floating-chat-input-${userId}`);
                    if (!input) return false;

                    const message = input.value.trim();
                    if (!message) return false;

                    const sendBtn = input.closest('form').querySelector('button[type="submit"]');
                    if (sendBtn) sendBtn.disabled = true;

                    try {
                        const response = await fetch(`/chat/with/${userId}/message`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ message })
                        });

                        if (response.ok) {
                            input.value = '';
                            // Reload messages
                            await loadConversation(userId);
                        }
                    } catch (e) {
                        console.error('Error sending message:', e);
                    } finally {
                        if (sendBtn) sendBtn.disabled = false;
                    }

                    return false;
                };

                // Close all floating chats (useful for cleanup)
                window.closeAllFloatingChats = function () {
                    Object.keys(openChats).forEach(userId => {
                        window.closeFloatingChat(parseInt(userId));
                    });
                };

                // Initialize on page load
                document.addEventListener('DOMContentLoaded', loadFromStorage);
            })();
        </script>
    @endif
@endauth