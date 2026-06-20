<?php
// UTF-8 sem BOM

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduledTask;
use App\Models\ScheduledTaskLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CronController extends Controller
{
    public function index()
    {
        $tasks = ScheduledTask::orderByDesc('id')->get();

        return view('panel.admin.cron.index', compact('tasks'));
    }

    public function create()
    {
        return view('panel.admin.cron.form', ['task' => new ScheduledTask()]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'command' => $request->input('real_command'),
            'frequency' => $request->input('real_frequency'),
        ]);

        $data = $request->validate([
            ...ScheduledTask::schedulerValidationRules(),
        ]);
        $data['active'] = $request->has('active');

        ScheduledTask::create($data);

        return redirect()->route('panel.admin.cron.index')->with('success', 'Tarefa criada com sucesso.');
    }

    public function edit(ScheduledTask $task)
    {
        return view('panel.admin.cron.form', compact('task'));
    }

    public function update(Request $request, ScheduledTask $task)
    {
        $request->merge([
            'command' => $request->input('real_command'),
            'frequency' => $request->input('real_frequency'),
        ]);

        $data = $request->validate([
            ...ScheduledTask::schedulerValidationRules(),
        ]);
        $data['active'] = $request->has('active');

        $task->update($data);

        return redirect()->route('panel.admin.cron.index')->with('success', 'Tarefa atualizada com sucesso.');
    }

    public function destroy(ScheduledTask $task)
    {
        $task->delete();

        return redirect()->route('panel.admin.cron.index')->with('success', 'Tarefa excluida com sucesso.');
    }

    public function logs(ScheduledTask $task)
    {
        $logs = $task->logs()->orderByDesc('executed_at')->limit(100)->get();

        return view('panel.admin.cron.logs', compact('task', 'logs'));
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

        return back()->with(
            $success ? 'success' : 'error',
            $success ? 'Tarefa executada com sucesso.' : 'Falha na execucao da tarefa.'
        );
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
}
