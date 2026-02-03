<?php $__env->startSection('page_title', ($template->id ? 'Editar' : 'Novo').' template de e-mail'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.mailtemplates.index')); ?>" data-pjax>Templates</a></li>
    <li class="breadcrumb-item active"><?php echo e($template->id ? 'Editar' : 'Novo'); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-body">
        <form method="POST" action="<?php echo e($template->id ? route('admin.mailtemplates.update', $template) : route('admin.mailtemplates.store')); ?>" class="ajax-form">
            <?php echo csrf_field(); ?>
            <?php if($template->id): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Nome</label>
                <input type="text" name="name" id="tpl_name" class="form-control" value="<?php echo e(old('name', $template->name)); ?>" required>
            </div>
            <div class="form-group col-md-4">
                <label>Slug</label>
                <input type="text" name="slug" id="tpl_slug" class="form-control" value="<?php echo e(old('slug', $template->slug)); ?>" <?php echo e($template->id ? 'readonly' : ''); ?> required>
            </div>
            <div class="form-group col-md-4">
                <label>Assunto</label>
                <input type="text" name="subject" class="form-control" value="<?php echo e(old('subject', $template->subject)); ?>" required>
            </div>
        </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Categoria</label>
                    <select name="category" class="form-control">
                        <?php $__currentLoopData = ['conta','financeiro','marketing','sistema']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat); ?>" <?php echo e(old('category',$template->category)==$cat?'selected':''); ?>><?php echo e(ucfirst($cat)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" <?php echo e(old('is_active', $template->is_active) ? 'selected' : ''); ?>>Ativo</option>
                        <option value="0" <?php echo e(!old('is_active', $template->is_active) ? 'selected' : ''); ?>>Inativo</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Idioma</label>
                    <input type="text" name="locale" class="form-control" value="<?php echo e(old('locale', $template->locale ?? 'pt-BR')); ?>">
                </div>
                <div class="form-group col-md-3 d-flex align-items-end">
                    <button type="button" id="btnSendTest" class="btn btn-outline-primary btn-block" data-url="<?php echo e(route('admin.mailtemplates.sendpreview', $template->id ?: 0)); ?>">Enviar teste</button>
                </div>
            </div>

        <div class="form-row">
            <div class="col-lg-8">
                <div class="form-group">
                    <label>Corpo (HTML)</label>
                    <textarea name="body" id="bodyEditor" class="form-control" rows="14"><?php echo e(old('body', $template->body)); ?></textarea>
                    <small class="text-muted">A logo da UNN será inserida automaticamente no cabeçalho.</small>
                </div>

                <div class="mb-3">
                    <label>Variáveis disponíveis (clique para inserir)</label>
                    <?php
                        $vars = [
                            ['{{user.name}}','Nome completo do usuário'],
                            ['{{user.email}}','E-mail do usuário'],
                            ['{{user.phone}}','Telefone do usuário'],
                            ['{{user.level}}','Nível / perfil do usuário'],
                            ['{{user.points}}','Pontuação atual'],
                            ['{{site.name}}','Nome do site'],
                            ['{{site.url}}','URL do site'],
                            ['{{site.support_email}}','E-mail de suporte'],
                            ['{{site.logo}}','Logo do site'],
                            ['{{order.id}}','ID do pedido'],
                            ['{{order.total}}','Total do pedido'],
                            ['{{order.status}}','Status do pedido'],
                            ['{{order.date}}','Data do pedido'],
                            ['{{payment.due_date}}','Vencimento do pagamento'],
                            ['{{payment.link}}','Link de pagamento'],
                            ['{{event.title}}','Título do evento'],
                            ['{{event.date}}','Data do evento'],
                            ['{{event.link}}','Link do evento'],
                            ['{{course.title}}','Título do curso'],
                            ['{{mentorship.title}}','Título da mentoria']
                        ];
                    ?>
                    <div class="row">
                        <?php $__currentLoopData = $vars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$v,$d]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-sm-6 col-md-4 mb-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary insert-var w-100 text-left" data-var="<?php echo e($v); ?>">
                                    <strong><?php echo e($v); ?></strong><br><small class="text-muted"><?php echo e($d); ?></small>
                                </button>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><strong>Preview em tempo real</strong></div>
                    <div class="card-body" id="tpl_preview" style="min-height:200px; background:#f8fafc;"></div>
                </div>
            </div>
        </div>

        <div class="text-right">
            <button class="btn btn-primary">Salvar</button>
            <a href="<?php echo e(route('admin.mailtemplates.index')); ?>" class="btn btn-secondary" data-pjax>Cancelar</a>
        </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
<script>
$(function(){
    $('#bodyEditor').summernote({height:420});
    $('.insert-var').on('click', function(){
        const v = $(this).data('var');
        $('#bodyEditor').summernote('pasteHTML', v);
    });
    $('#btnSendTest').on('click', function(){
        const url = $(this).data('url');
        const email = prompt('Enviar prévia para qual e-mail?');
        if(!email) return;
        $.post(url, {_token:'<?php echo e(csrf_token()); ?>', email: email}, function(){ toastr.success('Prévia enviada'); });
    });

    // auto slug (somente quando não existe)
    <?php if(!$template->id): ?>
    $('#tpl_name').on('keyup change', function(){
        if($('#tpl_slug').val().trim() !== '') return;
        const slug = $(this).val().toString()
            .normalize('NFD').replace(/[\\u0300-\\u036f]/g,'')
            .toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
        $('#tpl_slug').val(slug);
    });
    <?php endif; ?>

    function renderPreview(){
        const logo = '<?php echo e(asset('img/logo.svg')); ?>';
        const body = $('#bodyEditor').summernote('code');
        $('#tpl_preview').html(`
            <table style="width:100%;max-width:720px;margin:0 auto;font-family:Arial,Helvetica,sans-serif;background:#ffffff;border:1px solid #e5e7eb;">
                <tr><td style="background:#0f172a;padding:16px;text-align:center;">
                    <img src="${logo}" alt="UNN" style="max-height:60px;">
                </td></tr>
                <tr><td style="padding:18px;">${body}</td></tr>
            </table>
        `);
    }
    renderPreview();
    $('#bodyEditor').on('summernote.change', renderPreview);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/somosunn/public_html/resources/views/admin/mailtemplates/form.blade.php ENDPATH**/ ?>