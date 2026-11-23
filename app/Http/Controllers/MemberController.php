<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MemberController extends Controller
{
    /**
     * Display a listing of members.
     */
    public function index(Request $request)
    {
        $demoMode = (bool) config('app.demo_mode');

        // Check if members feature is enabled
        $isEnabled = \App\Models\Setting::get('feature_members', '1') === '1';

        if (!$isEnabled) {
            abort(404, 'Membros temporariamente indisponível');
        }

        $pageData = Page::dataBySlug('membros');

        // Regras de controle do Admin/Superadmin:
        // 'all' = Todos os membros ativos cadastrados (padrão recomendado para a comunidade)
        // 'with_plan' = Membros vinculados a qualquer plano (Starter, Pro, Elite)
        // 'paid_only' = Estritamente membros com plano pago ativo
        $displayMode = $pageData['members_display_mode'] ?? \App\Models\Setting::get('members_display_mode', 'all');
        $showAdmins = (bool) ($pageData['members_show_admins'] ?? \App\Models\Setting::get('members_show_admins', false));
        $onlyWithPhoto = (bool) ($pageData['members_only_with_photo'] ?? false);

        $blockedUserIds = [];
        $connectedUserIds = [];
        $connectionMap = [];

        if (auth()->check()) {
            $authId = auth()->id();
            $connectionRecords = \App\Models\Connection::where(function ($q) use ($authId) {
                $q->where('requester_id', $authId)->orWhere('requested_id', $authId);
            })->get();

            $connectionMap = $connectionRecords->mapWithKeys(function ($conn) use ($authId) {
                $otherId = $conn->requester_id === $authId ? $conn->requested_id : $conn->requester_id;
                return [$otherId => $conn];
            })->toArray();

            $blockedUserIds = $connectionRecords->filter(fn($c) => $c->status === 'blocked')
                ->map(fn($c) => $c->requester_id === $authId ? $c->requested_id : $c->requester_id)
                ->toArray();

            $connectedUserIds = $connectionRecords->filter(fn($c) => $c->status === 'accepted')
                ->map(fn($c) => $c->requester_id === $authId ? $c->requested_id : $c->requester_id)
                ->toArray();
        }

        $members = collect();
        $availableCities = collect();
        $availablePlans = collect();

        try {
            $query = User::query();

            // Ocultar superadmin do diretório público por segurança/privacidade
            $query->where('role', '!=', 'superadmin');

            // Controle do Admin: exibir administradores ou apenas membros?
            if (!$showAdmins) {
                $query->where('role', '!=', 'admin');
            }

            // Excluir usuários bloqueados
            if (!empty($blockedUserIds)) {
                $query->whereNotIn('id', $blockedUserIds);
            }

            // Controle do Admin: exibir apenas membros com foto de perfil?
            if ($onlyWithPhoto) {
                $query->whereNotNull('photo')->where('photo', '!=', '');
            }

            // Controle do Admin: Modo de exibição de planos
            if ($displayMode === 'paid_only') {
                $this->applyPaidPlanFilter($query);
            } elseif ($displayMode === 'with_plan') {
                $query->whereNotNull('plan_id');
            }
            // Se 'all', exibe todos os membros ativos cadastrados

            // Perfis privados: visitantes não vêem perfis marcados como ocultos
            if (!auth()->user()?->isAdmin()) {
                $query->where(function ($q) use ($connectedUserIds) {
                    $q->where('hide_profile', false)
                        ->orWhereIn('id', $connectedUserIds);
                });
            }

            // REGRA: Membros de caráter gratuito só podem ver membros do mesmo patamar (gratuito)
            $currentUser = auth()->user();
            $isFreeUserRestricted = false;
            if ($currentUser && $currentUser->isFreeMember() && !$currentUser->isAdmin()) {
                $isFreeUserRestricted = true;
                $query->where(function ($q) {
                    $q->whereNull('plan_id')
                        ->orWhereHas('plan', function ($pq) {
                            $pq->where('is_free', true)
                                ->orWhere('price', '<=', 0);
                        });
                });
            }

            // --- FILTRO DE BUSCA MULTIFACETADO NA PÁGINA PÚBLICA ---
            // 1. Busca textual livre (Nome, Profissão, Empresa, Bio)
            $search = trim($request->input('q') ?: $request->input('search') ?: '');
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('occupation', 'like', "%{$search}%")
                        ->orWhere('bio', 'like', "%{$search}%");
                });
            }

            // 2. Filtro por Cidade ou Estado
            $city = trim($request->input('city', ''));
            if ($city !== '') {
                $query->where(function ($q) use ($city) {
                    $q->where('city', 'like', "%{$city}%")
                        ->orWhere('state', 'like', "%{$city}%");
                });
            }

            // 3. Filtro por Plano / Nível
            $planId = $request->input('plan_id') ?: $request->input('plan');
            if (!empty($planId)) {
                $query->where('plan_id', $planId);
            }

            // 4. Ordenação / Classificação
            $sort = $request->input('sort', 'latest');
            if ($sort === 'name') {
                $query->orderBy('name', 'asc');
            } elseif ($sort === 'connections') {
                $query->select('users.*')
                    ->selectSub(function ($sub) {
                        $sub->from('connections')
                            ->selectRaw('count(*)')
                            ->where('status', 'accepted')
                            ->where(function ($q) {
                                $q->whereColumn('connections.requester_id', 'users.id')
                                    ->orWhereColumn('connections.requested_id', 'users.id');
                            });
                    }, 'connections_count')
                    ->orderByDesc('connections_count')
                    ->orderBy('name', 'asc');
            } else {
                $query->latest();
            }

            // Listas dinâmicas para os filtros da página com microcache adaptativo
            $availableCities = \App\Support\AdaptiveQueryCache::remember('members:available_cities', function () {
                return User::whereNotNull('city')
                    ->where('city', '!=', '')
                    ->where('hide_profile', false)
                    ->distinct()
                    ->pluck('city')
                    ->filter()
                    ->sort()
                    ->values();
            });

            $availablePlans = \App\Support\AdaptiveQueryCache::remember('plans:available_public', function () {
                return Plan::where('is_active', true)
                    ->orderBy('price', 'asc')
                    ->get(['id', 'name', 'price', 'is_free']);
            });

            $members = $query->with('plan')
                ->paginate(20)
                ->appends($request->query())
                ->through(function ($user) {
                    return (object) [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'bio' => $user->bio,
                        'avatar' => $user->photo ? asset($user->photo) : null,
                        'cover' => $user->cover_photo ? (str_starts_with($user->cover_photo, 'http') ? $user->cover_photo : asset($user->cover_photo)) : null,
                        'cover_photo' => $user->cover_photo ? (str_starts_with($user->cover_photo, 'http') ? $user->cover_photo : asset($user->cover_photo)) : null,
                        'city' => trim(($user->city ?? '') . ($user->state ? ', ' . $user->state : '')),
                        'occupation' => $user->occupation,
                        'company' => $user->company,
                        'linkedin' => $user->linkedin,
                        'facebook' => $user->facebook,
                        'instagram' => $user->instagram,
                        'twitter' => $user->twitter,
                        'youtube' => $user->youtube,
                        'website' => $user->website,
                        'level' => $user->level ?? ($user->plan?->name ?: 'Membro'),
                        'plan_name' => $user->plan?->name ?: null,
                        'plan_badge' => $user->plan?->name ? ($user->plan->is_free ? 'Starter' : $user->plan->name) : null,
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
                    'cover' => null,
                    'cover_photo' => null,
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
                'pageData' => $pageData,
                'availableCities' => $availableCities,
                'availablePlans' => $availablePlans,
            ]);
        }

        return view('site.membros', compact(
            'members',
            'connectionMap',
            'pageData',
            'availableCities',
            'availablePlans',
            'isFreeUserRestricted'
        ));
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
