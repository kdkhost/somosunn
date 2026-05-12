<?php

namespace App\Services\Waf;

use App\Models\Waf\WafRule;

/**
 * Serializa WafRules em JSON estavel (chaves ordenadas lexicograficamente).
 *
 * Garantia de round-trip (Property 1):
 *   serialize(parse(json)) == serialize(parse(serialize(parse(json))))
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisito: 20.1
 */
final class WafSerializer
{
    /**
     * Serializa uma WafRule em array associativo com chaves ordenadas.
     */
    public function serialize(WafRule $rule): array
    {
        $data = [
            'action'          => $rule->action,
            'attack_pattern'  => $rule->attack_pattern,
            'description'     => $rule->description ?? '',
            'is_active'       => (bool) $rule->is_active,
            'matcher_payload' => $this->sortRecursive((array) $rule->matcher_payload),
            'matcher_type'    => $rule->matcher_type,
            'name'            => $rule->name,
            'score'           => (int) $rule->score,
            'scope'           => $this->sortRecursive((array) $rule->scope),
            'severity'        => $rule->severity,
            'uid'             => $rule->uid,
        ];

        ksort($data);

        return $data;
    }

    /**
     * Serializa multiplas regras.
     *
     * @param iterable<WafRule> $rules
     * @return array<array>
     */
    public function serializeMany(iterable $rules): array
    {
        $out = [];

        foreach ($rules as $rule) {
            $out[] = $this->serialize($rule);
        }

        return $out;
    }

    /**
     * Converte para JSON string estavel.
     */
    public function toJson(WafRule $rule): string
    {
        return (string) json_encode(
            $this->serialize($rule),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Ordena chaves recursivamente para estabilidade.
     */
    private function sortRecursive(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Apenas ordena chaves se for array associativo
                if ($this->isAssoc($value)) {
                    ksort($value);
                }
                $data[$key] = $this->sortRecursive($value);
            }
        }

        if ($this->isAssoc($data)) {
            ksort($data);
        }

        return $data;
    }

    private function isAssoc(array $arr): bool
    {
        if (empty($arr)) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
