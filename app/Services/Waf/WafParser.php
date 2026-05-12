<?php

namespace App\Services\Waf;

use App\Models\Waf\WafRule;

/**
 * Parseia JSON de WafRule e reconstroi o objeto.
 *
 * Rejeita JSON invalido com erro descritivo citando o campo ofensor
 * (Property 2).
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 20.2, 20.3, 10.5, 20.6
 */
final class WafParser
{
    private const REQUIRED_FIELDS = [
        'name', 'attack_pattern', 'matcher_type', 'matcher_payload', 'score', 'action', 'severity',
    ];

    private const VALID_MATCHER_TYPES = ['regex', 'list', 'numeric', 'function'];
    private const VALID_ACTIONS       = ['monitor', 'challenge', 'block'];
    private const VALID_SEVERITIES    = ['info', 'low', 'medium', 'high', 'critical'];

    /**
     * Parseia um array (decodificado de JSON) em WafRule.
     *
     * @throws WafParseException
     */
    public function parse(array $json): WafRule
    {
        // Campos obrigatorios
        foreach (self::REQUIRED_FIELDS as $field) {
            if (! array_key_exists($field, $json)) {
                throw new WafParseException("Campo obrigatorio ausente: '{$field}'");
            }
        }

        // Validacoes de tipo e enum
        if (! is_string($json['name']) || trim($json['name']) === '') {
            throw new WafParseException("Campo 'name' deve ser string nao vazia");
        }

        if (! is_string($json['attack_pattern']) || trim($json['attack_pattern']) === '') {
            throw new WafParseException("Campo 'attack_pattern' deve ser string nao vazia");
        }

        if (! in_array($json['matcher_type'], self::VALID_MATCHER_TYPES, true)) {
            throw new WafParseException(
                "Campo 'matcher_type' invalido: '{$json['matcher_type']}'. Valores aceitos: " . implode(', ', self::VALID_MATCHER_TYPES)
            );
        }

        if (! is_array($json['matcher_payload'])) {
            throw new WafParseException("Campo 'matcher_payload' deve ser um objeto/array");
        }

        if (! is_int($json['score']) && ! is_float($json['score'])) {
            throw new WafParseException("Campo 'score' deve ser numerico");
        }

        if ((int) $json['score'] < 0 || (int) $json['score'] > 100) {
            throw new WafParseException("Campo 'score' deve estar entre 0 e 100");
        }

        if (! in_array($json['action'], self::VALID_ACTIONS, true)) {
            throw new WafParseException(
                "Campo 'action' invalido: '{$json['action']}'. Valores aceitos: " . implode(', ', self::VALID_ACTIONS)
            );
        }

        if (! in_array($json['severity'], self::VALID_SEVERITIES, true)) {
            throw new WafParseException(
                "Campo 'severity' invalido: '{$json['severity']}'. Valores aceitos: " . implode(', ', self::VALID_SEVERITIES)
            );
        }

        // Validacao de regex (se matcher_type == regex)
        if ($json['matcher_type'] === 'regex') {
            $pattern = $json['matcher_payload']['pattern'] ?? null;
            if (! is_string($pattern) || $pattern === '') {
                throw new WafParseException("Campo 'matcher_payload.pattern' obrigatorio para matcher_type=regex");
            }

            $flags = $json['matcher_payload']['flags'] ?? '';
            $test  = '/' . str_replace('/', '\\/', $pattern) . '/' . $flags . 'u';
            if (@preg_match($test, '') === false) {
                throw new WafParseException("Campo 'matcher_payload.pattern' contem regex invalido: " . preg_last_error_msg());
            }
        }

        // Monta o model (sem persistir)
        $rule = new WafRule();
        $rule->uid             = $json['uid'] ?? strtoupper(\Illuminate\Support\Str::ulid()->toBase32());
        $rule->name            = trim($json['name']);
        $rule->description     = $json['description'] ?? '';
        $rule->attack_pattern  = trim($json['attack_pattern']);
        $rule->scope           = $json['scope'] ?? ['fields' => ['query', 'body', 'headers', 'path']];
        $rule->matcher_type    = $json['matcher_type'];
        $rule->matcher_payload = $json['matcher_payload'];
        $rule->score           = (int) $json['score'];
        $rule->action          = $json['action'];
        $rule->severity        = $json['severity'];
        $rule->is_active       = (bool) ($json['is_active'] ?? true);

        return $rule;
    }

    /**
     * Parseia multiplas regras e retorna ImportReport.
     *
     * @return ImportReport
     */
    public function parseMany(array $jsonArray): ImportReport
    {
        $accepted = [];
        $rejected = [];

        foreach ($jsonArray as $i => $item) {
            if (! is_array($item)) {
                $rejected[] = ['index' => $i, 'error' => 'Entrada nao e um objeto'];
                continue;
            }

            try {
                $rule = $this->parse($item);
                $accepted[] = $rule;
            } catch (WafParseException $e) {
                $rejected[] = ['index' => $i, 'name' => $item['name'] ?? '?', 'error' => $e->getMessage()];
            }
        }

        return new ImportReport($accepted, $rejected);
    }
}
