<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderSplitService;

class PaymentGatewayRouterService
{
    public function __construct(
        private readonly OrderSplitService $splitService
    ) {
    }

    /**
     * Retorna o modo de automação de comissão configurado pelo superadmin:
     * - manual: apenas registro e controle (repasse manual)
     * - efi_pix_out: 100% SumUp + Pix Out automático via Efí Pay para a chave Pix do Mercado Pago
     * - mp_routing: vendas do administrador na SumUp; vendas de terceiros roteadas para Mercado Pago Marketplace
     */
    public function commissionMode(): string
    {
        $mode = trim((string) Setting::get('superadmin_commission_payout_mode', 'efi_pix_out'));

        return in_array($mode, ['manual', 'efi_pix_out', 'mp_routing'], true) ? $mode : 'manual';
    }

    /**
     * Indica se o pedido de um determinado vendedor deve ser roteado para o Mercado Pago
     * para garantir divisão nativa automática (quando o modo mp_routing estiver ativo).
     */
    public function shouldForceMercadoPagoForSeller(?User $seller): bool
    {
        if ($this->commissionMode() !== 'mp_routing') {
            return false;
        }

        if ((string) Setting::get('superadmin_commission_auto_enabled', '0') !== '1') {
            return false;
        }

        if (!$seller) {
            return false;
        }

        return $seller->shouldChargePlatformFee();
    }

    public function shouldForceMercadoPagoForOrder(Order $order): bool
    {
        if ($this->splitService->isAdminOriginatedOrder($order)) {
            return false;
        }

        return $this->shouldForceMercadoPagoForSeller($order->seller);
    }
}
