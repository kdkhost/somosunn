## ADDED Requirements

### Requirement: Exibir vagas dinamicamente
A página de oportunidades de carreira SHALL exibir vagas de emprego de forma dinâmica, utilizando dados reais do banco de dados.

#### Scenario: Exibição de vagas
- **WHEN** o usuário acessa a página de oportunidades
- **THEN** o sistema exibe uma lista de vagas ativas, ordenadas por data de expiração

#### Scenario: Vaga sem dados
- **WHEN** não houver vagas disponíveis
- **THEN** o sistema exibe uma mensagem informando que não há vagas abertas no momento

### Requirement: Paginação de vagas
O sistema MUST permitir paginação caso o número de vagas exceda o limite de exibição por página.

#### Scenario: Paginação
- **WHEN** houver mais vagas do que o limite por página
- **THEN** o sistema exibe controles de navegação para acessar páginas seguintes
