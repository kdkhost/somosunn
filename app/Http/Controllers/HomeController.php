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
        $demoMode = (bool) config('app.demo_mode');

        $freeEvents = collect();
        $paidMentorings = collect();

        if (view()->shared('unnDbAvailable')) {
            try {
                $freeEvents = Event::where('published', true)
                    ->orderBy('start_at')
                    ->limit(6)
                    ->get();

                $paidMentorings = Mentorship::where('slots', '>', 0)
                    ->orderByDesc('price')
                    ->limit(3)
                    ->get();
            } catch (\Throwable $e) {
                Log::warning('Falha ao carregar eventos/mentorias: ' . $e->getMessage());
            }
        }

        // Demo data fallback for mentorships (Only if demo_mode is true and no data exists)
        if ($demoMode && $paidMentorings->isEmpty()) {
            $paidMentorings = collect([
                (object) [
                    'id' => 1,
                    'title' => 'Conexão Elite: Mentoria de Negócios (DEMO)',
                    'description' => 'ESTE É UM DADO DE EXEMPLO. Aprenda estratégias de escala e networking de alto nível.',
                    'mentor' => (object) ['name' => 'Carlos Mendes'],
                    'price' => 997,
                    'slots' => 5,
                    'is_demo' => true,
                ],
            ]);
        }

        $overview = $this->networkingOverview();
        $levelSummary = $overview['levelSummary'];

        // Se não houver dados reais, exibe mensagem no ranking
        $topRankings = $overview['leaderboard'];
        $showNoRankingMsg = $topRankings->isEmpty();

        return view('site.index', [
            'freeEvents' => $freeEvents,
            'paidMentorings' => $paidMentorings,
            'levelSummary' => $levelSummary,
            'topRankings' => $topRankings,
            'showNoRankingMsg' => $showNoRankingMsg,
            'isDemo' => false,
        ]);
    }

    public function portal()
    {
        $mentorings = collect();
        $featuredCourses = collect();
        if (view()->shared('unnDbAvailable')) {
            try {
                $mentorings = Mentorship::where('slots', '>', 0)
                    ->orderBy('schedule')
                    ->limit(6)
                    ->get();

                // Cursos em destaque: status published OU paused, is_featured = true
                $featuredCourses = \App\Models\Course::where('is_featured', true)
                    ->whereIn('status', ['published', 'paused'])
                    ->inRandomOrder()
                    ->limit(6)
                    ->get();
            } catch (\Throwable $e) {
                Log::warning('Falha ao carregar mentorias/cursos em destaque: ' . $e->getMessage());
            }
        }

        $overview = $this->networkingOverview();

        return view('site.portal', [
            'mentorings' => $mentorings,
            'featuredCourses' => $featuredCourses,
            'levelSummary' => $overview['levelSummary'],
            'topRankings' => $overview['leaderboard'],
            'isDemo' => config('app.demo_mode') && $mentorings->isEmpty(),
        ]);
    }

    public function premium()
    {
        $plans = \App\Models\Plan::where('is_active', true)
            ->orderByDesc('highlight')
            ->orderBy('price')
            ->get();

        $testimonials = collect();
        if (view()->shared('unnDbAvailable')) {
            try {
                $testimonials = \App\Models\Testimonial::where('status', 'approved')
                    ->orderByDesc('is_featured')
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get();
            } catch (\Throwable $e) {
                \Log::warning('Falha ao carregar depoimentos: ' . $e->getMessage());
            }
        }

        return view('site.premium', compact('plans', 'testimonials'));
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
            // Contagem explícita para agrupar roles/levels
            $inicianteCount = User::where('level', 'iniciante')
                ->whereNotIn('role', ['admin', 'superadmin', 'mentor'])
                ->count();

            $sucessoCount = User::where(function ($q) {
                $q->where('level', 'sucesso')
                    ->orWhere('role', 'mentor');
            })->count();

            $levels = [
                'iniciante' => $inicianteCount,
                'sucesso' => $sucessoCount
            ];

            $leaderboard = Ranking::with([
                'user' => function ($q) {
                    $q->whereNotIn('role', ['admin', 'superadmin']);
                }
            ])
                ->orderByDesc('score')
                ->get()
                ->filter(function ($rank) {
                    // Exclui se não tem user ou se user é admin/superadmin
                    return $rank->user && !in_array($rank->user->role, ['admin', 'superadmin']);
                })
                ->take(6)
                ->values();
        } catch (\Throwable $e) {
            Log::warning('Falha ao montar overview de networking: ' . $e->getMessage());
        }

        return ['levelSummary' => $levels->toArray(), 'leaderboard' => $leaderboard];
    }
}
