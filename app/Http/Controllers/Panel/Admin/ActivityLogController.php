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
     * Retorna JSON quando chamado via AJAX (X-Requested-With: XMLHttpRequest)
     * ou redireciona com flash message quando chamado via form submit normal.
     */
    public function clear(Request $request)
    {
        $this->ensurePermission('logs.view');

        try {
            $userId = Auth::id();
            $deleted = DB::table('activity_logs')->delete();

            Log::channel(config('logging.channels.security') ? 'security' : 'stack')
                ->warning('ActivityLog limpo manualmente (painel novo)', [
                    'user_id' => $userId,
                    'deleted_count' => $deleted,
                    'ip' => $request->ip(),
                ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Histórico limpo com sucesso. ' . $deleted . ' registros removidos.',
                    'deleted' => $deleted,
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Histórico de logs limpo com sucesso. ' . $deleted . ' registros removidos.');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('Falha ao limpar activity_logs (painel novo)', [
                'exception' => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível limpar o histórico. Verifique os logs do servidor.',
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Não foi possível limpar o histórico. Verifique os logs do servidor.');
        }
    }

    private function ensurePermission(string $perm)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->hasPermission($perm)) {
            abort(403);
        }
    }
}
