<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\GatewayAccount;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Exception;

class MercadoPagoService
{
    protected string $baseUrl = 'https://api.mercadopago.com';

    public function __construct()
    {
        //
    }

    private function calculateTotalAndFee(Order $order, array $config): array
    {
        $originalAmount = (float) $order->total_amount;
        $feePercent = (float) Setting::get('marketplace_platform_fee_percent', 0);
        $globalBehavior = Setting::get('marketplace_fee_behavior', 'absorb');

        // Check Seller Override
        $passFee = $config['pass_fee'] ?? ($globalBehavior === 'pass');

        // application_fee só é válido para tokens OAuth de marketplace.
        // Tokens diretos (cadastrados manualmente) não suportam split automático.
        // Se is_marketplace não estiver setado como true, não calcular fee para envio à API.
        $isMarketplace = $config['is_platform'] ? true : ($config['is_marketplace'] ?? false);

        $finalAmount = $originalAmount;
        $applicationFee = 0.0;

        if ($feePercent > 0 && !$config['is_platform'] && $isMarketplace) {
            if ($passFee) {
                // Pass Fee: Add fee to total.
                // Formula: Total = Original + (Original * Fee%)
                $feeValue = round($originalAmount * ($feePercent / 100), 2);
                $finalAmount = $originalAmount + $feeValue;
                $applicationFee = $feeValue;
            } else {
                // Absorb Fee: Deduct from seller.
                // Formula: Application Fee = Total * Fee%
                $applicationFee = round($originalAmount * ($feePercent / 100), 2);
            }
        }

        return [
            'transaction_amount' => $finalAmount,
            'application_fee' => $applicationFee,
        ];
    }

    public function createPreference(Order $order, array $options = []): array
    {
        $config = $this->getSellerConfig($order);
        $token = $config['token'];

        $calc = $this->calculateTotalAndFee($order, $config);

        $preferenceData = $this->buildPreferenceData($order, $options, $calc);

        if (!$config['is_platform'] && $calc['application_fee'] > 0) {
            $preferenceData['marketplace_fee'] = $calc['application_fee'];
        }

        $response = Http::withToken($token)
            ->withHeaders($this->commonHeaders('pref-' . $order->id))
            ->post("{$this->baseUrl}/checkout/preferences", $preferenceData);

        if ($response->failed()) {
            \Log::error('MercadoPago Preference Error', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('MercadoPago Preference Error: ' . $response->body());
        }

        return (array) $response->json();
    }

    public function createPixPayment(Order $order, array $data): array
    {
        $config = $this->getSellerConfig($order);
        $token = $config['token'];

        $calc = $this->calculateTotalAndFee($order, $config);

        $payerEmail = trim((string) ($data['email'] ?? $order->user->email ?? ''));
        if ($payerEmail === '') {
            throw new Exception('E-mail do pagador não informado.');
        }

        $rawName = trim((string) ($data['name'] ?? $order->user->name ?? ''));
        if ($rawName === '') {
            $rawName = 'Cliente';
        }
        $nameParts = preg_split('/\s+/', $rawName) ?: ['Cliente'];
        $firstName = $nameParts[0] ?? 'Cliente';
        $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : 'Cliente';

        $cpf = preg_replace('/\D/', '', (string) ($data['cpf'] ?? $order->user->doc ?? ''));
        $payer = [
            'email' => $payerEmail,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];
        if ($cpf !== '') {
            $payer['identification'] = [
                'type' => 'CPF',
                'number' => $cpf,
            ];
        }

        $paymentData = [
            'transaction_amount' => $calc['transaction_amount'],
            'description' => $this->orderDescription($order),
            'payment_method_id' => 'pix',
            'payer' => $payer,
            'external_reference' => (string) $order->id,
            'notification_url' => $this->notificationUrl(),
        ];

        if (!$config['is_platform'] && $calc['application_fee'] > 0) {
            $paymentData['application_fee'] = $calc['application_fee'];
        }

        $response = Http::withToken($token)
            ->withHeaders($this->commonHeaders('pix-' . $order->id))
            ->post("{$this->baseUrl}/v1/payments", $paymentData);

        if ($response->failed()) {
            \Log::error('MercadoPago Pix Error: ' . $response->body(), [
                'order_id' => $order->id,
                'status' => $response->status()
            ]);
            throw new Exception('Falha ao criar Pix: ' . $response->body());
        }

        $body = (array) $response->json();

        return [
            'status' => $body['status'] ?? null,
            'id' => $body['id'] ?? null,
            'qr_code' => data_get($body, 'point_of_interaction.transaction_data.qr_code'),
            'qr_code_base64' => data_get($body, 'point_of_interaction.transaction_data.qr_code_base64'),
        ];
    }

    public function createCreditCardPayment(Order $order, array $data): array
    {
        $config = $this->getSellerConfig($order);
        $token = $config['token'];

        $calc = $this->calculateTotalAndFee($order, $config);

        $payerEmail = trim((string) ($data['email'] ?? $order->user->email ?? ''));
        if ($payerEmail === '') {
            throw new Exception('E-mail do pagador não informado.');
        }

        $cpf = preg_replace('/\D/', '', (string) ($data['cpf'] ?? $order->user->doc ?? ''));
        $payer = [
            'email' => $payerEmail,
        ];
        if ($cpf !== '') {
            $payer['identification'] = [
                'type' => 'CPF',
                'number' => $cpf,
            ];
        }

        $paymentData = [
            'transaction_amount' => $calc['transaction_amount'],
            'token' => $data['token'],
            'description' => $this->orderDescription($order),
            'installments' => (int) $data['installments'],
            'payment_method_id' => $data['payment_method_id'],
            'payer' => $payer,
            'external_reference' => (string) $order->id,
            'notification_url' => $this->notificationUrl(),
        ];

        // issuer_id é opcional — string vazia causa erro 400 na API
        if (!empty($data['issuer_id'])) {
            $paymentData['issuer_id'] = (int) $data['issuer_id'];
        }

        if (!$config['is_platform'] && $calc['application_fee'] > 0) {
            $paymentData['application_fee'] = $calc['application_fee'];
        }

        $response = Http::withToken($token)
            ->withHeaders($this->commonHeaders('cc-' . $order->id))
            ->post("{$this->baseUrl}/v1/payments", $paymentData);

        if ($response->failed()) {
            $causes = $response->json('cause') ?? [];
            $causeDetail = is_array($causes) && !empty($causes)
                ? implode(', ', array_map(fn($c) => ($c['code'] ?? '') . ':' . ($c['description'] ?? ''), $causes))
                : '';

            \Log::error('MercadoPago Credit Card Error', [
                'order_id' => $order->id,
                'http_status' => $response->status(),
                'mp_message' => $response->json('message'),
                'mp_cause' => $response->json('cause'),
                'mp_error' => $response->json('error'),
                'is_marketplace' => $config['is_marketplace'] ?? false,
                'application_fee' => $calc['application_fee'],
                'transaction_amount' => $calc['transaction_amount'],
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'body' => $response->body(),
            ]);

            // Mensagem mais informativa para depuração
            $msg = $response->json('message') ?? $response->json('error') ?? $response->body();
            if ($causeDetail !== '') {
                $msg .= ' [' . $causeDetail . ']';
            }
            throw new Exception('Falha ao processar cartão: ' . $msg);
        }

        $body = (array) $response->json();
        return [
            'status' => $body['status'] ?? null,
            'id' => $body['id'] ?? null,
            'status_detail' => $body['status_detail'] ?? null,
        ];
    }

    public function subscribeUser(string $mpPlanId, array $userData): array
    {
        $token = $this->accessToken();

        $subscriptionData = [
            'preapproval_plan_id' => $mpPlanId,
            'payer_email' => $userData['email'],
            'back_url' => $userData['back_url'] ?? route('panel.dashboard'),
            'status' => 'authorized',
            'reason' => $userData['reason'] ?? 'Assinatura',
            'external_reference' => $userData['external_reference'] ?? '',
        ];

        if (!empty($userData['card_token'])) {
            $subscriptionData['card_token_id'] = $userData['card_token'];
        }

        $response = Http::withToken($token)
            ->withHeaders($this->commonHeaders('sub-' . ($userData['external_reference'] ?? uniqid())))
            ->post("{$this->baseUrl}/preapproval", $subscriptionData);

        if ($response->failed()) {
            throw new Exception('Erro ao criar assinatura MP: ' . $response->body());
        }

        return (array) $response->json();
    }

    public function cancelSubscription(string $preapprovalId): array
    {
        $token = $this->accessToken();
        $response = Http::withToken($token)
            ->put("{$this->baseUrl}/preapproval/{$preapprovalId}", [
                'status' => 'cancelled'
            ]);

        if ($response->failed()) {
            throw new Exception('Erro ao cancelar assinatura MP: ' . $response->body());
        }

        return (array) $response->json();
    }

    public function getPreapproval(string $preapprovalId): array
    {
        $token = $this->accessToken();
        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/preapproval/{$preapprovalId}");

        if ($response->failed()) {
            throw new Exception('Erro ao buscar assinatura MP: ' . $response->body());
        }

        return (array) $response->json();
    }

    public function createPreapprovalPlan(array $data): array
    {
        $token = $this->accessToken();

        $planData = [
            'reason' => $data['name'],
            'auto_setup' => [
                'frequency' => (int) ($data['billing_cycle'] ?? 1),
                'frequency_type' => $this->mapPeriod($data['period'] ?? 'months'),
                'transaction_amount' => (float) $data['price'],
                'currency_id' => 'BRL',
            ],
            'back_url' => route('marketplace.index'),
            'status' => 'active',
        ];

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/preapproval_plan", $planData);

        if ($response->failed()) {
            throw new Exception('Erro ao criar plano recorrente MP: ' . $response->body());
        }

        return (array) $response->json();
    }

    private function mapPeriod(string $period): string
    {
        return match (strtolower($period)) {
            'day', 'days' => 'days',
            'month', 'months', 'mensal' => 'months',
            'year', 'years', 'anual' => 'months',
            default => 'months',
        };
    }

    private function accessToken(): string
    {
        $env = Setting::get('mercadopago_env', 'sandbox');
        $prefix = $env === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';

        $token = trim((string) Setting::get($prefix . 'access_token', ''));
        if ($token === '') {
            $token = trim((string) Setting::get('mercadopago_access_token', ''));
        }
        if ($token === '') {
            throw new Exception('MercadoPago não configurado. Verifique as configurações do gateway da plataforma.');
        }
        return $token;
    }

    private function notificationUrl(): string
    {
        return route('api.webhooks.mercadopago');
    }

    private function orderDescription(Order $order): string
    {
        return 'UNN - Pedido #' . $order->id;
    }

    private function buildPreferenceData(Order $order, array $options = [], array $calc = []): array
    {
        $items = [];
        $maxInstallments = 12;
        $enabledMethods = [];
        if (Setting::get('mercadopago_method_credit_card', 1))
            $enabledMethods[] = 'credit_card';
        if (Setting::get('mercadopago_method_debit_card', 1))
            $enabledMethods[] = 'debit_card';
        if (Setting::get('mercadopago_method_pix', 1))
            $enabledMethods[] = 'pix';

        if (empty($enabledMethods)) {
            $enabledMethods = ['credit_card', 'debit_card', 'pix'];
        }

        if ($order->seller_id) {
            $account = GatewayAccount::where('user_id', $order->seller_id)
                ->where('provider', 'mercadopago')
                ->where('enabled', true)
                ->first();

            if ($account && is_array($account->extra)) {
                $maxInstallments = (int) data_get($account->extra, 'max_installments', 12);
                $enabledMethods = data_get($account->extra, 'enabled_methods', $enabledMethods);
            }
        }

        // If we calculated a new total (passed fee), we need to adjust item prices roughly
        // or just send one aggregate item if detail isn't crucial.
        // For precision, let's adjust the unit price of items relative to the new total.

        $originalTotal = (float) $order->total_amount;
        $newTotal = $calc['transaction_amount'] ?? $originalTotal;
        $ratio = ($originalTotal > 0) ? ($newTotal / $originalTotal) : 1.0;

        foreach ($order->items as $item) {
            $items[] = [
                'title' => $item->title,
                'quantity' => $item->quantity,
                'currency_id' => 'BRL',
                'unit_price' => round((float) $item->price * $ratio, 2)
            ];
        }

        $context = (string) data_get($order->metadata, 'context', '');
        if ($context === '') {
            $firstType = (string) optional($order->items->first())->item_type;
            $context = $firstType ?: 'unknown';
        }

        $token = (string) data_get($order->metadata, 'public_token', '');
        $fallbackPlanId = (int) optional($order->items->first())->item_id;
        $subscriptionCheckoutUrl = $fallbackPlanId ? route('subscription.checkout', ['plan' => $fallbackPlanId]) : url('/');

        $backUrls = match ($context) {
            'course', 'mentorship', 'marketplace' => [
                'success' => route('checkout.success', ['order' => $order->id]),
                'failure' => route('checkout.failure', ['order' => $order->id]),
                'pending' => route('checkout.pending', ['order' => $order->id]),
            ],
            'event' => [
                'success' => route('events.payment.success', ['order' => $order->id, 'token' => $token]),
                'failure' => route('events.payment.failure', ['order' => $order->id, 'token' => $token]),
                'pending' => route('events.payment.pending', ['order' => $order->id, 'token' => $token]),
            ],
            'subscription' => [
                'success' => route('subscription.success', ['order' => $order->id]),
                'failure' => $subscriptionCheckoutUrl,
                'pending' => $subscriptionCheckoutUrl,
            ],
            default => [
                'success' => route('checkout.success', ['order' => $order->id]),
                'failure' => route('checkout.failure', ['order' => $order->id]),
                'pending' => route('checkout.pending', ['order' => $order->id]),
            ],
        };

        $statementDescriptor = trim((string) ($options['statement_descriptor'] ?? 'UNN PLATAFORMA'));
        if ($statementDescriptor === '') {
            $statementDescriptor = 'UNN PLATAFORMA';
        }

        $paymentMethods = [
            'installments' => $maxInstallments,
        ];

        $excludedMethods = [];
        if (!in_array('credit_card', $enabledMethods))
            $excludedMethods[] = ['id' => 'credit_card'];
        if (!in_array('pix', $enabledMethods))
            $excludedMethods[] = ['id' => 'pix'];
        if (!in_array('debit_card', $enabledMethods))
            $excludedMethods[] = ['id' => 'debit_card'];

        // Always exclude ticket unless somehow forced, but effectively we want to ban it as per user requirement.
        // Even if it was in enabledMethods (old config), we might want to force exclude or just respect config?
        // User said "No Boleto". So let's force exclude it or just not check for it.
        // If we don't check for it in enabledMethods, it won't be in allowed list.
        // But we must add it to excluded list if we want to block it.
        if (!in_array('ticket', $enabledMethods))
            $excludedMethods[] = ['id' => 'ticket'];

        if (!empty($excludedMethods)) {
            $paymentMethods['excluded_payment_types'] = $excludedMethods;
        }

        return [
            'items' => $items,
            'payer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ],
            'back_urls' => $options['back_urls'] ?? $backUrls,
            'auto_return' => 'approved',
            'external_reference' => (string) $order->id,
            'statement_descriptor' => $statementDescriptor,
            'notification_url' => $this->notificationUrl(),
            'payment_methods' => $paymentMethods,
        ];
    }

    public function refundPayment(Order $order): array
    {
        if (!$order->transaction_id) {
            throw new Exception('ID da transação de pagamento não encontrado para este pedido.');
        }

        // Determine who holds the token (Seller or Platform)
        // If the order was a split payment or direct payment to seller, we need seller token?
        // Actually, for split payments, the platform token (collector) might be needed to refund?
        // - If standard split: Platform is aggregator.
        // - If direct pay to seller: Seller token.

        $config = $this->getSellerConfig($order);
        $token = $config['token'];
        $paymentId = $order->transaction_id;

        $response = Http::withToken($token)
            ->withHeaders(['X-Idempotency-Key' => 'refund-' . $paymentId . '-' . time()])
            ->post("{$this->baseUrl}/v1/payments/{$paymentId}/refunds");

        if ($response->failed()) {
            throw new Exception('Falha ao processar reembolso no MercadoPago: ' . $response->body());
        }

        return (array) $response->json();
    }

    public function cancelPayment(Order $order): array
    {
        if (!$order->transaction_id) {
            // Se não tem ID de transação, apenas retorna sucesso (cancelamento local)
            return ['status' => 'cancelled', 'note' => 'Local only'];
        }

        $config = $this->getSellerConfig($order);
        $token = $config['token'];
        $paymentId = $order->transaction_id;

        $response = Http::withToken($token)
            ->withHeaders(['X-Idempotency-Key' => 'cancel-' . $paymentId . '-' . time()])
            ->put("{$this->baseUrl}/v1/payments/{$paymentId}", [
                'status' => 'cancelled'
            ]);

        if ($response->failed()) {
            // Ignorar erro se já estiver cancelado ou não puder ser cancelado (ex: expirado)
            // Mas lançar exceção se for erro de autenticação ou sistema
            if ($response->status() !== 400) {
                throw new Exception('Falha ao cancelar pagamento no MP: ' . $response->body());
            }
        }

        return (array) $response->json();
    }

    public function validateCredentials(int $userId): bool
    {
        $account = GatewayAccount::where('user_id', $userId)
            ->where('provider', 'mercadopago')
            ->first();

        if (!$account || empty($account->access_token)) {
            throw new Exception('Credenciais não encontradas.');
        }

        $response = Http::withToken($account->access_token)
            ->get("{$this->baseUrl}/users/me");

        if ($response->failed()) {
            throw new Exception('Falha na validação: ' . $response->body());
        }

        return true;
    }

    public function getBalance(?int $sellerId = null): array
    {
        $token = null;

        if ($sellerId) {
            $account = GatewayAccount::where('user_id', $sellerId)
                ->where('provider', 'mercadopago')
                ->where('enabled', true)
                ->first();
            $token = $account->access_token ?? null;
        }

        // Fallback para o token da plataforma se for admin (sellerId nulo) ou se o vendedor não tiver token
        if (empty($token)) {
            try {
                $token = $this->accessToken();
            } catch (\Exception $e) {
                throw new Exception('Token do Mercado Pago não encontrado (Plataforma ou Vendedor).');
            }
        }

        $response = Http::withToken($token)
            ->get("{$this->baseUrl}/users/me/mercadopago_account/balance");

        if ($response->failed()) {
            return [
                'total_amount' => 0,
                'available_balance' => 0,
                'unavailable_balance' => 0,
                'currency_id' => 'BRL'
            ];
        }

        return (array) $response->json();
    }

    private function getSellerConfig(Order $order): array
    {
        $env = Setting::get('mercadopago_env', 'sandbox');
        $prefix = $env === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';

        $platformToken = Setting::get($prefix . 'access_token');
        if (empty($platformToken)) {
            $platformToken = Setting::get('mercadopago_access_token');
        }

        $platformPublicKey = Setting::get($prefix . 'public_key');
        if (empty($platformPublicKey)) {
            $platformPublicKey = Setting::get('mercadopago_public_key');
        }

        $config = [
            'token' => trim((string) $platformToken),
            'public_key' => trim((string) $platformPublicKey),
            'is_platform' => true,
            'is_marketplace' => false,
            'pass_fee' => null
        ];

        if ($order->seller_id) {
            // Se o vendedor for o administrador da plataforma, não deve haver split (application_fee para si mesmo falha no MP)
            // Também verificamos se o vendedor tem uma conta configurada
            $sellerAccount = GatewayAccount::where('user_id', $order->seller_id)
                ->where('provider', 'mercadopago')
                ->where('enabled', true)
                ->first();

            // ID do administrador (que é o dono da plataforma)
            $platformOwnerId = Setting::get('platform_owner_id', 2); // Fallback para 2 conforme diagnóstico

            if ($sellerAccount && !empty($sellerAccount->access_token) && (int) $order->seller_id !== (int) $platformOwnerId) {
                $passFee = null;
                if (is_array($sellerAccount->extra) && isset($sellerAccount->extra['pass_fee'])) {
                    $passFee = (bool) $sellerAccount->extra['pass_fee'];
                }

                // marketplace_enabled = true: token via OAuth, suporta application_fee (split automático)
                // false/ausente: token direto do vendedor, NÃO enviar application_fee
                $isMarketplace = !empty($sellerAccount->extra['marketplace_enabled']);

                $config = [
                    'token' => $sellerAccount->access_token,
                    'public_key' => $sellerAccount->public_key,
                    'is_platform' => false,
                    'is_marketplace' => $isMarketplace,
                    'pass_fee' => $passFee
                ];
            }
        }

        if (empty($config['token'])) {
            throw new Exception('Vendedor não possui conta MercadoPago conectada.');
        }

        return $config;
    }

    private function calculateApplicationFee(Order $order): ?float
    {
        return null;
    }

    /**
     * Cabeçalhos comuns para todas as requisições da plataforma.
     * Inclui Idempotência, Integrador ID e Platform ID.
     */
    private function commonHeaders(string $idempotencyKeySuffix): array
    {
        $headers = [
            'X-Idempotency-Key' => $idempotencyKeySuffix . '-' . time(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Só envia estes headers se tiverem valor — string vazia causa internal_error na API
        $integratorId = trim((string) Setting::get('mercadopago_integrator_id', config('payments.mercadopago.integrator_id', '')));
        if ($integratorId !== '') {
            $headers['X-Integrator-Id'] = $integratorId;
        }

        $platformId = trim((string) Setting::get('mercadopago_platform_id', config('payments.mercadopago.platform_id', '')));
        if ($platformId !== '') {
            $headers['X-Platform-Id'] = $platformId;
        }

        return $headers;
    }
}
