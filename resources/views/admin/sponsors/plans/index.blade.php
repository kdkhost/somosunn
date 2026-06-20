@extends('admin.layouts.app')

@section('title', 'Planos de Patrocinio')

@section('content')
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">Planos de patrocinio</h1>
            <a href="{{ route('admin.sponsor-plans.create') }}" class="btn btn-primary">Novo plano</a>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card"><div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Plano</th><th>Preco</th><th>Banners</th><th>Eventos</th><th>Leads</th><th>Status</th><th class="text-right">Acoes</th></tr></thead>
                    <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td>{{ $plan->name }}</td>
                            <td>R$ {{ number_format((float) $plan->price, 2, ',', '.') }}</td>
                            <td>{{ $plan->max_banners }}</td>
                            <td>{{ $plan->max_events }}</td>
                            <td>{{ $plan->max_leads }}</td>
                            <td><span class="badge badge-{{ $plan->active ? 'success' : 'secondary' }}">{{ $plan->active ? 'Ativo' : 'Inativo' }}</span></td>
                            <td class="text-right"><a href="{{ route('admin.sponsor-plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Nenhum plano cadastrado.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div><div class="card-footer">{{ $plans->links() }}</div></div>
        </div>
    </section>
@endsection
