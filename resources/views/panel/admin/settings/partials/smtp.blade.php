<div class="space-y-6">
    <div
        class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700/50 rounded-2xl p-4 flex items-start gap-3 shadow-sm transition-all">
        <i class="fas fa-envelope text-blue-500 dark:text-blue-400 mt-1"></i>
        <div class="text-sm text-blue-800 dark:text-blue-100">
            Configure o servidor de e-mail (SMTP) para envio de notificações, recuperação de senha e boas-vindas.
        </div>
    </div>

    <!-- Server Config -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 transition-colors">
        <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-6">
            <i class="fas fa-server text-blue-500"></i> Configurações do Servidor
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Host
                    SMTP</label>
                <input type="text" name="smtp_host" value="{{ $settings['smtp_host'] ?? '' }}"
                    placeholder="smtp.exemplo.com"
                    class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div>
                <label
                    class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Porta</label>
                <input type="number" name="smtp_port" value="{{ $settings['smtp_port'] ?? '587' }}"
                    class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div>
                <label
                    class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Criptografia</label>
                <select name="smtp_encryption"
                    class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                    <option value="tls" {{ ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS
                        (Recomendado)</option>
                    <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL
                    </option>
                    <option value="null" {{ ($settings['smtp_encryption'] ?? '') === 'null' ? 'selected' : '' }}>Nenhuma
                    </option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Usuário
                    (E-mail)</label>
                <input type="text" name="smtp_username" value="{{ $settings['smtp_username'] ?? '' }}"
                    class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
            <div>
                <label
                    class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Senha</label>
                <div class="relative">
                    <input type="password" name="smtp_password" value="{{ $settings['smtp_password'] ?? '' }}"
                        class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white pr-10">
                    <button type="button" onclick="togglePassword(this)"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sender Identity -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 transition-colors">
        <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-6">
            <i class="fas fa-id-card-alt text-blue-500"></i> Identificação do Remetente
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">E-mail
                    Remetente
                    (From)</label>
                <input type="email" name="smtp_from_email" value="{{ $settings['smtp_from_email'] ?? '' }}"
                    class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 transition-colors">Geralmente o mesmo que o
                    usuário SMTP.</p>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Nome do
                    Remetente</label>
                <input type="text" name="smtp_from_name" value="{{ $settings['smtp_from_name'] ?? config('app.name') }}"
                    class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Cópia
                    (CC)</label>
                <input type="text" name="smtp_cc" value="{{ $settings['smtp_cc'] ?? '' }}"
                    placeholder="email@exemplo.com"
                    class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 transition-colors">Opcional. Separe por
                    vírgulas.</p>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 transition-colors">Cópia
                    Oculta
                    (BCC)</label>
                <input type="text" name="smtp_bcc" value="{{ $settings['smtp_bcc'] ?? '' }}"
                    placeholder="auditoria@exemplo.com"
                    class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 transition-colors">Opcional. Separe por
                    vírgulas.</p>
            </div>
        </div>
    </div>

    <!-- Test Connection -->
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 transition-colors">
        <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
            <i class="fas fa-paper-plane text-blue-500"></i> Teste de Envio
        </h3>

        <div class="max-w-xl">
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Enviar e-mail de teste
                para:</label>
            <div class="flex gap-2">
                <input type="email" id="smtp_test_email_input" placeholder="seu@email.com"
                    class="flex-1 rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium text-slate-800 dark:text-white">
                <button type="button" id="btnTestSmtp"
                    class="bg-slate-800 hover:bg-slate-900 dark:bg-blue-600 dark:hover:bg-blue-700 text-white rounded-2xl px-6 py-2 transition flex items-center gap-2 whitespace-nowrap shadow-md font-bold">
                    <i class="fas fa-paper-plane"></i> Testar
                </button>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Salve as configurações antes de testar.</p>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function togglePassword(btn) {
            const input = btn.previousElementSibling;
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('btnTestSmtp').addEventListener('click', function () {
            const btn = this;
            const originalContent = btn.innerHTML;
            const email = document.getElementById('smtp_test_email_input').value;

            if (!email) {
                alert('Digite um e-mail para teste');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando...';

            const form = document.querySelector('form');
            const formData = new FormData(form);
            formData.append('smtp_test_email', email);

            fetch('{{ route("admin.settings.test-smtp") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(({ status, body }) => {
                    if (status >= 200 && status < 300) {
                        alert(body.message || 'E-mail enviado com sucesso!');
                    } else {
                        alert(body.message || 'Erro ao enviar e-mail');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao processar requisição.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                });
        });
    </script>
@endpush