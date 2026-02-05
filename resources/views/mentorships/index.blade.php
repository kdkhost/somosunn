@extends('admin.layouts.app')

@section('title', 'Mentorias Disponíveis')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="m-0 text-dark">Mentorias</h1>
                <p class="text-muted">Conecte-se com especialistas e acelere seu crescimento.</p>
            </div>
        </div>

        @if($mentorships->count() > 0)
            <div class="row">
                @foreach($mentorships as $mentorship)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm hover-shadow transition">
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                style="height: 200px;">
                                @if(isset($mentorship->thumbnail))
                                    <img src="{{ asset($mentorship->thumbnail) }}" class="img-fluid" alt="{{ $mentorship->title }}">
                                @else
                                    <i class="fas fa-chalkboard-teacher fa-3x text-secondary"></i>
                                @endif
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title font-weight-bold">{{ $mentorship->title }}</h5>
                                <p class="card-text text-muted small flex-fill">
                                    {{ Str::limit($mentorship->description ?? 'Sem descrição.', 100) }}</p>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="font-weight-bold text-primary">
                                        {{ $mentorship->price ? 'R$ ' . number_format($mentorship->price, 2, ',', '.') : 'Gratuito' }}
                                    </span>
                                    <a href="#" class="btn btn-primary btn-sm rounded-pill px-3">
                                        Ver Detalhes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $mentorships->links() }}
            </div>
        @else
            <div class="row justify-content-center mt-5">
                <div class="col-md-6 text-center">
                    <div class="card shadow-sm border-0 p-5">
                        <div class="mb-3 text-muted">
                            <i class="fas fa-search fa-4x"></i>
                        </div>
                        <h4 class="font-weight-bold">Nenhuma mentoria adquirida</h4>
                        <p class="text-muted">Você ainda não possui mentorias ativas. Explore nossas opções para acelerar sua
                            carreira.</p>
                        <a href="{{ route('mentorships.index') }}" class="btn btn-primary btn-lg rounded-pill px-5 mt-3 shadow">
                            <i class="fas fa-shopping-cart mr-2"></i> Adquirir Mentoria
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection