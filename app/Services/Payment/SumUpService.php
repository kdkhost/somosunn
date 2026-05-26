<?php

namespace App\Services\Payment;

use App\Http\Controllers\PaymentWebhookController;
use App\Models\GatewayAccount;
use App\Models\Order;
use App\Models\Setting;
use App\Models\SumUpTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class SumUpService
{
    private const BASE_URL = 'https://api.sumup.com';

    // -------------------------------------------------------------------------
    // Checkout (pagamento único)
    // -------------------------------------------------------------------------

    /**
     * Cria um checkout SumUp e registra a SumUpTransaction.
     * Também registra o webhook dinâmico para esta transação.
     */
    public function createCheckout(Order $order, array $options = []): array
    {
        $config       = $this->getSellerConfig($order);
        $webhookToken = Str::random(64);
        $webhookUrl   = $this->buildWebhookUrl($order->id, $webhookToken);

        // Descrição: nome do serviço + nome do comprador (visível no painel SumUp)
        $description = $this->orderDescription($order);
        if (!empty($options['description'])) {
            $description = $options['description'];
        }

        // Calcula o breakdown de cobranca com base no numero de parcelas solicitado.
        // Por padrao, assume 1x (a vista) - sem taxa de gateway nem juros de parcelamento.
        $installments = max(1, (int) ($options['installments'] ?? 1));
        $breakdown    = $this->calculateBreakdown($order, $installments);

        // Atualiza o pedido para refletir o valor que sera efetivamente cobrado.
        $metaUpdates = [
            'sumup_base_amount'   => $breakdown['base_amount'],
            'sumup_fee_amount'    => $breakdown['total_extra'],
            'sumup_gateway_fee'   => $breakdown['gateway_fee'],
            'sumup_interest_fee'  => $breakdown['installment_interest'],
            'sumup_installments'  => $installments,
            'sumup_pass_fee'      => $breakdown['total_extra'] > 0,
        ];

        $order->forceFill([
            'total_amount' => $breakdown['charge_amount'],
            'fee_amount'   => $breakdown['total_extra'],
            'metadata'     => array_merge($order->metadata ?? [], $metaUpdates),
        ])->save();

        $payload = [
            'checkout_reference' => 'ORDER-' . $order->id . '-' . time(),
            'amount'             => $breakdown['charge_amount'],
            'currency'           => $order->currency ?? 'BRL',
            'merchant_code'      => $config['merchant_code'],
            'description'        => mb_substr($description, 0, 255),
            'return_url'         => $options['return_url'] ?? route('checkout.success', $order),
        ];

        $response = $this->post('/v0.1/checkouts', $payload, $config['api_key']);

        if (empty($response['id'])) {
            throw new RuntimeException('SumUp: falha ao criar checkout. ' . json_encode($response));
        }

        // Registra webhook dinâmico
        $this->registerWebhook($order, $webhookToken, $config['api_key']);

        // Persiste a transação
        SumUpTransaction::create([
            'order_id'      => $order->id,
            'checkout_id'   => $response['id'],
            'status'        => 'PENDING',
            'payment_type'  => strtoupper($options['payment_type'] ?? 'CARD'),
            'amount'        => $breakdown['charge_amount'],
            'currency'      => $order->currency ?? 'BRL',
            'webhook_token' => $webhookToken,
            'webhook_url'   => $webhookUrl,
            'raw_response'  => $response,
        ]);

        return [
            'checkout_id'   => $response['id'],
            'webhook_token' => $webhookToken,
            'charge_amount' => $breakdown['charge_amount'],
            'base_amount'   => $breakdown['base_amount'],
            'fee_amount'    => $breakdown['total_extra'],
            'installments'  => $installments,
            'raw'           => $response,
        ];
    }

    /**
     * Calcula o breakdown de cobranca (base + taxa de gateway + juros de parcelamento).
     *
     * Regra: taxa de gateway (pass_fee) e juros de parcelamento SO sao aplicados
     * quando o numero de parcelas for MAIOR que "sumup_installments_no_interest".
     * Para a vista (1x, dentro do limite sem juros), o cliente paga apenas o valor base.
     */
    public function calculateBreakdown(Order $order, int $installments = 1): array
    {
        // Prioriza o valor base gravado em metadata (se o pedido ja passou por este fluxo antes)
        $baseAmount = (float) (data_get($order->metadata, 'sumup_base_amount') ?? $order->total_amount);
        $installments = max(1, $installments);

        $passFee        = (bool)(int) Setting::get('sumup_pass_fee', 0);
        $feePercent     = (float) Setting::get('sumup_fee_percentage', 2.75);
        $feeFixed       = (float) Setting::get('sumup_fee_fixed', 0);
        $noInterestUpTo = max(1, (int) Setting::get('sumup_installments_no_interest', 1));
        $installmentTax = (float) Setting::get('sumup_installment_tax', 0);
        $interestType   = (string) Setting::get('sumup_interest_type', 'per_installment');

        $chargeAmount       = $baseAmount;
        $gatewayFee         = 0.0;
        $installmentInterest = 0.0;

        // Taxa de gateway e juros so se aplicam quando ha parcelamento acima do limite sem juros.
        if ($installments > $noInterestUpTo) {
            // 1) Taxa de gateway (pass_fee) - aplicada ao valor base
            if ($passFee && ($feePercent > 0 || $feeFixed > 0)) {
                $withGatewayFee = round($baseAmount * (1 + $feePercent / 100) + $feeFixed, 2);
                $gatewayFee     = round($withGatewayFee - $baseAmount, 2);
                $chargeAmount   = $withGatewayFee;
            }

            // 2) Juros de parcelamento - aplicado sobre o valor apos a taxa de gateway
            if ($installmentTax > 0) {
                $parcelsWithInterest = $installments - $noInterestUpTo;
                $before = $chargeAmount;
                if ($interestType === 'on_total') {
                    $chargeAmount = round($chargeAmount * (1 + $installmentTax / 100), 2);
                } else {
                    $chargeAmount = round($chargeAmount * (1 + ($installmentTax / 100) * $parcelsWithInterest), 2);
                }
                $installmentInterest = round($chargeAmount - $before, 2);
            }
        }

        return [
            'base_amount'          => round($baseAmount, 2),
            'charge_amount'        => round($chargeAmount, 2),
            'gateway_fee'          => round($gatewayFee, 2),
            'installment_interest' => round($installmentInterest, 2),
            'total_extra'          => round($gatewayFee + $installmentInterest, 2),
            'installments'         => $installments,
            'no_interest_up_to'    => $noInterestUpTo,
            'pass_fee'             => $passFee,
            'fee_percentage'       => $feePercent,
            'fee_fixed'            => $feeFixed,
            'installment_tax'      => $installmentTax,
            'interest_type'        => $interestType,
        ];
    }

    /**
     * @deprecated Use calculateBreakdown() em vez deste metodo.
     */
    public function resolveChargeAmount(Order $order): array
    {
        $b = $this->calculateBreakdown($order, 1);
        return [
            'base_amount'    => $b['base_amount'],
            'charge_amount'  => $b['charge_amount'],
            'fee_amount'     => $b['total_extra'],
            'fee_percentage' => $b['fee_percentage'],
            'fee_fixed'      => $b['fee_fixed'],
            'pass_fee'       => $b['pass_fee'] && $b['total_extra'] > 0,
        ];
    }

    /**
     * Processa pagamento com token de cartão (SumUp.js).
     */
    public function processCardCheckout(string $checkoutId, string $cardToken, string $apiKey): array
    {
        $payload = [
            'payment_type' => 'card',
            'card'         => ['token' => $cardToken],
        ];

        $response = $this->put("/v0.1/checkouts/{$checkoutId}", $payload, $apiKey);

        $this->updateTransactionStatus($checkoutId, $response);

        return $response;
    }

    /**
     * Cria checkout PIX e retorna QR Code.
     *
     * A API SumUp retorna os dados do PIX em pix.artefacts[]:
     *   - name="barcode" → imagem JPEG do QR Code (URL para download)
     *   - name="code"    → código copia-e-cola (texto, campo "content")
     *
     * Também suporta qr_code_pix com a mesma estrutura.
     */
    public function processPixCheckout(Order $order): array
    {
        $config   = $this->getSellerConfig($order);
        $checkout = $this->createCheckout($order, ['payment_type' => 'PIX']);

        // Submete o checkout como PIX (sem personal_details — conta SumUp do vendedor)
        $payload = ['payment_type' => 'pix'];

        $response = $this->put("/v0.1/checkouts/{$checkout['checkout_id']}", $payload, $config['api_key']);

        Log::debug('SumUp PIX checkout response', [
            'checkout_id' => $checkout['checkout_id'],
            'response'    => $response,
        ]);

        // Extrair artefatos do PIX — estrutura oficial da API SumUp
        // Tenta 'pix' primeiro, depois 'qr_code_pix' como fallback
        $artefacts = data_get($response, 'pix.artefacts', [])
            ?: data_get($response, 'qr_code_pix.artefacts', []);

        $qrCodeUrl  = '';
        $copyPaste  = '';
        $qrCodeBase64 = '';

        foreach ($artefacts as $artefact) {
            $name = $artefact['name'] ?? '';
            if ($name === 'barcode') {
                $qrCodeUrl = $artefact['location'] ?? '';
            } elseif ($name === 'code') {
                // O código copia-e-cola vem diretamente no campo "content"
                $copyPaste = $artefact['content'] ?? '';
                if (empty($copyPaste) && !empty($artefact['location'])) {
                    // Fallback: buscar o conteúdo via URL
                    try {
                        $codeResponse = Http::withHeaders($this->headers($config['api_key']))
                            ->timeout(10)
                            ->get($artefact['location']);
                        if ($codeResponse->successful()) {
                            $copyPaste = trim($codeResponse->body());
                        }
                    } catch (\Throwable $e) {
                        Log::warning('SumUp PIX: falha ao buscar codigo copia-e-cola', ['error' => $e->getMessage()]);
                    }
                }
            }
        }

        // Baixar imagem do QR Code e converter para base64
        if (!empty($qrCodeUrl)) {
            try {
                $imgResponse = Http::withHeaders($this->headers($config['api_key']))
                    ->timeout(10)
                    ->get($qrCodeUrl);
                if ($imgResponse->successful()) {
                    $qrCodeBase64 = base64_encode($imgResponse->body());
                }
            } catch (\Throwable $e) {
                Log::warning('SumUp PIX: falha ao baixar imagem do QR Code', ['error' => $e->getMessage()]);
            }
        }

        return [
            'checkout_id'    => $checkout['checkout_id'],
            'qr_code'        => $copyPaste,   // código copia-e-cola (texto EMV)
            'copy_paste'     => $copyPaste,
            'qr_code_base64' => $qrCodeBase64, // imagem base64 do QR Code
            'qr_code_url'    => $qrCodeUrl,    // URL original da imagem
            'raw'            => $response,
        ];
    }

    /**
     * Consulta status de um checkout.
     */
    public function getCheckout(string $checkoutId, string $apiKey): array
    {
        return $this->get("/v0.1/checkouts/{$checkoutId}", $apiKey);
    }

    /**
     * Reconcilia transacoes SumUp do pedido consultando a API oficial.
     *
     * O webhook pode falhar ou chegar depois do polling. Neste caso, uma
     * transacao paga antiga nao pode ficar escondida por um checkout pendente
     * mais recente do mesmo pedido.
     */
    public function reconcileOrderTransactions(
        Order $order,
        ?string $preferredCheckoutId = null,
        bool $settlePaidOrder = true
    ): array {
        $summary = [
            'order_id'        => $order->id,
            'checked'         => 0,
            'paid'            => false,
            'settled'         => false,
            'status'          => (string) ($order->status === 'paid' ? 'PAID' : 'PENDING'),
            'checkout_id'     => null,
            'transaction_id'  => null,
            'payment_method'  => null,
            'transactions'    => [],
        ];

        $localPaid = SumUpTransaction::query()
            ->where('order_id', $order->id)
            ->where('status', 'PAID')
            ->latest('id')
            ->first();

        if ($localPaid && (string) $order->status === 'paid') {
            $paymentMethod = $this->paymentMethodFromTransaction($localPaid, []);
            $updates = [];

            if (empty($order->payment_method)) {
                $updates['payment_method'] = $paymentMethod;
            }

            if (empty($order->transaction_id) && !empty($localPaid->transaction_id)) {
                $updates['transaction_id'] = $localPaid->transaction_id;
            }

            if ($updates) {
                $order->forceFill($updates)->save();
            }

            return array_merge($summary, [
                'paid'           => true,
                'status'         => 'PAID',
                'checkout_id'    => $localPaid->checkout_id,
                'transaction_id' => $localPaid->transaction_id,
                'payment_method' => $paymentMethod,
            ]);
        }

        $transactions = SumUpTransaction::query()
            ->where('order_id', $order->id)
            ->orderByDesc('id')
            ->get();

        if ($transactions->isEmpty()) {
            return $summary;
        }

        if ($preferredCheckoutId) {
            $transactions = $transactions
                ->sortByDesc(fn (SumUpTransaction $transaction) => $transaction->checkout_id === $preferredCheckoutId ? 1 : 0)
                ->values();
        }

        $config = $this->getSellerConfig($order);

        foreach ($transactions as $transaction) {
            if (empty($transaction->checkout_id)) {
                continue;
            }

            try {
                $checkout = $this->getCheckout($transaction->checkout_id, $config['api_key']);
            } catch (\Throwable $e) {
                Log::warning('SumUp reconcile: falha ao consultar checkout', [
                    'order_id'    => $order->id,
                    'checkout_id' => $transaction->checkout_id,
                    'error'       => $e->getMessage(),
                ]);
                continue;
            }

            $summary['checked']++;

            $status = $this->normalizeCheckoutStatus($checkout);
            $transactionId = $this->extractTransactionId($checkout) ?: $transaction->transaction_id;
            $paymentMethod = $this->paymentMethodFromTransaction($transaction, $checkout);
            $paidAmount = $this->extractCheckoutAmount($checkout);

            $transaction->forceFill([
                'status'         => $status,
                'transaction_id' => $transactionId,
                'amount'         => $paidAmount ?: $transaction->amount,
                'raw_response'   => $checkout,
            ])->save();

            $summary['transactions'][] = [
                'id'             => $transaction->id,
                'checkout_id'    => $transaction->checkout_id,
                'status'         => $status,
                'transaction_id' => $transactionId,
            ];

            if ($status !== 'PAID') {
                if (!$summary['paid'] && $summary['checkout_id'] === null) {
                    $summary['status'] = $status;
                    $summary['checkout_id'] = $transaction->checkout_id;
                }
                continue;
            }

            $summary['paid'] = true;
            $summary['status'] = 'PAID';
            $summary['checkout_id'] = $transaction->checkout_id;
            $summary['transaction_id'] = $transactionId;
            $summary['payment_method'] = $paymentMethod;

            if ($settlePaidOrder && (string) $order->status !== 'paid') {
                $order = $this->prepareOrderForPaidCheckout($order, $paymentMethod, $paidAmount);

                $payload = array_merge($checkout, [
                    'event_type'      => 'sumup.reconciled',
                    'reconciled_at'   => now()->toIso8601String(),
                    'checkout_id'     => $transaction->checkout_id,
                    'transaction_id'  => $transactionId,
                    'payment_method'  => $paymentMethod,
                    'sumup_tx_id'     => $transaction->id,
                ]);

                app(PaymentWebhookController::class)
                    ->processPaidOrder($order, $transactionId ?: $transaction->checkout_id, $payload);

                $summary['settled'] = true;
                $order->refresh();
            }

            break;
        }

        return $summary;
    }

    /**
     * Cancela um checkout SumUp pendente.
     */
    public function cancelCheckout(string $checkoutId): array
    {
        try {
            $apiKey   = $this->apiKey();
            $response = $this->delete("/v0.1/checkouts/{$checkoutId}", $apiKey);

            Log::info('SumUp checkout cancelled', [
                'checkout_id' => $checkoutId,
                'response'    => $response,
            ]);

            return ['success' => true, 'response' => $response];
        } catch (\Throwable $e) {
            Log::warning('SumUp cancelCheckout failed', [
                'checkout_id' => $checkoutId,
                'error'       => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Reembolso
    // -------------------------------------------------------------------------

    /**
     * Reembolsa um pagamento SumUp (total ou parcial).
     */
    public function refundPayment(Order $order, ?float $amount = null): array
    {
        $config        = $this->getSellerConfig($order);
        $transactionId = $order->transaction_id;

        if (empty($transactionId)) {
            throw new RuntimeException('SumUp: transaction_id nao encontrado no pedido #' . $order->id);
        }

        $payload = [];
        if ($amount !== null) {
            $payload['amount'] = round($amount, 2);
        }

        $response = $this->post("/v0.1/me/refund/{$transactionId}", $payload, $config['api_key']);

        Log::info('SumUp refund', ['order_id' => $order->id, 'amount' => $amount, 'response' => $response]);

        return $response;
    }

    // -------------------------------------------------------------------------
    // Assinaturas
    // -------------------------------------------------------------------------

    public function createSubscription(Order $order, array $userData): array
    {
        $config  = $this->getSellerConfig($order);
        $payload = [
            'checkout_reference' => 'SUB-' . $order->id . '-' . time(),
            'amount'             => (float) $order->total_amount,
            'currency'           => $order->currency ?? 'BRL',
            'merchant_code'      => $config['merchant_code'],
            'description'        => $this->orderDescription($order),
            'customer_id'        => (string) $order->user_id,
        ];

        return $this->post('/v0.1/subscriptions', $payload, $config['api_key']);
    }

    public function cancelSubscription(string $subscriptionId, string $apiKey): array
    {
        return $this->delete("/v0.1/subscriptions/{$subscriptionId}", $apiKey);
    }

    public function getSubscription(string $subscriptionId, string $apiKey): array
    {
        return $this->get("/v0.1/subscriptions/{$subscriptionId}", $apiKey);
    }

    // -------------------------------------------------------------------------
    // Webhook dinâmico
    // -------------------------------------------------------------------------

    /**
     * Registra URL de webhook dinâmica na API SumUp para esta transação.
     */
    public function registerWebhook(Order $order, string $token, string $apiKey): string
    {
        $url = $this->buildWebhookUrl($order->id, $token);

        $payload = [
            'url'        => $url,
            'event_types' => [
                'payment.succeeded',
                'payment.failed',
                'payment.refunded',
                'checkout.completed',
                'subscription.renewed',
                'subscription.cancelled',
            ],
        ];

        $this->post('/v0.1/me/webhooks', $payload, $apiKey);

        return $url;
    }

    /**
     * Valida assinatura HMAC do webhook.
     */
    public function validateWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    // -------------------------------------------------------------------------
    // Validação de credenciais
    // -------------------------------------------------------------------------

    public function validateCredentials(?int $userId = null): bool
    {
        try {
            $apiKey = $userId
                ? $this->getApiKeyForUser($userId)
                : $this->apiKey();

            if (empty($apiKey)) {
                return false;
            }

            $response = $this->get('/v0.1/me', $apiKey);
            return !empty(
                data_get($response, 'merchant_profile.merchant_code')
                ?? data_get($response, 'merchant_code')
                ?? data_get($response, 'username')
            );
        } catch (\Throwable $e) {
            Log::warning('SumUp validateCredentials failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function getSellerConfig(Order $order): array
    {
        // Tenta credenciais do vendedor primeiro
        if ($order->seller_id) {
            $account = GatewayAccount::where('user_id', $order->seller_id)
                ->where('provider', 'sumup')
                ->where('enabled', true)
                ->first();

            if ($account && !empty($account->access_token)) {
                $extra = $account->extra ?? [];
                return [
                    'api_key'       => $account->access_token,
                    'merchant_code' => $extra['merchant_code'] ?? $this->merchantCode(),
                    'source'        => 'seller',
                ];
            }
        }

        // Fallback para credenciais globais (conta do admin da plataforma)
        return [
            'api_key'       => $this->apiKey(),
            'merchant_code' => $this->merchantCode(),
            'source'        => 'platform',
        ];
    }

    private function getApiKeyForUser(int $userId): string
    {
        $account = GatewayAccount::where('user_id', $userId)
            ->where('provider', 'sumup')
            ->where('enabled', true)
            ->first();

        return $account?->access_token ?? $this->apiKey();
    }

    private function apiKey(): string
    {
        return (string) (Setting::get('sumup_api_key') ?: config('payments.sumup.api_key', ''));
    }

    private function merchantCode(): string
    {
        return (string) (Setting::get('sumup_merchant_code') ?: config('payments.sumup.merchant_code', ''));
    }

    private function normalizeCheckoutStatus(array $checkout): string
    {
        $statuses = [
            strtoupper((string) ($checkout['status'] ?? '')),
            strtoupper((string) data_get($checkout, 'transaction.status', '')),
        ];

        foreach ((array) data_get($checkout, 'transactions', []) as $transaction) {
            $statuses[] = strtoupper((string) ($transaction['status'] ?? ''));
        }

        if (array_intersect($statuses, ['PAID', 'SUCCESSFUL', 'SUCCESS'])) {
            return 'PAID';
        }

        if (array_intersect($statuses, ['REFUNDED'])) {
            return 'REFUNDED';
        }

        if (array_intersect($statuses, ['FAILED', 'FAIL', 'CANCELLED', 'CANCELED', 'EXPIRED'])) {
            return 'FAILED';
        }

        return 'PENDING';
    }

    private function extractTransactionId(array $checkout): ?string
    {
        foreach ((array) data_get($checkout, 'transactions', []) as $transaction) {
            $id = $transaction['id'] ?? $transaction['transaction_id'] ?? null;
            if ($id && in_array(strtoupper((string) ($transaction['status'] ?? '')), ['SUCCESSFUL', 'SUCCESS', 'PAID'], true)) {
                return (string) $id;
            }
        }

        foreach ((array) data_get($checkout, 'transactions', []) as $transaction) {
            $id = $transaction['id'] ?? $transaction['transaction_id'] ?? null;
            if ($id) {
                return (string) $id;
            }
        }

        $id = $checkout['transaction_id']
            ?? data_get($checkout, 'transaction.id')
            ?? null;

        return $id ? (string) $id : null;
    }

    private function extractCheckoutAmount(array $checkout): ?float
    {
        $amount = $checkout['amount']
            ?? data_get($checkout, 'transaction.amount')
            ?? data_get($checkout, 'transactions.0.amount')
            ?? null;

        return is_numeric($amount) ? round((float) $amount, 2) : null;
    }

    private function paymentMethodFromTransaction(SumUpTransaction $transaction, array $checkout): string
    {
        $method = (string) (
            data_get($checkout, 'payment_type')
            ?? data_get($checkout, 'transaction.payment_type')
            ?? data_get($checkout, 'transactions.0.payment_type')
            ?? $transaction->payment_type
            ?? ''
        );

        $method = strtolower($method);

        return str_contains($method, 'pix') ? 'pix' : 'card';
    }

    private function prepareOrderForPaidCheckout(Order $order, string $paymentMethod, ?float $paidAmount): Order
    {
        $metadata = $order->metadata ?? [];
        $updates = [
            'payment_method' => $paymentMethod,
            'metadata'       => array_merge($metadata, [
                'sumup_reconciled_at' => now()->toIso8601String(),
            ]),
        ];

        if ($paidAmount && abs($paidAmount - (float) $order->total_amount) > 0.009) {
            $updates['total_amount'] = $paidAmount;
            $updates['metadata']['sumup_reconciled_previous_total'] = (float) $order->total_amount;
            $updates['metadata']['sumup_reconciled_amount'] = $paidAmount;
        }

        $order->forceFill($updates)->save();

        return $order->fresh();
    }

    private function buildWebhookUrl(int $orderId, string $token): string
    {
        return url("/webhook/sumup/{$orderId}/{$token}");
    }

    private function orderDescription(Order $order): string
    {
        // Nome do serviço: título do primeiro item do pedido
        $order->loadMissing('items', 'user');

        $itemTitle = $order->items->first()?->title ?? '';
        $buyerName = $order->user?->name ?? '';

        if ($itemTitle !== '' && $buyerName !== '') {
            return $itemTitle . ' - ' . $buyerName;
        }

        if ($itemTitle !== '') {
            return $itemTitle;
        }

        if ($buyerName !== '') {
            return 'Pedido #' . $order->id . ' - ' . $buyerName;
        }

        return 'Pedido #' . $order->id;
    }

    private function headers(string $apiKey): array
    {
        return [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    private function post(string $endpoint, array $data, string $apiKey): array
    {
        $url      = self::BASE_URL . $endpoint;
        $response = Http::withHeaders($this->headers($apiKey))
            ->post($url, $data);

        Log::debug('SumUp POST', [
            'endpoint' => $endpoint,
            'status'   => $response->status(),
        ]);

        return $response->json() ?? [];
    }

    private function put(string $endpoint, array $data, string $apiKey): array
    {
        $url      = self::BASE_URL . $endpoint;
        $response = Http::withHeaders($this->headers($apiKey))
            ->put($url, $data);

        Log::debug('SumUp PUT', [
            'endpoint' => $endpoint,
            'status'   => $response->status(),
        ]);

        return $response->json() ?? [];
    }

    private function get(string $endpoint, string $apiKey): array
    {
        $url      = self::BASE_URL . $endpoint;
        $response = Http::withHeaders($this->headers($apiKey))
            ->get($url);

        Log::debug('SumUp GET', [
            'endpoint' => $endpoint,
            'status'   => $response->status(),
        ]);

        return $response->json() ?? [];
    }

    private function delete(string $endpoint, string $apiKey): array
    {
        $url      = self::BASE_URL . $endpoint;
        $response = Http::withHeaders($this->headers($apiKey))
            ->delete($url);

        return $response->json() ?? [];
    }

    private function updateTransactionStatus(string $checkoutId, array $response): void
    {
        $status = match (strtoupper($response['status'] ?? '')) {
            'PAID', 'SUCCESSFUL' => 'PAID',
            'FAILED'             => 'FAILED',
            default              => 'PENDING',
        };

        SumUpTransaction::where('checkout_id', $checkoutId)->update([
            'status'         => $status,
            'transaction_id' => $response['transaction_id'] ?? $response['id'] ?? null,
            'raw_response'   => $response,
        ]);
    }
}
