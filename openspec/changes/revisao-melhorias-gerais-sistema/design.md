## Context

O sistema possui diferentes perfis de usuário (membro, admin, superadmin), cada um com necessidades distintas de visualização e controle. Atualmente, a dashboard não reflete as particularidades de cada plano/pacote, e o painel de controle do superadmin/admin carece de visão consolidada e interativa dos dados do sistema. Não há contadores de visitas em tempo real para os principais produtos (mentoria, cursos, palestras, eventos, site), e as métricas não são facilmente acessíveis pelos responsáveis de cada produto.

## Goals / Non-Goals

**Goals:**
- Dashboard personalizada para cada membro, exibindo informações e acessos conforme plano/pacote
- Dashboard do superadmin/admin com visão consolidada, contadores em tempo real e controles do sistema
- Contador de visitas em tempo real para cada serviço/produto (mentoria, cursos, palestras, eventos, site)
- Exibição de métricas e pesquisas de cada produto para seus responsáveis
- Interface clara, responsiva e organizada

**Non-Goals:**
- Não inclui refatoração completa de todos os módulos legados (apenas integração e exibição dos dados relevantes)
- Não prevê integração com sistemas externos de analytics (Google Analytics etc.) neste momento

## Decisions

- Utilizar websockets (ex: Laravel Echo + Pusher ou Redis) para atualização em tempo real dos contadores e métricas nas dashboards
- Estruturar componentes de dashboard em Blade/Tailwind, com widgets reutilizáveis para cada tipo de métrica
- Implementar lógica de exibição condicional baseada no plano/pacote do usuário (via middleware e policies customizadas)
- Para contadores de visitas, criar tabela dedicada (ex: `service_visits`) com eventos disparados via middleware HTTP e jobs para agregação
- Permitir que cada responsável por produto visualize apenas suas métricas, enquanto admin/superadmin têm visão global
- Configurações de widgets e permissões centralizadas em arquivo de config e/ou tabela de settings

## Risks / Trade-offs

- [Performance] → Atualização em tempo real pode aumentar carga do servidor; mitigação: usar broadcast eficiente e limitar frequência de updates
- [Segurança] → Exposição de métricas sensíveis; mitigação: policies rigorosas e validação de permissões
- [Complexidade] → Diferentes dashboards por perfil aumentam manutenção; mitigação: componentes bem isolados e documentação

## Migration Plan

- Implementar novos componentes de dashboard em paralelo ao sistema atual
- Migrar dados de visitas existentes (se houver) para nova estrutura
- Testar dashboards com perfis reais antes de ativar para todos
- Rollback: reverter para dashboards anteriores e desabilitar contadores em tempo real

## Open Questions

- Qual solução de broadcast será usada (Pusher, Redis, outro)?
- Alguma métrica adicional deve ser exibida por produto?