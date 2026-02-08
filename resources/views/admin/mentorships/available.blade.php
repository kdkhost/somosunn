@extends('admin.layouts.app')

@section('page_title', 'Mentorias Disponíveis')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-success shadow-sm" style="border-radius: 15px; border: none;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <h2 class="h4 font-weight-bold mb-1 text-white">Pronto para o próximo nível?</h2>
                            <p class="mb-0 text-white opacity-75">Escolha uma mentoria e acelere seu crescimento com
                                orientação prática de especialistas.</p>
                        </div>
                        <div class="col-auto d-none d-md-block">
                            <i class="fas fa-chalkboard-teacher fa-4x text-white opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" class="mb-4">
        <div class="input-group input-group-lg shadow-sm" style="border-radius: 50px; overflow: hidden; background: white;">
            <input type="text" name="q" class="form-control border-0 px-4" placeholder="O que você deseja aprender hoje?"
                value="{{ $search ?? '' }}" style="background: transparent;">
            <div class="input-group-append">
                <button class="btn btn-white px-4 text-success" type="submit" style="background: transparent;"><i
                        class="fas fa-search"></i></button>
            </div>
        </div>
    </form>

    <div class="row">
        @forelse($items as $item)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0 hover-shadow transition"
                    style="border-radius: 20px; overflow: hidden;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="badge badge-success p-2 px-3 font-weight-bold" style="border-radius: 10px;">
                                R$ {{ number_format((float) $item->price, 2, ',', '.') }}
                            </div>
                            <span class="text-xs text-muted font-weight-bold text-uppercase"
                                style="font-size: 0.75rem;">{{ $item->mentor?->name ?? 'Mentor UNN' }}</span>
                        </div>

                        <h3 class="h5 font-weight-bold mb-3 text-dark" style="min-height: 3rem;">{{ $item->title }}</h3>
                        <p class="text-muted small mb-4" style="min-height: 4.5rem;">
                            {{ \Illuminate\Support\Str::limit(strip_tags((string) $item->description), 140) }}
                        </p>

                        <div class="row no-gutters mb-4">
                            <div class="col-6 pr-2">
                                <div class="bg-light rounded p-2 text-center">
                                    <small class="d-block text-muted font-weight-bold text-uppercase"
                                        style="font-size: 0.65rem;">Vagas</small>
                                    <span class="font-weight-bold">{{ $item->slots ?: 'Ilimitadas' }}</span>
                                </div>
                            </div>
                            <div class="col-6 pl-2">
                                <div class="bg-light rounded p-2 text-center">
                                    <small class="d-block text-muted font-weight-bold text-uppercase"
                                        style="font-size: 0.65rem;">Formato</small>
                                    <span class="font-weight-bold">Premium</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 p-4 pt-0">
                        {{-- Para seguir o padrão administrativo, abrimos a página de detalhes que também deve estar no painel
                        se possível, --}}
                        {{-- mas por enquanto direcionamos para a rota pública conforme o padrão de Cursos. --}}
                        <a href="{{ route('mentorships.show', $item->id) }}"
                            class="btn btn-primary btn-block py-3 font-weight-bold" style="border-radius: 12px;">
                            <i class="fas fa-external-link-alt mr-1"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="text-muted mb-3"><i class="fas fa-search fa-3x opacity-25"></i></div>
                <h4 class="text-muted font-weight-bold">Nenhuma mentoria encontrada.</h4>
                <p>Tente buscar por termos diferentes ou confira novamente em breve.</p>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $items->links() }}
    </div>

    <style>
        .hover-shadow {
            transition: all .3s ease;
        }

        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, .1) !important;
        }

        .transition {
            transition: all .3s ease;
        }
    </style>
@endsection