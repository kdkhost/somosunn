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

    <h5 class="text-primary mb-3"><i class="fas fa-tasks mr-2"></i> Regras da Fila de E-mails</h5>
    <div class="row">
        <div class="col-md-6 form-group">
            <label>Modo de disparo</label>
            <select name="email_dispatch_mode" class="form-control">
                <option value="sync" {{ ($settings['email_dispatch_mode'] ?? 'sync') === 'sync' ? 'selected' : '' }}>Imediato (recomendado)</option>
                <option value="queue" {{ ($settings['email_dispatch_mode'] ?? '') === 'queue' ? 'selected' : '' }}>Enfileirado</option>
            </select>
            <small class="form-text text-muted">Imediato evita e-mails presos quando não há worker ativo.</small>
        </div>
        <div class="col-md-3 form-group">
            <label>Conexão</label>
            @php($queueConnection = $settings['email_queue_connection'] ?? 'database')
            <select name="email_queue_connection" class="form-control">
                <option value="database" {{ $queueConnection === 'database' ? 'selected' : '' }}>database</option>
                <option value="redis" {{ $queueConnection === 'redis' ? 'selected' : '' }}>redis</option>
                <option value="sqs" {{ $queueConnection === 'sqs' ? 'selected' : '' }}>sqs</option>
                <option value="beanstalkd" {{ $queueConnection === 'beanstalkd' ? 'selected' : '' }}>beanstalkd</option>
                <option value="sync" {{ $queueConnection === 'sync' ? 'selected' : '' }}>sync</option>
            </select>
        </div>
        <div class="col-md-3 form-group">
            <label>Fila (nome)</label>
            <input type="text" name="email_queue_name" class="form-control"
                value="{{ $settings['email_queue_name'] ?? 'emails' }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 form-group">
            <label>Delay (seg.)</label>
            <input type="number" min="0" max="3600" name="email_queue_delay_seconds" class="form-control"
                value="{{ $settings['email_queue_delay_seconds'] ?? '0' }}">
        </div>
        <div class="col-md-3 form-group">
            <label>Tentativas</label>
            <input type="number" min="1" max="10" name="email_queue_tries" class="form-control"
                value="{{ $settings['email_queue_tries'] ?? '3' }}">
        </div>
        <div class="col-md-3 form-group">
            <label>Timeout (seg.)</label>
            <input type="number" min="30" max="900" name="email_queue_timeout" class="form-control"
                value="{{ $settings['email_queue_timeout'] ?? '120' }}">
        </div>
        <div class="col-md-3 form-group">
            <label>Sleep (seg.)</label>
            <input type="number" min="1" max="10" name="email_queue_sleep" class="form-control"
                value="{{ $settings['email_queue_sleep'] ?? '1' }}">
        </div>
    </div>

    <div class="custom-control custom-switch mb-3">
        <input type="hidden" name="email_queue_schedule_enabled" value="0">
        <input type="checkbox" class="custom-control-input" id="email_queue_schedule_enabled"
            name="email_queue_schedule_enabled" value="1" {{ ($settings['email_queue_schedule_enabled'] ?? 1) ? 'checked' : '' }}>
        <label class="custom-control-label" for="email_queue_schedule_enabled">Ativar processamento automático da fila (scheduler)</label>
        <small class="form-text text-muted">Funciona com cron do servidor ou com o cron interno do sistema (quando houver tráfego).</small>
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
                var form = btn.closest('form');
                if (form.length === 0) {
                    // Fallback if not inside a form
                    form = btn.closest('.card-body');
                }

                var data = {
                    _token: '{{ csrf_token() }}',
                    smtp_host: form.find('[name="smtp_host"]').val(),
                    smtp_port: form.find('[name="smtp_port"]').val(),
                    smtp_encryption: form.find('[name="smtp_encryption"]').val(),
                    smtp_username: form.find('[name="smtp_username"]').val(),
                    smtp_password: form.find('[name="smtp_password"]').val(),
                    smtp_from_email: form.find('[name="smtp_from_email"]').val(),
                    smtp_from_name: form.find('[name="smtp_from_name"]').val(),
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
