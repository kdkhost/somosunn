@php
    $sandboxRequestsAvailable = $sandboxRequestsAvailable ?? false;
    $sandboxRequests = $sandboxRequests ?? collect();
@endphp

<div class="card card-info card-outline shadow-sm mb-0">
    <div class="card-header border-0 pb-0">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
            <div class="mb-3 mb-lg-0">
                <h3 class="card-title fw-bold mb-1">
                    <i class="fas fa-vial me-2"></i>Tickets de sandbox da API
                </h3>
                <p class="text-muted mb-0">
                    Aprove ou rejeite o acesso ao playground e à API de homologação com base no motivo, IP e domínio informados.
                </p>
            </div>
            @if($sandboxRequestsAvailable)
                <span class="badge text-bg-info px-3 py-2">
                    {{ number_format($sandboxRequests->count()) }} ticket(s)
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
                <table id="admin-referrals-sandbox-table" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th>Afiliado</th>
                            <th>Motivo</th>
                            <th>Domínio / IP</th>
                            <th>Status</th>
                            <th>Revisão</th>
                            <th class="text-end">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sandboxRequests as $sandboxRequest)
                            @php
                                $badgeClass = match ($sandboxRequest->status) {
                                    'approved' => 'text-bg-success',
                                    'rejected' => 'text-bg-danger',
                                    'revoked' => 'text-bg-secondary',
                                    default => 'text-bg-warning',
                                };
                                $reviewedAt = optional($sandboxRequest->reviewed_at);
                            @endphp
                            <tr>
                                <td data-order="{{ $sandboxRequest->id }}">
                                    <div class="fw-bold">{{ $sandboxRequest->user?->name ?: 'Afiliado removido' }}</div>
                                    <small class="text-muted d-block">{{ $sandboxRequest->user?->email ?: 'Sem e-mail' }}</small>
                                    <span class="badge text-bg-secondary mt-1">{{ $sandboxRequest->user?->referral_code ?: 'Sem código' }}</span>
                                </td>
                                <td data-order="{{ $sandboxRequest->created_at?->timestamp ?? 0 }}">
                                    <div class="fw-bold">{{ \Illuminate\Support\Str::limit($sandboxRequest->reason, 140) }}</div>
                                    @if($sandboxRequest->admin_notes)
                                        <small class="text-muted d-block mt-2">
                                            <strong>Notas atuais:</strong> {{ \Illuminate\Support\Str::limit($sandboxRequest->admin_notes, 120) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div><strong>Domínio:</strong> {{ $sandboxRequest->requested_domain ?: 'Não informado' }}</div>
                                    <small class="text-muted"><strong>IP:</strong> {{ $sandboxRequest->requested_ip ?: 'Não informado' }}</small>
                                </td>
                                <td data-order="{{ strtoupper($sandboxRequest->status) }}">
                                    <span class="badge {{ $badgeClass }}">{{ strtoupper($sandboxRequest->status) }}</span>
                                </td>
                                <td data-order="{{ $reviewedAt?->timestamp ?? 0 }}">
                                    <div class="fw-bold">{{ $reviewedAt?->format('d/m/Y H:i') ?: 'Pendente' }}</div>
                                    <small class="text-muted">{{ $sandboxRequest->reviewer?->name ?: 'Sem revisão' }}</small>
                                </td>
                                <td class="text-end" style="min-width: 24rem;">
                                    <form action="{{ route('admin.referrals.sandbox.update', $sandboxRequest->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="row g-2 justify-content-end">
                                            <div class="col-12 col-xl-4 mb-2">
                                                <select name="status" class="form-control form-control-sm">
                                                    <option value="approved" @selected($sandboxRequest->status === 'approved')>Aprovar</option>
                                                    <option value="rejected" @selected($sandboxRequest->status === 'rejected')>Rejeitar</option>
                                                    <option value="revoked" @selected($sandboxRequest->status === 'revoked')>Revogar</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-xl-6 mb-2">
                                                <textarea name="admin_notes" rows="2" class="form-control form-control-sm" placeholder="Observações para o afiliado">{{ old('admin_notes', $sandboxRequest->admin_notes) }}</textarea>
                                            </div>
                                            <div class="col-12 col-xl-2 mb-2">
                                                <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">
                                                    <i class="fas fa-save me-1"></i> Salvar
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
