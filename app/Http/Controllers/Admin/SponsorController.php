<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SponsorRequest;
use App\Models\Sponsor;
use App\Services\SponsorService;

class SponsorController extends Controller
{
    public function __construct(
        private readonly SponsorService $sponsorService
    ) {
    }

    public function index()
    {
        $sponsors = $this->sponsorService->paginatedSponsors();

        return view('admin.sponsors.index', compact('sponsors'));
    }

    public function create()
    {
        return view('admin.sponsors.form', [
            'sponsor' => new Sponsor(),
            'companies' => $this->sponsorService->availableCompanies(),
            'plans' => $this->sponsorService->availablePlans(),
        ]);
    }

    public function store(SponsorRequest $request)
    {
        $sponsor = $this->sponsorService->saveSponsor($request->validated());

        return redirect()
            ->route('admin.sponsors.edit', $sponsor)
            ->with('success', 'Patrocinador criado com sucesso.');
    }

    public function edit(Sponsor $sponsor)
    {
        $sponsor->load(['company', 'plan']);

        return view('admin.sponsors.form', [
            'sponsor' => $sponsor,
            'companies' => $this->sponsorService->availableCompanies(),
            'plans' => $this->sponsorService->availablePlans(),
        ]);
    }

    public function update(SponsorRequest $request, Sponsor $sponsor)
    {
        $sponsor = $this->sponsorService->saveSponsor($request->validated(), $sponsor);

        return redirect()
            ->route('admin.sponsors.edit', $sponsor)
            ->with('success', 'Patrocinador atualizado com sucesso.');
    }

    public function destroy(Sponsor $sponsor)
    {
        $sponsor->delete();

        return redirect()
            ->route('admin.sponsors.index')
            ->with('success', 'Patrocinador removido com sucesso.');
    }
}
