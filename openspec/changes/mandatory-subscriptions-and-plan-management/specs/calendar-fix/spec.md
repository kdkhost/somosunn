# Spec: FullCalendar Fix e Visibilidade de Eventos

## Requisitos

### 1. Correção do FullCalendar v4 (Admin & Dashboard)
- **Feeds de Dados**: Garantir que o `EventController` retorne os eventos no formato correto esperado pelo FullCalendar 4 (campos `id`, `title`, `start`, `end`, `backgroundColor`, etc.).
- **Dashboard Widget**: Atualizar o `DashboardController` para garantir que a variável `$calendarEvents` contenha os dados reais do banco de dados e seja passada corretamente para o script do widget.
- **Interatividade**: Validar que o clique em eventos no calendário redireciona corretamente para a página de detalhes ou abre o modal de edição (no admin).

### 2. Exibição de Eventos Reais no Front-end (Public)
- **HomeController**: Alterar a lógica de busca de eventos para incluir eventos pagos e gratuitos que estejam marcados como `published`. Remover a dependência exclusiva de "eventos gratuitos" na seção principal se o usuário tiver eventos pagos relevantes.
- **EventController (Public)**: Garantir que a listagem pública (`events.index`) priorize dados do banco de dados e use os dados de demonstração apenas se a tabela estiver completamente vazia.
- **Fidelidade Visual ("Sem mudar uma vírgula")**:
    - Manter os cards de eventos da Home exatamente como estão.
    - Manter a tabela/layout da página de listagem de eventos.
    - Garantir que cores e ícones definidos no banco de dados sejam respeitados.

### 3. Sincronização de Fusos Horários
- Garantir que as datas salvas no banco de dados sejam exibidas corretamente no calendário (evitando desvios de fuso horário causados pelo formato ISO8601 vs local time no JS).

## Critérios de Aceite
- [ ] O calendário no Dashboard exibe bolinhas/barras para eventos gravados anteriormente no banco.
- [ ] Ao clicar em um evento no calendário do Dashboard, o usuário é levado para a página do evento.
- [ ] A seção "Palestras Gratuitas" na Home exibe eventos reais do banco.
- [ ] Se houver eventos pagos no banco, eles aparecem na listagem pública de `/eventos`.
