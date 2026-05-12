@extends('admin.layouts.app')

@section('title', 'WAF - Alertas')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-bell text-primary"></i> WAF - Alertas</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if(!$hasTable)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Tabela <code>waf_alerts_config</code> nao encontrada. Execute <code>php artisan migrate</code>.
            </div>
        @else
            <div class="row">
                {{-- Formulario de adicao --}}
                <div class="col-md-4">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Novo Alerta</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.waf.alerts.store') }}">
                                @csrf
                                <div class="form-group">
                                    <label>Canal <span class="text-danger">*</span></label>
                                    <select name="channel" class="form-control form-control-sm" required>
                                        <option value="email">E-mail</option>
                                        <option value="webhook">Webhook</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Destino <span class="text-danger">*</span></label>
                                    <input type="text" name="target" class="form-control form-control-sm" required
                                           placeholder="email@exemplo.com ou https://..." value="{{ old('target') }}">
                                    <small class="text-muted">E-mail ou URL do webhook.</small>
                                </div>
                                <div class="form-group">
                                    <label>Gatilho <span class="text-danger">*</span></label>
                                    <select name="trigger" class="form-control form-control-sm" required>
                                        <option value="block_spike">Pico de Bloqueios</option>
                                        <option value="auto_block">Auto-Bloqueio de IP</option>
                                        <option value="critical_finding">Achado Critico</option>
                                        <option value="ip_reputation">Reputacao de IP</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Threshold (JSON, opcional)</label>
                                    <textarea name="threshold" class="form-control form-control-sm" rows="2"
                                              placeholder='{"count": 10, "window_minutes": 5}'>{{ old('threshold') }}</textarea>
                                    <small class="text-muted">Configuracao de limiar em JSON.</small>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-plus"></i> Criar Alerta
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Tabela --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Alertas Configurados</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Canal</th>
                                        <th>Destino</th>
                                        <th>Gatilho</th>
                                        <th>Ativo</th>
                                        <th>Criado em</th>
                                        <th>Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($alerts as $alert)
                                        <tr>
                                            <td>
                                                <span class="badge badge-{{ $alert->channel === 'email' ? 'info' : 'secondary' }}">
                                                    <i class="fas fa-{{ $alert->channel === 'email' ? 'envelope' : 'link' }}"></i>
                                                    {{ ucfirst($alert->channel) }}
                                                </span>
                                            </td>
                                            <td><small>{{ \Illuminate\Support\Str::limit($alert->target, 40) }}</small></td>
                                            <td>
                                                @switch($alert->trigger)
                                                    @case('block_spike')
                                                        <span class="badge badge-danger">Pico Bloqueios</span>
                                                        @break
                                                    @case('auto_block')
                                                        <span class="badge badge-warning">Auto-Block</span>
                                                        @break
                                                    @case('critical_finding')
                                                        <span class="badge" style="background-color:#6f42c1;color:#fff;">Critico</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-info">IP Reputation</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($alert->is_active)
                                                    <span class="badge badge-success">Sim</span>
                                                @else
                                                    <span class="badge badge-secondary">Nao</span>
                                                @endif
                                            </td>
                                            <td>{{ $alert->created_at?->format('d/m/Y') }}</td>
                                            <td class="text-nowrap">
                                                <form method="POST" action="{{ route('admin.waf.alerts.update', $alert->id) }}" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="channel" value="{{ $alert->channel }}">
                                                    <input type="hidden" name="target" value="{{ $alert->target }}">
                                                    <input type="hidden" name="trigger" value="{{ $alert->trigger }}">
                                                    <input type="hidden" name="is_active" value="{{ $alert->is_active ? '0' : '1' }}">
                                                    <button type="submit" class="btn btn-xs btn-{{ $alert->is_active ? 'warning' : 'success' }}" title="{{ $alert->is_active ? 'Desativar' : 'Ativar' }}">
                                                        <i class="fas fa-{{ $alert->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.waf.alerts.destroy', $alert->id) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Remover este alerta?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Nenhum alerta configurado.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($alerts instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="card-footer clearfix">
                                {{ $alerts->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
