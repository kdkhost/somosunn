# Spec: Event Calendar Management

## MODIFIED Requirements

### Requirement: Admin FullCalendar consumes a reliable JSON feed
O calendário de eventos no Admin (FullCalendar v4) MUST consumir um endpoint dedicado que SEMPRE retorna JSON, independentemente de headers como `X-Requested-With`.

#### Scenario: FullCalendar requests events for a date range
- **WHEN** o FullCalendar faz `GET /admin/events/feed?start=<iso>&end=<iso>`
- **THEN** o endpoint MUST responder `200` com `Content-Type: application/json`
- **THEN** o payload MUST ser uma lista de eventos no formato esperado pelo FullCalendar v4 (`id`, `title`, `start`, `end`, `allDay`, `backgroundColor`, `borderColor`, `textColor`, `extendedProps`)

### Requirement: Admin calendar displays persisted events from the database
Eventos já gravados no banco MUST aparecer no calendário administrativo automaticamente quando a página do calendário for carregada.

#### Scenario: Existing event appears on admin calendar
- **WHEN** um admin acessa a página do calendário
- **THEN** o calendário SHALL renderizar os eventos retornados pelo feed para o range atual

### Requirement: Admin calendar event click stays within admin (modal/edit)
Ao clicar em um evento no calendário do Admin, o sistema MUST permanecer no contexto do Admin (modal/edição) e MUST NOT redirecionar para o Site.

#### Scenario: Clicking an event opens modal and does not navigate away
- **WHEN** um admin clica em um evento no FullCalendar
- **THEN** o sistema SHALL abrir o modal de edição (ou tela administrativa de edição) com os dados do evento
- **THEN** o browser MUST NOT navegar para uma URL pública (`/eventos/...`)

### Requirement: Event datetime updates do not suffer timezone drift
Ao criar/editar eventos via Admin (formulário, modal e interações do calendário como drag/resize), o sistema MUST persistir `start_at`/`end_at` de modo que, ao recarregar, o evento continue aparecendo no mesmo horário local exibido ao admin.

#### Scenario: Drag-and-drop preserves local time after reload
- **WHEN** um admin arrasta um evento no calendário para um novo horário e o sistema salva a mudança
- **THEN** após recarregar a página, o evento SHALL aparecer exatamente no novo horário local selecionado

### Requirement: Public events pages are accessible without login but only show published events
As páginas públicas de eventos MUST ser acessíveis sem autenticação, porém MUST expor apenas eventos publicados:
- `GET /eventos` MUST listar apenas eventos com `published = true` e `start_at >= now()`.
- `GET /eventos/{event}` MUST retornar `404` para eventos com `published = false`.

#### Scenario: Guest can view an upcoming published event
- **WHEN** um visitante acessa `GET /eventos`
- **THEN** a lista SHALL incluir eventos `published = true` futuros (gratuitos ou pagos)

#### Scenario: Guest cannot access an unpublished event detail page
- **WHEN** um visitante acessa `GET /eventos/{event}` para um evento com `published = false`
- **THEN** o sistema MUST responder `404`

## ADDED Requirements

### Requirement: Paid events require payment to confirm seat/reservation
Eventos no Site são públicos para visualização, mas a confirmação de vaga (consumação/ingresso) MUST depender do valor vigente do evento:
- Se `current_price > 0`, o sistema MUST exigir pagamento aprovado antes de confirmar a vaga.
- Se `current_price = 0`, o sistema MUST permitir confirmar a vaga sem pagamento.
- Se `capacity` estiver definida, o sistema MUST impedir confirmação quando não houver vagas disponíveis.

#### Scenario: Paid event requires successful payment
- **WHEN** um visitante solicita confirmar vaga em um evento com `current_price > 0`
- **THEN** o sistema MUST direcionar para um fluxo de checkout/pagamento
- **THEN** a vaga MUST ser confirmada apenas após o pagamento ser aprovado

#### Scenario: Free event allows reservation without payment
- **WHEN** um visitante solicita confirmar vaga em um evento com `current_price = 0`
- **THEN** o sistema MUST confirmar a vaga sem exigir pagamento

