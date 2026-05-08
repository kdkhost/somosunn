<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SumUpService
{
    protected $apiKey;
    protected $merchantCode;
    protected $environment;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = Setting::get('sumup_api_key');
        $this->merchantCode = Setting::get('sumup_merchant_code');
        $this->environment = Setting::get('sumup_env', 'sandbox');
        $this->baseUrl = $this->environment === 'production' 
            ? 'https://api.sumup.com' 
            : 'https://api.sumup.com'; // SumUp usa a mesma URL para sandbox e produção
    }

    /**
     * Verifica se o SumUp está habilitado e configurado
     */
    public function isEnabled(): bool
    {
        return Setting::get('sumup_enabled', 0) && 
               !empty($this->apiKey) && 
               !empty($this->merchantCode);
    }

    /**
     * Verifica se o SumUp está disponível para um tipo específico de usuário
     */
    public function isAllowedForUser($userType): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $allowedTypes = [
            'member' => 'sumup_allow_members',
            'instructor' => 'sumup_allow_instructors',
            'seller' => 'sumup_allow_sellers',
            'mentor' => 'sumup_allow_mentors',
        ];

        $settingKey = $allowedTypes[$userType] ?? null;
        return $settingKey ? Setting::get($settingKey, 1) : false;
    }

    /**
     * Verifica se o SumUp está disponível para um tipo específico de produto/serviço
     */
    public function isAllowedForProduct($productType): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $allowedTypes = [
            'course' => 'sumup_allow_courses',
            'mentorship' => 'sumup_allow_mentorships',
            'event' => 'sumup_allow_events',
            'marketplace' => 'sumup_allow_marketplace',
            'subscription' => 'sumup_allow_subscriptions',
            'service' => 'sumup_allow_services',
        ];

        $settingKey = $allowedTypes[$productType] ?? null;
        return $settingKey ? Setting::get($settingKey, 1) : false;
    }

    /**
     * Verifica se o valor está dentro dos limites configurados
     */
    public function isAmountAllowed($amount): bool
    {
        $minAmount = (float) Setting::get('sumup_minimum_amount', 0);
        $maxAmount = (float) Setting::get('sumup_maximum_amount', 0);

        if ($minAmount > 0 && $amount < $minAmount) {
            return false;
        }

        if ($maxAmount > 0 && $amount > $maxAmount) {
            return false;
        }

        return true;
    }

    /**
     * Testa a conexão com a API do SumUp
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/v0.1/me');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Conexão com SumUp estabelecida com sucesso! Merchant: ' . ($data['merchant_code'] ?? 'N/A'),
                    'data' => $data
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Falha na conexão: ' . $response->body(),
                    'status' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('SumUp connection test failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Erro na conexão: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cria um checkout no SumUp
     */
    public function createCheckout($data): array
    {
        try {
            $payload = [
                'checkout_reference' => $data['reference'],
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'BRL',
                'merchant_code' => $this->merchantCode,
                'description' => $data['description'] ?? '',
                'return_url' => $data['return_url'] ?? null,
            ];

            // Adicionar dados do cliente se fornecidos
            if (!empty($data['customer'])) {
                $payload['pay_to_email'] = $data['customer']['email'] ?? null;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/v0.1/checkouts', $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao criar checkout: ' . $response->body(),
                    'status' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('SumUp checkout creation failed', ['error' => $e->getMessage(), 'data' => $data]);
            return [
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Consulta o status de um checkout
     */
    public function getCheckout($checkoutId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . '/v0.1/checkouts/' . $checkoutId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao consultar checkout: ' . $response->body(),
                    'status' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('SumUp checkout query failed', ['error' => $e->getMessage(), 'checkout_id' => $checkoutId]);
            return [
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calcula as taxas do SumUp
     */
    public function calculateFees($amount): array
    {
        $feePercentage = (float) Setting::get('sumup_fee_percentage', 2.75);
        $feeFixed = (float) Setting::get('sumup_fee_fixed', 0);
        $passFee = Setting::get('sumup_pass_fee', 0);

        $percentageFee = ($amount * $feePercentage) / 100;
        $totalFee = $percentageFee + $feeFixed;

        return [
            'percentage_fee' => $percentageFee,
            'fixed_fee' => $feeFixed,
            'total_fee' => $totalFee,
            'net_amount' => $passFee ? $amount : ($amount - $totalFee),
            'gross_amount' => $passFee ? ($amount + $totalFee) : $amount,
            'pass_fee_to_customer' => (bool) $passFee
        ];
    }

    /**
     * Calcula parcelamento com juros
     */
    public function calculateInstallments($amount, $installments): array
    {
        $maxInstallments = (int) Setting::get('sumup_max_installments', 12);
        $installmentsNoInterest = (int) Setting::get('sumup_installments_no_interest', 1);
        $installmentTax = (float) Setting::get('sumup_installment_tax', 0);
        $interestType = Setting::get('sumup_interest_type', 'per_installment');

        if ($installments > $maxInstallments) {
            $installments = $maxInstallments;
        }

        if ($installments <= $installmentsNoInterest || $installmentTax == 0) {
            return [
                'installments' => $installments,
                'installment_amount' => $amount / $installments,
                'total_amount' => $amount,
                'interest_amount' => 0,
                'interest_rate' => 0
            ];
        }

        if ($interestType === 'per_installment') {
            // Taxa por parcela: valor × (1 + taxa% × nº parcelas)
            $interestRate = $installmentTax * $installments;
            $totalAmount = $amount * (1 + ($interestRate / 100));
        } else {
            // Taxa sobre o total: valor × (1 + taxa%)
            $interestRate = $installmentTax;
            $totalAmount = $amount * (1 + ($interestRate / 100));
        }

        return [
            'installments' => $installments,
            'installment_amount' => $totalAmount / $installments,
            'total_amount' => $totalAmount,
            'interest_amount' => $totalAmount - $amount,
            'interest_rate' => $interestRate
        ];
    }

    /**
     * Verifica se deve fazer fallback para MercadoPago
     */
    public function shouldFallbackToMercadoPago(): bool
    {
        return Setting::get('sumup_fallback_to_mercadopago', 1) && !$this->isEnabled();
    }

    /**
     * Obtém os métodos de pagamento habilitados
     */
    public function getEnabledPaymentMethods(): array
    {
        $methods = [];

        if (Setting::get('sumup_method_card', 1)) {
            $methods[] = 'card';
        }

        if (Setting::get('sumup_method_pix', 1)) {
            $methods[] = 'pix';
        }

        return $methods;
    }

    /**
     * Valida webhook do SumUp
     */
    public function validateWebhook($payload, $signature = null): bool
    {
        $webhookSecret = Setting::get('sumup_webhook_secret');
        
        if (empty($webhookSecret)) {
            // Se não há secret configurado, aceita qualquer webhook
            return true;
        }

        if (empty($signature)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }
}