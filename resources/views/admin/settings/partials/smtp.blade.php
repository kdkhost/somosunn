<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-envelope mr-2"></i> Configure o servidor de e-mail (SMTP) para envio de notificações,
        recuperação de senha e boas-vindas.
    </div>

    <h5 class="text-primary mb-3"><i class="fas fa-server mr-2"></i> Configurações do Servidor</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Host SMTP</label>
            <input type="text" name="smtp_host" class="form-control" value="{{ $settings['smtp_host'] ?? '' }}"
                placeholder="smtp.exemplo.com">
        </div>
        <div class="col-md-3 form-group">
            <label>Porta</label>
            <input type="number" name="smtp_port" class="form-control" value="{{ $settings['smtp_port'] ?? '587' }}">
        </div>
        <div class="col-md-3 form-group">
            <label>Criptografia</label>
            <select name="smtp_encryption" class="form-control">
                <option value="tls" {{ ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS
                    (Recomendado)</option>
                <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                <option value="null" {{ ($settings['smtp_encryption'] ?? '') === 'null' ? 'selected' : '' }}>Nenhuma
                </option>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label>Usuário (E-mail)</label>
            <input type="text" name="smtp_username" class="form-control" value="{{ $settings['smtp_username'] ?? '' }}">
        </div>
        <div class="col-md-6 form-group">
            <label>Senha</label>
            <input type="password" name="smtp_password" class="form-control"
                value="{{ $settings['smtp_password'] ?? '' }}">
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-id-card-alt mr-2"></i> Identificação do Remetente</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>E-mail Remetente (From)</label>
            <input type="email" name="smtp_from_email" class="form-control"
                value="{{ $settings['smtp_from_email'] ?? '' }}">
            <small class="form-text text-muted">Geralmente o mesmo que o usuário SMTP.</small>
        </div>
        <div class="col-md-6 form-group">
            <label>Nome do Remetente</label>
            <input type="text" name="smtp_from_name" class="form-control"
                value="{{ $settings['smtp_from_name'] ?? config('app.name') }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 form-group">
            <label>Cópia (CC)</label>
            <input type="text" name="smtp_cc" class="form-control" value="{{ $settings['smtp_cc'] ?? '' }}"
                placeholder="email@exemplo.com">
            <small class="form-text text-muted">Opcional. Separe por vírgulas.</small>
        </div>
        <div class="col-md-6 form-group">
            <label>Cópia Oculta (BCC)</label>
            <input type="text" name="smtp_bcc" class="form-control" value="{{ $settings['smtp_bcc'] ?? '' }}"
                placeholder="auditoria@exemplo.com">
            <small class="form-text text-muted">Opcional. Separe por vírgulas.</small>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-paper-plane mr-2"></i> Teste de Envio</h5>
    <div class="form-group" style="max-width: 500px;">
        <label>Enviar e-mail de teste para:</label>
        <div class="input-group">
            <input type="email" id="smtp_test_email_input" class="form-control" placeholder="seu@email.com">
            <div class="input-group-append">
                <button type="button" id="btnTestSmtp" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i>
                    Testar Configuração</button>
            </div>
        </div>
        <small class="form-text text-muted">Salve as configurações antes de testar.</small>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Test SMTP
            $('#btnTestSmtp').click(function () {
                var btn = $(this);
                var originalText = btn.html();
                var email = $('#smtp_test_email_input').val();

                if (!email) {
                    toastr.warning('Digite um e-mail para teste');
                    return;
                }

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testando...');

                // Gather all SMTP fields
                var data = {
                    _token: '{{ csrf_token() }}',
                    smtp_host: $('[name="smtp_host"]').val(),
                    smtp_port: $('[name="smtp_port"]').val(),
                    smtp_encryption: $('[name="smtp_encryption"]').val(),
                    smtp_username: $('[name="smtp_username"]').val(),
                    smtp_password: $('[name="smtp_password"]').val(),
                    smtp_from_email: $('[name="smtp_from_email"]').val(),
                    smtp_from_name: $('[name="smtp_from_name"]').val(),
                    smtp_test_email: email
                };

                $.post('{{ route("admin.settings.test-smtp") }}', data)
                    .done(function (res) {
                        toastr.success(res.message);
                    })
                    .fail(function (xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao enviar e-mail';
                        toastr.error(msg);
                    })
                    .always(function () {
                        btn.prop('disabled', false).html(originalText);
                    });
            });
        });
    </script>
@endpush