@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h3>Regras de Pontuação</h3>
            <a href="{{ route('admin.points-rules.create') }}" class="btn btn-primary">Nova Regra</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Rótulo</th>
                    <th>Pontos</th>
                    <th>Ativa</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rules as $r)
                    <tr>
                        <td>{{ $r->key }}</td>
                        <td>{{ $r->label }}</td>
                        <td>{{ $r->points }}</td>
                        <td>{{ $r->active ? 'Sim' : 'Não' }}</td>
                        <td>
                            <a href="{{ route('admin.points-rules.edit', $r) }}" class="btn btn-sm btn-secondary">Editar</a>
                            <form action="{{ route('admin.points-rules.destroy', $r) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"
                                    onclick="return confirmAction(event, 'Remover?', 'Esta ação não pode ser desfeita.')">Remover</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $rules->links() }}
    </div>
@endsection