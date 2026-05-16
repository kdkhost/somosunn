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
 * Sistema UNN - Migration que prepara a tabela `settings` para o
 * suporte multi-provedor S3 (IDrive e2, Wasabi, AWS S3).
 *
 * Acoes:
 *   1) Cria 21 chaves vazias (7 por provedor) com defaults sensatos
 *      por provedor (Req 9.1, 9.2, 9.3).
 *   2) Cria a chave `storage_active_provider` com valor `idrive`.
 *   3) Migra os valores legados (`storage_*`) para o namespace
 *      `idrive_*` apenas quando o namespace IDrive ainda esta vazio.
 *   4) Idempotente: pode rodar varias vezes sem efeito colateral.
 *
 * Spec: .kiro/specs/multi-provider-s3-storage (task 1.1)
 * Requirements: 1.1, 7.1, 7.2, 7.5, 9.1, 9.2, 9.3
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Defaults por provedor. Apenas valores que fazem sentido como
     * sugestao inicial (Req 9): Wasabi tem endpoint regional default,
     * AWS sugere path_style=0, IDrive sugere path_style=1.
     *
     * @var array<string, array<string, string>>
     */
    private const PROVIDER_DEFAULTS = [
        'idrive_' => [
            'access_key' => '',
            'secret_key' => '',
            'bucket' => '',
            'region' => '',
            'endpoint' => '',
            'url' => '',
            'path_style' => '1',
        ],
        'wasabi_' => [
            'access_key' => '',
            'secret_key' => '',
            'bucket' => '',
            'region' => 'us-east-1',
            'endpoint' => 's3.us-east-1.wasabisys.com',
            'url' => '',
            'path_style' => '1',
        ],
        'aws_' => [
            'access_key' => '',
            'secret_key' => '',
            'bucket' => '',
            'region' => 'us-east-1',
            'endpoint' => '',
            'url' => '',
            'path_style' => '0',
        ],
    ];

    /**
     * Campos do schema legado (`storage_*`) -> sufixo no namespace
     * IDrive (`idrive_*`).
     *
     * @var array<string, string>
     */
    private const LEGACY_TO_IDRIVE_MAP = [
        'storage_access_key' => 'access_key',
        'storage_secret_key' => 'secret_key',
        'storage_bucket' => 'bucket',
        'storage_region' => 'region',
        'storage_endpoint' => 'endpoint',
        'storage_url' => 'url',
        'storage_path_style' => 'path_style',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            // Sem tabela settings nao ha nada a fazer; outras migrations
            // criam a tabela antes desta.
            return;
        }

        try {
            $this->seedProviderDefaults();
            $this->seedActiveProvider();
            $this->migrateLegacyToIdrive();
        } catch (\Throwable $e) {
            // Req 7.3: preservar configuracoes legadas inalteradas em caso
            // de erro de leitura/dados corrompidos. Logamos e seguimos sem
            // falhar a migration (compatibilidade).
            Log::warning('seed_multi_provider_s3 migration failed silently: ' . $e->getMessage(), [
                'exception' => get_class($e),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        // Reversao remove APENAS as chaves criadas por esta migration.
        // Nunca toca em `storage_*` (legado), preservando o estado anterior.
        $keysToRemove = [];
        foreach (self::PROVIDER_DEFAULTS as $prefix => $fields) {
            foreach (array_keys($fields) as $field) {
                $keysToRemove[] = $prefix . $field;
            }
        }
        $keysToRemove[] = 'storage_active_provider';

        DB::table('settings')->whereIn('key', $keysToRemove)->delete();
    }

    /**
     * Cria 21 chaves vazias (7 por provedor) com defaults sensatos.
     * Idempotente: chaves ja existentes (com qualquer valor) sao
     * preservadas sem alteracao.
     */
    private function seedProviderDefaults(): void
    {
        $now = now();
        $existingKeys = $this->existingSettingKeys();

        $rows = [];
        foreach (self::PROVIDER_DEFAULTS as $prefix => $fields) {
            foreach ($fields as $field => $defaultValue) {
                $key = $prefix . $field;
                if (in_array($key, $existingKeys, true)) {
                    continue;
                }
                $rows[] = $this->buildSettingRow($key, $defaultValue, $now);
            }
        }

        if ($rows !== []) {
            DB::table('settings')->insert($rows);
        }
    }

    /**
     * Garante a chave `storage_active_provider`. Default: 'idrive'
     * (Req 7.5). Se ja existir, NAO sobrescreve.
     */
    private function seedActiveProvider(): void
    {
        $existingKeys = $this->existingSettingKeys();
        if (in_array('storage_active_provider', $existingKeys, true)) {
            return;
        }

        DB::table('settings')->insert(
            $this->buildSettingRow('storage_active_provider', 'idrive', now())
        );
    }

    /**
     * Copia valores legados (`storage_access_key`, etc.) para o
     * namespace `idrive_*` quando este ainda esta vazio (Req 7.1).
     *
     * Lemos o estado atual da tabela e atualizamos apenas as chaves
     * IDrive cujo valor esta vazio. Idempotente por construcao.
     */
    private function migrateLegacyToIdrive(): void
    {
        // Carrega de uma vez todas as chaves relevantes (idrive_* e storage_*)
        $relevantKeys = array_merge(
            array_map(fn ($field) => 'idrive_' . $field, array_keys(self::LEGACY_TO_IDRIVE_MAP)),
            array_keys(self::LEGACY_TO_IDRIVE_MAP)
        );

        $rows = DB::table('settings')
            ->whereIn('key', $relevantKeys)
            ->get(['key', 'value'])
            ->keyBy('key');

        foreach (self::LEGACY_TO_IDRIVE_MAP as $legacyKey => $idriveSuffix) {
            $idriveKey = 'idrive_' . $idriveSuffix;

            $idriveValue = isset($rows[$idriveKey])
                ? trim((string) $rows[$idriveKey]->value)
                : '';

            // Pula se o IDrive ja esta preenchido.
            if ($idriveValue !== '') {
                continue;
            }

            $legacyValue = isset($rows[$legacyKey])
                ? (string) $rows[$legacyKey]->value
                : '';

            // Pula se nao ha valor legado.
            if (trim($legacyValue) === '') {
                continue;
            }

            DB::table('settings')
                ->where('key', $idriveKey)
                ->update([
                    'value' => $legacyValue,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Retorna a lista de chaves ja presentes na tabela settings.
     *
     * @return array<int, string>
     */
    private function existingSettingKeys(): array
    {
        return DB::table('settings')->pluck('key')->all();
    }

    /**
     * Monta uma linha de settings com tolerancia a colunas opcionais
     * (group, label, type, etc.) que podem variar entre seeders
     * legados. Apenas key/value/timestamps sao garantidos.
     *
     * @return array<string, mixed>
     */
    private function buildSettingRow(string $key, string $value, $now): array
    {
        $row = [
            'key' => $key,
            'value' => $value,
            'updated_at' => $now,
        ];

        // Define created_at quando a coluna existir.
        if (Schema::hasColumn('settings', 'created_at')) {
            $row['created_at'] = $now;
        }

        // Define group quando a coluna existir.
        if (Schema::hasColumn('settings', 'group')) {
            $row['group'] = 'storage';
        }

        return $row;
    }
};
