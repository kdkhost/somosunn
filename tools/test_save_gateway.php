<?php
// Teste: simula um POST para salvar settings do gateway com algum campo desmarcado
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

echo "=== TESTE DE SALVAMENTO DE CHECKBOX ===\n\n";

// Ler valor ATUAL
$before = Setting::where('key', 'sumup_method_pix')->value('value');
echo "ANTES: sumup_method_pix = {$before}\n";

// Simular "desmarcar" o checkbox (salvar valor 0)
Setting::updateOrCreate(['key' => 'sumup_method_pix'], ['value' => '0']);
echo "APOS salvar 0: sumup_method_pix = " . Setting::where('key', 'sumup_method_pix')->value('value') . "\n";

// Simular "marcar" o checkbox (salvar valor 1)
Setting::updateOrCreate(['key' => 'sumup_method_pix'], ['value' => '1']);
echo "APOS salvar 1: sumup_method_pix = " . Setting::where('key', 'sumup_method_pix')->value('value') . "\n";

// Restaurar ao valor original
Setting::updateOrCreate(['key' => 'sumup_method_pix'], ['value' => $before]);
echo "RESTAURADO: sumup_method_pix = {$before}\n";

echo "\nSe o TESTE passou (valores mudaram), o banco aceita updates.\n";
echo "Se o site nao salva, o bug esta no controller (que foi corrigido).\n";
