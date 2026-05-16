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
 * Sistema UNN - Unit tests para StorageProviderConfig (value object).
 *
 * Cobre:
 *   - construtor com defaults sensatos
 *   - isValid() exige access_key, secret_key, bucket e region preenchidos
 *   - maskedSecret() expoe so' os ultimos 4 caracteres
 *   - fromSettings() tolera chaves ausentes
 *   - toSettingsArray() produz exatamente as 7 chaves prefixadas
 *   - path_style aceita boolean, '1'/'0', 'true'/'false', etc.
 *
 * Spec: .kiro/specs/multi-provider-s3-storage (task 3.5 - parcial)
 * Requirements: 1.1, 1.3, 3.5, 4.5
 */

namespace Tests\Unit\Support;

use App\Support\StorageProviderConfig;
use Tests\TestCase;

class StorageProviderConfigTest extends TestCase
{
    /* -------- construtor -------- */

    public function test_default_constructor_returns_empty_invalid_config(): void
    {
        $config = new StorageProviderConfig();

        $this->assertSame('', $config->accessKey);
        $this->assertSame('', $config->secretKey);
        $this->assertSame('', $config->bucket);
        $this->assertSame('', $config->region);
        $this->assertSame('', $config->endpoint);
        $this->assertSame('', $config->url);
        $this->assertTrue($config->pathStyle, 'pathStyle default deve ser true');
        $this->assertFalse($config->isValid());
    }

    public function test_constructor_assigns_all_fields(): void
    {
        $config = new StorageProviderConfig(
            accessKey: 'AK',
            secretKey: 'SK',
            bucket: 'b',
            region: 'r',
            endpoint: 'e.example.com',
            url: 'https://cdn.example.com',
            pathStyle: false,
        );

        $this->assertSame('AK', $config->accessKey);
        $this->assertSame('SK', $config->secretKey);
        $this->assertSame('b', $config->bucket);
        $this->assertSame('r', $config->region);
        $this->assertSame('e.example.com', $config->endpoint);
        $this->assertSame('https://cdn.example.com', $config->url);
        $this->assertFalse($config->pathStyle);
    }

    /* -------- isValid -------- */

    public function test_is_valid_requires_all_four_required_fields(): void
    {
        $full = new StorageProviderConfig('A', 'B', 'C', 'D');
        $this->assertTrue($full->isValid());
    }

    public function test_is_valid_returns_false_when_any_required_field_is_blank(): void
    {
        // access_key vazio
        $this->assertFalse((new StorageProviderConfig('', 'B', 'C', 'D'))->isValid());
        // secret_key vazio
        $this->assertFalse((new StorageProviderConfig('A', '', 'C', 'D'))->isValid());
        // bucket vazio
        $this->assertFalse((new StorageProviderConfig('A', 'B', '', 'D'))->isValid());
        // region vazio
        $this->assertFalse((new StorageProviderConfig('A', 'B', 'C', ''))->isValid());
    }

    public function test_is_valid_treats_whitespace_only_as_empty(): void
    {
        $this->assertFalse((new StorageProviderConfig('   ', 'B', 'C', 'D'))->isValid());
        $this->assertFalse((new StorageProviderConfig('A', "\t", 'C', 'D'))->isValid());
        $this->assertFalse((new StorageProviderConfig('A', 'B', "\n", 'D'))->isValid());
    }

    public function test_is_valid_does_not_require_endpoint_url_or_path_style(): void
    {
        $config = new StorageProviderConfig('A', 'B', 'C', 'D', endpoint: '', url: '', pathStyle: false);
        $this->assertTrue($config->isValid());
    }

    /* -------- maskedSecret -------- */

    public function test_masked_secret_for_long_secret_keeps_only_last_four_chars(): void
    {
        $config = new StorageProviderConfig(secretKey: 'ABCDEFGHIJKLMNOP');

        $masked = $config->maskedSecret();

        $this->assertSame(strlen('ABCDEFGHIJKLMNOP'), strlen($masked));
        $this->assertStringEndsWith('MNOP', $masked);
        $this->assertStringStartsWith(str_repeat('*', 12), $masked);
    }

    public function test_masked_secret_for_short_secret_masks_everything(): void
    {
        $this->assertSame('***', (new StorageProviderConfig(secretKey: 'abc'))->maskedSecret());
        $this->assertSame('****', (new StorageProviderConfig(secretKey: 'abcd'))->maskedSecret());
    }

    public function test_masked_secret_for_empty_returns_empty_string(): void
    {
        $this->assertSame('', (new StorageProviderConfig(secretKey: ''))->maskedSecret());
    }

    /* -------- fromSettings -------- */

    public function test_from_settings_reads_all_seven_fields_with_prefix(): void
    {
        $settings = [
            'idrive_access_key' => 'AK',
            'idrive_secret_key' => 'SK',
            'idrive_bucket' => 'b',
            'idrive_region' => 'r',
            'idrive_endpoint' => 'e',
            'idrive_url' => 'u',
            'idrive_path_style' => '1',
        ];

        $config = StorageProviderConfig::fromSettings($settings, 'idrive_');

        $this->assertSame('AK', $config->accessKey);
        $this->assertSame('SK', $config->secretKey);
        $this->assertSame('b', $config->bucket);
        $this->assertSame('r', $config->region);
        $this->assertSame('e', $config->endpoint);
        $this->assertSame('u', $config->url);
        $this->assertTrue($config->pathStyle);
    }

    public function test_from_settings_tolerates_missing_keys_returning_empty_strings(): void
    {
        $config = StorageProviderConfig::fromSettings([], 'wasabi_');

        $this->assertSame('', $config->accessKey);
        $this->assertSame('', $config->secretKey);
        $this->assertSame('', $config->bucket);
        $this->assertSame('', $config->region);
        $this->assertSame('', $config->endpoint);
        $this->assertSame('', $config->url);
        // path_style ausente: usa default true
        $this->assertTrue($config->pathStyle);
        $this->assertFalse($config->isValid());
    }

    public function test_from_settings_isolates_by_prefix(): void
    {
        $settings = [
            'idrive_access_key' => 'IDRIVE_KEY',
            'idrive_secret_key' => 'IDRIVE_SECRET',
            'idrive_bucket' => 'idrive_bucket',
            'idrive_region' => 'idrive_region',
            'wasabi_access_key' => 'WASABI_KEY',
            'wasabi_bucket' => 'wasabi_bucket',
        ];

        $idrive = StorageProviderConfig::fromSettings($settings, 'idrive_');
        $wasabi = StorageProviderConfig::fromSettings($settings, 'wasabi_');

        $this->assertSame('IDRIVE_KEY', $idrive->accessKey);
        $this->assertSame('idrive_bucket', $idrive->bucket);

        $this->assertSame('WASABI_KEY', $wasabi->accessKey);
        $this->assertSame('wasabi_bucket', $wasabi->bucket);
        $this->assertSame('', $wasabi->secretKey, 'Wasabi nao deve herdar secret do IDrive');
    }

    /**
     * @dataProvider pathStyleTruthyProvider
     */
    public function test_from_settings_normalizes_path_style_truthy_values_to_true(mixed $value): void
    {
        $config = StorageProviderConfig::fromSettings(['p_path_style' => $value], 'p_');
        $this->assertTrue($config->pathStyle, 'pathStyle deveria ser true para ' . var_export($value, true));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function pathStyleTruthyProvider(): array
    {
        return [
            'string 1' => ['1'],
            'string true' => ['true'],
            'string TRUE' => ['TRUE'],
            'string yes' => ['yes'],
            'string on' => ['on'],
            'bool true' => [true],
            'int 1' => [1],
            'int 42' => [42],
        ];
    }

    /**
     * @dataProvider pathStyleFalsyProvider
     */
    public function test_from_settings_normalizes_path_style_falsy_values_to_false(mixed $value): void
    {
        $config = StorageProviderConfig::fromSettings(['p_path_style' => $value], 'p_');
        $this->assertFalse($config->pathStyle, 'pathStyle deveria ser false para ' . var_export($value, true));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function pathStyleFalsyProvider(): array
    {
        return [
            'string 0' => ['0'],
            'string false' => ['false'],
            'string FALSE' => ['FALSE'],
            'string no' => ['no'],
            'string off' => ['off'],
            'string vazio' => [''],
            'bool false' => [false],
            'int 0' => [0],
        ];
    }

    /* -------- toSettingsArray -------- */

    public function test_to_settings_array_produces_seven_prefixed_keys(): void
    {
        $config = new StorageProviderConfig(
            accessKey: 'AK',
            secretKey: 'SK',
            bucket: 'b',
            region: 'r',
            endpoint: 'e',
            url: 'u',
            pathStyle: true,
        );

        $arr = $config->toSettingsArray('aws_');

        $this->assertSame([
            'aws_access_key' => 'AK',
            'aws_secret_key' => 'SK',
            'aws_bucket' => 'b',
            'aws_region' => 'r',
            'aws_endpoint' => 'e',
            'aws_url' => 'u',
            'aws_path_style' => '1',
        ], $arr);
    }

    public function test_to_settings_array_serializes_path_style_false_as_zero_string(): void
    {
        $config = new StorageProviderConfig(pathStyle: false);
        $arr = $config->toSettingsArray('x_');

        $this->assertSame('0', $arr['x_path_style']);
    }

    public function test_round_trip_from_to_settings_preserves_values(): void
    {
        $original = new StorageProviderConfig('AK', 'SK', 'b', 'r', 'e', 'u', false);
        $arr = $original->toSettingsArray('p_');
        $back = StorageProviderConfig::fromSettings($arr, 'p_');

        $this->assertSame($original->accessKey, $back->accessKey);
        $this->assertSame($original->secretKey, $back->secretKey);
        $this->assertSame($original->bucket, $back->bucket);
        $this->assertSame($original->region, $back->region);
        $this->assertSame($original->endpoint, $back->endpoint);
        $this->assertSame($original->url, $back->url);
        $this->assertSame($original->pathStyle, $back->pathStyle);
    }
}
