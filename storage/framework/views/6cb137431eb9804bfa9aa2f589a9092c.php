<?php
    $pageLabel = (string) ($schema['label'] ?? 'Conteúdo do Site');
    $sections = (array) ($schema['sections'] ?? []);
?>

<?php $__env->startSection('title', $pageLabel); ?>
<?php $__env->startSection('page_title', $pageLabel); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <strong>Editor por seções</strong>
                        <div class="text-muted small">Edite título, textos, listas, imagens e SEO sem colar HTML/CSS/JS no painel.</div>
                    </div>
                    <span class="badge badge-primary"><?php echo e($pageLabel); ?></span>
                </div>

                <form method="POST" action="<?php echo e(route('admin.cms.update', ['slug' => $slug])); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>

                    <div class="card-body">
                        <?php if(count($sections) === 0): ?>
                            <div class="alert alert-warning mb-0">
                                Nenhuma seção configurada para esta página.
                            </div>
                        <?php else: ?>
                            <ul class="nav nav-tabs" role="tablist">
                                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionKey => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo e($loop->first ? 'active' : ''); ?>"
                                            id="cms-tab-<?php echo e($sectionKey); ?>"
                                            data-toggle="tab"
                                            href="#cms-section-<?php echo e($sectionKey); ?>"
                                            role="tab"
                                            aria-controls="cms-section-<?php echo e($sectionKey); ?>"
                                            aria-selected="<?php echo e($loop->first ? 'true' : 'false'); ?>">
                                            <?php echo e($section['label'] ?? $sectionKey); ?>

                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>

                            <div class="tab-content pt-4">
                                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionKey => $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $fields = (array) ($section['fields'] ?? []);
                                    ?>

                                    <div class="tab-pane fade <?php echo e($loop->first ? 'show active' : ''); ?>"
                                        id="cms-section-<?php echo e($sectionKey); ?>"
                                        role="tabpanel"
                                        aria-labelledby="cms-tab-<?php echo e($sectionKey); ?>">

                                        <?php if(count($fields) === 0): ?>
                                            <div class="text-muted">Nenhum campo nesta seção.</div>
                                        <?php else: ?>
                                            <div class="row">
                                                <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fieldKey => $def): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $def = is_array($def) ? $def : ['type' => (string) $def];
                                                        $type = (string) ($def['type'] ?? 'text');
                                                        $label = (string) ($def['label'] ?? $fieldKey);
                                                        $help = (string) ($def['help'] ?? '');
                                                        $placeholder = (string) ($def['placeholder'] ?? '');
                                                        $rows = (int) ($def['rows'] ?? 3);
                                                        $height = (int) ($def['height'] ?? 280);
                                                        $rawCurrent = $contents[$fieldKey] ?? '';
                                                        $current = is_string($rawCurrent) ? $rawCurrent : '';
                                                    ?>

                                                    <div class="col-12">
                                                        <?php switch($type):
                                                            case ('repeater'): ?>
                                                                <?php
                                                                    $items = old($fieldKey, $repeaters[$fieldKey] ?? []);
                                                                    if (!is_array($items)) {
                                                                        $items = [];
                                                                    }
                                                                    $itemFields = (array) ($def['fields'] ?? []);
                                                                    $templateId = 'tpl_' . $fieldKey;
                                                                ?>

                                                                <div class="form-group">
                                                                    <label class="d-flex align-items-center justify-content-between">
                                                                        <span><?php echo e($label); ?></span>
                                                                        <button type="button"
                                                                            class="btn btn-outline-primary btn-sm js-repeater-add"
                                                                            data-template="#<?php echo e($templateId); ?>">
                                                                            <i class="fas fa-plus"></i> Adicionar
                                                                        </button>
                                                                    </label>

                                                                    <?php if($help !== ''): ?>
                                                                        <small class="text-muted d-block mb-2"><?php echo e($help); ?></small>
                                                                    <?php endif; ?>

                                                                    <div class="js-repeater" data-field="<?php echo e($fieldKey); ?>">
                                                                        <div class="js-repeater-items">
                                                                            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                                <?php
                                                                                    $item = is_array($item) ? $item : [];
                                                                                ?>
                                                                                <div class="card card-body mb-2 js-repeater-item" data-index="<?php echo e($idx); ?>">
                                                                                    <div class="row">
                                                                                        <?php $__currentLoopData = $itemFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemKey => $itemDef): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                            <?php
                                                                                                $itemDef = is_array($itemDef) ? $itemDef : ['type' => (string) $itemDef];
                                                                                                $itemType = (string) ($itemDef['type'] ?? 'text');
                                                                                                $itemLabel = (string) ($itemDef['label'] ?? $itemKey);
                                                                                                $itemRows = (int) ($itemDef['rows'] ?? 3);
                                                                                                $itemValue = old($fieldKey . '.' . $idx . '.' . $itemKey, $item[$itemKey] ?? '');
                                                                                                $col = $itemType === 'textarea' ? 'col-12' : 'col-md-6';
                                                                                            ?>
                                                                                            <div class="<?php echo e($col); ?>">
                                                                                                <div class="form-group">
                                                                                                    <label><?php echo e($itemLabel); ?></label>
                                                                                                    <?php if($itemType === 'textarea'): ?>
                                                                                                        <textarea class="form-control" name="<?php echo e($fieldKey); ?>[<?php echo e($idx); ?>][<?php echo e($itemKey); ?>]" rows="<?php echo e($itemRows); ?>"><?php echo e($itemValue); ?></textarea>
                                                                                                    <?php elseif($itemType === 'boolean'): ?>
                                                                                                        <div class="custom-control custom-switch">
                                                                                                            <input type="checkbox"
                                                                                                                class="custom-control-input"
                                                                                                                id="<?php echo e($fieldKey); ?>_<?php echo e($idx); ?>_<?php echo e($itemKey); ?>"
                                                                                                                name="<?php echo e($fieldKey); ?>[<?php echo e($idx); ?>][<?php echo e($itemKey); ?>]"
                                                                                                                value="1"
                                                                                                                <?php echo e(!empty($itemValue) ? 'checked' : ''); ?>>
                                                                                                            <label class="custom-control-label" for="<?php echo e($fieldKey); ?>_<?php echo e($idx); ?>_<?php echo e($itemKey); ?>">Ativar</label>
                                                                                                        </div>
                                                                                                    <?php else: ?>
                                                                                                        <input class="form-control"
                                                                                                            type="text"
                                                                                                            name="<?php echo e($fieldKey); ?>[<?php echo e($idx); ?>][<?php echo e($itemKey); ?>]"
                                                                                                            value="<?php echo e($itemValue); ?>">
                                                                                                    <?php endif; ?>
                                                                                                </div>
                                                                                            </div>
                                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                                    </div>

                                                                                    <div class="text-right">
                                                                                        <button type="button" class="btn btn-outline-danger btn-sm js-repeater-remove">
                                                                                            <i class="fas fa-trash"></i> Remover
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                                <div class="text-muted small mb-2">Nenhum item. Clique em “Adicionar”.</div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <template id="<?php echo e($templateId); ?>">
                                                                    <div class="card card-body mb-2 js-repeater-item" data-index="__INDEX__">
                                                                        <div class="row">
                                                                            <?php $__currentLoopData = $itemFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemKey => $itemDef): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <?php
                                                                                    $itemDef = is_array($itemDef) ? $itemDef : ['type' => (string) $itemDef];
                                                                                    $itemType = (string) ($itemDef['type'] ?? 'text');
                                                                                    $itemLabel = (string) ($itemDef['label'] ?? $itemKey);
                                                                                    $itemRows = (int) ($itemDef['rows'] ?? 3);
                                                                                    $col = $itemType === 'textarea' ? 'col-12' : 'col-md-6';
                                                                                ?>
                                                                                <div class="<?php echo e($col); ?>">
                                                                                    <div class="form-group">
                                                                                        <label><?php echo e($itemLabel); ?></label>
                                                                                        <?php if($itemType === 'textarea'): ?>
                                                                                            <textarea class="form-control" name="<?php echo e($fieldKey); ?>[__INDEX__][<?php echo e($itemKey); ?>]" rows="<?php echo e($itemRows); ?>"></textarea>
                                                                                        <?php elseif($itemType === 'boolean'): ?>
                                                                                            <div class="custom-control custom-switch">
                                                                                                <input type="checkbox"
                                                                                                    class="custom-control-input"
                                                                                                    id="<?php echo e($fieldKey); ?>___INDEX___<?php echo e($itemKey); ?>"
                                                                                                    name="<?php echo e($fieldKey); ?>[__INDEX__][<?php echo e($itemKey); ?>]"
                                                                                                    value="1">
                                                                                                <label class="custom-control-label" for="<?php echo e($fieldKey); ?>___INDEX___<?php echo e($itemKey); ?>">Ativar</label>
                                                                                            </div>
                                                                                        <?php else: ?>
                                                                                            <input class="form-control" type="text" name="<?php echo e($fieldKey); ?>[__INDEX__][<?php echo e($itemKey); ?>]">
                                                                                        <?php endif; ?>
                                                                                    </div>
                                                                                </div>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        </div>
                                                                        <div class="text-right">
                                                                            <button type="button" class="btn btn-outline-danger btn-sm js-repeater-remove">
                                                                                <i class="fas fa-trash"></i> Remover
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                                <?php break; ?>

                                                            <?php case ('image'): ?>
                                                                <div class="form-group">
                                                                    <label><?php echo e($label); ?></label>
                                                                    <input type="file" name="<?php echo e($fieldKey); ?>" class="form-control-file" accept="image/*">

                                                                    <?php
                                                                        $imagePath = trim((string) $current);
                                                                        $imageUrl = '';
                                                                        if ($imagePath !== '') {
                                                                            $imageUrl = (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://'))
                                                                                ? $imagePath
                                                                                : asset('storage/' . ltrim($imagePath, '/'));
                                                                        }
                                                                    ?>

                                                                    <?php if($imageUrl !== ''): ?>
                                                                        <div class="mt-2">
                                                                            <img src="<?php echo e($imageUrl); ?>" alt="Imagem"
                                                                                style="max-width: 360px; border-radius: 8px;">
                                                                        </div>
                                                                        <div class="form-check mt-2">
                                                                            <input class="form-check-input" type="checkbox" name="remove_<?php echo e($fieldKey); ?>"
                                                                                value="1" id="remove_<?php echo e($fieldKey); ?>">
                                                                            <label class="form-check-label" for="remove_<?php echo e($fieldKey); ?>">Remover imagem atual</label>
                                                                        </div>
                                                                    <?php endif; ?>

                                                                    <?php if($help !== ''): ?>
                                                                        <small class="text-muted d-block mt-2"><?php echo e($help); ?></small>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <?php break; ?>

                                                            <?php case ('html'): ?>
                                                                <div class="form-group">
                                                                    <label><?php echo e($label); ?></label>
                                                                    <textarea name="<?php echo e($fieldKey); ?>"
                                                                        class="form-control summernote"
                                                                        data-height="<?php echo e($height); ?>"
                                                                        data-toolbar="full"
                                                                        data-upload-url="<?php echo e(route('admin.cms.upload')); ?>"
                                                                        data-cms-slug="<?php echo e($slug); ?>"><?php echo e(old($fieldKey, $current)); ?></textarea>
                                                                    <?php if($help !== ''): ?>
                                                                        <small class="text-muted d-block mt-2"><?php echo e($help); ?></small>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <?php break; ?>

                                                            <?php case ('textarea'): ?>
                                                                <div class="form-group">
                                                                    <label><?php echo e($label); ?></label>
                                                                    <textarea name="<?php echo e($fieldKey); ?>" rows="<?php echo e($rows); ?>" class="form-control"
                                                                        placeholder="<?php echo e($placeholder); ?>"><?php echo e(old($fieldKey, $current)); ?></textarea>
                                                                    <?php if($help !== ''): ?>
                                                                        <small class="text-muted d-block mt-2"><?php echo e($help); ?></small>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <?php break; ?>

                                                            <?php case ('boolean'): ?>
                                                                <?php
                                                                    $checked = old($fieldKey, $current) ? true : false;
                                                                ?>
                                                                <div class="form-group">
                                                                    <label class="d-block"><?php echo e($label); ?></label>
                                                                    <div class="custom-control custom-switch">
                                                                        <input type="checkbox" class="custom-control-input" id="<?php echo e($fieldKey); ?>"
                                                                            name="<?php echo e($fieldKey); ?>" value="1" <?php echo e($checked ? 'checked' : ''); ?>>
                                                                        <label class="custom-control-label" for="<?php echo e($fieldKey); ?>">Ativar</label>
                                                                    </div>
                                                                    <?php if($help !== ''): ?>
                                                                        <small class="text-muted d-block mt-2"><?php echo e($help); ?></small>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <?php break; ?>

                                                            <?php default: ?>
                                                                <div class="form-group">
                                                                    <label><?php echo e($label); ?></label>
                                                                    <input type="text" name="<?php echo e($fieldKey); ?>" class="form-control"
                                                                        value="<?php echo e(old($fieldKey, $current)); ?>"
                                                                        placeholder="<?php echo e($placeholder); ?>">
                                                                    <?php if($help !== ''): ?>
                                                                        <small class="text-muted d-block mt-2"><?php echo e($help); ?></small>
                                                                    <?php endif; ?>
                                                                </div>
                                                        <?php endswitch; ?>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\cms\index.blade.php ENDPATH**/ ?>