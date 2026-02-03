<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Event;
use App\Models\Mentorship;
use App\Models\Ranking;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $freeEvents = collect();
        $paidMentorings = collect();

        if (view()->shared('unnDbAvailable')) {
            try {
                $freeEvents = Event::where('published', true)
                    ->where('price', 0)
                    ->orderBy('start_at')
                    ->limit(3)
                    ->get();

                $paidMentorings = Mentorship::where('slots', '>', 0)
                    ->orderByDesc('price')
                    ->limit(3)
                    ->get();
            } catch (\Throwable $e) {
                Log::warning('Falha ao carregar eventos/mentorias: '.$e->getMessage());
            }
        }

        $overview = $this->networkingOverview();

        return view('site.index', [
            'freeEvents' => $freeEvents,
            'paidMentorings' => $paidMentorings,
            'levelSummary' => $overview['levelSummary'],
            'topRankings' => $overview['leaderboard'],
        ]);
    }

    public function portal()
    {
        $mentorings = collect();
        if (view()->shared('unnDbAvailable')) {
            try {
                $mentorings = Mentorship::where('slots', '>', 0)
                    ->orderBy('schedule')
                    ->limit(6)
                    ->get();
            } catch (\Throwable $e) {
                Log::warning('Falha ao carregar mentorias: '.$e->getMessage());
            }
        }

        $overview = $this->networkingOverview();

        return view('site.portal', [
            'mentorings' => $mentorings,
            'levelSummary' => $overview['levelSummary'],
            'topRankings' => $overview['leaderboard'],
        ]);
    }

    public function premium()
    {
        return view('site.premium');
    }

    // Webhook placeholders
    public function webhookMercadoPago(Request $request)
    {
        // TODO: validar assinatura e processar pagamento
        \Log::info('MercadoPago webhook', $request->all());
        return response()->json(['status' => 'ok']);
    }

    public function webhookPagSeguro(Request $request)
    {
        // TODO: validar notificacao e processar
        \Log::info('PagSeguro webhook', $request->all());
        return response()->json(['status' => 'ok']);
    }

    private function networkingOverview(): array
    {
        $levels = collect(['iniciante' => 0, 'sucesso' => 0]);
        $leaderboard = collect();

        if (!view()->shared('unnDbAvailable')) {
            return ['levelSummary' => $levels->toArray(), 'leaderboard' => $leaderboard];
        }

        try {
            $levels = User::select('level', DB::raw('count(*) as total'))
                ->groupBy('level')
                ->pluck('total', 'level');

            $leaderboard = Ranking::with('user')
                ->orderByDesc('score')
                ->limit(6)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('Falha ao montar overview de networking: '.$e->getMessage());
        }

        return ['levelSummary' => $levels->toArray(), 'leaderboard' => $leaderboard];
    }
}
