@extends('admin.layouts.app')

@php
    $pageLabel = (string) ($schema['label'] ?? 'Conteúdo do Site');
    $sections = (array) ($schema['sections'] ?? []);
@endphp

@section('title', $pageLabel)
@section('page_title', $pageLabel)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <strong>Editor por seções</strong>
                        <div class="text-muted small">Edite título, textos, listas, imagens e SEO sem colar HTML/CSS/JS no painel.</div>
                    </div>
                    <span class="badge badge-primary">{{ $pageLabel }}</span>
                </div>

                <form method="POST" action="{{ route('admin.cms.update', ['slug' => $slug]) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">
                        @if(count($sections) === 0)
                            <div class="alert alert-warning mb-0">
                                Nenhuma seção configurada para esta página.
                            </div>
                        @else
                            <ul class="nav nav-tabs" role="tablist">
                                @foreach($sections as $sectionKey => $section)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                            id="cms-tab-{{ $sectionKey }}"
                                            data-toggle="tab"
                                            href="#cms-section-{{ $sectionKey }}"
                                            role="tab"
                                            aria-controls="cms-section-{{ $sectionKey }}"
                                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                            {{ $section['label'] ?? $sectionKey }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content pt-4">
                                @foreach($sections as $sectionKey => $section)
                                    @php
                                        $fields = (array) ($section['fields'] ?? []);
                                    @endphp

                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                        id="cms-section-{{ $sectionKey }}"
                                        role="tabpanel"
                                        aria-labelledby="cms-tab-{{ $sectionKey }}">

                                        @if(count($fields) === 0)
                                            <div class="text-muted">Nenhum campo nesta seção.</div>
                                        @else
                                            <div class="row">
                                                @foreach($fields as $fieldKey => $def)
                                                    @php
                                                        $def = is_array($def) ? $def : ['type' => (string) $def];
                                                        $type = (string) ($def['type'] ?? 'text');
                                                        $label = (string) ($def['label'] ?? $fieldKey);
                                                        $help = (string) ($def['help'] ?? '');
                                                        $placeholder = (string) ($def['placeholder'] ?? '');
                                                        $rows = (int) ($def['rows'] ?? 3);
                                                        $height = (int) ($def['height'] ?? 280);
                                                        $rawCurrent = $contents[$fieldKey] ?? '';
                                                        $current = is_string($rawCurrent) ? $rawCurrent : '';
                                                    @endphp

                                                    <div class="col-12">
                                                        @switch($type)
                                                            @case('repeater')
                                                                @php
                                                                    $items = old($fieldKey, $repeaters[$fieldKey] ?? []);
                                                                    if (!is_array($items)) {
                                                                        $items = [];
                                                                    }
                                                                    $itemFields = (array) ($def['fields'] ?? []);
                                                                    $templateId = 'tpl_' . $fieldKey;
                                                                @endphp

                                                                <div class="form-group">
                                                                    <label class="d-flex align-items-center justify-content-between">
                                                                        <span>{{ $label }}</span>
                                                                        <button type="button"
                                                                            class="btn btn-outline-primary btn-sm js-repeater-add"
                                                                            data-template="#{{ $templateId }}">
                                                                            <i class="fas fa-plus"></i> Adicionar
                                                                        </button>
                                                                    </label>

                                                                    @if($help !== '')
                                                                        <small class="text-muted d-block mb-2">{{ $help }}</small>
                                                                    @endif

                                                                    <div class="js-repeater" data-field="{{ $fieldKey }}">
                                                                        <div class="js-repeater-items">
                                                                            @forelse($items as $idx => $item)
                                                                                @php
                                                                                    $item = is_array($item) ? $item : [];
                                                                                @endphp
                                                                                <div class="card card-body mb-2 js-repeater-item" data-index="{{ $idx }}">
                                                                                    <div class="row">
                                                                                        @foreach($itemFields as $itemKey => $itemDef)
                                                                                            @php
                                                                                                $itemDef = is_array($itemDef) ? $itemDef : ['type' => (string) $itemDef];
                                                                                                $itemType = (string) ($itemDef['type'] ?? 'text');
                                                                                                $itemLabel = (string) ($itemDef['label'] ?? $itemKey);
                                                                                                $itemRows = (int) ($itemDef['rows'] ?? 3);
                                                                                                $itemValue = old($fieldKey . '.' . $idx . '.' . $itemKey, $item[$itemKey] ?? '');
                                                                                                $col = $itemType === 'textarea' ? 'col-12' : 'col-md-6';
                                                                                            @endphp
                                                                                            <div class="{{ $col }}">
                                                                                                <div class="form-group">
                                                                                                    <label>{{ $itemLabel }}</label>
                                                                                                    @if($itemType === 'textarea')
                                                                                                        <textarea class="form-control" name="{{ $fieldKey }}[{{ $idx }}][{{ $itemKey }}]" rows="{{ $itemRows }}">{{ $itemValue }}</textarea>
                                                                                                    @elseif($itemType === 'boolean')
                                                                                                        <div class="custom-control custom-switch">
                                                                                                            <input type="checkbox"
                                                                                                                class="custom-control-input"
                                                                                                                id="{{ $fieldKey }}_{{ $idx }}_{{ $itemKey }}"
                                                                                                                name="{{ $fieldKey }}[{{ $idx }}][{{ $itemKey }}]"
                                                                                                                value="1"
                                                                                                                {{ !empty($itemValue) ? 'checked' : '' }}>
                                                                                                            <label class="custom-control-label" for="{{ $fieldKey }}_{{ $idx }}_{{ $itemKey }}">Ativar</label>
                                                                                                        </div>
                                                                                                    @else
                                                                                                        <input class="form-control"
                                                                                                            type="text"
                                                                                                            name="{{ $fieldKey }}[{{ $idx }}][{{ $itemKey }}]"
                                                                                                            value="{{ $itemValue }}">
                                                                                                    @endif
                                                                                                </div>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>

                                                                                    <div class="text-right">
                                                                                        <button type="button" class="btn btn-outline-danger btn-sm js-repeater-remove">
                                                                                            <i class="fas fa-trash"></i> Remover
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            @empty
                                                                                <div class="text-muted small mb-2">Nenhum item. Clique em “Adicionar”.</div>
                                                                            @endforelse
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <template id="{{ $templateId }}">
                                                                    <div class="card card-body mb-2 js-repeater-item" data-index="__INDEX__">
                                                                        <div class="row">
                                                                            @foreach($itemFields as $itemKey => $itemDef)
                                                                                @php
                                                                                    $itemDef = is_array($itemDef) ? $itemDef : ['type' => (string) $itemDef];
                                                                                    $itemType = (string) ($itemDef['type'] ?? 'text');
                                                                                    $itemLabel = (string) ($itemDef['label'] ?? $itemKey);
                                                                                    $itemRows = (int) ($itemDef['rows'] ?? 3);
                                                                                    $col = $itemType === 'textarea' ? 'col-12' : 'col-md-6';
                                                                                @endphp
                                                                                <div class="{{ $col }}">
                                                                                    <div class="form-group">
                                                                                        <label>{{ $itemLabel }}</label>
                                                                                        @if($itemType === 'textarea')
                                                                                            <textarea class="form-control" name="{{ $fieldKey }}[__INDEX__][{{ $itemKey }}]" rows="{{ $itemRows }}"></textarea>
                                                                                        @elseif($itemType === 'boolean')
                                                                                            <div class="custom-control custom-switch">
                                                                                                <input type="checkbox"
                                                                                                    class="custom-control-input"
                                                                                                    id="{{ $fieldKey }}___INDEX___{{ $itemKey }}"
                                                                                                    name="{{ $fieldKey }}[__INDEX__][{{ $itemKey }}]"
                                                                                                    value="1">
                                                                                                <label class="custom-control-label" for="{{ $fieldKey }}___INDEX___{{ $itemKey }}">Ativar</label>
                                                                                            </div>
                                                                                        @else
                                                                                            <input class="form-control" type="text" name="{{ $fieldKey }}[__INDEX__][{{ $itemKey }}]">
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                        <div class="text-right">
                                                                            <button type="button" class="btn btn-outline-danger btn-sm js-repeater-remove">
                                                                                <i class="fas fa-trash"></i> Remover
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                                @break

                                                            @case('image')
                                                                <div class="form-group">
                                                                    <label>{{ $label }}</label>
                                                                    <input type="file" name="{{ $fieldKey }}" class="form-control-file" accept="image/*">

                                                                    @php
                                                                        $imagePath = trim((string) $current);
                                                                        $imageUrl = '';
                                                                        if ($imagePath !== '') {
                                                                            $imageUrl = (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://'))
                                                                                ? $imagePath
                                                                                : asset('storage/' . ltrim($imagePath, '/'));
                                                                        }
                                                                    @endphp

                                                                    @if($imageUrl !== '')
                                                                        <div class="mt-2">
                                                                            <img src="{{ $imageUrl }}" alt="Imagem"
                                                                                style="max-width: 360px; border-radius: 8px;">
                                                                        </div>
                                                                        <div class="form-check mt-2">
                                                                            <input class="form-check-input" type="checkbox" name="remove_{{ $fieldKey }}"
                                                                                value="1" id="remove_{{ $fieldKey }}">
                                                                            <label class="form-check-label" for="remove_{{ $fieldKey }}">Remover imagem atual</label>
                                                                        </div>
                                                                    @endif

                                                                    @if($help !== '')
                                                                        <small class="text-muted d-block mt-2">{{ $help }}</small>
                                                                    @endif
                                                                </div>
                                                                @break

                                                            @case('html')
                                                                <div class="form-group">
                                                                    <label>{{ $label }}</label>
                                                                    <textarea name="{{ $fieldKey }}"
                                                                        class="form-control summernote"
                                                                        data-height="{{ $height }}"
                                                                        data-toolbar="full"
                                                                        data-upload-url="{{ route('admin.cms.upload') }}"
                                                                        data-cms-slug="{{ $slug }}">{{ old($fieldKey, $current) }}</textarea>
                                                                    @if($help !== '')
                                                                        <small class="text-muted d-block mt-2">{{ $help }}</small>
                                                                    @endif
                                                                </div>
                                                                @break

                                                            @case('textarea')
                                                                <div class="form-group">
                                                                    <label>{{ $label }}</label>
                                                                    <textarea name="{{ $fieldKey }}" rows="{{ $rows }}" class="form-control"
                                                                        placeholder="{{ $placeholder }}">{{ old($fieldKey, $current) }}</textarea>
                                                                    @if($help !== '')
                                                                        <small class="text-muted d-block mt-2">{{ $help }}</small>
                                                                    @endif
                                                                </div>
                                                                @break

                                                            @case('boolean')
                                                                @php
                                                                    $checked = old($fieldKey, $current) ? true : false;
                                                                @endphp
                                                                <div class="form-group">
                                                                    <label class="d-block">{{ $label }}</label>
                                                                    <div class="custom-control custom-switch">
                                                                        <input type="checkbox" class="custom-control-input" id="{{ $fieldKey }}"
                                                                            name="{{ $fieldKey }}" value="1" {{ $checked ? 'checked' : '' }}>
                                                                        <label class="custom-control-label" for="{{ $fieldKey }}">Ativar</label>
                                                                    </div>
                                                                    @if($help !== '')
                                                                        <small class="text-muted d-block mt-2">{{ $help }}</small>
                                                                    @endif
                                                                </div>
                                                                @break

                                                            @default
                                                                <div class="form-group">
                                                                    <label>{{ $label }}</label>
                                                                    <input type="text" name="{{ $fieldKey }}" class="form-control"
                                                                        value="{{ old($fieldKey, $current) }}"
                                                                        placeholder="{{ $placeholder }}">
                                                                    @if($help !== '')
                                                                        <small class="text-muted d-block mt-2">{{ $help }}</small>
                                                                    @endif
                                                                </div>
                                                        @endswitch
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            if (window.__cmsRepeaterBound) return;
            window.__cmsRepeaterBound = true;

            function nextIndex(wrapper) {
                const items = wrapper.querySelectorAll('.js-repeater-item');
                let max = -1;
                items.forEach(function (el) {
                    const raw = el.getAttribute('data-index');
                    const idx = parseInt((raw || '').toString(), 10);
                    if (!Number.isNaN(idx) && idx > max) max = idx;
                });
                return max + 1;
            }

            document.addEventListener('click', function (e) {
                const addBtn = e.target.closest('.js-repeater-add');
                if (addBtn) {
                    e.preventDefault();

                    const group = addBtn.closest('.form-group');
                    const wrapper = group ? group.querySelector('.js-repeater') : null;
                    if (!wrapper) return;

                    const templateSelector = addBtn.getAttribute('data-template') || '';
                    const template = templateSelector ? document.querySelector(templateSelector) : null;
                    if (!template) return;

                    const idx = nextIndex(wrapper);
                    const html = (template.innerHTML || '').split('__INDEX__').join(String(idx));

                    const container = wrapper.querySelector('.js-repeater-items');
                    if (!container) return;

                    const temp = document.createElement('div');
                    temp.innerHTML = html.trim();
                    const node = temp.firstElementChild;
                    if (!node) return;

                    node.setAttribute('data-index', String(idx));
                    container.appendChild(node);
                    return;
                }

                const removeBtn = e.target.closest('.js-repeater-remove');
                if (removeBtn) {
                    e.preventDefault();
                    const item = removeBtn.closest('.js-repeater-item');
                    if (item) item.remove();
                }
            });
        })();
    </script>
@endpush
