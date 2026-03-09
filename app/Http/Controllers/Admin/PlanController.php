<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesPlanFormData;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlanController extends Controller
{
    use HandlesPlanFormData;

    protected \App\Services\Payment\MercadoPagoService $mpService;

    public function __construct(\App\Services\Payment\MercadoPagoService $mpService)
    {
        $this->mpService = $mpService;
    }

    public function index()
    {
        $plans = Plan::orderBy('sort_order')->orderByDesc('highlight')->orderBy('price')->paginate(24);

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.form', [
            'plan' => new Plan(),
            'planFeatures' => $this->planFeatures(),
            'planFeatureGroups' => $this->planFeatureGroups(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePlanData($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('plan-images', 'public');
        }

        DB::transaction(function () use ($data) {
            $plan = Plan::create($data);
            $this->enforceSingleHighlight($plan);
            $this->enforceSingleFree($plan);

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
            'planFeatures' => $this->planFeatures(),
            'planFeatureGroups' => $this->planFeatureGroups(),
        ]);
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $this->validatePlanData($request, $plan->id);

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
            $this->enforceSingleFree($plan);

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
            ->where(function ($query) {
                $query->where('highlight', true)->orWhere('is_featured', true);
            })
            ->update([
                'highlight' => false,
                'is_featured' => false,
            ]);
    }

    private function enforceSingleFree(Plan $plan): void
    {
        if (!$plan->is_free) {
            return;
        }

        Plan::query()
            ->where('id', '!=', $plan->id)
            ->where('is_free', true)
            ->update(['is_free' => false]);
    }

    private function syncWithMercadoPago(Plan $plan): void
    {
        try {
            if ($plan->is_recurring && empty($plan->mp_plan_id)) {
                $mpPlan = $this->mpService->createPreapprovalPlan([
                    'name' => $plan->name,
                    'price' => $plan->getPriceForPeriod($plan->firstAvailablePeriod()),
                    'period' => $plan->period === 'vitalicio' ? 'mensal' : $plan->period,
                    'billing_cycle' => $plan->billing_cycle ?: 1,
                ]);

                if (isset($mpPlan['id'])) {
                    Plan::where('id', $plan->id)->update(['mp_plan_id' => $mpPlan['id']]);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Erro ao sincronizar plano com Mercado Pago: ' . $e->getMessage());
        }
    }

    public function toggleActive(Request $request, Plan $plan)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Acesso nao autorizado.'], 403);
            }

            return redirect()->route('panel.dashboard')->with('error', 'Acesso nao autorizado.');
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

    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:plans,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->input('items') as $item) {
                Plan::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
            }
        });

        return response()->json(['status' => 'ok']);
    }
}
