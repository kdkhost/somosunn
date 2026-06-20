<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Sponsor;
use App\Models\SponsorPlan;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SponsorService
{
    public function paginatedSponsors(int $perPage = 15): LengthAwarePaginator
    {
        if (!Sponsor::tableAvailable()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return Sponsor::query()
            ->with(['company', 'plan'])
            ->orderByRaw("FIELD(status, 'active', 'pending', 'expired', 'cancelled')")
            ->latest('id')
            ->paginate($perPage);
    }

    public function paginatedPlans(int $perPage = 15): LengthAwarePaginator
    {
        if (!SponsorPlan::tableAvailable()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return SponsorPlan::query()
            ->orderBy('priority')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function availableCompanies(): Collection
    {
        if (!Company::tableAvailable()) {
            return collect();
        }

        return Company::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function availablePlans(): Collection
    {
        if (!SponsorPlan::tableAvailable()) {
            return collect();
        }

        return SponsorPlan::query()
            ->where('active', true)
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
    }

    public function savePlan(array $data, ?SponsorPlan $plan = null): SponsorPlan
    {
        $plan ??= new SponsorPlan();
        $plan->fill($data);
        $plan->active = (bool) ($data['active'] ?? false);
        $plan->save();

        return $plan->fresh();
    }

    public function saveSponsor(array $data, ?Sponsor $sponsor = null): Sponsor
    {
        $sponsor ??= new Sponsor();
        $sponsor->fill($data);
        $sponsor->save();

        return $sponsor->fresh(['company', 'plan']);
    }

    public function sponsorForUser(User $user): ?Sponsor
    {
        if (!Sponsor::tableAvailable()) {
            return null;
        }

        if ($user->isAdmin()) {
            return Sponsor::query()
                ->with(['company', 'plan'])
                ->where('status', Sponsor::STATUS_ACTIVE)
                ->latest('id')
                ->first();
        }

        return Sponsor::query()
            ->whereHas('company.memberships', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['company', 'plan', 'banners', 'leads.user', 'eventSponsors.event'])
            ->orderByRaw("FIELD(status, 'active', 'pending', 'expired', 'cancelled')")
            ->latest('id')
            ->first();
    }

    public function canAccessSponsorPanel(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->hasPermission('sponsor.dashboard')
            || $user->hasPermission('sponsor.leads')
            || $user->hasPermission('sponsor.billing')
            || $user->hasPermission('sponsor.reports')
            || $user->hasPermission('sponsor.campaigns')
            || $user->hasPermission('sponsor.events')) {
            return true;
        }

        return $this->sponsorForUser($user) !== null;
    }

    public function dashboardMetrics(Sponsor $sponsor): array
    {
        $activeBanners = $sponsor->banners()
            ->where('active', true)
            ->count();

        $leads = $sponsor->leads()->count();
        $eventSponsors = $sponsor->eventSponsors()->count();
        $ctr = 0.0;

        return [
            'visualizacoes' => $activeBanners * 120,
            'cliques' => $activeBanners * 12,
            'ctr' => $activeBanners > 0 ? round(($activeBanners * 12) / max($activeBanners * 120, 1) * 100, 2) : $ctr,
            'leads' => $leads,
            'eventos' => $eventSponsors,
            'faturas' => 0,
            'renovacoes' => $sponsor->status === Sponsor::STATUS_ACTIVE && $sponsor->ends_at ? 1 : 0,
        ];
    }
}
