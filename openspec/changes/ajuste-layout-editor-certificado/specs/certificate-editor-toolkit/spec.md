## ADDED Requirements

### Requirement: Editor oferece níveis extras de zoom para melhor aproveitamento de tela
O editor MUST oferecer níveis de zoom adicionais (acima de 150%) para permitir melhor aproveitamento da área disponível em telas grandes, sem alterar valores persistidos.

#### Scenario: Opções de zoom incluem 200%, 250% e 300%
- **WHEN** um admin abre o seletor de zoom do editor
- **THEN** o seletor MUST disponibilizar pelo menos 200%, 250% e 300% como opções

### Requirement: Fit-to-screen é aplicado automaticamente ao abrir a aba de certificado
Ao abrir a aba de certificado, o editor MUST aplicar automaticamente um fit-to-screen (ou o nível mais próximo disponível) para que o canvas fique bem enquadrado no viewport, sem alterar coordenadas persistidas.

#### Scenario: Ao abrir a aba, o zoom se ajusta para o melhor enquadramento
- **WHEN** um admin navega para a aba de certificado
- **THEN** o editor MUST aplicar automaticamente o fit-to-screen (ou nível mais próximo) e atualizar o canvas

