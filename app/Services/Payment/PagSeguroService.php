<?php

namespace App\Services\Payment;

use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Models\GatewayAccount;
use App\Models\Setting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class PagSeguroService
{
    protected string $baseUrlProd = 'https://api.pagseguro.com';
    protected string $baseUrlSandbox = 'https://sandbox.api.pagseguro.com';

    private function getBaseUrl(): string
    {
        $env = Setting::get('pagseguro_env', config('payments.pagseguro.env', 'sandbox'));
        return ($env === 'sandbox') ? $this->baseUrlSandbox : $this->baseUrlProd;
    }

    private function getSellerConfig(Order $order): array
    {
        $env = Setting::get('pagseguro_env', 'sandbox');
        $prefix = $env === 'production' ? 'pagseguro_prod_' : 'pagseguro_sandbox_';

        $platformToken = Setting::get($prefix . 'token');
        if (empty($platformToken)) {
            $platformToken = Setting::get('pagseguro_token');
        }

        $platformEmail = Setting::get('pagseguro_email');

        $config = [
            'token' => trim((string) $platformToken),
            'email' => trim((string) $platformEmail), // Legacy or identification
            'is_platform' => true,
            'pass_fee' => null
        ];

        if ($order->seller_id) {
            $sellerAccount = GatewayAccount::where('user_id', $order->seller_id)
                ->where('provider', 'pagseguro')
                ->where('enabled', true)
                ->first();

            if ($sellerAccount && !empty($sellerAccount->access_token)) {
                $passFee = null;
                if (is_array($sellerAccount->extra) && isset($sellerAccount->extra['pass_fee'])) {
                    $passFee = (bool) $sellerAccount->extra['pass_fee'];
                }

                $config = [
                    'token' => $sellerAccount->access_token,
                    'email' => $sellerAccount->client_id ?? '', // Using client_id column for email/app_id
                    'is_platform' => false,
                    'pass_fee' => $passFee
                ];
            }
        }

        if (empty($config['token'])) {
            throw new Exception('PagSeguro não configurado para processar este pedido.');
        }

        return $config;
    }

    private function calculateTotalAndFee(Order $order, array $config): array
    {
        $originalAmount = (float) $order->total_amount;
        $feePercent = (float) Setting::get('marketplace_platform_fee_percent', 0);
        $globalBehavior = Setting::get('marketplace_fee_behavior', 'absorb');

        // Check Seller Override
        $passFee = $config['pass_fee'] ?? ($globalBehavior === 'pass');

        $finalAmount = $originalAmount;
        $applicationFee = 0.0;

        if ($feePercent > 0 && !$config['is_platform']) {
            if ($passFee) {
                // Pass Fee: Add fee to total.
                $feeValue = round($originalAmount * ($feePercent / 100), 2);
                $finalAmount = $originalAmount + $feeValue;
                $applicationFee = $feeValue;
            } else {
                // Absorb Fee: Deduct from seller.
                $applicationFee = round($originalAmount * ($feePercent / 100), 2);
            }
        }

        return [
            'transaction_amount' => $finalAmount,
            'application_fee' => $applicationFee,
        ];
    }

    public function isPixAvailable(Order $order): bool
    {
        return !Cache::get($this->pixAvailabilityCacheKey($order), false);
    }

    public function markPixAsUnavailable(Order $order, int $minutes = 360): void
    {
        Cache::put($this->pixAvailabilityCacheKey($order), true, now()->addMinutes($minutes));
    }

    private function pixAvailabilityCacheKey(Order $order): string
    {
        $owner = $order->seller_id ?: 'platform';

        return 'pagseguro:pix:whitelist-blocked:' . $owner;
    }

    private function throwApiException(string $context, Response $response): void
    {
        $payload = $response->json();
        if (!is_array($payload)) {
            $decoded = json_decode((string) $response->body(), true);
            $payload = is_array($decoded) ? $decoded : [];
        }

        $errors = collect(data_get($payload, 'error_messages', []))
            ->filter(fn ($item) => is_array($item))
            ->values();

        $codes = $errors
            ->pluck('code')
            ->filter()
            ->map(fn ($code) => strtoupper((string) $code))
            ->values()
            ->all();

        $descriptions = $errors
            ->pluck('description')
            ->filter()
            ->map(fn ($description) => trim((string) $description))
            ->values()
            ->all();

        $descriptionText = Str::lower(implode(' ', $descriptions));
        $contextLabel = match ($context) {
            'pix' => 'o Pix',
            'credit_card' => 'o pagamento com cartao',
            'checkout' => 'a sessao de pagamento',
            default => 'o pagamento',
        };

        if (in_array('ACCESS_DENIED', $codes, true) && str_contains($descriptionText, 'whitelist')) {
            throw new PaymentGatewayException(
                'O Pix do PagSeguro nao esta liberado para esta conta no momento. Solicite a liberacao de whitelist no PagSeguro ou use outro metodo de pagamento disponivel.',
                'pagseguro_pix_whitelist_required',
                422,
                [
                    'provider' => 'pagseguro',
                    'status' => $response->status(),
                    'context' => $context,
                    'provider_codes' => $codes,
                    'provider_descriptions' => $descriptions,
                ]
            );
        }

        $detail = $descriptions[0] ?? trim((string) $response->body());
        $detail = $detail !== '' ? $detail : 'Resposta invalida do PagSeguro.';

        throw new PaymentGatewayException(
            'Nao foi possivel concluir ' . $contextLabel . ' no PagSeguro. ' . $detail,
            'pagseguro_api_error',
            422,
            [
                'provider' => 'pagseguro',
                'status' => $response->status(),
                'context' => $context,
                'provider_codes' => $codes,
                'provider_descriptions' => $descriptions,
            ]
        );
    }

    public function createCheckoutSession(Order $order): array
    {
        $config = $this->getSellerConfig($order);
        $baseUrl = $this->getBaseUrl();

        $calc = $this->calculateTotalAndFee($order, $config);

        // Adjust items if fee passed
        $items = [];
        $ratio = ($order->total_amount > 0) ? ($calc['transaction_amount'] / $order->total_amount) : 1.0;

        foreach ($order->items as $item) {
            $items[] = [
                'reference_id' => (string) $item->id,
                'name' => $item->title,
                'quantity' => $item->quantity,
                'unit_amount' => (int) (round($item->price * $ratio, 2) * 100) // cents
            ];
        }

        $body = [
            'reference_id' => (string) $order->id,
            'items' => $items,
            'customer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
                'tax_id' => preg_replace('/\D/', '', $order->user->doc ?? '00000000000'),
            ],
            'notification_urls' => [
                route('api.webhooks.pagseguro')
            ]
        ];

        // Split Payment Logic (Platform overrides)
        // Note: PagSeguro might require the Platform to be the primary receiver and split to seller, 
        // OR seller to be primary and split to platform. 
        // Typically in V4 "Split" is defined in 'charges'.

        // For simplicity in this modernization, we primarily support Auth with Seller Credentials (direct sales)
        // If we want Platform to take a fee from Seller's transaction, we use Application Fee?
        // PagSeguro Split typically requires "Split" object in 'charges'.

        // If it's not platform, and there's a fee, we try to split.
        // HOWEVER, to keep it simple and safe for now (paridade with previous MP step):
        // references usually just use the Seller Token directly.
        // If we need to send money to platform, we need the platform's account ID.
        // Since we don't have the Platform's Account ID easily hardcoded without OAuth, 
        // we might skip actual Split implementation unless we have the Platform's Public Key/ID.

        // FUTURE: Add Split object if Platform Account ID is known.

        $response = Http::withToken($config['token'])
            ->post("{$baseUrl}/orders", $body);

        if ($response->failed()) {
            $this->throwApiException('checkout', $response);
        }

        return (array) $response->json();
    }

    public function createPixPayment(Order $order): array
    {
        $config = $this->getSellerConfig($order);
        $baseUrl = $this->getBaseUrl();
        $calc = $this->calculateTotalAndFee($order, $config);

        $body = [
            'reference_id' => (string) $order->id,
            'customer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
                'tax_id' => preg_replace('/\D/', '', $order->user->doc ?? '00000000000'),
            ],
            'items' => $this->buildItems($order, $calc),
            'qr_codes' => [
                [
                    'amount' => [
                        'value' => (int) ($calc['transaction_amount'] * 100)
                    ],
                    'expiration_date' => now()->addMinutes(30)->format('Y-m-d\TH:i:sP'),
                ]
            ],
            'notification_urls' => [
                route('api.webhooks.pagseguro')
            ]
        ];

        $response = Http::withToken($config['token'])->post("{$baseUrl}/orders", $body);

        if ($response->failed()) {
            $this->throwApiException('pix', $response);
        }

        return $response->json();
    }

    public function createCreditCardPayment(Order $order, array $data): array
    {
        $config = $this->getSellerConfig($order);
        $baseUrl = $this->getBaseUrl();
        $calc = $this->calculateTotalAndFee($order, $config);

        $body = [
            'reference_id' => (string) $order->id,
            'customer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
                'tax_id' => preg_replace('/\D/', '', $order->user->doc ?? '00000000000'),
            ],
            'items' => $this->buildItems($order, $calc),
            'charges' => [
                [
                    'reference_id' => (string) $order->id,
                    'description' => 'Pedido #' . $order->id,
                    'amount' => [
                        'value' => (int) ($calc['transaction_amount'] * 100),
                        'currency' => 'BRL'
                    ],
                    'payment_method' => [
                        'type' => 'CREDIT_CARD',
                        'installments' => (int) $data['installments'],
                        'capture' => true,
                        'card' => [
                            'encrypted' => $data['encrypted_card'], // Card Token/Encrypted Data
                            'store' => false
                        ]
                    ]
                ]
            ],
            'notification_urls' => [
                route('api.webhooks.pagseguro')
            ]
        ];

        $response = Http::withToken($config['token'])->post("{$baseUrl}/orders", $body);

        if ($response->failed()) {
            $this->throwApiException('credit_card', $response);
        }

        return $response->json();
    }

    private function buildItems(Order $order, array $calc): array
    {
        $items = [];
        $ratio = ($order->total_amount > 0) ? ($calc['transaction_amount'] / $order->total_amount) : 1.0;

        foreach ($order->items as $item) {
            $items[] = [
                'reference_id' => (string) $item->id,
                'name' => $item->title,
                'quantity' => $item->quantity,
                'unit_amount' => (int) (round($item->price * $ratio, 2) * 100)
            ];
        }
        return $items;
    }

    public function validateCredentials(int $userId): bool
    {
        $account = GatewayAccount::where('user_id', $userId)
            ->where('provider', 'pagseguro')
            ->first();

        if (!$account || empty($account->access_token)) {
            throw new Exception('Credenciais PagSeguro não encontradas.');
        }

        $token = trim((string) $account->access_token);
        $baseUrl = $this->getBaseUrl();

        // PagSeguro v4: POST /public-keys com body {"type":"CREDIT_CARD"} é o endpoint
        // de menor impacto para validar credenciais sem criar transações.
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post("{$baseUrl}/public-keys", ['type' => 'CREDIT_CARD']);

        // 200 = válido | 422 = token válido mas parâmetro errado (aceitável) | 4xx auth = inválido
        if ($response->status() === 401 || $response->status() === 403) {
            throw new Exception('Token PagSeguro inválido ou sem permissão (HTTP ' . $response->status() . ').');
        }

        if ($response->serverError()) {
            throw new Exception('Erro no servidor PagSeguro ao validar credenciais.');
        }

        return true;
    }

    public function refundPayment(Order $order): array
    {
        if (!$order->transaction_id) {
            throw new Exception('ID da transação não encontrado.');
        }

        $config = $this->getSellerConfig($order);
        $baseUrl = $this->getBaseUrl();
        $paymentId = $order->transaction_id;

        // V4 Refund: /charges/{id}/cancel
        $response = Http::withToken($config['token'])
            ->post("{$baseUrl}/charges/{$paymentId}/cancel", [
                'amount' => ['value' => (int) ($order->total_amount * 100)]
            ]);

        if ($response->failed()) {
            // Fallup logic if needed
            throw new Exception('Falha no reembolso PagSeguro: ' . $response->body());
        }

        return (array) $response->json();
    }
}
