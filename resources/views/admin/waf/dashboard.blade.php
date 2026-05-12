@extends('admin.layouts.app')

@section('title', 'Firewall (WAF) - Painel de Seguranca')

@push('styles')
<style>
.waf-kpi-card {
    border-radius: 10px;
    padding: 16px 20px;
    color: #fff;
    position: relative;
    overflow: hidden;
    min-height: 90px;
    transition: transform .15s ease, box-shadow .15s ease;
}
.waf-kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.15); }
.waf-kpi-card .kpi-icon { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-size: 2rem; opacity: .25; }
.waf-kpi-card .kpi-value { font-size: 1.6rem; font-weight: 700; line-height: 1.2; }
.waf-kpi-card .kpi-label { font-size: .78rem; opacity: .85; margin-top: 2px; }
.waf-kpi-inspected { background: linear-gradient(135deg, #1F5EDB 0%, #177FD6 100%); }
.waf-kpi-blocked   { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
.waf-kpi-monitored { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.waf-kpi-challenged{ background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); }
.waf-card { border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
.waf-card .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; padding: 12px 16px; }
.waf-card .card-header .card-title { font-size: .85rem; font-weight: 600; color: #374151; }
.waf-mode-badge { font-size: .7rem; padding: 5px 12px; border-radius: 20px; font-weight: 600; letter-spacing: .3px; }
.waf-mode-detection { background: #fef3cd; color: #856404; border: 1px solid #ffc107; }
.waf-mode-enforce { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="font-size:1.4rem; font-weight:700; color:#1e293b;">
                    <i class="fas fa-shield-alt" style="color:#1F5EDB;"></i> Firewall (WAF)
                </h1>
                <small class="text-muted">Monitoramento de seguranca em tempo real</small>
            </div>
            <div class="col-sm-6 text-right">
                <span class="waf-mode-badge {{ $wafSettings->isEnforce() ? 'waf-mode-enforce' : 'waf-mode-detection' }}">
                    {{ $wafSettings->isEnforce() ? 'MODO ENFORCE' : 'MODO DETECCAO' }}
                </span>
                <button class="btn btn-sm btn-outline-primary ml-2" id="btn-toggle-mode" data-current="{{ $wafSettings->mode }}" style="border-radius:20px; font-size:.75rem; padding:4px 14px;">
                    <i class="fas fa-exchange-alt"></i> Alternar
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
                As tabelas do WAF ainda nao foram criadas. Execute <code>php artisan migrate</code> para ativar.
            </div>
        @endif

        {{-- KPIs compactos --}}
        <div class="row mb-3">
            <div class="col-lg-3 col-sm-6 mb-2">
                <div class="waf-kpi-card waf-kpi-inspected">
                    <i class="fas fa-eye kpi-icon"></i>
                    <div class="kpi-value" id="kpi-inspected">0</div>
                    <div class="kpi-label">Inspecionadas (24h)</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-2">
                <div class="waf-kpi-card waf-kpi-blocked">
                    <i class="fas fa-ban kpi-icon"></i>
                    <div class="kpi-value" id="kpi-blocked">0</div>
                    <div class="kpi-label">Bloqueadas</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-2">
                <div class="waf-kpi-card waf-kpi-monitored">
                    <i class="fas fa-exclamation-triangle kpi-icon"></i>
                    <div class="kpi-value" id="kpi-monitored">0</div>
                    <div class="kpi-label">Monitoradas</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-2">
                <div class="waf-kpi-card waf-kpi-challenged">
                    <i class="fas fa-puzzle-piece kpi-icon"></i>
                    <div class="kpi-value" id="kpi-challenged">0</div>
                    <div class="kpi-label">Desafiadas</div>
                </div>
            </div>
        </div>

        {{-- Graficos --}}
        <div class="row">
            <div class="col-lg-8 mb-3">
                <div class="card waf-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-area mr-1"></i> Linha do Tempo (24h)</h3>
                    </div>
                    <div class="card-body" style="padding:12px;">
                        <canvas id="waf-timeline-chart" height="180"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card waf-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Tipos de Ataque</h3>
                    </div>
                    <div class="card-body" style="padding:12px;">
                        <canvas id="waf-pattern-chart" height="180"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabelas --}}
        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="card waf-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-crosshairs mr-1 text-danger"></i> Top 10 IPs Atacantes</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0" id="waf-top-ips">
                            <thead class="thead-light"><tr><th>IP</th><th>Pais</th><th class="text-right">Eventos</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card waf-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-route mr-1 text-warning"></i> Top 10 Rotas Atacadas</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0" id="waf-top-routes">
                            <thead class="thead-light"><tr><th>Rota</th><th class="text-right">Eventos</th></tr></thead>
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
    var pollInterval = 30000;
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
        animateValue('#kpi-inspected', kpis.inspected);
        animateValue('#kpi-blocked', kpis.blocked);
        animateValue('#kpi-monitored', kpis.monitored);
        animateValue('#kpi-challenged', kpis.challenged);
    }

    function animateValue(selector, value) {
        $(selector).text(Number(value || 0).toLocaleString('pt-BR'));
    }

    function updateTimeline(timeline) {
        var labels = timeline.map(function(t) { return t.ts ? t.ts.substring(11, 16) : ''; });
        var blocked = timeline.map(function(t) { return t.blocked || 0; });
        var monitored = timeline.map(function(t) { return t.monitored || 0; });

        if (timelineChart) timelineChart.destroy();

        timelineChart = new Chart(document.getElementById('waf-timeline-chart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Bloqueadas', data: blocked, borderColor: '#dc3545', backgroundColor: 'rgba(220,53,69,.08)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0 },
                    { label: 'Monitoradas', data: monitored, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.08)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }, scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } } }
        });
    }

    function updatePatternChart(byPattern) {
        var labels = Object.keys(byPattern);
        var values = Object.values(byPattern);

        if (patternChart) patternChart.destroy();

        patternChart = new Chart(document.getElementById('waf-pattern-chart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{ data: values, backgroundColor: ['#1F5EDB','#177FD6','#1D3FC4','#dc3545','#f59e0b','#10b981','#6f42c1','#fd7e14','#14b8a6','#ec4899'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 }, padding: 8 } } } }
        });
    }

    function updateTopIps(ips) {
        var html = '';
        (ips || []).forEach(function(ip, i) {
            html += '<tr><td><code class="text-dark">' + ip.ip + '</code></td><td>' + (ip.country || '—') + '</td><td class="text-right"><span class="badge badge-danger">' + ip.count + '</span></td></tr>';
        });
        $('#waf-top-ips tbody').html(html || '<tr><td colspan="3" class="text-center text-muted py-3"><i class="fas fa-check-circle text-success"></i> Nenhum ataque registrado</td></tr>');
    }

    function updateTopRoutes(routes) {
        var html = '';
        (routes || []).forEach(function(r) {
            html += '<tr><td><code class="text-dark">' + r.route + '</code></td><td class="text-right"><span class="badge badge-warning">' + r.count + '</span></td></tr>';
        });
        $('#waf-top-routes tbody').html(html || '<tr><td colspan="2" class="text-center text-muted py-3"><i class="fas fa-check-circle text-success"></i> Nenhum ataque registrado</td></tr>');
    }

    // Alternar modo com SweetAlert2
    $('#btn-toggle-mode').on('click', function() {
        var current = $(this).data('current');
        var newMode = current === 'detection-only' ? 'enforce' : 'detection-only';
        var newLabel = newMode === 'enforce' ? 'ENFORCE (bloqueio ativo)' : 'DETECCAO (apenas monitorar)';

        Swal.fire({
            title: 'Alterar modo do Firewall?',
            html: 'O WAF sera alterado para:<br><strong>' + newLabel + '</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1F5EDB',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Confirmar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            $.post('{{ route("admin.waf.mode") }}', { _token: '{{ csrf_token() }}', mode: newMode }, function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Modo alterado!', text: 'Firewall agora em ' + newLabel, timer: 2000, showConfirmButton: false }).then(function() { location.reload(); });
                }
            }).fail(function() {
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Nao foi possivel alterar o modo.' });
            });
        });
    });

    fetchData();
    setInterval(fetchData, pollInterval);
})();
</script>
@endpush
