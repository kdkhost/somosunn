<div class="card-body">
    <h5 class="text-primary mb-3"><i class="fas fa-users mr-2"></i>Login Social</h5>

    <div id="social-accordion">
        
        <div class="card card-outline card-danger social-card">
            <div class="card-header">
                <h3 class="card-title">
                    <button type="button" class="btn btn-link py-0 text-danger" data-toggle="collapse"
                        data-target="#collapseGoogle" aria-expanded="true" aria-controls="collapseGoogle">
                        <i class="fab fa-google mr-1"></i> Google
                    </button>
                </h3>
            </div>
            <div id="collapseGoogle" class="collapse show" data-parent="#social-accordion">
                <div class="card-body">
                    <div class="custom-control custom-switch mb-3">
                        <input type="hidden" name="social_google_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="social_google_enabled"
                            name="social_google_enabled" value="1" <?php echo e(($settings['social_google_enabled'] ?? 0) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="social_google_enabled">Ativar Login com
                            Google</label>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Client ID</label><input name="social_google_client_id"
                                class="form-control" value="<?php echo e($settings['social_google_client_id'] ?? ''); ?>"></div>
                        <div class="col-md-6 form-group"><label>Client Secret</label><input
                                name="social_google_client_secret" class="form-control"
                                value="<?php echo e($settings['social_google_client_secret'] ?? ''); ?>"></div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card card-outline card-primary social-card">
            <div class="card-header">
                <h3 class="card-title">
                    <button type="button" class="btn btn-link py-0" data-toggle="collapse"
                        data-target="#collapseFacebook" aria-expanded="false" aria-controls="collapseFacebook">
                        <i class="fab fa-facebook mr-1"></i> Facebook
                    </button>
                </h3>
            </div>
            <div id="collapseFacebook" class="collapse" data-parent="#social-accordion">
                <div class="card-body">
                    <div class="custom-control custom-switch mb-3">
                        <input type="hidden" name="social_facebook_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="social_facebook_enabled"
                            name="social_facebook_enabled" value="1" <?php echo e(($settings['social_facebook_enabled'] ?? 0) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="social_facebook_enabled">Ativar Login
                            com
                            Facebook</label>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>App ID</label><input name="social_facebook_app_id"
                                class="form-control" value="<?php echo e($settings['social_facebook_app_id'] ?? ''); ?>"></div>
                        <div class="col-md-6 form-group"><label>App Secret</label><input
                                name="social_facebook_app_secret" class="form-control"
                                value="<?php echo e($settings['social_facebook_app_secret'] ?? ''); ?>"></div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card card-outline card-info social-card">
            <div class="card-header">
                <h3 class="card-title">
                    <button type="button" class="btn btn-link py-0 text-info" data-toggle="collapse"
                        data-target="#collapseTwitter" aria-expanded="false" aria-controls="collapseTwitter">
                        <i class="fab fa-twitter mr-1"></i> Twitter / X
                    </button>
                </h3>
            </div>
            <div id="collapseTwitter" class="collapse" data-parent="#social-accordion">
                <div class="card-body">
                    <div class="custom-control custom-switch mb-3">
                        <input type="hidden" name="social_twitter_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="social_twitter_enabled"
                            name="social_twitter_enabled" value="1" <?php echo e(($settings['social_twitter_enabled'] ?? 0) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="social_twitter_enabled">Ativar Login
                            com
                            Twitter / X</label>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Client ID (API Key)</label><input
                                name="social_twitter_client_id" class="form-control"
                                value="<?php echo e($settings['social_twitter_client_id'] ?? ''); ?>"></div>
                        <div class="col-md-6 form-group"><label>Client Secret (API Secret)</label><input
                                name="social_twitter_client_secret" class="form-control"
                                value="<?php echo e($settings['social_twitter_client_secret'] ?? ''); ?>"></div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card card-outline card-dark social-card">
            <div class="card-header">
                <h3 class="card-title">
                    <button type="button" class="btn btn-link py-0 text-dark" data-toggle="collapse"
                        data-target="#collapseLinkedin" aria-expanded="false" aria-controls="collapseLinkedin">
                        <i class="fab fa-linkedin mr-1"></i> LinkedIn
                    </button>
                </h3>
            </div>
            <div id="collapseLinkedin" class="collapse" data-parent="#social-accordion">
                <div class="card-body">
                    <div class="custom-control custom-switch mb-3">
                        <input type="hidden" name="social_linkedin_enabled" value="0">
                        <input type="checkbox" class="custom-control-input" id="social_linkedin_enabled"
                            name="social_linkedin_enabled" value="1" <?php echo e(($settings['social_linkedin_enabled'] ?? 0) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="social_linkedin_enabled">Ativar Login
                            com
                            LinkedIn</label>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Client ID</label><input name="social_linkedin_client_id"
                                class="form-control" value="<?php echo e($settings['social_linkedin_client_id'] ?? ''); ?>"></div>
                        <div class="col-md-6 form-group"><label>Client Secret</label><input
                                name="social_linkedin_client_secret" class="form-control"
                                value="<?php echo e($settings['social_linkedin_client_secret'] ?? ''); ?>"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\settings\partials\social.blade.php ENDPATH**/ ?>