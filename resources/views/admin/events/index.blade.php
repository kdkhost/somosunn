@extends('admin.layouts.app')

@section('page_title','Eventos')
@section('breadcrumb')
    <li class="breadcrumb-item active">Eventos</li>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary mb-3">Novo evento</a>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Início</th>
                    <th>Preço</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $e)
                    <tr>
                        <td>{{ $e->id }}</td>
                        <td>{{ $e->title }}</td>
                        <td>{{ $e->start_at }}</td>
                        <td>{{ number_format($e->price,2,',','.') }}</td>
                        <td>
                            <a href="{{ route('admin.events.edit',$e) }}" class="btn btn-sm btn-info mr-1">Editar</a>
                            <a href="{{ route('admin.events.destroy',$e) }}" class="btn btn-sm btn-danger btn-delete">Excluir</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $events->links() }}
    </div>
</div>
@endsection
