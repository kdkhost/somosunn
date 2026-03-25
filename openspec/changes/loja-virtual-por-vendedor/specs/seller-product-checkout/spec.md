## ADDED Requirements

### Requirement: Carrinho de produto proprio e limitado a um vendedor
O sistema MUST manter um carrinho por vendedor para produtos proprios e MUST impedir mistura de vendedores diferentes no mesmo checkout.

#### Scenario: Adicao de item do mesmo vendedor
- **WHEN** o cliente adiciona um segundo produto proprio do mesmo vendedor ao carrinho
- **THEN** o sistema mantem ambos os itens no mesmo carrinho

#### Scenario: Adicao de item de vendedor diferente
- **WHEN** o cliente tenta adicionar um produto proprio de outro vendedor
- **THEN** o sistema exige substituir o carrinho atual antes de prosseguir

### Requirement: Pedido proprio reaproveita o fluxo financeiro do marketplace
O sistema MUST criar pedidos de produtos proprios usando `orders`, `order_items` e `order_splits`, com `metadata.context = marketplace` e `sale_type = seller_product`.

#### Scenario: Criacao de pedido de produto proprio
- **WHEN** o cliente inicia checkout de produtos proprios de um vendedor
- **THEN** o sistema cria um `order` com `seller_id` do vendedor
- **AND** cria `order_items` com snapshot completo dos itens comprados
- **AND** calcula o split pelo mesmo fluxo financeiro do marketplace

### Requirement: Frete fisico depende de cotacao dos Correios
O sistema MUST calcular frete para produtos fisicos com os servicos habilitados dos Correios e MUST bloquear checkout fisico sem cotacao valida.

#### Scenario: Checkout fisico com cotacao valida
- **WHEN** o cliente informa um CEP de destino valido e o vendedor possui endereco de origem completo
- **THEN** o sistema exibe opcoes de frete dos Correios com prazo e valor

#### Scenario: Checkout fisico sem cotacao valida
- **WHEN** nenhuma cotacao valida esta disponivel para o pedido fisico
- **THEN** o sistema impede a finalizacao do checkout

### Requirement: Endereco de entrega e rastreio ficam no pedido
O sistema MUST persistir snapshot do endereco de entrega e do servico escolhido para pedidos fisicos.

#### Scenario: Pedido fisico aprovado
- **WHEN** um pedido fisico e criado
- **THEN** o sistema registra `order_shipments` com endereco, servico, valor do frete e prazo

### Requirement: Pedido digital libera acesso apos pagamento
O sistema MUST disponibilizar o item digital comprado ao comprador somente apos aprovacao do pagamento.

#### Scenario: Pagamento aprovado de produto digital
- **WHEN** o pedido de produto digital muda para pago
- **THEN** o comprador passa a ver a entrega digital na area de compras ou no detalhamento do pedido
