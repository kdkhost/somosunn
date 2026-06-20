<?php

namespace App\Http\Controllers;

use App\Services\CompanyService;

class CompanyProfileController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService
    ) {
    }

    public function show(string $slug)
    {
        $payload = $this->companyService->publicProfileBySlug($slug);

        abort_unless($payload, 404);

        return view('companies.show', $payload);
    }
}
