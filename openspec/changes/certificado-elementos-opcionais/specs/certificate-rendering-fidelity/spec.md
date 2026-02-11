## ADDED Requirements

### Requirement: Assinatura respeita posição e visibilidade persistidas
Quando existir arquivo de assinatura e o elemento `instructor_signature` estiver presente em `certificate_settings.elements`, o renderizador MUST posicionar a assinatura usando `x/y` (em %) e `width/height` (em px), respeitando `zIndex` e `visible`.

#### Scenario: Elemento de assinatura é renderizado conforme layout salvo
- **WHEN** `certificate_settings.elements.instructor_signature` possui `x`, `y`, `width`, `height` e `visible: true`
- **THEN** o certificado (preview e PDF) MUST renderizar a assinatura em `left: x%`, `top: y%`, `width: <px>`, `height: <px>`

#### Scenario: Assinatura oculta não é renderizada
- **WHEN** `certificate_settings.elements.instructor_signature.visible` é `false`
- **THEN** o certificado (preview e PDF) MUST omitir a assinatura

#### Scenario: Compatibilidade com certificados antigos sem elemento de assinatura
- **WHEN** existe arquivo de assinatura, mas `certificate_settings` não possui `elements.instructor_signature`
- **THEN** o certificado MUST renderizar a assinatura em um fallback compatível (sem exigir reposicionamento no editor)

### Requirement: PDF é gerado com encoding UTF-8
A geração de PDF MUST carregar o HTML como UTF-8 para evitar problemas de acentuação/mojibake em textos do certificado.

#### Scenario: Acentuação é preservada no PDF
- **WHEN** um certificado contém texto com caracteres acentuados (ex.: "Conclusão", "Validação", "Horária")
- **THEN** o PDF MUST renderizar esses textos corretamente

### Requirement: Multiline preserva quebras de linha na renderização
Quando um elemento está marcado como `multiline: true`, o renderizador MUST preservar quebras de linha no preview e no PDF (além do wrap por `maxWidth`).

#### Scenario: Texto multiline com `
` é renderizado em mais de uma linha
- **WHEN** um elemento multiline possui texto com `
`
- **THEN** o certificado (preview e PDF) MUST exibir as quebras de linha de forma consistente
