<div id="chat-widget-container"
    style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; font-family: 'Inter', sans-serif;">
    <!-- Toggle Button -->
    <button id="chat-toggle-btn"
        class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center"
        style="width: 60px; height: 60px; border-radius: 50%; transition: transform 0.2s;" onclick="toggleChatWidget()">
        <i class="fas fa-comments fa-2x"></i>
        <span id="chat-unread-badge" class="badge badge-danger navbar-badge"
            style="position: absolute; top: 0; right: 0; display: none;">0</span>
    </button>

    <!-- Chat Window -->
    <div id="chat-window" class="card shadow-lg d-none"
        style="width: 320px; height: 450px; position: absolute; bottom: 70px; right: 0; display: flex; flex-direction: column; overflow: hidden; border-radius: 12px; margin-bottom: 0;">

        <!-- Header -->
        <div class="card-header bg-primary text-white p-2 d-flex justify-content-between align-items-center"
            style="cursor: pointer;" onclick="toggleChatWidget()">
            <h6 class="mb-0 font-weight-bold"><i class="fas fa-comments mr-2"></i> Mensagens</h6>
            <button class="btn btn-sm text-white"><i class="fas fa-chevron-down"></i></button>
        </div>

        <!-- Content Area -->
        <div class="card-body p-0 position-relative flex-grow-1" style="overflow: hidden;">

            <!-- List View -->
            <div id="chat-list-view" class="h-100 overflow-auto">
                <div class="text-center p-4" id="chat-list-loading">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div id="chat-conversations-list" class="list-group list-group-flush">
                    <!-- Conversations will be loaded here -->
                </div>
            </div>

            <!-- Conversation View -->
            <div id="chat-conversation-view" class="h-100 w-100 bg-white d-flex flex-column position-absolute"
                style="top: 0; left: 0; transform: translateX(100%); transition: transform 0.3s ease;">

                <div class="p-2 border-bottom bg-light d-flex align-items-center">
                    <button class="btn btn-sm btn-link mr-2" onclick="showChatList()"><i
                            class="fas fa-arrow-left"></i></button>
                    <div class="d-flex align-items-center">
                        <img id="chat-current-avatar" src="" class="rounded-circle mr-2"
                            style="width: 30px; height: 30px; object-fit: cover;">
                        <span id="chat-current-name" class="font-weight-bold text-truncate"
                            style="max-width: 180px;">User</span>
                    </div>
                </div>

                <div id="chat-messages-container" class="flex-grow-1 overflow-auto p-3 bg-white">
                    <!-- Messages -->
                </div>

                <div class="p-2 border-top bg-light">
                    <form id="chat-send-form" class="d-flex gap-2" onsubmit="sendChatMessage(event)">
                        <input type="text" id="chat-input" class="form-control form-control-sm"
                            placeholder="Digite uma mensagem..." autocomplete="off">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .chat-message {
        max-width: 80%;
        padding: 8px 12px;
        border-radius: 12px;
        margin-bottom: 8px;
        font-size: 0.9rem;
        word-wrap: break-word;
    }

    .chat-message.me {
        background-color: #007bff;
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 2px;
        margin-left: auto;
    }

    .chat-message.other {
        background-color: #f1f3f4;
        color: #333;
        align-self: flex-start;
        border-bottom-left-radius: 2px;
    }

    .conversation-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .conversation-item:hover {
        background-color: #f8f9fa;
    }
</style>

<script>
    let chatWidgetOpen = false;
    let currentConversationId = null;
    let chatPollInterval = null;

    function toggleChatWidget() {
        const window = document.getElementById('chat-window');
        chatWidgetOpen = !chatWidgetOpen;

        if (chatWidgetOpen) {
            window.classList.remove('d-none');
            loadChatConversations();
        } else {
            window.classList.add('d-none');
        }
    }

    function loadChatConversations() {
        fetch('<?php echo e(route("chat.list")); ?>', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(r => {
                if (!r.ok) throw new Error('Erro ao carregar');
                return r.json();
            })
            .then(data => {
                const list = document.getElementById('chat-conversations-list');
                document.getElementById('chat-list-loading').classList.add('d-none');
                list.innerHTML = '';

                if (data.length === 0) {
                    list.innerHTML = '<div class="text-center p-3 text-muted">Nenhuma conversa iniciada.</div>';
                    return;
                }

                data.forEach(conv => {
                    const otherUser = conv.users.find(u => u.id !== <?php echo e(auth()->id()); ?>) || { name: 'Usuário', photo: null };
                    const lastMsg = conv.messages && conv.messages.length ? conv.messages[0].body : 'Nova conversa';
                    const avatar = otherUser.photo ? (otherUser.photo.startsWith('http') ? otherUser.photo : '/' + otherUser.photo) : '/img/default-user.svg';

                    const item = document.createElement('div');
                    item.className = 'conversation-item list-group-item px-3 py-2 border-0 border-bottom';
                    item.onclick = () => openChatConversation(conv.id, otherUser.name, avatar);
                    item.innerHTML = `
                        <div class="d-flex align-items-center">
                            <img src="${avatar}" class="rounded-circle mr-3" style="width: 40px; height: 40px; object-fit: cover;">
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-truncate font-weight-bold" style="font-size: 0.95rem;">${otherUser.name}</h6>
                                    <small class="text-muted" style="font-size: 0.75rem;">${new Date(conv.updated_at).toLocaleDateString()}</small>
                                </div>
                                <small class="text-muted text-truncate d-block">${lastMsg}</small>
                            </div>
                        </div>
                    `;
                    list.appendChild(item);
                });
            })
            .catch(err => console.error('Error loading chats:', err));
    }

    function openChatConversation(id, name, avatar) {
        currentConversationId = id;
        document.getElementById('chat-current-name').innerText = name;
        document.getElementById('chat-current-avatar').src = avatar;

        // Slide animation
        document.getElementById('chat-conversation-view').style.transform = 'translateX(0)';

        loadChatMessages(id);

        // Polling for new messages
        if (chatPollInterval) clearInterval(chatPollInterval);
        chatPollInterval = setInterval(() => loadChatMessages(id, false), 5000);
    }

    function showChatList() {
        currentConversationId = null;
        if (chatPollInterval) clearInterval(chatPollInterval);
        document.getElementById('chat-conversation-view').style.transform = 'translateX(100%)';
        loadChatConversations(); // Refresh list to update last messages
    }

    function loadChatMessages(conversationId, scroll = true) {
        fetch(`/chat/${conversationId}/messages`)
            .then(r => r.json())
            .then(messages => {
                const container = document.getElementById('chat-messages-container');
                // Simple Diff check could be optimized, but full redraw for now is safer for order
                // Only if count changes or force reload
                container.innerHTML = '';

                messages.reverse().forEach(msg => {
                    const div = document.createElement('div');
                    const isMe = msg.user_id === <?php echo e(auth()->id()); ?>;
                    div.className = `chat-message ${isMe ? 'me' : 'other'}`;
                    div.innerText = msg.body;
                    div.title = new Date(msg.created_at).toLocaleString();
                    container.appendChild(div);
                });

                if (scroll) container.scrollTop = container.scrollHeight;
            });
    }

    function sendChatMessage(e) {
        e.preventDefault();
        const input = document.getElementById('chat-input');
        const text = input.value.trim();
        if (!text || !currentConversationId) return;

        input.value = ''; // Optimistic clear

        fetch(`/chat/${currentConversationId}/message`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            body: JSON.stringify({ body: text })
        })
            .then(r => r.json())
            .then(msg => {
                loadChatMessages(currentConversationId, true);
            })
            .catch(err => {
                console.error('Send failed', err);
                input.value = text; // Restore on fail
            });
    }
</script>
<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\partials\chat-widget.blade.php ENDPATH**/ ?>