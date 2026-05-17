<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 *
 * Sistema UNN - Panel\Admin\ActivityLogController
 *
 * Controlador do painel novo (Tailwind) para gestao de logs de
 * atividade. Espelha o comportamento do Admin\ActivityLogController
 * (admin antigo) com fail-safe estrito na limpeza de historico:
 *   - DELETE em vez de TRUNCATE (preserva integridade referencial
 *     se FKs vierem a apontar para activity_logs).
 *   - try/catch e audit log no canal `security` com user_id, ip e
 *     contagem de registros removidos.
 *   - Mensagem de erro amigavel em caso de falha; nunca propaga
 *     excecao para o usuario.
 *
 * O middleware `LogUserActivity` ja contem `painel/admin/logs/clear`
 * em SKIP_PATHS, garantindo que a propria limpeza nao gere um novo
 * registro logo apos esvaziar a tabela.
 */

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('logs.view');

        $type = $request->query('type');
        $userId = $request->query('user_id');
        $q = trim((string) $request->query('q', ''));

        $logs = ActivityLog::query()
            ->with('user')
            ->when($type, fn($query) => $query->where('activity_type', $type))
            ->when($userId, fn($query) => $query->where('user_id', $userId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where('description', 'like', '%' . $q . '%')
                    ->orWhere('ip_address', 'like', '%' . $q . '%');
            })
            ->latest()
            ->paginate(30);

        $logs->appends($request->all());

        return view('panel.admin.logs.index', compact('logs', 'type', 'userId', 'q'));
    }

    /**
     * Limpa o historico de logs de atividade.
     *
     * Implementacao fail-safe espelhando Admin\ActivityLogController::clear():
     *   - DELETE em vez de TRUNCATE
     *   - audit log no canal security
     *   - try/catch para mensagem amigavel
     */
    public function clear()
    {
        $this->ensurePermission('logs.view');

        try {
            $userId = Auth::id();
            $deleted = DB::table('activity_logs')->delete();

            Log::channel(config('logging.channels.security') ? 'security' : 'stack')
                ->warning('ActivityLog limpo manualmente (painel novo)', [
                    'user_id' => $userId,
                    'deleted_count' => $deleted,
                    'ip' => request()->ip(),
                ]);

            return redirect()
                ->back()
                ->with('success', 'Historico de logs limpo com sucesso. ' . $deleted . ' registros removidos.');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('Falha ao limpar activity_logs (painel novo)', [
                'exception' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Nao foi possivel limpar o historico. Verifique os logs do servidor.');
        }
    }

    private function ensurePermission(string $perm)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->hasPermission($perm)) {
            abort(403);
        }
    }
}
