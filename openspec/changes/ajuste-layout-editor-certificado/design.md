## Context

O editor de certificado hoje concentra praticamente todas as configurações em uma única coluna lateral, o que gera muita rolagem e deixa uma grande área “ociosa” na tela em resoluções maiores. Também há rótulos com acentuação quebrada no painel de camadas/elementos por conta de strings incorretas no front-end.

Restrições importantes:
- Não alterar coordenadas/estilos persistidos (`x/y`, `fontSize`, `zIndex`, etc.) de forma implícita.
- Manter compatibilidade com o schema atual de `certificate_settings` (v2) e com certificados já configurados.
- Evitar BOM (UTF-8 com BOM) no repositório.

## Goals / Non-Goals

**Goals:**
- Distribuir melhor os painéis de configuração do editor (sidebar + painéis abaixo do canvas), reduzindo a sensação de “espaço em branco” e diminuindo rolagem na lateral.
- Melhorar a experiência de zoom (mais níveis) e aplicar um “fit” inicial ao abrir a aba do certificado, sem impactar persistência.
- Corrigir textos/labels do editor com acentuação quebrada.

**Non-Goals:**
- Alterar o pipeline de geração de PDF ou a renderização server-side do certificado.
- Migrar/alterar estrutura de dados de `certificate_settings`.
- Reescrever o editor (framework/stack) ou adicionar dependências externas.

## Decisions

- **Layout (Bootstrap grid):** ajustar as colunas para dar mais espaço ao painel e evitar sidebar “espremida” (ex.: `col-xl-8/col-xl-4`) e distribuir parte das configurações em uma faixa abaixo do canvas (cards em colunas).
- **Compatibilidade JS:** manter os mesmos `id`s e classes usados pelo JavaScript (ex.: `#cert-layers`, `#cert-style-controls`, `#cert-bg-fit`, etc.). A mudança é estrutural (layout), não comportamental.
- **Zoom/fit:** adicionar níveis extras de zoom (ex.: 200%, 250%, 300%) e disparar `fitCanvas()` quando a aba do certificado for exibida (`shown.bs.tab`), preservando a regra de que zoom não altera valores persistidos.
- **Acentuação:** corrigir as strings “mojibake” diretamente no template, garantindo que os arquivos permaneçam em UTF-8 sem BOM.

## Risks / Trade-offs

- **[Risco]** Mudanças de layout quebrarem seletores/eventos por dependerem de hierarquia no DOM → **Mitigação:** não renomear `id`s/classes e limitar alterações a wrappers/colunas.
- **[Trade-off]** Fit automático pode surpreender usuários acostumados ao zoom padrão → **Mitigação:** manter controle manual + botão Fit e não persistir zoom sem intenção explícita.
- **[Risco]** Ajustes em múltiplas telas (cursos/eventos/mentorias) podem divergir com o tempo → **Mitigação:** aplicar o mesmo padrão de layout nos três templates.

