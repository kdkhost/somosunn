@extends('admin.layouts.app')

@section('title', 'Conteudo do Site')
@section('page_title', 'Conteudo do Site')

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a href="{{ route('admin.cms.index', ['slug' => 'home']) }}"
                                class="nav-link {{ $slug === 'home' ? 'active' : '' }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.cms.index', ['slug' => 'about']) }}"
                                class="nav-link {{ $slug === 'about' ? 'active' : '' }}">Sobre</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.cms.index', ['slug' => 'footer']) }}"
                                class="nav-link {{ $slug === 'footer' ? 'active' : '' }}">Rodape</a>
                        </li>
                    </ul>
                </div>
                <form method="POST" action="{{ route('admin.cms.update', ['slug' => $slug]) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @if($slug === 'home')
                            <div class="form-group">
                                <label>Titulo do Hero</label>
                                <input type="text" name="hero_title" class="form-control"
                                    value="{{ old('hero_title', $contents['hero_title'] ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label>Subtitulo do Hero</label>
                                <textarea name="hero_subtitle" rows="3" class="form-control">{{ old('hero_subtitle', $contents['hero_subtitle'] ?? '') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Imagem de Fundo (Hero)</label>
                                <input type="file" name="hero_image" class="form-control-file" accept="image/*">
                                @if(!empty($contents['hero_image']))
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . ltrim($contents['hero_image'], '/')) }}" alt="Hero"
                                            style="max-width: 280px; border-radius: 8px;">
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="remove_hero_image" value="1"
                                            id="remove_hero_image">
                                        <label class="form-check-label" for="remove_hero_image">Remover imagem atual</label>
                                    </div>
                                @endif
                            </div>
                        @elseif($slug === 'about')
                            <div class="form-group">
                                <label>Manifesto</label>
                                <textarea name="manifesto" rows="4" class="form-control">{{ old('manifesto', $contents['manifesto'] ?? '') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Visao</label>
                                <textarea name="vision" rows="4" class="form-control">{{ old('vision', $contents['vision'] ?? '') }}</textarea>
                            </div>
                            <div class="form-group mb-0">
                                <label>Valores</label>
                                <textarea name="values" rows="4" class="form-control">{{ old('values', $contents['values'] ?? '') }}</textarea>
                            </div>
                        @else
                            <div class="form-group">
                                <label>Instagram</label>
                                <input type="url" name="instagram_url" class="form-control"
                                    value="{{ old('instagram_url', $contents['instagram_url'] ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label>LinkedIn</label>
                                <input type="url" name="linkedin_url" class="form-control"
                                    value="{{ old('linkedin_url', $contents['linkedin_url'] ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label>YouTube</label>
                                <input type="url" name="youtube_url" class="form-control"
                                    value="{{ old('youtube_url', $contents['youtube_url'] ?? '') }}">
                            </div>
                            <div class="form-group mb-0">
                                <label>Facebook</label>
                                <input type="url" name="facebook_url" class="form-control"
                                    value="{{ old('facebook_url', $contents['facebook_url'] ?? '') }}">
                            </div>
                        @endif
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">Salvar conteudo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
