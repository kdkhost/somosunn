# Tarefas de Implementação — Integração Gateway SumUp

## Tasks

- [x] 1. Infraestrutura base (migrations, models, config)
  - [x] 1.1 Criar migration `sumup_transactions`
  - [x] 1.2 Criar migration `sumup_webhook_logs`
  - [x] 1.3 Criar migration `sumup_saved_cards`
  - [x] 1.4 Criar migration para adicionar `sumup_subscription_id` em `subscriptions`
  - [x] 1.5 Criar model `SumUpTransaction`
  - [x] 1.6 Criar model `SumUpWebhookLog`
  - [x] 1.7 Criar model `SumUpSavedCard`
  - [x] 1.8 Adicionar seção `sumup` em `config/payments.php`
  - [x] 1.9 Adicionar variáveis SumUp em `.env.example`

- [x] 2. SumUpService — comunicação com a API
  - [x] 2.1 Criar `app/Services/Payment/SumUpService.php` com métodos base (headers, post, get, apiKey, merchantCode)
  - [x] 2.2 Implementar `createCheckout()` — cria checkout na API SumUp e registra `SumUpTransaction`
  - [x] 2.3 Implementar `processCardCheckout()` — processa pagamento com token de cartão
  - [x] 2.4 Implementar `processPixCheckout()` — cria checkout PIX e retorna QR Code
  - [x] 2.5 Implementar `registerWebhook()` — registra URL dinâmica por transação na API SumUp
  - [x] 2.6 Implementar `refundPayment()` — reembolso total e parcial
  - [x] 2.7 Implementar `createSubscription()`, `cancelSubscription()`, `getSubscription()`
  - [x] 2.8 Implementar `validateCredentials()` — testa conexão com a API
  - [x] 2.9 Implementar `validateWebhookSignature()` — validação HMAC
  - [x] 2.10 Implementar `getSellerConfig()` — resolução de credenciais (vendedor vs global)
  - [x] 2.11 Adicionar método `resolveForSellerSumUp()` em `GatewayAccount`

- [x] 3. Webhook — recebimento e processamento
  - [x] 3.1 Criar `app/Services/Payment/SumUpWebhookProcessor.php` com dispatcher de eventos
  - [x] 3.2 Implementar `handlePaymentSucceeded()` — chama `processPaidOrder()` existente
  - [x] 3.3 Implementar `handlePaymentFailed()`, `handlePaymentRefunded()`
  - [x] 3.4 Implementar `handleSubscriptionRenewed()`, `handleSubscriptionCancelled()`
  - [x] 3.5 Implementar idempotência — verificar `SumUpTransaction.status` antes de processar
  - [x] 3.6 Adicionar método `sumup()` em `PaymentWebhookController`
  - [x] 3.7 Adicionar rota `POST /webhook/sumup/{orderId}/{token}` em `routes/web.php`

- [-] 4. Reembolso — integração com sistema existente
  - [x] 4.1 Adicionar case `'sumup'` no `match` de `OrderRefundService::refundOnGateway()`
  - [ ] 4.2 Testar reembolso total via painel admin
  - [ ] 4.3 Testar reembolso parcial via painel admin

- [ ] 5. Checkout integrado — cartão e PIX
  - [ ] 5.1 Criar partial `resources/views/partials/checkout/sumup-card-form.blade.php` com SumUp.js
  - [ ] 5.2 Criar partial `resources/views/partials/checkout/sumup-pix.blade.php` com QR Code inline
  - [ ] 5.3 Criar partial `resources/views/partials/checkout/sumup-saved-cards.blade.php`
  - [ ] 5.4 Adicionar método `processSumUpPayment()` em `CheckoutController`
  - [ ] 5.5 Exibir opção SumUp nos checkouts: cursos, eventos, mentorias, marketplace, assinaturas
  - [ ] 5.6 Implementar polling de status PIX no frontend (verificar pagamento a cada 5s)

- [ ] 6. Tokenização de cartão (cartões salvos)
  - [ ] 6.1 Salvar token do cartão em `sumup_saved_cards` após pagamento bem-sucedido
  - [ ] 6.2 Criar `app/Http/Controllers/Panel/SumUpCardController.php`
  - [ ] 6.3 Criar view `resources/views/panel/sumup-cards/index.blade.php`
  - [ ] 6.4 Adicionar rotas de cartões salvos em `routes/web.php`

- [x] 7. Painel Admin — transações e relatórios
  - [x] 7.1 Criar `app/Http/Controllers/Panel/Admin/SumUpController.php`
  - [x] 7.2 Criar view `resources/views/panel/admin/sumup/index.blade.php` — listagem com filtros
  - [x] 7.3 Criar view `resources/views/panel/admin/sumup/show.blade.php` — detalhes da transação
  - [x] 7.4 Criar view `resources/views/panel/admin/sumup/report.blade.php` — relatório com gráfico
  - [x] 7.5 Implementar exportação CSV do relatório
  - [x] 7.6 Adicionar rotas SumUp no grupo `panel.admin` em `routes/web.php`
  - [x] 7.7 Adicionar item "SumUp" no sidebar do painel admin (`panel/partials/sidebar.blade.php`)

- [ ] 8. Configurações SumUp no Admin
  - [ ] 8.1 Adicionar campos SumUp na página de configurações de gateway (`admin/settings`)
  - [ ] 8.2 Implementar botão "Testar Conexão SumUp" nas configurações
  - [ ] 8.3 Adicionar toggle para ativar/desativar SumUp globalmente
  - [ ] 8.4 Adicionar campos de taxa SumUp (percentual, fixo, repassar ao comprador)

- [ ] 9. Assinaturas recorrentes
  - [ ] 9.1 Integrar `SumUpService::createSubscription()` no `SubscriptionController`
  - [ ] 9.2 Integrar `SumUpService::cancelSubscription()` no cancelamento de planos
  - [ ] 9.3 Processar renovação automática via `handleSubscriptionRenewed()`

- [ ] 10. Notificações e e-mails
  - [ ] 10.1 Disparar e-mail de confirmação de pagamento após `processPaidOrder()` para SumUp
  - [ ] 10.2 Disparar e-mail de falha de pagamento via `handlePaymentFailed()`
  - [ ] 10.3 Notificar admin via log quando webhook falhar no processamento

- [ ] 11. Testes e validação
  - [ ] 11.1 Testar fluxo completo de pagamento com cartão em sandbox SumUp
  - [ ] 11.2 Testar fluxo completo de pagamento PIX em sandbox SumUp
  - [ ] 11.3 Testar recebimento e processamento de webhooks
  - [ ] 11.4 Testar reembolso total e parcial
  - [ ] 11.5 Testar idempotência de webhooks duplicados
  - [ ] 11.6 Verificar que dados de cartão nunca são logados
  - [ ] 11.7 Executar `php tools/check-no-bom.php` em todos os arquivos criados

- [ ] 12. Deploy e configuração de produção
  - [ ] 12.1 Executar migrations em produção
  - [ ] 12.2 Configurar variáveis de ambiente SumUp no servidor
  - [ ] 12.3 Publicar no Git
