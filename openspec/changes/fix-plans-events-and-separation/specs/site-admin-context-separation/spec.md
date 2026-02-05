# Spec: Site/Admin Context Separation

## ADDED Requirements

### Requirement: Public and admin routes are separated and non-conflicting
O sistema MUST separar rotas públicas (Site) e rotas administrativas (Admin) de forma explícita:
- Rotas do Admin MUST estar sob prefixo `/admin` e usar nomes `admin.*`.
- Rotas do Site MUST NÃO usar prefixo `/admin` e MUST usar nomes fora de `admin.*`.
- MUST NOT existir duplicidade de nomes de rota para páginas públicas de eventos (`events.index`, `events.show`).

#### Scenario: Generating public event URLs
- **WHEN** o sistema gera URLs para `events.index` e `events.show`
- **THEN** as URLs MUST iniciar com `/eventos` e MUST NOT apontar para `/admin/...`

#### Scenario: Generating admin event management URLs
- **WHEN** o sistema gera URLs para `admin.events.*`
- **THEN** as URLs MUST iniciar com `/admin/events` e MUST NOT apontar para `/eventos/...`

### Requirement: Public events pages use site layout and never extend admin layout
As views públicas de eventos MUST manter o contexto do Site. Elas MUST estender o layout do Site (ex.: `layouts.app`) e MUST NOT estender `admin.layouts.app`.

#### Scenario: Visiting public events list keeps site context
- **WHEN** um visitante acessa `GET /eventos`
- **THEN** a página SHALL renderizar com layout do Site
- **THEN** a página MUST NOT renderizar sidebar/breadcrumb do Admin nem links para `admin.*` como navegação principal

### Requirement: Admin navigation does not route to the public site by default
No Admin, o item “Eventos → Calendário” MUST levar ao calendário administrativo e MUST permanecer no contexto do Admin.

#### Scenario: Admin clicks events calendar menu
- **WHEN** um admin clica em “Eventos → Calendário”
- **THEN** o sistema MUST navegar para uma rota `admin.events.*` (ex.: `GET /admin/events`)
- **THEN** o usuário MUST permanecer dentro do layout do Admin

### Requirement: Public navigation does not route into admin
No Site, links de navegação e links de detalhes de eventos MUST utilizar apenas rotas públicas (`events.*`) e MUST NOT direcionar o usuário para `/admin`.

#### Scenario: Visitor opens event details from the list
- **WHEN** um visitante clica em “Detalhes” de um evento na listagem pública
- **THEN** o sistema MUST navegar para `GET /eventos/{event}` (rota pública)
- **THEN** o visitante MUST NOT ser redirecionado para `/admin/...`

