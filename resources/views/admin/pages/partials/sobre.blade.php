{{-- Partial: sobre --}}
{{-- $data = $page->data ?? [] --}}

{{-- Hero --}}
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-heading mr-1"></i> Hero</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Título principal</label>
            <input type="text" name="hero_title" class="form-control" value="{{ old('hero_title', $data['hero_title'] ?? '') }}" placeholder="Somos a ponte entre quem quer crescer e quem já chegou lá.">
        </div>
        <div class="form-group">
            <label>Missão / Visão <small class="text-muted">(parágrafo de abertura)</small></label>
            <textarea name="vision" rows="4" class="form-control" placeholder="Texto descritivo da missão da UNN.">{{ old('vision', $data['vision'] ?? '') }}</textarea>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Botão principal</label>
                <input type="text" name="cta_btn_primary" class="form-control" value="{{ old('cta_btn_primary', $data['cta_btn_primary'] ?? '') }}" placeholder="Fazer parte">
            </div>
            <div class="form-group col-md-6">
                <label>Botão secundário</label>
                <input type="text" name="cta_btn_secondary" class="form-control" value="{{ old('cta_btn_secondary', $data['cta_btn_secondary'] ?? '') }}" placeholder="Conhecer a equipe">
            </div>
        </div>
    </div>
</div>

{{-- Estatísticas --}}
<div class="card card-outline card-info">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Estatísticas (4 números)</h3></div>
    <div class="card-body">
        @foreach ([1,2,3,4] as $i)
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Número {{ $i }}</label>
                <input type="text" name="stat_{{ $i }}_value" class="form-control" value="{{ old('stat_'.$i.'_value', $data['stat_'.$i.'_value'] ?? '') }}" placeholder="5.000+">
            </div>
            <div class="form-group col-md-8">
                <label>Legenda {{ $i }}</label>
                <input type="text" name="stat_{{ $i }}_label" class="form-control" value="{{ old('stat_'.$i.'_label', $data['stat_'.$i.'_label'] ?? '') }}" placeholder="Membros ativos">
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- História --}}
<div class="card card-outline card-secondary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-book-open mr-1"></i> Nossa História</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Título da seção</label>
            <input type="text" name="history_title" class="form-control" value="{{ old('history_title', $data['history_title'] ?? '') }}" placeholder="Nossa História">
        </div>
        <div class="form-group">
            <label>Lead <small class="text-muted">(1ª frase de destaque)</small></label>
            <textarea name="history_lead" rows="2" class="form-control">{{ old('history_lead', $data['history_lead'] ?? '') }}</textarea>
        </div>
        <div class="form-group">
            <label>Parágrafo 1</label>
            <textarea name="history_p1" rows="4" class="form-control">{{ old('history_p1', $data['history_p1'] ?? '') }}</textarea>
        </div>
        <div class="form-group mb-0">
            <label>Parágrafo 2</label>
            <textarea name="history_p2" rows="4" class="form-control">{{ old('history_p2', $data['history_p2'] ?? '') }}</textarea>
        </div>
    </div>
</div>

{{-- Diferenciais --}}
<div class="card card-outline card-secondary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-star mr-1"></i> Diferenciais</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Título da seção</label>
            <input type="text" name="diff_title" class="form-control" value="{{ old('diff_title', $data['diff_title'] ?? '') }}" placeholder="Por que a UNN é diferente">
        </div>
        <hr>
        @foreach ([1,2,3] as $i)
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Card {{ $i }} — Título</label>
                <input type="text" name="diff_card_{{ $i }}_title" class="form-control" value="{{ old('diff_card_'.$i.'_title', $data['diff_card_'.$i.'_title'] ?? '') }}">
            </div>
            <div class="form-group col-md-8">
                <label>Card {{ $i }} — Texto</label>
                <textarea name="diff_card_{{ $i }}_text" rows="2" class="form-control">{{ old('diff_card_'.$i.'_text', $data['diff_card_'.$i.'_text'] ?? '') }}</textarea>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- CTA --}}
<div class="card card-outline card-success">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3></div>
    <div class="card-body">
        <div class="form-group">
            <label>Título</label>
            <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $data['cta_title'] ?? '') }}" placeholder="Faça parte da nossa história">
        </div>
        <div class="form-group">
            <label>Subtítulo</label>
            <input type="text" name="cta_subtitle" class="form-control" value="{{ old('cta_subtitle', $data['cta_subtitle'] ?? '') }}">
        </div>
        <div class="form-group mb-0">
            <label>Texto do botão</label>
            <input type="text" name="cta_btn" class="form-control" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}" placeholder="Quero fazer parte">
        </div>
    </div>
</div>
