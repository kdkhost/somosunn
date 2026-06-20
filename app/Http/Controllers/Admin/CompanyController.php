<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyRequest;
use App\Models\Company;
use App\Services\CompanyService;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService
    ) {
    }

    public function index()
    {
        $companies = $this->companyService->paginatedForAdmin();

        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('admin.companies.form', [
            'company' => new Company(),
            'users' => $this->companyService->assignableUsers(),
        ]);
    }

    public function store(CompanyRequest $request)
    {
        $company = $this->companyService->save($request->validated());

        return redirect()
            ->route('admin.companies.edit', $company)
            ->with('success', 'Empresa cadastrada com sucesso.');
    }

    public function edit(Company $company)
    {
        $company->load('memberships.user', 'activeSponsor.plan');

        return view('admin.companies.form', [
            'company' => $company,
            'users' => $this->companyService->assignableUsers(),
        ]);
    }

    public function update(CompanyRequest $request, Company $company)
    {
        $company = $this->companyService->save($request->validated(), $company);

        return redirect()
            ->route('admin.companies.edit', $company)
            ->with('success', 'Empresa atualizada com sucesso.');
    }

    public function destroy(Company $company)
    {
        if ($company->logo) {
            \App\Support\UploadStorage::delete($company->logo);
        }

        if ($company->banner) {
            \App\Support\UploadStorage::delete($company->banner);
        }

        $company->delete();

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Empresa removida com sucesso.');
    }
}
