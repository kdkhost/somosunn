<?php
// Simula a logica de processamento de checkbox para entender o bug
// PHP lida com input HTML diferente conforme se é POST

// Caso 1: Checkbox DESMARCADO - só hidden é enviado
$dataDesmarcado = [
    'sumup_method_card' => '0'  // apenas hidden
];

// Caso 2: Checkbox MARCADO - ambos hidden e checkbox são enviados
// No PHP, o último value "vence" para nomes duplicados
$dataMarcado = [
    'sumup_method_card' => '1'  // último (checkbox) ganha
];

echo "=== Teste: has() vs input() ===\n\n";

// Simular Request::has()
$simulateHas = fn($data, $key) => isset($data[$key]) && $data[$key] !== null;

echo "CASO 1 (Desmarcado):\n";
echo "  has(): " . ($simulateHas($dataDesmarcado, 'sumup_method_card') ? 'true' : 'false') . "\n";
echo "  input(): {$dataDesmarcado['sumup_method_card']}\n";
echo "  Resultado (has): " . ($simulateHas($dataDesmarcado, 'sumup_method_card') ? 1 : 0) . " <-- BUG! Deveria ser 0\n";
echo "  Resultado (input): " . ((int) $dataDesmarcado['sumup_method_card'] === 1 ? 1 : 0) . " <-- CORRETO\n\n";

echo "CASO 2 (Marcado):\n";
echo "  has(): " . ($simulateHas($dataMarcado, 'sumup_method_card') ? 'true' : 'false') . "\n";
echo "  input(): {$dataMarcado['sumup_method_card']}\n";
echo "  Resultado (has): " . ($simulateHas($dataMarcado, 'sumup_method_card') ? 1 : 0) . " <-- OK\n";
echo "  Resultado (input): " . ((int) $dataMarcado['sumup_method_card'] === 1 ? 1 : 0) . " <-- OK\n";
