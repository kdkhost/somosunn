## ADDED Requirements

### Requirement: Vendedor pode gerenciar produtos proprios fisicos e digitais
O sistema MUST permitir que o vendedor crie, edite, publique e despublique produtos proprios fisicos e digitais dentro da sua loja.

#### Scenario: Criacao de produto fisico simples
- **WHEN** o vendedor informa titulo, preco, estoque e dimensoes/peso obrigatorios
- **THEN** o sistema cria um produto fisico publicado ou em rascunho

#### Scenario: Criacao de produto digital simples
- **WHEN** o vendedor informa titulo, preco e um arquivo protegido ou URL externa
- **THEN** o sistema cria um produto digital com configuracao valida de entrega

### Requirement: Produto fisico exige dados logisticos minimos
O sistema MUST exigir peso, altura, largura, comprimento e estoque para qualquer produto fisico publicado.

#### Scenario: Produto fisico sem dimensoes nao publica
- **WHEN** o vendedor tenta publicar um produto fisico sem peso ou dimensoes completas
- **THEN** o sistema rejeita a publicacao com erro de validacao

### Requirement: Produto digital preserva snapshot da entrega
O sistema MUST armazenar a configuracao da entrega digital de forma que o pedido aprovado mantenha acesso ao conteudo comprado mesmo se o vendedor editar o produto depois.

#### Scenario: Produto digital atualizado apos uma venda
- **WHEN** o vendedor troca o arquivo ou a URL de um produto digital depois de uma compra aprovada
- **THEN** o comprador continua com acesso ao snapshot registrado no pedido antigo

### Requirement: Produto suporta galeria de midias
O sistema MUST permitir que o vendedor associe uma galeria de midias ao produto proprio.

#### Scenario: Galeria exibe capa e midias adicionais
- **WHEN** o vendedor faz upload de capa e midias complementares do produto
- **THEN** a pagina do produto exibe a imagem principal e a galeria configurada

### Requirement: Vendedor visualiza pedidos proprios e logistica
O sistema MUST permitir que o vendedor acompanhe pedidos dos seus produtos proprios e atualize status logistico, codigo de rastreio e observacoes de entrega.

#### Scenario: Atualizacao de envio fisico
- **WHEN** o vendedor informa codigo de rastreio e marca o pedido como enviado
- **THEN** o sistema persiste os dados de envio vinculados ao pedido
