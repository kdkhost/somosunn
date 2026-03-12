<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Concerns\HandlesPlanFormData;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\WatermarkService;
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
        $plans = Plan::orderBy('sort_order')->orderByDesc('highlight')->orderBy('price')->paginate(20);

        return view('panel.admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('panel.admin.plans.form', [
            'plan' => new Plan(),
            'planFeatures' => $this->planFeatures(),
            'planFeatureGroups' => $this->planFeatureGroups(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePlanData($request);

        if ($request->hasFile('image')) {
            $data['image'] = app(WatermarkService::class)->processStorageImage(
                $request->file('image'),
                'plan-images',
                null,
                ['prefix' => 'plan']
            );
        }

        DB::transaction(function () use ($data) {
            $plan = Plan::create($data);
            $this->enforceSingleHighlight($plan);
            $this->enforceSingleFree($plan);

            if ($plan->is_recurring && empty($plan->mp_plan_id)) {
                $this->syncWithMercadoPago($plan);
            }
        });

        return redirect()->route('panel.admin.plans.index')->with('success', 'Plano criado com sucesso.');
    }

    public function edit(Plan $plan)
    {
        return view('panel.admin.plans.form', [
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
            $data['image'] = app(WatermarkService::class)->processStorageImage(
                $request->file('image'),
                'plan-images',
                null,
                ['prefix' => 'plan']
            );
        }

        DB::transaction(function () use ($plan, $data) {
            $plan->update($data);
            $this->enforceSingleHighlight($plan);
            $this->enforceSingleFree($plan);

            if ($plan->is_recurring) {
                $this->syncWithMercadoPago($plan);
            }
        });

        return redirect()->route('panel.admin.plans.index')->with('success', 'Plano atualizado com sucesso.');
    }

    public function destroy(Plan $plan)
    {
        $this->deletePlanImageIfExists($plan);
        $plan->delete();

        return redirect()->route('panel.admin.plans.index')->with('success', 'Plano removido com sucesso.');
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
