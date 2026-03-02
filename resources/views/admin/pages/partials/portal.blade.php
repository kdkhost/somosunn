{{-- Portal partial - admin CMS editor --}}

{{-- ===== HERO ===== --}}
<div class="card card-outline card-secondary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-home mr-2"></i>Hero / Cabeçalho</h3></div>
    <div class="card-body">

        <div class="form-group">
            <label>Título principal <small class="text-muted">(hero_title)</small></label>
            <input type="text" name="data[hero_title]" class="form-control"
                   value="{{ $data['hero_title'] ?? '' }}"
                   placeholder="ex: Portal de Networking">
        </div>

        <div class="form-group">
            <label>Subtítulo <small class="text-muted">(hero_subtitle — suporta HTML)</small></label>
            <textarea name="data[hero_subtitle]" class="summernote-sm"
                      placeholder="Descrição do portal...">{{ $data['hero_subtitle'] ?? '' }}</textarea>
        </div>

        {{-- Hero Image --}}
        @php $heroImg = $data['hero_image'] ?? null; @endphp
        <div class="form-group">
            <label>Imagem Hero <small class="text-muted">(hero_image)</small></label>
            @if($heroImg)
                <div class="mb-2">
                    <img src="{{ asset('storage/'.$heroImg) }}" alt="Hero" class="img-thumbnail" style="max-height:160px;">
                    <div class="mt-1">
                        <input type="checkbox" name="remove_image[hero_image]" value="1" id="remove_portal_hero_image">
                        <label for="remove_portal_hero_image" class="text-danger ml-1">Remover imagem atual</label>
                    </div>
                </div>
            @endif
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="portal_hero_image"
                       name="images[hero_image]" accept="image/*"
                       data-preview="portal_hero_preview">
                <label class="custom-file-label" for="portal_hero_image">Escolher imagem…</label>
            </div>
            <img id="portal_hero_preview" src="#" alt="Preview" class="img-thumbnail mt-2 d-none" style="max-height:120px;">
        </div>

    </div>
</div>

{{-- ===== STATS ===== --}}
<div class="card card-outline card-info">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Estatísticas (4 cards)</h3></div>
    <div class="card-body">
        <div class="row">
            @for($i = 1; $i <= 4; $i++)
            <div class="col-md-6">
                <div class="card card-light mb-3">
                    <div class="card-header py-2"><h4 class="card-title text-sm">Stat {{ $i }}</h4></div>
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label class="text-xs">Valor <small class="text-muted">(stat_{{ $i }}_value)</small></label>
                            <input type="text" name="data[stat_{{ $i }}_value]" class="form-control form-control-sm"
                                   value="{{ $data['stat_'.$i.'_value'] ?? '' }}"
                                   placeholder="ex: 120+">
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-xs">Label <small class="text-muted">(stat_{{ $i }}_label)</small></label>
                            <input type="text" name="data[stat_{{ $i }}_label]" class="form-control form-control-sm"
                                   value="{{ $data['stat_'.$i.'_label'] ?? '' }}"
                                   placeholder="ex: Palestras">
                        </div>
                    </div>
                </div>
            </div>
            @endfor
        </div>
    </div>
</div>

{{-- ===== CTA FINAL ===== --}}
<div class="card card-outline card-warning">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-bullhorn mr-2"></i>CTA Final</h3></div>
    <div class="card-body">

        <div class="form-group">
            <label>Título do CTA <small class="text-muted">(cta_title)</small></label>
            <input type="text" name="data[cta_title]" class="form-control"
                   value="{{ $data['cta_title'] ?? '' }}"
                   placeholder="ex: Pronto para expandir sua rede?">
        </div>

        <div class="form-group">
            <label>Subtítulo do CTA <small class="text-muted">(cta_subtitle — suporta HTML)</small></label>
            <textarea name="data[cta_subtitle]" class="summernote-sm"
                      placeholder="Descrição do CTA...">{{ $data['cta_subtitle'] ?? '' }}</textarea>
        </div>

        <div class="form-group">
            <label>Texto do botão <small class="text-muted">(cta_btn)</small></label>
            <input type="text" name="data[cta_btn]" class="form-control"
                   value="{{ $data['cta_btn'] ?? '' }}"
                   placeholder="ex: Explorar recursos">
        </div>

    </div>
</div>
