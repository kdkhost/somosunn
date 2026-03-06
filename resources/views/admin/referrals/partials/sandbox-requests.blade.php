@php
    $sandboxRequestsAvailable = $sandboxRequestsAvailable ?? false;
    $sandboxRequests = $sandboxRequests ?? collect();
@endphp

<div class="row">
    <div class="col-12">
        <div class="card card-info card-outline">
            <div class="card-header">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                    <div class="mb-3 mb-lg-0">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-vial mr-2"></i>Tickets de sandbox da API</h3>
                        <p class="text-muted mb-0">Aprove ou rejeite o acesso ao playground/API de homologação com base em motivo, IP e domínio informados.</p>
                    </div>
                    @if($sandboxRequestsAvailable)
                        <span class="badge badge-info px-3 py-2">
                            {{ $sandboxRequests instanceof \Illuminate\Contracts\Pagination\Paginator ? number_format($sandboxRequests->total()) : number_format($sandboxRequests->count()) }} ticket(s)
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if(!$sandboxRequestsAvailable)
                    <div class="alert alert-warning mb-0">
                        A tabela de tickets de sandbox ainda não existe neste ambiente. Rode <code>php artisan migrate</code> para liberar esta gestão.
                    </div>
                @elseif($sandboxRequests->isEmpty())
                    <div class="alert alert-light border mb-0">
                        Nenhum ticket de sandbox recebido até agora.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-valign-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Afiliado</th>
                                    <th>Motivo</th>
                                    <th>Domínio / IP</th>
                                    <th>Status</th>
                                    <th>Revisão</th>
                                    <th class="text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sandboxRequests as $sandboxRequest)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $sandboxRequest->user?->name ?: 'Afiliado removido' }}</div>
                                            <small class="text-muted d-block">{{ $sandboxRequest->user?->email ?: 'Sem e-mail' }}</small>
                                            <span class="badge badge-secondary mt-1">{{ $sandboxRequest->user?->referral_code ?: 'Sem código' }}</span>
                                        </td>
                                        <td style="min-width: 20rem;">
                                            <div class="font-weight-bold">{{ \Illuminate\Support\Str::limit($sandboxRequest->reason, 140) }}</div>
                                            @if($sandboxRequest->admin_notes)
                                                <small class="text-muted d-block mt-2"><strong>Notas atuais:</strong> {{ \Illuminate\Support\Str::limit($sandboxRequest->admin_notes, 120) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div><strong>Domínio:</strong> {{ $sandboxRequest->requested_domain ?: 'Não informado' }}</div>
                                            <small class="text-muted"><strong>IP:</strong> {{ $sandboxRequest->requested_ip ?: 'Não informado' }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match ($sandboxRequest->status) {
                                                    'approved' => 'badge-success',
                                                    'rejected' => 'badge-danger',
                                                    'revoked' => 'badge-secondary',
                                                    default => 'badge-warning',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ strtoupper($sandboxRequest->status) }}</span>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold">{{ optional($sandboxRequest->reviewed_at)->format('d/m/Y H:i') ?: 'Pendente' }}</div>
                                            <small class="text-muted">{{ $sandboxRequest->reviewer?->name ?: 'Sem revisão' }}</small>
                                        </td>
                                        <td class="text-right" style="min-width: 22rem;">
                                            <form action="{{ route('admin.referrals.sandbox.update', $sandboxRequest->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="form-group text-left mb-2">
                                                    <select name="status" class="form-control form-control-sm">
                                                        <option value="approved" @selected($sandboxRequest->status === 'approved')>Aprovar</option>
                                                        <option value="rejected" @selected($sandboxRequest->status === 'rejected')>Rejeitar</option>
                                                        <option value="revoked" @selected($sandboxRequest->status === 'revoked')>Revogar</option>
                                                    </select>
                                                </div>
                                                <div class="form-group text-left mb-2">
                                                    <textarea name="admin_notes" rows="3" class="form-control form-control-sm" placeholder="Observações para o afiliado">{{ old('admin_notes', $sandboxRequest->admin_notes) }}</textarea>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-primary font-weight-bold">
                                                    <i class="fas fa-save mr-1"></i> Salvar revisão
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if($sandboxRequestsAvailable && method_exists($sandboxRequests, 'hasPages') && $sandboxRequests->hasPages())
                <div class="card-footer clearfix">
                    {{ $sandboxRequests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
