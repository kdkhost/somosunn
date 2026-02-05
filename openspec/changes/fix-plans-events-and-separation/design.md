## Context

O sistema hoje mistura responsabilidades entre **Site (público)** e **Admin (gestão)** em rotas, views e links. Isso gera efeitos colaterais visíveis:

- A listagem pública de eventos (`/eventos`) está renderizando com layout do Admin (ex.: `resources/views/events/index.blade.php` estende `admin.layouts.app`), criando a sensação de “redirecionar para o admin”.
- Existem rotas duplicadas e conflitantes para eventos no `routes/web.php` (ex.: múltiplas definições de `/eventos/{...}` e nome `events.show`), o que torna o comportamento frágil e difícil de manter.
- No Admin, o FullCalendar v4 não exibe eventos já cadastrados porque o endpoint que deveria retornar JSON depende de `$request->ajax()`; o FullCalendar pode não enviar o header esperado e o controller acaba retornando HTML ao invés de JSON.
- A gestão de planos e “destaque” (highlight/featured) está inconsistente entre colunas e abordagens legadas (`highlight`, `highlight_legacy`, `is_featured`) e até entre modelos de permissão (JSON em `plans.permissions` vs. pivot `permission_plan`). Isso facilita divergência entre o que está no banco e o que a vitrine do Site renderiza.

O objetivo desta change é “desembaraçar” essas camadas, garantindo que **o que é do Site fique no Site** e **o que é do Admin fique no Admin**, com dados do banco como fonte de verdade.

## Goals / Non-Goals

**Goals:**
- A vitrine de planos do Site (`/premium`) deve refletir 100% os planos ativos do banco e seu estado de destaque na próxima renderização (sem depender de mock/hardcode ou rotas erradas).
- Ao atualizar um plano no Admin (ex.: marcar “Destacar”), a mudança deve ser refletida no Site de forma imediata na próxima visita/refresh, sem necessidade de “limpar cache” manual.
- O FullCalendar v4 no Admin deve consumir um feed JSON confiável e exibir os eventos do banco sempre.
- Separação total de contexto: views públicas não devem depender de layout, breadcrumb ou rotas `admin.*`; views administrativas não devem empurrar o usuário para o Site sem intenção explícita.
- Eliminar rotas duplicadas, nomes de rotas conflitando e comportamentos implícitos frágeis.

**Non-Goals:**
- Redesenhar UI/UX do Site ou do Admin (apenas correções estruturais e consistência).
- Implementar “tempo real” via WebSocket para refletir mudanças sem refresh (pode ser melhoria futura, não requisito).
- Reescrever toda a camada de permissões/planos do sistema em uma única tacada; a prioridade é estabilizar e padronizar os fluxos afetados (planos + eventos + calendário) sem regressões.

## Decisions

1) **Separação explícita de rotas (Site vs Admin)**
- Consolidar as rotas públicas de eventos em um único bloco (ex.: `/eventos` e `/eventos/{event}`), removendo duplicações.
- Definir um único padrão para o `events.show` no Site (preferencialmente com route-model binding: `{event}`) e manter consistência também no controller público.
- Manter rotas do Admin apenas sob `Route::prefix('admin')->name('admin.')...` e garantir que o Site nunca gere URL para `admin.*`.

2) **Separação explícita de views/layouts**
- Corrigir o template público de listagem de eventos para usar o layout do Site (ex.: `layouts.app`) e mover/renomear views para uma convenção clara (`resources/views/site/events/*`), evitando que “eventos do site” apareçam com UI do Admin.
- Auditar links/breadcrumbs em views públicas para remover referências a `route('admin.*')`.
- Auditar views do Admin para evitar links para `route('events.*')` (site) quando o contexto é gestão (preferir `admin.events.*`).

3) **Feed do FullCalendar v4 como endpoint dedicado**
- Não sobrecarregar `GET /admin/events` (resource index) para responder “HTML OU JSON” baseado em heurística frágil.
- Criar um endpoint dedicado (ex.: `GET /admin/events/feed`) que **sempre** retorna JSON e que será usado pela opção `events:` do FullCalendar v4.
- O payload seguirá o contrato do FullCalendar v4 (`id`, `title`, `start`, `end`, `allDay`, `backgroundColor`, `borderColor`, `textColor`, `extendedProps...`) usando ISO8601 (aproveitando `Event::$appends = ['start','end']`).
- O feed filtrará por intervalo (`start`/`end`) considerando eventos que cruzam o range (eventos iniciados antes do fim e que terminam depois do início, ou sem `end_at`).

4) **Fonte de verdade e normalização do “destaque” de planos**
- Adotar `plans.highlight` como sinal canônico usado pelo Site (já existe uso em `/premium`).
- Tratar campos legados (`is_featured`, `highlight_legacy`) como compatibilidade: decidir uma estratégia de backfill/migração para evitar divergência (ex.: migrar valores antigos para `highlight` e padronizar leitura/escrita).
- Remover hardcodes que causam percepção de “planos diferentes do banco” (ex.: CTA final com preço fixo), usando um plano selecionado do banco (por regra clara, como o destacado ou o mais barato).

5) **Permissões de plano: reduzir ambiguidade**
- Escolher um único mecanismo para permissões de plano e alinhar o código que faz gating (ex.: `User::canAccessFeature`) com esse mecanismo.
  - Opção A (curto prazo, menor impacto): manter JSON `plans.permissions` e descontinuar o uso do pivot `permission_plan` nos fluxos atuais.
  - Opção B (médio prazo, mais robusta): migrar para pivot `permission_plan` e remover `plans.permissions`.
- A decisão final depende do que já está em produção (dados reais); o design assume que manteremos compatibilidade durante a transição (ler de ambos por um período, escrever em um só).

## Risks / Trade-offs

- **Mudança de semântica de acesso a eventos** (público vs. restrito por login/plano): hoje há middleware `check.feature:events` em algumas rotas e checagem por setting em controller. Precisamos decidir o comportamento esperado e padronizar (risco de liberar/fechar acesso inadvertidamente) → Mitigação: consolidar regra em um único lugar (rota OU controller) e cobrir com testes de rotas.
- **Quebra de links existentes** por remoção de rotas duplicadas → Mitigação: manter rotas “alias” temporárias com redirect 301 (ou preservar URIs e apenas corrigir o handler/rota-name).
- **Divergência de dados de plano** (legado `is_featured`/`highlight_legacy`) → Mitigação: migration de backfill + padronização de leitura/escrita + auditoria rápida de seeders.
- **FullCalendar depender de endpoint novo** → Mitigação: manter o comportamento antigo por um tempo (se necessário) e trocar o `events:` do JS para o feed dedicado.

## Migration Plan

1. Criar a rota de feed dedicada do FullCalendar no Admin e ajustar `resources/views/admin/events/calendar.blade.php` para consumir esse endpoint.
2. Corrigir a view pública de eventos para usar layout do Site e remover qualquer referência a rotas/layout do Admin.
3. Consolidar rotas públicas de eventos no `routes/web.php`, removendo duplicações e garantindo nomes únicos.
4. Normalizar o “destaque” de planos: padronizar a coluna canônica, ajustar Admin/Site para ler/escrever a mesma fonte e remover hardcode de preço no CTA do `/premium`.
5. Auditoria e alinhamento do mecanismo de permissões de plano (documentar e, se necessário, preparar migração compatível).
6. Verificação manual: Admin calendário mostra eventos existentes; Site eventos mantém contexto do Site; `/premium` reflete planos e destaque atualizados.

## Open Questions

- Eventos do Site devem ser **públicos** (apenas “published”) ou devem exigir login/plano? Se for restrito, qual UX (login, upgrade, 403)?
- Deve existir **apenas 1** plano em destaque ou múltiplos? Se for 1, qual regra (o último marcado “destacar” desmarca os outros)?
- No Admin, ao clicar em um evento no calendário: abrir modal (atual), abrir tela de edição (`admin.events.edit`) ou abrir detalhe interno?
- Qual mecanismo de permissão de plano está ativo em produção (JSON vs. pivot)? Precisamos de uma decisão para eliminar a ambiguidade sem perder dados.

