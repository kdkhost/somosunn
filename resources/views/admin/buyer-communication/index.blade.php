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
                            <label>Filtrar por Tipo de Compra</label>
                            <select name="sale_type" id="sale-type-select" class="form-control">
                                <option value="">Todos os compradores</option>
                                @foreach($saleTypeLabels ?? [] as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="button" onclick="previewRecipients()" class="btn btn-light btn-block mb-3">
                            <i class="fas fa-eye mr-2"></i> Visualizar Destinatários
                        </button>

                        <div id="recipients-preview" class="alert alert-info hidden">
                            <p><span id="recipients-count" class="font-bold">0</span> compradores serão notificados.</p>
                            <div id="recipients-list" class="mt-2" style="max-height: 150px; overflow-y: auto; font-size: 12px;"></div>
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

        function previewRecipients() {
            const saleType = document.getElementById('sale-type-select').value;
            const url = '{{ route('admin.buyer-communication.preview-recipients') }}' + (saleType ? '?sale_type=' + saleType : '');

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('recipients-count').textContent = data.count;
                    const listDiv = document.getElementById('recipients-list');
                    listDiv.innerHTML = data.users.map(u => `<div>${u.name} (${u.email})</div>`).join('');
                    document.getElementById('recipients-preview').classList.remove('hidden');
                });
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#user-search') && !e.target.closest('#user-results')) {
                document.getElementById('user-results').classList.add('hidden');
            }
        });
    </script>
@endsection
