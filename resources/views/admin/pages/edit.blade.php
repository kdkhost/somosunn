@extends('admin.layouts.app')

@section('page_title', 'Editar Página — ' . $page->slug)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Páginas do Site</a></li>
    <li class="breadcrumb-item active">{{ $page->title ?? $page->slug }}</li>
@endsection

@php
    $slugRoutes = [
        'home' => 'home',
        'sobre' => 'sobre',
        'manifesto' => 'manifesto',
        'valores' => 'valores',
        'como-funciona' => 'como-funciona',
        'quem-somos' => 'quem-somos',
        'eventos' => 'events.index',
        'membros' => 'membros',
        'vagas-abertas' => 'jobs.public.index',
        'cursos' => 'courses.index',
        'portal' => 'portal',
        'premium' => 'premium',
        'feed' => 'social.feed',
        'somos-unicas' => 'somos-unicas',
        'somos-unicas-sobre' => 'site.somos-unicas.sobre',
    ];
    $siteUrl = isset($slugRoutes[$page->slug]) ? route($slugRoutes[$page->slug]) : null;

    $slugSections = [
        'home' => [
            'sec-hero' => ['icon' => 'fa-home', 'label' => 'Hero'],
            'sec-stats' => ['icon' => 'fa-chart-bar', 'label' => 'Estatísticas'],
            'sec-about' => ['icon' => 'fa-info-circle', 'label' => 'O que é a UNN'],
            'sec-events' => ['icon' => 'fa-calendar', 'label' => 'Eventos & Mentorias'],
            'sec-community' => ['icon' => 'fa-users', 'label' => 'Comunidade'],
            'sec-ranking' => ['icon' => 'fa-trophy', 'label' => 'Ranking & Depoimentos'],
            'sec-cta' => ['icon' => 'fa-bullhorn', 'label' => 'CTA Final'],
        ],
        'sobre' => [
            'sec-hero' => ['icon' => 'fa-heading', 'label' => 'Hero + Imagem'],
            'sec-stats' => ['icon' => 'fa-chart-bar', 'label' => 'Estatísticas'],
            'sec-history' => ['icon' => 'fa-book-open', 'label' => 'Nossa História'],
            'sec-diff' => ['icon' => 'fa-star', 'label' => 'Diferenciais'],
            'sec-cta' => ['icon' => 'fa-bullhorn', 'label' => 'CTA Final'],
        ],
        'manifesto' => [
            'sec-hero' => ['icon' => 'fa-fist-raised', 'label' => 'Hero'],
            'sec-sections' => ['icon' => 'fa-list', 'label' => 'Seções'],
            'sec-quote' => ['icon' => 'fa-quote-right', 'label' => 'Citação'],
            'sec-pillars' => ['icon' => 'fa-columns', 'label' => 'Pilares'],
            'sec-cta' => ['icon' => 'fa-bullhorn', 'label' => 'CTA Final'],
        ],
        'valores' => [
            'sec-header' => ['icon' => 'fa-heart', 'label' => 'Cabeçalho'],
            'sec-values' => ['icon' => 'fa-list-ul', 'label' => 'Valores'],
            'sec-quote' => ['icon' => 'fa-quote-left', 'label' => 'Citação'],
            'sec-cta' => ['icon' => 'fa-bullhorn', 'label' => 'CTA Final'],
        ],
        'como-funciona' => [
            'sec-header' => ['icon' => 'fa-cogs', 'label' => 'Cabeçalho'],
            'sec-steps' => ['icon' => 'fa-list-ol', 'label' => 'Passos'],
            'sec-plans' => ['icon' => 'fa-tags', 'label' => 'Planos'],
            'sec-cta' => ['icon' => 'fa-bullhorn', 'label' => 'CTA Final'],
        ],
        'quem-somos' => [
            'sec-header' => ['icon' => 'fa-users', 'label' => 'Cabeçalho'],
            'sec-founders' => ['icon' => 'fa-crown', 'label' => 'Fundadores'],
            'sec-team' => ['icon' => 'fa-user-friends', 'label' => 'Equipe'],
            'sec-stats' => ['icon' => 'fa-chart-bar', 'label' => 'Números'],
            'sec-cta' => ['icon' => 'fa-bullhorn', 'label' => 'CTA Final'],
        ],
        'portal' => [
            'sec-hero' => ['icon' => 'fa-network-wired', 'label' => 'Hero'],
            'sec-stats' => ['icon' => 'fa-chart-bar', 'label' => 'Estatísticas'],
            'sec-community' => ['icon' => 'fa-layer-group', 'label' => 'Níveis'],
            'sec-ranking' => ['icon' => 'fa-trophy', 'label' => 'Ranking'],
            'sec-cta' => ['icon' => 'fa-bullhorn', 'label' => 'CTA Final'],
        ],
        'somos-unicas' => [
            'sec-identity' => ['icon' => 'fa-palette', 'label' => 'Identidade'],
            'sec-hero' => ['icon' => 'fa-star', 'label' => 'Hero'],
            'sec-headers' => ['icon' => 'fa-heading', 'label' => 'Cabeçalhos'],
        ],
        'somos-unicas-sobre' => [
            'sec-identity' => ['icon' => 'fa-palette', 'label' => 'Identidade'],
            'sec-content' => ['icon' => 'fa-file-alt', 'label' => 'Conteúdo'],
        ],
    ];
    $sections = $slugSections[$page->slug] ?? [];
    $badgeColor = 'primary';
@endphp

@section('content')
    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5><i class="icon fas fa-ban mr-1"></i> Erro ao salvar</h5>
                <ul class="mb-0 pl-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form id="page-form" method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="page-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-seo-link" data-toggle="pill" href="#sec-seo" role="tab">
                                <i class="fas fa-search mr-1"></i> Geral & SEO
                            </a>
                        </li>
                        @foreach($sections as $anchor => $info)
                            <li class="nav-item">
                                <a class="nav-link" id="tab-{{ $anchor }}-link" data-toggle="pill" href="#{{ $anchor }}" role="tab">
                                    <i class="fas {{ $info['icon'] }} mr-1"></i> {{ $info['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="page-tabs-content">
                        {{-- Tab: Geral & SEO --}}
                        <div class="tab-pane fade show active" id="sec-seo" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-outline card-info">
                                        <div class="card-header"><h3 class="card-title">Identificação e Títulos</h3></div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>Nome da Página (Painel)</label>
                                                <input type="text" name="title" class="form-control" value="{{ $page->title }}">
                                            </div>
                                            <div class="form-group">
                                                <label>Título H1 Principal <small class="text-muted">(Visível no site)</small></label>
                                                <input type="text" name="h1_title" class="form-control" value="{{ $data['h1_title'] ?? '' }}" placeholder="Ex: Bem-vinda à UNN">
                                            </div>
                                            <div class="form-group">
                                                <label>Meta Title <small class="text-muted">(Google)</small></label>
                                                <input type="text" name="seo_title" class="form-control" value="{{ $data['seo_title'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card card-outline card-info">
                                        <div class="card-header"><h3 class="card-title">Metadados Avançados</h3></div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>Meta Description</label>
                                                <textarea name="seo_description" rows="3" class="form-control" id="seo_description">{{ $data['seo_description'] ?? '' }}</textarea>
                                                <small class="text-muted"><span id="seo-desc-count">0</span>/320 caracteres</small>
                                            </div>
                                            <div class="form-group">
                                                <label>Meta Keywords</label>
                                                <input type="text" name="seo_keywords" class="form-control" value="{{ $data['seo_keywords'] ?? '' }}" placeholder="palavra1, palavra2...">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Robots</label>
                                                        <select name="meta_robots" class="form-control">
                                                            @php $robots = $data['meta_robots'] ?? 'index,follow'; @endphp
                                                            <option value="index,follow" {{ $robots === 'index,follow' ? 'selected' : '' }}>Index, Follow</option>
                                                            <option value="noindex,follow" {{ $robots === 'noindex,follow' ? 'selected' : '' }}>NoIndex, Follow</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>OG Type</label>
                                                        <select name="og_type" class="form-control">
                                                            @php $ogType = $data['og_type'] ?? 'website'; @endphp
                                                            <option value="website" {{ $ogType === 'website' ? 'selected' : '' }}>Website</option>
                                                            <option value="article" {{ $ogType === 'article' ? 'selected' : '' }}>Article</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card card-outline card-warning">
                                        <div class="card-header"><h3 class="card-title">Imagens para Redes Sociais</h3></div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>OpenGraph Image <small class="text-muted">(FB/WA - 1200x630)</small></label>
                                                @include('admin.components.upload-global', [
                                                    'name' => 'seo_og_image',
                                                    'path' => $data['seo_og_image'] ?? ($data['seo_image'] ?? null),
                                                    'preview_url' => !empty($data['seo_og_image'] ?? ($data['seo_image'] ?? null)) ? asset('storage/' . ($data['seo_og_image'] ?? ($data['seo_image'] ?? null))) : null,
                                                    'remove_name' => 'remove_seo_og_image',
                                                    'accept' => 'image/*',
                                                ])
                                            </div>
                                            <hr>
                                            <div class="form-group mb-0">
                                                <label>Twitter Card Image <small class="text-muted">(1200x600)</small></label>
                                                @include('admin.components.upload-global', [
                                                    'name' => 'seo_twitter_image',
                                                    'path' => $data['seo_twitter_image'] ?? null,
                                                    'preview_url' => !empty($data['seo_twitter_image']) ? asset('storage/' . $data['seo_twitter_image']) : null,
                                                    'remove_name' => 'remove_seo_twitter_image',
                                                    'accept' => 'image/*',
                                                ])
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card card-outline card-secondary">
                                        <div class="card-header"><h3 class="card-title">Links Úteis</h3></div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label>URL Canônica</label>
                                                <input type="url" name="canonical_url" class="form-control" value="{{ $data['canonical_url'] ?? '' }}">
                                            </div>
                                            @if($siteUrl)
                                                <a href="{{ $siteUrl }}" target="_blank" class="btn btn-outline-info btn-block">
                                                    <i class="fas fa-external-link-alt mr-1"></i> Ver página no site
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary btn-block">
                                                <i class="fas fa-list mr-1"></i> Ver lista de páginas
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Inclusão do Conteúdo Dinâmico --}}
                        @php $partialView = 'admin.pages.partials.' . $page->slug; @endphp
                        @if (View::exists($partialView))
                            @include($partialView, ['data' => $data])
                        @else
                            <div class="tab-pane fade" id="sec-raw-json" role="tabpanel">
                                <div class="card card-outline card-danger">
                                    <div class="card-header"><h3 class="card-title">JSON Bruto</h3></div>
                                    <div class="card-body">
                                        <textarea name="raw_json" rows="20" class="form-control text-monospace" style="font-size:.85rem">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-footer bg-white border-top">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div id="unsaved-alert" class="text-warning d-none">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Alterações não salvas!
                            </div>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm px-5">
                                <i class="fas fa-save mr-1"></i> Salvar Todas as Alterações
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        $(function () {
            // Sincronização de abas com a URL (Zero Refresh)
            const hash = window.location.hash || '#sec-seo';
            $(`#page-tabs a[href="${hash}"]`).tab('show');

            $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                history.replaceState(null, null, e.target.hash);
                $(window).scrollTop(0);
            });

            // Ajustar o partial para se comportar como abas
            // Nota: Os partials atuais usam IDs como sec-hero, sec-stats.
            // Precisamos garantir que eles tenham a classe 'tab-pane fade' e estejam dentro do 'tab-content'.
            // Como eles já estão dentro de 'tab-content' via include, basta adicionar as classes via JS
            // ou garantir que o partial já as tenha (ideal).
            $('.tab-content div[id^="sec-"]').addClass('tab-pane fade');
            $(`.tab-content ${hash}`).addClass('show active');

            /* Meta counter */
            const desc = document.getElementById('seo_description');
            if (desc) {
                const count = document.getElementById('seo-desc-count');
                const update = () => count.textContent = desc.value.length;
                desc.addEventListener('input', update);
                update();
            }

            /* Marcador de alterações */
            $('input, textarea, select').on('change input', function() {
                $('#unsaved-alert').removeClass('d-none');
            });

            /* Motor de Repeater Global */
            window.initJSONRepeater = function({ containerId, inputId, addButtonId, itemSchema, template, initialData }) {
                const container = document.getElementById(containerId);
                const input = document.querySelector(`[name="${inputId}"]`);
                const addButton = document.getElementById(addButtonId);
                if (!container || !input || !addButton) return;
                let items = initialData || [];

                function sync() {
                    input.value = JSON.stringify(items);
                    $('#unsaved-alert').removeClass('d-none');
                }

                function render() {
                    container.innerHTML = '';
                    items.forEach((item, index) => {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'repeater-item card card-outline card-secondary mb-3 shadow-sm';
                        wrapper.dataset.index = index;
                        wrapper.innerHTML = `
                            <div class="card-header p-2 d-flex align-items-center justify-content-between bg-light">
                                <div class="handle cursor-move px-2 text-muted"><i class="fas fa-grip-vertical"></i></div>
                                <div class="text-[10px] font-bold text-muted uppercase">ITEM #${index + 1}</div>
                                <button type="button" class="btn btn-xs btn-outline-danger btn-remove ml-auto" data-index="${index}"><i class="fas fa-trash-alt"></i></button>
                            </div>
                            <div class="card-body p-3">${template(item, index)}</div>
                        `;
                        wrapper.querySelectorAll('input, textarea, select').forEach(field => {
                            field.addEventListener('input', function() {
                                const fieldMatch = this.name.match(/\[(.*?)\]/);
                                if (fieldMatch) items[index][fieldMatch[1]] = this.value;
                                sync();
                            });
                        });
                        wrapper.querySelector('.btn-remove').onclick = () => {
                            items.splice(index, 1); render(); sync();
                        };
                        container.appendChild(wrapper);
                    });
                }
                addButton.onclick = () => { items.push({ ...itemSchema }); render(); sync(); };
                new Sortable(container, { handle: '.handle', animation: 150, onEnd: () => {
                    const newItems = [];
                    container.querySelectorAll('.repeater-item').forEach(el => newItems.push(items[parseInt(el.dataset.index)]));
                    items = newItems; render(); sync();
                }});
                render();
            };
        });
    </script>
@endpush
<!-- VERSION: 2026-03-24-V2-TABS -->
