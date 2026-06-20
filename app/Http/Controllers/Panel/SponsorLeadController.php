<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\SponsorLeadConsentRequest;
use App\Services\SponsorLeadService;
use App\Services\SponsorService;

class SponsorLeadController extends Controller
{
    public function __construct(
        private readonly SponsorService $sponsorService,
        private readonly SponsorLeadService $leadService
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        abort_unless($this->sponsorService->canAccessSponsorPanel($user), 403);

        $sponsor = $this->sponsorService->sponsorForUser($user);
        abort_unless($sponsor, 404);

        $leads = $this->leadService->paginatedForSponsor($sponsor);

        return view('panel.sponsor.leads', compact('sponsor', 'leads'));
    }

    public function store(SponsorLeadConsentRequest $request)
    {
        $user = $request->user();
        $sponsor = $this->sponsorService->sponsorForUser($user);
        abort_unless($sponsor, 404);

        $this->leadService->register($sponsor, $user, $request->validated());

        return back()->with('success', 'Lead registrado com consentimento.');
    }
}
