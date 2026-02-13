<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-ad mr-2"></i> Configure a monetização do seu conteúdo. Você pode usar Google AdSense ou códigos
        de publicidade personalizados.
    </div>

    <div class="form-group mb-4">
        <div class="custom-control custom-switch custom-switch-lg custom-switch-off-danger custom-switch-on-success">
            <input type="hidden" name="ads_enabled" value="0">
            <input type="checkbox" class="custom-control-input" id="ads_enabled" name="ads_enabled" value="1" <?php echo e(($settings['ads_enabled'] ?? 0) ? 'checked' : ''); ?>>
            <label class="custom-control-label font-weight-bold" for="ads_enabled">Ativar Exibição de Anúncios</label>
        </div>
        <small class="form-text text-muted ml-5">Quando desativado, nenhum anúncio será carregado, independentemente das
            configurações abaixo.</small>
    </div>

    <div class="row">
        
        <div class="col-md-6">
            <div class="card card-outline card-primary h-100">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fab fa-google mr-2"></i> Google AdSense</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Publisher ID (Pub-ID)</label>
                        <input type="text" name="adsense_publisher_id" class="form-control"
                            value="<?php echo e($settings['adsense_publisher_id'] ?? ''); ?>" placeholder="ca-pub-000000000000">
                    </div>
                    <div class="form-group">
                        <label>Slot ID</label>
                        <input type="text" name="adsense_slot_id" class="form-control"
                            value="<?php echo e($settings['adsense_slot_id'] ?? ''); ?>" placeholder="1234567890">
                    </div>
                    <div class="form-group">
                        <label>Formato do Anúncio</label>
                        <select name="adsense_format" class="form-control">
                            <?php ($adsFormat = $settings['adsense_format'] ?? 'auto'); ?>
                            <option value="auto" <?php echo e($adsFormat === 'auto' ? 'selected' : ''); ?>>Automático (Responsivo)
                            </option>
                            <option value="fluid" <?php echo e($adsFormat === 'fluid' ? 'selected' : ''); ?>>Fluxo (In-feed)</option>
                            <option value="rectangle" <?php echo e($adsFormat === 'rectangle' ? 'selected' : ''); ?>>Retângulo
                            </option>
                            <option value="horizontal" <?php echo e($adsFormat === 'horizontal' ? 'selected' : ''); ?>>Horizontal
                                (Banner)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-md-6">
            <div class="card card-outline card-secondary h-100">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-code mr-2"></i> Código Personalizado /
                        Outras Redes</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>HTML/Javascript (Global)</label>
                        <textarea name="ads_code_html" class="form-control code-editor" rows="9"
                            placeholder="<!-- Cole aqui o script do seu ad network -->"><?php echo e($settings['ads_code_html'] ?? ''); ?></textarea>
                        <small class="text-muted">Use este campo se não estiver usando a integração nativa do
                            AdSense.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h5 class="text-primary mb-3"><i class="fas fa-stream mr-2"></i> Anúncios no Feed (Comunidade)</h5>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group mb-3">
                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                    <input type="hidden" name="ads_inter_feed_enabled" value="0">
                    <input type="checkbox" class="custom-control-input" id="ads_inter_feed_enabled"
                        name="ads_inter_feed_enabled" value="1" <?php echo e(($settings['ads_inter_feed_enabled'] ?? 0) ? 'checked' : ''); ?>>
                    <label class="custom-control-label font-weight-bold" for="ads_inter_feed_enabled">Habilitar Anúncios
                        entre Postagens</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 form-group">
            <label>Frequência (A cada X posts)</label>
            <select name="adsense_frequency" class="form-control">
                <?php ($adsFreq = (int) ($settings['adsense_frequency'] ?? 5)); ?>
                <option value="3" <?php echo e($adsFreq === 3 ? 'selected' : ''); ?>>A cada 3 posts</option>
                <option value="5" <?php echo e($adsFreq === 5 ? 'selected' : ''); ?>>A cada 5 posts</option>
                <option value="10" <?php echo e($adsFreq === 10 ? 'selected' : ''); ?>>A cada 10 posts</option>
                <option value="15" <?php echo e($adsFreq === 15 ? 'selected' : ''); ?>>A cada 15 posts</option>
            </select>
        </div>
        <div class="col-md-8 form-group">
            <label>Código Específico para o Feed (Opcional)</label>
            <textarea name="ads_inter_feed_code" class="form-control" rows="2"
                placeholder="Se vazio, usará o global/AdSense configurado acima."><?php echo e($settings['ads_inter_feed_code'] ?? ''); ?></textarea>
        </div>
    </div>
</div><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\settings\partials\ads.blade.php ENDPATH**/ ?>