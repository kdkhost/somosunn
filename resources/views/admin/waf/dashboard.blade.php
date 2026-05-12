@extends('admin.layouts.app')

@section('title', 'WAF - Dashboard de Segurança')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-shield-alt text-primary"></i> WAF - Dashboard</h1>
            </div>
            <div class="col-sm-6 text-right">
                <span class="badge badge-{{ $wafSettings->isEnforce() ? 'danger' : 'warning' }} p-2" id="waf-mode-badge">
                    Modo: {{ $wafSettings->isEnforce() ? 'ENFORCE' : 'DETECTION-ONLY' }}
                </span>
                <button class="btn btn-sm btn-outline-primary ml-2" id="btn-toggle-mode"
                    data-current="{{ $wafSettings->mode }}">
                    <i class="fas fa-exchange-alt"></i> Alternar Modo
                </button>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(!$hasTable)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                As tabelas do WAF ainda não foram criadas. Execute <code>php artisan migrate</code> para ativar o painel.
            </div>
        @endif

        <!-- KPIs -->
        <div class="row" id="waf-kpis">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="kpi-inspected">0</h3>
                        <p>Inspecionadas (24h)</p>
                    </div>
                    <div class="icon"><i class="fas fa-eye"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="kpi-blocked">0</h3>
                        <p>Bloqueadas</p>
                    </div>
                    <div class="icon"><i class="fas fa-ban"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="kpi-monitored">0</h3>
                        <p>Monitoradas</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3 id="kpi-challenged">0</h3>
                        <p>Desafiadas</p>
                    </div>
                    <div class="icon"><i class="fas fa-puzzle-piece"></i></div>
                </div>
            </div>
        </div>

        <!-- Timeline Chart -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-line"></i> Timeline (24h)</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="waf-timeline-chart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-pie"></i> Por Tipo de Ataque</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="waf-pattern-chart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top IPs e Rotas -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-globe"></i> Top 10 IPs Atacantes</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped" id="waf-top-ips">
                            <thead><tr><th>IP</th><th>País</th><th>Eventos</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-route"></i> Top 10 Rotas Atacadas</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped" id="waf-top-routes">
                            <thead><tr><th>Rota</th><th>Eventos</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function() {
    var pollInterval = parseInt(document.body.getAttribute('data-waf-poll') || '30') * 1000;
    var timelineChart = null;
    var patternChart = null;

    function fetchData() {
        $.get('{{ route("admin.waf.data") }}', function(data) {
            updateKpis(data.kpis);
            updateTimeline(data.timeline);
            updatePatternChart(data.by_pattern);
            updateTopIps(data.top_ips);
            updateTopRoutes(data.top_routes);
        });
    }

    function updateKpis(kpis) {
        $('#kpi-inspected').text(kpis.inspected.toLocaleString());
        $('#kpi-blocked').text(kpis.blocked.toLocaleString());
        $('#kpi-monitored').text(kpis.monitored.toLocaleString());
        $('#kpi-challenged').text(kpis.challenged.toLocaleString());
    }

    function updateTimeline(timeline) {
        var labels = timeline.map(function(t) { return t.ts ? t.ts.substring(11, 16) : ''; });
        var blocked = timeline.map(function(t) { return t.blocked || 0; });
        var monitored = timeline.map(function(t) { return t.monitored || 0; });

        if (timelineChart) { timelineChart.destroy(); }

        var ctx = document.getElementById('waf-timeline-chart').getContext('2d');
        timelineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Bloqueadas', data: blocked, borderColor: '#dc3545', fill: false, tension: 0.3 },
                    { label: 'Monitoradas', data: monitored, borderColor: '#ffc107', fill: false, tension: 0.3 }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }

    function updatePatternChart(byPattern) {
        var labels = Object.keys(byPattern);
        var values = Object.values(byPattern);

        if (patternChart) { patternChart.destroy(); }

        var ctx = document.getElementById('waf-pattern-chart').getContext('2d');
        patternChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{ data: values, backgroundColor: ['#1F5EDB','#177FD6','#1D3FC4','#dc3545','#ffc107','#28a745','#6f42c1','#fd7e14','#20c997','#e83e8c'] }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    }

    function updateTopIps(ips) {
        var html = '';
        (ips || []).forEach(function(ip) {
            html += '<tr><td><code>' + ip.ip + '</code></td><td>' + (ip.country || '?') + '</td><td>' + ip.count + '</td></tr>';
        });
        $('#waf-top-ips tbody').html(html || '<tr><td colspan="3" class="text-center text-muted">Nenhum dado</td></tr>');
    }

    function updateTopRoutes(routes) {
        var html = '';
        (routes || []).forEach(function(r) {
            html += '<tr><td><code>' + r.route + '</code></td><td>' + r.count + '</td></tr>';
        });
        $('#waf-top-routes tbody').html(html || '<tr><td colspan="2" class="text-center text-muted">Nenhum dado</td></tr>');
    }

    // Toggle mode
    $('#btn-toggle-mode').on('click', function() {
        var current = $(this).data('current');
        var newMode = current === 'detection-only' ? 'enforce' : 'detection-only';

        Swal.fire({
            title: 'Alterar modo do WAF?',
            html: 'Mudar para <strong>' + newMode.toUpperCase() + '</strong>?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1F5EDB',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, alterar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $.post('{{ route("admin.waf.mode") }}', { _token: '{{ csrf_token() }}', mode: newMode }, function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Modo alterado!', text: 'WAF agora em ' + newMode.toUpperCase(), timer: 2000, showConfirmButton: false }).then(function() { location.reload(); });
                }
            }).fail(function() {
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha ao alterar modo.' });
            });
        });
    });

    // Polling
    fetchData();
    setInterval(fetchData, pollInterval);
})();
</script>
@endpush
