## ADDED Requirements

### Requirement: Menu responsivo funcional em dispositivos móveis
O sistema SHALL garantir que o menu principal do frontend funcione corretamente em smartphones e telas pequenas, incluindo abertura, navegação e fechamento.

#### Scenario: Usuário acessa menu em smartphone
- **WHEN** o usuário toca no ícone/menu em um dispositivo móvel
- **THEN** o menu expande, exibe as opções e permite navegação

#### Scenario: Usuário fecha o menu
- **WHEN** o usuário toca fora do menu ou no botão de fechar
- **THEN** o menu recolhe e a navegação retorna ao conteúdo principal

#### Scenario: Menu permanece acessível em todas as páginas
- **WHEN** o usuário navega entre páginas
- **THEN** o menu permanece funcional e responsivo em todas as rotas