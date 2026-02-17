@extends('panel.layouts.app')

@section('title', 'Mensagens - UNN')

@section('panel_content')
    <div x-data='{ 
            activeConversationId: null,
            loading: false,
            conversations: [],

            async loadConversation(id) {
                if (this.activeConversationId === id) return;
                this.activeConversationId = id;
                this.loading = true;

                try {
                    const response = await fetch(`/chat/${id}`, {
                        headers: { "X-Requested-With": "XMLHttpRequest" }
                    });
                    const html = await response.text();
                    this.$refs.chatContainer.innerHTML = html;

                    // Executar scripts injetados
                    const scripts = this.$refs.chatContainer.querySelectorAll("script");
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement("script");
                        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });

                    // Atualizar histórico sem reload
                    window.history.pushState({}, "", `/chat/${id}`);
                } catch (error) {
                    console.error("Erro ao carregar conversa:", error);
                } finally {
                    this.loading = false;
                }
            },

            refreshList() {
                fetch("{{ route("chat.list") }}")
                    .then(r => r.json())
                    .then(data => { this.conversations = data; });
            }
        }' x-init='conversations = @json($conversations); setInterval(() => refreshList(), 5000)'
        class="max-w-6xl mx-auto px-0 sm:px-4 py-2 sm:py-6 h-[calc(100vh-120px)] sm:h-[calc(100vh-160px)] md:h-[calc(100vh-180px)] min-h-[400px]">

        <div
            class="bg-white dark:bg-slate-900 rounded-none sm:rounded-lg shadow-xl overflow-hidden flex h-full border-0 sm:border border-gray-200 dark:border-slate-800 transition-all duration-300">

            <!-- Sidebar Conversations -->
            <div :class="activeConversationId ? 'hidden md:flex' : 'flex'"
                class="w-full md:w-1/3 border-r border-gray-200 dark:border-slate-800 flex-col transition-all">
                <div
                    class="p-3 sm:p-4 border-b border-gray-200 dark:border-slate-800 bg-gray-50 dark:bg-slate-950 transition-colors">
                    <h2 class="font-bold text-lg sm:text-xl text-gray-800 dark:text-white">Mensagens</h2>
                </div>

                <div class="flex-1 overflow-y-auto no-scrollbar" id="conversations-list">
                    <template x-for="conv in conversations" :key="conv.id">
                        <div @click="loadConversation(conv.id)"
                            :class="activeConversationId == conv.id ? 'bg-blue-50 dark:bg-blue-900/30' : 'hover:bg-gray-50 dark:hover:bg-slate-800/50'"
                            class="cursor-pointer block p-3 sm:p-4 transition border-b border-gray-100 dark:border-slate-800/50">

                            <div class="flex items-center gap-2 sm:gap-3">
                                <div class="relative flex-shrink-0">
                                    <img :src="conv.users.find(u => u.id != {{ Auth::id() }})?.profile_photo_url || '{{ asset('img/default-user.svg') }}'"
                                        class="w-10 h-10 rounded-full object-cover border border-gray-200 dark:border-slate-700"
                                        x-on:error="$el.src='{{ asset('img/default-user.svg') }}'">
                                    <span
                                        class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate"
                                        x-text="conv.users.find(u => u.id != {{ Auth::id() }})?.name || 'Conversa'"></h4>
                                    <p class="text-xs text-gray-500 dark:text-slate-400 truncate"
                                        x-text="conv.messages[0]?.body || 'Ver conversa...'"></p>
                                </div>

                                <div class="flex flex-col items-end gap-1">
                                    <span x-show="conv.unread_count > 0" x-text="conv.unread_count"
                                        class="bg-blue-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="conversations.length === 0" class="p-8 text-center text-gray-500 dark:text-slate-400">
                        Nenhuma conversa iniciada.
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div :class="activeConversationId ? 'flex' : 'hidden md:flex'"
                class="flex-1 flex-col bg-slate-50 dark:bg-slate-950 transition-all">

                <div x-ref="chatContainer" class="flex-1 flex flex-col h-full relative">
                    <!-- Default state -->
                    <div x-show="!activeConversationId && !loading"
                        class="flex-1 flex flex-col items-center justify-center text-gray-400 dark:text-slate-600 gap-4">
                        <div class="w-20 h-20 bg-gray-100 dark:bg-slate-800 rounded-full flex items-center justify-center">
                            <i class="fas fa-comments text-3xl opacity-50"></i>
                        </div>
                        <p class="font-bold text-lg">Selecione uma conversa</p>
                        <p class="text-sm opacity-70">Para começar a trocar mensagens agora mesmo</p>
                    </div>

                    <!-- Loading state -->
                    <div x-show="loading" class="flex-1 flex items-center justify-center">
                        <div class="animate-spin rounded-full h-10 w-10 border-4 border-blue-600 border-t-transparent">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection