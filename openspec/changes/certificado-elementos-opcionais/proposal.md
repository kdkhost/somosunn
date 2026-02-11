## Why

Hoje o certificado não cobre bem alguns itens comuns na personalização: título do certificado, texto de apresentação e a assinatura do instrutor/mentor/organizador como um elemento do layout. Isso faz com que parte das informações fique "fora" do editor (ou fixa no template) e o PDF nem sempre reflita 100% do que foi configurado no editor, especialmente quando há ajuste fino de posição/visibilidade.

## What Changes

- Adicionar três elementos opcionais no editor: `title`, `presentation_text` e `instructor_signature`, com opção clara de mostrar/ocultar (adição/remoção) sem alterar coordenadas já salvas.
- Tratar `instructor_signature` como **elemento de imagem** no canvas (com resize e posição via `x/y/width/height/zIndex`) e respeitar sua visibilidade na renderização do certificado.
- Garantir que preview e PDF respeitem quebra de linhas para elementos `multiline` e mantenham fidelidade do layout conforme gravado no editor.
- Fortalecer a geração do PDF com encoding UTF-8 para evitar problemas de acentuação/mojibake.

## Capabilities

### New Capabilities

<!-- Nenhuma capability nova: é uma evolução incremental do editor/renderizador existente -->

### Modified Capabilities

- `certificate-editor-toolkit`: incluir controles e comportamento para título, texto de apresentação e assinatura como elementos opcionais, incluindo preview no canvas e respeito à visibilidade.
- `certificate-rendering-fidelity`: renderizar PDF/preview usando somente o `certificate_settings` persistido (incluindo assinatura como imagem), com encoding UTF-8 e multiline consistente.

## Impact

- **Front-end/Admin**: ajustes nos formulários (Blade/JS) do editor de certificado (cursos/eventos/mentorias) para exibir e manipular os novos elementos opcionais.
- **Template/PDF**: atualização do template de certificado para renderizar assinatura como elemento e respeitar `visible`, além de manter quebra de linhas em `multiline`.
- **Compatibilidade**: não muda o conceito de coordenadas nem "recalcula" layout. Certificados antigos continuam funcionando com fallback quando o elemento de assinatura não existe no JSON.
