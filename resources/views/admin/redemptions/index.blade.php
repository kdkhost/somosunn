@extends('admin.layouts.app')

@section('page_title', 'Resgate de Pontos')
@section('breadcrumb')<li class="breadcrumb-item active">Resgate de Pontos</li>@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            {{-- Pendentes --}}
            <div class="card card-warning card-outline mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-clock mr-2"></i>Solicitações Pendentes</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Item</th>
                                <th>Custo em Pontos</th>
                                <th>Data</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingRedemptions as $redemption)
                                <tr>
                                    <td>{{ $redemption->user->name }}</td>
                                    <td>{{ $redemption->item->name }}</td>
                                    <td>{{ $redemption->points_spent }} pts</td>
                                    <td>{{ $redemption->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-right">
                                        <form action="{{ route('admin.redemptions.approve', $redemption) }}" method="POST"
                                            style="display:inline-block">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success mr-1 btn-delete" data-text="Deseja confirmar a entrega deste item?"><i
                                                    class="fas fa-check mr-1"></i> Aprovar</button>
                                        </form>
                                        <form action="{{ route('admin.redemptions.cancel', $redemption) }}" method="POST"
                                            style="display:inline-block">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger btn-delete" data-text="Os pontos serão devolvidos ao usuário. Continuar?"><i
                                                    class="fas fa-times mr-1"></i> Cancelar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Nenhuma solicitação pendente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Itens de Resgate --}}
            <div class="card card-primary card-outline">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-gift mr-2"></i>Itens para Resgate</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.redemptions.create') }}"
                            class="btn btn-sm btn-primary font-weight-bold shadow-sm px-3">
                            <i class="fas fa-plus mr-1"></i> Novo Item
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Custo em Pontos</th>
                                <th>Estoque</th>
                                <th>Resgates</th>
                                <th>Status</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>
                                        @if($item->image)
                                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                                                class="img-circle img-size-32 mr-2">
                                        @else
                                            <div
                                                class="img-circle img-size-32 mr-2 bg-secondary d-inline-flex align-items-center justify-content-center">
                                                <i class="fas fa-gift text-xs"></i>
                                            </div>
                                        @endif
                                        {{ $item->name }}
                                    </td>
                                    <td>{{ $item->points_cost }} pts</td>
                                    <td>
                                        @if($item->stock < 5)
                                            <span class="text-danger font-weight-bold">{{ $item->stock }}</span>
                                        @else
                                            {{ $item->stock }}
                                        @endif
                                    </td>
                                    <td>{{ $item->redemptions_count }}</td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge badge-success">Ativo</span>
                                        @else
                                            <span class="badge badge-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.redemptions.edit', $item) }}" class="text-muted mr-2">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
