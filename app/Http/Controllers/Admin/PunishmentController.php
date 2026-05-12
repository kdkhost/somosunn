<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PunishmentController extends Controller
{
    /**
     * Lista todos os usuarios com punicao ativa ou historico de punicao.
     */
    public function index()
    {
        $users = User::where(function ($q) {
            $q->whereNotNull('blocked_until')
              ->orWhere('events_suspension_remaining', '>', 0)
              ->orWhereNotNull('block_reason');
        })->orderByDesc('blocked_until')->get();

        $users->each(function ($user) {
            $user->punishment_status = $this->resolveStatus($user);
        });

        return view('admin.punishments.index', compact('users'));
    }

    /**
     * Detalhes da punicao de um usuario especifico (JSON).
     */
    public function show($userId)
    {
        $user = User::findOrFail($userId);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'photo' => $user->photo ? asset($user->photo) : asset('img/user.png'),
                'blocked_until' => $user->blocked_until,
                'blocked_until_formatted' => $user->blocked_until
                    ? Carbon::parse($user->blocked_until)->format('d/m/Y H:i')
                    : null,
                'block_reason' => $user->block_reason,
                'events_suspension_remaining' => (int) ($user->events_suspension_remaining ?? 0),
                'status' => $this->resolveStatus($user),
            ],
        ]);
    }

    /**
     * Aplicar punicao manual (POST AJAX).
     */
    public function apply(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'block_duration_hours' => 'required|integer|min:0',
            'block_reason' => 'required|string|max:255',
            'events_suspension' => 'required|integer|min:0',
            'notify_user' => 'nullable|boolean',
        ]);

        $user = User::findOrFail($data['user_id']);
        $accumulate = Setting::get('punishment_accumulate', '1') === '1';

        // Calcular bloqueio
        $blockUntil = null;
        if ($data['block_duration_hours'] > 0) {
            if ($accumulate && $user->blocked_until && Carbon::parse($user->blocked_until)->isFuture()) {
                $base = Carbon::parse($user->blocked_until);
            } else {
                $base = now();
            }
            $blockUntil = $base->copy()->addHours($data['block_duration_hours']);
        }

        // Calcular suspensao de eventos
        $eventsSuspension = (int) $data['events_suspension'];
        if ($accumulate) {
            $eventsSuspension += (int) ($user->events_suspension_remaining ?? 0);
        }

        $updateData = [
            'block_reason' => $data['block_reason'],
            'events_suspension_remaining' => $eventsSuspension,
        ];

        if ($blockUntil) {
            $updateData['blocked_until'] = $blockUntil;
        }

        $user->update($updateData);

        Log::channel('security')->info('Punicao manual aplicada', [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'block_duration_hours' => $data['block_duration_hours'],
            'block_reason' => $data['block_reason'],
            'events_suspension' => $eventsSuspension,
        ]);

        // Notificar usuario se solicitado
        if (!empty($data['notify_user']) && $blockUntil) {
            try {
                $user->notify(new \App\Notifications\PunishmentApplied(
                    $data['block_reason'],
                    $blockUntil->format('d/m/Y H:i'),
                    $eventsSuspension
                ));
            } catch (\Throwable $e) {
                Log::warning('Falha ao notificar usuario sobre punicao: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Punicao aplicada com sucesso.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'blocked_until' => $user->blocked_until,
                'events_suspension_remaining' => $user->events_suspension_remaining,
                'status' => $this->resolveStatus($user->fresh()),
            ],
        ]);
    }

    /**
     * Remover punicao (POST AJAX).
     */
    public function remove(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $previousBlock = $user->blocked_until;
        $previousReason = $user->block_reason;
        $previousSuspension = $user->events_suspension_remaining;

        $user->update([
            'blocked_until' => null,
            'block_reason' => null,
            'events_suspension_remaining' => 0,
        ]);

        Log::channel('security')->info('Punicao removida', [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'previous_block' => $previousBlock,
            'previous_reason' => $previousReason,
            'previous_suspension' => $previousSuspension,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Punicao removida com sucesso.',
        ]);
    }

    /**
     * Editar punicao existente (PUT AJAX).
     */
    public function edit(Request $request, $userId)
    {
        $data = $request->validate([
            'block_duration_hours' => 'nullable|integer|min:0',
            'block_reason' => 'nullable|string|max:255',
            'events_suspension' => 'nullable|integer|min:0',
        ]);

        $user = User::findOrFail($userId);

        $updateData = [];

        if (isset($data['block_duration_hours'])) {
            if ((int) $data['block_duration_hours'] === 0) {
                $updateData['blocked_until'] = null;
            } else {
                $updateData['blocked_until'] = now()->addHours((int) $data['block_duration_hours']);
            }
        }

        if (isset($data['block_reason'])) {
            $updateData['block_reason'] = $data['block_reason'] ?: null;
        }

        if (isset($data['events_suspension'])) {
            $updateData['events_suspension_remaining'] = (int) $data['events_suspension'];
        }

        $user->update($updateData);

        Log::channel('security')->info('Punicao editada', [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'changes' => $updateData,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Punicao atualizada com sucesso.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'blocked_until' => $user->fresh()->blocked_until,
                'block_reason' => $user->fresh()->block_reason,
                'events_suspension_remaining' => $user->fresh()->events_suspension_remaining,
                'status' => $this->resolveStatus($user->fresh()),
            ],
        ]);
    }

    /**
     * Pagina de configuracoes de punicao (GET).
     */
    public function settings()
    {
        $settings = [
            'punishment_block_days' => Setting::get('punishment_block_days', '2'),
            'punishment_events_suspended' => Setting::get('punishment_events_suspended', '2'),
            'punishment_auto_enabled' => Setting::get('punishment_auto_enabled', '1'),
            'punishment_notify_user' => Setting::get('punishment_notify_user', '1'),
            'punishment_accumulate' => Setting::get('punishment_accumulate', '1'),
        ];

        return view('admin.punishments.settings', compact('settings'));
    }

    /**
     * Salvar configuracoes de punicao (PUT).
     */
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'punishment_block_days' => 'required|integer|min:1|max:365',
            'punishment_events_suspended' => 'required|integer|min:0|max:100',
            'punishment_auto_enabled' => 'nullable|boolean',
            'punishment_notify_user' => 'nullable|boolean',
            'punishment_accumulate' => 'nullable|boolean',
        ]);

        Setting::set('punishment_block_days', (string) $data['punishment_block_days'], 'punishment');
        Setting::set('punishment_events_suspended', (string) $data['punishment_events_suspended'], 'punishment');
        Setting::set('punishment_auto_enabled', $request->has('punishment_auto_enabled') ? '1' : '0', 'punishment');
        Setting::set('punishment_notify_user', $request->has('punishment_notify_user') ? '1' : '0', 'punishment');
        Setting::set('punishment_accumulate', $request->has('punishment_accumulate') ? '1' : '0', 'punishment');

        Log::channel('security')->info('Configuracoes de punicao atualizadas', [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'settings' => $data,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Configuracoes salvas com sucesso.',
            ]);
        }

        return redirect()->route('admin.punishments.settings')
            ->with('success', 'Configuracoes de punicao salvas com sucesso.');
    }

    /**
     * Resolve o status de punicao do usuario.
     */
    private function resolveStatus(User $user): string
    {
        $blocked = $user->blocked_until && Carbon::parse($user->blocked_until)->isFuture();
        $suspended = (int) ($user->events_suspension_remaining ?? 0) > 0;

        if ($blocked && $suspended) {
            return 'ambos';
        }
        if ($blocked) {
            return 'bloqueado';
        }
        if ($suspended) {
            return 'suspenso';
        }

        return 'livre';
    }
}
