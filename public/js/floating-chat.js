// Floating Chat Box Functions
function openChatBox(userId, userName, userPhoto) {
    const chatBox = document.getElementById('chatBox');
    const chatUserName = document.getElementById('chatUserName');
    const chatUserInitial = document.getElementById('chatUserInitial');
    const chatUserAvatar = document.getElementById('chatUserAvatar');
    const chatUserId = document.getElementById('chatUserId');
    
    // Set user info
    chatUserName.textContent = userName;
    chatUserInitial.textContent = userName.charAt(0).toUpperCase();
    chatUserId.value = userId;
    
    if (userPhoto) {
        chatUserAvatar.src = userPhoto;
        chatUserAvatar.classList.remove('hidden');
        chatUserInitial.classList.add('hidden');
    }
    
    // Show and animate chat box
    chatBox.style.display = 'block';
    setTimeout(() => {
        chatBox.style.transform = 'translateY(0)';
    }, 10);
    
    // Load messages
    loadMessages(userId);
}

function closeChatBox() {
    const chatBox = document.getElementById('chatBox');
    chatBox.style.transform = 'translateY(100%)';
    setTimeout(() => {
        chatBox.style.display = 'none';
    }, 300);
}

function toggleMinimizeChat() {
    const chatBody = document.getElementById('chatBody');
    const chatFooter = document.getElementById('chatFooter');
    const minimizeIcon = document.getElementById('chatMinimizeIcon');
    
    if (chatBody.style.display === 'none') {
        chatBody.style.display = 'block';
        chatFooter.style.display = 'block';
        minimizeIcon.className = 'fas fa-minus text-sm';
    } else {
        chatBody.style.display = 'none';
        chatFooter.style.display = 'none';
        minimizeIcon.className = 'fas fa-window-maximize text-sm';
    }
}

function sendMessage(event) {
    event.preventDefault();
    const input = document.getElementById('chatInput');
    const userId = document.getElementById('chatUserId').value;
    const message = input.value.trim();
    
    if (!message) return;
    
    // Append message to chat (optimistic UI)
    appendMessage(message, true);
    input.value = '';
    
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
// Send to server
    fetch(`/chat/with/${userId}/message`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            message: message
        })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            toastr.error(data.message || 'Erro ao enviar mensagem');
        }
    })
    .catch(error => {
        toastr.error('Erro ao enviar mensagem');
        console.error(error);
    });
}

function loadMessages(userId) {
    fetch(`/chat/with/${userId}`)
        .then(r => r.json())
        .then(data => {
            const chatBody = document.getElementById('chatBody');
            chatBody.innerHTML = '';
            
            if (data.messages && data.messages.length > 0) {
                // Since they are latest, reverse to append in order
                data.messages.reverse().forEach(msg => {
                    appendMessage(msg.content, msg.is_mine, false);
                });
                scrollChatToBottom();
                
                // Refresh global notifications because messages were marked as read
                if (window.refreshNotifications) window.refreshNotifications();
            } else {
                chatBody.innerHTML = `
                    <div class="text-center text-gray-500 text-sm py-8">
                        <i class="fas fa-comment-dots text-4xl mb-2 opacity-50"></i>
                        <p>Inicie uma conversa!</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erro ao carregar mensagens:', error);
        });
}

function appendMessage(content, isMine, shouldScroll = true) {
    const chatBody = document.getElementById('chatBody');
    
    // Remove empty state if exists
    if (chatBody.querySelector('.text-center')) {
        chatBody.innerHTML = '';
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex ${isMine ? 'justify-end' : 'justify-start'}`;
    
    messageDiv.innerHTML = `
        <div class="${isMine ? 'bg-[#1F5EDB] text-white' : 'bg-white text-gray-800'} px-4 py-2 rounded-2xl max-w-xs shadow">
            ${content}
        </div>
    `;
    
    chatBody.appendChild(messageDiv);
    
    if (shouldScroll) {
        scrollChatToBottom();
    }
}

function scrollChatToBottom() {
    const chatBody = document.getElementById('chatBody');
    chatBody.scrollTop = chatBody.scrollHeight;
}
