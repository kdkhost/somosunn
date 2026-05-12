<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Regra de validação para política de senha forte.
 *
 * Exige:
 *   - Mínimo 8 caracteres (configurável)
 *   - Pelo menos 1 letra maiúscula
 *   - Pelo menos 1 letra minúscula
 *   - Pelo menos 1 número
 *   - Pelo menos 1 caractere especial
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 17.7, 17.8
 */
class StrongPassword implements Rule
{
    private int $minLength;
    private string $failReason = '';

    public function __construct(int $minLength = 8)
    {
        $this->minLength = $minLength;
    }

    public function passes($attribute, $value): bool
    {
        if (! is_string($value)) {
            $this->failReason = 'A senha deve ser uma string.';
            return false;
        }

        if (mb_strlen($value) < $this->minLength) {
            $this->failReason = "A senha deve ter no mínimo {$this->minLength} caracteres.";
            return false;
        }

        if (! preg_match('/[A-Z]/', $value)) {
            $this->failReason = 'A senha deve conter pelo menos uma letra maiúscula.';
            return false;
        }

        if (! preg_match('/[a-z]/', $value)) {
            $this->failReason = 'A senha deve conter pelo menos uma letra minúscula.';
            return false;
        }

        if (! preg_match('/[0-9]/', $value)) {
            $this->failReason = 'A senha deve conter pelo menos um número.';
            return false;
        }

        if (! preg_match('/[^A-Za-z0-9]/', $value)) {
            $this->failReason = 'A senha deve conter pelo menos um caractere especial.';
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->failReason ?: "A senha não atende aos requisitos de complexidade.";
    }
}
