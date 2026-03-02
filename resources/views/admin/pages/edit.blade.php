@extends('admin.layouts.app')

@section('page_title', 'Editar Página — ' . $page->slug)
@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.pages.index') }}">Páginas</a>
    </li>
    <li class="breadcrumb-item active">{{ $page->slug }}</li>
@endsection

@section('content')

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h5><i class="icon fas fa-ban"></i> Verifique os erros</h5>
        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">

        {{-- ===== COLUNA PRINCIPAL: campos específicos do slug ===== --}}
        <div class="col-lg-8">

            @php $partialView = 'admin.pages.partials.' . $page->slug; @endphp

            @if (View::exists($partialView))
                @include($partialView, ['data' => $data])
            @else
                {{-- Fallback genérico para slugs sem partial --}}
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-code mr-1"></i> Dados JSON brutos</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Nenhum formulário específico para este slug. Edite os dados diretamente em JSON:</p>
                        <textarea name="raw_json" rows="20" class="form-control font-monospace" style="font-family: monospace; font-size: 13px">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                    </div>
                </div>
            @endif

        </div>

        {{-- ===== COLUNA LATERAL: campos comuns a todas as páginas ===== --}}
        <div class="col-lg-4">

            {{-- Identificação --}}
            <div class="card card-outline card-info sticky-top" style="top: 70px">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tag mr-1"></i> Identificação</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Slug <small class="text-muted">(somente leitura)</small></label>
                        <input type="text" class="form-control bg-light" value="{{ $page->slug }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="title">Título <small class="text-muted">(exibido no painel)</small></label>
                        <input type="text"
                               id="title"
                               name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $page->title) }}">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-search mr-1"></i> SEO</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="seo_title">Meta title</label>
                        <input type="text"
                               id="seo_title"
                               name="seo_title"
                               class="form-control @error('seo_title') is-invalid @enderror"
                               value="{{ old('seo_title', $data['seo_title'] ?? '') }}"
                               maxlength="255"
                               placeholder="Ex: Sobre Nós — UNN">
                        @error('seo_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group mb-0">
                        <label for="seo_description">Meta description</label>
                        <textarea id="seo_description"
                                  name="seo_description"
                                  rows="3"
                                  maxlength="320"
                                  class="form-control @error('seo_description') is-invalid @enderror"
                                  placeholder="Até 160 caracteres recomendado">{{ old('seo_description', $data['seo_description'] ?? '') }}</textarea>
                        @error('seo_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted"><span id="seo-desc-count">0</span>/320 caracteres</small>
                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-save mr-1"></i> Salvar alterações
                    </button>
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-clock mr-1"></i>
                            Última atualização: {{ $page->updated_at?->format('d/m/Y H:i') ?? '—' }}
                        </small>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const desc = document.getElementById('seo_description');
    const counter = document.getElementById('seo-desc-count');
    if (desc && counter) {
        const update = () => { counter.textContent = desc.value.length; };
        desc.addEventListener('input', update);
        update();
    }

    // Atualiza label dos custom-file-input com o nome do arquivo selecionado
    document.querySelectorAll('.custom-file-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const label = this.nextElementSibling;
            if (label) {
                label.textContent = this.files.length > 0 ? this.files[0].name : 'Escolher imagem...';
            }
        });
    });

    // Formata e valida todos os textareas JSON da página
    document.querySelectorAll('textarea[data-json]').forEach(function (ta) {
        // Botão de formato
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-xs btn-outline-secondary mt-1';
        btn.innerHTML = '<i class="fas fa-magic"></i> Formatar JSON';
        btn.onclick = function () {
            try {
                const parsed = JSON.parse(ta.value);
                ta.value = JSON.stringify(parsed, null, 2);
                ta.classList.remove('is-invalid');
            } catch (e) {
                ta.classList.add('is-invalid');
                alert('JSON inválido: ' + e.message);
            }
        };
        ta.after(btn);
    });
})();
</script>
@endpush
