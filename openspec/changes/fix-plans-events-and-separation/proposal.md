## Why

Atualmente, existe um descompasso entre os planos cadastrados no banco de dados e o que é exibido no frontend, além da funcionalidade de destaque (`highlight`) não ser refletida dinamicamente. Ademais, há uma mistura de contextos entre o Site (público) e o Admin (gestão), onde rotas do site redirecionam para o admin e o calendário administrativo do FullCalendar v4 não exibe os eventos programados. Este projeto visa limpar essa redundância, garantir a sincronização total dos dados e separar claramente as janelas de Frontend e Backend.

## What Changes

- **Sincronização de Planos**: Garantir que o frontend (`/premium`) renderize 100% dos dados do banco de dados, incluindo preços, benefícios e o status de destaque em tempo real.
- **Correção do FullCalendar v4**: Ajustar o feed JSON e a inicialização do calendário no Dashboard Admin para exibir eventos reais do banco.
- **Desacoplamento de Contextos (Site vs. Admin)**: Separar as rotas e views de eventos do Site das rotas de gestão do Admin. Clicar em eventos no site deve manter o usuário no site.
- **Refatoração de Links e Redirecionamentos**: Eliminar redirecionamentos cruzados desnecessários que "misturam" a experiência do portal com a do site institucional.
- **Limpeza de Mock Data**: Remoção definitiva de qualquer dado estático (hardcoded) que ainda persista nos controllers de Home e Eventos.

## Capabilities

### New Capabilities
- `plan-reactive-showcase`: Sincronização automática e reativa entre a gestão administrativa e a vitrine de planos.
- `site-admin-context-separation`: Separação lógica e visual de rotas entre o portal/site público e a área de gestão administrativa.

### Modified Capabilities
- `event-calendar-management`: Ajuste nos requisitos de exibição e alimentação do calendário (FullCalendar v4) para suportar dados reais.

## Impact

Afeta diretamente os controladores `HomeController`, `EventController` (público e admin), `DashboardController` e `PlanController`. Impacto moderado em `web.php` e nas views Blade de frontend (`site.premium`, `events.index`) e administrativas (`admin.dashboard`, `admin.events.calendar`).
