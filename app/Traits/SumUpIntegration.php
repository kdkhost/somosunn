<?php

namespace App\Traits;

use App\Services\SumUpService;
use App\Models\Setting;

trait SumUpIntegration
{
    /**
     * Verifica se o SumUp deve ser exibido como opção de pagamento
     */
    protected function shouldShowSumUp($amount = null, $productType = null, $userType = null): bool
    {
        $sumupService = app(SumUpService::class);
        
        if (!$sumupService->isEnabled()) {
            return false;
        }

        if ($userType && !$sumupService->isAllowedForUser($userType)) {
            return false;
        }

        if ($productType && !$sumupService->isAllowedForProduct($productType)) {
            return false;
        }

        if ($amount && !$sumupService->isAmountAllowed($amount)) {
            return false;
        }

        return true;
    }

    /**
     * Obtém as configurações do SumUp para o frontend
     */
    protected function getSumUpConfig($amount = null): array
    {
        $sumupService = app(SumUpService::class);
        
        if (!$sumupService->isEnabled()) {
            return ['enabled' => false];
        }

        $config = [
            'enabled' => true,
            'methods' => $sumupService->getEnabledPaymentMethods(),
            'max_installments' => (int) Setting::get('sumup_max_installments', 12),
            'installments_no_interest' => (int) Setting::get('sumup_installments_no_interest', 1),
            'pix_expiration_minutes' => (int) Setting::get('sumup_pix_expiration_minutes', 10),
            'fallback_to_mercadopago' => $sumupService->shouldFallbackToMercadoPago(),
        ];

        if ($amount) {
            $config['fees'] = $sumupService->calculateFees($amount);
            
            // Calcular opções de parcelamento
            $installmentOptions = [];
            $maxInstallments = $config['max_installments'];
            
            for ($i = 1; $i <= $maxInstallments; $i++) {
                $installmentOptions[] = $sumupService->calculateInstallments($amount, $i);
            }
            
            $config['installment_options'] = $installmentOptions;
        }

        return $config;
    }

    /**
     * Determina o tipo de usuário baseado no usuário autenticado
     */
    protected function getUserType($user = null): string
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return 'member';
        }

        // Lógica para determinar o tipo de usuário
        // Adapte conforme sua estrutura de usuários
        if ($user->isAdmin()) {
            return 'member'; // Admin pode usar como membro
        }

        if ($user->hasRole('instructor')) {
            return 'instructor';
        }

        if ($user->hasRole('seller')) {
            return 'seller';
        }

        if ($user->hasRole('mentor')) {
            return 'mentor';
        }

        return 'member';
    }

    /**
     * Adiciona dados do SumUp ao contexto de checkout
     */
    protected function addSumUpToCheckoutContext(array $context, $amount, $productType = null): array
    {
        $userType = $this->getUserType();
        
        $context['sumup'] = [
            'available' => $this->shouldShowSumUp($amount, $productType, $userType),
            'config' => $this->getSumUpConfig($amount),
        ];

        return $context;
    }

    /**
     * Cria um pedido com suporte ao SumUp
     */
    protected function createOrderWithSumUpSupport(array $orderData): array
    {
        // Adicionar metadados do SumUp se necessário
        if (!isset($orderData['metadata'])) {
            $orderData['metadata'] = [];
        }

        $orderData['metadata']['sumup_available'] = $this->shouldShowSumUp(
            $orderData['total_amount'] ?? null,
            $orderData['product_type'] ?? null,
            $this->getUserType()
        );

        return $orderData;
    }

    /**
     * Processa pagamento via SumUp
     */
    protected function processSumUpPayment($order, array $paymentData): array
    {
        $sumupService = app(SumUpService::class);

        try {
            $checkoutData = [
                'reference' => $order->id . '_' . time(),
                'amount' => $order->total_amount,
                'description' => $paymentData['description'] ?? "Pedido #{$order->id}",
                'return_url' => $paymentData['return_url'] ?? route('checkout.success'),
            ];

            if (!empty($paymentData['customer_email'])) {
                $checkoutData['customer'] = [
                    'email' => $paymentData['customer_email']
                ];
            }

            $result = $sumupService->createCheckout($checkoutData);

            if ($result['success']) {
                // Atualizar pedido com dados do SumUp
                $order->update([
                    'gateway' => 'sumup',
                    'gateway_transaction_id' => $result['data']['id'],
                    'status' => 'pending',
                    'metadata' => array_merge($order->metadata ?? [], [
                        'sumup_checkout_data' => $result['data'],
                        'payment_method' => 'sumup'
                    ])
                ]);

                return [
                    'success' => true,
                    'checkout_url' => $result['data']['checkout_url'] ?? null,
                    'checkout_id' => $result['data']['id'],
                    'order_id' => $order->id
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['message']
                ];
            }

        } catch (\Exception $e) {
            \Log::error('SumUp payment processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erro interno no processamento do pagamento.'
            ];
        }
    }

    /**
     * Obtém gateways disponíveis incluindo SumUp
     */
    protected function getAvailableGateways($amount = null, $productType = null): array
    {
        $gateways = [];
        $userType = $this->getUserType();

        // MercadoPago (assumindo que já existe)
        if (Setting::get('mercadopago_enabled', 1)) {
            $gateways['mercadopago'] = [
                'name' => 'MercadoPago',
                'enabled' => true,
                'methods' => $this->getMercadoPagoMethods(), // Implementar se necessário
            ];
        }

        // SumUp
        if ($this->shouldShowSumUp($amount, $productType, $userType)) {
            $gateways['sumup'] = [
                'name' => 'SumUp',
                'enabled' => true,
                'methods' => app(SumUpService::class)->getEnabledPaymentMethods(),
                'config' => $this->getSumUpConfig($amount),
            ];
        }

        return $gateways;
    }

    /**
     * Obtém métodos do MercadoPago (placeholder)
     */
    protected function getMercadoPagoMethods(): array
    {
        $methods = [];
        
        if (Setting::get('mercadopago_method_credit_card', 1)) {
            $methods[] = 'credit_card';
        }
        
        if (Setting::get('mercadopago_method_debit_card', 0)) {
            $methods[] = 'debit_card';
        }
        
        if (Setting::get('mercadopago_method_pix', 1)) {
            $methods[] = 'pix';
        }
        
        if (Setting::get('mercadopago_method_ticket', 0)) {
            $methods[] = 'ticket';
        }
        
        if (Setting::get('mercadopago_method_mercadopago', 0)) {
            $methods[] = 'mercadopago';
        }

        return $methods;
    }
}