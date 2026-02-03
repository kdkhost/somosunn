@extends('admin.layouts.app')

@section('page_title','Cursos')

@section('content')
<div class="card"><div class="card-body">
    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary mb-3">Novo curso</a>
    <table class="table table-striped">
        <thead><tr><th>#</th><th>Título</th><th>Preço</th><th>Publicado</th><th>Ações</th></tr></thead>
        <tbody>
            @foreach($courses as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->title }}</td>
                    <td>{{ number_format($c->price,2,',','.') }}</td>
                    <td>{{ $c->published ? 'Sim':'Não' }}</td>
                    <td><a href="{{ route('admin.courses.edit',$c) }}" class="btn btn-sm btn-info">Editar</a>
                        <form method="POST" action="{{ route('admin.courses.destroy',$c) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Excluir</button></form></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $courses->links() }}
</div></div>
@endsection