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
 * Este software, incluindo seu codigo-fonte, estrutura, banco de dados,
 * layout, funcionalidades, logica de programacao e documentacao associada,
 * e protegido pelas leis brasileiras de direitos autorais (Lei nº 9.610/98)
 * e demais legislacoes internacionais aplicaveis.
 *
 * -----------------------------------------------------------------------------
 * PROPRIEDADE INTELECTUAL:
 * Todo o conteudo deste sistema e de propriedade exclusiva do autor,
 * sendo proibida a reproducao total ou parcial, modificacao,
 * engenharia reversa, redistribuicao, sublicenciamento,
 * comercializacao ou qualquer forma de exploracao sem autorizacao
 * expressa e formal do titular dos direitos.
 *
 * -----------------------------------------------------------------------------
 * LICENCA DE USO:
 * Este sistema e licenciado, nao vendido.
 * O uso e restrito ao cliente contratante conforme contrato firmado.
 * E vedado o compartilhamento, revenda ou distribuicao a terceiros
 * sem autorizacao previa e documentada.
 *
 * -----------------------------------------------------------------------------
 * RESPONSABILIDADE:
 * Alteracoes realizadas por terceiros nao autorizados anulam qualquer
 * responsabilidade do autor sobre falhas, vulnerabilidades ou danos
 * decorrentes do uso indevido do sistema.
 *
 * -----------------------------------------------------------------------------
 * SEGURANCA E MONITORAMENTO:
 * Este software pode conter mecanismos de identificacao,
 * rastreamento de licenca e validacao de integridade para
 * protecao contra uso nao autorizado e pirataria.
 *
 * -----------------------------------------------------------------------------
 * PENALIDADES:
 * O uso indevido ou nao autorizado podera resultar em medidas legais
 * cabiveis nas esferas civil e criminal, incluindo indenizacoes por
 * perdas e danos.
 *
 * =============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('post_hides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['post_id', 'user_id']);
            $table->index(['user_id', 'post_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_hides');
    }
};
