@extends('admin.layouts.app')

@section('page_title', 'Regras de Pontuação')
@section('breadcrumb')
<li class="breadcrumb-item active">Pontuação</li>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('success') }}
    </div>
@endif

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><i class="fas fa-star mr-2"></i>Regras de Pontuação</h3>
        <a href="{{ route('admin.points-rules.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>Nova Regra
        </a>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            <i class="fas fa-info-circle mr-1"></i>
            Configure as ações que concedem pontos aos usuários. Os pontos são usados para o ranking da comunidade.
        </p>

        {{-- Estatísticas rápidas --}}
        <div class="row mb-4">
            @foreach($categories as $key => $cat)
                @php
                    $categoryRules = $rulesGrouped->get($key, collect());
                    $totalPoints = $categoryRules->where('active', true)->sum('points');
                    $count = $categoryRules->count();
                @endphp
                <div class="col-md-4 col-lg-2 mb-2">
                    <div class="small-box bg-{{ $cat['color'] }}">
                        <div class="inner">
                            <h4>{{ $count }}</h4>
                            <p>{{ $cat['label'] }}</p>
                        </div>
                        <div class="icon">
                            <i class="{{ $cat['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Cards por categoria --}}
@foreach($categories as $catKey => $cat)
    @php $rules = $rulesGrouped->get($catKey, collect()); @endphp
    @if($rules->count() > 0)
    <div class="card mb-4 border-{{ $cat['color'] }}">
        <div class="card-header bg-{{ $cat['color'] }} {{ in_array($cat['color'], ['warning', 'light']) ? 'text-dark' : 'text-white' }}">
            <h3 class="card-title mb-0">
                <i class="{{ $cat['icon'] }} mr-2"></i>{{ $cat['label'] }}
                <span class="badge badge-light ml-2">{{ $rules->count() }} regras</span>
            </h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th style="width:150px">Chave</th>
                            <th>Descrição</th>
                            <th style="width:100px" class="text-center">Pontos</th>
                            <th style="width:100px" class="text-center">Repetível</th>
                            <th style="width:80px" class="text-center">Status</th>
                            <th style="width:120px" class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rules->sortBy('sort_order') as $r)
                        <tr class="{{ !$r->active ? 'table-secondary' : '' }}">
                            <td class="text-center">
                                <i class="{{ $r->icon ?? 'fas fa-star' }} text-{{ $cat['color'] }}"></i>
                            </td>
                            <td>
                                <code>{{ $r->key }}</code>
                            </td>
                            <td>
                                <strong>{{ $r->label }}</strong>
                                @if($r->description ?? null)
                                    <br><small class="text-muted">{{ $r->description }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-{{ $r->points > 0 ? 'success' : 'danger' }} px-3 py-2" style="font-size: 1rem;">
                                    {{ $r->points > 0 ? '+' : '' }}{{ $r->points }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($r->repeatable ?? false)
                                    <span class="badge badge-info" title="Pode ser ganho múltiplas vezes">
                                        <i class="fas fa-sync-alt mr-1"></i>Sim
                                        @if($r->max_daily ?? null)
                                            <br><small>(máx {{ $r->max_daily }}/dia)</small>
                                        @endif
                                    </span>
                                @else
                                    <span class="badge badge-secondary">Única vez</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($r->active)
                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Ativa</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fas fa-pause mr-1"></i>Inativa</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.points-rules.edit', $r) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.points-rules.destroy', $r) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover"
                                        onclick="return confirm('Remover esta regra de pontuação?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
@endforeach

{{-- Regras sem categoria (inclui 'outros' do fallback) --}}
@php 
    $uncategorizedKeys = array_diff($rulesGrouped->keys()->toArray(), array_keys($categories));
    $uncategorized = collect();
    foreach ($uncategorizedKeys as $key) {
        $uncategorized = $uncategorized->merge($rulesGrouped->get($key, collect()));
    }
@endphp
@if($uncategorized->count() > 0)
<div class="card mb-4 border-secondary">
    <div class="card-header bg-secondary text-white">
        <h3 class="card-title mb-0">
            <i class="fas fa-folder mr-2"></i>Outras Regras
            <span class="badge badge-light ml-2">{{ $uncategorized->count() }} regras</span>
        </h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:40px"></th>
                        <th style="width:150px">Chave</th>
                        <th>Descrição</th>
                        <th style="width:100px" class="text-center">Pontos</th>
                        <th style="width:100px" class="text-center">Repetível</th>
                        <th style="width:80px" class="text-center">Status</th>
                        <th style="width:120px" class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($uncategorized as $r)
                    <tr class="{{ !$r->active ? 'table-secondary' : '' }}">
                        <td class="text-center">
                            <i class="{{ $r->icon ?? 'fas fa-star' }} text-secondary"></i>
                        </td>
                        <td><code>{{ $r->key }}</code></td>
                        <td>
                            <strong>{{ $r->label }}</strong>
                            @if($r->description ?? null)
                                <br><small class="text-muted">{{ $r->description }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $r->points > 0 ? 'success' : 'danger' }} px-3 py-2" style="font-size: 1rem;">
                                {{ $r->points > 0 ? '+' : '' }}{{ $r->points }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($r->repeatable ?? false)
                                <span class="badge badge-info">Sim</span>
                            @else
                                <span class="badge badge-secondary">Única vez</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($r->active)
                                <span class="badge badge-success">Ativa</span>
                            @else
                                <span class="badge badge-secondary">Inativa</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.points-rules.edit', $r) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.points-rules.destroy', $r) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Remover esta regra?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Info box se não houver regras --}}
@if($rulesGrouped->flatten(1)->count() == 0)
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-star fa-4x text-muted mb-3"></i>
        <h4 class="text-muted">Nenhuma regra de pontuação cadastrada</h4>
        <p class="text-muted mb-4">Crie regras para recompensar a participação dos usuários.</p>
        <a href="{{ route('admin.points-rules.create') }}" class="btn btn-primary btn-lg">
            <i class="fas fa-plus mr-2"></i>Criar primeira regra
        </a>
    </div>
</div>
@endif
@endsection