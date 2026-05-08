# Documento de Requisitos

## Introdução

Esta feature adiciona suporte a múltiplos gateways de pagamento simultâneos no sistema SaaS de cursos e eventos (Laravel). Atualmente, apenas um gateway (Mercado Pago ou SumUp) pode ficar ativo por vez. A mudança permite que ambos fiquem ativos ao mesmo tempo, exibindo ao cliente um seletor visual de gateway no checkout quando os dois estiverem configurados. Cada gateway passa a ter configurações independentes de métodos de pagamento, parcelamento e repasse de taxas, gerenciáveis nos painéis `/admin` e `/painel`.

---

## Glossário

- **Checkout**: Página onde o cliente finaliza a compra de um evento pago.
- **Gateway**: Provedor de pagamento integrado ao sistema (Mercado Pago ou SumUp).
- **Gateway_Selector**: Componente visual exibido no checkout quando dois gateways estão ativos simultaneamente, permitindo ao cliente escolher o provedor de pagamento.
- **GatewayAccount**: Model Laravel (`app/Models/GatewayAccount.php`) que armazena credenciais de gateway por vendedor.
- **Método_de_Pagamento**: Forma de pagamento disponível dentro de um gateway (Cartão de Crédito, PIX, etc.).
- **Parcela**: Divisão do valor total em pagamentos mensais sucessivos no cartão de crédito.
- **Repasse_de_Taxa**: Configuração que define se o custo de parcelamento é absorvido pelo vendedor ou repassado ao cliente.
- **Admin_Panel**: Painel administrativo acessível em `/admin` (interface legada AdminLTE).
- **Painel_Moderno**: Painel administrativo acessível em `/painel` (interface moderna Tailwind).
- **SettingController**: Controller Laravel (`app/Http/Controllers/Admin/SettingController.php`) responsável por salvar configurações de gateway.
- **Setting**: Model Laravel que persiste configurações chave-valor na tabela `settings`.
- **Vendedor**: Usuário organizador do evento, cujas credenciais de gateway são usadas no checkout.

---

## Requisitos

### Requisito 1: Remoção da Regra de Exclusividade de Gateway

**User Story:** Como administrador da plataforma, quero poder ativar Mercado Pago e SumUp simultaneamente, para que os clientes tenham mais opções de pagamento no checkout.

#### Critérios de Aceitação

1. THE SettingController SHALL permitir que `mercadopago_enabled` e `sumup_enabled` sejam ambos `1` ao mesmo tempo, sem desativar automaticamente o outro.
2. WHEN o administrador ativa um gateway no toggle do painel, THE SettingController SHALL salvar o estado do gateway ativado sem alterar o estado do outro gateway.
3. THE Admin_Panel SHALL remover o aviso "Apenas um gateway pode ficar ativo por vez" da interface de configurações de gateway.
4. THE Painel_Moderno SHALL remover o aviso "Apenas um gateway pode ficar ativo por vez" da interface de configurações de gateway.
5. WHEN ambos os gateways estão com `enabled = 1`, THE GatewayAccount SHALL retornar os dois gateways ativos ao resolver os gateways disponíveis para um vendedor.

---

### Requisito 2: Resolução de Gateways Ativos para o Vendedor

**User Story:** Como sistema, quero identificar corretamente quais gateways estão disponíveis para um vendedor, para que o checkout exiba as opções corretas ao cliente.

#### Critérios de Aceitação

1. THE GatewayAccount SHALL expor um método `resolveAllActiveGatewaysForSeller(int $sellerId): array` que retorna todos os gateways ativos do vendedor, em vez de apenas o primeiro encontrado.
2. WHEN o vendedor possui apenas Mercado Pago ativo, THE GatewayAccount SHALL retornar um array com exatamente um gateway (`mercadopago`).
3. WHEN o vendedor possui apenas SumUp ativo, THE GatewayAccount SHALL retornar um array com exatamente um gateway (`sumup`).
4. WHEN o vendedor possui ambos os gateways ativos, THE GatewayAccount SHALL retornar um array com dois gateways (`mercadopago` e `sumup`).
5. WHEN o vendedor não possui nenhum gateway configurado, THE GatewayAccount SHALL aplicar o fallback para as credenciais globais da plataforma na mesma ordem de prioridade atual.
6. IF nenhum gateway estiver ativo para o vendedor nem nas configurações globais, THEN THE GatewayAccount SHALL retornar um array vazio com `enabled = false`.

---

### Requisito 3: Seletor de Gateway no Checkout (Cliente)

**User Story:** Como cliente, quero escolher entre Mercado Pago e SumUp no checkout quando ambos estiverem disponíveis, para que eu pague com o método de minha preferência.

#### Critérios de Aceitação

1. WHEN apenas um gateway está ativo para o vendedor do evento, THE Checkout SHALL exibir diretamente o formulário de pagamento daquele gateway, sem exibir o Gateway_Selector.
2. WHEN dois gateways estão ativos para o vendedor do evento, THE Checkout SHALL exibir o Gateway_Selector antes do formulário de pagamento.
3. THE Gateway_Selector SHALL exibir um card visual para cada gateway ativo, contendo o logotipo ou nome do gateway e uma descrição resumida dos métodos disponíveis.
4. WHEN o cliente seleciona um gateway no Gateway_Selector, THE Checkout SHALL exibir o formulário de pagamento correspondente ao gateway escolhido.
5. WHEN o cliente seleciona um gateway no Gateway_Selector, THE Checkout SHALL ocultar o formulário do gateway não selecionado.
6. THE Checkout SHALL permitir que o cliente retorne ao Gateway_Selector para trocar de gateway antes de confirmar o pagamento.
7. WHEN o cliente confirma o pagamento, THE EventReservationController SHALL processar o pagamento exclusivamente pelo gateway selecionado pelo cliente.
8. THE Gateway_Selector SHALL ser responsivo e acessível em dispositivos móveis com largura mínima de 320px.

---

### Requisito 4: Configuração de Métodos de Pagamento por Gateway no Admin

**User Story:** Como administrador, quero ativar ou desativar individualmente Cartão e PIX para cada gateway, para que o checkout exiba apenas os métodos que desejo oferecer.

#### Critérios de Aceitação

1. THE Admin_Panel SHALL exibir, na aba de Métodos do Mercado Pago, toggles independentes para: Cartão de Crédito (`mercadopago_method_credit_card`) e PIX (`mercadopago_method_pix`).
2. THE Painel_Moderno SHALL exibir, na aba de Métodos do Mercado Pago, os mesmos toggles independentes para Cartão de Crédito e PIX.
3. THE Admin_Panel SHALL exibir, na seção SumUp, toggles independentes para: Cartão (`sumup_method_card`) e PIX (`sumup_method_pix`).
4. THE Painel_Moderno SHALL exibir, na seção SumUp, os mesmos toggles independentes para Cartão e PIX.
5. WHEN o administrador tenta desativar o último método ativo de um gateway, THE SettingController SHALL rejeitar a operação e retornar um erro com a mensagem "Ao menos um método de pagamento deve permanecer ativo para este gateway."
6. WHEN o administrador salva as configurações de métodos, THE SettingController SHALL persistir cada flag de método individualmente na tabela `settings`.
7. WHEN o checkout carrega para um gateway específico, THE Checkout SHALL exibir apenas os métodos de pagamento marcados como ativos nas configurações daquele gateway.

---

### Requisito 5: Configuração de Parcelamento por Gateway no Admin

**User Story:** Como administrador, quero configurar regras de parcelamento separadas para Mercado Pago e SumUp, para que cada gateway aplique suas próprias condições de parcelamento.

#### Critérios de Aceitação

1. THE Admin_Panel SHALL exibir, nas configurações do Mercado Pago, campos para: máximo de parcelas (1–12), parcelas sem juros (1–12) e percentual de juros por parcela (0,00–99,99%).
2. THE Painel_Moderno SHALL exibir os mesmos campos de parcelamento para o Mercado Pago.
3. THE Admin_Panel SHALL exibir, nas configurações do SumUp, campos para: máximo de parcelas (`sumup_max_installments`, 1–12), parcelas sem juros (`sumup_installments_no_interest`, 1–12) e percentual de juros por parcela (`sumup_installment_tax`, 0,00–99,99%).
4. THE Painel_Moderno SHALL exibir os mesmos campos de parcelamento para o SumUp.
5. WHEN o administrador informa um valor fora do intervalo permitido para máximo de parcelas, THE SettingController SHALL corrigir o valor para o limite mais próximo (1 ou 12) antes de persistir.
6. WHEN o administrador informa um valor fora do intervalo permitido para percentual de juros, THE SettingController SHALL corrigir o valor para o limite mais próximo (0,00 ou 99,99) antes de persistir.
7. WHEN o checkout processa um pagamento parcelado, THE Checkout SHALL calcular o valor de cada parcela aplicando os juros configurados para o gateway selecionado.
8. WHEN o número de parcelas escolhido pelo cliente é menor ou igual ao limite de parcelas sem juros configurado, THE Checkout SHALL exibir o valor da parcela sem acréscimo de juros.

---

### Requisito 6: Configuração de Repasse de Taxas por Gateway no Admin

**User Story:** Como administrador, quero configurar o repasse de taxas de parcelamento de forma independente para cada gateway, para que eu controle quem absorve o custo em cada provedor.

#### Critérios de Aceitação

1. THE Admin_Panel SHALL exibir, nas configurações de cobrança do Mercado Pago, uma opção de repasse de taxa com os valores: "Não — empresa absorve" e "Sim — cliente paga" (`gateway_pass_tax_to_client`).
2. THE Painel_Moderno SHALL exibir a mesma opção de repasse de taxa para o Mercado Pago.
3. THE Admin_Panel SHALL exibir, nas configurações do SumUp, uma opção de repasse de taxa independente (`sumup_pass_fee`) com os mesmos valores.
4. THE Painel_Moderno SHALL exibir a mesma opção de repasse de taxa para o SumUp.
5. WHEN o repasse de taxa está ativo para um gateway e o cliente escolhe parcelamento, THE Checkout SHALL adicionar o valor dos juros ao total exibido ao cliente antes da confirmação do pagamento.
6. WHEN o repasse de taxa está inativo para um gateway, THE Checkout SHALL exibir o valor original sem acréscimo de juros ao cliente, independentemente do número de parcelas.

---

### Requisito 7: Exibição de Métodos e Parcelas no Checkout após Seleção de Gateway

**User Story:** Como cliente, quero ver apenas os métodos de pagamento e as opções de parcelamento configuradas pelo administrador para o gateway que escolhi, para que o checkout seja claro e sem opções indisponíveis.

#### Critérios de Aceitação

1. WHEN o cliente seleciona Mercado Pago no Gateway_Selector, THE Checkout SHALL inicializar o Payment Brick do Mercado Pago com apenas os métodos marcados como ativos nas configurações (`mercadopago_method_credit_card`, `mercadopago_method_pix`, etc.).
2. WHEN o cliente seleciona SumUp no Gateway_Selector, THE Checkout SHALL exibir apenas os métodos ativos configurados para o SumUp (`sumup_method_card`, `sumup_method_pix`).
3. WHEN o cliente seleciona cartão de crédito no checkout do Mercado Pago, THE Checkout SHALL exibir as opções de parcelamento respeitando o máximo de parcelas configurado em `gateway_max_installments` (ou equivalente por gateway).
4. WHEN o cliente seleciona cartão de crédito no checkout do SumUp, THE Checkout SHALL exibir as opções de parcelamento respeitando o máximo de parcelas configurado em `sumup_max_installments`.
5. THE Checkout SHALL exibir o valor total atualizado em tempo real ao cliente quando ele altera o número de parcelas, refletindo os juros e o repasse de taxa configurados.

---

### Requisito 8: Paridade de Configurações entre Admin_Panel e Painel_Moderno

**User Story:** Como administrador, quero que as configurações de gateway sejam idênticas nos dois painéis (/admin e /painel), para que eu possa gerenciar o sistema a partir de qualquer interface sem perda de funcionalidade.

#### Critérios de Aceitação

1. THE Admin_Panel SHALL exibir todas as configurações de gateway (métodos, parcelamento, repasse de taxas) para Mercado Pago e SumUp que estiverem disponíveis no Painel_Moderno.
2. THE Painel_Moderno SHALL exibir todas as configurações de gateway (métodos, parcelamento, repasse de taxas) para Mercado Pago e SumUp que estiverem disponíveis no Admin_Panel.
3. WHEN o administrador salva uma configuração de gateway pelo Admin_Panel, THE SettingController SHALL persistir os mesmos campos que seriam persistidos ao salvar pelo Painel_Moderno.
4. WHEN o administrador salva uma configuração de gateway pelo Painel_Moderno, THE SettingController SHALL persistir os mesmos campos que seriam persistidos ao salvar pelo Admin_Panel.

---

### Requisito 9: Integridade do Fluxo de Pagamento com Gateway Selecionado

**User Story:** Como sistema, quero garantir que o pedido seja processado pelo gateway que o cliente escolheu no checkout, para que não haja inconsistência entre a seleção do cliente e o gateway que efetua a cobrança.

#### Critérios de Aceitação

1. WHEN o cliente confirma o pagamento após selecionar um gateway, THE EventReservationController SHALL registrar o campo `gateway` do pedido (`orders.gateway`) com o identificador do gateway selecionado pelo cliente.
2. WHEN o EventReservationController roteia o pagamento, THE EventReservationController SHALL chamar exclusivamente o serviço de pagamento correspondente ao gateway registrado no pedido.
3. IF o gateway informado pelo cliente não estiver entre os gateways ativos para o vendedor no momento do processamento, THEN THE EventReservationController SHALL rejeitar o pagamento e retornar um erro ao cliente.
4. WHEN um pedido é criado com dois gateways disponíveis, THE EventReservationController SHALL exigir que o campo `gateway` seja informado explicitamente pelo cliente antes de processar o pagamento.
5. THE EventReservationController SHALL registrar em log o gateway utilizado em cada transação, incluindo o `order_id`, `event_id` e o identificador do gateway.

---

### Requisito 10: Configuração do Tempo de Expiração do PIX

**User Story:** Como administrador, quero configurar o tempo de expiração do QR Code PIX de forma independente para cada gateway (Mercado Pago e SumUp), para que eu controle por quanto tempo o código permanece válido para pagamento.

#### Critérios de Aceitação

1. THE Admin_Panel SHALL exibir, nas configurações de cobrança do Mercado Pago, um campo numérico para o tempo de expiração do PIX em minutos (`mercadopago_pix_expiration_minutes`), com valor padrão de `10` minutos.
2. THE Painel_Moderno SHALL exibir o mesmo campo de expiração do PIX para o Mercado Pago com o mesmo valor padrão.
3. THE Admin_Panel SHALL exibir, nas configurações do SumUp, um campo numérico para o tempo de expiração do PIX em minutos (`sumup_pix_expiration_minutes`), com valor padrão de `10` minutos.
4. THE Painel_Moderno SHALL exibir o mesmo campo de expiração do PIX para o SumUp com o mesmo valor padrão.
5. WHEN o administrador informa um valor menor que 1 para o tempo de expiração, THE SettingController SHALL corrigir o valor para `1` antes de persistir.
6. WHEN o administrador informa um valor maior que 1440 (24 horas) para o tempo de expiração, THE SettingController SHALL corrigir o valor para `1440` antes de persistir.
7. WHEN o checkout gera um QR Code PIX via Mercado Pago, THE CheckoutController SHALL usar o valor de `mercadopago_pix_expiration_minutes` para definir o tempo de expiração do pagamento, com fallback para `pix_expiration_minutes` (chave legada) e depois para `10`.
8. WHEN o checkout gera um QR Code PIX via SumUp, THE CheckoutController SHALL usar o valor de `sumup_pix_expiration_minutes` para definir o timer de expiração exibido ao cliente, com fallback para `10`.
9. THE Checkout SHALL exibir o timer regressivo do PIX com o tempo configurado para o gateway correspondente, iniciando a contagem a partir do momento em que o QR Code é gerado.
