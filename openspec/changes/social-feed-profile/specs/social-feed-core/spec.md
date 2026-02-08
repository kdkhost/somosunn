## ADDED Requirements

### Requirement: Usuario pode criar post no feed
O sistema SHALL permitir que usuarios autenticados criem posts no feed com texto e anexos permitidos, preservando o layout atual.

#### Scenario: Criacao de post bem sucedida
- **WHEN** o usuario envia um post valido
- **THEN** o sistema cria o post e o exibe no topo do feed do usuario

### Requirement: Usuario pode visualizar feed paginado
O sistema SHALL listar o feed do usuario com paginacao e ordenacao por data de criacao desc, mantendo o layout atual.

#### Scenario: Listagem inicial do feed
- **WHEN** o usuario acessa a area de feed
- **THEN** o sistema retorna a primeira pagina de posts ordenados por data

### Requirement: Usuario pode curtir e descurtir posts
O sistema SHALL permitir curtir e descurtir posts com atualizacao do contador de curtidas.

#### Scenario: Curtida aplicada
- **WHEN** o usuario clica em curtir um post nao curtido
- **THEN** o sistema registra a curtida e incrementa o contador

#### Scenario: Curtida removida
- **WHEN** o usuario clica em curtir um post ja curtido
- **THEN** o sistema remove a curtida e decrementa o contador

### Requirement: Usuario pode comentar em posts
O sistema SHALL permitir que usuarios autenticados criem comentarios em posts.

#### Scenario: Comentario criado
- **WHEN** o usuario envia um comentario valido em um post
- **THEN** o sistema registra o comentario e o exibe abaixo do post

### Requirement: Usuario pode compartilhar posts
O sistema SHALL permitir compartilhar posts no proprio feed do usuario, criando uma entrada derivada do post original.

#### Scenario: Compartilhamento efetuado
- **WHEN** o usuario escolhe compartilhar um post
- **THEN** o sistema cria uma nova entrada referenciando o post original
