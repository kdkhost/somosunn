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

namespace App\Services;

use App\Models\User;

class ProfilePhotoService
{
    public function urlFor(?User $user): string
    {
        if (!$user || !$user->photo) {
            return asset('img/default-user.svg');
        }

        $photo = $user->photo;

        if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
            return $photo;
        }

        return asset($photo);
    }
}
