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
    public function index()
    {
        $logs = ActivityLog::with('user')->latest()->take(1000)->get();
        return view('admin.activity_logs.index', compact('logs'));
    }

    /**
     * Limpa o historico de logs de atividade.
     *
     * Usa DELETE em vez de TRUNCATE para preservar a integridade
     * referencial caso existam (ou venham a existir) FKs apontando
     * para activity_logs. TRUNCATE no MySQL ignora as constraints
     * e pode causar inconsistencias silenciosas.
     *
     * Em caso de falha, exibe mensagem de erro e mantem os logs.
     */
    public function clear()
    {
        try {
            $userId = Auth::id();
            $deleted = DB::table('activity_logs')->delete();

            Log::channel(config('logging.channels.security') ? 'security' : 'stack')
                ->warning('ActivityLog limpo manualmente', [
                    'user_id' => $userId,
                    'deleted_count' => $deleted,
                    'ip' => request()->ip(),
                ]);

            return redirect()
                ->back()
                ->with('success', 'Historico de logs limpo com sucesso. ' . $deleted . ' registros removidos.');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('Falha ao limpar activity_logs', [
                'exception' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Nao foi possivel limpar o historico. Verifique os logs do servidor.');
        }
    }
}
