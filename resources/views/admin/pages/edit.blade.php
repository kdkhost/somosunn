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
            'sec-about' => ['icon' => 'fa-info-circle', 'label' => 'O que é a SOMOS UNN'],
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
            'sec-sections' => ['icon' => 'fa-list', 'label' => 'Seções do Manifesto'],
            'sec-quote' => ['icon' => 'fa-quote-right', 'label' => 'Citação Final'],
            'sec-pillars' => ['icon' => 'fa-columns', 'label' => 'Pilares'],
            'sec-cta' => ['icon' => 'fa-bullhorn', 'label' => 'CTA Final'],
        ],
        'valores' => [
            'sec-header' => ['icon' => 'fa-heart', 'label' => 'Cabeçalho'],
            'sec-values' => ['icon' => 'fa-list-ul', 'label' => 'Os 6 Valores (Visual)'],
            'sec-quote' => ['icon' => 'fa-quote-left', 'label' => 'Citação Central'],
            'sec-cta' => ['icon' => 'fa-bullhorn', 'label' => 'CTA Final'],
        ],
        'como-funciona' => [
            'sec-header' => ['icon' => 'fa-cogs', 'label' => 'Cabeçalho'],
            'sec-steps' => ['icon' => 'fa-list-ol', 'label' => 'Passos (Visual)'],
            'sec-plans' => ['icon' => 'fa-tags', 'label' => 'Seção Planos'],
            'sec-cta' => ['icon' => 'fa-bullhorn', 'label' => 'CTA Final'],
        ],
        'quem-somos' => [
            'sec-header' => ['icon' => 'fa-users', 'label' => 'Cabeçalho + Imagem'],
            'sec-founders' => ['icon' => 'fa-crown', 'label' => 'Fundadores (Visual)'],
            'sec-team' => ['icon' => 'fa-user-friends', 'label' => 'Equipe (Visual)'],
            'sec-stats' => ['icon' => 'fa-chart-bar', 'label' => 'SOMOS UNN em Números'],
            'sec-cta' => ['icon' => 'fa-bullhorn', 'label' => 'CTA Final'],
        ],
        'portal' => [
            'sec-hero' => ['icon' => 'fa-network-wired', 'label' => 'Hero'],
            'sec-stats' => ['icon' => 'fa-chart-bar', 'label' => 'Estatísticas'],
            'sec-community' => ['icon' => 'fa-layer-group', 'label' => 'Niveis da Comunidade'],
            'sec-ranking' => ['icon' => 'fa-trophy', 'label' => 'Top Networkers'],
            'sec-cta' => ['icon' => 'fa-bullhorn', 'label' => 'CTA Final'],
        ],
        'somos-unicas' => [
            'sec-identity' => ['icon' => 'fa-palette', 'label' => 'Identidade'],
            'sec-hero' => ['icon' => 'fa-star', 'label' => 'Hero Section'],
            'sec-headers' => ['icon' => 'fa-heading', 'label' => 'Cabeçalhos'],
            'sec-empty' => ['icon' => 'fa-ghost', 'label' => 'Estado Vazio'],
        ],
        'somos-unicas-sobre' => [
            'sec-identity' => ['icon' => 'fa-palette', 'label' => 'Identidade'],
            'sec-content' => ['icon' => 'fa-file-alt', 'label' => 'Conteúdo Principal'],
        ],
    ];
    $sections = $slugSections[$page->slug] ?? [];

    $slugColors = [
        'home' => 'primary',
        'sobre' => 'info',
        'manifesto' => 'warning',
        'valores' => 'danger',
        'como-funciona' => 'success',
        'quem-somos' => 'secondary',
        'eventos' => 'primary',
        'portal' => 'secondary',
        'somos-unicas' => 'pink',
        'somos-unicas-sobre' => 'pink',
    ];
    $badgeColor = $slugColors[$page->slug] ?? 'dark';
@endphp

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h5 class="mb-2"><i class="icon fas fa-ban mr-1"></i> Corrija os erros antes de salvar</h5>
            <ul class="mb-0 pl-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Barra de contexto --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3" style="gap:.5rem">
        <div class="d-flex align-items-center" style="gap:.6rem">
            <span class="badge badge-{{ $badgeColor }} px-3 py-2" style="font-size:.85rem">
                <i class="fas fa-file-alt mr-1"></i>/{{ $page->slug === 'home' ? '' : $page->slug }}
            </span>
            <span class="text-muted small">
                <i class="fas fa-clock mr-1"></i>
                Salvo: {{ $page->updated_at?->setTimezone('America/Sao_Paulo')->format('d/m/Y \à\s H:i') ?? '—' }}
            </span>
        </div>
        @if($siteUrl)
            <a href="{{ $siteUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-external-link-alt mr-1"></i> Ver página no site
            </a>
        @endif
    </div>

    <div id="unsaved-alert" class="alert alert-warning d-none" role="alert">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        <strong>Alterações não salvas.</strong> Clique em "Salvar alterações" para publicar.
    </div>

    <form id="page-form" method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">

            {{-- ===== COLUNA PRINCIPAL ===== --}}
            <div class="col-xl-8 col-lg-7">
                @php $partialView = 'admin.pages.partials.' . $page->slug; @endphp

                @if ($page->slug === 'somos-unicas')
                    @include('admin.pages.partials.somos-unicas', ['data' => $data])
                @elseif ($page->slug === 'somos-unicas-sobre')
                    @include('admin.pages.partials.somos-unicas-sobre', ['data' => $data])
                @elseif (View::exists($partialView))
                    <div id="dynamic-sections">
                        @include($partialView, ['data' => $data])
                    </div>
                @else
                    <section id="sec-raw-json" class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-code mr-1"></i> Dados JSON brutos</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">Nenhum formulário específico para este slug. Edite os dados diretamente em JSON:</p>
                            <textarea name="raw_json" rows="24" class="form-control" style="font-family:monospace;font-size:13px">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                        </div>
                    </section>
                @endif
            </div>

            {{-- ===== SIDEBAR ===== --}}
            <div class="col-xl-4 col-lg-5">
                <div class="sticky-top" style="top:70px">

                    {{-- Ações --}}
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-save mr-1"></i> Ações</h3>
                        </div>
                        <div class="card-body pb-2">
                            <button type="submit" class="btn btn-primary btn-block mb-2" id="btn-save">
                                <i class="fas fa-save mr-1"></i> Salvar alterações
                            </button>
                            @if($siteUrl)
                                <a href="{{ $siteUrl }}" target="_blank" class="btn btn-outline-info btn-block mb-2">
                                    <i class="fas fa-eye mr-1"></i> Visualizar no site
                                </a>
                            @endif
                            <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-arrow-left mr-1"></i> Voltar à lista
                            </a>
                        </div>
                    </div>

                    {{-- Configurações da Página (SEO Robusto) --}}
                    <section id="sec-seo" class="card card-outline card-info">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title text-bold"><i class="fas fa-search mr-1"></i> Configurações da Página</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Identificação Interna --}}
                            <div class="form-group mb-4">
                                <label class="text-xs text-uppercase text-muted mb-2">Identificação Interna</label>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small font-weight-bold mb-1">Slug</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">/</span></div>
                                            <input type="text" class="form-control bg-light" value="{{ $page->slug === 'home' ? '' : $page->slug }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="small font-weight-bold mb-1" for="title">Nome no Painel</label>
                                        <input type="text" id="title" name="title" class="form-control form-control-sm" value="{{ $page->title }}">
                                    </div>
                                </div>
                            </div>

                            {{-- SEO Principal --}}
                            <hr class="my-4">
                            <div class="form-group">
                                <label class="text-xs text-uppercase text-muted mb-2">SEO Principal</label>
                                
                                <div class="mb-3">
                                    <label class="small font-weight-bold mb-1" for="h1_title">Título H1 <small class="text-muted">(Destaque no site)</small></label>
                                    <input type="text" id="h1_title" name="h1_title" class="form-control form-control-sm" value="{{ $data['h1_title'] ?? '' }}" placeholder="Ex: O Manifesto UNN">
                                    <p class="text-xs text-muted mb-0">Título principal exibido visualmente na página.</p>
                                </div>

                                <div class="mb-3">
                                    <label class="small font-weight-bold mb-1" for="seo_title">Meta Title <small class="text-muted">(Título Google)</small></label>
                                    <input type="text" id="seo_title" name="seo_title" class="form-control form-control-sm" value="{{ $data['seo_title'] ?? '' }}" placeholder="Título para os buscadores...">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="small font-weight-bold mb-1" for="seo_description">Meta Description</label>
                                    <textarea id="seo_description" name="seo_description" rows="3" class="form-control form-control-sm" placeholder="Breve resumo para os buscadores...">{{ $data['seo_description'] ?? '' }}</textarea>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="text-xs text-muted">Ideal: até 160 caracteres.</span>
                                        <span class="text-xs text-muted"><span id="seo-desc-count">0</span>/320</span>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="small font-weight-bold mb-1" for="seo_keywords">Meta Keywords</label>
                                    <input type="text" id="seo_keywords" name="seo_keywords" class="form-control form-control-sm" value="{{ $data['seo_keywords'] ?? '' }}" placeholder="ex: curso, mentoria, comunidade">
                                    <p class="text-xs text-muted mb-0">Separe as palavras por vírgula.</p>
                                </div>
                            </div>

                            {{-- Indexação & Avançado --}}
                            <hr class="my-4">
                            <div class="form-group">
                                <label class="text-xs text-uppercase text-muted mb-2">Indexação &amp; Avançado</label>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <label class="small font-weight-bold mb-1">Robots</label>
                                        <select name="meta_robots" class="form-control form-control-sm">
                                            @php $robots = $data['meta_robots'] ?? 'index,follow'; @endphp
                                            <option value="index,follow" {{ $robots === 'index,follow' ? 'selected' : '' }}>Index, Follow</option>
                                            <option value="noindex,follow" {{ $robots === 'noindex,follow' ? 'selected' : '' }}>NoIndex, Follow</option>
                                            <option value="index,nofollow" {{ $robots === 'index,nofollow' ? 'selected' : '' }}>Index, NoFollow</option>
                                            <option value="noindex,nofollow" {{ $robots === 'noindex,nofollow' ? 'selected' : '' }}>NoIndex, NoFollow</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small font-weight-bold mb-1">OG Type</label>
                                        <select name="og_type" class="form-control form-control-sm">
                                            @php $ogType = $data['og_type'] ?? 'website'; @endphp
                                            <option value="website" {{ $ogType === 'website' ? 'selected' : '' }}>Website</option>
                                            <option value="article" {{ $ogType === 'article' ? 'selected' : '' }}>Article</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label class="small font-weight-bold mb-1" for="canonical_url">URL Canônica <small class="text-muted">(Opcional)</small></label>
                                    <input type="url" id="canonical_url" name="canonical_url" class="form-control form-control-sm" value="{{ $data['canonical_url'] ?? '' }}" placeholder="https://exemplo.com/pagina">
                                </div>
                            </div>

                            {{-- Imagens Sociais --}}
                            <hr class="my-4">
                            <div class="form-group mb-0">
                                <label class="text-xs text-uppercase text-muted mb-3 d-block">Social Sharing (OG / Twitter)</label>
                                
                                <div class="mb-4">
                                    <label class="small font-weight-bold mb-2">OpenGraph Image <small class="text-muted">(FB/WA - 1200x630)</small></label>
                                    @include('admin.components.upload-global', [
                                        'name' => 'seo_og_image',
                                        'path' => $data['seo_og_image'] ?? ($data['seo_image'] ?? null),
                                        'preview_url' => !empty($data['seo_og_image'] ?? ($data['seo_image'] ?? null)) ? asset('storage/' . ($data['seo_og_image'] ?? ($data['seo_image'] ?? null))) : null,
                                        'remove_name' => 'remove_seo_og_image',
                                        'accept' => 'image/*',
                                    ])
                                </div>

                                <div class="mb-0">
                                    <label class="small font-weight-bold mb-2">Twitter Card Image <small class="text-muted">(Twitter - 1200x600)</small></label>
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
                    </section>

                    {{-- Navegação rápida --}}
                    @if(!empty($sections))
                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-map-signs mr-1"></i> Ir para seção</h3>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-unstyled mb-0">
                                    @foreach($sections as $anchor => $info)
                                        <li>
                                            <a href="#{{ $anchor }}" class="section-jump d-flex align-items-center px-3 py-2 text-dark border-bottom" style="font-size:.875rem;text-decoration:none">
                                                <i class="fas {{ $info['icon'] }} text-secondary mr-2" style="width:16px;font-size:.8rem"></i>
                                                {{ $info['label'] }}
                                                <i class="fas fa-chevron-right ml-auto text-muted" style="font-size:.65rem"></i>
                                            </a>
                                        </li>
                                    @endforeach
                                    {{-- Sempre incluir SEO como âncora --}}
                                    <li>
                                        <a href="#sec-seo" class="section-jump d-flex align-items-center px-3 py-2 text-dark" style="font-size:.875rem;text-decoration:none">
                                            <i class="fas fa-search text-secondary mr-2" style="width:16px;font-size:.8rem"></i>
                                            SEO & Meta Tags
                                            <i class="fas fa-chevron-right ml-auto text-muted" style="font-size:.65rem"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        (function () {
            /* Motor de Repeater */
            window.initJSONRepeater = function({ containerId, inputId, addButtonId, itemSchema, template, initialData }) {
                const container = document.getElementById(containerId);
                const input = document.querySelector(`[name="${inputId}"]`);
                const addButton = document.getElementById(addButtonId);
                if (!container || !input || !addButton) return;
                let items = initialData || [];

                function sync() {
                    input.value = JSON.stringify(items);
                    window.dirty = true;
                    document.getElementById('unsaved-alert')?.classList.remove('d-none');
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

            /* Navegação por Abas (Zero Refresh) */
            const sections = document.querySelectorAll('.col-xl-8 section, .col-xl-8 div[id^="sec-"], #sec-seo');
            function showSection(targetId) {
                if (!targetId) return;
                const id = targetId.replace('#', '');
                const targetElement = document.getElementById(id);
                if (targetElement) {
                    sections.forEach(s => s.classList.add('d-none'));
                    targetElement.classList.remove('d-none');
                    document.querySelectorAll('a[href^="#sec-"]').forEach(link => {
                        link.classList.toggle('bg-light', link.getAttribute('href') === `#${id}`);
                        link.classList.toggle('font-weight-bold', link.getAttribute('href') === `#${id}`);
                    });
                    history.replaceState(null, null, `#${id}`);
                }
            }

            document.addEventListener('click', function(e) {
                const link = e.target.closest('a[href^="#sec-"]');
                if (link) {
                    e.preventDefault();
                    showSection(link.getAttribute('href'));
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });

            const currentHash = window.location.hash || '#sec-seo';
            showSection(currentHash);

            /* Meta counters */
            const desc = document.getElementById('seo_description');
            if (desc) {
                desc.addEventListener('input', () => { document.getElementById('seo-desc-count').textContent = desc.value.length; });
            }
        })();
    </script>
@endpush
