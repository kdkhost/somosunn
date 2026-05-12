<?php

namespace App\Services\Waf\Scanners;

/**
 * Finding da auditoria de seguranca.
 *
 * Objeto imutavel com todas as informacoes necessarias para o relatorio
 * e para validacao automatica (Property 23 do design).
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 1.13, 1.14, 1.15, 23.1, 23.2, 23.4, 23.5
 */
class AuditFinding
{
    public const SEVERITY_INFO     = 'info';
    public const SEVERITY_LOW      = 'low';
    public const SEVERITY_MEDIUM   = 'medium';
    public const SEVERITY_HIGH     = 'high';
    public const SEVERITY_CRITICAL = 'critical';

    public const SEVERITIES = [
        self::SEVERITY_INFO,
        self::SEVERITY_LOW,
        self::SEVERITY_MEDIUM,
        self::SEVERITY_HIGH,
        self::SEVERITY_CRITICAL,
    ];

    /**
     * Area funcional (Auth, Uploads, Webhooks, Impersonacao, API,
     * Painel Admin, Painel Novo, Area Publica, Headers, Config, SQL, Blade, Outros).
     */
    public const AREAS = [
        'Auth',
        'Uploads',
        'Webhooks',
        'Impersonacao',
        'API',
        'Painel Admin',
        'Painel Novo',
        'Area Publica',
        'Headers',
        'Config',
        'SQL',
        'Blade',
        'Outros',
    ];

    public function __construct(
        public readonly string  $id,
        public readonly string  $category,
        public readonly string  $severity,
        public readonly string  $area,
        public readonly string  $title,
        public readonly string  $recommendation,
        public readonly ?string $file = null,
        public readonly ?int    $line = null,
        public readonly ?string $context = null,
        public readonly bool    $wafMitigable = false,
        public readonly ?string $compensatingControl = null,
        public readonly ?string $deadline = null,
    ) {
        // Os invariantes abaixo sao assegurados por construcao - o
        // SecurityAudit command valida antes de emitir (ver Property 23).
    }

    /**
     * Mapeia severidade para o prazo-alvo padrao do Requisito 23.2.
     */
    public static function defaultDeadline(string $severity): string
    {
        return match ($severity) {
            self::SEVERITY_CRITICAL => 'imediato',
            self::SEVERITY_HIGH     => '7d',
            self::SEVERITY_MEDIUM   => '30d',
            self::SEVERITY_LOW      => '90d',
            self::SEVERITY_INFO     => 'backlog',
            default                  => 'backlog',
        };
    }

    /**
     * Ordem numerica para ordenacao (mais severa primeiro).
     */
    public function severityWeight(): int
    {
        return match ($this->severity) {
            self::SEVERITY_CRITICAL => 5,
            self::SEVERITY_HIGH     => 4,
            self::SEVERITY_MEDIUM   => 3,
            self::SEVERITY_LOW      => 2,
            self::SEVERITY_INFO     => 1,
            default                  => 0,
        };
    }

    /**
     * Serializa o finding como array (para o relatorio JSON).
     */
    public function toArray(): array
    {
        return [
            'id'                    => $this->id,
            'category'              => $this->category,
            'severity'              => $this->severity,
            'area'                  => $this->area,
            'title'                 => $this->title,
            'recommendation'        => $this->recommendation,
            'file'                  => $this->file,
            'line'                  => $this->line,
            'context'               => $this->context,
            'waf_mitigable'         => $this->wafMitigable,
            'compensating_control'  => $this->compensatingControl,
            'deadline'              => $this->deadline ?? self::defaultDeadline($this->severity),
        ];
    }
}
