@extends('admin.layouts.app')

@section('title', 'Produtos do marketplace')
@section('page_title', 'Produtos do marketplace')

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-boxes-stacked mr-2"></i>Produtos das lojas</h3>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Monitore o catalogo proprio dos vendedores e modere o status de publicacao.</p>
            <form method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Buscar por titulo, SKU ou loja">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search mr-1"></i> Buscar</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Loja</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th class="text-right">Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="font-weight-bold">{{ $product->title }}</td>
                                <td>{{ $product->store->brand_name ?? '-' }}</td>
                                <td>{{ strtoupper($product->type) }}</td>
                                <td><span class="badge badge-secondary">{{ strtoupper($product->status) }}</span></td>
                                <td class="text-right">
                                    <form action="{{ route('admin.marketplace.catalog.toggle', $product) }}" method="POST" class="form-inline justify-content-end">
                                        @csrf
                                        <select name="status" class="form-control form-control-sm mr-2">
                                            @foreach(['draft' => 'Rascunho', 'published' => 'Publicado', 'blocked' => 'Bloqueado'] as $value => $label)
                                                <option value="{{ $value }}" {{ $product->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Salvar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Nenhum produto encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($products, 'links'))
            <div class="card-footer">{{ $products->links() }}</div>
        @endif
    </div>
@endsection
