<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    protected $mpService;

    public function __construct(\App\Services\Payment\MercadoPagoService $mpService)
    {
        $this->mpService = $mpService;
    }
    /**
     * Recursos disponíveis para planos.
     * Esses controlam o que o MEMBRO pode acessar no site.
     */
    private const PLAN_FEATURES = [
        // Acesso básico
        'community' => 'Comunidade (perfil/feed)',
        'chat' => 'Chat (mensagens)',
        'connections' => 'Networking (conexões)',
        'connections.unlimited' => 'Conexões ilimitadas',

        // Cursos
        'courses' => 'Acesso a cursos',
        'courses.create' => 'Criar cursos',
        'courses.edit' => 'Editar cursos',
        'courses.delete' => 'Excluir cursos',
        'courses.certificates' => 'Certificados de cursos',
        'courses.downloads' => 'Downloads de materiais',

        // Eventos
        'events' => 'Acesso a eventos',
        'events.create' => 'Criar eventos',
        'events.edit' => 'Editar eventos',
        'events.delete' => 'Excluir eventos',
        'events.recordings' => 'Gravações de eventos',
        'events.vip' => 'Eventos VIP/exclusivos',

        // Mentorias
        'mentorships' => 'Acesso a mentorias',
        'mentorships.create' => 'Criar mentorias',
        'mentorships.edit' => 'Editar mentorias',
        'mentorships.delete' => 'Excluir mentorias',
        'mentorships.group' => 'Mentorias em grupo',
        'mentorships.individual' => 'Mentorias individuais',

        // Marketplace
        'marketplace' => 'Acesso ao marketplace',
        'marketplace.sales' => 'Ver histórico de vendas',
        'marketplace.buy' => 'Comprar produtos/serviços',
        'marketplace.sell' => 'Vender no marketplace',

        // Extras
        'rankings' => 'Participar do ranking',
        'support.priority' => 'Suporte prioritário',
        'early.access' => 'Acesso antecipado a novidades',

        // Admin
        'admin.panel' => 'Acesso ao painel admin',
    ];

    public function index()
    {
        $plans = Plan::orderByDesc('highlight')->orderBy('price')->paginate(12);
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.form', [
            'plan' => new Plan(),
            'planFeatures' => self::PLAN_FEATURES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('plan-images', 'public');
        }
        DB::transaction(function () use ($data) {
            $plan = Plan::create($data);
            $this->enforceSingleHighlight($plan);

            if ($plan->is_recurring && empty($plan->mp_plan_id)) {
                $this->syncWithMercadoPago($plan);
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['redirect' => route('admin.plans.index')]);
        }

        return redirect()->route('admin.plans.index')->with('success', 'Plano criado');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.form', [
            'plan' => $plan,
            'planFeatures' => self::PLAN_FEATURES,
        ]);
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $this->validateData($request, $plan->id);

        if ($request->boolean('remove_image')) {
            $this->deletePlanImageIfExists($plan);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            $this->deletePlanImageIfExists($plan);
            $data['image'] = $request->file('image')->store('plan-images', 'public');
        }

        DB::transaction(function () use ($plan, $data) {
            $plan->update($data);
            $this->enforceSingleHighlight($plan);

            if ($plan->is_recurring) {
                $this->syncWithMercadoPago($plan);
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['redirect' => route('admin.plans.index')]);
        }

        return redirect()->route('admin.plans.index')->with('success', 'Plano atualizado');
    }

    public function destroy(Plan $plan)
    {
        $this->deletePlanImageIfExists($plan);
        $plan->delete();

        if (request()->expectsJson()) {
            return response()->json(['redirect' => route('admin.plans.index')]);
        }

        return redirect()->route('admin.plans.index')->with('success', 'Plano removido');
    }

    protected function validateData(Request $request, $id = null)
    {
        if ($request->has('price')) {
            $price = trim((string) $request->input('price'));
            $price = str_replace(['R$', ' ', "\u{00A0}"], '', $price);

            // If brazilian format, normalize: 1.234,56 -> 1234.56
            if (str_contains($price, ',')) {
                $price = str_replace('.', '', $price);
                $price = str_replace(',', '.', $price);
            }

            $request->merge(['price' => $price]);
        }

        if ($request->has('slug')) {
            $request->merge(['slug' => trim((string) $request->input('slug'))]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => [
                'nullable',
                'string',
                'max:160',
                Rule::unique('plans', 'slug')->ignore($id),
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'remove_image' => 'nullable|boolean',
            'price' => 'required|numeric|min:0',
            'period' => 'required|string|max:50',
            'highlight' => 'nullable|boolean',
            'coupons_enabled' => 'nullable|boolean',
            'benefits' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => [
                'string',
                Rule::in(array_keys(self::PLAN_FEATURES)),
            ],
            'comparison' => 'nullable|array',
            'comparison.connections_per_month' => 'nullable|string|max:50',
            'comparison.group_mentorship' => 'nullable|string|max:50',
            'comparison.individual_mentorship' => 'nullable|string|max:50',
            'comparison.priority_support' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_recurring' => 'nullable|boolean',
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['slug'] ?: $data['name'], $id);
        $data['highlight'] = $request->boolean('highlight');
        $data['is_featured'] = $data['highlight'];
        $data['coupons_enabled'] = $request->boolean('coupons_enabled');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_recurring'] = $request->boolean('is_recurring');

        // Handle benefits from textarea
        $benefitsRaw = $request->input('benefits', '');
        $data['benefits'] = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $benefitsRaw))));

        $rawPermissions = $request->input('permissions', []);
        if (!is_array($rawPermissions)) {
            $rawPermissions = [];
        }

        $allowed = array_keys(self::PLAN_FEATURES);
        $data['permissions'] = array_values(array_unique(array_values(array_filter($rawPermissions, function ($name) use ($allowed) {
            return is_string($name) && in_array($name, $allowed, true);
        }))));

        $comparison = $request->input('comparison', []);
        if (!is_array($comparison)) {
            $comparison = [];
        }
        $data['comparison'] = [
            'connections_per_month' => isset($comparison['connections_per_month']) ? trim((string) $comparison['connections_per_month']) : null,
            'group_mentorship' => isset($comparison['group_mentorship']) ? trim((string) $comparison['group_mentorship']) : null,
            'individual_mentorship' => isset($comparison['individual_mentorship']) ? trim((string) $comparison['individual_mentorship']) : null,
            'priority_support' => (bool) ($comparison['priority_support'] ?? false),
        ];

        return $data;
    }

    private function deletePlanImageIfExists(Plan $plan): void
    {
        $path = trim((string) ($plan->image ?? ''));
        if ($path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function enforceSingleHighlight(Plan $plan): void
    {
        if (!$plan->highlight) {
            return;
        }

        Plan::query()
            ->where('id', '!=', $plan->id)
            ->where(function ($q) {
                $q->where('highlight', true)->orWhere('is_featured', true);
            })
            ->update([
                'highlight' => false,
                'is_featured' => false,
            ]);
    }

    private function syncWithMercadoPago(Plan $plan): void
    {
        try {
            // Se já tem um ID, talvez queiramos atualizar no futuro. 
            // Por enquanto, focamos em criar se for novo e for recorrente.
            if ($plan->is_recurring && empty($plan->mp_plan_id)) {
                $mpPlan = $this->mpService->createPreapprovalPlan([
                    'name' => $plan->name,
                    'price' => $plan->price,
                    'period' => $plan->period,
                    'billing_cycle' => $plan->billing_cycle ?: 1,
                ]);

                if (isset($mpPlan['id'])) {
                    Plan::where('id', $plan->id)->update(['mp_plan_id' => $mpPlan['id']]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao sincronizar plano com Mercado Pago: ' . $e->getMessage());
            // Opcional: Notificar o admin sem travar a transação do BD local
        }
    }

    private function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        if ($base === '') {
            $base = 'plano';
        }

        $slug = $base;
        $suffix = 2;

        while (
            Plan::query()
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public function toggleActive(Request $request, Plan $plan)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Acesso não autorizado.'], 403);
            }

            return redirect()->route('panel.dashboard')->with('error', 'Acesso não autorizado.');
        }

        $plan->is_active = !$plan->is_active;
        $plan->save();

        $message = $plan->is_active
            ? 'Plano ativado e exibido no site.'
            : 'Plano desativado e ocultado do site.';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'is_active' => (bool) $plan->is_active,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
