## Context

A area de perfil e feed ja existe com layout definido, mas ainda nao oferece um fluxo social completo. Precisamos ativar criacao/listagem de posts e interacoes, mantendo a aparencia atual, e garantir que a foto de perfil configurada do usuario apareca em todos os pontos relevantes.

## Goals / Non-Goals

**Goals:**
- Habilitar fluxo social (posts, comentarios, curtidas e compartilhamentos) preservando o layout atual.
- Garantir que a foto de perfil do usuario seja exibida no perfil e em cada item do feed.
- Definir contratos de API e dados para criacao, leitura e interacao no feed.

**Non-Goals:**
- Redesenhar o layout da area de perfil/feed.
- Implementar novas funcionalidades sociais fora do escopo (ex.: stories, mensagens diretas, grupos).
- Migracao de dados historicos inexistentes.

## Decisions

- Centralizar a logica de feed em um service dedicado (ex.: `app/Services/`) para reduzir acoplamento entre controllers e models.
  - Alternativa: logica distribuida nos controllers. Rejeitada por dificultar reuso e testes.
- Reaproveitar modelos existentes quando possivel e criar modelos novos apenas para entidades sociais que nao existirem.
  - Alternativa: criar tudo novo. Rejeitada para evitar duplicacao de dados e de regras.
- Foto de perfil deve ser resolvida por um unico ponto (helper/service) para evitar inconsistencias entre perfil e feed.
  - Alternativa: resolver em cada view. Rejeitada por risco de divergencia.
- Expor endpoints de feed via rotas web e/ou api conforme uso atual do projeto, mantendo o retorno necessario para Blade.
  - Alternativa: somente API e fetch no frontend. Rejeitada para nao alterar o fluxo atual.

## Risks / Trade-offs

- [Risco] A logica de feed pode impactar desempenho em listagens grandes. -> Mitigar com paginacao e queries otimizadas.
- [Risco] Inconsistencias de foto de perfil entre fontes antigas e novas. -> Mitigar com normalizacao e fallback padrao.
- [Trade-off] Manter layout atual limita algumas melhorias de UX. -> Aceito para atender requisito de aparencia.

## Migration Plan

- Deploy com endpoints e modelos novos sem quebrar fluxo atual.
- Atualizar views Blade para consumir os dados sociais, mantendo o HTML atual.
- Rollback: desativar novas rotas e remover chamadas nas views, mantendo layout original.

## Open Questions

- Quais modelos existentes ja representam posts/interacoes (se existirem) e devem ser reutilizados?
- A foto de perfil deve priorizar qual campo/relacao quando houver multiplas fontes?
- O feed sera apenas do usuario ou agregara conteudo de conexoes/seguidores?
