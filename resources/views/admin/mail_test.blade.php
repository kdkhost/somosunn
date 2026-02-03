@extends('admin.layouts.app')

@section('page_title','Teste de E-mail')

@section('content')
<div class="card"><div class="card-body">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    <form method="POST" action="{{ route('admin.mailtest.send') }}">
        @csrf
        <div class="form-group mb-2"><label>Para</label><input type="email" name="to" class="form-control" required></div>
        <div class="form-group mb-2"><label>Assunto</label><input type="text" name="subject" class="form-control" required></div>
        <div class="form-group mb-2"><label>Mensagem</label><textarea name="body" class="form-control" rows="6" required></textarea></div>
        <button class="btn btn-primary">Enviar</button>
    </form>
</div></div>
@endsection