@extends('admin.layouts.app')

@section('page_title', 'Mentorias')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
                <div>
                    <h5 class="mb-1">Gestao de mentorias</h5>
                    <p class="text-muted mb-0">Cadastre e organize mentorias para exibicao no site.</p>
                </div>
                <a href="{{ route('admin.mentorships.create') }}" class="btn btn-primary mt-2 mt-md-0">
                    <i class="fas fa-plus mr-1"></i> Nova mentoria
                </a>
            </div>

            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por titulo ou descricao"
                        value="{{ $search ?? '' }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 80px;">#</th>
                            <th>Titulo</th>
                            <th>Mentor</th>
                            <th style="width: 120px;">Vagas</th>
                            <th style="width: 150px;">Preco</th>
                            <th style="width: 200px;">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $item->title }}</div>
                                    <small
                                        class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item->description), 70) }}</small>
                                </td>
                                <td>{{ $item->mentor?->name ?? 'Nao definido' }}</td>
                                <td>{{ $item->slots ?? '-' }}</td>
                                <td>R$ {{ number_format((float) $item->price, 2, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('admin.mentorships.edit', $item) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <form action="{{ route('admin.mentorships.destroy', $item) }}" method="POST"
                                        class="d-inline-block"
                                        onsubmit="return confirmAction(event, 'Remover esta mentoria?', 'Esta ação não pode ser desfeita.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Excluir
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhuma mentoria encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $items->links() }}
        </div>
    </div>
@endsection