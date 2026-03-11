<?php
/**
 * Script de deploy manual via navegador.
 * Acesse: https://somosunn.com.br/atualizar.php?token=SEU_TOKEN
 *
 * IMPORTANTE: defina um token secreto no .env:
 *   DEPLOY_TOKEN=coloque_uma_senha_forte_aqui
 * Se não existir .env acessível, edite $fallback_token abaixo.
 */

$fallback_token = 'somosunn_deploy_2026'; // token padrão se não houver .env

// Tenta ler token do .env do Laravel (2 níveis acima de public/)
$env_token = null;
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $env = file_get_contents($env_file);
    if (preg_match('/^DEPLOY_TOKEN=(.+)$/m', $env, $m)) {
        $env_token = trim($m[1]);
    }
}
$secret = $env_token ?? $fallback_token;

// Validação do token
$provided = $_GET['token'] ?? '';
if (!hash_equals($secret, $provided)) {
    http_response_code(403);
    die('<h2 style="font-family:monospace;color:red">403 - Token inválido.</h2>');
}

// Caminho raiz do projeto (um nível acima de public/)
$base = realpath(__DIR__ . '/..');

$output = [];
$errors = [];

function run(string $cmd, string $cwd): string {
    $desc = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $proc = proc_open($cmd, $desc, $pipes, $cwd);
    if (!is_resource($proc)) return "ERRO: proc_open falhou em [$cmd]";
    $out = stream_get_contents($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);
    return trim($out . ($err ? "\n[stderr] " . $err : ''));
}

// Detectar caminho do PHP
$php = PHP_BINARY ?: 'php';

$steps = [
    'git pull --rebase'                              => "git pull --rebase",
    'php artisan optimize:clear'                     => "$php artisan optimize:clear",
    'php artisan config:cache'                       => "$php artisan config:cache",
    'php artisan route:cache'                        => "$php artisan route:cache",
    'php artisan view:cache'                         => "$php artisan view:cache",
];

foreach ($steps as $label => $cmd) {
    $result = run($cmd, $base);
    $output[$label] = $result;
}

// OPcache reset
if (function_exists('opcache_reset')) {
    opcache_reset();
    $output['opcache_reset'] = 'OK';
}

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Deploy — SomosUNN</title>
<style>
  body { font-family: 'Courier New', monospace; background: #0f172a; color: #e2e8f0; padding: 2rem; }
  h1 { color: #38bdf8; margin-bottom: 1.5rem; }
  .step { background: #1e293b; border: 1px solid #334155; border-radius: 8px; margin-bottom: 1rem; overflow: hidden; }
  .step-header { background: #0ea5e9; color: white; padding: .5rem 1rem; font-weight: bold; font-size: .85rem; }
  .step-body { padding: .75rem 1rem; white-space: pre-wrap; font-size: .8rem; color: #94a3b8; }
  .ok { border-color: #22c55e; }
  .ok .step-header { background: #16a34a; }
  .err .step-header { background: #dc2626; }
  .done { margin-top: 2rem; padding: 1rem; background: #14532d; border: 1px solid #22c55e; border-radius: 8px; color: #86efac; font-size: 1.1rem; text-align: center; }
</style>
</head>
<body>
<h1>🚀 Deploy — SomosUNN</h1>
<?php foreach ($output as $label => $result): ?>
<?php $cls = (stripos($result, 'erro') !== false || stripos($result, 'fatal') !== false || stripos($result, 'error') !== false) ? 'err' : 'ok'; ?>
<div class="step <?= $cls ?>">
  <div class="step-header"><?= htmlspecialchars($label) ?></div>
  <div class="step-body"><?= htmlspecialchars($result ?: '(sem saída)') ?></div>
</div>
<?php endforeach; ?>
<div class="done">✅ Deploy concluído em <?= date('d/m/Y H:i:s') ?></div>
</body>
</html>
