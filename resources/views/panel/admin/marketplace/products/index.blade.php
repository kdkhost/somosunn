@extends('panel.layouts.app')

@section('title', 'Produtos do marketplace - UNN')

@section('panel_content')
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 p-6 md:p-8 shadow-sm">
            <h1 class="text-3xl font-black text-slate-900 dark:text-white">Produtos das lojas</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Monitore o catalogo proprio dos vendedores e modere o status de publicacao.</p>
        </div>

        <form method="GET" class="rounded-3xl border border-slate-200 bg-white dark:bg-slate-900 dark:border-slate-800 p-4 shadow-sm">
            <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por titulo, SKU ou loja" class="w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-4 py-3 text-slate-900 dark:text-white">
        </form>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 uppercase tracking-wide text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Produto</th>
                        <th class="px-4 py-3 text-left">Loja</th>
                        <th class="px-4 py-3 text-left">Tipo</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-center">Vendas</th>
                        <th class="px-4 py-3 text-right">Acao</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($products as $product)
                        <tr>
                            <td class="px-4 py-4 font-bold text-slate-900 dark:text-white">{{ $product->title }}</td>
                            <td class="px-4 py-4 text-slate-600 dark:text-slate-300">{{ $product->store->brand_name ?? '-' }}</td>
                            <td class="px-4 py-4 text-slate-600 dark:text-slate-300">{{ strtoupper($product->type) }}</td>
                            <td class="px-4 py-4 text-slate-600 dark:text-slate-300">{{ strtoupper($product->status) }}</td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="font-black text-slate-900 dark:text-white">{{ (int) ($product->sales_count ?? 0) }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ (int) ($product->buyers_count ?? 0) }} clientes</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <form action="{{ route('panel.admin.marketplace.products.toggle', $product) }}" method="POST" class="inline-flex gap-2">
                                    @csrf
                                    <select name="status" class="rounded-2xl border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 px-3 py-2 text-xs text-slate-900 dark:text-white">
                                        @foreach(['draft' => 'Rascunho', 'published' => 'Publicado', 'blocked' => 'Bloqueado'] as $value => $label)
                                            <option value="{{ $value }}" {{ $product->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-4 py-2 text-xs font-black text-white shadow-lg shadow-blue-500/20 hover:brightness-110 transition">Salvar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">Nenhum produto encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $products->links() }}</div>
    </div>
@endsection
