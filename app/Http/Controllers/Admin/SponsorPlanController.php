<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SponsorPlanRequest;
use App\Models\SponsorPlan;
use App\Services\SponsorService;

class SponsorPlanController extends Controller
{
    public function __construct(
        private readonly SponsorService $sponsorService
    ) {
    }

    public function index()
    {
        $plans = $this->sponsorService->paginatedPlans();

        return view('admin.sponsors.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.sponsors.plans.form', ['plan' => new SponsorPlan()]);
    }

    public function store(SponsorPlanRequest $request)
    {
        $plan = $this->sponsorService->savePlan($request->validated());

        return redirect()
            ->route('admin.sponsor-plans.edit', $plan)
            ->with('success', 'Plano de patrocinio criado com sucesso.');
    }

    public function edit(SponsorPlan $sponsorPlan)
    {
        return view('admin.sponsors.plans.form', ['plan' => $sponsorPlan]);
    }

    public function update(SponsorPlanRequest $request, SponsorPlan $sponsorPlan)
    {
        $plan = $this->sponsorService->savePlan($request->validated(), $sponsorPlan);

        return redirect()
            ->route('admin.sponsor-plans.edit', $plan)
            ->with('success', 'Plano de patrocinio atualizado com sucesso.');
    }

    public function destroy(SponsorPlan $sponsorPlan)
    {
        $sponsorPlan->delete();

        return redirect()
            ->route('admin.sponsor-plans.index')
            ->with('success', 'Plano removido com sucesso.');
    }
}
