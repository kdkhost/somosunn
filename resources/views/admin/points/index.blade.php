@extends('admin.layouts.app')

@section('page_title', 'Regras de Pontuação')
@section('breadcrumb_items')
    <li class="breadcrumb-item active">Pontuação</li>
@endsection

@push('styles')
    <style>
        /* Esconder colunas em mobile */
        @media (max-width: 767px) {
            .hide-mobile {
                display: none !important;
            }

            .points-table td,
            .points-table th {
                padding: 0.5rem 0.4rem;
                font-size: 0.85rem;
            }

            .points-table .btn-sm {
                padding: 0.2rem 0.4rem;
            }
        }

        /* Badge de categoria compacto */
        .category-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.8rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .category-badge i {
            margin-right: 0.4rem;
        }
    </style>
@endpush

@section('content')
    {{-- Toastr global --}}

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-star mr-2"></i>Regras de Pontuação</h3>
            <div class="card-tools">
                <a href="{{ route('admin.points-rules.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i><span class="d-none d-sm-inline">Nova Regra</span><span
                        class="d-sm-none">Novo</span>
                </a>
            </div>
        </div>
        <div class="card-body py-3">
            <p class="text-muted mb-3 small">
                <i class="fas fa-info-circle mr-1"></i>
                Configure as ações que concedem pontos aos usuários.
            </p>

            {{-- Estatísticas compactas --}}
            <div class="d-flex flex-wrap gap-2" style="gap: 0.5rem;">
                @foreach($categories as $key => $cat)
                    @php
                        $categoryRules = $rulesGrouped->get($key, collect());
                        $count = $categoryRules->count();
                    @endphp
                    <span
                        class="category-badge bg-{{ $cat['color'] }} {{ in_array($cat['color'], ['warning', 'light']) ? 'text-dark' : 'text-white' }}">
                        <i class="{{ $cat['icon'] }}"></i>
                        <span class="d-none d-sm-inline">{{ $cat['label'] }}:</span> {{ $count }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Cards por categoria --}}
    @foreach($categories as $catKey => $cat)
        @php $rules = $rulesGrouped->get($catKey, collect()); @endphp
        @if($rules->count() > 0)
            <div class="card mb-3">
                <div
                    class="card-header bg-{{ $cat['color'] }} {{ in_array($cat['color'], ['warning', 'light']) ? 'text-dark' : 'text-white' }} py-2">
                    <h3 class="card-title mb-0" style="font-size: 1rem;">
                        <i class="{{ $cat['icon'] }} mr-2"></i>{{ $cat['label'] }}
                        <span class="badge badge-light ml-1">{{ $rules->count() }}</span>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 points-table">
                            <thead class="thead-light">
                                <tr>
                                    <th class="hide-mobile" style="width:35px"></th>
                                    <th>Regra</th>
                                    <th style="width:70px" class="text-center">Pontos</th>
                                    <th style="width:70px" class="text-center hide-mobile">Repetível</th>
                                    <th style="width:60px" class="text-center hide-mobile">Status</th>
                                    <th style="width:80px" class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rules->sortBy('sort_order') as $r)
                                    <tr class="{{ !$r->active ? 'table-secondary' : '' }}">
                                        <td class="text-center hide-mobile">
                                            <i class="{{ $r->icon ?? 'fas fa-star' }} text-{{ $cat['color'] }}"></i>
                                        </td>
                                        <td>
                                            <strong>{{ $r->label }}</strong>
                                            <br><code class="small">{{ $r->key }}</code>
                                            @if($r->description ?? null)
                                                <span class="d-none d-md-inline text-muted"> - {{ Str::limit($r->description, 40) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $r->points > 0 ? 'success' : 'danger' }} px-2 py-1">
                                                {{ $r->points > 0 ? '+' : '' }}{{ $r->points }}
                                            </span>
                                        </td>
                                        <td class="text-center hide-mobile">
                                            @if($r->repeatable ?? false)
                                                <span class="badge badge-info"
                                                    title="Repetível{{ ($r->max_daily ?? null) ? ' (máx ' . $r->max_daily . '/dia)' : '' }}">
                                                    <i class="fas fa-sync-alt"></i>
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center hide-mobile">
                                            @if($r->active)
                                                <i class="fas fa-check text-success" title="Ativa"></i>
                                            @else
                                                <i class="fas fa-pause text-secondary" title="Inativa"></i>
                                            @endif
                                        </td>
                                        <td class="text-right text-nowrap">
                                            <a href="{{ route('admin.points-rules.edit', $r) }}"
                                                class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.points-rules.destroy', $r) }}" method="POST" class="d-inline js-confirm-delete" data-confirm="Remover esta regra de pontuação?">
                                                 @csrf
                                                 @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- Regras sem categoria --}}
    @php 
            $uncategorizedKeys = array_diff($rulesGrouped->keys()->toArray(), array_keys($categories));
        $uncategorized = collect();
        foreach ($uncategorizedKeys as $key) {
            $uncategorized = $uncategorized->merge($rulesGrouped->get($key, collect()));
        }
    @endphp
    @if($uncategorized->count() > 0)
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white py-2">
                <h3 class="card-title mb-0" style="font-size: 1rem;">
                    <i class="fas fa-folder mr-2"></i>Outras Regras
                    <span class="badge badge-light ml-1">{{ $uncategorized->count() }}</span>
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 points-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="hide-mobile" style="width:35px"></th>
                                <th>Regra</th>
                                <th style="width:70px" class="text-center">Pontos</th>
                                <th style="width:70px" class="text-center hide-mobile">Repetível</th>
                                <th style="width:60px" class="text-center hide-mobile">Status</th>
                                <th style="width:80px" class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($uncategorized as $r)
                                <tr class="{{ !$r->active ? 'table-secondary' : '' }}">
                                    <td class="text-center hide-mobile">
                                        <i class="{{ $r->icon ?? 'fas fa-star' }} text-secondary"></i>
                                    </td>
                                    <td>
                                        <strong>{{ $r->label }}</strong>
                                        <br><code class="small">{{ $r->key }}</code>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $r->points > 0 ? 'success' : 'danger' }} px-2 py-1">
                                            {{ $r->points > 0 ? '+' : '' }}{{ $r->points }}
                                        </span>
                                    </td>
                                    <td class="text-center hide-mobile">
                                        @if($r->repeatable ?? false)
                                            <span class="badge badge-info"><i class="fas fa-sync-alt"></i></span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center hide-mobile">
                                        @if($r->active)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            <i class="fas fa-pause text-secondary"></i>
                                        @endif
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <a href="{{ route('admin.points-rules.edit', $r) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.points-rules.destroy', $r) }}" method="POST" class="d-inline js-confirm-delete" data-confirm="Remover esta regra?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Info box se não houver regras --}}
    @if($rulesGrouped->flatten(1)->count() == 0)
        <div class="card">
            <div class="card-body text-center py-4">
                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhuma regra de pontuação cadastrada</h5>
                <p class="text-muted mb-3 small">Crie regras para recompensar a participação dos usuários.</p>
                <a href="{{ route('admin.points-rules.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i>Criar primeira regra
                </a>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        $(function () {
            $(document)
                .off('submit.pointsDelete', 'form.js-confirm-delete')
                .on('submit.pointsDelete', 'form.js-confirm-delete', function (e) {
                    e.preventDefault();
                    const form = this;
                    const message = (form.getAttribute('data-confirm') || 'Confirma a remoção?').toString();

                    if (typeof Swal === 'undefined') {
                        form.submit();
                        return;
                    }

                    Swal.fire({
                        title: 'Confirmar remoção',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Remover',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#d33'
                    }).then((result) => {
                        if (!result.isConfirmed) return;
                        form.submit();
                    });
                });
        });
    </script>
@endpush
