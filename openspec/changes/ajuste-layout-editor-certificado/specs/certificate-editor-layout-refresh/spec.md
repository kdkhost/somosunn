## ADDED Requirements

### Requirement: Configurações do certificado são distribuídas para reduzir rolagem e espaço ocioso
Em telas de desktop, o editor MUST distribuir as configurações em painéis (ex.: sidebar + painéis abaixo do canvas) para reduzir rolagem excessiva e evitar que a página fique com grande área vazia enquanto os controles ficam concentrados apenas na lateral.

#### Scenario: Configurações principais aparecem em mais de um painel no desktop
- **WHEN** um admin abre a aba de certificado em uma viewport desktop
- **THEN** o editor MUST exibir o canvas e um painel lateral de configurações
- **AND** o editor MUST exibir um painel adicional abaixo do canvas com configurações frequentes (ex.: elementos visíveis, camadas e/ou propriedades do elemento selecionado)

### Requirement: Lista de camadas/elementos exibe labels legíveis com acentuação correta
O painel de camadas/elementos MUST exibir labels legíveis em PT-BR (sem caracteres quebrados por encoding), refletindo o significado do elemento.

#### Scenario: Labels de elementos aparecem com acentuação correta
- **WHEN** o editor renderiza a lista de camadas/elementos
- **THEN** labels como "Data de Conclusão", "Código de Validação" e "Carga Horária" MUST ser exibidas corretamente (sem "ConclusÃ£o", "CÃ³digo", "HorÃ¡ria")

