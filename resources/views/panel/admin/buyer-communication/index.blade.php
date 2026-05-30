@extends('panel.layouts.app')

@section('title', 'Comunicação com Compradores')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.buyer-communication.index') }}" class="hover:underline">Comunicação</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white transition-colors">Comunicação com Compradores</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">
                Envie mensagens individuais ou em massa para compradores de eventos, cursos, mentorias e produtos.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Envio Individual -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-user text-blue-500"></i> Envio Individual
                </h2>

                <form action="{{ route('panel.admin.buyer-communication.individual') }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Comprador</label>
                            <input type="text" id="user-search" placeholder="Buscar por nome ou e-mail..."
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 bg-white dark:bg-slate-800">
                            <input type="hidden" name="user_id" id="user-id" required>
                            <div id="user-results" class="mt-2 hidden bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-48 overflow-y-auto"></div>
                            <div id="user-selected" class="mt-2 hidden">
                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-sm">
                                    <span id="selected-name"></span>
                                    <button type="button" onclick="clearUser()" class="hover:text-blue-900 dark:hover:text-blue-300">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Assunto</label>
                            <input type="text" name="subject" required maxlength="255"
                                placeholder="Assunto da mensagem"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 bg-white dark:bg-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Mensagem</label>
                            <textarea name="message" required rows="5"
                                placeholder="Escreva sua mensagem aqui..."
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 bg-white dark:bg-slate-800 resize-none"></textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="send_email" id="send-email-individual" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <label for="send-email-individual" class="text-sm text-slate-600 dark:text-slate-400">Enviar também por e-mail</label>
                        </div>

                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-blue-200">
                            <i class="fas fa-paper-plane"></i> Enviar Mensagem
                        </button>
                    </div>
                </form>
            </div>

            <!-- Envio em Massa -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-users text-purple-500"></i> Envio em Massa
                </h2>

                <form action="{{ route('panel.admin.buyer-communication.bulk') }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Tipo de Serviço</label>
                            <select name="service_type" id="service-type-select"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 bg-white dark:bg-slate-800">
                                <option value="">Todos os tipos</option>
                                <option value="event">Eventos</option>
                                <option value="course">Cursos</option>
                                <option value="mentorship">Mentorias</option>
                                <option value="marketplace">Marketplace</option>
                            </select>
                        </div>

                        <div id="item-select-container" class="hidden">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Item Específico</label>
                            <select name="item_id" id="item-select"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 bg-white dark:bg-slate-800">
                                <option value="">Todos os itens deste tipo</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Data Inicial</label>
                                <input type="date" name="date_from" id="date-from"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 bg-white dark:bg-slate-800">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Data Final</label>
                                <input type="date" name="date_to" id="date-to"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 bg-white dark:bg-slate-800">
                            </div>
                        </div>

                        <button type="button" onclick="openRecipientsModal()"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-xl transition-all">
                            <i class="fas fa-users"></i> Selecionar Destinatários
                        </button>

                        <p class="text-xs text-slate-500 dark:text-slate-400 text-center">
                            <span id="selected-count">0</span> destinatários selecionados
                        </p>

                        <input type="hidden" name="selected_recipients" id="selected-recipients-input" value="">

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Assunto</label>
                            <input type="text" name="subject" required maxlength="255"
                                placeholder="Assunto da mensagem"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 bg-white dark:bg-slate-800">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Mensagem</label>
                            <textarea name="message" required rows="5"
                                placeholder="Escreva sua mensagem aqui..."
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 bg-white dark:bg-slate-800 resize-none"></textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="send_email" id="send-email-bulk" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <label for="send-email-bulk" class="text-sm text-slate-600 dark:text-slate-400">Enviar também por e-mail</label>
                        </div>

                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-purple-200">
                            <i class="fas fa-bullhorn"></i> Enviar para Todos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let searchTimeout;

        // Carregar itens baseado no tipo de serviço
        document.getElementById('service-type-select').addEventListener('change', function() {
            const serviceType = this.value;
            const itemContainer = document.getElementById('item-select-container');
            const itemSelect = document.getElementById('item-select');

            if (serviceType) {
                itemContainer.classList.remove('hidden');
                itemSelect.innerHTML = '<option value="">Todos os itens deste tipo</option>';

                fetch('{{ route('panel.admin.buyer-communication.get-items') }}?service_type=' + encodeURIComponent(serviceType))
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.name;
                            itemSelect.appendChild(option);
                        });
                    });
            } else {
                itemContainer.classList.add('hidden');
            }
        });

        document.getElementById('user-search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const term = this.value.trim();

            if (term.length < 2) {
                document.getElementById('user-results').classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch('{{ route('panel.admin.buyer-communication.search-users') }}?term=' + encodeURIComponent(term))
                    .then(response => response.json())
                    .then(data => {
                        const resultsDiv = document.getElementById('user-results');
                        resultsDiv.innerHTML = '';

                        if (data.length === 0) {
                            resultsDiv.innerHTML = '<div class="p-3 text-sm text-slate-500">Nenhum usuário encontrado</div>';
                        } else {
                            data.forEach(user => {
                                const div = document.createElement('div');
                                div.className = 'p-3 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer text-sm';
                                div.innerHTML = `<div class="font-medium text-slate-900 dark:text-white">${user.name}</div><div class="text-xs text-slate-500">${user.email}</div>`;
                                div.onclick = () => selectUser(user.id, user.name, user.email);
                                resultsDiv.appendChild(div);
                            });
                        }

                        resultsDiv.classList.remove('hidden');
                    });
            }, 300);
        });

        function selectUser(id, name, email) {
            document.getElementById('user-id').value = id;
            document.getElementById('selected-name').textContent = name + ' (' + email + ')';
            document.getElementById('user-selected').classList.remove('hidden');
            document.getElementById('user-results').classList.add('hidden');
            document.getElementById('user-search').value = '';
        }

        function clearUser() {
            document.getElementById('user-id').value = '';
            document.getElementById('user-selected').classList.add('hidden');
            document.getElementById('user-search').value = '';
        }

        function openRecipientsModal() {
            const serviceType = document.getElementById('service-type-select').value;
            const itemId = document.getElementById('item-select').value;
            const dateFrom = document.getElementById('date-from').value;
            const dateTo = document.getElementById('date-to').value;

            const params = new URLSearchParams();
            if (serviceType) params.append('service_type', serviceType);
            if (itemId) params.append('item_id', itemId);
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);

            const url = '{{ route('panel.admin.buyer-communication.preview-recipients') }}' + (params.toString() ? '?' + params.toString() : '');

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modal-recipients-count').textContent = data.count;
                    const listDiv = document.getElementById('modal-recipients-list');
                    listDiv.innerHTML = data.users.map(u => `
                        <label class="flex items-center gap-2 p-3 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700">
                            <input type="checkbox" class="recipient-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500" value="${u.id}" checked>
                            <span class="text-sm text-slate-700 dark:text-slate-300">${u.name} (${u.email})</span>
                        </label>
                    `).join('');
                    document.getElementById('recipients-modal').classList.remove('hidden');
                    document.getElementById('recipients-modal').classList.add('flex');
                    updateSelectedRecipients();
                });
        }

        function closeRecipientsModal() {
            document.getElementById('recipients-modal').classList.add('hidden');
            document.getElementById('recipients-modal').classList.remove('flex');
        }

        document.getElementById('modal-select-all-recipients').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.recipient-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSelectedRecipients();
        });

        document.getElementById('modal-recipients-list').addEventListener('change', function(e) {
            if (e.target.classList.contains('recipient-checkbox')) {
                updateSelectedRecipients();
            }
        });

        function updateSelectedRecipients() {
            const checkboxes = document.querySelectorAll('.recipient-checkbox:checked');
            const selectedIds = Array.from(checkboxes).map(cb => cb.value);
            document.getElementById('selected-recipients-input').value = selectedIds.join(',');
            document.getElementById('selected-count').textContent = selectedIds.length;
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#user-search') && !e.target.closest('#user-results')) {
                document.getElementById('user-results').classList.add('hidden');
            }
        });
    </script>

    <!-- Modal de Seleção de Destinatários -->
    <div id="recipients-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-2xl max-h-[80vh] overflow-hidden m-4">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Selecionar Destinatários</h3>
                    <button type="button" onclick="closeRecipientsModal()" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-4">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        <span id="modal-recipients-count" class="font-bold text-slate-900 dark:text-white">0</span> compradores encontrados.
                    </p>
                    <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                        <input type="checkbox" id="modal-select-all-recipients" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        Selecionar todos
                    </label>
                </div>
            </div>
            <div id="modal-recipients-list" class="p-6 overflow-y-auto max-h-[60vh] space-y-2"></div>
            <div class="p-6 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="closeRecipientsModal()" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all">
                    <i class="fas fa-check"></i> Confirmar Seleção
                </button>
            </div>
        </div>
    </div>
@endsection
