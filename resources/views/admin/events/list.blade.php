@extends('admin.layouts.app')

@section('title', 'Gerenciar Eventos')

@section('page_title', 'Gerenciar Eventos')

@section('breadcrumb_items')
    <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Eventos</a></li>
    <li class="breadcrumb-item active">Gerenciar</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12">
                <a href="{{ route('admin.events.create') }}"
                    class="btn btn-primary shadow-sm hover:translate-y-[-2px] transition-all">
                    <i class="fas fa-plus mr-2"></i> Novo Evento
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Listagem de Eventos</h3>

                        <div class="card-tools">
                            <form action="{{ route('admin.events.list') }}" method="GET" class="input-group input-group-sm"
                                style="width: 250px;">
                                <input type="text" name="q" value="{{ $search }}" class="form-control float-right"
                                    placeholder="Buscar eventos...">

                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Data/Hora</th>
                                    <th>Local</th>
                                    <th>Preço</th>
                                    <th>Status</th>
                                    <th class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($event->image)
                                                    <img src="{{ $event->image_url }}" alt="{{ $event->title }}"
                                                        class="img-size-50 mr-3 img-rounded shadow-sm"
                                                        style="object-fit: cover; width: 50px; height: 35px;">
                                                @else
                                                    <div class="img-size-50 mr-3 d-flex align-items-center justify-center bg-light img-rounded"
                                                        style="width: 50px; height: 35px;">
                                                        <i class="fas fa-calendar-star text-muted"></i>
                                                    </div>
                                                @endif
                                                <span class="font-weight-bold"
                                                    title="{{ $event->title }}">{{ Str::limit($event->title, 40) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span>{{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y') }}</span>
                                                <small
                                                    class="text-muted">{{ \Carbon\Carbon::parse($event->start_at)->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="fas fa-map-marker-alt mr-1 text-muted"></i>
                                            {{ $event->location ?: 'Online' }}
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">
                                                {{ $event->price > 0 ? 'R$ ' . number_format($event->price, 2, ',', '.') : 'Gratuito' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($event->published)
                                                <span class="badge badge-success">Publicado</span>
                                            @else
                                                <span class="badge badge-secondary">Rascunho</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-info"
                                                    title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.events.destroy', $event) }}" method="POST"
                                                    onsubmit="return confirm('Deseja realmente excluir este evento?');"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                            <p>Nenhum evento encontrado.</p>
                                            <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm">Criar
                                                meu primeiro evento</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    @if($events->hasPages())
                        <div class="card-footer clearfix">
                            <div class="float-right">
                                {{ $events->links() }}
                            </div>
                        </div>
                    @endif
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .img-rounded {
            border-radius: 4px;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }
    </style>
@endpush