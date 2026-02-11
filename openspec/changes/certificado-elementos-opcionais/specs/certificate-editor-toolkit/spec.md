## ADDED Requirements

### Requirement: Elementos opcionais de título e apresentação são configuráveis no editor
O editor MUST permitir configurar os elementos `title` e `presentation_text` como opcionais, com controle de visibilidade persistido em `certificate_settings.elements.<tag>.visible`.

#### Scenario: Admin habilita e desabilita título/apresentação sem alterar layout
- **WHEN** o admin marca/desmarca a visibilidade de `title` ou `presentation_text`
- **THEN** o canvas MUST exibir/ocultar o elemento imediatamente
- **AND** ao salvar, o sistema MUST persistir apenas `visible` (sem recalcular `x/y` ou estilos)

### Requirement: Assinatura é tratada como elemento de imagem no canvas
O editor MUST renderizar `instructor_signature` como elemento de imagem (e não como texto), permitindo drag e resize para definir `x/y/width/height`.

#### Scenario: Upload de assinatura atualiza o preview do canvas
- **WHEN** o admin seleciona um arquivo de assinatura no formulário
- **THEN** o canvas MUST atualizar o preview da assinatura imediatamente
- **AND** o elemento MUST permanecer controlável por visibilidade (mostrar/ocultar)

### Requirement: Multiline preserva quebras de linha no editor
Quando `multiline` está habilitado e o texto contém quebras de linha, o editor MUST preservar essas quebras no canvas.

#### Scenario: Texto com quebra de linha é exibido em mais de uma linha
- **WHEN** um elemento multiline possui texto com `
`
- **THEN** o canvas MUST exibir a quebra de linha e realizar wrap dentro do `maxWidth`
