<?php

namespace App\Models\Waf;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Configuração do WAF (chave/valor) gerenciada pelo painel do superadmin.
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 15.6, 22.1
 */
class WafSetting extends Model
{
    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'waf_settings';
    protected $primaryKey = 'key';
    protected $keyType    = 'string';

    protected $fillable = [
        'key',
        'value',
        'updated_by',
        'updated_at',
    ];

    protected $casts = [
        'value'      => 'array',
        'updated_at' => 'datetime',
    ];

    /**
     * Atalho para ler uma chave da tabela (fallback para config/waf.php quando ausente).
     */
    public static function getValue(string $key, $default = null)
    {
        $row = self::query()->find($key);

        return $row ? $row->value : $default;
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
