## ADDED Requirements

### Requirement: Acessibilidade na página de oportunidades
A página de oportunidades MUST garantir acessibilidade, incluindo contraste adequado, navegação por teclado e uso de ARIA.

#### Scenario: Contraste adequado
- **WHEN** o usuário acessa a página
- **THEN** o sistema exibe elementos com contraste suficiente para leitura

#### Scenario: Navegação por teclado
- **WHEN** o usuário navega usando apenas o teclado
- **THEN** o sistema permite acessar todos os elementos interativos

#### Scenario: Uso de ARIA
- **WHEN** o usuário utiliza leitor de tela
- **THEN** o sistema fornece informações corretas via atributos ARIA
