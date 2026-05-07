#!/bin/bash
# Script para corrigir a migração do SumUp no servidor de produção

echo "=== Corrigindo migração SumUp ==="
echo ""

# Fazer backup do arquivo original
echo "1. Fazendo backup do arquivo de migração..."
cp database/migrations/2026_04_23_000004_add_sumup_subscription_id_to_subscriptions_table.php database/migrations/2026_04_23_000004_add_sumup_subscription_id_to_subscriptions_table.php.bak

# Corrigir o arquivo de migração
echo "2. Corrigindo o arquivo de migração..."
cat > database/migrations/2026_04_23_000004_add_sumup_subscription_id_to_subscriptions_table.php << 'EOF'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('sumup_subscription_id')->nullable()->after('next_billing_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('sumup_subscription_id');
        });
    }
};
EOF

# Fazer rollback da última migração que falhou
echo ""
echo "3. Fazendo rollback da migração que falhou..."
php artisan migrate:rollback --step=1

echo ""
echo "4. Executando a migração corrigida..."
php artisan migrate

echo ""
echo "5. Verificando se a tabela sum_up_transactions existe..."
php artisan db:table sum_up_transactions 2>/dev/null && echo "✓ Tabela sum_up_transactions existe" || echo "✗ Tabela sum_up_transactions NÃO existe"

echo ""
echo "=== Concluído ==="
echo ""
echo "Agora você pode testar o checkout com SumUp!"
