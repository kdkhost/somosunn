<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Event;
use App\Models\Mentorship;
use App\Models\Page;
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
                $freeEvents = Event::query()
                    ->where('published', true)
                    ->where(function ($query) {
                        $query->where('visibility', '!=', 'somos_unicas')->orWhereNull('visibility');
                    })
                    ->publicUpcoming()
                    ->orderBy('start_at')
                    ->limit(6)
                    ->get();

                $paidMentorings = Mentorship::query()
                    ->where('slots', '>', 0)
                    ->where(function ($query) {
                        $query->where('visibility', '!=', 'somos_unicas')->orWhereNull('visibility');
                    })
                    ->orderByDesc('price')
                    ->limit(24)
                    ->get()
                    ->filter(fn(Mentorship $mentorship) => $mentorship->hasPublicAction())
                    ->take(3)
                    ->values();
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

        $homePage = Page::findBySlug('home') ?? new Page();

        $dbTestimonials = collect();
        if (view()->shared('unnDbAvailable')) {
            try {
                $dbTestimonials = \App\Models\Testimonial::forSite()
                    ->with('user')
                    ->orderByDesc('is_featured')
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get();
            } catch (\Throwable $e) {
                \Log::warning('Falha ao carregar depoimentos: ' . $e->getMessage());
            }
        }

        return view('site.index', [
            'freeEvents' => $freeEvents,
            'paidMentorings' => $paidMentorings,
            'levelSummary' => $levelSummary,
            'topRankings' => $topRankings,
            'showNoRankingMsg' => $showNoRankingMsg,
            'isDemo' => false,
            'homePage' => $homePage,
            'dbTestimonials' => $dbTestimonials,
        ]);
    }

    public function portal()
    {
        $mentorings = collect();
        $featuredCourses = collect();
        if (view()->shared('unnDbAvailable')) {
            try {
                $mentorings = Mentorship::query()
                    ->where('slots', '>', 0)
                    ->where(function ($query) {
                        $query->where('visibility', '!=', 'somos_unicas')->orWhereNull('visibility');
                    })
                    ->orderBy('schedule')
                    ->limit(24)
                    ->get()
                    ->filter(fn(Mentorship $mentorship) => $mentorship->hasPublicAction())
                    ->take(6)
                    ->values();

                // Cursos em destaque: status published OU paused, is_featured = true
                $featuredCourses = \App\Models\Course::where('is_featured', true)
                    ->whereIn('status', ['published', 'paused'])
                    ->where(function ($query) {
                        $query->where('visibility', '!=', 'somos_unicas')->orWhereNull('visibility');
                    })
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
            'pageData' => Page::dataBySlug('portal'),
        ]);
    }

    public function premium()
    {
        $allActive = \App\Models\Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('highlight')
            ->orderBy('price')
            ->get();

        $requiredFeature = trim((string) request()->query('feature', ''));
        $requiredFeatureLabel = $requiredFeature !== '' ? $this->featureLabel($requiredFeature) : '';

        $recommendedPlans = collect();
        if ($requiredFeature !== '') {
            $recommendedPlans = $allActive
                ->filter(fn($plan) => method_exists($plan, 'hasFeature') && (bool) $plan->hasFeature($requiredFeature))
                ->values();
        }

        // Reposicionamento: plano destacado sempre no centro
        $plans = $this->centerHighlightedPlan($allActive);

        // Períodos disponíveis entre todos os planos (para o toggle de período)
        $allPeriods = ['mensal' => 'Mensal'];
        foreach ($plans as $plan) {
            foreach (($plan->price_periods ?? []) as $pk => $pv) {
                if ($pv > 0 && in_array($pk, ['trimestral', 'semestral', 'anual'], true)) {
                    $allPeriods[$pk] = ucfirst($pk);
                }
            }
        }
        ksort($allPeriods);

        // Dados de preços por período para o JS (keyed por plan id)
        $planPriceData = $plans->mapWithKeys(fn($plan) => [
            $plan->id => $plan->getAvailablePeriods(),
        ])->all();

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

        $pageData = Page::dataBySlug('premium');

        return view('site.premium', compact(
            'plans',
            'testimonials',
            'requiredFeature',
            'requiredFeatureLabel',
            'recommendedPlans',
            'allPeriods',
            'planPriceData',
            'pageData'
        ));
    }

    /**
     * Reposiciona o plano com highlight=true para o centro da coleção.
     */
    private function centerHighlightedPlan(\Illuminate\Support\Collection $plans): \Illuminate\Support\Collection
    {
        $highlighted = $plans->firstWhere('highlight', true);
        if (!$highlighted || $plans->count() <= 1) {
            return $plans;
        }

        $others = $plans->filter(fn($p) => $p->id !== $highlighted->id)->values();
        $total = $others->count() + 1;
        $center = (int) floor($total / 2);

        $before = $others->slice(0, $center)->values();
        $after = $others->slice($center)->values();

        return $before->push($highlighted)->merge($after)->values();
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
                    ->orWhereIn('role', ['mentor', 'admin', 'superadmin']);
            })->count();

            $levels = collect([
                'iniciante' => $inicianteCount,
                'sucesso' => $sucessoCount
            ]);

            $leaderboard = Ranking::with([
                'user' => function ($q) {
                    $q->whereNotIn('role', ['admin', 'superadmin']);
                }
            ])
                ->orderByDesc('score')
                ->get()
                ->filter(function ($rank) {
                    return $rank->user && !in_array($rank->user->role, ['admin', 'superadmin']);
                })
                ->take(3)
                ->values();

            // Fallback: se não há entradas no Ranking, usa os 3 usuários com mais pontos
            if ($leaderboard->isEmpty()) {
                $leaderboard = User::whereNotIn('role', ['admin', 'superadmin'])
                    ->where('points', '>', 0)
                    ->orderByDesc('points')
                    ->take(3)
                    ->get()
                    ->map(fn($u) => (object) [
                        'user' => $u,
                        'score' => $u->points ?? 0,
                        'level' => $u->level ?? 'iniciante',
                        'interactions_count' => 0,
                        'average_rating' => null,
                        'is_points_fallback' => true,
                    ])
                    ->values();
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao montar overview de networking: ' . $e->getMessage());
        }

        return ['levelSummary' => $levels->toArray(), 'leaderboard' => $leaderboard];
    }

    private function featureLabel(string $feature): string
    {
        $feature = trim($feature);

        return match ($feature) {
            'courses_access', 'courses', 'courses_lessons_access' => 'Acesso a cursos e aulas',
            'mentorships_access', 'mentorships' => 'Acesso a mentorias',
            'events_access', 'events' => 'Acesso a eventos',
            'chat', 'chat_access' => 'Acesso ao chat',
            'community', 'community_access' => 'Acesso a comunidade',
            'marketplace.buy', 'marketplace' => 'Acesso ao marketplace',
            default => 'Acesso a recurso premium',
        };
    }
}
