@php
    $sandboxRequestsAvailable = $sandboxRequestsAvailable ?? false;
    $sandboxRequests = $sandboxRequests ?? collect();
@endphp

<section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white">Tickets de acesso ao sandbox</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Revise motivo, IP, domínio e libere o playground/API de homologação para o afiliado certo.</p>
        </div>
        @if($sandboxRequestsAvailable)
            <span class="inline-flex items-center gap-2 rounded-full border border-amber-100 bg-amber-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300">
                <i class="fas fa-ticket-alt text-[10px]"></i>
                {{ $sandboxRequests instanceof \Illuminate\Contracts\Pagination\Paginator ? number_format($sandboxRequests->total()) : number_format($sandboxRequests->count()) }} ticket(s)
            </span>
        @endif
    </div>

    @if(!$sandboxRequestsAvailable)
        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300">
            A tabela de tickets de sandbox ainda não existe neste ambiente. Rode `php artisan migrate` para liberar esta gestão.
        </div>
    @elseif($sandboxRequests->isEmpty())
        <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
            Nenhum afiliado solicitou acesso ao sandbox até agora.
        </div>
    @else
        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[1080px] text-sm">
                <thead class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:border-slate-800 dark:text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Afiliado</th>
                        <th class="px-4 py-3 text-left">Domínio / IP</th>
                        <th class="px-4 py-3 text-left">Motivo</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Última revisão</th>
                        <th class="px-4 py-3 text-left">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($sandboxRequests as $sandboxRequest)
                        <tr class="align-top hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-4">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $sandboxRequest->user?->name }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $sandboxRequest->user?->email }}</p>
                                <p class="mt-1 text-[11px] font-semibold text-blue-600 dark:text-blue-300">{{ $sandboxRequest->user?->referral_code ?: 'Sem código' }}</p>
                            </td>
                            <td class="px-4 py-4 text-slate-600 dark:text-slate-300">
                                <p><strong>Domínio:</strong> {{ $sandboxRequest->requested_domain ?: '—' }}</p>
                                <p class="mt-1"><strong>IP:</strong> {{ $sandboxRequest->requested_ip ?: '—' }}</p>
                            </td>
                            <td class="px-4 py-4 text-slate-600 dark:text-slate-300">
                                <p class="max-w-[320px] whitespace-pre-line">{{ $sandboxRequest->reason }}</p>
                                @if($sandboxRequest->admin_notes)
                                    <div class="mt-2 rounded-xl bg-slate-100 px-3 py-2 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        <strong>Notas admin:</strong> {{ $sandboxRequest->admin_notes }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @php
                                    $statusMap = [
                                        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                        'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                        'rejected' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                                        'revoked' => 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] {{ $statusMap[$sandboxRequest->status] ?? $statusMap['pending'] }}">
                                    {{ $sandboxRequest->status }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-600 dark:text-slate-300">
                                <p>{{ optional($sandboxRequest->reviewed_at)->format('d/m/Y H:i') ?: 'Aguardando' }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $sandboxRequest->reviewer?->name ?: 'Sem revisão' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <form action="{{ route('panel.admin.referrals.sandbox.update', $sandboxRequest) }}" method="POST" class="space-y-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
                                        @foreach(['approved' => 'Aprovar', 'rejected' => 'Rejeitar', 'revoked' => 'Revogar'] as $value => $label)
                                            <option value="{{ $value }}" @selected($sandboxRequest->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <textarea name="admin_notes" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 outline-none dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100" placeholder="Notas internas ou retorno ao afiliado">{{ old('admin_notes', $sandboxRequest->admin_notes) }}</textarea>
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white transition-all hover:bg-blue-700">
                                        <i class="fas fa-check"></i>
                                        Atualizar ticket
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($sandboxRequests instanceof \Illuminate\Contracts\Pagination\Paginator && method_exists($sandboxRequests, 'links'))
            <div class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800">
                {{ $sandboxRequests->links() }}
            </div>
        @endif
    @endif
</section>
