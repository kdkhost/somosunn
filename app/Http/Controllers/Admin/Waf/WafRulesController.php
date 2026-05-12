<?php

namespace App\Http\Controllers\Admin\Waf;

use App\Http\Controllers\Controller;
use App\Models\Waf\WafRule;
use App\Services\Waf\WafParser;
use App\Services\Waf\WafRuleRepository;
use App\Services\Waf\WafSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WafRulesController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('waf_rules')) {
            return view('admin.waf.rules.index', ['rules' => collect(), 'hasTable' => false]);
        }

        $query = WafRule::query()->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('attack_pattern', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $rules = $query->paginate(25)->withQueryString();

        return view('admin.waf.rules.index', [
            'rules'    => $rules,
            'hasTable' => true,
        ]);
    }

    public function create()
    {
        return view('admin.waf.rules.form', ['rule' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'attack_pattern'  => 'required|string|max:100',
            'matcher_type'    => 'required|in:regex,list,numeric,function',
            'matcher_payload' => 'required|json',
            'score'           => 'required|integer|min:0|max:100',
            'action'          => 'required|in:monitor,challenge,block',
            'severity'        => 'required|in:info,low,medium,high,critical',
        ]);

        $data['matcher_payload'] = json_decode($data['matcher_payload'], true);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $rule = new WafRule($data);

        $repo = app(WafRuleRepository::class);
        $repo->save($rule, auth()->id());

        try {
            Log::channel('security')->info('WAF regra criada', [
                'rule_id'   => $rule->id,
                'rule_name' => $rule->name,
                'actor_id'  => auth()->id(),
                'ip'        => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return redirect()->route('admin.waf.rules.index')->with('success', 'Regra criada com sucesso.');
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        if (! Schema::hasTable('waf_rules')) {
            abort(404);
        }

        $rule = WafRule::findOrFail($id);

        return view('admin.waf.rules.form', compact('rule'));
    }

    public function update(Request $request, $id)
    {
        if (! Schema::hasTable('waf_rules')) {
            abort(404);
        }

        $rule = WafRule::findOrFail($id);

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string|max:1000',
            'attack_pattern'  => 'required|string|max:100',
            'matcher_type'    => 'required|in:regex,list,numeric,function',
            'matcher_payload' => 'required|json',
            'score'           => 'required|integer|min:0|max:100',
            'action'          => 'required|in:monitor,challenge,block',
            'severity'        => 'required|in:info,low,medium,high,critical',
        ]);

        $data['matcher_payload'] = json_decode($data['matcher_payload'], true);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['updated_by'] = auth()->id();

        $rule->fill($data);

        $repo = app(WafRuleRepository::class);
        $repo->save($rule, auth()->id());

        try {
            Log::channel('security')->info('WAF regra atualizada', [
                'rule_id'   => $rule->id,
                'rule_name' => $rule->name,
                'actor_id'  => auth()->id(),
                'ip'        => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return redirect()->route('admin.waf.rules.index')->with('success', 'Regra atualizada com sucesso.');
    }

    public function destroy(Request $request, $id)
    {
        if (! Schema::hasTable('waf_rules')) {
            abort(404);
        }

        $rule = WafRule::findOrFail($id);
        $ruleName = $rule->name;

        $repo = app(WafRuleRepository::class);
        $repo->delete($id, auth()->id());

        try {
            Log::channel('security')->info('WAF regra removida', [
                'rule_id'   => $id,
                'rule_name' => $ruleName,
                'actor_id'  => auth()->id(),
                'ip'        => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return redirect()->route('admin.waf.rules.index')->with('success', 'Regra removida com sucesso.');
    }

    public function toggle(Request $request, $id): JsonResponse
    {
        if (! Schema::hasTable('waf_rules')) {
            return response()->json(['error' => 'Tabela nao disponivel'], 404);
        }

        $repo = app(WafRuleRepository::class);
        $rule = $repo->toggle($id, auth()->id());

        if (! $rule) {
            return response()->json(['error' => 'Regra nao encontrada'], 404);
        }

        try {
            Log::channel('security')->info('WAF regra toggled', [
                'rule_id'   => $rule->id,
                'is_active' => $rule->is_active,
                'actor_id'  => auth()->id(),
                'ip'        => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success'   => true,
            'is_active' => $rule->is_active,
        ]);
    }

    public function test(Request $request): JsonResponse
    {
        $request->validate([
            'matcher_type'    => 'required|in:regex,list,numeric,function',
            'matcher_payload' => 'required|json',
            'sample'          => 'required|string|max:5000',
        ]);

        $matcherType = $request->input('matcher_type');
        $payload = json_decode($request->input('matcher_payload'), true);
        $sample = $request->input('sample');

        $matched = false;
        $details = '';

        try {
            switch ($matcherType) {
                case 'regex':
                    $pattern = $payload['pattern'] ?? '';
                    $flags = $payload['flags'] ?? '';
                    $regex = '/' . str_replace('/', '\\/', $pattern) . '/' . $flags . 'u';
                    $matched = (bool) preg_match($regex, $sample, $matches);
                    $details = $matched ? 'Match: ' . ($matches[0] ?? '') : 'Sem match';
                    break;

                case 'list':
                    $items = $payload['items'] ?? [];
                    foreach ($items as $item) {
                        if (stripos($sample, $item) !== false) {
                            $matched = true;
                            $details = "Encontrado: {$item}";
                            break;
                        }
                    }
                    if (! $matched) {
                        $details = 'Nenhum item da lista encontrado';
                    }
                    break;

                case 'numeric':
                    $op = $payload['operator'] ?? '>';
                    $threshold = $payload['value'] ?? 0;
                    $numericSample = (float) $sample;
                    $matched = match ($op) {
                        '>'  => $numericSample > $threshold,
                        '>=' => $numericSample >= $threshold,
                        '<'  => $numericSample < $threshold,
                        '<=' => $numericSample <= $threshold,
                        '==' => $numericSample == $threshold,
                        default => false,
                    };
                    $details = "{$numericSample} {$op} {$threshold} = " . ($matched ? 'true' : 'false');
                    break;

                default:
                    $details = 'Tipo de matcher nao suportado para teste';
            }
        } catch (\Throwable $e) {
            return response()->json([
                'error'   => true,
                'message' => 'Erro ao testar: ' . $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'matched' => $matched,
            'details' => $details,
        ]);
    }

    public function exportAll()
    {
        if (! Schema::hasTable('waf_rules')) {
            abort(404);
        }

        $serializer = app(WafSerializer::class);
        $rules = WafRule::all();
        $data = $serializer->serializeMany($rules);

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="waf-rules-' . date('Y-m-d') . '.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,txt|max:2048',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $jsonArray = json_decode($content, true);

        if (! is_array($jsonArray)) {
            return back()->with('error', 'Arquivo JSON invalido.');
        }

        $parser = app(WafParser::class);
        $report = $parser->parseMany($jsonArray);

        $repo = app(WafRuleRepository::class);
        $imported = 0;

        foreach ($report->accepted as $rule) {
            $rule->created_by = auth()->id();
            $rule->updated_by = auth()->id();
            $repo->save($rule, auth()->id());
            $imported++;
        }

        try {
            Log::channel('security')->info('WAF regras importadas', [
                'imported' => $imported,
                'rejected' => $report->totalRejected(),
                'actor_id' => auth()->id(),
                'ip'       => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        $msg = "Importacao concluida: {$imported} regras importadas.";
        if ($report->hasRejections()) {
            $msg .= " {$report->totalRejected()} rejeitadas.";
        }

        return back()->with('success', $msg);
    }
}
