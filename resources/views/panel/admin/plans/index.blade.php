@extends('panel.layouts.app')

@section('title', 'Planos e Assinaturas')

@section('panel_breadcrumb')
    <a href="{{ route('panel.admin.plans.index') }}" class="hover:underline">Planos</a>
@endsection

@section('panel_content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Planos e Pacotes</h2>
                <p class="text-slate-500 text-sm">Gerencie as assinaturas, preços e recursos disponíveis para venda.</p>
            </div>
            <a href="{{ route('panel.admin.plans.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl shadow-lg shadow-blue-500/30 transition transform hover:scale-[1.02] flex items-center gap-2">
                <i class="fas fa-plus"></i> Novo Plano
            </a>
        </div>

        <!-- Plans Table -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Plano</th>
                            <th class="px-6 py-4">Preço / Ciclo</th>
                            <th class="px-6 py-4">Benefícios</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($plans as $plan)
                            <tr class="hover:bg-slate-50 transition group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-10 rounded-lg bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                                            @if($plan->image)
                                                <img src="{{ asset('storage/' . $plan->image) }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-slate-800 text-base">{{ $plan->name }}</span>
                                                @if($plan->highlight)
                                                    <span class="text-[10px] uppercase font-bold bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Destaque</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-400 font-mono">{{ $plan->slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">R$ {{ number_format($plan->price, 2, ',', '.') }}</div>
                                    <div class="text-xs text-slate-500 capitalize">{{ $plan->period }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($plan->benefits)
                                        <div class="text-xs text-slate-500 line-clamp-2 max-w-[200px]" title="{{ implode(', ', $plan->benefits) }}">
                                            {{ implode(', ', $plan->benefits) }}
                                        </div>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button type="button" 
                                            onclick="toggleActive('{{ route('panel.admin.plans.toggle-active', $plan) }}', this)"
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold transition cursor-pointer hover:opacity-80
                                            {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $plan->is_active ? 'Ativo' : 'Oculto' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('panel.admin.plans.edit', $plan) }}" 
                                           class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form action="{{ route('panel.admin.plans.destroy', $plan) }}" method="POST" 
                                              onsubmit="return confirm('Tem certeza que deseja remover este plano?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="Remover">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="fas fa-box-open text-4xl opacity-20"></i>
                                        <p>Nenhum plano cadastrado.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($plans->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $plans->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function toggleActive(url, btn) {
        if(btn.disabled) return;
        btn.disabled = true;
        const originalText = btn.innerText;
        btn.innerText = '...';
        
        // CSRF Token
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                if(data.is_active) {
                    btn.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold transition cursor-pointer hover:opacity-80 bg-green-100 text-green-700';
                    btn.innerText = 'Ativo';
                } else {
                    btn.className = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold transition cursor-pointer hover:opacity-80 bg-slate-100 text-slate-500';
                    btn.innerText = 'Oculto';
                }
            } else {
                alert('Erro ao alterar status.');
                btn.innerText = originalText;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro de conexão.');
            btn.innerText = originalText;
        })
        .finally(() => {
            btn.disabled = false;
        });
    }
</script>
@endpush
