@extends('admin.layouts.app')

@section('title', 'WAF - Regras')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-gavel text-primary"></i> WAF - Regras</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('admin.waf.rules.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Nova Regra
                </a>
                <a href="{{ route('admin.waf.rules.export') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-download"></i> Exportar
                </a>
                <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-upload"></i> Importar
                </button>
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

        @if(!$hasTable)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Tabela <code>waf_rules</code> nao encontrada. Execute <code>php artisan migrate</code>.
            </div>
        @else
            {{-- Filtros --}}
            <div class="card card-outline card-primary collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.waf.rules.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Buscar por nome...">
                            </div>
                            <div class="col-md-2">
                                <select name="severity" class="form-control form-control-sm">
                                    <option value="">Severidade</option>
                                    @foreach(['info','low','medium','high','critical'] as $s)
                                        <option value="{{ $s }}" {{ request('severity') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="action" class="form-control form-control-sm">
                                    <option value="">Acao</option>
                                    @foreach(['monitor','challenge','block'] as $a)
                                        <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control form-control-sm">
                                    <option value="">Status</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativa</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativa</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Filtrar</button>
                                <a href="{{ route('admin.waf.rules.index') }}" class="btn btn-sm btn-default">Limpar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabela --}}
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-sm table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Pattern</th>
                                <th>Matcher</th>
                                <th>Score</th>
                                <th>Severidade</th>
                                <th>Acao</th>
                                <th>Ativa</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $rule)
                                <tr>
                                    <td>{{ $rule->name }}</td>
                                    <td><code>{{ $rule->attack_pattern }}</code></td>
                                    <td><span class="badge badge-secondary">{{ $rule->matcher_type }}</span></td>
                                    <td>{{ $rule->score }}</td>
                                    <td>
                                        @switch($rule->severity)
                                            @case('critical')
                                                <span class="badge badge-danger">Critical</span>
                                                @break
                                            @case('high')
                                                <span class="badge badge-warning">High</span>
                                                @break
                                            @case('medium')
                                                <span class="badge badge-info">Medium</span>
                                                @break
                                            @case('low')
                                                <span class="badge badge-secondary">Low</span>
                                                @break
                                            @default
                                                <span class="badge badge-light">Info</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @switch($rule->action)
                                            @case('block')
                                                <span class="badge badge-danger">Block</span>
                                                @break
                                            @case('challenge')
                                                <span class="badge" style="background-color:#6f42c1;color:#fff;">Challenge</span>
                                                @break
                                            @default
                                                <span class="badge badge-warning">Monitor</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input toggle-rule" id="toggle-{{ $rule->id }}" data-id="{{ $rule->id }}" {{ $rule->is_active ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="toggle-{{ $rule->id }}"></label>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.waf.rules.edit', $rule->id) }}" class="btn btn-xs btn-info" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.waf.rules.destroy', $rule->id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger btn-swal-confirm" title="Remover" data-swal-title="Remover regra?" data-swal-text="Regra: {{ $rule->name }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Nenhuma regra encontrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($hasTable && $rules instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="card-footer clearfix">
                        {{ $rules->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>

{{-- Modal Importar --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.waf.rules.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Importar Regras (JSON)</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Arquivo JSON</label>
                        <input type="file" name="file" class="form-control-file" accept=".json,.txt" required>
                        <small class="text-muted">Array de objetos de regra no formato padrao WAF.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('.toggle-rule').on('change', function() {
        var id = $(this).data('id');
        var $el = $(this);
        $.post('/admin/waf/rules/' + id + '/toggle', { _token: '{{ csrf_token() }}' }, function(res) {
            if (!res.success) {
                $el.prop('checked', !$el.prop('checked'));
            }
        }).fail(function() {
            $el.prop('checked', !$el.prop('checked'));
            alert('Erro ao alternar regra.');
        });
    });
});
</script>
@endpush

@push('scripts')
@include('admin.waf.partials.swal-confirm')
@endpush
