## Context

O sistema possui dashboards distintas para membros, admin e superadmin, mas há limitações: widgets e métricas não são totalmente dinâmicos, a performance pode ser prejudicada por queries pesadas e não há atualização em tempo real. O crescimento do número de usuários e a diversificação de planos aumentaram a necessidade de dashboards personalizadas, responsivas e eficientes. O painel de membros usa Tailwind, enquanto o de superadmin permanece em AdminLTE. Permissões são controladas por traits, policies e middlewares, mas a lógica de exibição de widgets ainda pode ser centralizada e otimizada.

## Goals / Non-Goals

**Goals:**
- Exibir dashboards dinâmicas, com widgets e gráficos adaptados ao plano, permissões e papel do usuário.
- Garantir atualização em tempo real de métricas críticas (visitas, vendas, etc.) usando websockets (Laravel Echo + Redis/Pusher).
- Otimizar queries e uso de cache para evitar sobrecarga no banco de dados.
- Centralizar lógica de permissões e exibição de widgets em traits, policies e middlewares customizados.
- Garantir responsividade e experiência fluida em todos os dispositivos.
- Registrar logs de acesso e tentativas negadas para auditoria.

**Non-Goals:**
- Não migrar o painel de superadmin para Tailwind (AdminLTE permanece).
- Não alterar a lógica de negócio dos módulos existentes.
- Não remover funcionalidades já presentes nos painéis.

## Decisions

- **Stack de tempo real:** Usar Laravel Echo com Redis (preferencial) ou Pusher para atualização instantânea dos widgets e gráficos.
- **Estrutura de widgets:** Criar componentes Blade reutilizáveis para cada tipo de métrica, com lógica condicional baseada em permissões/plano.
- **Otimização de queries:** Utilizar Eloquent com eager loading, chunking e cache (ex: Redis) para métricas agregadas. Jobs periódicos para agregação de dados pesados.
- **Controle de acesso:** Middleware check.plan, check.feature e traits HasRoles/HasFeatureAccess para garantir exibição correta dos widgets.
- **Logs e auditoria:** Registrar tentativas de acesso negadas e ações críticas em tabela de logs.
- **Migração suave:** Implementar feature flags para liberar gradualmente as novas dashboards.

## Risks / Trade-offs

- [Risco] Websockets podem aumentar a complexidade de infraestrutura → Mitigação: Documentar setup e fornecer fallback para polling.
- [Risco] Queries mal otimizadas podem sobrecarregar o banco → Mitigação: Testes de carga, uso de cache e jobs para agregação.
- [Risco] Mudanças em permissões podem gerar inconsistências → Mitigação: Testes automatizados e revisão de policies/middlewares.
- [Risco] Adaptação visual pode causar bugs em dispositivos antigos → Mitigação: Testes cross-browser e responsividade via Tailwind.

## Migration Plan

- Implementar novos widgets e lógica de exibição em ambiente de staging.
- Ativar feature flag para liberar gradualmente para grupos de usuários.
- Monitorar métricas de performance e logs de erro.
- Caso necessário, rollback para dashboards antigas via flag.

## Open Questions

- Quais métricas adicionais são críticas para cada tipo de usuário?
- Algum widget específico deve ser priorizado na primeira entrega?
- Há restrições de infraestrutura para uso de Redis/Pusher?
