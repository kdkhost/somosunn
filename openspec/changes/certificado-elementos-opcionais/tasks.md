## 1. Editor (Cursos / Eventos / Mentorias)

- [x] 1.1 Adicionar controles e toggles para `title`, `presentation_text` e `instructor_signature` no editor
- [x] 1.2 Renderizar `instructor_signature` como imagem no canvas (placeholder, preview no upload, resize)
- [x] 1.3 Garantir multiline consistente no editor (`white-space: pre-line` + `maxWidth`)
- [x] 1.4 Gerar `tagLabels` via `@json(...)` para evitar problema de encoding/acentuação quebrada

## 2. Template e PDF

- [x] 2.1 Renderizar assinatura pelo elemento `instructor_signature` (posição, tamanho, `visible`, `zIndex`) com fallback compatível
- [x] 2.2 Preservar quebras de linha de elementos multiline no template (`white-space: pre-line`)
- [x] 2.3 Forçar Dompdf a carregar HTML em UTF-8 para evitar mojibake no PDF

## 3. Verificação

- [x] 3.1 Rodar checagem de views (Blade) para garantir que o template compila
- [x] 3.2 Verificar ausência de BOM nos arquivos alterados
