## Why

O marketplace atual vende cursos, mentorias e eventos, mas nao oferece uma loja propria por vendedor nem um catalogo de produtos fisicos e digitais. Isso limita a descoberta da marca do membro, impede uma experiencia de loja premium e dificulta reaproveitar a logica de split e cobranca para um ecossistema comercial mais completo.

## What Changes

- Criar uma loja virtual publica por vendedor em `/loja/{slug}` com identidade premium, slug unico e imutavel e publicacao condicionada a permissao de venda e plano ativo.
- Adicionar um catalogo proprio de produtos fisicos e digitais por vendedor, com CRUD, midias, configuracao da entrega digital e dados logisticos para frete.
- Implementar checkout de produtos proprios com carrinho de um unico vendedor, split existente do marketplace, pedidos fisicos com frete dos Correios e snapshot do item comprado.
- Exibir produtos proprios no marketplace principal e acrescentar CTA de descoberta da loja do vendedor em cursos, mentorias, eventos e produtos proprios.
- Adicionar governanca minima no admin para listar lojas/produtos e bloquear publicacao sem alterar o slug.

## Capabilities

### New Capabilities
- `seller-stores`: Loja virtual por vendedor com onboarding, publicacao, slug imutavel, identidade premium e governanca basica.
- `seller-product-catalog`: Catalogo proprio do vendedor com produtos fisicos/digitais, midias, configuracao de entrega e gestao no painel.
- `seller-product-checkout`: Carrinho e checkout de produtos proprios com split do marketplace, frete dos Correios e entrega fisica/digital.
- `marketplace-seller-discovery`: Descoberta da loja do vendedor no marketplace principal e agregacao dos produtos proprios na vitrine publica.

### Modified Capabilities
<!-- Nenhuma capability base existe no repositorio principal -->

## Impact

- **Database**: novas tabelas `seller_stores`, `seller_products`, `seller_product_media` e `order_shipments`
- **Models/Services**: novos models, servicos de loja, catalogo, carrinho e frete; extensao do fluxo de pedidos existentes
- **Routes/Controllers**: novas rotas publicas da loja e novas rotas do painel para configuracao, produtos e logistica
- **Views**: novas telas premium da loja publica, CRUD do catalogo e extensoes no marketplace principal
- **Integracoes**: reaproveitamento de Mercado Pago/split e integracao de cotacao dos Correios
