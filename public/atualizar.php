<?php
/**
 * SomosUNN — Deploy via Browser
 * Acesse: https://somosunn.com.br/atualizar.php?token=somosunn_deploy_2026
 */

// ─── Autenticação ────────────────────────────────────────────────────────────
$secret = 'somosunn_deploy_2026';
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    if (preg_match('/^DEPLOY_TOKEN=(.+)$/m', file_get_contents($env_file), $m)) {
        $secret = trim($m[1]);
    }
}
if (!hash_equals($secret, (string)($_GET['token'] ?? ''))) {
    http_response_code(403);
    die('<pre style="color:red">403 Forbidden — token inválido.</pre>');
}

$base   = realpath(__DIR__ . '/..');
$output = [];

// ─── Helpers ─────────────────────────────────────────────────────────────────
function sh(string $cmd, string $cwd): string {
    if (!function_exists('proc_open')) return '(proc_open desabilitado)';
    $p = proc_open($cmd, [1 => ['pipe','w'], 2 => ['pipe','w']], $pipes, $cwd);
    if (!is_resource($p)) return "ERRO: proc_open falhou";
    $o = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    proc_close($p);
    return trim($o) ?: '(sem saída)';
}

function gh_raw(string $repo, string $branch, string $path): ?string {
    $url = "https://raw.githubusercontent.com/{$repo}/{$branch}/{$path}";
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_USERAGENT      => 'SomosUNN-Deploy/1.0',
        ]);
        $r = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($code === 200 && $r !== false) ? $r : null;
    }
    // fallback
    $ctx = stream_context_create(['http' => ['timeout' => 20, 'user_agent' => 'SomosUNN-Deploy/1.0']]);
    $r = @file_get_contents($url, false, $ctx);
    return $r !== false ? $r : null;
}

function clear_caches(string $base): array {
    $log = [];
    // View cache
    foreach (glob($base . '/storage/framework/views/*.php') ?: [] as $f) {
        if (@unlink($f)) $log[] = 'view cache: ' . basename($f);
    }
    // Bootstrap cache
    foreach (['config.php', 'routes.php', 'routes-v7.php', 'services.php'] as $c) {
        $f = $base . '/bootstrap/cache/' . $c;
        if (file_exists($f) && @unlink($f)) $log[] = 'bootstrap cache: ' . $c;
    }
    // Artisan (se disponível)
    try {
        $r = sh(PHP_BINARY . ' artisan optimize:clear', $base);
        $log[] = 'artisan optimize:clear: ' . $r;
    } catch (Throwable $e) {}
    // OPcache
    if (function_exists('opcache_reset')) { opcache_reset(); $log[] = 'opcache: resetado'; }
    return $log;
}

// ─── Arquivos a sincronizar via GitHub ───────────────────────────────────────
$repo   = 'kdkhost/somosunn';
$branch = 'main';
$files  = [
    'resources/views/panel/admin/settings/partials/gateway.blade.php',
    'resources/views/panel/marketplace/connect.blade.php',
    'app/Http/Controllers/Admin/SettingController.php',
    'routes/web.php',
    'public/atualizar.php',
];

// ─── 1) Tentar git pull ───────────────────────────────────────────────────────
$git = sh('git pull --rebase 2>&1', $base);
$git_ok = stripos($git, 'error') === false && stripos($git, 'fatal') === false;
$output['git pull'] = ['result' => $git, 'ok' => $git_ok];

// ─── 2) Se git falhou, baixar arquivo por arquivo do GitHub ──────────────────
if (!$git_ok) {
    $output['modo'] = ['result' => 'Git falhou — usando download direto do GitHub', 'ok' => true];
    foreach ($files as $rel) {
        $dest = $base . '/' . $rel;
        if (!is_dir(dirname($dest))) { $output[$rel] = ['result' => 'Diretório não existe: ' . dirname($dest), 'ok' => false]; continue; }
        $content = gh_raw($repo, $branch, $rel);
        if ($content === null) {
            $output[$rel] = ['result' => 'Falha ao baixar do GitHub (repo privado ou sem internet)', 'ok' => false];
        } else {
            file_put_contents($dest, $content);
            $output[$rel] = ['result' => 'Atualizado (' . strlen($content) . ' bytes)', 'ok' => true];
        }
    }
}

// ─── 3) Limpar caches ────────────────────────────────────────────────────────
$cache_log = clear_caches($base);
$output['cache clear'] = ['result' => implode("\n", $cache_log) ?: '(nada a limpar)', 'ok' => true];

?><!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><title>Deploy — SomosUNN</title>
<style>
  body { font: 14px 'Courier New', monospace; background: #0f172a; color: #e2e8f0; padding: 2rem; max-width: 900px; margin: auto; }
  h1 { color: #38bdf8; } h2 { color: #94a3b8; font-size: .75rem; letter-spacing: .1em; text-transform: uppercase; margin: 0 0 .25rem; }
  .box { background: #1e293b; border: 1px solid #334155; border-radius: 8px; margin-bottom: .75rem; overflow: hidden; }
  .box-head { padding: .4rem .75rem; font-weight: bold; font-size: .8rem; }
  .box-body { padding: .5rem .75rem; white-space: pre-wrap; font-size: .78rem; color: #94a3b8; }
  .ok .box-head { background: #166534; color: #bbf7d0; }
  .err .box-head { background: #7f1d1d; color: #fca5a5; }
  .done { margin-top: 1.5rem; padding: 1rem; background: #14532d; border: 1px solid #22c55e; border-radius: 8px; color: #86efac; text-align: center; font-size: 1rem; }
</style></head><body>
<h1>🚀 Deploy — SomosUNN</h1>
<p style="color:#64748b;font-size:.85rem">Executado em <?= date('d/m/Y H:i:s') ?></p>
<?php foreach ($output as $label => $info): ?>
<?php $cls = !empty($info['ok']) ? 'ok' : 'err'; ?>
<div class="box <?= $cls ?>">
  <div class="box-head"><?= htmlspecialchars($label) ?></div>
  <div class="box-body"><?= htmlspecialchars($info['result']) ?></div>
</div>
<?php endforeach; ?>
<div class="done">✅ Concluído. Atualize a página do admin para ver as mudanças.</div>
<p style="margin-top:1rem;font-size:.7rem;color:#475569">⚠️ Delete ou proteja este arquivo após usar.</p>
</body></html>