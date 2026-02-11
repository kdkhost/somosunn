<div class="card-body">
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle mr-2"></i> Ajustes do marketplace (comissão da plataforma e regras de venda).
    </div>

    <h5 class="text-primary mb-3"><i class="fas fa-percent mr-2"></i> Taxas da Plataforma</h5>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Comissão do Marketplace (%)</label>
                <div class="input-group">
                    <input type="number" name="marketplace_platform_fee_percent" class="form-control"
                        value="{{ $settings['marketplace_platform_fee_percent'] ?? '0' }}" min="0" max="100"
                        step="0.01">
                    <div class="input-group-append">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <small class="text-muted">
                    Percentual descontado do vendedor em cada venda (não altera o preço exibido para o comprador).
                </small>
            </div>
        </div>
    </div>
</div>

