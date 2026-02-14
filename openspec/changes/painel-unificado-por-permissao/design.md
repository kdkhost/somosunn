## Context

O sistema atual utiliza o AdminLTE 3.2 como painel administrativo único para todos os perfis de usuário. Isso limita a experiência dos membros comuns, dificulta a manutenção de permissões e impede a personalização visual alinhada ao site público. O objetivo é separar o painel do superadmin (mantendo AdminLTE) e criar um painel moderno, responsivo e seguro para membros/usuários, aproveitando o layout do site e garantindo todas as funções administrativas necessárias.

## Goals / Non-Goals

**Goals:**
- Implementar painel exclusivo para membros/usuários, com layout próprio (Tailwind, responsivo, sem AdminLTE).
- Manter AdminLTE 3.2 apenas para superadmin.
- Replicar todas as funções administrativas no novo painel, respeitando permissões, planos e pacotes.
- Garantir responsividade, usabilidade e coerência visual em todos os dispositivos.
- Corrigir e reforçar permissões em rotas, controllers e views.
- Garantir transição suave e sem perda de funcionalidades.

**Non-Goals:**
- Não alterar a lógica de negócios dos módulos existentes.
- Não migrar o superadmin para o novo painel.
- Não remover o AdminLTE do projeto.

## Decisions

- **Stack do novo painel:** Tailwind CSS via CDN, componentes Blade, JS vanilla ou Alpine.js para interatividade leve.
- **Estrutura de views:** Nova pasta resources/views/member/ para o painel de membros, mantendo resources/views/admin/ para o superadmin.
- **Controle de acesso:** Middleware check.role e check.plan reforçados, rotas separadas por prefixo (/admin para superadmin, /painel para membros).
- **Permissões:** Uso de traits HasRoles e HasFeatureAccess no User, validação centralizada nos controllers e policies.
- **Assets:** AdminLTE e dependências apenas para superadmin; Tailwind e assets customizados para membros.
- **Botões e componentes:** Utilizar classes utilitárias do Tailwind para garantir responsividade e evitar quebra de linhas.
- **Reaproveitamento:** Funções administrativas replicadas via controllers/services compartilhados, views adaptadas conforme role.

## Risks / Trade-offs

- [Risco] Divergência de funcionalidades entre painéis → Mitigação: checklist de features e testes cruzados.
- [Risco] Quebra de layout em telas complexas → Mitigação: uso intensivo de Tailwind e testes responsivos.
- [Risco] Permissões inconsistentes → Mitigação: revisão de policies, traits e middlewares.
- [Trade-off] Duplicidade de views para algumas telas → Aceito para garantir UX adequada por perfil.
- [Risco] Carga inicial de manutenção maior → Mitigação: documentação e padronização de componentes.
