<?php
// UTF-8 sem BOM
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduledTask;
use App\Models\ScheduledTaskLog;
use Cron\CronExpression;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CronController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $totalCount = ScheduledTask::query()->count();
        $activeCount = ScheduledTask::query()->where('active', true)->count();
        $inactiveCount = max(0, $totalCount - $activeCount);

        return view('admin.cron.index', compact('totalCount', 'activeCount', 'inactiveCount'));
    }

    public function data(Request $request): JsonResponse
    {
        $draw = (int) $request->input('draw', 1);
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        $length = $length > 0 ? min($length, 100) : 10;
        $search = trim((string) $request->input('search.value', ''));

        $total = ScheduledTask::query()->count();
        $query = ScheduledTask::query();

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('command', 'like', '%' . $search . '%')
                    ->orWhere('frequency', 'like', '%' . $search . '%');

                if (is_numeric($search)) {
                    $builder->orWhere('id', (int) $search);
                }

                $normalized = mb_strtolower($search);
                if (str_contains($normalized, 'inativa') || str_contains($normalized, 'inativo')) {
                    $builder->orWhere('active', false);
                } elseif (str_contains($normalized, 'ativa') || str_contains($normalized, 'ativo')) {
                    $builder->orWhere('active', true);
                }
            });
        }

        $filtered = (clone $query)->count();
        $orderColumn = (int) $request->input('order.0.column', 0);
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderMap = [
            0 => 'id',
            1 => 'command',
            2 => 'frequency',
            3 => 'active',
            4 => 'last_run_at',
        ];

        $tasks = $query
            ->orderBy($orderMap[$orderColumn] ?? 'id', $orderDirection)
            ->orderByDesc('id')
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $tasks->map(fn (ScheduledTask $task): array => $this->formatDataTableRow($task))->values(),
        ]);
    }

    public function create()
    {
        return view('admin.cron.form', ['task' => new ScheduledTask()]);
    }

    public function store(Request $request)
    {
        // Map UI inputs to Model attributes
        $request->merge([
            'command' => $request->input('real_command'),
            'frequency' => $request->input('real_frequency'),
        ]);

        $data = $request->validate([
            ...ScheduledTask::schedulerValidationRules(),
        ]);
        $data['active'] = $request->has('active');

        ScheduledTask::create($data);

        return redirect()->route('admin.cron.index')->with('success', 'Tarefa criada!');
    }

    public function edit(ScheduledTask $task)
    {
        return view('admin.cron.form', compact('task'));
    }

    public function update(Request $request, ScheduledTask $task)
    {
        // Map UI inputs to Model attributes
        $request->merge([
            'command' => $request->input('real_command'),
            'frequency' => $request->input('real_frequency'),
        ]);

        $data = $request->validate([
            ...ScheduledTask::schedulerValidationRules(),
        ]);
        $data['active'] = $request->has('active');

        $task->update($data);

        return redirect()->route('admin.cron.index')->with('success', 'Tarefa atualizada!');
    }

    public function destroy(ScheduledTask $task)
    {
        $task->delete();
        return redirect()->route('admin.cron.index')->with('success', 'Tarefa excluída!');
    }

    public function logs(ScheduledTask $task)
    {
        $logs = $task->logs()->orderByDesc('executed_at')->limit(50)->get();
        return view('admin.cron.logs', compact('task', 'logs'));
    }

    public function run(ScheduledTask $task)
    {
        if (!$task->active) {
            return back()->with('error', 'Tarefa inativa.');
        }

        if (!ScheduledTask::isAllowedForScheduler($task->command)) {
            return back()->with('error', 'Comando bloqueado por seguranca. A central de cron nao executa tarefas destrutivas ou fora da lista permitida.');
        }

        $output = '';
        $success = true;

        try {
            // Fix: Artisan::call returns exit code. Output is captured via Artisan::output()
            $exitCode = Artisan::call($task->command);
            $output = Artisan::output();
            $success = $exitCode === 0;
        } catch (\Throwable $e) {
            $output = $e->getMessage();
            $success = false;
        }

        $task->last_run_at = now();
        $task->save();

        ScheduledTaskLog::create([
            'scheduled_task_id' => $task->id,
            'executed_at' => now(),
            'output' => $output,
            'success' => $success,
        ]);

        return back()->with($success ? 'success' : 'error', $success ? 'Executada com sucesso!' : 'Falha na execução.');
    }

    /**
     * Executa TODAS as tarefas ativas de uma vez.
     */
    public function runAll()
    {
        $tasks = ScheduledTask::where('active', true)->get();
        $executed = 0;
        $failed = 0;

        foreach ($tasks as $task) {
            if (!ScheduledTask::isAllowedForScheduler($task->command)) {
                ScheduledTaskLog::create([
                    'scheduled_task_id' => $task->id,
                    'executed_at' => now(),
                    'output' => 'Comando bloqueado por seguranca. A central de cron nao executa tarefas destrutivas ou fora da lista permitida.',
                    'success' => false,
                ]);

                $failed++;
                continue;
            }

            $output = '';
            $success = true;

            try {
                $exitCode = Artisan::call($task->command);
                $output = Artisan::output();
                $success = $exitCode === 0;
            } catch (\Throwable $e) {
                $output = $e->getMessage();
                $success = false;
            }

            $task->last_run_at = now();
            $task->save();

            ScheduledTaskLog::create([
                'scheduled_task_id' => $task->id,
                'executed_at' => now(),
                'output' => $output ?: 'Executado via "Executar Todas".',
                'success' => $success,
            ]);

            $success ? $executed++ : $failed++;
        }

        $msg = "Executadas: {$executed} tarefas.";
        if ($failed > 0) {
            $msg .= " Falhas: {$failed}.";
        }

        return back()->with($failed > 0 ? 'warning' : 'success', $msg);
    }

    private function formatDataTableRow(ScheduledTask $task): array
    {
        return [
            'id' => '<span class="badge badge-light border">' . $task->id . '</span>',
            'command' => '<code class="text-dark cron-command">' . e($task->command) . '</code>',
            'frequency' => '<span class="badge badge-info"><i class="fas fa-redo mr-1"></i>' . e($task->frequency) . '</span>',
            'status' => $task->active
                ? '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Ativa</span>'
                : '<span class="badge badge-secondary"><i class="fas fa-pause mr-1"></i>Inativa</span>',
            'last_run_at' => $task->last_run_at
                ? '<small><i class="fas fa-calendar-check text-muted mr-1"></i>' . e($task->last_run_at->format('d/m/Y H:i')) . '</small>'
                : '<small class="text-muted">Nunca executada</small>',
            'next_run_at' => $this->nextRunHtml($task),
            'actions' => $this->actionsHtml($task),
        ];
    }

    private function nextRunHtml(ScheduledTask $task): string
    {
        try {
            $cron = new CronExpression((string) $task->frequency);
            $nextRun = $cron->getNextRunDate();

            return '<small><i class="fas fa-clock text-muted mr-1"></i>' . e($nextRun->format('d/m/Y H:i')) . '</small>';
        } catch (\Throwable $e) {
            return '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Inválido</span>';
        }
    }

    private function actionsHtml(ScheduledTask $task): string
    {
        $csrf = csrf_field();
        $methodDelete = method_field('DELETE');
        $runUrl = route('admin.cron.run', $task);
        $editUrl = route('admin.cron.edit', $task);
        $logsUrl = route('admin.cron.logs', $task);
        $deleteUrl = route('admin.cron.destroy', $task);

        return <<<HTML
            <form action="{$runUrl}" method="POST" class="d-inline run-cron-form">
                {$csrf}
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill btn-run-cron" title="Executar Agora">
                    <i class="fas fa-play"></i>
                </button>
            </form>
            <a href="{$editUrl}" class="btn btn-sm btn-outline-primary rounded-pill" title="Editar"><i class="fas fa-edit"></i></a>
            <a href="{$logsUrl}" class="btn btn-sm btn-outline-info rounded-pill" title="Logs"><i class="fas fa-list"></i></a>
            <form action="{$deleteUrl}" method="POST" class="d-inline delete-cron-form">
                {$csrf}
                {$methodDelete}
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill btn-delete-cron" title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        HTML;
    }
}
