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
 */

namespace App\Http\Controllers\Admin;

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
        $q = trim((string) $request->query('q', ''));

        $logs = ActivityLog::with('user')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('description', 'like', '%' . $q . '%')
                    ->orWhere('ip_address', 'like', '%' . $q . '%')
                    ->orWhere('action', 'like', '%' . $q . '%');
            })
            ->latest()
            ->paginate(30);

        $logs->appends($request->all());

        return view('admin.activity_logs.index', compact('logs', 'q'));
    }

    /**
     * Limpa o historico de logs de atividade.
     *
     * Retorna JSON quando chamado via AJAX (X-Requested-With: XMLHttpRequest)
     * ou redireciona com flash message quando chamado via form submit normal.
     */
    public function clear(Request $request)
    {
        try {
            $userId = Auth::id();
            $deleted = DB::table('activity_logs')->delete();

            Log::channel(config('logging.channels.security') ? 'security' : 'stack')
                ->warning('ActivityLog limpo manualmente', [
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
            Log::channel('stack')->error('Falha ao limpar activity_logs', [
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
}
