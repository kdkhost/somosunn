# Bugfix Requirements Document

## Introduction

O gateway de pagamento SumUp foi implementado no sistema seguindo o mesmo padrão arquitetural do Mercado Pago, incluindo migrations, models, services e controllers administrativos. No entanto, o gateway SumUp não está sendo carregado no checkout de eventos porque o sistema não verifica qual gateway o vendedor/organizador configurou como ativo.

**Regra de Negócio do Sistema:**
- Cada usuário/vendedor deve escolher **APENAS UM gateway ativo** (Mercado Pago OU SumUp)
- Somente um gateway pode ficar ativo por usuário do sistema
- O usuário escolhe qual gateway irá utilizar para cobrar suas vendas nas configurações
- **Não deve haver seleção de gateway no checkout** - o gateway já está pré-definido pelo vendedor

**Problema Atual:**
O `EventReservationController` está hardcoded para usar apenas Mercado Pago, sem verificar qual gateway o vendedor configurou como ativo. Isso impede que vendedores que configuraram SumUp como gateway ativo recebam pagamentos através deste gateway para vendas de ingressos de eventos.

## Bug Analysis

### 1. Current Behavior (Defect)

1.1 WHEN um evento pago é acessado no checkout (`EventReservationController::checkout`) THEN o sistema verifica apenas se o Mercado Pago está habilitado (`$gateways['mpEnabled']`) e define `$preferredGateway = 'mercadopago'`, ignorando completamente se o vendedor configurou SumUp como gateway ativo

1.2 WHEN um usuário reserva um ingresso pago (`EventReservationController::reserve`) THEN o sistema define `$gatewayProvider = 'mercadopago'` de forma hardcoded, sem verificar qual gateway o vendedor configurou como ativo

1.3 WHEN o pedido (Order) é criado para um evento pago THEN o campo `gateway` é sempre definido como `'mercadopago'`, mesmo que o vendedor tenha configurado SumUp como gateway ativo

1.4 WHEN a preferência de pagamento é criada THEN o sistema chama apenas `$mpService->createPreference()` sem verificar qual gateway está ativo para aquele vendedor, impossibilitando o uso do SumUp

1.5 WHEN a view de checkout de eventos é renderizada THEN apenas as variáveis `$mpEnabled` e `$preferredGateway` são passadas, sem informações sobre qual gateway está ativo para aquele vendedor

1.6 WHEN o sistema verifica disponibilidade de pagamento THEN ele verifica apenas `$gateways['mpEnabled']`, sem verificar se o vendedor tem SumUp configurado como gateway ativo

### 2. Expected Behavior (Correct)

2.1 WHEN um evento pago é acessado no checkout THEN o sistema SHALL verificar qual gateway o vendedor/organizador configurou como ativo (Mercado Pago OU SumUp) usando `GatewayAccount::resolveActiveGatewayForSeller()` e retornar o gateway ativo

2.2 WHEN um usuário reserva um ingresso pago THEN o sistema SHALL usar automaticamente o gateway que o vendedor configurou como ativo, sem aceitar parâmetro de seleção de gateway na requisição

2.3 WHEN o pedido (Order) é criado para um evento pago THEN o campo `gateway` SHALL ser definido com o valor do gateway ativo do vendedor (`'mercadopago'` ou `'sumup'`)

2.4 WHEN a preferência/checkout de pagamento é criada THEN o sistema SHALL verificar qual gateway está ativo para aquele vendedor e chamar o serviço apropriado: `$mpService->createPreference()` para Mercado Pago ou `$sumUpService->createCheckout()` para SumUp

2.5 WHEN a view de checkout de eventos é renderizada THEN o sistema SHALL passar variáveis indicando qual gateway está ativo para aquele vendedor (`activeGateway`, `gatewayEnabled`) sem permitir seleção pelo comprador

2.6 WHEN o SumUp está configurado como gateway ativo e o checkout é criado THEN o sistema SHALL renderizar a view `checkout.transparent` com os dados do checkout SumUp (checkout_id, publicKey do SumUp) em vez dos dados do Mercado Pago

2.7 WHEN o sistema verifica disponibilidade de pagamento THEN ele SHALL verificar se o vendedor tem pelo menos um gateway ativo configurado (Mercado Pago OU SumUp), não apenas Mercado Pago

2.8 WHEN o modelo `GatewayAccount` é consultado THEN ele SHALL fornecer um método para retornar o gateway ativo do vendedor, verificando qual registro tem `enabled = true` na tabela `gateway_accounts`

### 3. Unchanged Behavior (Regression Prevention)

3.1 WHEN um evento pago tem Mercado Pago configurado como gateway ativo THEN o sistema SHALL CONTINUE TO processar o pagamento via Mercado Pago exatamente como antes

3.2 WHEN um evento gratuito é processado THEN o sistema SHALL CONTINUE TO criar o pedido com `gateway = 'free'` e liquidar imediatamente sem envolver gateways de pagamento

3.3 WHEN credenciais de gateway são resolvidas THEN o sistema SHALL CONTINUE TO priorizar credenciais do vendedor sobre credenciais globais da plataforma

3.4 WHEN um pedido de evento é criado THEN o sistema SHALL CONTINUE TO calcular taxas da plataforma, aplicar cupons de desconto e criar itens do pedido da mesma forma

3.5 WHEN o checkout é concluído com sucesso THEN o sistema SHALL CONTINUE TO criar registros de evento (`EventRegistration`), gerar códigos de ingresso e executar notificações

3.6 WHEN a view `checkout.transparent` é renderizada para Mercado Pago THEN o sistema SHALL CONTINUE TO passar `preferenceId` e `publicKey` do Mercado Pago sem alterações

3.7 WHEN o método `GatewayAccount::resolveForSeller()` é chamado THEN o sistema SHALL CONTINUE TO retornar as mesmas chaves e estrutura de dados para Mercado Pago (`mpEnabled`, `mpPublicKey`, `source`) para manter compatibilidade com outros fluxos de checkout

3.8 WHEN múltiplos gateways estão cadastrados para um vendedor mas apenas um está com `enabled = true` THEN o sistema SHALL CONTINUE TO usar apenas o gateway marcado como ativo
