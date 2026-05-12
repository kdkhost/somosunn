@extends('admin.layouts.app')

@section('title', 'WAF - IP Allowlist')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-check-circle text-success"></i> WAF - IP Allowlist</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if(!$hasTable)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Tabela <code>waf_ip_allowlist</code> nao encontrada. Execute <code>php artisan migrate</code>.
            </div>
        @else
            <div class="row">
                {{-- Formulario de adicao --}}
                <div class="col-md-4">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">Adicionar IP/CIDR</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.waf.allowlist.store') }}">
                                @csrf
                                <div class="form-group">
                                    <label>IP ou CIDR <span class="text-danger">*</span></label>
                                    <input type="text" name="cidr" class="form-control form-control-sm" required
                                           placeholder="192.168.1.0/24 ou 10.0.0.1" value="{{ old('cidr') }}">
                                </div>
                                <div class="form-group">
                                    <label>Razao</label>
                                    <input type="text" name="reason" class="form-control form-control-sm"
                                           placeholder="Motivo da permissao" value="{{ old('reason') }}">
                                </div>
                                <div class="form-group">
                                    <label>Expiracao (opcional)</label>
                                    <input type="datetime-local" name="expires_at" class="form-control form-control-sm" value="{{ old('expires_at') }}">
                                    <small class="text-muted">Deixe vazio para permissao permanente.</small>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm btn-block">
                                    <i class="fas fa-check"></i> Permitir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Tabela --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">IPs Permitidos</h3>
                            <div class="card-tools">
                                <form method="GET" action="{{ route('admin.waf.allowlist.index') }}" class="input-group input-group-sm" style="width:250px;">
                                    <input type="text" name="search" class="form-control" placeholder="Buscar IP/razao..." value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>IP/CIDR</th>
                                        <th>Razao</th>
                                        <th>Expira em</th>
                                        <th>Status</th>
                                        <th>Criado em</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($entries as $entry)
                                        <tr>
                                            <td><code>{{ $entry->cidr }}</code></td>
                                            <td><small>{{ \Illuminate\Support\Str::limit($entry->reason, 50) }}</small></td>
                                            <td>{{ $entry->expires_at ? $entry->expires_at->format('d/m/Y H:i') : 'Permanente' }}</td>
                                            <td>
                                                @if($entry->isActive())
                                                    <span class="badge badge-success">Ativo</span>
                                                @else
                                                    <span class="badge badge-secondary">Expirado</span>
                                                @endif
                                            </td>
                                            <td>{{ $entry->created_at?->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('admin.waf.allowlist.destroy', $entry->id) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger" onclick="return confirm('Remover {{ $entry->cidr }} da allowlist?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Nenhum IP na allowlist.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($entries instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="card-footer clearfix">
                                {{ $entries->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
