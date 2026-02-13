<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Não é necessário alterar a tabela, pois Setting é key-value.
        // Apenas insere o valor default se não existir.
        if (!DB::table('settings')->where('key', 'marketplace_platform_fee_percent')->exists()) {
            DB::table('settings')->insert([
                'key' => 'marketplace_platform_fee_percent',
                'value' => '10', // valor padrão: 10%
                'group' => 'marketplace',
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'marketplace_platform_fee_percent')->delete();
    }
};
