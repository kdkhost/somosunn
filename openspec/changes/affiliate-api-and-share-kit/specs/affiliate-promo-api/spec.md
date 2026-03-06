## ADDED Requirements

### Requirement: API autenticada deve expor dados promocionais do afiliado
O sistema SHALL disponibilizar endpoints REST autenticados para que o afiliado consulte link, métricas, materiais de divulgação, ofertas e blocos de landing page.

#### Scenario: Consultar visão geral do afiliado
- **WHEN** um membro autenticado chamar a API de afiliado
- **THEN** o sistema MUST retornar o código de indicação, o link completo, resumo das métricas e informações básicas da marca

#### Scenario: Consultar materiais e landing page
- **WHEN** um membro autenticado chamar os endpoints de materiais ou landing page
- **THEN** o sistema MUST retornar payload estruturado e pronto para uso externo, sem depender do HTML do painel

### Requirement: API deve suportar acompanhamento analítico externo
O sistema SHALL permitir que o afiliado consuma métricas e eventos suficientes para montar dashboards próprios fora da plataforma.

#### Scenario: Consultar rastreio e funil
- **WHEN** um membro autenticado chamar o endpoint de analytics
- **THEN** o sistema MUST retornar resumo, canais, funil por origem e eventos detalhados paginados

#### Scenario: Exportar ou paginar dados externos
- **WHEN** o volume de eventos for maior que uma página
- **THEN** a API MUST respeitar paginação previsível para viabilizar painéis externos e integrações
