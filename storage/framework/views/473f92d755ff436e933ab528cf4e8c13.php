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



<?php $__env->startSection('title', 'Post de ' . $post->user->name); ?>
<?php $__env->startSection('meta_title', 'Post de ' . $post->user->name); ?>
<?php $__env->startSection('meta_description', $shareDescription); ?>
<?php $__env->startSection('meta_image', $shareImage); ?>
<?php $__env->startSection('twitter_image', $shareImage); ?>
<?php $__env->startSection('og_type', 'article'); ?>

<?php $__env->startSection('content'); ?>
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="rounded-full w-12 h-12 overflow-hidden flex-shrink-0">
                        <img src="<?php echo e($post->user->profile_photo_url); ?>" alt="Avatar" class="w-12 h-12 object-cover"
                            onerror="this.onerror=null;this.src='<?php echo e(asset('img/default-user.svg')); ?>';">
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900"><?php echo e($post->user->name); ?></h2>
                        <p class="text-xs text-gray-500"><?php echo e($post->created_at->diffForHumans()); ?></p>
                    </div>
                </div>

                <div class="prose max-w-none text-gray-800">
                    <?php echo nl2br(e($post->content)); ?>

                </div>

                <?php if($post->media->isNotEmpty()): ?>
                    <div class="mt-4">
                        <img src="<?php echo e(asset($post->media->first()->path)); ?>" alt="Midia do post"
                            class="w-full rounded-lg object-cover">
                    </div>
                <?php endif; ?>

                <div class="mt-6">
                    <a href="<?php echo e(route('social.feed')); ?>" class="text-blue-600 hover:underline">
                        Ver mais na comunidade
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\social\post_share.blade.php ENDPATH**/ ?>