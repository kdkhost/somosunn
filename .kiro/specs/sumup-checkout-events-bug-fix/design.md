# SumUp Checkout Events Bug Fix Design

## Overview

O gateway de pagamento SumUp foi implementado no sistema seguindo o mesmo padrão arquitetural do Mercado Pago, mas não está sendo carregado no checkout de eventos porque o `EventReservationController` está hardcoded para usar apenas Mercado Pago. Este bugfix implementa a detecção dinâmica do gateway ativo configurado pelo vendedor/organizador do evento, permitindo que vendedores que configuraram SumUp como gateway ativo recebam pagamentos através deste gateway.

A correção segue a regra de negócio do sistema: cada vendedor escolhe **APENAS UM gateway ativo** (Mercado Pago OU SumUp) nas configurações, e o checkout deve usar automaticamente o gateway configurado pelo vendedor, sem permitir seleção pelo comprador.

## Glossary

- **Bug_Condition (C)**: A condição que dispara o bug - quando um evento pago tem SumUp configurado como gateway ativo do vendedor, mas o sistema usa Mercado Pago
- **Property (P)**: O comportamento desejado - o sistema deve detectar e usar o gateway ativo configurado pelo vendedor (SumUp ou Mercado Pago)
- **Preservation**: Comportamento existente de eventos com Mercado Pago e eventos gratuitos que deve permanecer inalterado
- **EventReservationController**: O controller em `app/Http/Controllers/EventReservationController.php` que gerencia o checkout de eventos
- **GatewayAccount**: O modelo em `app/Models/GatewayAccount.php` que armazena credenciais de gateways de pagamento
- **Active Gateway**: O gateway marcado com `enabled = true` na tabela `gateway_accounts` para um vendedor específico
- **SumUpService**: O serviço em `app/Services/Payment/SumUpService.php` que encapsula comunicação com a API SumUp
- **MercadoPagoService**: O serviço em `app/Services/Payment/MercadoPagoService.php` que encapsula comunicação com a API Mercado Pago

## Bug Details

### Bug Condition

O bug manifesta-se quando um vendedor/organizador de evento configurou SumUp como seu gateway ativo, mas o sistema ignora essa configuração e tenta processar o pagamento via Mercado Pago. O `EventReservationController` possui lógica hardcoded que sempre define `$gatewayProvider = 'mercadopago'` e sempre chama `$mpService->createPreference()`, sem verificar qual gateway o vendedor configurou como ativo.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input of type {event: Event, seller: User}
  OUTPUT: boolean
  
  RETURN input.event.effective_price > 0
         AND sellerHasActiveGateway(input.seller, 'sumup')
         AND NOT systemUsesSellerActiveGateway(input.event, input.seller)
END FUNCTION

FUNCTION sellerHasActiveGateway(seller, provider)
  RETURN EXISTS (
    SELECT * FROM gateway_accounts 
    WHERE user_id = seller.id 
    AND provider = provider 
    AND enabled = true
  )
END FUNCTION

FUNCTION systemUsesSellerActiveGateway(event, seller)
  // Verifica se o sistema detecta e usa o gateway ativo do vendedor
  RETURN gatewayDetectionLogicExists() 
         AND orderCreatedWithCorrectGateway()
         AND checkoutServiceMatchesGateway()
END FUNCTION
```

### Examples

**Exemplo 1: Vendedor com SumUp ativo - Bug manifesta**
- Vendedor configurou SumUp como gateway ativo (`gateway_accounts.provider = 'sumup'`, `enabled = true`)
- Evento pago com `effective_price = 100.00`
- **Comportamento atual (incorreto)**: Sistema define `$gatewayProvider = 'mercadopago'`, cria Order com `gateway = 'mercadopago'`, chama `$mpService->createPreference()`
- **Comportamento esperado**: Sistema deve detectar SumUp ativo, definir `$gatewayProvider = 'sumup'`, criar Order com `gateway = 'sumup'`, chamar `$sumUpService->createCheckout()`

**Exemplo 2: Vendedor com Mercado Pago ativo - Sem bug**
- Vendedor configurou Mercado Pago como gateway ativo (`gateway_accounts.provider = 'mercadopago'`, `enabled = true`)
- Evento pago com `effective_price = 50.00`
- **Comportamento atual (correto)**: Sistema define `$gatewayProvider = 'mercadopago'`, cria Order com `gateway = 'mercadopago'`, chama `$mpService->createPreference()`
- **Comportamento esperado**: Mesmo comportamento (preservação)

**Exemplo 3: Evento gratuito - Sem bug**
- Evento com `effective_price = 0`
- **Comportamento atual (correto)**: Sistema cria Order com `gateway = 'free'`, liquida imediatamente
- **Comportamento esperado**: Mesmo comportamento (preservação)

**Exemplo 4: Vendedor sem gateway configurado - Edge case**
- Vendedor não tem nenhum gateway ativo configurado
- Evento pago com `effective_price = 75.00`
- **Comportamento esperado**: Sistema deve retornar erro informando que o organizador não configurou método de pagamento (já implementado, deve ser preservado)

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Eventos com Mercado Pago configurado como gateway ativo devem continuar processando pagamentos via Mercado Pago exatamente como antes
- Eventos gratuitos (`effective_price = 0`) devem continuar criando pedidos com `gateway = 'free'` e liquidando imediatamente
- A priorização de credenciais do vendedor sobre credenciais globais da plataforma deve continuar funcionando
- O cálculo de taxas da plataforma, aplicação de cupons de desconto e criação de itens do pedido devem permanecer inalterados
- A criação de registros de evento (`EventRegistration`), geração de códigos de ingresso e execução de notificações devem continuar funcionando
- A view `checkout.transparent` para Mercado Pago deve continuar recebendo `preferenceId` e `publicKey` do Mercado Pago
- O método `GatewayAccount::resolveForSeller()` deve continuar retornando as mesmas chaves e estrutura de dados para Mercado Pago

**Scope:**
Todas as transações que NÃO envolvem vendedores com SumUp configurado como gateway ativo devem ser completamente inalteradas por este fix. Isso inclui:
- Todos os eventos com Mercado Pago ativo
- Todos os eventos gratuitos
- Todos os outros fluxos de checkout (cursos, mentorias, marketplace) que já usam Mercado Pago
- Validações de credenciais e resolução de configurações de gateway

## Hypothesized Root Cause

Baseado na análise do código e na descrição do bug, as causas mais prováveis são:

1. **Hardcoded Gateway Selection**: O método `EventReservationController::reserve()` define `$gatewayProvider = 'mercadopago'` de forma hardcoded na linha ~127, sem verificar qual gateway o vendedor configurou como ativo. Isso força todos os eventos pagos a usarem Mercado Pago.

2. **Missing Gateway Detection Logic**: O método `EventReservationController::checkout()` verifica apenas `$gateways['mpEnabled']` e define `$preferredGateway = 'mercadopago'` sem consultar se o vendedor tem SumUp ativo. Não existe lógica para detectar qual gateway está ativo.

3. **Incomplete Gateway Resolution**: O método `GatewayAccount::resolveForSeller()` retorna apenas informações do Mercado Pago (`mpEnabled`, `mpPublicKey`). Embora exista `GatewayAccount::resolveForSellerSumUp()`, ele não é chamado no fluxo de eventos.

4. **Hardcoded Service Call**: O método `EventReservationController::reserve()` sempre chama `$mpService->createPreference()` na linha ~640, sem verificar qual gateway está ativo e sem branch condicional para chamar `$sumUpService->createCheckout()` quando SumUp está ativo.

5. **Missing View Logic**: A view `checkout.transparent` é sempre renderizada com dados do Mercado Pago (`preferenceId`, `publicKey`), sem lógica para renderizar com dados do SumUp (`checkout_id`, `sumupPublicKey`) quando SumUp está ativo.

6. **Incomplete Gateway Account Model**: O modelo `GatewayAccount` não possui um método unificado que retorna o gateway ativo do vendedor (independente de ser Mercado Pago ou SumUp), forçando cada controller a implementar sua própria lógica de detecção.

## Correctness Properties

Property 1: Bug Condition - Active Gateway Detection

_For any_ evento pago onde o vendedor/organizador configurou SumUp como gateway ativo (`gateway_accounts.provider = 'sumup'` AND `enabled = true`), o sistema SHALL detectar automaticamente o gateway ativo usando `GatewayAccount::resolveActiveGatewayForSeller()`, criar o pedido (Order) com `gateway = 'sumup'`, chamar `SumUpService::createCheckout()` para criar o checkout SumUp, e renderizar a view `checkout.transparent` com os dados do checkout SumUp (`checkout_id`, `publicKey` do SumUp).

**Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8**

Property 2: Preservation - Mercado Pago and Free Events

_For any_ evento que NÃO tem SumUp configurado como gateway ativo (eventos com Mercado Pago ativo OU eventos gratuitos), o sistema SHALL produzir exatamente o mesmo comportamento que o código original, preservando o processamento via Mercado Pago para eventos pagos com Mercado Pago ativo, e preservando a liquidação imediata com `gateway = 'free'` para eventos gratuitos.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8**

## Fix Implementation

### Changes Required

Assumindo que nossa análise de causa raiz está correta:

**File**: `app/Models/GatewayAccount.php`

**Function**: Adicionar novo método `resolveActiveGatewayForSeller()`

**Specific Changes**:
1. **Criar método unificado de detecção de gateway ativo**: Adicionar método `public static function resolveActiveGatewayForSeller(int $sellerId): array` que:
   - Consulta `gateway_accounts` WHERE `user_id = $sellerId` AND `enabled = true`
   - Retorna array com estrutura: `['provider' => 'sumup'|'mercadopago'|null, 'enabled' => bool, 'config' => array, 'source' => 'seller'|'global']`
   - Prioriza credenciais do vendedor sobre credenciais globais
   - Retorna `null` se nenhum gateway estiver ativo

2. **Manter compatibilidade com métodos existentes**: Os métodos `resolveForSeller()` e `resolveForSellerSumUp()` devem continuar existindo para manter compatibilidade com outros fluxos de checkout (cursos, mentorias, marketplace)

**File**: `app/Http/Controllers/EventReservationController.php`

**Function**: `checkout()` e `reserve()`

**Specific Changes**:
3. **Modificar método `checkout()`**: Substituir lógica hardcoded de detecção de gateway:
   - Substituir chamada `GatewayAccount::resolveForSeller()` por `GatewayAccount::resolveActiveGatewayForSeller()`
   - Detectar qual gateway está ativo (`$activeGateway = $gatewayConfig['provider']`)
   - Passar `$activeGateway` para a view em vez de `$preferredGateway`
   - Verificar se pelo menos um gateway está ativo (Mercado Pago OU SumUp) em vez de verificar apenas `$mpEnabled`

4. **Modificar método `reserve()`**: Implementar detecção dinâmica de gateway:
   - Substituir `$gatewayProvider = 'mercadopago'` hardcoded por detecção dinâmica usando `GatewayAccount::resolveActiveGatewayForSeller()`
   - Adicionar branch condicional: `if ($gatewayProvider === 'sumup') { return $this->processSumUpPayment(...); }`
   - Manter branch existente para Mercado Pago: `if ($gatewayProvider === 'mercadopago') { return $this->processMercadoPagoPayment(...); }`
   - Criar Order com `gateway` dinâmico baseado no gateway ativo detectado

5. **Criar método privado `processSumUpPayment()`**: Extrair lógica de processamento SumUp:
   - Chamar `$sumUpService->createCheckout($order, $options)`
   - Salvar `checkout_id` e `webhook_token` nos metadados do Order
   - Resolver `publicKey` do SumUp (credenciais do vendedor ou globais)
   - Renderizar view `checkout.transparent` com dados do SumUp

6. **Refatorar lógica existente de Mercado Pago**: Extrair para método privado `processMercadoPagoPayment()` para manter simetria e facilitar manutenção futura

7. **Adicionar injeção de dependência**: Adicionar `SumUpService $sumUpService` como parâmetro do método `reserve()` ao lado de `MercadoPagoService $mpService`

**File**: `resources/views/checkout/transparent.blade.php`

**Specific Changes**:
8. **Adicionar detecção de gateway na view**: Verificar qual gateway está sendo usado:
   - Se `$order->gateway === 'sumup'`: renderizar formulário SumUp com `checkout_id` e carregar SumUp.js
   - Se `$order->gateway === 'mercadopago'`: renderizar formulário Mercado Pago existente com `preferenceId` (preservação)
   - Adicionar partial `@include('partials.checkout.sumup-card-form')` quando SumUp está ativo

**File**: `resources/views/partials/checkout/sumup-card-form.blade.php` (novo arquivo)

**Specific Changes**:
9. **Criar partial para formulário SumUp**: Implementar formulário de cartão SumUp:
   - Carregar SumUp.js SDK
   - Renderizar iframe seguro de captura de cartão
   - Implementar tokenização de cartão via SumUp.js
   - Enviar `card_token` para backend após tokenização

## Testing Strategy

### Validation Approach

A estratégia de testes segue uma abordagem de duas fases: primeiro, demonstrar o bug no código não corrigido através de testes exploratórios, depois verificar que a correção funciona corretamente e preserva o comportamento existente.

### Exploratory Bug Condition Checking

**Goal**: Demonstrar o bug ANTES de implementar a correção. Confirmar ou refutar a análise de causa raiz. Se refutarmos, precisaremos re-hipotetisar.

**Test Plan**: Escrever testes que simulam um vendedor com SumUp configurado como gateway ativo tentando criar um checkout de evento pago. Executar esses testes no código NÃO CORRIGIDO para observar falhas e entender a causa raiz.

**Test Cases**:
1. **Vendor with SumUp Active - Order Gateway Mismatch**: Criar evento pago com vendedor que tem SumUp ativo, reservar ingresso, verificar que Order é criado com `gateway = 'mercadopago'` em vez de `'sumup'` (falhará no código não corrigido - demonstra o bug)
2. **Vendor with SumUp Active - Wrong Service Called**: Criar evento pago com vendedor que tem SumUp ativo, reservar ingresso, verificar que `MercadoPagoService::createPreference()` é chamado em vez de `SumUpService::createCheckout()` (falhará no código não corrigido - demonstra o bug)
3. **Vendor with SumUp Active - Wrong View Data**: Criar evento pago com vendedor que tem SumUp ativo, reservar ingresso, verificar que view recebe `preferenceId` do Mercado Pago em vez de `checkout_id` do SumUp (falhará no código não corrigido - demonstra o bug)
4. **Vendor with Both Gateways - Only One Active**: Criar vendedor com ambos gateways cadastrados mas apenas SumUp com `enabled = true`, verificar que sistema usa SumUp (pode falhar no código não corrigido)

**Expected Counterexamples**:
- Order criado com `gateway = 'mercadopago'` quando deveria ser `'sumup'`
- Chamada para `MercadoPagoService::createPreference()` quando deveria chamar `SumUpService::createCheckout()`
- View renderizada com dados do Mercado Pago quando deveria ter dados do SumUp
- Possíveis causas: falta de detecção de gateway ativo, lógica hardcoded, ausência de branch condicional

### Fix Checking

**Goal**: Verificar que para todas as entradas onde a condição de bug é verdadeira, a função corrigida produz o comportamento esperado.

**Pseudocode:**
```
FOR ALL input WHERE isBugCondition(input) DO
  result := EventReservationController_fixed::reserve(input)
  ASSERT expectedBehavior(result)
END FOR

FUNCTION expectedBehavior(result)
  RETURN result.order.gateway === 'sumup'
         AND result.checkout_created_via_sumup_service === true
         AND result.view_data.contains('checkout_id')
         AND result.view_data.contains('sumupPublicKey')
END FUNCTION
```

**Test Cases**:
1. **SumUp Active - Correct Order Gateway**: Vendedor com SumUp ativo, evento pago, verificar Order criado com `gateway = 'sumup'`
2. **SumUp Active - Correct Service Called**: Vendedor com SumUp ativo, evento pago, verificar que `SumUpService::createCheckout()` é chamado
3. **SumUp Active - Correct View Data**: Vendedor com SumUp ativo, evento pago, verificar que view recebe `checkout_id` e `publicKey` do SumUp
4. **SumUp Active - SumUpTransaction Created**: Vendedor com SumUp ativo, evento pago, verificar que registro `SumUpTransaction` é criado
5. **SumUp Active - Webhook Registered**: Vendedor com SumUp ativo, evento pago, verificar que webhook dinâmico é registrado na API SumUp

### Preservation Checking

**Goal**: Verificar que para todas as entradas onde a condição de bug NÃO é verdadeira, a função corrigida produz o mesmo resultado que a função original.

**Pseudocode:**
```
FOR ALL input WHERE NOT isBugCondition(input) DO
  ASSERT EventReservationController_original::reserve(input) 
         = EventReservationController_fixed::reserve(input)
END FOR
```

**Testing Approach**: Property-based testing é recomendado para preservation checking porque:
- Gera muitos casos de teste automaticamente através do domínio de entrada
- Captura edge cases que testes unitários manuais podem perder
- Fornece garantias fortes de que o comportamento permanece inalterado para todas as entradas não-buggy

**Test Plan**: Observar comportamento no código NÃO CORRIGIDO primeiro para eventos com Mercado Pago e eventos gratuitos, depois escrever testes baseados em propriedades capturando esse comportamento.

**Test Cases**:
1. **Mercado Pago Active - Order Gateway Preserved**: Observar que eventos com Mercado Pago ativo criam Order com `gateway = 'mercadopago'` no código não corrigido, então escrever teste verificando que isso continua após correção
2. **Mercado Pago Active - Service Call Preserved**: Observar que `MercadoPagoService::createPreference()` é chamado no código não corrigido, então escrever teste verificando que isso continua após correção
3. **Mercado Pago Active - View Data Preserved**: Observar que view recebe `preferenceId` e `publicKey` do Mercado Pago no código não corrigido, então escrever teste verificando que isso continua após correção
4. **Free Event - Gateway Preserved**: Observar que eventos gratuitos criam Order com `gateway = 'free'` no código não corrigido, então escrever teste verificando que isso continua após correção
5. **Free Event - Immediate Settlement Preserved**: Observar que eventos gratuitos são liquidados imediatamente no código não corrigido, então escrever teste verificando que isso continua após correção
6. **Coupon Application Preserved**: Observar que aplicação de cupons funciona corretamente no código não corrigido, então escrever teste verificando que isso continua após correção
7. **Platform Fee Calculation Preserved**: Observar que cálculo de taxas da plataforma funciona corretamente no código não corrigido, então escrever teste verificando que isso continua após correção

### Unit Tests

- Testar `GatewayAccount::resolveActiveGatewayForSeller()` com diferentes configurações de gateway (SumUp ativo, Mercado Pago ativo, nenhum ativo, ambos cadastrados mas apenas um ativo)
- Testar detecção de gateway no método `checkout()` para diferentes cenários (SumUp ativo, Mercado Pago ativo, nenhum ativo)
- Testar criação de Order com gateway correto baseado no gateway ativo detectado
- Testar edge cases (vendedor sem gateway configurado, evento gratuito, evento com preço zero)

### Property-Based Tests

- Gerar eventos aleatórios com diferentes configurações de preço e vendedores com diferentes configurações de gateway, verificar que o gateway correto é sempre usado
- Gerar configurações aleatórias de gateway (ativo/inativo, credenciais válidas/inválidas) e verificar que a detecção funciona corretamente
- Testar que todos os eventos com Mercado Pago ativo continuam funcionando exatamente como antes através de muitos cenários gerados aleatoriamente
- Testar que todos os eventos gratuitos continuam funcionando exatamente como antes através de muitos cenários gerados aleatoriamente

### Integration Tests

- Testar fluxo completo de checkout de evento com SumUp ativo: desde a visualização do evento até a criação do checkout SumUp
- Testar fluxo completo de checkout de evento com Mercado Pago ativo para garantir preservação
- Testar fluxo completo de checkout de evento gratuito para garantir preservação
- Testar troca de gateway ativo (vendedor muda de Mercado Pago para SumUp) e verificar que novos checkouts usam o gateway atualizado
- Testar que webhook SumUp é registrado corretamente e pode ser processado após pagamento
