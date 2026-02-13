<?php
/**
 * =============================================================================
 * AVISO LEGAL DE DIREITOS AUTORAIS E PROPRIEDADE INTELECTUAL
 * =============================================================================
 *
 * © 2026 Marcelo Brad - Todos os direitos reservados.
 *
 * AUTOR:
 * marcelo-brad rj
 *
 * CONTATO:
 * Tel: +55 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 * WhatsApp: +55 21 98132-5441
 *
 * -----------------------------------------------------------------------------
 * DIREITOS AUTORAIS:
 * Este software, incluindo seu código-fonte, estrutura, banco de dados,
 * layout, funcionalidades, lógica de programação e documentação associada,
 * é protegido pelas leis brasileiras de direitos autorais (Lei nº 9.610/98)
 * e demais legislações internacionais aplicáveis.
 *
 * -----------------------------------------------------------------------------
 * PROPRIEDADE INTELECTUAL:
 * Todo o conteúdo deste sistema é de propriedade exclusiva do autor,
 * sendo proibida a reprodução total ou parcial, modificação,
 * engenharia reversa, redistribuição, sublicenciamento,
 * comercialização ou qualquer forma de exploração sem autorização
 * expressa e formal do titular dos direitos.
 *
 * -----------------------------------------------------------------------------
 * LICENÇA DE USO:
 * Este sistema é licenciado, não vendido.
 * O uso é restrito ao cliente contratante conforme contrato firmado.
 * É vedado o compartilhamento, revenda ou distribuição a terceiros
 * sem autorização prévia e documentada.
 *
 * -----------------------------------------------------------------------------
 * RESPONSABILIDADE:
 * Alterações realizadas por terceiros não autorizados anulam qualquer
 * responsabilidade do autor sobre falhas, vulnerabilidades ou danos
 * decorrentes do uso indevido do sistema.
 *
 * -----------------------------------------------------------------------------
 * SEGURANÇA E MONITORAMENTO:
 * Este software pode conter mecanismos de identificação,
 * rastreamento de licença e validação de integridade para
 * proteção contra uso não autorizado e pirataria.
 *
 * -----------------------------------------------------------------------------
 * PENALIDADES:
 * O uso indevido ou não autorizado poderá resultar em medidas legais
 * cabíveis nas esferas civil e criminal, incluindo indenizações por
 * perdas e danos.
 *
 * =============================================================================
 */
?>



<?php $__env->startSection('title', 'Perfil Privado - UNN'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-4xl mx-auto py-20 px-4 text-center">
        <div class="bg-white rounded-3xl shadow-xl p-10 border border-slate-100">
            <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-400">
                <i class="fas fa-lock text-4xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 mb-4">Este perfil é privado</h2>
            <p class="text-slate-600 mb-8 max-w-md mx-auto">
                Você precisa estar conectado com <strong><?php echo e($user->name); ?></strong> para ver as publicações e informações
                detalhadas.
            </p>

            <div class="flex justify-center gap-4">
                <a href="<?php echo e(route('social.feed')); ?>"
                    class="px-6 py-3 bg-slate-100 text-slate-700 rounded-full font-bold hover:bg-slate-200 transition">
                    Voltar ao Feed
                </a>

                <?php
                    $pending = auth()->user()->hasPendingConnectionWith($user->id);
                    $isRequester = $pending && $pending->requester_id === auth()->id();
                    $pendingTime = $pending ? $pending->created_at->diffForHumans() : '';
                ?>

                <?php if($pending): ?>
                    <div class="text-center">
                        <div class="text-sm text-slate-500">Pendente <?php echo e($pendingTime); ?></div>
                        <?php if($isRequester): ?>
                            <button type="button" onclick="cancelInvite(<?php echo e($user->id); ?>)"
                                class="mt-2 px-6 py-3 bg-red-100 text-red-600 rounded-full font-bold hover:bg-red-200 transition">
                                Cancelar convite
                            </button>
                        <?php else: ?>
                            <div class="mt-2 flex flex-wrap justify-center gap-3">
                                <button type="button" onclick="acceptInvite(<?php echo e($user->id); ?>)"
                                    class="px-6 py-3 bg-green-600 text-white rounded-full font-bold hover:bg-green-700 transition">
                                    Aceitar
                                </button>
                                <button type="button" onclick="cancelInvite(<?php echo e($user->id); ?>)"
                                    class="px-6 py-3 bg-slate-100 text-slate-700 rounded-full font-bold hover:bg-slate-200 transition">
                                    Recusar
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <button type="button" onclick="requestInvite(<?php echo e($user->id); ?>)"
                        class="px-8 py-3 bg-[#1F5EDB] text-white rounded-full font-bold hover:bg-blue-700 transition shadow-lg">
                        Conectar agora
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            const csrfToken = '<?php echo e(csrf_token()); ?>';

            function requestInvite(userId) {
                Swal.fire({
                    title: 'Conectar com este membro?',
                    text: 'Voce enviara uma solicitacao de conexao.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1F5EDB',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sim, conectar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    fetch(`/connect/${userId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Enviado!', data.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Ops!', data.message, 'warning');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Ops!', 'Erro ao conectar.', 'error');
                        });
                });
            }

            function cancelInvite(userId) {
                Swal.fire({
                    title: 'Cancelar solicitacao?',
                    text: 'Voce deseja cancelar o convite?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sim, cancelar',
                    cancelButtonText: 'Voltar'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    fetch(`/connection/remove/${userId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json'
                        }
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Cancelado!', data.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Ops!', data.message, 'warning');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Ops!', 'Erro ao cancelar.', 'error');
                        });
                });
            }

            function acceptInvite(userId) {
                fetch(`/connection/accept/${userId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Conexao aceita!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Ops!', data.message, 'warning');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Ops!', 'Erro ao aceitar.', 'error');
                    });
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\social\profile_private.blade.php ENDPATH**/ ?>