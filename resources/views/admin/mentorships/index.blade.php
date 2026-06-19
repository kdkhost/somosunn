@extends('admin.layouts.app')

@section('page_title', 'Mentorias')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-indigo elevation-1">
                <span class="info-box-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de mentorias</span>
                    <span class="info-box-number">{{ $items->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-success elevation-1">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Com vagas</span>
                    <span class="info-box-number">{{ $items->filter(fn ($i) => $i->slots && $i->slots > 0)->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-info elevation-1">
                <span class="info-box-icon"><i class="fas fa-user-tie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Mentores</span>
                    <span class="info-box-number">{{ $items->pluck('mentor_id')->unique()->filter()->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="info-box bg-gradient-warning elevation-1">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Preco medio</span>
                    <span class="info-box-number">R$ {{ $items->count() > 0 ? number_format($items->avg('price'), 2, ',', '.') : '0,00' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chalkboard-teacher mr-2"></i>Gestao de mentorias</h3>
            <div class="card-tools">
                <a href="{{ route('admin.mentorships.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1">
                    <i class="fas fa-plus mr-1"></i> Nova mentoria
                </a>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3"><i class="fas fa-info-circle mr-1"></i>Cadastre e organize mentorias para exibicao no site.</p>

            <form method="GET" class="mb-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" name="q" class="form-control" placeholder="Buscar por titulo ou descricao" value="{{ $search ?? '' }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary rounded-right" type="submit">Buscar</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 60px;"><i class="fas fa-hashtag text-muted mr-1"></i>#</th>
                            <th><i class="fas fa-book text-muted mr-1"></i>Titulo</th>
                            <th><i class="fas fa-user-tie text-muted mr-1"></i>Mentor</th>
                            <th style="width: 100px;"><i class="fas fa-chair text-muted mr-1"></i>Vagas</th>
                            <th style="width: 130px;"><i class="fas fa-coins text-muted mr-1"></i>Preco</th>
                            <th style="width: 140px;"><i class="fas fa-chart-line text-muted mr-1"></i>Vendas</th>
                            <th class="text-right" style="width: 180px;">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td><span class="badge badge-light border">{{ $item->id }}</span></td>
                                <td>
                                    <div class="font-weight-bold">{{ $item->title }}</div>
                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item->description), 70) }}</small>
                                </td>
                                <td>
                                    @if($item->mentor)
                                        <span class="badge badge-info"><i class="fas fa-user mr-1"></i>{{ $item->mentor->name }}</span>
                                    @else
                                        <span class="badge badge-secondary"><i class="fas fa-user-slash mr-1"></i>Nao definido</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->slots)
                                        <span class="badge badge-success"><i class="fas fa-users mr-1"></i>{{ $item->slots }}</span>
                                    @else
                                        <span class="badge badge-light border"><i class="fas fa-infinity mr-1"></i>Ilimitado</span>
                                    @endif
                                </td>
                                <td class="font-weight-bold text-success">R$ {{ number_format((float) $item->price, 2, ',', '.') }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold">{{ (int) ($item->sales_count ?? 0) }} venda(s)</span>
                                        <small class="text-muted">{{ (int) ($item->buyers_count ?? 0) }} comprador(es)</small>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.mentorships.edit', $item) }}" class="btn btn-sm btn-outline-info rounded-pill elevation-1 mr-1" title="Editar">
                                        <i class="fas fa-edit mr-1"></i>Editar
                                    </a>
                                    <form action="{{ route('admin.mentorships.destroy', $item) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger rounded-pill elevation-1" data-confirm-delete
                                            data-confirm-title="Remover esta mentoria?"
                                            data-confirm-text="Esta acao nao pode ser desfeita." title="Excluir">
                                            <i class="fas fa-trash mr-1"></i>Excluir
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-2">Nenhuma mentoria encontrada.</p>
                                    <a href="{{ route('admin.mentorships.create') }}" class="btn btn-primary btn-sm rounded-pill elevation-1">
                                        <i class="fas fa-plus mr-1"></i> Criar primeira mentoria
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $items->links() }}</div>
        </div>
    </div>
</div>
@endsection
