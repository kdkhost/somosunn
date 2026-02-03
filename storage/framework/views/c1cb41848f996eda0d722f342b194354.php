
<nav class="main-header navbar navbar-expand navbar-dark sidebar-dark-primary">
    <ul class="navbar-nav mr-auto align-items-center">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?php echo e(route('home')); ?>" class="nav-link" target="_blank" rel="noopener">Ver site</a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto align-items-center">
        <li class="nav-item mr-2 d-flex align-items-center">
            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>" id="themeToggleForm" class="m-0 p-0">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="site_theme" id="site_theme_input" value="<?php echo e($settings['site_theme'] ?? 'light'); ?>">
                <button type="button" class="btn btn-sm btn-outline-light d-flex align-items-center" id="themeToggleBtn" title="Alternar tema">
                    <i class="fas <?php echo e(($settings['site_theme'] ?? 'light') === 'dark' ? 'fa-sun' : 'fa-moon'); ?>"></i>
                </button>
            </form>
        </li>
        <?php if(auth()->guard()->check()): ?>
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#" aria-expanded="false">
                <i class="fas fa-user-circle mr-1"></i> <?php echo e(auth()->user()->name ?? 'Usuário'); ?>

            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <span class="dropdown-item-text text-muted text-sm">
                    <?php echo e((auth()->user()->role ?? 'user') === 'superadmin' ? 'Superadmin' : (auth()->user()->role ?? 'Usuário')); ?>

                </span>
                <div class="dropdown-divider"></div>
                <form method="POST" action="<?php echo e(route('logout')); ?>" class="m-0 p-0">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i> Sair
                    </button>
                </form>
            </div>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php /**PATH /home/somosunn/public_html/resources/views/admin/partials/navbar.blade.php ENDPATH**/ ?>