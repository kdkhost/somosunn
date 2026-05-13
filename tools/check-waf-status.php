<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== Status do WAF ===" . PHP_EOL;
echo "WAF habilitado (config): " . (config('waf.enabled') ? 'SIM' : 'NAO') . PHP_EOL;
echo "Modo: " . config('waf.mode') . PHP_EOL;
echo "Fail policy: " . config('waf.fail_policy') . PHP_EOL;
echo PHP_EOL;

echo "=== Banco de Dados ===" . PHP_EOL;
echo "Tabela waf_rules: " . (Schema::hasTable('waf_rules') ? 'EXISTE' : 'NAO EXISTE') . PHP_EOL;
echo "Tabela waf_events: " . (Schema::hasTable('waf_events') ? 'EXISTE' : 'NAO EXISTE') . PHP_EOL;

if (Schema::hasTable('waf_rules')) {
    $total = App\Models\Waf\WafRule::count();
    $active = App\Models\Waf\WafRule::where('is_active', true)->where('quarantined', false)->count();
    echo "Regras totais: {$total}" . PHP_EOL;
    echo "Regras ativas: {$active}" . PHP_EOL;
}

if (Schema::hasTable('waf_events')) {
    $events = App\Models\Waf\WafEvent::count();
    echo "Eventos registrados: {$events}" . PHP_EOL;
}

echo PHP_EOL;
echo "=== Teste de Inspecao ===" . PHP_EOL;

// Simular uma requisicao com payload SQLi
$request = Illuminate\Http\Request::create('/?q=UNION+SELECT+1,2,3--', 'GET');
$request->headers->set('User-Agent', 'Mozilla/5.0 Test');

try {
    $settings = App\Services\Waf\WafSettings::load();
    echo "Settings carregadas: SIM (modo={$settings->mode}, enabled=" . ($settings->enabled ? 'true' : 'false') . ")" . PHP_EOL;

    if (!$settings->enabled) {
        echo "PROBLEMA: WAF desabilitado nas settings do banco!" . PHP_EOL;
        echo "Verificando waf_settings..." . PHP_EOL;
        if (Schema::hasTable('waf_settings')) {
            $row = DB::table('waf_settings')->where('key', 'waf.enabled')->first();
            echo "waf.enabled no banco: " . ($row ? json_encode($row->value) : 'NAO ENCONTRADO') . PHP_EOL;
        }
    } else {
        echo "Fail policy: " . $settings->failPolicy . PHP_EOL;
        echo "isFailOpen: " . ($settings->isFailOpen() ? 'SIM' : 'NAO') . PHP_EOL;

        // Testar diretamente sem try/catch do engine para ver o erro real
        try {
            $engine = App\Services\Waf\WafEngine::make($settings);

            // Testar manualmente cada etapa
            $ruleRepo = new App\Services\Waf\WafRuleRepository();
            $rules = $ruleRepo->allActive();
            echo "Regras carregadas do repo: " . $rules->count() . PHP_EOL;

            $ctx = App\Services\Waf\WafContext::fromRequest($request);
            echo "Contexto criado: IP={$ctx->ip}, path={$ctx->path}, scope={$ctx->scope}" . PHP_EOL;

            // Testar matching manual
            $matchers = [
                'regex' => new App\Services\Waf\Matchers\RegexRuleMatcher(),
                'list' => new App\Services\Waf\Matchers\ListRuleMatcher(),
                'numeric' => new App\Services\Waf\Matchers\NumericRuleMatcher(),
                'function' => new App\Services\Waf\Matchers\FunctionRuleMatcher(),
            ];

            $matchCount = 0;
            foreach ($rules as $rule) {
                $matcher = $matchers[$rule->matcher_type] ?? null;
                if (!$matcher) continue;
                try {
                    $match = $matcher->evaluate($rule, $ctx);
                    if ($match) {
                        echo "  MATCH: {$rule->name} (score: {$match->score})" . PHP_EOL;
                        $matchCount++;
                    }
                } catch (\Throwable $re) {
                    echo "  ERRO na regra {$rule->name}: {$re->getMessage()}" . PHP_EOL;
                }
            }
            echo "Total matches: {$matchCount}" . PHP_EOL;

        } catch (\Throwable $e2) {
            echo "ERRO DIRETO: " . $e2->getMessage() . PHP_EOL;
            echo "Arquivo: " . $e2->getFile() . ":" . $e2->getLine() . PHP_EOL;
        }

        // Agora testar via engine completo
        $engine = App\Services\Waf\WafEngine::make($settings);
        $decision = $engine->inspect($request);
        echo "Decisao: {$decision->decision}" . PHP_EOL;
        echo "Risk Score: {$decision->riskScore}" . PHP_EOL;
        echo "Razao: {$decision->reason}" . PHP_EOL;
        echo "Regras disparadas: " . count($decision->rules) . PHP_EOL;
        if (!empty($decision->rules)) {
            foreach ($decision->rules as $match) {
                echo "  - {$match->rule->name} (score: {$match->score})" . PHP_EOL;
            }
        }
    }
} catch (\Throwable $e) {
    echo "ERRO: " . $e->getMessage() . PHP_EOL;
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}
