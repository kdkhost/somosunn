@extends('admin.layouts.app')

@section('page_title','Mentorias')

@section('content')
<div class="card"><div class="card-body">
    <a href="{{ route('admin.mentorships.create') }}" class="btn btn-primary mb-3">Nova mentoria</a>
    <table class="table table-striped">
        <thead><tr><th>#</th><th>Título</th><th>Mentor</th><th>Preço</th><th>Ações</th></tr></thead>
        <tbody>
            @foreach($items as $i)
                <tr>
                    <td>{{ $i->id }}</td>
                    <td>{{ $i->title }}</td>
                    <td>{{ $i->mentor?->name }}</td>
                    <td>{{ number_format($i->price,2,',','.') }}</td>
                    <td><a href="{{ route('admin.mentorships.edit',$i) }}" class="btn btn-sm btn-info">Editar</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $items->links() }}
</div></div>
@endsection