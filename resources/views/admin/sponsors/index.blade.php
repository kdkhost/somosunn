@extends('admin.layouts.app')

@section('title', 'Patrocinadores')

@section('content')
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">Patrocinadores</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.sponsor-plans.index') }}" class="btn btn-outline-secondary">Planos</a>
                <a href="{{ route('admin.sponsor-banners.index') }}" class="btn btn-outline-secondary">Banners</a>
                <a href="{{ route('admin.sponsors.create') }}" class="btn btn-primary">Novo patrocinador</a>
            </div>
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
                                <th>Plano</th>
                                <th>Inicio</th>
                                <th>Fim</th>
                                <th>Status</th>
                                <th class="text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sponsors as $sponsor)
                                <tr>
                                    <td>{{ $sponsor->company?->name ?: '-' }}</td>
                                    <td>{{ $sponsor->plan?->name ?: '-' }}</td>
                                    <td>{{ optional($sponsor->starts_at)->format('d/m/Y H:i') ?: '-' }}</td>
                                    <td>{{ optional($sponsor->ends_at)->format('d/m/Y H:i') ?: '-' }}</td>
                                    <td><span class="badge badge-info text-uppercase">{{ $sponsor->status }}</span></td>
                                    <td class="text-right"><a href="{{ route('admin.sponsors.edit', $sponsor) }}" class="btn btn-sm btn-outline-primary">Editar</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum patrocinador cadastrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $sponsors->links() }}</div>
            </div>
        </div>
    </section>
@endsection
