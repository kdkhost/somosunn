@extends('admin.layouts.app')

@section('title', 'Empresas')

@section('content')
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">Empresas</h1>
            <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Nova empresa
            </a>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Cidade</th>
                                <th class="text-center">Membros</th>
                                <th class="text-center">Patrocinios</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($companies as $company)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold">{{ $company->name }}</div>
                                        <div class="small text-muted">{{ $company->slug }}</div>
                                    </td>
                                    <td>{{ trim(($company->city ? $company->city . ' / ' : '') . $company->state) ?: '-' }}</td>
                                    <td class="text-center">{{ $company->memberships_count }}</td>
                                    <td class="text-center">{{ $company->sponsors_count }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $company->active ? 'success' : 'secondary' }}">{{ $company->active ? 'Ativa' : 'Inativa' }}</span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('companies.show', $company->slug) }}" target="_blank" class="btn btn-sm btn-outline-info">Publico</a>
                                        <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma empresa cadastrada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $companies->links() }}</div>
            </div>
        </div>
    </section>
@endsection
