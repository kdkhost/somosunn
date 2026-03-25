## ADDED Requirements

### Requirement: Vendedor elegivel pode configurar uma loja propria
O sistema MUST permitir que um membro elegivel para vendas no marketplace e com plano ativo configure uma loja propria em modo rascunho.

#### Scenario: Primeiro acesso cria rascunho da loja
- **WHEN** um vendedor elegivel acessa o painel da loja pela primeira vez
- **THEN** o sistema cria ou recupera uma `seller_store` vinculada ao usuario
- **AND** a loja fica em modo rascunho ate ser publicada

#### Scenario: Vendedor sem elegibilidade nao acessa o painel da loja
- **WHEN** um usuario sem permissao comercial ou sem plano ativo acessa as rotas da loja do vendedor
- **THEN** o sistema bloqueia o acesso com erro de autorizacao

### Requirement: Slug da loja e unico e imutavel
O sistema MUST exigir um slug globalmente unico para a loja e MUST impedir alteracao do slug apos a primeira publicacao.

#### Scenario: Publicacao com slug unico
- **WHEN** o vendedor publica a loja com um slug disponivel
- **THEN** o sistema salva o slug
- **AND** registra a confirmacao do slug

#### Scenario: Tentativa de alterar slug apos publicacao
- **WHEN** o vendedor tenta alterar o slug depois que a loja ja foi publicada
- **THEN** o sistema rejeita a alteracao
- **AND** mantem o slug original

### Requirement: Loja publica depende de elegibilidade e publicacao
O sistema MUST expor a loja publica somente quando a loja estiver publicada, o vendedor continuar elegivel e o plano estiver ativo.

#### Scenario: Loja publicada fica acessivel
- **WHEN** a loja esta publicada e o vendedor continua elegivel
- **THEN** `GET /loja/{slug}` retorna a vitrine publica da loja

#### Scenario: Loja fica inativa por perda de elegibilidade
- **WHEN** o vendedor perde permissao comercial ou plano ativo
- **THEN** a rota publica da loja retorna 404

### Requirement: Loja premium agrega produtos do vendedor
O sistema MUST exibir na storefront premium os produtos proprios do vendedor e os itens publicos existentes do ecossistema que pertencem a ele.

#### Scenario: Storefront agrega diferentes tipos de oferta
- **WHEN** um cliente acessa a loja publica de um vendedor elegivel
- **THEN** o sistema exibe produtos proprios publicados do vendedor
- **AND** exibe cursos, mentorias e eventos publicos vinculados ao mesmo vendedor

### Requirement: Administracao pode moderar lojas e produtos
O sistema MUST permitir que administradores listem lojas e produtos de vendedores e possam bloquear a publicacao sem alterar o slug.

#### Scenario: Admin despublica uma loja
- **WHEN** um administrador despublica ou bloqueia uma loja
- **THEN** a loja deixa de aparecer publicamente
- **AND** o slug cadastrado continua reservado
