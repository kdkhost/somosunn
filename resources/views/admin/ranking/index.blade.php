@extends('admin.layouts.app')

@section('page_title','Ranking')
@section('breadcrumb')<li class="breadcrumb-item active">Ranking</li>@endsection

@section('content')
    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-primary elevation-1">
                <span class="info-box-icon"><i class="fas fa-trophy"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total no Ranking</span>
                    <span class="info-box-number">{{ count($top) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-success elevation-1">
                <span class="info-box-icon"><i class="fas fa-medal"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Maior Pontuação</span>
                    <span class="info-box-number">{{ count($top) > 0 ? number_format($top[0]->points ?? 0) : 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-warning elevation-1">
                <span class="info-box-icon"><i class="fas fa-star"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Média de Pontos</span>
                    <span class="info-box-number">{{ count($top) > 0 ? number_format(collect($top)->avg('points'), 0) : 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-info elevation-1">
                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de Pontos</span>
                    <span class="info-box-number">{{ number_format(collect($top)->sum('points')) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-trophy text-warning mr-2"></i>Top Usuários
            </h3>
        </div>
        <div class="card-body p-0">
            @if(count($top) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width:60px" class="text-center">Pos.</th>
                                <th>Usuário</th>
                                <th class="text-right">Pontos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($top as $i => $u)
                                <tr>
                                    <td class="text-center">
                                        @if($i === 0)
                                            <span class="badge badge-warning px-2 py-1"><i class="fas fa-crown mr-1"></i>1º</span>
                                        @elseif($i === 1)
                                            <span class="badge badge-secondary px-2 py-1"><i class="fas fa-medal mr-1"></i>2º</span>
                                        @elseif($i === 2)
                                            <span class="badge badge-info px-2 py-1"><i class="fas fa-medal mr-1"></i>3º</span>
                                        @else
                                            <span class="text-muted font-weight-bold">{{ $i + 1 }}º</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">{{ $u->name }}</div>
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-success px-2 py-1">
                                            <i class="fas fa-coins mr-1"></i>{{ number_format($u->points) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Nenhum usuário no ranking ainda.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
