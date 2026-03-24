{{-- Partial: manifesto --}}
{{-- $data = $page->data ?? [] --}}

<div class="tab-pane fade" id="sec-hero">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-fist-raised mr-1"></i> Hero</h3>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Título (parte 1)</label>
                    <input type="text" name="hero_title" class="form-control" value="{{ old('hero_title', $data['hero_title'] ?? '') }}">
                </div>
                <div class="form-group col-md-6">
                    <label>Título (destaque)</label>
                    <input type="text" name="hero_title_highlight" class="form-control" value="{{ old('hero_title_highlight', $data['hero_title_highlight'] ?? '') }}">
                </div>
            </div>
            <div class="form-group mb-0">
                <label>Citação inicial</label>
                <textarea name="quote_top" rows="3" class="form-control">{{ old('quote_top', $data['quote_top'] ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-sections">
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list mr-1"></i> Seções do Manifesto</h3>
        </div>
        <div class="card-body">
            @foreach ([1,2,3,4,5] as $i)
            <div class="form-group">
                <label>Seção {{ $i }} — Título</label>
                <input type="text" name="section_{{ $i }}_title" class="form-control" value="{{ old('section_'.$i.'_title', $data['section_'.$i.'_title'] ?? '') }}">
            </div>
            <div class="form-group {{ $i < 5 ? '' : 'mb-0' }}">
                <label>Seção {{ $i }} — Texto</label>
                <textarea name="section_{{ $i }}_text" rows="3" class="form-control">{{ old('section_'.$i.'_text', $data['section_'.$i.'_text'] ?? '') }}</textarea>
            </div>
            @if ($i < 5)<hr>@endif
            @endforeach
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-quote">
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-quote-right mr-1"></i> Citação Final</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Texto da citação</label>
                <textarea name="quote_bottom" rows="3" class="form-control">{{ old('quote_bottom', $data['quote_bottom'] ?? '') }}</textarea>
            </div>
            <div class="form-group mb-0">
                <label>Autor</label>
                <input type="text" name="quote_author" class="form-control" value="{{ old('quote_author', $data['quote_author'] ?? '') }}">
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-pillars">
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-columns mr-1"></i> Pilares</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Título da seção</label>
                <input type="text" name="pillars_title" class="form-control" value="{{ old('pillars_title', $data['pillars_title'] ?? '') }}">
            </div>
            <div class="form-row">
                @foreach ([1,2,3,4] as $i)
                <div class="form-group col-md-6">
                    <label>Pilar {{ $i }}</label>
                    <input type="text" name="pillar_{{ $i }}_title" class="form-control" value="{{ old('pillar_'.$i.'_title', $data['pillar_'.$i.'_title'] ?? '') }}">
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="tab-pane fade" id="sec-cta">
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Título</label>
                <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $data['cta_title'] ?? '') }}">
            </div>
            <div class="form-group mb-0">
                <label>Texto do botão</label>
                <input type="text" name="cta_btn" class="form-control" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}">
            </div>
        </div>
    </div>
</div>
