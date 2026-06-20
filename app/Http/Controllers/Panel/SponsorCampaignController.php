<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\SponsorService;

class SponsorCampaignController extends Controller
{
    public function __construct(
        private readonly SponsorService $sponsorService
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        abort_unless($this->sponsorService->canAccessSponsorPanel($user), 403);

        $sponsor = $this->sponsorService->sponsorForUser($user);
        abort_unless($sponsor, 404);

        return view('panel.sponsor.campaigns', compact('sponsor'));
    }
}
