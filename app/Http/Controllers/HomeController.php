<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Mentorship;
use App\Models\Magazine;
use App\Models\Page;
use App\Models\Ranking;
use App\Models\User;
use App\Support\ContentVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        $demoMode = (bool) config('app.demo_mode');

        $freeEvents = collect();
        $promotionalEvents = collect();
        $paidMentorings = collect();
        $featuredMagazines = collect();

        if (view()->shared('unnDbAvailable')) {
            try {
                $eventBaseQuery = fn () => ContentVisibility::applyPublicFilter(
                    Event::query()
                        ->where('published', true)
                        ->where('type', 'event'),
                    'events'
                )->publicUpcoming();

                $upcomingEvents = $eventBaseQuery()
                    ->orderBy('start_at')
                    ->limit(24)
                    ->get();

                $freeEvents = $upcomingEvents
                    ->filter(fn (Event $event) => $event->isActuallyFreeForPublic())
                    ->take(6)
                    ->values();

                $promotionalEvents = $upcomingEvents
                    ->filter(fn (Event $event) => !$event->isActuallyFreeForPublic())
                    ->take(6)
                    ->values();

                $paidMentorings = ContentVisibility::applyPublicFilter(
                    Mentorship::query()->where('slots', '>', 0),
                    'mentorships'
                )
                    ->orderByDesc('price')
                    ->limit(24)
                    ->get()
                    ->filter(fn (Mentorship $mentorship) => $mentorship->hasPublicAction())
                    ->take(3)
                    ->values();

                $featuredMagazines = Magazine::query()
                    ->visibleTo(auth()->user())
                    ->where('is_featured', true)
                    ->whereNotNull('thumbnail')
                    ->where('thumbnail', '!=', '')
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->limit(12)
                    ->get();
            } catch (\Throwable $e) {
                Log::warning('Falha ao carregar eventos/mentorias: ' . $e->getMessage());
            }
        }

        if ($demoMode && $paidMentorings->isEmpty()) {
            $paidMentorings = collect([
                (object) [
                    'id' => 1,
                    'title' => 'Conexao Elite: Mentoria de Negocios (DEMO)',
                    'description' => 'ESTE E UM DADO DE EXEMPLO. Aprenda estrategias de escala e networking de alto nivel.',
                    'mentor' => (object) ['name' => 'Carlos Mendes'],
                    'price' => 997,
                    'slots' => 5,
                    'is_demo' => true,
                ],
            ]);
        }

        $overview = $this->networkingOverview();
        $levelSummary = $overview['levelSummary'];
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
                Log::warning('Falha ao carregar depoimentos: ' . $e->getMessage());
            }
        }

        return view('site.index', [
            'freeEvents' => $freeEvents,
            'promotionalEvents' => $promotionalEvents,
            'paidMentorings' => $paidMentorings,
            'featuredMagazines' => $featuredMagazines,
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
                $mentorings = ContentVisibility::applyPublicFilter(
                    Mentorship::query()->where('slots', '>', 0),
                    'mentorships'
                )
                    ->orderBy('schedule')
                    ->limit(24)
                    ->get()
                    ->filter(fn (Mentorship $mentorship) => $mentorship->hasPublicAction())
                    ->take(6)
                    ->values();

                $featuredCourses = ContentVisibility::applyPublicFilter(
                    \App\Models\Course::where('is_featured', true)
                        ->whereIn('status', ['published', 'paused']),
                    'courses'
                )
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
                ->filter(fn ($plan) => method_exists($plan, 'hasFeature') && (bool) $plan->hasFeature($requiredFeature))
                ->values();
        }

        $plans = $this->centerHighlightedPlan($allActive);

        $paidPlans = $plans->filter(fn ($plan) => (float) ($plan->price ?? 0) > 0)->values();
        $allPeriods = [];
        foreach ($paidPlans as $plan) {
            foreach ($plan->getAvailablePeriods() as $periodKey => $periodPrice) {
                if ($periodPrice <= 0) {
                    continue;
                }

                $allPeriods[$periodKey] = \App\Models\Plan::periodLabels()[$periodKey] ?? ucfirst($periodKey);
            }
        }

        if ($allPeriods === []) {
            $allPeriods = ['mensal' => 'Mensal'];
        }

        $orderedPeriods = [];
        foreach (\App\Models\Plan::PERIOD_KEYS as $periodKey) {
            if (isset($allPeriods[$periodKey])) {
                $orderedPeriods[$periodKey] = $allPeriods[$periodKey];
            }
        }
        $allPeriods = $orderedPeriods;
        $defaultPeriod = array_key_first($allPeriods) ?: 'mensal';

        $planPriceData = $plans->mapWithKeys(fn ($plan) => [
            $plan->id => $plan->getAvailablePeriods(),
        ])->all();

        $comparisonRows = \App\Models\Plan::premiumComparisonRows();
        $premiumPillars = [
            [
                'icon' => 'users',
                'title' => 'Comunidade e networking',
                'desc' => 'Perfil interno, conexoes de alto valor e encontros que geram relacionamento real.',
            ],
            [
                'icon' => 'graduation-cap',
                'title' => 'Cursos e mentorias',
                'desc' => 'Consuma conhecimento pronto ou publique sua propria expertise, conforme o plano.',
            ],
            [
                'icon' => 'handshake',
                'title' => 'Clube de beneficios',
                'desc' => 'Acesse beneficios exclusivos e, no Elite, ative perfil parceiro com cupons.',
            ],
            [
                'icon' => 'microphone-lines',
                'title' => 'Visibilidade em eventos',
                'desc' => 'Pitch diferenciado, apresentacao anual e prioridade comercial nos eventos do grupo.',
            ],
            [
                'icon' => 'calendar-check',
                'title' => 'Eventos e networking',
                'desc' => 'Entre na agenda dos eventos da comunidade e, no Elite, publique os seus.',
            ],
            [
                'icon' => 'store',
                'title' => 'Escala comercial',
                'desc' => 'O Elite abre area de criador, historico de vendas e operacao comercial na plataforma.',
            ],
        ];

        $testimonials = collect();
        if (view()->shared('unnDbAvailable')) {
            try {
                $testimonials = \App\Models\Testimonial::forSite()
                    ->orderByDesc('is_featured')
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get();
            } catch (\Throwable $e) {
                Log::warning('Falha ao carregar depoimentos: ' . $e->getMessage());
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
            'defaultPeriod',
            'planPriceData',
            'comparisonRows',
            'premiumPillars',
            'pageData'
        ));
    }

    private function centerHighlightedPlan(\Illuminate\Support\Collection $plans): \Illuminate\Support\Collection
    {
        $highlighted = $plans->firstWhere('highlight', true);
        if (!$highlighted || $plans->count() <= 1) {
            return $plans;
        }

        $others = $plans->filter(fn ($plan) => $plan->id !== $highlighted->id)->values();
        $total = $others->count() + 1;
        $center = (int) floor($total / 2);

        $before = $others->slice(0, $center)->values();
        $after = $others->slice($center)->values();

        return $before->push($highlighted)->merge($after)->values();
    }

    public function webhookMercadoPago(Request $request)
    {
        Log::info('MercadoPago webhook', $request->all());

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
            $inicianteCount = User::where('level', 'iniciante')
                ->whereNotIn('role', ['admin', 'superadmin', 'mentor'])
                ->count();

            $sucessoCount = User::where(function ($query) {
                $query->where('level', 'sucesso')
                    ->orWhereIn('role', ['mentor', 'admin', 'superadmin']);
            })->count();

            $levels = collect([
                'iniciante' => $inicianteCount,
                'sucesso' => $sucessoCount,
            ]);

            $leaderboard = Ranking::with([
                'user' => function ($query) {
                    $query->whereNotIn('role', ['admin', 'superadmin']);
                },
            ])
                ->orderByDesc('score')
                ->get()
                ->filter(function ($rank) {
                    return $rank->user && !in_array($rank->user->role, ['admin', 'superadmin']);
                })
                ->take(3)
                ->values();

            if ($leaderboard->isEmpty()) {
                $leaderboard = User::whereNotIn('role', ['admin', 'superadmin'])
                    ->where('points', '>', 0)
                    ->orderByDesc('points')
                    ->take(3)
                    ->get()
                    ->map(fn ($user) => (object) [
                        'user' => $user,
                        'score' => $user->points ?? 0,
                        'level' => $user->level ?? 'iniciante',
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
            'courses.create' => 'Publicacao de cursos',
            'mentorships_access', 'mentorships' => 'Acesso a mentorias',
            'mentorships.create' => 'Publicacao de mentorias',
            'events_access', 'events' => 'Acesso a eventos',
            'events.create' => 'Criacao de eventos',
            'events.pitch.priority' => 'Pitch diferenciado nos eventos',
            'events.keynote.annual' => 'Apresentacao principal anual',
            'events.first_lot' => 'Compra prioritaria com primeiro lote',
            'events.mentor' => 'Mentoria nas dinamicas dos eventos',
            'chat', 'chat_access' => 'Acesso ao chat',
            'community', 'community_access' => 'Acesso a comunidade',
            'benefits.club.access' => 'Acesso ao clube de beneficios',
            'benefits.club.partner' => 'Perfil parceiro no clube de beneficios',
            'marketplace.buy', 'marketplace' => 'Acesso ao marketplace',
            default => 'Acesso a recurso premium',
        };
    }
}
