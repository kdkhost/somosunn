@extends('admin.layouts.app')

@section('title', 'Comunicação com Compradores')
@section('page_title', 'Comunicação com Compradores')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Comunicação com Compradores</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2"></i> Envio Individual
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.buyer-communication.individual') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label>Comprador</label>
                            <input type="text" id="user-search" class="form-control" placeholder="Buscar por nome ou e-mail...">
                            <input type="hidden" name="user_id" id="user-id" required>
                            <div id="user-results" class="list-group mt-2 hidden" style="max-height: 200px; overflow-y: auto;"></div>
                            <div id="user-selected" class="mt-2 hidden">
                                <span class="badge badge-info">
                                    <span id="selected-name"></span>
                                    <button type="button" onclick="clearUser()" class="ml-2 text-white">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Assunto</label>
                            <input type="text" name="subject" class="form-control" required maxlength="255" placeholder="Assunto da mensagem">
                        </div>

                        <div class="form-group">
                            <label>Mensagem</label>
                            <textarea name="message" class="form-control" rows="5" required placeholder="Escreva sua mensagem aqui..."></textarea>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="send_email" id="send-email-individual" value="1" class="custom-control-input">
                                <label for="send-email-individual" class="custom-control-label">Enviar também por e-mail</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane mr-2"></i> Enviar Mensagem
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-2"></i> Envio em Massa
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.buyer-communication.bulk') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label>Tipo de Serviço</label>
                            <select name="service_type" id="service-type-select" class="form-control">
                                <option value="">Todos os tipos</option>
                                <option value="event">Eventos</option>
                                <option value="course">Cursos</option>
                                <option value="mentorship">Mentorias</option>
                                <option value="marketplace">Marketplace</option>
                            </select>
                        </div>

                        <div class="form-group" id="item-select-container" style="display: none;">
                            <label>Item Específico</label>
                            <select name="item_id" id="item-select" class="form-control">
                                <option value="">Todos os itens deste tipo</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Data Inicial</label>
                                    <input type="date" name="date_from" id="date-from" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Data Final</label>
                                    <input type="date" name="date_to" id="date-to" class="form-control">
                                </div>
                            </div>
                        </div>

                        <button type="button" onclick="openRecipientsModal()" class="btn btn-light btn-block mb-3">
                            <i class="fas fa-users mr-2"></i> Selecionar Destinatários
                        </button>

                        <p class="text-center small text-muted">
                            <span id="selected-count">0</span> destinatários selecionados
                        </p>

                        <input type="hidden" name="selected_recipients" id="selected-recipients-input" value="">

                        <div class="form-group">
                            <label>Assunto</label>
                            <input type="text" name="subject" class="form-control" required maxlength="255" placeholder="Assunto da mensagem">
                        </div>

                        <div class="form-group">
                            <label>Mensagem</label>
                            <textarea name="message" class="form-control" rows="5" required placeholder="Escreva sua mensagem aqui..."></textarea>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="send_email" id="send-email-bulk" value="1" class="custom-control-input">
                                <label for="send-email-bulk" class="custom-control-label">Enviar também por e-mail</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fas fa-bullhorn mr-2"></i> Enviar para Todos
                        </button>
                    </form>
                </div>
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
                itemContainer.style.display = 'block';
                itemSelect.innerHTML = '<option value="">Todos os itens deste tipo</option>';

                fetch('{{ route('admin.buyer-communication.get-items') }}?service_type=' + encodeURIComponent(serviceType))
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
                itemContainer.style.display = 'none';
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
                fetch('{{ route('admin.buyer-communication.search-users') }}?term=' + encodeURIComponent(term))
                    .then(response => response.json())
                    .then(data => {
                        const resultsDiv = document.getElementById('user-results');
                        resultsDiv.innerHTML = '';

                        if (data.length === 0) {
                            resultsDiv.innerHTML = '<div class="list-group-item text-muted">Nenhum usuário encontrado</div>';
                        } else {
                            data.forEach(user => {
                                const div = document.createElement('a');
                                div.className = 'list-group-item list-group-item-action';
                                div.href = '#';
                                div.innerHTML = `<div class="font-weight-bold">${user.name}</div><small class="text-muted">${user.email}</small>`;
                                div.onclick = (e) => {
                                    e.preventDefault();
                                    selectUser(user.id, user.name, user.email);
                                };
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

            const url = '{{ route('admin.buyer-communication.preview-recipients') }}' + (params.toString() ? '?' + params.toString() : '');

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modal-recipients-count').textContent = data.count;
                    const listDiv = document.getElementById('modal-recipients-list');
                    if (data.users && data.users.length > 0) {
                        listDiv.innerHTML = data.users.map(u => `
                            <label class="d-flex align-items-center p-2 bg-light rounded mb-1" style="cursor: pointer;">
                                <input type="checkbox" class="recipient-checkbox mr-2" value="${u.id}" checked>
                                <span class="small">${u.name} (${u.email})</span>
                            </label>
                        `).join('');
                    } else {
                        listDiv.innerHTML = '<p class="text-muted small">Nenhum destinatário encontrado com os filtros selecionados.</p>';
                    }
                    $('#recipientsModal').modal('show');
                    updateSelectedRecipients();
                })
                .catch(error => {
                    console.error('Erro ao carregar destinatários:', error);
                    alert('Erro ao carregar destinatários. Verifique o console para mais detalhes.');
                });
        }

        function closeRecipientsModal() {
            $('#recipientsModal').modal('hide');
        }

        function updateSelectedRecipients() {
            const checkboxes = document.querySelectorAll('.recipient-checkbox:checked');
            const selectedIds = Array.from(checkboxes).map(cb => cb.value);
            document.getElementById('selected-recipients-input').value = selectedIds.join(',');
            document.getElementById('selected-count').textContent = selectedIds.length;
        }

        // Delegação de eventos para checkboxes dinâmicos
        document.addEventListener('change', function(e) {
            if (e.target.id === 'modal-select-all-recipients') {
                const checkboxes = document.querySelectorAll('.recipient-checkbox');
                checkboxes.forEach(cb => cb.checked = e.target.checked);
                updateSelectedRecipients();
            }
            if (e.target.classList.contains('recipient-checkbox')) {
                updateSelectedRecipients();
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#user-search') && !e.target.closest('#user-results')) {
                document.getElementById('user-results').classList.add('hidden');
            }
        });
    </script>

    <!-- Modal de Seleção de Destinatários -->
    <div class="modal fade" id="recipientsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Selecionar Destinatários</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeRecipientsModal()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="mb-0"><span id="modal-recipients-count" class="font-bold">0</span> compradores encontrados.</p>
                        <label class="mb-0 small">
                            <input type="checkbox" id="modal-select-all-recipients"> Selecionar todos
                        </label>
                    </div>
                    <div id="modal-recipients-list" style="max-height: 400px; overflow-y: auto;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="closeRecipientsModal()">
                        <i class="fas fa-check mr-2"></i> Confirmar Seleção
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
