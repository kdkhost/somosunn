## Context

A página de oportunidades de carreira atualmente apresenta um layout simples, com poucas vagas e sem recursos de filtragem ou destaque visual. O objetivo é modernizar a experiência, tornando-a mais atraente, organizada e funcional, alinhada com o crescimento da comunidade e a necessidade de engajamento dos usuários. O sistema utiliza Laravel, Tailwind CSS e já possui models para vagas (JobVacancy).

## Goals / Non-Goals

**Goals:**
- Redesenhar a página de oportunidades com layout moderno e responsivo.
- Exibir vagas dinamicamente, com dados reais do banco.
- Implementar filtros por área, local, empresa e tipo de vaga.
- Destacar empresas parceiras e vagas premium.
- Permitir candidatura rápida e visualização de detalhes.
- Melhorar acessibilidade e usabilidade.

**Non-Goals:**
- Não incluir integração com sistemas externos de vagas (ex: LinkedIn).
- Não implementar painel de gestão de vagas (admin) neste change.
- Não alterar o fluxo de aprovação de vagas.

## Decisions

- Utilizar Tailwind CSS para garantir visual moderno e responsivo.
- Reaproveitar o model JobVacancy para exibição das vagas.
- Implementar filtros com JavaScript leve (Alpine.js ou vanilla JS) para não sobrecarregar o backend.
- Botão de candidatura rápida: redireciona para página de detalhes ou abre modal.
- Destaque visual para empresas: badge, cor diferenciada ou card especial.
- Acessibilidade: garantir contraste, navegação por teclado e uso de ARIA.
- Manter rotas separadas para testes e produção até validação do novo layout.

## Risks / Trade-offs

- [Risco] Filtros client-side podem não escalar com grande volume de vagas → [Mitigação] Implementar paginação e filtros server-side se necessário.
- [Risco] Mudança visual pode impactar usuários acostumados com layout antigo → [Mitigação] Disponibilizar versão beta/teste antes de substituir.
- [Risco] Candidatura rápida pode gerar candidaturas incompletas → [Mitigação] Validar campos obrigatórios e permitir revisão antes do envio.
- [Risco] Destaque visual pode gerar conflito de branding entre empresas → [Mitigação] Definir regras claras para destaque e personalização.

## Migration Plan

- Implementar nova página e rotas de teste.
- Validar com usuários e empresas parceiras.
- Migrar para rota oficial após aprovação.
- Rollback: manter layout antigo disponível até migração completa.

## Open Questions

- Quais critérios para destacar vagas premium?
- Como integrar sugestões de vagas com perfil do usuário?
- Quais métricas de engajamento serão monitoradas?
