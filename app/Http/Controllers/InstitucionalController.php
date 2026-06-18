<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Plan;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class InstitucionalController extends Controller
{
    public function sobre(): View
    {
        $page = Page::findBySlug('sobre') ?? new Page();
        return view('site.institucional.sobre', compact('page'));
    }

    public function manifesto(): View
    {
        $page = Page::findBySlug('manifesto') ?? new Page();
        return view('site.institucional.manifesto', compact('page'));
    }

    public function valores(): View
    {
        $page = Page::findBySlug('valores') ?? new Page();
        return view('site.institucional.valores', compact('page'));
    }

    public function comoFunciona(): View
    {
        $page = Page::findBySlug('como-funciona') ?? new Page();
        $allActive = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('highlight')
            ->orderBy('price')
            ->get();

        $plans = $this->centerHighlightedPlan($allActive);

        $paidPlans = $plans->filter(fn (Plan $plan) => !$plan->isFreeAccessPlan())->values();
        $allPeriods = [];
        foreach ($paidPlans as $plan) {
            foreach ($plan->getAvailablePeriods() as $periodKey => $periodPrice) {
                if ($periodPrice <= 0) {
                    continue;
                }

                $allPeriods[$periodKey] = Plan::periodLabels()[$periodKey] ?? ucfirst($periodKey);
            }
        }

        if ($allPeriods === []) {
            $allPeriods = ['mensal' => 'Mensal'];
        }

        $orderedPeriods = [];
        foreach (Plan::PERIOD_KEYS as $periodKey) {
            if (isset($allPeriods[$periodKey])) {
                $orderedPeriods[$periodKey] = $allPeriods[$periodKey];
            }
        }

        $allPeriods = $orderedPeriods;
        $defaultPeriod = array_key_first($allPeriods) ?: 'mensal';
        $planPriceData = $plans->mapWithKeys(fn (Plan $plan) => [
            $plan->id => $plan->getAvailablePeriods(),
        ])->all();

        return view('site.institucional.como-funciona', compact(
            'page',
            'plans',
            'allPeriods',
            'defaultPeriod',
            'planPriceData'
        ));
    }

    public function quemSomos(): View
    {
        $page = Page::findBySlug('quem-somos') ?? new Page();
        return view('site.institucional.quem-somos', compact('page'));
    }
    
    public function termos(): View
    {
        $page = Page::findBySlug('termos-de-uso') ?? new Page();
        return view('site.institucional.termos', compact('page'));
    }

    public function privacidade(): View
    {
        $page = Page::findBySlug('politica-de-privacidade') ?? new Page();
        return view('site.institucional.privacidade', compact('page'));
    }

    public function lgpd(): View
    {
        $page = Page::findBySlug('consentimento-lgpd') ?? new Page();
        return view('site.institucional.lgpd', compact('page'));
    }

    private function centerHighlightedPlan(Collection $plans): Collection
    {
        $highlighted = $plans->firstWhere('highlight', true);
        if (!$highlighted || $plans->count() <= 1) {
            return $plans;
        }

        $others = $plans->filter(fn (Plan $plan) => $plan->id !== $highlighted->id)->values();
        $middleIndex = intdiv($others->count(), 2);

        return $others
            ->slice(0, $middleIndex)
            ->push($highlighted)
            ->concat($others->slice($middleIndex))
            ->values();
    }
}
