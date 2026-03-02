{{-- Partial: quem-somos --}}
{{-- $data = $page->data ?? [] --}}

{{-- Intro --}}
<div id="sec-header" class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-users mr-1"></i> Cabeçalho</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Subtítulo do hero</label>
            <input type="text" name="hero_subtitle" class="form-control" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}" placeholder="Conheça as pessoas por trás da maior comunidade de networking do Brasil.">
        </div>
        {{-- Imagem de capa --}}
        <div class="form-group mb-0">
            <label class="font-weight-bold">Imagem de capa <small class="text-muted font-weight-normal">(banner abaixo do título — JPG, PNG, WebP — máx 6 MB)</small></label>
            <div class="mb-2">
                <img id="prev-cover-img"
                     src="{{ !empty($data['cover_image']) ? asset('storage/'.$data['cover_image']) : '' }}"
                     class="img-fluid rounded shadow-sm {{ empty($data['cover_image']) ? 'd-none' : '' }}"
                     style="max-height:220px;max-width:100%;object-fit:cover;border:1px solid #dee2e6"
                     alt="Preview da capa">
            </div>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="cover_image" name="cover_image"
                       accept="image/*" data-preview="prev-cover-img">
                <label class="custom-file-label" for="cover_image">
                    {{ !empty($data['cover_image']) ? 'Substituir imagem...' : 'Escolher imagem...' }}
                </label>
            </div>
            @if (!empty($data['cover_image']))
                <div class="custom-control custom-checkbox mt-2">
                    <input type="checkbox" class="custom-control-input" id="remove_cover_image" name="remove_cover_image" value="1">
                    <label class="custom-control-label text-danger" for="remove_cover_image">Remover imagem atual</label>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Fundadores (array JSON) --}}
<div id="sec-founders" class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-crown mr-1"></i> Fundadores</h3>
        <div class="card-tools"><span class="badge badge-secondary">JSON</span></div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Título da seção</label>
            <input type="text" name="founders_title" class="form-control" value="{{ old('founders_title', $data['founders_title'] ?? '') }}" placeholder="Fundadores">
        </div>
        <p class="text-muted small mb-2">
            Array de objetos com os campos:
            <code>name</code>, <code>role</code>, <code>bio</code>, <code>initials</code> (2 letras para o avatar).
        </p>
        @error('founders_json')<div class="alert alert-danger py-2 small">{{ $message }}</div>@enderror
        <textarea name="founders_json"
                  rows="20"
                  data-json="1"
                  class="form-control @error('founders_json') is-invalid @enderror"
                  style="font-family: monospace; font-size: 12px">{{ old('founders_json', json_encode($data['founders'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
    </div>
</div>

{{-- Equipe (array JSON) --}}
<div id="sec-team" class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-friends mr-1"></i> Equipe</h3>
        <div class="card-tools"><span class="badge badge-secondary">JSON</span></div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Título da seção</label>
            <input type="text" name="team_title" class="form-control" value="{{ old('team_title', $data['team_title'] ?? '') }}" placeholder="Nossa Equipe">
        </div>
        <p class="text-muted small mb-2">
            Array de objetos com os campos:
            <code>name</code>, <code>role</code>, <code>initials</code>.
        </p>
        @error('team_json')<div class="alert alert-danger py-2 small">{{ $message }}</div>@enderror
        <textarea name="team_json"
                  rows="20"
                  data-json="1"
                  class="form-control @error('team_json') is-invalid @enderror"
                  style="font-family: monospace; font-size: 12px">{{ old('team_json', json_encode($data['team'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
    </div>
</div>

{{-- Estatísticas --}}
<div id="sec-stats" class="card card-outline card-info">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> UNN em Números</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Título da seção</label>
            <input type="text" name="stats_title" class="form-control" value="{{ old('stats_title', $data['stats_title'] ?? '') }}" placeholder="UNN em Números">
        </div>
        <hr>
        @foreach ([1,2,3,4] as $i)
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Número {{ $i }}</label>
                <input type="text" name="stat_{{ $i }}_value" class="form-control" value="{{ old('stat_'.$i.'_value', $data['stat_'.$i.'_value'] ?? '') }}">
            </div>
            <div class="form-group col-md-8">
                <label>Legenda {{ $i }}</label>
                <input type="text" name="stat_{{ $i }}_label" class="form-control" value="{{ old('stat_'.$i.'_label', $data['stat_'.$i.'_label'] ?? '') }}">
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- CTA --}}
<div id="sec-cta" class="card card-outline card-success">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Título</label>
            <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $data['cta_title'] ?? '') }}" placeholder="Quer fazer parte do time?">
        </div>
        <div class="form-group">
            <label>Subtítulo</label>
            <input type="text" name="cta_subtitle" class="form-control" value="{{ old('cta_subtitle', $data['cta_subtitle'] ?? '') }}">
        </div>
        <div class="form-group mb-0">
            <label>Texto do botão</label>
            <input type="text" name="cta_btn" class="form-control" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}" placeholder="Entre em contato">
        </div>
    </div>
</div>
