## Context

O sistema ja possui marketplace com venda direta de cursos, mentorias e eventos, usando `orders`, `order_items`, `order_splits`, Mercado Pago e um painel de vendedor focado em pagamentos e relatorios. Nao existe hoje uma entidade de loja por vendedor, nem um catalogo generico de produtos proprios. O projeto tambem ja possui dados de endereco no perfil do membro (`cep`, `address`, `city`, `state`), o que permite usar o endereco do vendedor como origem de frete sem criar um cadastro paralelo.

## Goals / Non-Goals

**Goals:**
- Criar a loja do vendedor sem quebrar o fluxo atual de cursos, eventos e mentorias
- Reaproveitar o mesmo modelo de pedido, split e notificacoes do marketplace
- Permitir catalogo proprio com produtos fisicos e digitais
- Expor uma storefront premium publica com agregacao do que o vendedor ja comercializa no ecossistema
- Garantir que a loja so fique publica para vendedores elegiveis e com plano ativo

**Non-Goals:**
- Nao criar page builder para a loja no v1
- Nao suportar variacoes de SKU, kits ou multiorigem no v1
- Nao misturar itens de vendedores diferentes no mesmo carrinho
- Nao substituir os checkouts atuais de cursos, eventos e mentorias

## Decisions

### 1. Separar `seller_stores` de `users`
**Decisao:** criar uma tabela propria para a loja do vendedor.

**Rationale:** a loja tem ciclo de vida, identidade visual, slug e status operacional que nao pertencem ao perfil base do usuario. Isso tambem permite governanca administrativa sem poluir a tabela `users`.

**Alternativas consideradas:**
- Colunas em `users` -> rejeitado por acoplamento excessivo e baixa extensibilidade

### 2. Reaproveitar `orders` com `seller_id` unico e `metadata.context = marketplace`
**Decisao:** pedidos de produtos proprios continuam em `orders` com `seller_id` unico, item snapshot em `order_items.data` e `sale_type = seller_product`.

**Rationale:** o split do marketplace, emails, contabilizacao e webhook ja assumem um pedido com um vendedor. O carrinho de vendedor unico preserva essa arquitetura e evita refatorar todo o pagamento.

**Alternativas consideradas:**
- Novo agregado de pedidos para loja -> rejeitado por duplicacao de fluxo financeiro
- Carrinho multivendedor -> rejeitado no v1 por incompatibilidade com o modelo atual

### 3. Frete fisico em tabela `order_shipments`
**Decisao:** dados de envio fisico ficam em `order_shipments`, um para cada pedido fisico.

**Rationale:** envio e rastreio possuem ciclo de vida proprio e nao devem ficar espremidos em `orders.metadata`. A tabela tambem facilita filtros no painel do vendedor.

**Alternativas consideradas:**
- Guardar tudo em `metadata` -> rejeitado por baixa consultabilidade

### 4. Produto digital com snapshot da entrega comprada
**Decisao:** salvar em `order_items.data` o snapshot do arquivo ou URL entregue no momento da compra.

**Rationale:** o vendedor pode substituir o produto depois, mas o comprador precisa manter acesso ao que comprou. O snapshot evita inconsistencias retroativas.

### 5. Loja publicada depende de elegibilidade dinamica
**Decisao:** a loja so responde publicamente quando `seller_store.is_published = true`, `user->canSellOnMarketplace()` e `user->activePlan()` continuarem verdadeiros.

**Rationale:** a regra de negocio exige que a loja saia do ar automaticamente quando a elegibilidade expirar, sem apagar o cadastro.

### 6. Identidade premium em preset fixo
**Decisao:** usar um layout premium fixo com campos de personalizacao (logo, banner, cores, bio, links).

**Rationale:** atende ao requisito comercial sem introduzir um construtor visual complexo.

## Risks / Trade-offs

- [Correios exigir credenciais e disponibilidade externa] -> encapsular em servico configuravel e falhar com mensagem explicita no checkout
- [Fluxo amplo atravessando marketplace, painel e pagamento] -> manter novos fluxos de produto proprio isolados por modelos/rotas especificos
- [Slug imutavel ser escolhido incorretamente] -> validar unicidade antes da publicacao e bloquear edicao depois de confirmado
- [Vendedor sem dados completos de endereco] -> impedir publicacao de produto fisico ate que CEP/endereco/cidade/UF estejam completos

## Migration Plan

1. Criar tabelas e models da loja, produtos, midias e envios
2. Implementar servicos de elegibilidade, frete e catalogo agregado
3. Adicionar painel do vendedor para configuracao da loja e CRUD de produtos
4. Adicionar storefront publica e integracao no marketplace principal
5. Implementar checkout/carrinho de produto proprio usando o fluxo financeiro existente
6. Adicionar telas administrativas e testes automatizados

**Rollback:** remover rotas novas e despublicar lojas. As tabelas novas sao aditivas e nao alteram os fluxos antigos.

## Open Questions

- O endpoint final dos Correios pode variar conforme o contrato da plataforma; a implementacao deve suportar configuracao por ambiente.
