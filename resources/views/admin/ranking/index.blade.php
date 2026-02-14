@extends('admin.layouts.app')

@section('page_title','Ranking')

@section('content')
<div class="card"><div class="card-body">
    <h4>Top Usuários</h4>
    <table class="table table-striped">
        <thead><tr><th>Pos</th><th>Nome</th><th>Pontos</th></tr></thead>
        <tbody>
            @foreach($top as $i => $u)
                <tr><td>{{ $i+1 }}</td><td>{{ $u->name }}</td><td>{{ $u->points }}</td></tr>
            @endforeach
        </tbody>
    </table>
</div></div>
@endsection