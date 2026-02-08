## ADDED Requirements

### Requirement: Foto de perfil e resolvida por fonte unica
O sistema SHALL resolver a foto de perfil do usuario a partir de uma fonte unica e consistente para evitar divergencias entre perfil e feed.

#### Scenario: Resolucao de foto valida
- **WHEN** o usuario possui foto de perfil configurada
- **THEN** o sistema retorna a URL da foto configurada

### Requirement: Foto de perfil aparece no perfil do usuario
O sistema SHALL exibir a foto de perfil do usuario na area de perfil.

#### Scenario: Exibicao no perfil
- **WHEN** o usuario acessa sua pagina de perfil
- **THEN** o sistema exibe a foto de perfil resolvida

### Requirement: Foto de perfil aparece nos itens do feed
O sistema SHALL exibir a foto de perfil do autor em cada item do feed.

#### Scenario: Exibicao no feed
- **WHEN** o feed lista posts de um autor
- **THEN** o sistema exibe a foto de perfil resolvida do autor em cada item

### Requirement: Fallback de foto padrao
O sistema SHALL usar uma imagem padrao quando o usuario nao possuir foto configurada.

#### Scenario: Sem foto configurada
- **WHEN** o usuario nao possui foto de perfil
- **THEN** o sistema exibe a imagem padrao definida
