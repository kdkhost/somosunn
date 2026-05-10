<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MemberController extends Controller
{
    /**
     * Display a listing of members.
     */
    public function index()
    {
        $demoMode = (bool) config('app.demo_mode');

        // Check if members feature is enabled
        $isEnabled = \App\Models\Setting::get('feature_members', '1') === '1';

        if (!$isEnabled) {
            abort(404, 'Membros temporariamente indisponível');
        }

        $blockedUserIds = [];
        $connectedUserIds = [];
        $connectionMap = [];

        if (auth()->check()) {
            $connectionRecords = \App\Models\Connection::where(function ($q) {
                $q->where('requester_id', auth()->id())->orWhere('requested_id', auth()->id());
            })->get();

            $connectionMap = $connectionRecords->mapWithKeys(function ($conn) {
                $otherId = $conn->requester_id === auth()->id() ? $conn->requested_id : $conn->requester_id;
                return [$otherId => $conn];
            })->toArray();

            $blockedUserIds = $connectionRecords->filter(fn($c) => $c->status === 'blocked')
                ->map(function ($conn) {
                    return $conn->requester_id === auth()->id() ? $conn->requested_id : $conn->requester_id;
                })->toArray();

            $connectedUserIds = $connectionRecords->filter(fn($c) => $c->status === 'accepted')
                ->map(function ($conn) {
                    return $conn->requester_id === auth()->id() ? $conn->requested_id : $conn->requester_id;
                })->toArray();
        }

        $members = collect();

        try {
            $query = User::where('role', '!=', 'superadmin')
                ->whereNotIn('id', $blockedUserIds);

            $this->applyPaidPlanFilter($query);

            // Hide private profiles if not connected and not admin
            if (!auth()->user()?->isAdmin()) {
                $query->where(function ($q) use ($connectedUserIds) {
                    $q->where('hide_profile', false)
                        ->orWhereIn('id', $connectedUserIds);
                });
            }

            $members = $query->latest()
                ->paginate(20)
                ->through(function ($user) {
                    return (object) [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'bio' => $user->bio,
                        'avatar' => $user->photo ? asset($user->photo) : null,
                        'city' => trim(($user->city ?? '') . ($user->state ? ', ' . $user->state : '')),
                        'occupation' => $user->occupation,
                        'company' => $user->company,
                        'linkedin' => $user->linkedin,
                        'facebook' => $user->facebook,
                        'instagram' => $user->instagram,
                        'twitter' => $user->twitter,
                        'youtube' => $user->youtube,
                        'website' => $user->website,
                        'level' => $user->level ?? 'Iniciante',
                        'connections' => \App\Models\Connection::where(function ($q) use ($user) {
                            $q->where('requester_id', $user->id)->orWhere('requested_id', $user->id);
                        })->where('status', 'accepted')->count(),
                        'is_demo' => false,
                    ];
                });
        } catch (\Throwable $e) {
            \Log::warning('Falha ao carregar membros: ' . $e->getMessage());
        }

        // If no members exist, provide demo data
        if ($demoMode && $members->isEmpty()) {
            $members = collect([
                (object) [
                    'id' => 1,
                    'name' => 'Carlos Eduardo Silva',
                    'email' => 'carlos@demo.com',
                    'bio' => 'Empreendedor serial com 15 anos de experiência em tecnologia e inovação.',
                    'company' => 'Tech Solutions LTDA',
                    'role' => 'CEO & Fundador',
                    'city' => 'São Paulo, SP',
                    'linkedin' => 'https://linkedin.com/in/demo',
                    'level' => 'Empresário de Sucesso',
                    'connections' => 234,
                    'avatar' => null,
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 2,
                    'name' => 'Ana Paula Costa',
                    'email' => 'ana@demo.com',
                    'bio' => 'Especialista em marketing digital e growth hacking. Mentora de startups.',
                    'company' => 'Growth Marketing Co.',
                    'role' => 'CMO',
                    'city' => 'Rio de Janeiro, RJ',
                    'linkedin' => 'https://linkedin.com/in/demo',
                    'level' => 'Mentor Premium',
                    'connections' => 189,
                    'avatar' => null,
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 3,
                    'name' => 'Roberto Mendes',
                    'email' => 'roberto@demo.com',
                    'bio' => 'Investidor anjo e conselheiro de empresas de médio porte.',
                    'company' => 'Mendes Investimentos',
                    'role' => 'Diretor de Investimentos',
                    'city' => 'Belo Horizonte, MG',
                    'linkedin' => 'https://linkedin.com/in/demo',
                    'level' => 'Investidor',
                    'connections' => 312,
                    'avatar' => null,
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 4,
                    'name' => 'Juliana Ferreira',
                    'email' => 'juliana@demo.com',
                    'bio' => 'Fundadora de e-commerce de moda sustentável. Palestrante.',
                    'company' => 'EcoFashion Brasil',
                    'role' => 'Fundadora',
                    'city' => 'Curitiba, PR',
                    'linkedin' => 'https://linkedin.com/in/demo',
                    'level' => 'Empreendedor',
                    'connections' => 156,
                    'avatar' => null,
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 5,
                    'name' => 'Fernando Oliveira',
                    'email' => 'fernando@demo.com',
                    'bio' => 'Consultor de negócios com foco em transformação digital.',
                    'company' => 'Digital Transform',
                    'role' => 'Consultor Sênior',
                    'city' => 'Porto Alegre, RS',
                    'linkedin' => 'https://linkedin.com/in/demo',
                    'level' => 'Consultor',
                    'connections' => 198,
                    'avatar' => null,
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 6,
                    'name' => 'Mariana Santos',
                    'email' => 'mariana@demo.com',
                    'bio' => 'CEO de fintech premiada. Ex-executiva de grandes bancos.',
                    'company' => 'FinNext',
                    'role' => 'CEO',
                    'city' => 'São Paulo, SP',
                    'linkedin' => 'https://linkedin.com/in/demo',
                    'level' => 'Empresária de Sucesso',
                    'connections' => 287,
                    'avatar' => null,
                    'is_demo' => true,
                ],
            ]);

            return view('site.membros', [
                'members' => $members,
                'isDemo' => true,
                'connectionMap' => $connectionMap,
                'pageData' => Page::dataBySlug('membros'),
            ]);
        }

        $pageData = Page::dataBySlug('membros');

        return view('site.membros', compact('members', 'connectionMap', 'pageData'));
    }

    protected function applyPaidPlanFilter($query): void
    {
        $hasUsersPlan = Schema::hasColumn('users', 'plan_id');
        $hasUsersPlanExpiry = Schema::hasColumn('users', 'plan_expires_at');
        $hasPlansTable = Schema::hasTable('plans');
        $hasSubscriptionsTable = Schema::hasTable('subscriptions');

        $canFilterByDirectPlan = $hasUsersPlan && $hasPlansTable;
        $canFilterBySubscription = $hasSubscriptionsTable && $hasPlansTable;

        if (!$canFilterByDirectPlan && !$canFilterBySubscription) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($membershipQuery) use (
            $canFilterByDirectPlan,
            $canFilterBySubscription,
            $hasUsersPlanExpiry
        ) {
            if ($canFilterByDirectPlan) {
                $membershipQuery->where(function ($directPlanQuery) use ($hasUsersPlanExpiry) {
                    $directPlanQuery->whereNotNull('plan_id');

                    if ($hasUsersPlanExpiry) {
                        $directPlanQuery->where(function ($expiryQuery) {
                            $expiryQuery->whereNull('plan_expires_at')
                                ->orWhere('plan_expires_at', '>', now());
                        });
                    }

                    $directPlanQuery->whereHas('plan', function ($planQuery) {
                        $planQuery->where('is_active', true)
                            ->where(function ($paidPlanQuery) {
                                $paidPlanQuery->where('is_free', false)
                                    ->orWhere('price', '>', 0);
                            });
                    });
                });
            }

            if ($canFilterBySubscription) {
                $method = $canFilterByDirectPlan ? 'orWhereHas' : 'whereHas';

                $membershipQuery->{$method}('subscriptions', function ($subscriptionQuery) {
                    $subscriptionQuery->where('status', 'active')
                        ->where(function ($endsAtQuery) {
                            $endsAtQuery->whereNull('ends_at')
                                ->orWhere('ends_at', '>', now());
                        })
                        ->whereHas('plan', function ($planQuery) {
                            $planQuery->where('is_active', true)
                                ->where(function ($paidPlanQuery) {
                                    $paidPlanQuery->where('is_free', false)
                                        ->orWhere('price', '>', 0);
                                });
                        });
                });
            }
        });
    }
}
