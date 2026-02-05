@extends('admin.layouts.app')

@section('title', 'Próximos Eventos - UNN')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Próximos Eventos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Eventos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">

            @if(isset($isDemo) && $isDemo)
                <div class="alert alert-warning">
                    <i class="icon fas fa-exclamation-triangle"></i>
                    <strong>Dados de Demonstração:</strong> Estes eventos são exemplos. Configure eventos reais no painel
                    administrativo.
                </div>
            @endif

            <div class="card">
                <div class="card-header bg-white border-bottom-0">
                    <h3 class="card-title">Agenda de Eventos</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Data</th>
                                    <th style="width: 30%">Evento</th>
                                    <th style="width: 20%">Localização</th>
                                    <th style="width: 15%">Vagas</th>
                                    <th style="width: 10%">Valor</th>
                                    <th style="width: 10%">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                    @php
                                        $startDate = is_string($event->start_at) ? \Carbon\Carbon::parse($event->start_at) : $event->start_at;
                                        $endDate = is_string($event->end_at) ? \Carbon\Carbon::parse($event->end_at) : $event->end_at;
                                    @endphp
                                    <tr>
                                        <td class="align-middle">
                                            <div class="text-center bg-light rounded p-2 border" style="width: 60px;">
                                                <span
                                                    class="d-block font-weight-bold text-lg leading-none">{{ $startDate->format('d') }}</span>
                                                <span
                                                    class="d-block text-uppercase small font-weight-bold text-muted">{{ $startDate->translatedFormat('M') }}</span>
                                            </div>
                                            <small
                                                class="d-block mt-1 text-muted text-center">{{ $startDate->format('H:i') }}</small>
                                        </td>
                                        <td class="align-middle">
                                            <h5 class="font-weight-bold mb-1 text-primary">{{ $event->title }}</h5>
                                            <span class="text-muted small"><i class="fas fa-user-tie mr-1"></i>
                                                {{ $event->speaker }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="d-block"><i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                                {{ $event->location }}</span>
                                            <small class="text-muted d-block text-truncate"
                                                style="max-width: 200px;">{{ $event->address }}</small>
                                        </td>
                                        <td class="align-middle">
                                            @if($event->capacity)
                                                <div class="progress progress-sm rounded mb-1" style="height: 6px;">
                                                    <div class="progress-bar bg-success" style="width: {{ rand(30, 80) }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $event->capacity }} vagas totais</small>
                                            @else
                                                <span class="badge badge-success">Ilimitado</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            @if($event->current_price > 0 || $event->price > 0)
                                                <span class="font-weight-bold text-success">R$
                                                    {{ number_format($event->current_price ?: $event->price, 2, ',', '.') }}</span>
                                            @else
                                                <span class="badge badge-success">GRÁTIS</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-right">
                                            <a href="{{ isset($isDemo) && $isDemo ? '#' : route('events.show', $event->id) }}"
                                                class="btn btn-primary btn-sm shadow-sm">
                                                <i class="fas fa-ticket-alt mr-1"></i> Detalhes
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                                <h5>Nenhum evento próximo encontrado</h5>
                                                <p>Fique atento, novas datas serão liberadas em breve.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection