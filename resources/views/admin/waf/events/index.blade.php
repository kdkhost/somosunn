@extends('admin.layouts.app')

@section('title', 'WAF - Eventos')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-list-alt text-primary"></i> WAF - Eventos</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.waf.events.export', request()->query()) }}&format=csv" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-file-csv"></i> Exportar CSV
                </a>
                <a href="{{ route('admin.waf.events.export', request()->query()) }}&format=json" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-file-code"></i> Exportar JSON
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(!$hasTable)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Tabela <code>waf_events</code> nao encontrada. Execute <code>php artisan migrate</code>.
            </div>
        @else
            {{-- Filtros --}}
            <div class="card card-outline card-primary collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.waf.events.index') }}">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Data Inicio</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Data Fim</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>IP</label>
                                    <input type="text" name="ip" class="form-control form-control-sm" value="{{ request('ip') }}" placeholder="192.168...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Rota</label>
                                    <input type="text" name="route" class="form-control form-control-sm" value="{{ request('route') }}" placeholder="/api/...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Metodo</label>
                                    <select name="method" class="form-control form-control-sm">
                                        <option value="">Todos</option>
                                        @foreach(['GET','POST','PUT','PATCH','DELETE'] as $m)
                                            <option value="{{ $m }}" {{ request('method') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Decisao</label>
                                    <select name="decision" class="form-control form-control-sm">
                                        <option value="">Todas</option>
                                        <option value="blocked" {{ request('decision') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                                        <option value="monitored" {{ request('decision') === 'monitored' ? 'selected' : '' }}>Monitored</option>
                                        <option value="challenged" {{ request('decision') === 'challenged' ? 'selected' : '' }}>Challenged</option>
                                        <option value="allowed" {{ request('decision') === 'allowed' ? 'selected' : '' }}>Allowed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Risk Score Min</label>
                                    <input type="number" name="risk_score_min" class="form-control form-control-sm" value="{{ request('risk_score_min') }}" min="0" max="100">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Attack Pattern</label>
                                    <input type="text" name="attack_pattern" class="form-control form-control-sm" value="{{ request('attack_pattern') }}" placeholder="sqli, xss...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Busca Livre</label>
                                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="IP, rota, user-agent...">
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                                    <a href="{{ route('admin.waf.events.index') }}" class="btn btn-sm btn-default ml-1">Limpar</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabela --}}
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-sm table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>IP</th>
                                <th>Metodo</th>
                                <th>Rota</th>
                                <th>Score</th>
                                <th>Decisao</th>
                                <th>Pais</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $event)
                                <tr>
                                    <td class="text-nowrap">{{ $event->occurred_at?->format('d/m/Y H:i') }}</td>
                                    <td><code>{{ $event->ip }}</code></td>
                                    <td><span class="badge badge-secondary">{{ $event->method }}</span></td>
                                    <td><small>{{ \Illuminate\Support\Str::limit($event->route ?? $event->path, 40) }}</small></td>
                                    <td>
                                        <span class="badge badge-{{ $event->risk_score >= 80 ? 'danger' : ($event->risk_score >= 50 ? 'warning' : 'info') }}">
                                            {{ $event->risk_score }}
                                        </span>
                                    </td>
                                    <td>
                                        @switch($event->decision)
                                            @case('blocked')
                                                <span class="badge badge-danger">Blocked</span>
                                                @break
                                            @case('monitored')
                                                <span class="badge badge-warning">Monitored</span>
                                                @break
                                            @case('challenged')
                                                <span class="badge" style="background-color:#6f42c1;color:#fff;">Challenged</span>
                                                @break
                                            @default
                                                <span class="badge badge-success">Allowed</span>
                                        @endswitch
                                        @if($event->is_false_positive)
                                            <span class="badge badge-light" title="Falso Positivo"><i class="fas fa-flag"></i></span>
                                        @endif
                                    </td>
                                    <td>{{ $event->country ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.waf.events.show', $event->id) }}" class="btn btn-xs btn-info" title="Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Nenhum evento encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($hasTable && $events instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer clearfix">
                        {{ $events->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
@endsection
