<?php
    $is = fn($patterns) => request()->routeIs($patterns) ? 'active' : '';
    $open = fn($patterns) => request()->routeIs($patterns) ? 'menu-open' : '';
?>
<?php
    $brandLogo = asset('img/logo.svg');
    $brandFavicon = file_exists(public_path('favicon.ico')) ? asset('favicon.ico') : $brandLogo;
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?php echo e(route('admin.dashboard')); ?>" class="brand-link d-flex flex-column align-items-center justify-content-center py-2" style="height:60px;">
        <img src="<?php echo e($brandLogo); ?>" alt="UNN" class="brand-logo-img" style="max-width:160px; max-height:40px; width:auto; height:auto; display:block;">
        <img src="<?php echo e($brandFavicon); ?>" alt="UNN" class="brand-favicon-img d-none" style="max-width:32px; max-height:32px; width:auto; height:auto; display:block;">
    </a>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" data-accordion="true" id="sidebar-tree" role="menu">
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link <?php echo e($is('admin.dashboard')); ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item has-treeview <?php echo e($open('admin.courses.*')); ?>">
                    <a href="#" class="nav-link <?php echo e($is('admin.courses.*')); ?>">
                        <i class="nav-icon fas fa-graduation-cap"></i>
                        <p>Cursos<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="<?php echo e(route('admin.courses.index')); ?>" class="nav-link <?php echo e($is('admin.courses.index')); ?>"><i class="fas fa-list nav-icon"></i><p>Listar</p></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('admin.courses.create')); ?>" class="nav-link <?php echo e($is('admin.courses.create')); ?>"><i class="fas fa-plus nav-icon"></i><p>Novo</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview <?php echo e($open('admin.users.*')); ?>">
                    <a href="#" class="nav-link <?php echo e($is('admin.users.*')); ?>">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Usuários<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="<?php echo e(route('admin.users.index')); ?>" class="nav-link <?php echo e($is('admin.users.index')); ?>"><i class="fas fa-list nav-icon"></i><p>Listar</p></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('admin.users.create')); ?>" class="nav-link <?php echo e($is('admin.users.create')); ?>"><i class="fas fa-plus nav-icon"></i><p>Novo</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview <?php echo e($open('admin.events.*')); ?>">
                    <a href="#" class="nav-link <?php echo e($is('admin.events.*')); ?>">
                        <i class="nav-icon fas fa-calendar"></i>
                        <p>Eventos<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="<?php echo e(route('admin.events.index')); ?>" class="nav-link <?php echo e($is('admin.events.index')); ?>"><i class="fas fa-list nav-icon"></i><p>Listar</p></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('admin.events.create')); ?>" class="nav-link <?php echo e($is('admin.events.create')); ?>"><i class="fas fa-plus nav-icon"></i><p>Novo</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview <?php echo e($open('admin.mentorships.*')); ?>">
                    <a href="#" class="nav-link <?php echo e($is('admin.mentorships.*')); ?>">
                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                        <p>Mentorias<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="<?php echo e(route('admin.mentorships.index')); ?>" class="nav-link <?php echo e($is('admin.mentorships.index')); ?>"><i class="fas fa-list nav-icon"></i><p>Listar</p></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('admin.mentorships.create')); ?>" class="nav-link <?php echo e($is('admin.mentorships.create')); ?>"><i class="fas fa-plus nav-icon"></i><p>Novo</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview <?php echo e($open('admin.plans.*')); ?>">
                    <a href="#" class="nav-link <?php echo e($is('admin.plans.*')); ?>">
                        <i class="nav-icon fas fa-tags"></i>
                        <p>Planos<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="<?php echo e(route('admin.plans.index')); ?>" class="nav-link <?php echo e($is('admin.plans.index')); ?>"><i class="fas fa-list nav-icon"></i><p>Listar</p></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('admin.plans.create')); ?>" class="nav-link <?php echo e($is('admin.plans.create')); ?>"><i class="fas fa-plus nav-icon"></i><p>Novo</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview <?php echo e($open('admin.certificates.*')); ?>">
                    <a href="#" class="nav-link <?php echo e($is('admin.certificates.*')); ?>">
                        <i class="nav-icon fas fa-certificate"></i>
                        <p>Certificados<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="<?php echo e(route('admin.certificates.create')); ?>" class="nav-link <?php echo e($is('admin.certificates.create')); ?>"><i class="fas fa-file-signature nav-icon"></i><p>Gerar</p></a></li>
                    </ul>
                </li>

                <li class="nav-item"><a href="<?php echo e(route('admin.upload.test')); ?>" class="nav-link <?php echo e($is('admin.upload.test')); ?>"><i class="nav-icon fas fa-upload"></i><p>Upload</p></a></li>

                <li class="nav-item has-treeview <?php echo e($open(['admin.mailtemplates.*','admin.mailtest'])); ?>">
                    <a href="#" class="nav-link <?php echo e($is(['admin.mailtemplates.*','admin.mailtest'])); ?>">
                        <i class="nav-icon fas fa-envelope"></i>
                        <p>E-mails<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="<?php echo e(route('admin.mailtemplates.index')); ?>" class="nav-link <?php echo e($is('admin.mailtemplates.index')); ?>"><i class="fas fa-table nav-icon"></i><p>Templates</p></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('admin.mailtest')); ?>" class="nav-link <?php echo e($is('admin.mailtest')); ?>"><i class="fas fa-paper-plane nav-icon"></i><p>Enviar teste</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview <?php echo e($open(['admin.points-rules.*','admin.ranking'])); ?>">
                    <a href="#" class="nav-link <?php echo e($is(['admin.points-rules.*','admin.ranking'])); ?>">
                        <i class="nav-icon fas fa-star"></i>
                        <p>Pontuação<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="<?php echo e(route('admin.points-rules.index')); ?>" class="nav-link <?php echo e($is('admin.points-rules.index')); ?>"><i class="fas fa-sliders-h nav-icon"></i><p>Regras</p></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('admin.ranking')); ?>" class="nav-link <?php echo e($is('admin.ranking')); ?>"><i class="fas fa-trophy nav-icon"></i><p>Ranking</p></a></li>
                    </ul>
                </li>

                <li class="nav-item has-treeview <?php echo e($open('admin.permissions.*')); ?>">
                    <a href="#" class="nav-link <?php echo e($is('admin.permissions.*')); ?>">
                        <i class="nav-icon fas fa-user-shield"></i>
                        <p>Permissões<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-4">
                        <li class="nav-item"><a href="<?php echo e(route('admin.permissions.index')); ?>" class="nav-link <?php echo e($is('admin.permissions.index')); ?>"><i class="fas fa-list nav-icon"></i><p>Listar</p></a></li>
                        <li class="nav-item"><a href="<?php echo e(route('admin.permissions.create')); ?>" class="nav-link <?php echo e($is('admin.permissions.create')); ?>"><i class="fas fa-plus nav-icon"></i><p>Novo</p></a></li>
                    </ul>
                </li>

                <li class="nav-item"><a href="<?php echo e(route('admin.settings')); ?>" class="nav-link <?php echo e($is('admin.settings')); ?>"><i class="nav-icon fas fa-cogs"></i><p>Configurações</p></a></li>
            </ul>
        </nav>
    </div>
</aside>
<?php /**PATH /home/somosunn/public_html/resources/views/admin/partials/sidebar.blade.php ENDPATH**/ ?>