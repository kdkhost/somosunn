<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 *
 * Sistema UNN - Migration seed advanced_security settings
 *
 * Insere as chaves de configuracao usadas pelos modulos de seguranca
 * avancada e performance (queue, image processor, presigned URLs,
 * cache manager, rate limiter, audit logger, backup, CSP, log
 * rotator, anomaly detector). Todas com group = 'advanced_security'.
 *
 * Usa insertOrIgnore para evitar erro caso a chave ja exista, o que
 * permite re-rodar a migration de forma idempotente sem sobrescrever
 * valores ajustados pelo Superadmin via painel.
 *
 * Spec: .kiro/specs/advanced-security-performance
 * Requirements: 1.8, 2.8, 3.2, 4.8, 5.4, 5.5, 6.3, 7.6, 8.6, 10.6, 11.5
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    /**
     * Chaves de configuracao do modulo advanced-security-performance.
     *
     * @return array<string, string>
     */
    private function settings(): array
    {
        return [
            // Queue System (Requirement 1.8)
            'queue_retry_attempts'          => '3',
            'queue_timeout'                 => '60',

            // Image Processor (Requirement 2.8)
            'image_max_resolution'          => '2048',
            'image_jpeg_quality'            => '80',
            'image_webp_quality'            => '85',
            'image_thumb_sizes'             => '{"thumb":150,"medium":600,"large":1200}',

            // Presigned URLs (Requirement 3.2)
            'presigned_url_default_ttl'     => '60',
            'presigned_url_docs_ttl'        => '30',
            'presigned_url_media_ttl'       => '120',

            // Cache Manager (Requirement 4.8)
            'cache_ttl_navigation'          => '60',
            'cache_ttl_settings'            => '120',
            'cache_ttl_permissions'         => '30',
            'cache_ttl_dashboard'           => '5',
            'cache_ttl_heavy_query'         => '15',

            // Rate Limiter (Requirements 5.4, 5.5)
            'rate_limit_threshold'          => '100',
            'rate_limit_block_duration'     => '15',
            'rate_limit_block_increment'    => '5',
            'rate_limit_whitelist'          => '[]',
            'rate_limit_ua_patterns'        => '["sqlmap","nikto","acunetix","masscan","nmap","python-requests","gobuster","dirbuster","wpscan","nuclei"]',

            // Audit Logger (Requirement 6.3)
            'audit_retention_days'          => '90',

            // Backup (Requirement 7.6)
            'backup_keep_daily'             => '30',
            'backup_keep_weekly'            => '12',

            // CSP / Security Headers (Requirement 8.6)
            'csp_extra_allowlist'           => '{}',

            // Log Rotator (Requirement 10.6)
            'log_retention_days'            => '30',
            'log_security_retention'        => '90',

            // Anomaly Detector (Requirement 11.5)
            'anomaly_login_threshold'       => '10',
            'anomaly_upload_threshold'      => '20',
            'anomaly_webhook_threshold'     => '5',
            'anomaly_auto_block'            => '0',
        ];
    }

    public function up(): void
    {
        $now = Carbon::now();
        $rows = [];

        foreach ($this->settings() as $key => $value) {
            $rows[] = [
                'key'        => $key,
                'value'      => $value,
                'group'      => 'advanced_security',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // insertOrIgnore evita erro de chave duplicada caso a chave ja
        // tenha sido inserida (por seeder ou em ambiente parcialmente
        // migrado). Valores existentes nao sao sobrescritos.
        DB::table('settings')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        DB::table('settings')
            ->whereIn('key', array_keys($this->settings()))
            ->delete();
    }
};
