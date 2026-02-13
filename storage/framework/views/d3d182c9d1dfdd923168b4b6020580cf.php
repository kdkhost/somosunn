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



<?php $__env->startSection('page_title', 'Meus Cursos'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $resolveImageUrl = function (?string $path): ?string {
            $path = trim((string) $path);
            if ($path === '') {
                return null;
            }

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }

            if (str_starts_with($path, 'storage/')) {
                return asset($path);
            }

            if (str_starts_with($path, 'uploads/')) {
                return asset($path);
            }

            return asset(ltrim($path, '/'));
        };
    ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Meus Cursos</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4">Aqui ficam todos os cursos que você comprou na plataforma. Cada usuário vê apenas as próprias compras.</p>

            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="O que voce deseja aprender hoje?"
                        value="<?php echo e($q ?? ''); ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>

            <div class="row">
                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $showParam = $item->slug ?: $item->id;
                        $thumbUrl = $resolveImageUrl($item->thumbnail ?? null);
                        $author = $item->author_name ?: (optional($item->creator)->name ?? 'Instrutor UNN');
                    ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <?php if($thumbUrl): ?>
                                <img src="<?php echo e($thumbUrl); ?>" class="card-img-top" alt="<?php echo e($item->title); ?>"
                                    style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-primary">
                                        <i class="fas fa-check-circle mr-1"></i> Acesso liberado
                                    </span>
                                    <span class="text-xs text-muted text-uppercase"><?php echo e($author); ?></span>
                                </div>

                                <h3 class="h6 font-weight-bold mb-2 text-dark"><?php echo e($item->title); ?></h3>
                                <p class="text-muted small">
                                    <?php echo e(\Illuminate\Support\Str::limit(strip_tags((string) $item->short_description), 140)); ?>

                                </p>

                                <div class="bg-light rounded p-2 text-center">
                                    <small class="d-block text-muted text-uppercase">Duracao</small>
                                    <span class="font-weight-bold"><?php echo e($item->duration ?: '---'); ?></span>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="<?php echo e(route('courses.show', $showParam)); ?>" class="btn btn-primary btn-block">
                                    <i class="fas fa-play-circle mr-1"></i> Acessar curso
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12 text-center py-4 text-muted">Você ainda não comprou nenhum curso.</div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-center mt-3">
                <?php echo e($items->links()); ?>

            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\courses\available.blade.php ENDPATH**/ ?>