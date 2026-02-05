# Tarefas: Planos, Eventos e Separação Site/Admin

## 1. Rotas públicas de eventos (Site)

- [x] 1.1 Consolidar rotas públicas de eventos em um único bloco (`/eventos` e `/eventos/{event}`) e remover duplicidades/conflitos de `events.show`
- [x] 1.2 Remover `check.feature:events` das rotas públicas (eventos públicos sem login) e padronizar a regra para listar apenas `published = true`
- [x] 1.3 Trocar `EventController@show($id)` para usar route-model binding (`show(Event $event)`) e retornar `404` se `published = false`
- [x] 1.4 Ajustar `EventController@index` para listar eventos publicados futuros (gratuitos e pagos), ordenados por `start_at`

## 2. Views e navegação (separação de contexto)

- [x] 2.1 Corrigir a listagem pública `resources/views/events/index.blade.php` para usar layout do Site (`layouts.app`) e remover breadcrumbs/links de Admin
- [x] 2.2 Auditar links em views públicas de eventos para garantir que usem apenas `route('events.*')` (nunca `admin.*`)
- [x] 2.3 Ajustar menu do Admin: “Eventos → Calendário” MUST apontar para `route('admin.events.index')` (não `route('events.index')`)
- [x] 2.4 Ajustar o widget de calendário do `resources/views/admin/dashboard.blade.php` para não navegar para `/eventos/...` (usar `admin.events.edit` ou remover `url`)

## 3. FullCalendar v4 no Admin (feed JSON confiável)

- [x] 3.1 Criar rota dedicada `admin.events.feed` (`GET /admin/events/feed`) que SEMPRE retorna JSON (independente de `$request->ajax()`)
- [x] 3.2 Implementar método no `App\Http\Controllers\Admin\EventController` para o feed: filtrar por `start`/`end` e retornar payload compatível com FullCalendar v4
- [x] 3.3 Alterar `resources/views/admin/events/calendar.blade.php` para consumir `admin.events.feed` em `events: ...`
- [x] 3.4 Resolver drift de fuso/horário no modal e no drag/resize: padronizar formato enviado pelo JS e normalização no backend (`start_at`/`end_at`)
- [x] 3.5 Validar click no evento no calendário do Admin: abrir modal/edição e MUST NOT redirecionar para rotas públicas

## 4. Planos: vitrine reativa + destaque único

- [x] 4.1 Atualizar `App\Http\Controllers\Admin\PlanController` para salvar `slug` e `description` (e gerar `slug` automaticamente quando vazio, garantindo unicidade)
- [x] 4.2 Normalizar entrada de preço no Admin (aceitar `49,90` / `R$ 49,90` e persistir decimal)
- [x] 4.3 Implementar regra “apenas 1 destaque”: ao salvar plano com `highlight = true`, desmarcar `highlight` de todos os outros em transação
- [x] 4.4 Criar migração/backfill para compatibilidade: copiar `is_featured` e/ou `highlight_legacy` para `highlight` (garantindo no máximo 1 destacado)
- [x] 4.5 Ajustar `resources/views/site/premium.blade.php` para o CTA final usar plano do banco (destacado → mais barato pago → primeiro ativo) e remover preço hardcoded

## 5. Eventos públicos: confirmação de vaga com pagamento (consumação)

- [x] 5.1 Criar tabela/modelo de inscrições (ex.: `event_registrations`): `event_id`, `user_id`, `order_id`, `status`, `price`, `quantity`, timestamps
- [x] 5.2 Implementar regra de capacidade: bloquear confirmação se `capacity` atingida (considerar apenas registros `paid`/`confirmed`)
- [x] 5.3 Criar endpoint público para iniciar confirmação de vaga (ex.: `POST /eventos/{event}/reservar`) com suporte a visitante (criar conta se necessário) e idempotência
- [x] 5.4 Criar `Order` + `OrderItem` do tipo `event` usando `current_price` no momento da compra (snapshot do preço)
- [x] 5.5 Integrar pagamento (MercadoPago) e webhook: ao aprovar pagamento, marcar `orders.status = paid` e confirmar a inscrição do evento
- [x] 5.6 Atualizar `resources/views/events/show.blade.php` para o botão principal iniciar o fluxo correto (pago → checkout; grátis → confirmar vaga), com bloqueio para lotação e modo demo

## 6. Performance e hardening (ajustes sem “mexer demais”)

- [x] 6.1 Adicionar índices no banco para queries críticas (ex.: `events(published,start_at)`, `plans(is_active,highlight)`, `event_registrations(event_id,status)`)
- [x] 6.2 Remover/encapsular fallbacks de “demo data” para evitar divergência (usar flag `DEMO_MODE`/setting e nunca misturar com dados reais)
- [x] 6.3 Criar testes de rota básicos: eventos públicos não exigem login; evento não publicado retorna 404; feed do calendário admin retorna JSON; destaque único de plano

## 7. QA / validação final

- [ ] 7.1 Validar fluxo Admin → Site: marcar destaque no Admin e confirmar que `/premium` reflete na próxima atualização
- [ ] 7.2 Validar calendário Admin: eventos existentes aparecem; criar/editar/mover evento reflete sem drift de horário
- [ ] 7.3 Validar Site: lista de eventos usa layout do Site; “Detalhes” mantém usuário no Site; reserva/compra confirma vaga corretamente
