@extends('admin.layouts.app')

@section('page_title', 'Editar Página — ' . $page->slug)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pages.index') }}">Páginas do Site</a></li>
    <li class="breadcrumb-item active">{{ $page->title ?? $page->slug }}</li>
@endsection

@php
$slugRoutes = [
    'home'          => 'home',
    'sobre'         => 'sobre',
    'manifesto'     => 'manifesto',
    'valores'       => 'valores',
    'como-funciona' => 'como-funciona',
    'quem-somos'    => 'quem-somos',
    'eventos'       => 'events.index',
    'membros'       => 'membros',
    'vagas-abertas' => 'jobs.public.index',
    'cursos'        => 'courses.index',
    'portal'        => 'portal',
    'premium'       => 'premium',
    'feed'          => 'social.feed',
];
$siteUrl = isset($slugRoutes[$page->slug]) ? route($slugRoutes[$page->slug]) : null;

$slugSections = [
    'home' => [
        'sec-hero'      => ['icon' => 'fa-home',       'label' => 'Hero'],
        'sec-stats'     => ['icon' => 'fa-chart-bar',  'label' => 'Estatísticas'],
        'sec-about'     => ['icon' => 'fa-info-circle','label' => 'O que é a UNN'],
        'sec-events'    => ['icon' => 'fa-calendar',   'label' => 'Eventos & Mentorias'],
        'sec-community' => ['icon' => 'fa-users',      'label' => 'Comunidade'],
        'sec-ranking'   => ['icon' => 'fa-trophy',     'label' => 'Ranking & Depoimentos'],
        'sec-cta'       => ['icon' => 'fa-bullhorn',   'label' => 'CTA Final'],
    ],
    'sobre' => [
        'sec-hero'    => ['icon' => 'fa-heading',   'label' => 'Hero + Imagem'],
        'sec-stats'   => ['icon' => 'fa-chart-bar', 'label' => 'Estatísticas'],
        'sec-history' => ['icon' => 'fa-book-open', 'label' => 'Nossa História'],
        'sec-diff'    => ['icon' => 'fa-star',      'label' => 'Diferenciais'],
        'sec-cta'     => ['icon' => 'fa-bullhorn',  'label' => 'CTA Final'],
    ],
    'manifesto' => [
        'sec-hero'     => ['icon' => 'fa-fist-raised', 'label' => 'Hero'],
        'sec-sections' => ['icon' => 'fa-list',        'label' => 'Seções do Manifesto'],
        'sec-quote'    => ['icon' => 'fa-quote-right', 'label' => 'Citação Final'],
        'sec-pillars'  => ['icon' => 'fa-columns',     'label' => 'Pilares'],
        'sec-cta'      => ['icon' => 'fa-bullhorn',    'label' => 'CTA Final'],
    ],
    'valores' => [
        'sec-header' => ['icon' => 'fa-heart',      'label' => 'Cabeçalho'],
        'sec-values' => ['icon' => 'fa-list-ul',    'label' => 'Os 6 Valores (JSON)'],
        'sec-quote'  => ['icon' => 'fa-quote-left', 'label' => 'Citação Central'],
        'sec-cta'    => ['icon' => 'fa-bullhorn',   'label' => 'CTA Final'],
    ],
    'como-funciona' => [
        'sec-header' => ['icon' => 'fa-cogs',    'label' => 'Cabeçalho'],
        'sec-steps'  => ['icon' => 'fa-list-ol', 'label' => 'Passos (JSON)'],
        'sec-plans'  => ['icon' => 'fa-tags',    'label' => 'Seção Planos'],
        'sec-cta'    => ['icon' => 'fa-bullhorn','label' => 'CTA Final'],
    ],
    'quem-somos' => [
        'sec-header'   => ['icon' => 'fa-users',        'label' => 'Cabeçalho + Imagem'],
        'sec-founders' => ['icon' => 'fa-crown',        'label' => 'Fundadores (JSON)'],
        'sec-team'     => ['icon' => 'fa-user-friends', 'label' => 'Equipe (JSON)'],
        'sec-stats'    => ['icon' => 'fa-chart-bar',    'label' => 'UNN em Números'],
        'sec-cta'      => ['icon' => 'fa-bullhorn',     'label' => 'CTA Final'],
    ],
    // Páginas de app
    'eventos' => [
        'sec-hero' => ['icon' => 'fa-calendar-alt', 'label' => 'Hero & Badge'],
        'sec-cta'  => ['icon' => 'fa-bullhorn',     'label' => 'CTA Final'],
    ],
    'membros' => [
        'sec-hero' => ['icon' => 'fa-users', 'label' => 'Hero'],
    ],
    'vagas-abertas' => [
        'sec-hero' => ['icon' => 'fa-briefcase', 'label' => 'Hero & Badge'],
    ],
    'cursos' => [
        'sec-hero' => ['icon' => 'fa-graduation-cap', 'label' => 'Hero & Badge'],
    ],
    'portal' => [
        'sec-hero'  => ['icon' => 'fa-network-wired', 'label' => 'Hero'],
        'sec-stats' => ['icon' => 'fa-chart-bar',     'label' => 'Estatísticas'],
        'sec-cta'   => ['icon' => 'fa-bullhorn',      'label' => 'CTA Final'],
    ],
    'premium' => [
        'sec-hero'  => ['icon' => 'fa-crown',  'label' => 'Hero & Imagem'],
        'sec-plans' => ['icon' => 'fa-tags',   'label' => 'Seção de Planos'],
    ],
    'feed' => [
        'sec-seo' => ['icon' => 'fa-search', 'label' => 'SEO'],
    ],
];
$sections = $slugSections[$page->slug] ?? [];

$slugColors = [
    'home'          => 'primary',
    'sobre'         => 'info',
    'manifesto'     => 'warning',
    'valores'       => 'danger',
    'como-funciona' => 'success',
    'quem-somos'    => 'secondary',
    'eventos'       => 'primary',
    'membros'       => 'info',
    'vagas-abertas' => 'success',
    'cursos'        => 'warning',
    'portal'        => 'secondary',
    'premium'       => 'danger',
    'feed'          => 'dark',
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
            @if (View::exists($partialView))
                @include($partialView, ['data' => $data])
            @else
                <div class="card card-outline card-secondary">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-code mr-1"></i> Dados JSON brutos</h3></div>
                    <div class="card-body">
                        <p class="text-muted small">Nenhum formulário específico para este slug. Edite os dados diretamente em JSON:</p>
                        <textarea name="raw_json" rows="24" class="form-control" style="font-family:monospace;font-size:13px">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                    </div>
                </div>
            @endif
        </div>

        {{-- ===== SIDEBAR ===== --}}
        <div class="col-xl-4 col-lg-5">
            <div class="sticky-top" style="top:70px">

                {{-- Ações --}}
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-save mr-1"></i> Ações</h3></div>
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

                {{-- Identificação & SEO --}}
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-search mr-1"></i> SEO &amp; Identificação</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="mb-1">Slug <small class="text-muted">(somente leitura)</small></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text text-muted">/</span></div>
                                <input type="text" class="form-control bg-light" value="{{ $page->slug === 'home' ? '' : $page->slug }}" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="mb-1" for="title">Título <small class="text-muted">(no painel)</small></label>
                            <input type="text" id="title" name="title"
                                   class="form-control form-control-sm @error('title') is-invalid @enderror"
                                   value="{{ old('title', $page->title) }}">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label class="mb-1" for="seo_title">Meta Title <small class="text-muted">(~60 car.)</small></label>
                            <input type="text" id="seo_title" name="seo_title"
                                   class="form-control form-control-sm @error('seo_title') is-invalid @enderror"
                                   value="{{ old('seo_title', $data['seo_title'] ?? '') }}"
                                   maxlength="255"
                                   placeholder="Ex: Sobre a UNN — Rede de Empreendedores">
                            @error('seo_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="mb-1" for="seo_description">
                                Meta Description
                                <small class="text-muted">(~160 car.)</small>
                            </label>
                            <textarea id="seo_description" name="seo_description"
                                      rows="3" maxlength="320"
                                      class="form-control form-control-sm @error('seo_description') is-invalid @enderror"
                                      placeholder="Texto exibido nos resultados do Google">{{ old('seo_description', $data['seo_description'] ?? '') }}</textarea>
                            @error('seo_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted d-flex justify-content-between">
                                <span>Recomendado: até 160 car.</span>
                                <span><span id="seo-desc-count">0</span>/320</span>
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Navegação rápida --}}
                @if(!empty($sections))
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-map-signs mr-1"></i> Ir para seção</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-unstyled mb-0">
                            @foreach($sections as $anchor => $info)
                            <li>
                                <a href="#{{ $anchor }}" class="section-jump d-flex align-items-center px-3 py-2 text-dark border-bottom" style="font-size:.875rem;text-decoration:none" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background=''">
                                    <i class="fas {{ $info['icon'] }} text-secondary mr-2" style="width:16px;font-size:.8rem"></i>
                                    {{ $info['label'] }}
                                    <i class="fas fa-chevron-right ml-auto text-muted" style="font-size:.65rem"></i>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                {{-- Dicas --}}
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-lightbulb mr-1"></i> Dicas de uso</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-3" style="font-size:.82rem;line-height:1.7">
                        <ul class="pl-3 mb-0">
                            <li>Campos vazios exibem o valor padrão do sistema.</li>
                            <li>Campos <span class="badge badge-secondary badge-sm">JSON</span> aceitam arrays — use <strong>Formatar JSON</strong> para validar antes de salvar.</li>
                            <li>Imagens ficam disponíveis no site imediatamente após salvar.</li>
                            <li>O botão "Visualizar no site" abre a página sem sair do painel.</li>
                        </ul>
                    </div>
                </div>

            </div>{{-- /sticky --}}
        </div>{{-- /sidebar --}}

    </div>{{-- /row --}}
</form>
@endsection

@push('scripts')
<script>
(function () {
    /* Contador meta description */
    const desc = document.getElementById('seo_description');
    const counter = document.getElementById('seo-desc-count');
    if (desc && counter) {
        const upd = () => {
            const n = desc.value.length;
            counter.textContent = n;
            counter.style.color = n > 160 ? '#dc3545' : (n > 120 ? '#fd7e14' : '');
        };
        desc.addEventListener('input', upd); upd();
    }

    /* Alerta de alterações não salvas */
    let dirty = false;
    const form = document.getElementById('page-form');
    const alert = document.getElementById('unsaved-alert');
    const btnSave = document.getElementById('btn-save');
    if (form) {
        form.addEventListener('change', () => {
            if (!dirty) {
                dirty = true;
                alert?.classList.remove('d-none');
                btnSave?.classList.replace('btn-primary', 'btn-warning');
            }
        });
        form.addEventListener('submit', () => { dirty = false; });
    }
    window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

    /* Atualiza label dos file inputs */
    document.querySelectorAll('.custom-file-input').forEach(inp => {
        inp.addEventListener('change', function () {
            const lbl = this.nextElementSibling;
            if (lbl) lbl.textContent = this.files.length ? this.files[0].name : 'Escolher imagem...';
        });
    });

    /* Preview inline ao selecionar imagem (file input com data-preview="id") */
    document.querySelectorAll('.custom-file-input[data-preview]').forEach(inp => {
        inp.addEventListener('change', function () {
            const prev = document.getElementById(this.dataset.preview);
            if (!prev || !this.files?.[0]) return;
            const reader = new FileReader();
            reader.onload = e => { prev.src = e.target.result; prev.classList.remove('d-none'); };
            reader.readAsDataURL(this.files[0]);
        });
    });

    /* Scroll suave para seção */
    document.querySelectorAll('.section-jump').forEach(link => {
        link.addEventListener('click', function (e) {
            const el = document.getElementById(this.getAttribute('href').slice(1));
            if (el) {
                e.preventDefault();
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                const was = el.style.transition;
                el.style.transition = 'box-shadow .25s';
                el.style.boxShadow = '0 0 0 3px rgba(0,123,255,.4)';
                setTimeout(() => { el.style.boxShadow = was || ''; }, 1600);
            }
        });
    });

    /* Summernote compacto para subtítulos / campos HTML curtos */
    if (typeof $ !== 'undefined' && $.fn && $.fn.summernote) {
        $('.summernote-sm').summernote({
            height: 120,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol']],
                ['insert', ['link']],
                ['view', ['codeview']]
            ],
            callbacks: {
                onChange: function () {
                    /* dispara o dirty-check do formulário */
                    const form = document.getElementById('page-form');
                    if (form) form.dispatchEvent(new Event('change'));
                }
            }
        });
    }

    /* Formatar + Copiar JSON */
    document.querySelectorAll('textarea[data-json]').forEach(ta => {
        const wrap = document.createElement('div');
        wrap.style.cssText = 'display:flex;gap:6px;margin-top:6px';

        const btnFmt = document.createElement('button');
        btnFmt.type = 'button';
        btnFmt.className = 'btn btn-xs btn-outline-secondary';
        btnFmt.innerHTML = '<i class="fas fa-magic mr-1"></i>Formatar JSON';
        btnFmt.onclick = () => {
            try {
                ta.value = JSON.stringify(JSON.parse(ta.value), null, 2);
                ta.classList.remove('is-invalid');
                btnFmt.className = 'btn btn-xs btn-outline-success';
                btnFmt.innerHTML = '<i class="fas fa-check mr-1"></i>Válido!';
                setTimeout(() => { btnFmt.className = 'btn btn-xs btn-outline-secondary'; btnFmt.innerHTML = '<i class="fas fa-magic mr-1"></i>Formatar JSON'; }, 1600);
            } catch (err) {
                ta.classList.add('is-invalid');
                alert('JSON inválido: ' + err.message);
            }
        };

        const btnCopy = document.createElement('button');
        btnCopy.type = 'button';
        btnCopy.className = 'btn btn-xs btn-outline-secondary';
        btnCopy.innerHTML = '<i class="fas fa-copy mr-1"></i>Copiar';
        btnCopy.onclick = () => {
            navigator.clipboard?.writeText(ta.value).then(() => {
                btnCopy.innerHTML = '<i class="fas fa-check mr-1"></i>Copiado!';
                setTimeout(() => { btnCopy.innerHTML = '<i class="fas fa-copy mr-1"></i>Copiar'; }, 1500);
            });
        };

        wrap.appendChild(btnFmt);
        wrap.appendChild(btnCopy);
        ta.after(wrap);
    });
})();
</script>
@endpush
