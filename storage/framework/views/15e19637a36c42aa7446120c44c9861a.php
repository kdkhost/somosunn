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
                <div class="card">
                    <div class="card-header"><strong>Preview em tempo real</strong></div>
                    <div class="card-body p-0" id="tpl_preview" style="min-height:200px; background:#f4f6f9;"></div>
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
    $(document)
        .off('click.mailtpl', '#btnSendTest')
        .on('click.mailtpl', '#btnSendTest', function () {
            const url = $(this).data('url');
            if (!url) return;

            const fallbackEmail = <?php echo json_encode(optional(auth()->user())->email, 15, 512) ?>;
            const lastEmail = localStorage.getItem('mailtpl_test_email');
            const defaultEmail = (lastEmail || fallbackEmail || '').toString();

            Swal.fire({
                title: 'Enviar teste',
                text: 'Enviar prévia para qual e-mail?',
                input: 'email',
                inputLabel: 'E-mail',
                inputPlaceholder: 'email@dominio.com',
                inputValue: defaultEmail,
                showCancelButton: true,
                confirmButtonText: 'Enviar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: (value) => {
                    const email = (value || '').toString().trim();
                    const isValid = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(email);
                    if (!isValid) {
                        Swal.showValidationMessage('Informe um e-mail válido.');
                        return false;
                    }

                    return $.post(url, { _token: '<?php echo e(csrf_token()); ?>', email })
                        .then(
                            (data) => {
                                localStorage.setItem('mailtpl_test_email', email);
                                return data;
                            },
                            (xhr) => {
                                let msg = 'Não foi possível enviar o e-mail de teste.';
                                if (xhr && xhr.responseJSON) {
                                    msg = xhr.responseJSON.message || xhr.responseJSON.error || msg;
                                }
                                Swal.showValidationMessage(msg);
                            }
                        );
                }
            }).then((result) => {
                if (!result.isConfirmed) return;
                const message = result.value && result.value.message ? result.value.message : 'Prévia enviada com sucesso.';
                Swal.fire({ title: 'Sucesso', text: message, icon: 'success' });
            });
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
        <?php
            // Fetch dynamic settings for JS preview
            $logo = \App\Models\Setting::where('key', 'logo_admin')->value('value');
            if(!$logo) $logo = \App\Models\Setting::where('key', 'logo_front')->value('value');
            if(!$logo) $logo = \App\Models\Setting::where('key', 'logo_image')->value('value');
            $logoUrl = $logo ? asset($logo) : asset('img/logo.svg');
            
            $primaryColor = \App\Models\Setting::where('key', 'site_color_primary')->value('value') ?? '#007bff';
            $secondaryColor = \App\Models\Setting::where('key', 'site_color_secondary')->value('value') ?? '#6c757d';
        ?>

        const logo = '<?php echo e($logoUrl); ?>';
        const primaryColor = '<?php echo e($primaryColor); ?>';
        const secondaryColor = '<?php echo e($secondaryColor); ?>';
        const siteName = '<?php echo e(config('app.name')); ?>';
        const siteUrl = '<?php echo e(url('/')); ?>';
        const year = '<?php echo e(date('Y')); ?>';
        
        const body = $('#bodyEditor').summernote('code');
        
        // Use the same layout structure as the backend, but scaled for sidebar
        $('#tpl_preview').html(`
        <div style="background-color: #ffffff; width: 100%; font-family: sans-serif; box-sizing: border-box; display: flex; flex-direction: column;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%); padding: 25px 20px; text-align: center; flex-shrink: 0;">
                <img src="${logo}" alt="${siteName}" style="max-height: 50px; max-width: 100%; height: auto;">
            </div>
            
            <!-- Body -->
            <div style="padding: 25px 20px; color: #333333; line-height: 1.6; word-wrap: break-word; font-size: 14px;">
                ${body}
            </div>
            
            <!-- Footer -->
            <div style="background-color: #f8f9fa; padding: 15px; text-align: center; color: #777777; font-size: 11px; border-top: 1px solid #eeeeee; flex-shrink: 0;">
                <p style="margin: 2px 0;">&copy; ${year} ${siteName}.</p>
                <p style="margin: 2px 0;"><a href="${siteUrl}" style="color: ${primaryColor}; text-decoration: none;">Visite nosso site</a></p>
            </div>
        </div>
        `);
    }
    renderPreview();
    $('#bodyEditor').on('summernote.change', renderPreview);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\mailtemplates\form.blade.php ENDPATH**/ ?>