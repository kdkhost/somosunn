# Design Técnico — Integração Gateway SumUp

## Visão Geral

A integração SumUp segue exatamente o mesmo padrão arquitetural do Mercado Pago já existente no sistema. Um novo `SumUpService` em `app/Services/Payment/` encapsula toda a comunicação com a API SumUp. O `OrderRefundService` e o `PaymentWebhookController` são estendidos para suportar o novo gateway via `match` statement. O checkout existente recebe uma nova branch de processamento para SumUp.

---

## Arquitetura

```
Browser
  │
  ├── SumUp.js (tokenização de cartão — client-side)
  │
  └── Laravel App
        ├── CheckoutController / MentorshipCheckoutController / etc.
        │     └── SumUpService::createCheckout()
        │           └── SumUp API (POST /v0.1/checkouts)
        │
        ├── PaymentWebhookController::sumup()
        │     └── SumUpWebhookProcessor
        │           └── processPaidOrder() [existente]
        │
        └── Panel\Admin\SumUpController
              ├── index()     — listagem de transações
              ├── show()      — detalhes
              ├── refund()    — reembolso
              └── report()    — relatórios
```

---

## Banco de Dados

### Migrations Necessárias

#### 1. `sumup_transactions` (nova tabela)
```sql
CREATE TABLE sumup_transactions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NOT NULL,
    checkout_id     VARCHAR(255) NOT NULL,        -- ID do checkout SumUp
    transaction_id  VARCHAR(255) NULL,            -- ID da transação após pagamento
    status          VARCHAR(50) NOT NULL,         -- PENDING, PAID, FAILED, REFUNDED
    payment_type    VARCHAR(50) NOT NULL,         -- CARD, PIX
    amount          DECIMAL(10,2) NOT NULL,
    currency        VARCHAR(10) DEFAULT 'BRL',
    webhook_token   VARCHAR(64) NOT NULL,         -- token único por transação
    webhook_url     VARCHAR(500) NOT NULL,        -- URL dinâmica registrada
    raw_response    JSON NULL,                    -- resposta completa da API
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);
```

#### 2. `sumup_webhook_logs` (nova tabela)
```sql
CREATE TABLE sumup_webhook_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        BIGINT UNSIGNED NULL,
    event_type      VARCHAR(100) NOT NULL,
    payload         JSON NOT NULL,
    signature       VARCHAR(255) NULL,
    is_valid        BOOLEAN DEFAULT FALSE,
    processed_at    TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);
```

#### 3. `sumup_saved_cards` (nova tabela)
```sql
CREATE TABLE sumup_saved_cards (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    token           VARCHAR(255) NOT NULL,        -- token SumUp
    last_four       VARCHAR(4) NOT NULL,
    brand           VARCHAR(50) NOT NULL,         -- VISA, MASTERCARD, etc.
    expires_at      VARCHAR(7) NOT NULL,          -- MM/YYYY
    is_default      BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### 4. Alterações em tabelas existentes

**`gateway_accounts`** — já suporta `provider = 'sumup'` sem alteração.

**`orders`** — já possui `gateway VARCHAR` que receberá `'sumup'`.

**`subscriptions`** — adicionar coluna:
```sql
ALTER TABLE subscriptions ADD COLUMN sumup_subscription_id VARCHAR(255) NULL;
```

---

## Novos Arquivos

### Services

#### `app/Services/Payment/SumUpService.php`
Responsável por toda comunicação com a API SumUp v0.1.

```php
class SumUpService
{
    // Configuração
    private function apiKey(): string
    private function merchantCode(): string
    private function baseUrl(): string  // https://api.sumup.com

    // Checkout (pagamento único)
    public function createCheckout(Order $order, array $options = []): array
    // POST /v0.1/checkouts
    // Registra webhook dinâmico por transação
    // Retorna: ['checkout_id', 'checkout_url', 'webhook_token']

    public function getCheckout(string $checkoutId): array
    // GET /v0.1/checkouts/{id}

    public function processCardCheckout(string $checkoutId, string $cardToken): array
    // PUT /v0.1/checkouts/{id}
    // Processa pagamento com token de cartão

    public function processPixCheckout(Order $order): array
    // Cria checkout PIX e retorna QR Code

    // Reembolso
    public function refundPayment(Order $order, ?float $amount = null): array
    // POST /v0.1/me/refund/{transaction_id}

    // Assinaturas
    public function createSubscription(Order $order, array $userData): array
    public function cancelSubscription(string $subscriptionId): array
    public function getSubscription(string $subscriptionId): array

    // Cartões salvos
    public function tokenizeCard(string $checkoutId): string
    // Retorna token do cartão após pagamento bem-sucedido

    // Validação
    public function validateCredentials(?int $userId = null): bool
    // GET /v0.1/me — testa conexão

    // Webhook
    public function registerWebhook(Order $order, string $token): string
    // POST /v0.1/me/webhooks — registra URL dinâmica
    // Retorna URL registrada

    public function validateWebhookSignature(string $payload, string $signature, string $secret): bool

    // Helpers privados
    private function getSellerConfig(Order $order): array
    private function headers(string $apiKey): array
    private function post(string $endpoint, array $data, string $apiKey): array
    private function get(string $endpoint, string $apiKey): array
}
```

#### `app/Services/Payment/SumUpWebhookProcessor.php`
Processa eventos de webhook SumUp de forma idempotente.

```php
class SumUpWebhookProcessor
{
    public function process(array $payload, string $webhookToken): void
    // Dispatcher principal de eventos

    private function handlePaymentSucceeded(array $payload): void
    // Chama PaymentWebhookController::processPaidOrder()

    private function handlePaymentFailed(array $payload): void
    private function handlePaymentRefunded(array $payload): void
    private function handleSubscriptionRenewed(array $payload): void
    private function handleSubscriptionCancelled(array $payload): void

    private function findOrderByWebhookToken(string $token): ?Order
    private function isAlreadyProcessed(Order $order, string $eventType): bool
    // Idempotência: verifica metadata do Order
}
```

### Controllers

#### `app/Http/Controllers/PaymentWebhookController.php` (alteração)
Adicionar método `sumup()`:

```php
// Rota: POST /webhook/sumup/{order_id}/{token}
public function sumup(Request $request, int $orderId, string $token): Response
{
    // 1. Busca SumUpTransaction pelo token
    // 2. Valida assinatura HMAC
    // 3. Loga em sumup_webhook_logs
    // 4. Despacha para SumUpWebhookProcessor
    // 5. Retorna HTTP 200
}
```

#### `app/Http/Controllers/Panel/Admin/SumUpController.php` (novo)

```php
class SumUpController extends Controller
{
    public function index(Request $request): View
    // Lista transações SumUp com filtros

    public function show(SumUpTransaction $transaction): View
    // Detalhes de uma transação

    public function refund(Request $request, Order $order): RedirectResponse
    // Inicia reembolso via OrderRefundService

    public function report(Request $request): View
    // Relatório de vendas com totais e gráfico

    public function exportReport(Request $request): StreamedResponse
    // Exporta relatório em CSV

    public function testConnection(): JsonResponse
    // Testa credenciais SumUp configuradas
}
```

### Models

#### `app/Models/SumUpTransaction.php` (novo)
```php
class SumUpTransaction extends Model
{
    protected $fillable = [
        'order_id', 'checkout_id', 'transaction_id',
        'status', 'payment_type', 'amount', 'currency',
        'webhook_token', 'webhook_url', 'raw_response'
    ];

    protected $casts = ['raw_response' => 'array', 'amount' => 'decimal:2'];

    public function order(): BelongsTo
    public function webhookLogs(): HasMany
}
```

#### `app/Models/SumUpWebhookLog.php` (novo)
```php
class SumUpWebhookLog extends Model
{
    protected $fillable = [
        'order_id', 'event_type', 'payload',
        'signature', 'is_valid', 'processed_at'
    ];
    protected $casts = ['payload' => 'array', 'is_valid' => 'boolean'];
}
```

#### `app/Models/SumUpSavedCard.php` (novo)
```php
class SumUpSavedCard extends Model
{
    protected $fillable = [
        'user_id', 'token', 'last_four',
        'brand', 'expires_at', 'is_default'
    ];

    public function user(): BelongsTo
}
```

---

## Alterações em Arquivos Existentes

### `app/Services/OrderRefundService.php`
Adicionar `'sumup'` no `match` de `refundOnGateway()`:

```php
private function refundOnGateway(Order $order, float $requestedAmount, bool $isPartial): array
{
    return match ((string) $order->gateway) {
        'mercadopago' => app(MercadoPagoService::class)
            ->refundPayment($order, $isPartial ? $requestedAmount : null),
        'sumup' => app(SumUpService::class)
            ->refundPayment($order, $isPartial ? $requestedAmount : null),
        default => throw new RuntimeException('Gateway nao suportado para reembolso automatico.'),
    };
}
```

### `app/Http/Controllers/CheckoutController.php`
Adicionar branch SumUp em `processPayment()`:

```php
// Detecta gateway selecionado pelo usuário
$gateway = $request->input('gateway', 'mercadopago');

if ($gateway === 'sumup') {
    return $this->processSumUpPayment($request, $order);
}
// ... lógica MP existente
```

### `app/Models/GatewayAccount.php`
Adicionar método `resolveForSellerSumUp()` seguindo o mesmo padrão de `resolveForSeller()`.

### `config/payments.php`
Adicionar seção SumUp:
```php
'sumup' => [
    'api_key'       => env('SUMUP_API_KEY', ''),
    'client_id'     => env('SUMUP_CLIENT_ID', ''),
    'client_secret' => env('SUMUP_CLIENT_SECRET', ''),
    'merchant_code' => env('SUMUP_MERCHANT_CODE', ''),
    'env'           => env('SUMUP_ENV', 'sandbox'),
    'fee_percentage'=> env('SUMUP_FEE_PERCENTAGE', 2.75),
    'fee_fixed'     => env('SUMUP_FEE_FIXED', 0),
    'pass_fee'      => env('SUMUP_PASS_FEE', false),
    'webhook_secret'=> env('SUMUP_WEBHOOK_SECRET', ''),
],
```

---

## Rotas

```php
// Webhook público (sem auth)
Route::post('/webhook/sumup/{orderId}/{token}',
    [PaymentWebhookController::class, 'sumup']
)->name('webhook.sumup');

// Painel Admin — dentro do grupo panel.admin
Route::prefix('sumup')->name('sumup.')->group(function () {
    Route::get('/',                    [SumUpController::class, 'index'])->name('index');
    Route::get('/{transaction}',       [SumUpController::class, 'show'])->name('show');
    Route::post('/orders/{order}/refund', [SumUpController::class, 'refund'])->name('refund');
    Route::get('/report',              [SumUpController::class, 'report'])->name('report');
    Route::get('/report/export',       [SumUpController::class, 'exportReport'])->name('report.export');
    Route::post('/test-connection',    [SumUpController::class, 'testConnection'])->name('test-connection');
});

// Cartões salvos — painel do membro
Route::prefix('painel/cartoes-sumup')->name('panel.sumup.cards.')->middleware(['auth'])->group(function () {
    Route::get('/',           [SumUpCardController::class, 'index'])->name('index');
    Route::delete('/{card}',  [SumUpCardController::class, 'destroy'])->name('destroy');
    Route::post('/{card}/default', [SumUpCardController::class, 'setDefault'])->name('default');
});
```

---

## Fluxo de Pagamento com Cartão

```
1. Usuário seleciona "SumUp" no checkout
2. Frontend carrega SumUp.js
3. SumUp.js renderiza formulário de cartão (iframe seguro)
4. Usuário preenche dados do cartão
5. SumUp.js tokeniza o cartão → retorna card_token
6. Frontend envia card_token + order_id para POST /checkout/process-payment
7. CheckoutController::processSumUpPayment()
   a. Cria Order com gateway='sumup', status='pending'
   b. SumUpService::createCheckout(order) → obtém checkout_id
   c. SumUpService::registerWebhook(order, token) → URL dinâmica
   d. SumUpService::processCardCheckout(checkout_id, card_token)
   e. Salva SumUpTransaction
8. Resposta imediata ao frontend (pending ou success)
9. Webhook SumUp → POST /webhook/sumup/{order_id}/{token}
   a. Valida assinatura
   b. processPaidOrder() → matrícula, ingresso, etc.
```

## Fluxo de Pagamento PIX

```
1. Usuário seleciona "PIX" no checkout SumUp
2. POST /checkout/process-payment com payment_type=pix
3. SumUpService::processPixCheckout(order)
   a. Cria checkout SumUp com tipo PIX
   b. Retorna QR Code (base64) + código copia-e-cola
4. Frontend exibe QR Code inline
5. Usuário paga no app do banco
6. Webhook SumUp confirma pagamento
7. processPaidOrder() executa fulfillments
```

---

## Views

### Painel Admin

- `resources/views/panel/admin/sumup/index.blade.php` — listagem de transações
- `resources/views/panel/admin/sumup/show.blade.php` — detalhes da transação
- `resources/views/panel/admin/sumup/report.blade.php` — relatório com gráfico
- `resources/views/panel/admin/sumup/partials/transaction-row.blade.php`

### Checkout (partials)

- `resources/views/partials/checkout/sumup-card-form.blade.php` — formulário de cartão via SumUp.js
- `resources/views/partials/checkout/sumup-pix.blade.php` — QR Code PIX inline
- `resources/views/partials/checkout/sumup-saved-cards.blade.php` — cartões salvos

### Painel do Membro

- `resources/views/panel/sumup-cards/index.blade.php` — gerenciar cartões salvos

---

## Sidebar do Admin

Adicionar item "SumUp" no menu de configurações do sidebar, visível apenas para `isAdmin()`, com ícone `fas fa-credit-card` e rota `panel.admin.sumup.index`.

---

## Segurança

- **Webhook token**: gerado com `Str::random(64)` por transação, armazenado em `sumup_transactions.webhook_token`
- **Assinatura HMAC**: validada com `hash_hmac('sha256', $payload, $webhookSecret)`
- **Dados de cartão**: nunca passam pelo servidor — apenas o token SumUp
- **API Keys**: armazenadas em `gateway_accounts` (criptografadas) ou `.env`
- **Idempotência**: verificada via `sumup_transactions.status` antes de processar webhook

---

## Propriedades de Correção (PBT)

1. `createCheckout` sempre deve criar um registro `SumUpTransaction` antes de retornar.
2. `processPaidOrder` para gateway `sumup` deve executar os mesmos fulfillments que para `mercadopago`.
3. Webhook com token inválido nunca deve alterar o status de nenhum `Order`.
4. Reembolso SumUp nunca deve exceder `order.charged_amount`.
5. Dois webhooks `payment.succeeded` para o mesmo `order_id` devem resultar em exatamente um fulfillment.
