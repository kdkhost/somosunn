## Why

O editor de certificado está com um layout pouco eficiente: sobra muito espaço em branco na área de trabalho enquanto as configurações ficam concentradas apenas na lateral, exigindo rolagem e dificultando o fluxo de edição. Além disso, alguns rótulos aparecem com acentuação quebrada (ex.: “ConclusÃ£o”), causando confusão e reduzindo a qualidade percebida do sistema.

## What Changes

- Reorganizar a tela do editor para distribuir as configurações em painéis mais equilibrados (sidebar + painéis abaixo do canvas), reduzindo rolagem e aproveitando melhor a área útil da página.
- Melhorar o comportamento de zoom/fit para preencher melhor o espaço disponível (mais níveis de zoom e ajuste automático ao abrir a aba), sem alterar coordenadas ou estilos persistidos.
- Corrigir rótulos/labels com acentuação quebrada no editor (camadas/elementos), garantindo strings em UTF-8 sem BOM.
- Manter a fidelidade do certificado: nenhuma mudança deve “recalcular” posições ou estilos já gravados no editor.

## Capabilities

### New Capabilities

<!-- Nenhuma capability nova: é um ajuste incremental em cima do editor já existente -->

### Modified Capabilities

- `certificate-editor-layout-refresh`: Ajustar a organização dos painéis para distribuir melhor as configurações na página e evitar excesso de espaço vazio, mantendo o fluxo “toolbar → canvas → propriedades”.
- `certificate-editor-toolkit`: Refinar zoom/fit e corrigir labels/nomes exibidos no painel de camadas/elementos (acentuação correta), sem impactar persistência.

## Impact

- **Front-end/Admin**: Views Blade do editor de certificado em cursos (e eventualmente eventos/mentorias) e seus trechos de JS/CSS.
- **UX/Produtividade**: Menos rolagem, melhor aproveitamento de tela e melhor legibilidade (acentuação correta).
- **Compatibilidade**: Nenhuma alteração no formato persistido de `certificate_settings` e nenhum impacto no pipeline de PDF além do que já existe (mantém fidelidade).

