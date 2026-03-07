{{-- Premium partial - admin CMS editor --}}

{{-- ===== HERO ===== --}}
<div class="card card-outline card-danger">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-crown mr-2"></i>Hero / Cabeçalho</h3></div>
    <div class="card-body">

        <div class="form-group">
            <label>Badge do hero <small class="text-muted">(hero_badge)</small></label>
            <input type="text" name="data[hero_badge]" class="form-control"
                   value="{{ $data['hero_badge'] ?? '' }}"
                   placeholder="ex: Associação Premium">
        </div>

        <div class="form-group">
            <label>Título principal <small class="text-muted">(hero_title)</small></label>
            <input type="text" name="data[hero_title]" class="form-control"
                   value="{{ $data['hero_title'] ?? '' }}"
                   placeholder="ex: Invista no seu crescimento">
        </div>

        <div class="form-group">
            <label>Subtítulo <small class="text-muted">(hero_subtitle — suporta HTML)</small></label>
            <textarea name="data[hero_subtitle]" class="summernote-sm"
                      placeholder="Descrição principal...">{{ $data['hero_subtitle'] ?? '' }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Selo de confiança 1 <small class="text-muted">(hero_trust_1)</small></label>
                    <input type="text" name="data[hero_trust_1]" class="form-control"
                           value="{{ $data['hero_trust_1'] ?? '' }}"
                           placeholder="ex: Sem fidelidade">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Selo de confiança 2 <small class="text-muted">(hero_trust_2)</small></label>
                    <input type="text" name="data[hero_trust_2]" class="form-control"
                           value="{{ $data['hero_trust_2'] ?? '' }}"
                           placeholder="ex: Cancele quando quiser">
                </div>
            </div>
        </div>

        {{-- Hero Image --}}
        @php $heroImg = $data['hero_image'] ?? null; @endphp
        <div class="form-group">
            <label>Imagem Hero <small class="text-muted">(hero_image)</small></label>
            @include('admin.components.upload-global', ['name'=>'images[hero_image]', 'accept'=>'image/*'])
            @if($heroImg)
                <div class="mb-2">
                    <img src="{{ asset('storage/'.$heroImg) }}" alt="Hero" class="img-thumbnail" style="max-height:160px;">
                    <div class="mt-1">
                        <input type="checkbox" name="remove_image[hero_image]" value="1" id="remove_premium_hero_image">
                        <label for="remove_premium_hero_image" class="text-danger ml-1">Remover imagem atual</label>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

{{-- ===== SEÇÃO DE PLANOS ===== --}}
<div class="card card-outline card-warning">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-tags mr-2"></i>Seção de Planos</h3></div>
    <div class="card-body">

        <div class="form-group">
            <label>Título da seção de planos <small class="text-muted">(plans_title)</small></label>
            <input type="text" name="data[plans_title]" class="form-control"
                   value="{{ $data['plans_title'] ?? '' }}"
                   placeholder="ex: Escolha seu plano">
        </div>

        <div class="form-group">
            <label>Subtítulo da seção de planos <small class="text-muted">(plans_subtitle)</small></label>
            <input type="text" name="data[plans_subtitle]" class="form-control"
                   value="{{ $data['plans_subtitle'] ?? '' }}"
                   placeholder="ex: Sem taxa de adesão. Cancele quando quiser.">
        </div>

    </div>
</div>
