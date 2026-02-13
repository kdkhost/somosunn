@extends('admin.layouts.app')

@section('title', 'Conteúdo do Site')
@section('page_title', 'Conteúdo do Site')

@section('content')
    @php
        $isInstitutional = str_starts_with($slug, 'institucional_');

        $pageLabels = [
            'home' => 'Home',
            'about' => 'Sobre (Seções)',
            'footer' => 'Rodapé',
            'institucional_sobre' => 'Institucional: Sobre',
            'institucional_manifesto' => 'Institucional: Manifesto',
            'institucional_quem_somos' => 'Institucional: Quem Somos',
            'institucional_como_funciona' => 'Institucional: Como Funciona',
            'institucional_valores' => 'Institucional: Valores',
            'institucional_contato' => 'Institucional: Contato',
        ];
        $currentLabel = $pageLabels[$slug] ?? $slug;
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <strong>Editor</strong>
                        <div class="text-muted small">Edite textos, imagens e SEO. Salve para publicar.</div>
                    </div>
                    <span class="badge badge-primary">{{ $currentLabel }}</span>
                </div>

                <form method="POST" action="{{ route('admin.cms.update', ['slug' => $slug]) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">
                        @if($isInstitutional)
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <label>Título da Página (aba do navegador)</label>
                                        <input type="text" name="title" class="form-control"
                                            value="{{ old('title', $contents['title'] ?? '') }}">
                                        <small class="text-muted">Se vazio, usa o título padrão do site.</small>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label>Corpo (HTML)</label>
                                        <textarea id="cmsBody" name="body" class="form-control summernote-lg"
                                            data-height="520"
                                            data-toolbar="full"
                                            data-upload-url="{{ route('admin.cms.upload') }}"
                                            data-cms-slug="{{ $slug }}">{{ old('body', $contents['body'] ?? '') }}</textarea>
                                        <small class="text-muted d-block mt-2">
                                            Dica: arraste e solte imagens/GIFs no editor para enviar e inserir automaticamente.
                                        </small>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card card-outline card-primary">
                                        <div class="card-header">
                                            <strong>SEO</strong>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>Meta Title</label>
                                                <input type="text" name="meta_title" class="form-control"
                                                    value="{{ old('meta_title', $contents['meta_title'] ?? '') }}">
                                                <small class="text-muted">Se vazio, usa o título da página.</small>
                                            </div>

                                            <div class="form-group">
                                                <label>Meta Description</label>
                                                <textarea name="meta_description" rows="3"
                                                    class="form-control">{{ old('meta_description', $contents['meta_description'] ?? '') }}</textarea>
                                                <small class="text-muted">Se vazio, usa a descrição global em Configurações.</small>
                                            </div>

                                            <div class="form-group">
                                                <label>Meta Keywords</label>
                                                <input type="text" name="meta_keywords" class="form-control"
                                                    value="{{ old('meta_keywords', $contents['meta_keywords'] ?? '') }}">
                                            </div>

                                            <div class="form-group">
                                                <label>Canonical</label>
                                                <input type="url" name="canonical" class="form-control"
                                                    value="{{ old('canonical', $contents['canonical'] ?? '') }}">
                                                <small class="text-muted">Se vazio, usa a URL atual da página.</small>
                                            </div>

                                            <div class="form-group">
                                                <label>Robots</label>
                                                <input type="text" name="meta_robots" class="form-control"
                                                    value="{{ old('meta_robots', $contents['meta_robots'] ?? '') }}"
                                                    placeholder="index,follow">
                                            </div>

                                            <div class="form-group">
                                                <label>OG Type</label>
                                                <input type="text" name="og_type" class="form-control"
                                                    value="{{ old('og_type', $contents['og_type'] ?? '') }}"
                                                    placeholder="website">
                                            </div>

                                            <div class="form-group">
                                                <label>Twitter Card</label>
                                                <input type="text" name="twitter_card" class="form-control"
                                                    value="{{ old('twitter_card', $contents['twitter_card'] ?? '') }}"
                                                    placeholder="summary_large_image">
                                            </div>

                                            <div class="form-group">
                                                <label>Imagem (OG)</label>
                                                <input type="file" name="meta_image" class="form-control-file" accept="image/*">
                                                @if(!empty($contents['meta_image']))
                                                    <div class="mt-2">
                                                        <img src="{{ asset('storage/' . ltrim($contents['meta_image'], '/')) }}" alt="OG"
                                                            style="max-width: 100%; border-radius: 8px;">
                                                    </div>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="remove_meta_image" value="1"
                                                            id="remove_meta_image">
                                                        <label class="form-check-label" for="remove_meta_image">Remover imagem atual</label>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="form-group mb-0">
                                                <label>Imagem (Twitter)</label>
                                                <input type="file" name="twitter_image" class="form-control-file" accept="image/*">
                                                @if(!empty($contents['twitter_image']))
                                                    <div class="mt-2">
                                                        <img src="{{ asset('storage/' . ltrim($contents['twitter_image'], '/')) }}" alt="Twitter"
                                                            style="max-width: 100%; border-radius: 8px;">
                                                    </div>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="remove_twitter_image" value="1"
                                                            id="remove_twitter_image">
                                                        <label class="form-check-label" for="remove_twitter_image">Remover imagem atual</label>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    @if($slug === 'institucional_contato')
                                        <div class="card card-outline card-info">
                                            <div class="card-header">
                                                <strong>Blocos do Contato</strong>
                                            </div>
                                            <div class="card-body">
                                                <p class="text-muted small mb-3">
                                                    Use estes placeholders para manter formulário, mapa e FAQ funcionando mesmo com HTML editável.
                                                </p>

                                                <div class="d-flex flex-column">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2 js-cms-insert-placeholder"
                                                        data-token="[[CONTACT_ALERTS]]" data-target="#cmsBody">
                                                        Inserir [[CONTACT_ALERTS]]
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2 js-cms-insert-placeholder"
                                                        data-token="[[CONTACT_INFO]]" data-target="#cmsBody">
                                                        Inserir [[CONTACT_INFO]]
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2 js-cms-insert-placeholder"
                                                        data-token="[[CONTACT_FORM]]" data-target="#cmsBody">
                                                        Inserir [[CONTACT_FORM]]
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-2 js-cms-insert-placeholder"
                                                        data-token="[[CONTACT_MAP_EMBED_URL]]" data-target="#cmsBody">
                                                        Inserir [[CONTACT_MAP_EMBED_URL]]
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary js-cms-insert-placeholder"
                                                        data-token="[[FAQ_SECTION]]" data-target="#cmsBody">
                                                        Inserir [[FAQ_SECTION]]
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif($slug === 'home')
                            <div class="form-group">
                                <label>Título do Hero</label>
                                <input type="text" name="hero_title" class="form-control"
                                    value="{{ old('hero_title', $contents['hero_title'] ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label>Subtítulo do Hero</label>
                                <textarea name="hero_subtitle" rows="3"
                                    class="form-control">{{ old('hero_subtitle', $contents['hero_subtitle'] ?? '') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Texto do Hero</label>
                                <textarea name="hero_text" rows="4"
                                    class="form-control">{{ old('hero_text', $contents['hero_text'] ?? '') }}</textarea>
                            </div>
                            <div class="form-group mb-0">
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
                                <textarea name="manifesto" rows="4"
                                    class="form-control">{{ old('manifesto', $contents['manifesto'] ?? '') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Visão</label>
                                <textarea name="vision" rows="4"
                                    class="form-control">{{ old('vision', $contents['vision'] ?? '') }}</textarea>
                            </div>
                            <div class="form-group mb-0">
                                <label>Valores</label>
                                <textarea name="values" rows="4"
                                    class="form-control">{{ old('values', $contents['values'] ?? '') }}</textarea>
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
                        <button type="submit" class="btn btn-primary">Salvar conteúdo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

