@extends('admin.layouts.app')

@section('title', 'WAF - Evento #' . $event->id)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-search text-primary"></i> Evento WAF #{{ $event->id }}</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.waf.events.index') }}" class="btn btn-sm btn-default">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
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

        <div class="row">
            {{-- Info Principal --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Informacoes do Evento</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm">
                            <tr><th style="width:35%">UID</th><td><code>{{ $event->uid }}</code></td></tr>
                            <tr><th>Request ID</th><td><code>{{ $event->request_id ?? '-' }}</code></td></tr>
                            <tr><th>Data</th><td>{{ $event->occurred_at?->format('d/m/Y H:i:s') }}</td></tr>
                            <tr><th>IP</th><td><code>{{ $event->ip }}</code></td></tr>
                            <tr><th>Pais / ASN</th><td>{{ $event->country ?? '-' }} / {{ $event->asn ?? '-' }}</td></tr>
                            <tr><th>Metodo</th><td><span class="badge badge-secondary">{{ $event->method }}</span></td></tr>
                            <tr><th>Rota</th><td><code>{{ $event->route ?? '-' }}</code></td></tr>
                            <tr><th>Path</th><td><code>{{ $event->path ?? '-' }}</code></td></tr>
                            <tr><th>Status HTTP</th><td>{{ $event->status ?? '-' }}</td></tr>
                            <tr><th>Risk Score</th><td>
                                <span class="badge badge-{{ $event->risk_score >= 80 ? 'danger' : ($event->risk_score >= 50 ? 'warning' : 'info') }} p-2">
                                    {{ $event->risk_score }}/100
                                </span>
                            </td></tr>
                            <tr><th>Decisao</th><td>
                                @switch($event->decision)
                                    @case('blocked')
                                        <span class="badge badge-danger p-2">BLOCKED</span>
                                        @break
                                    @case('monitored')
                                        <span class="badge badge-warning p-2">MONITORED</span>
                                        @break
                                    @case('challenged')
                                        <span class="badge p-2" style="background-color:#6f42c1;color:#fff;">CHALLENGED</span>
                                        @break
                                    @default
                                        <span class="badge badge-success p-2">ALLOWED</span>
                                @endswitch
                            </td></tr>
                            <tr><th>User Agent</th><td><small>{{ $event->user_agent ?? '-' }}</small></td></tr>
                            <tr><th>Referrer</th><td><small>{{ $event->referrer ?? '-' }}</small></td></tr>
                            <tr><th>Falso Positivo</th><td>
                                @if($event->is_false_positive)
                                    <span class="badge badge-warning"><i class="fas fa-flag"></i> Sim</span>
                                @else
                                    <span class="text-muted">Nao</span>
                                @endif
                            </td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Acoes --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-bolt"></i> Acoes</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if(!$event->is_false_positive)
                                <div class="col-md-12 mb-3">
                                    <form method="POST" action="{{ route('admin.waf.events.false-positive', $event->id) }}">
                                        @csrf
                                        <div class="input-group">
                                            <input type="text" name="note" class="form-control form-control-sm" placeholder="Nota (opcional)">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Marcar como falso positivo?')">
                                                    <i class="fas fa-flag"></i> Falso Positivo
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('admin.waf.events.block-ip', $event->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-block btn-sm" onclick="return confirm('Bloquear IP {{ $event->ip }}?')">
                                        <i class="fas fa-ban"></i> Bloquear IP
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <form method="POST" action="{{ route('admin.waf.events.allow-ip', $event->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-block btn-sm" onclick="return confirm('Permitir IP {{ $event->ip }}?')">
                                        <i class="fas fa-check"></i> Permitir IP
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Regras Disparadas --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-gavel"></i> Regras Disparadas</h3>
                    </div>
                    <div class="card-body">
                        @if($event->rules_fired && count($event->rules_fired) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead><tr><th>Regra</th><th>Pattern</th><th>Score</th><th>Acao</th></tr></thead>
                                    <tbody>
                                        @foreach($event->rules_fired as $rule)
                                            <tr>
                                                <td>{{ $rule['name'] ?? $rule['uid'] ?? '-' }}</td>
                                                <td><code>{{ $rule['attack_pattern'] ?? '-' }}</code></td>
                                                <td>{{ $rule['score'] ?? '-' }}</td>
                                                <td>{{ $rule['action'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">Nenhuma regra disparada registrada.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Amostras (mascaradas) --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-code"></i> Amostras (dados mascarados)</h3>
                    </div>
                    <div class="card-body">
                        @if($event->samples && count($event->samples) > 0)
                            <pre class="bg-dark text-light p-3 rounded" style="max-height:400px;overflow:auto;"><code>{{ json_encode($event->samples, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                        @else
                            <p class="text-muted mb-0">Nenhuma amostra registrada.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
