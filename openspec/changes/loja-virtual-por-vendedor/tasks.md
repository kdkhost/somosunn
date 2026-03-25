## 1. OpenSpec e base de dados

- [x] 1.1 Criar as migrations e models de `seller_stores`, `seller_products`, `seller_product_media` e `order_shipments`
- [x] 1.2 Implementar servicos e regras de elegibilidade da loja, slug imutavel e catalogo agregado do vendedor
- [x] 1.3 Configurar validacoes, relacionamentos e utilitarios de upload/URLs para loja e produtos

## 2. Painel do vendedor

- [x] 2.1 Adicionar rotas e controller para configuracao da loja premium do vendedor
- [x] 2.2 Implementar CRUD de produtos proprios fisicos/digitais com galeria de midias
- [x] 2.3 Implementar tela de pedidos/logistica dos produtos proprios no painel do vendedor
- [x] 2.4 Atualizar sidebar e dashboard do marketplace para expor loja, produtos e pedidos

## 3. Experiencia publica e descoberta

- [x] 3.1 Criar storefront publica em `/loja/{slug}` e pagina publica de produto proprio
- [x] 3.2 Integrar agregacao de cursos, mentorias, eventos e produtos proprios na loja publica
- [x] 3.3 Atualizar o marketplace principal com secao de produtos proprios e CTA "Ver mais desse vendedor"

## 4. Checkout e frete

- [x] 4.1 Implementar carrinho de um unico vendedor para produtos proprios
- [x] 4.2 Implementar checkout de produtos proprios reaproveitando `orders`, `order_items`, `order_splits` e Mercado Pago
- [x] 4.3 Integrar cotacao de frete dos Correios e persistencia em `order_shipments`
- [x] 4.4 Liberar entrega digital por snapshot apos pagamento aprovado

## 5. Governanca, testes e publicacao

- [x] 5.1 Adicionar visao administrativa minima para listar/bloquear lojas e produtos
- [x] 5.2 Cobrir elegibilidade, slug, catalogo, checkout, frete e descoberta com testes automatizados
- [x] 5.3 Validar views e codificacao, atualizar tasks e publicar no Git
