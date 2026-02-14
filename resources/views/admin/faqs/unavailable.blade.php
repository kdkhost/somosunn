@extends('admin.layouts.app')

@section('page_title','FAQ')
@section('breadcrumb')<li class="breadcrumb-item active">FAQ</li>@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="alert alert-danger mb-0">
                <div class="font-weight-bold mb-2">FAQ indisponível</div>
                <div>{{ $message ?? 'Seu banco de dados está desatualizado.' }}</div>
                <div class="mt-2">
                    <code class="user-select-all">php artisan migrate --force</code>
                </div>
            </div>
        </div>
    </div>
@endsection

