

<?php $__env->startSection('page_title', 'Gerenciar Certificados'); ?>
<?php $__env->startSection('breadcrumb_items'); ?>
    <li class="breadcrumb-item active">Certificados</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card card-primary card-outline card-tabs">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs" id="certificate-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tabs-issued-tab" data-toggle="pill" href="#tabs-issued" role="tab"
                        aria-controls="tabs-issued" aria-selected="true">
                        <i class="fas fa-check-circle mr-1"></i> Emitidos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tabs-pending-tab" data-toggle="pill" href="#tabs-pending" role="tab"
                        aria-controls="tabs-pending" aria-selected="false">
                        <i class="fas fa-clock mr-1"></i> Pendentes
                        <?php if($pendingEnrollments->count() > 0): ?>
                            <span class="badge badge-warning ml-1"><?php echo e($pendingEnrollments->count()); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="certificate-tabs-content">
                <!-- TAB EMITIDOS -->
                <div class="tab-pane fade show active" id="tabs-issued" role="tabpanel" aria-labelledby="tabs-issued-tab">
                    <table id="table_issued" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Aluno</th>
                                <th>Tipo</th>
                                <th>Produto</th>
                                <th>Hash</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $issuedCertificates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $product = $cert->course ?? $cert->mentorship ?? $cert->event;
                                    $type = $cert->course ? 'Curso' : ($cert->mentorship ? 'Mentoria' : 'Evento');
                                ?>
                                <tr>
                                    <td data-sort="<?php echo e($cert->issued_at->format('YmdHis')); ?>">
                                        <?php echo e($cert->issued_at->format('d/m/Y H:i')); ?>

                                    </td>
                                    <td><?php echo e($cert->user->name); ?></td>
                                    <td><span class="badge badge-info"><?php echo e($type); ?></span></td>
                                    <td><?php echo e($product->title ?? 'N/A'); ?></td>
                                    <td><small><?php echo e($cert->cert_hash); ?></small></td>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-default btn-view-cert"
                                            data-hash="<?php echo e($cert->cert_hash); ?>"
                                            data-download="<?php echo e(route('admin.certificates.view', $cert->cert_hash, false)); ?>?download=1"
                                            title="Visualizar">
                                            <i class="fas fa-eye text-primary"></i>
                                        </button>
                                        <form action="<?php echo e(route('admin.certificates.regenerate', $cert->id)); ?>" method="POST"
                                            class="d-inline form-regenerate-cert">
                                            <?php echo csrf_field(); ?>
                                            <button type="button" class="btn btn-xs btn-default btn-regenerate"
                                                title="Regenerar PDF (Aplicar novo design)">
                                                <i class="fas fa-sync text-warning"></i>
                                            </button>
                                        </form>
                                        <a href="<?php echo e(route('admin.certificates.view', $cert->cert_hash)); ?>?download=1"
                                            class="btn btn-xs btn-default" title="Baixar">
                                            <i class="fas fa-download text-danger"></i>
                                        </a>
                                        <form action="<?php echo e(route('admin.certificates.send', $cert->id)); ?>" method="POST"
                                            class="d-inline form-send-email">
                                            <?php echo csrf_field(); ?>
                                            <button type="button" class="btn btn-xs btn-primary btn-send-email"
                                                data-email="<?php echo e($cert->user->email); ?>" title="Enviar por Email">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        </form>
                                        <?php if(auth()->user()->isAdmin()): ?>
                                            <form action="<?php echo e(route('admin.certificates.destroy', $cert->id)); ?>" method="POST"
                                                class="d-inline form-delete-cert">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="button" class="btn btn-xs btn-danger btn-delete-cert"
                                                    title="Excluir (Permite emitir novamente)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- TAB PENDENTES -->
                <div class="tab-pane fade" id="tabs-pending" role="tabpanel" aria-labelledby="tabs-pending-tab">
                    <?php if($pendingEnrollments->isEmpty()): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check mr-2"></i> Todos os alunos que concluíram cursos/mentorias/eventos já possuem
                            certificados emitidos.
                        </div>
                    <?php else: ?>
                        <table id="table_pending" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Conclusão</th>
                                    <th>Aluno</th>
                                    <th>Tipo</th>
                                    <th>Produto</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $pendingEnrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $type = class_basename($enrollment->enrollable_type);
                                        $typeLabel = $type === 'Course' ? 'Curso' : ($type === 'Mentorship' ? 'Mentoria' : 'Evento');
                                    ?>
                                    <tr>
                                        <td><?php echo e($enrollment->completed_at ? $enrollment->completed_at->format('d/m/Y') : '-'); ?></td>
                                        <td><?php echo e($enrollment->user->name); ?></td>
                                        <td><span class="badge badge-secondary"><?php echo e($typeLabel); ?></span></td>
                                        <td><?php echo e($enrollment->enrollable->title); ?></td>
                                        <td>
                                            <form action="<?php echo e(route('admin.certificates.generate')); ?>" method="POST"
                                                class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="user_id" value="<?php echo e($enrollment->user_id); ?>">

                                                <?php if($type === 'Course'): ?>
                                                    <input type="hidden" name="course_id" value="<?php echo e($enrollment->enrollable_id); ?>">
                                                <?php elseif($type === 'Mentorship'): ?>
                                                    <input type="hidden" name="mentorship_id" value="<?php echo e($enrollment->enrollable_id); ?>">
                                                <?php elseif($type === 'Event'): ?>
                                                    <input type="hidden" name="event_id" value="<?php echo e($enrollment->enrollable_id); ?>">
                                                <?php endif; ?>

                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-certificate mr-1"></i> Emitir Certificado
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Certificate Preview Modal -->
    <div class="modal fade" id="certificateModal" tabindex="-1" role="dialog" aria-labelledby="certificateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 95vw;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="certificateModalLabel">
                        <i class="fas fa-certificate mr-2"></i> Visualizar Certificado
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0"
                    style="background: #f5f5f5; height: 80vh; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                    <div id="certPreviewContainer"
                        style="width: 100%; padding: 20px; display: flex; justify-content: center; align-items: center;">
                        <!-- Certificate HTML will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <a id="btnDownloadCert" href="#" class="btn btn-primary" target="_blank">
                        <i class="fas fa-download mr-1"></i> Baixar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <!-- DataTables & Plugins -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <script>
        $(function () {
            // Common settings
            var dtSettings = {
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json" },
                "pageLength": 10
            };

            // Issued Table
            $("#table_issued").DataTable(Object.assign({}, dtSettings, {
                "order": [[0, "desc"]],
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            })).buttons().container().appendTo('#table_issued_wrapper .col-md-6:eq(0)');

            // Pending Table
            $("#table_pending").DataTable(Object.assign({}, dtSettings, {
                "order": [[0, "desc"]] // Sort by completion date newest
            }));

            // Modal Logic - Load HTML Preview
            // Modal Logic - Load PDF Preview
            $('.btn-view-cert').on('click', function () {
                var hash = $(this).data('hash');
                var downloadUrl = $(this).data('download');

                // Use the view route which streams PDF inline
                // Force regenerate to ensure styling changes (landscape) applied
                var pdfUrl = "<?php echo e(route('admin.certificates.view', ':hash')); ?>".replace(':hash', hash) + "?regenerate=true";

                $('#btnDownloadCert').attr('href', downloadUrl);

                // Set iframe to container
                $('#certPreviewContainer').html('<iframe src="' + pdfUrl + '" style="width: 100%; height: 100%; border: none;"></iframe>');

                // Open the correct modal ID
                $('#certificateModal').modal('show');
            });

            // Clear preview on close
            $('#modalViewCert').on('hidden.bs.modal', function () {
                $('#certPreviewContainer').html('');
            });

            // SweetAlert2 for Email Sending
            $('.btn-send-email').on('click', function (e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var email = $(this).data('email');

                Swal.fire({
                    title: 'Enviar Certificado?',
                    text: "O certificado será enviado por e-mail para " + email,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Sim, enviar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // SweetAlert2 for Regenerate
            $('.btn-regenerate').on('click', function (e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: 'Regenerar PDF?',
                    text: "Isso atualizará o arquivo PDF com as configurações atuais do curso (fundo, assinatura, locais). O arquivo antigo será substituído.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Sim, regenerar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // SweetAlert2 for Delete
            $('.btn-delete-cert').on('click', function (e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: 'Excluir Certificado?',
                    text: "Ao excluir, o registro será removido permanentemente e o aluno voltará para a lista de PENDENTES. Você poderá emitir um novo certificado para ele com o design atualizado.",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\certificates\index.blade.php ENDPATH**/ ?>