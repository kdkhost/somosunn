{{-- Partial: como-funciona --}}
{{-- $data = $page->data ?? [] --}}

{{-- Intro --}}
<div id="sec-header" class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cogs mr-1"></i> Cabeçalho</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-header" data-section="header"
                    {{ ($data['header_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-header">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group mb-0">
            <label>Subtítulo do hero</label>
            <input type="text" name="hero_subtitle" class="form-control" value="{{ old('hero_subtitle', $data['hero_subtitle'] ?? '') }}" placeholder="Entenda como a UNN pode transformar sua rede de contatos…">
        </div>
    </div>
</div>

{{-- Steps (array JSON) --}}
<div id="sec-steps" class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list-ol mr-1"></i> Passos (4 etapas)</h3>
        <div class="card-tools d-flex align-items-center">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success mr-3">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-steps" data-section="steps"
                    {{ ($data['steps_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-steps">Exibir no site</label>
            </div>
            <span class="badge badge-secondary">JSON</span>
        </div>
    </div>
        <div id="steps-repeater-container"></div>
        <button type="button" id="add-step-btn" class="btn btn-outline-primary btn-block mt-3">
            <i class="fas fa-plus mr-1"></i> Adicionar Passo
        </button>
        <textarea name="steps_json" class="d-none">{{ json_encode($data['steps'] ?? []) }}</textarea>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.initJSONRepeater({
        containerId: 'steps-repeater-container',
        inputId: 'steps_json',
        addButtonId: 'add-step-btn',
        itemSchema: { direction: 'row', title: '', text: '', li: ['', '', ''] },
        initialData: {!! json_encode($data['steps'] ?? []) !!},
        template: (item, index) => `
            <div class="row">
                <div class="col-md-6 border-right">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Direção da Seção</label>
                        <select name="step[direction]" class="form-control form-control-sm">
                            <option value="row" ${item.direction === 'row' ? 'selected' : ''}>Normal (Imagem Direita)</option>
                            <option value="row-reverse" ${item.direction === 'row-reverse' ? 'selected' : ''}>Invertido (Imagem Esquerda)</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Título do Passo</label>
                        <input type="text" name="step[title]" value="${item.title || ''}" class="form-control form-control-sm">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Texto Descritivo</label>
                        <textarea name="step[text]" rows="3" class="form-control form-control-sm">${item.text || ''}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="small font-weight-bold">Tópicos / Benefícios (3 itens)</label>
                    ${[0,1,2].map(i => `
                        <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-check text-success"></i></span>
                            </div>
                            <input type="text" name="step[li.${i}]" value="${(item.li && item.li[i]) || ''}" 
                                class="form-control" placeholder="Item ${i+1}"
                                oninput="this.closest('.repeater-item').querySelector('[name=\\'step[li.${i}]\\']').dispatchEvent(new Event('change'))">
                        </div>
                    `).join('')}
                    <small class="text-muted italic">Os tópicos aparecem como lista abaixo do texto.</small>
                </div>
            </div>
        `
    });

    // Listener para o array aninhado 'li'
    document.getElementById('steps-repeater-container').addEventListener('input', function(e) {
        if (e.target.name && e.target.name.startsWith('step[li.')) {
            const repeaterItem = e.target.closest('.repeater-item');
            const index = Array.from(repeaterItem.parentNode.children).indexOf(repeaterItem);
            const liIndex = e.target.name.split('.')[1].replace(']', '');
            
            const stepsJsonInput = document.getElementsByName('steps_json')[0];
            const stepsJson = JSON.parse(stepsJsonInput.value);
            
            if (!stepsJson[index].li) stepsJson[index].li = ['', '', ''];
            stepsJson[index].li[liIndex] = e.target.value;
            
            stepsJsonInput.value = JSON.stringify(stepsJson);
        }
    });
});
</script>
@endpush

{{-- Planos --}}
<div id="sec-plans" class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tags mr-1"></i> Seção Planos</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-plans" data-section="plans"
                    {{ ($data['plans_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-plans">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Título</label>
            <input type="text" name="plans_title" class="form-control" value="{{ old('plans_title', $data['plans_title'] ?? '') }}" placeholder="Escolha seu Plano">
        </div>
        <div class="form-group mb-0">
            <label>Subtítulo</label>
            <input type="text" name="plans_subtitle" class="form-control" value="{{ old('plans_subtitle', $data['plans_subtitle'] ?? '') }}" placeholder="Temos opções para todos os estágios…">
        </div>
    </div>
</div>

{{-- CTA --}}
<div id="sec-cta" class="card card-outline card-success">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bullhorn mr-1"></i> CTA Final</h3>
        <div class="card-tools">
            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                <input type="checkbox" class="custom-control-input section-toggle" id="toggle-cta" data-section="cta"
                    {{ ($data['cta_enabled'] ?? true) ? 'checked' : '' }}>
                <label class="custom-control-label" for="toggle-cta">Exibir no site</label>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label>Título</label>
            <input type="text" name="cta_title" class="form-control" value="{{ old('cta_title', $data['cta_title'] ?? '') }}" placeholder="Pronto para começar?">
        </div>
        <div class="form-group">
            <label>Subtítulo</label>
            <input type="text" name="cta_subtitle" class="form-control" value="{{ old('cta_subtitle', $data['cta_subtitle'] ?? '') }}">
        </div>
        <div class="form-group mb-0">
            <label>Texto do botão</label>
            <input type="text" name="cta_btn" class="form-control" value="{{ old('cta_btn', $data['cta_btn'] ?? '') }}" placeholder="Criar conta grátis">
        </div>
    </div>
</div>
