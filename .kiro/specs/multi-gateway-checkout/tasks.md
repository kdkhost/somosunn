# Plano de Implementação: Multi-Gateway Checkout

## Visão Geral

Implementação incremental que remove a exclusividade entre gateways, adiciona resolução de múltiplos gateways ativos, seletor visual no checkout e paridade de configurações entre os dois painéis de admin.

## Tarefas

- [ ] 1. Adicionar resolveAllActiveGatewaysForSeller em GatewayAccount
  - Criar o método estático público que itera sobre mercadopago e sumup e resolve cada provider independentemente usando a lógica de prioridade já existente (seller → global)
  - Retornar apenas os gateways com enabled = true e credenciais válidas; retornar array vazio se nenhum estiver ativo
  - Manter resolveActiveGatewayForSeller intacto para compatibilidade retroativa
  - _Requisitos: 1.5, 2.1, 2.2, 2.3, 2.4, 2.5, 2.6_

- [ ] 2. Remover lógica de exclusividade de gateway no SettingController
  - Em toggle(): remover o bloco que desativa o outro gateway ao ativar um
  - Em update(): remover o bloco de validação de exclusividade quando ambos os gateways estão ativos
  - _Requisitos: 1.1, 1.2_

- [ ] 3. Adicionar validacao de metodo minimo por gateway no SettingController
  - No método update(), após processar os booleans do grupo gateway, verificar que cada gateway ativo possui ao menos um método de pagamento ativo
  - Se a validação falhar, retornar HTTP 422 com mensagem adequada
  - A validação deve ocorrer antes de qualquer chamada a Setting::set()
  - _Requisitos: 4.5_

- [ ] 4. Adicionar clamping das novas settings de parcelamento MP e expiracao PIX no SettingController
  - Adicionar mercadopago_max_installments e mercadopago_installments_no_interest ao loop de clamping inteiro [1, 12]
  - Adicionar mercadopago_installment_tax ao bloco de clamping float [0.00, 99.99]
  - Adicionar mercadopago_pix_expiration_minutes e sumup_pix_expiration_minutes ao loop de clamping inteiro [1, 1440]
  - _Requisitos: 5.5, 5.6, 10.5, 10.6_

- [ ] 5. Atualizar EventReservationController checkout para multiplos gateways
  - Substituir a chamada a resolveActiveGatewayForSeller por resolveAllActiveGatewaysForSeller
  - Passar activeGateways (array) para a view
  - Se activeGateways estiver vazio, redirecionar com erro
  - _Requisitos: 3.1, 3.2_

- [ ] 6. Atualizar EventReservationController reserve para rotear pelo gateway selecionado
  - Quando há múltiplos gateways ativos, ler gateway do request e validar que está na lista de providers ativos
  - Quando há apenas 1 gateway ativo, usar esse diretamente sem exigir o campo no request
  - Registrar orders.gateway com o identificador do gateway selecionado
  - Logar order_id, event_id e gateway em cada transação
  - _Requisitos: 3.7, 9.1, 9.2, 9.3, 9.4, 9.5_

- [ ] 7. Atualizar CheckoutController sumupPix para usar sumup_pix_expiration_minutes
  - Substituir o now()->addMinutes(30) hardcoded pelo valor configurado na setting sumup_pix_expiration_minutes com padrão de 10 minutos
  - _Requisitos: 10.3, 10.4, 10.8_

- [ ] 8. Atualizar checkout MP para usar mercadopago_pix_expiration_minutes
  - No CheckoutController::processPayment, ao gerar PIX, usar mercadopago_pix_expiration_minutes com fallback para pix_expiration_minutes e depois para 10
  - _Requisitos: 10.1, 10.2, 10.7_

- [ ] 9. Atualizar view checkout transparent para suportar Gateway Selector
  - Alterar a view para receber activeGateways (array) em vez de gateway (string)
  - Quando 1 gateway: manter o comportamento atual (formulário direto, sem seletor)
  - Quando 2 gateways: renderizar o Gateway_Selector com um card por gateway, seguido dos dois formulários em divs ocultas
  - Adicionar input hidden selected_gateway
  - Implementar função JS selectGateway(provider) que atualiza o campo, oculta o seletor e exibe o formulário correspondente
  - Adicionar botão Trocar gateway que retorna ao seletor
  - _Requisitos: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.8_

- [ ] 10. Atualizar sumup-card-form para usar sumupPixExpirationMinutes
  - Receber a variável sumupPixExpirationMinutes da view pai
  - Usar esse valor no timer do PIX em vez do valor hardcoded de 30 minutos
  - _Requisitos: 10.3, 10.4, 10.9_

- [ ] 11. Atualizar painel moderno panel/admin/settings/partials/gateway.blade.php
  - Remover o aviso de exclusividade de gateway
  - Na tab Cobrança do Mercado Pago, adicionar campos: mercadopago_max_installments (1-12), mercadopago_installments_no_interest (1-12), mercadopago_installment_tax (0.00-99.99%), mercadopago_pix_expiration_minutes (1-1440 min, padrão 10)
  - Na seção SumUp, adicionar campo sumup_pix_expiration_minutes (1-1440 min, padrão 10)
  - _Requisitos: 1.4, 5.2, 6.2, 8.2, 10.2, 10.4_

- [ ] 12. Atualizar painel legado admin/settings/partials/gateway.blade.php
  - Remover o alert-info com a mensagem de exclusividade de gateway
  - Na aba Cobrança do Mercado Pago, adicionar os mesmos campos do painel moderno
  - Na seção SumUp, adicionar campo sumup_pix_expiration_minutes
  - _Requisitos: 1.3, 5.1, 6.1, 8.1, 10.1, 10.3_

- [ ] 13. Escrever testes de integracao para fluxo de checkout
  - Criar tests/Feature/MultiGateway/GatewayResolutionTest.php com testes para fluxo com 1 e 2 gateways ativos
  - Testar fallback para credenciais globais quando o vendedor não tem gateway configurado
  - _Requisitos: 2.2, 2.3, 2.4, 2.5, 9.1, 9.2_

- [ ] 14. Commit e deploy das alteracoes
  - Fazer commit de todas as alterações com mensagem descritiva
  - Fazer push para o repositório remoto
  - Atualizar CHANGELOG.md com as mudanças implementadas
