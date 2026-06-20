@extends('admin.layouts.app')

@section('title', $banner->exists ? 'Editar Banner Patrocinado' : 'Novo Banner Patrocinado')

@section('content')
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">{{ $banner->exists ? 'Editar banner patrocinado' : 'Novo banner patrocinado' }}</h1>
            <a href="{{ route('admin.sponsor-banners.index') }}" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>
    <section class="content"><div class="container-fluid">
        <form method="POST" enctype="multipart/form-data" action="{{ $banner->exists ? route('admin.sponsor-banners.update', $banner) : route('admin.sponsor-banners.store') }}">
            @csrf
            @if($banner->exists) @method('PUT') @endif
            <div class="card"><div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group"><label>Patrocinador</label><select name="sponsor_id" class="form-control" required><option value="">Selecione</option>@foreach($sponsors as $sponsor)<option value="{{ $sponsor->id }}" @selected(old('sponsor_id', $banner->sponsor_id) == $sponsor->id)>{{ $sponsor->company?->name ?: ('Patrocinador #' . $sponsor->id) }}</option>@endforeach</select></div>
                    <div class="col-md-6 form-group"><label>Titulo</label><input type="text" name="title" class="form-control" value="{{ old('title', $banner->title) }}" required></div>
                    <div class="col-md-6 form-group"><label>Posicao</label><select name="position" class="form-control" required>@foreach(['home_top','home_middle','home_bottom','event_sidebar','event_footer','marketplace_top','course_top','member_dashboard'] as $position)<option value="{{ $position }}" @selected(old('position', $banner->position) === $position)>{{ $position }}</option>@endforeach</select></div>
                    <div class="col-md-6 form-group"><label>URL</label><input type="url" name="url" class="form-control" value="{{ old('url', $banner->url) }}"></div>
                    <div class="col-md-4 form-group"><label>Inicio</label><input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($banner->starts_at)->format('Y-m-d\TH:i')) }}"></div>
                    <div class="col-md-4 form-group"><label>Fim</label><input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', optional($banner->ends_at)->format('Y-m-d\TH:i')) }}"></div>
                    <div class="col-md-4 form-group"><label>Imagem</label><input type="file" name="image" class="form-control-file"></div>
                    <div class="col-md-12"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" id="active" name="active" value="1" @checked(old('active', $banner->active ?? true))><label class="custom-control-label" for="active">Banner ativo</label></div></div>
                </div>
            </div><div class="card-footer text-right"><button type="submit" class="btn btn-primary">Salvar banner</button></div></div>
        </form>
    </div></section>
@endsection
