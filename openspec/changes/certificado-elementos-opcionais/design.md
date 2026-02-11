## Context

- O editor de certificado usa um canvas base A4 paisagem em 72 DPI (842x595) e persiste layout/estilos em `certificate_settings` (schema v2: `schemaVersion`, `meta`, `elements`).
- Cursos, eventos e mentorias possuem um editor visual em Blade com JS inline (drag/resize/visibilidade/camadas) e salvamento via campo oculto `certificate_settings`.
- A renderização do certificado (preview e PDF) usa o template `resources/views/admin/certificates/template.blade.php`.
- A geração de PDF ocorre em `app/Http/Controllers/Admin/CertificateController.php` com Dompdf, e fontes customizadas são embutidas via `CertificateFontCssGenerator`.

## Goals / Non-Goals

**Goals:**
- Permitir elementos opcionais no certificado (título, texto de apresentação e assinatura) controlados pelo editor, sem alterar coordenadas já gravadas.
- Tratar a assinatura como elemento de imagem com `x/y/width/height/zIndex` e respeitar `visible` no preview e no PDF.
- Garantir consistência de multiline entre editor e PDF (wrap e quebra de linha).
- Evitar problemas de encoding no PDF (UTF-8) e reduzir risco de labels com acentuação quebrada no painel de camadas.

**Non-Goals:**
- Implementar editor de texto rico (HTML) para elementos.
- "Embelezar" automaticamente layout (alinhamento/centralização) mudando posições salvas.
- Converter automaticamente formatos de fonte não suportados pelo Dompdf (ex.: WOFF2).

## Decisions

- **Novos elementos como chaves em `elements`:**
  - `title` e `presentation_text` são elementos de texto e usam `multiline: true`, `maxWidth` e `textAlign` para controlar wrap.
  - `instructor_signature` é um elemento de imagem (preview via CSS `background-image` no editor e `<img>` no template), com `width/height` em px e `x/y` em %.
- **Renderização fiel ao persistido:**
  - O template renderiza apenas os elementos persistidos e respeita `visible`, `zIndex`, `fontFamily`, `fontSize`, etc.
  - `platform_logo` continua obrigatório (sempre visível).
- **Compatibilidade retroativa:**
  - Se houver arquivo de assinatura, mas o elemento `instructor_signature` ainda não existir no JSON, o template usa um fallback compatível (posição antiga) para não quebrar certificados antigos.
- **Multiline consistente:**
  - Para `multiline`, o editor e o template usam `white-space: pre-line` para preservar quebras de linha (e ainda permitir wrap).
- **Encoding no PDF:**
  - O Dompdf passa a receber HTML explicitamente como UTF-8 (`loadHtml($html, 'UTF-8')`) para evitar mojibake no PDF.
- **Labels em JS com segurança de encoding:**
  - `tagLabels` passa a ser gerado via `@json(...)` nos formulários, reduzindo risco de quebra de acentuação por encoding (sem BOM).

## Risks / Trade-offs

- [Mais elementos no JSON] → Elementos novos entram como defaults (ocultos por padrão) e podem aparecer na lista de camadas; mitigado mantendo `visible: false` para título/apresentação e `visible` da assinatura dependente de existência do arquivo.
- [Mudan?a de wrap em multiline] → `pre-line` pode alterar levemente a quebra de texto vs. `normal`; mitigado por manter `maxWidth` e aplicar o mesmo comportamento no PDF.
- [Fontes não suportadas] → Se uma fonte ativa estiver somente em WOFF2, o PDF pode cair em fallback; mitigado via regras existentes do gerador e orientação ao admin.
