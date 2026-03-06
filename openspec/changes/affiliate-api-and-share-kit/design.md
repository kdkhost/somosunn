## Context

O sistema já possui rastreio detalhado de afiliados no painel e endpoints REST públicos para catálogo institucional, mas ainda não existe uma camada orientada ao afiliado para consumo externo. O novo escopo cruza painel, API autenticada via Sanctum, catálogo público e conteúdo institucional do CMS.

## Goals / Non-Goals

**Goals:**
- Entregar materiais prontos para divulgação dentro do painel do afiliado.
- Expor uma API REST autenticada para consumo de métricas, ofertas, branding e blocos de landing page.
- Reaproveitar o conteúdo já existente do CMS, Settings e catálogo público sem exigir cadastro duplicado de campanhas.
- Permitir que o afiliado use token próprio para montar páginas e painéis externos.

**Non-Goals:**
- Não criar editor visual de landing page externo.
- Não criar webhook em tempo real para terceiros nesta etapa.
- Não introduzir nova dependência de analytics ou geolocalização além do que já é coletado.

## Decisions

- **Centralizar o kit em um serviço dedicado**: um novo serviço agregará link de afiliado, branding, conteúdos prontos e ofertas. Isso evita lógica espalhada entre painel e API.
- **Expor API autenticada com Sanctum**: o afiliado usará o token já suportado pela API existente. Isso preserva coerência de autenticação e evita inventar um mecanismo paralelo.
- **Reaproveitar o catálogo existente**: planos, cursos, eventos, mentorias, depoimentos e CMS alimentarão o kit. A alternativa seria criar um módulo manual de campanhas, mas isso aumentaria manutenção e risco de divergência.
- **Entregar payload estruturado para landing page**: em vez de HTML pronto, a API retornará blocos normalizados (`hero`, `benefits`, `proof`, `offers`, `cta`). Isso dá flexibilidade para o membro montar site próprio em qualquer stack.
- **Acoplar o painel ao mesmo serviço da API**: o painel mostrará os mesmos materiais e endpoints já consumidos pela API, reduzindo risco de inconsistência.

## Risks / Trade-offs

- **[Conteúdo incompleto no CMS]** → Usar fallback para `config/app`, `Setting` e mensagens padrão de campanha.
- **[Catálogo muito grande para payload externo]** → Limitar ofertas por tipo e expor apenas itens publicados/ativos.
- **[Token exposto em frontend externo]** → Documentar uso recomendado via backend/proxy do afiliado e manter endpoints somente autenticados.
- **[Dados divergentes entre painel e API]** → Consumir tudo a partir de serviços compartilhados, sem duplicação de regras no controller.

## Migration Plan

- Não há migration obrigatória nesta etapa.
- Deploy exige apenas `git pull`, `php artisan optimize:clear` e cache de views.
- Rollback consiste em reverter rotas/controladores/views adicionados; não há impacto em dados persistidos.

## Open Questions

- Nenhuma crítica para implementação inicial. A primeira versão focará em kit pronto + API autenticada para consumo externo.
