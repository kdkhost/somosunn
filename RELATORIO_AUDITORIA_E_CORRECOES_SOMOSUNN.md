# Relatório de Auditoria e Correções - SOMOS UNN

## Painéis Administrativos

Data de atualização: 18/06/2026

### Atualizacao aplicada em 19/06/2026 - cupom de expositor e ingresso marcado

- Cupons de evento receberam o campo `applies_to`, com opcoes `attendee`, `exhibitor` e `both`, para separar cupom de ingresso normal, cupom de expositor e cupom compartilhado.
- A regra de negocio foi centralizada em `app/Services/EventCouponService.php`, consumida pelo checkout normal de evento e pelo checkout de expositor.
- O checkout de expositor (`/eventos/{event}/expositor/checkout`) valida cupom com escopo de expositor e bloqueia reuso do mesmo cupom pelo mesmo usuario.
- Cupons exclusivos de expositor nao liberam ingresso normal; cupons exclusivos de ingresso normal nao liberam area de expositor. Apenas `both` atende aos dois fluxos.
- `EventExhibitorService` emite automaticamente um `EventRegistration` pago para o membro quando a compra/reserva de expositor com cupom de expositor e confirmada.
- O ingresso do membro em `/painel/ingressos` e `/painel/ingressos/{registration}` exibe carimbo visual `Expositor` quando o pedido vem do contexto `event_exhibitor`.
- As telas de cupons foram atualizadas nos dois paineis: `/admin/events/{event}/coupons` e `/painel/admin/events/{event}/coupons`.

### Atualização aplicada em 19/06/2026 - hardening da auditoria atual

- A rota publica `events.reserve` (`POST /eventos/{event}/reservar`) recebeu o rate limiter nomeado `event_reservations`, com chaves por evento, IP e e-mail/CPF/documento quando informados.
- A ordem das rotas publicas de eventos foi ajustada para declarar `/eventos/create` antes de `/eventos/{event}`.
- `WhatsAppGroupLinkRule` passou a aceitar somente `https://chat.whatsapp.com` e `https://www.chat.whatsapp.com`.
- `InstallController@testConnection` passou a retornar mensagem generica em producao, mantendo o detalhe tecnico apenas no log interno e em ambientes nao produtivos.
- Comandos destrutivos de banco em producao (`migrate:rollback`, `migrate:reset`, `migrate:fresh`, `db:wipe`) foram bloqueados sem a confirmacao explicita `ALLOW_PRODUCTION_DESTRUCTIVE_DB_COMMANDS=SOMOS_UNN_CONFIRMAR_DESTRUICAO`.
- Adicionados testes de precedencia de rotas de eventos, middleware de rate limit da reserva publica e rejeicao de link HTTP do WhatsApp.
- `composer audit` foi executado e ainda aponta vulnerabilidades em dependencias; a atualizacao de pacotes deve ser tratada em entrega separada com suite completa.
- Pendencias planejadas: CAPTCHA invisivel no checkout publico de eventos, refatoracao gradual do `EventReservationController` em services, atualizacao de dependencias vulneraveis e modularizacao progressiva de `routes/web.php`.

### Atualização aplicada em 19/06/2026 - paginação do painel de cron

- A tela principal `/admin/cron` passou a usar DataTables com carregamento AJAX server-side, paginação, busca e ordenação.
- O novo endpoint `admin.cron.data` retorna somente a página solicitada da tabela `scheduled_tasks`, mantendo as ações de executar, editar, logs e excluir por linha.
- Os contadores do topo agora usam consultas agregadas no controller, sem carregar toda a coleção de tarefas para renderizar a página.
- O painel moderno `/painel/admin/cron` foi preservado sem alteração visual nesta entrega.
- Rotas preservadas/adicionadas: `admin.cron.index`, `admin.cron.data`, `admin.cron.create`, `admin.cron.edit`, `admin.cron.logs`, `admin.cron.run`, `admin.cron.run-all`, `admin.cron.destroy` e `panel.admin.cron.*`.

### Hardening aplicado em 18/06/2026

- Mantido o bloqueio global `BlockSensitiveRoutesInProduction`; removido uso redundante do alias `sensitive.production` nas rotas de manutencao.
- `routes/modules/maintenance.php` deixou de expor cache por GET e deixou de executar migracoes por HTTP.
- `InstallController` passou a bloquear instalacao quando ja ha APP_KEY, tabelas principais ou `storage/app/installed.lock`; em producao tambem exige `INSTALLER_TOKEN`.
- `config/payments.php` passou a iniciar SumUp desativado e adicionou controles de assinatura para webhook Mercado Pago.
- Webhook Mercado Pago recebeu `throttle:webhook_mercadopago`, validacao de assinatura com `x-signature`, `x-request-id` e `data.id`, e log sanitizado.
- `WhatsAppGroupLinkRule` passou a aceitar somente links de grupo `chat.whatsapp.com`.
- `EventGroupAccessService` passou a exigir `payment_status` explicito `paid` ou `free`.
- Criada migracao conservadora para sanear `event_registrations.payment_status` nulo sem rollback destrutivo.
- Criado `EventCouponPermissionSeeder` idempotente para organizar permissoes do modulo.

Pendencias estruturais ainda abertas:

- Modularizar completamente `routes/web.php` em arquivos por dominio.
- Refatorar `EventReservationController` para services menores de checkout, inscricao, gateway e pagamento gratuito/pago.
- Ampliar testes feature reais dos dois paineis apos estabilizar banco de teste MariaDB do projeto.

### Atualizacao aplicada em 19/06/2026 - crons, backups e pedidos pendentes

- O cron automatico principal fica em `app/Console/Kernel.php` e deve ser acionado pelo cron real do servidor com `php artisan schedule:run` a cada minuto; o middleware `RunInternalCron` fica apenas como fallback desativado por padrao.
- O painel principal de tarefas fica em `/admin/cron` e o painel moderno equivalente em `/painel/admin/cron`; ambos preservam a tabela compartilhada `scheduled_tasks`.
- O gerenciamento operacional de backups fica em `/admin/backups`, usando `BackupController` e `BackupService`; os horarios automaticos passam a ser configurados por `backup_database_time`, `backup_config_time` e `backup_config_weekday`.
- Adicionado o comando compartilhado `orders:send-unpaid-reminders`, agendado antes de `orders:cancel-unpaid`, para enviar lembretes de pedidos pendentes.
- O cancelamento automatico de pedidos pendentes passou a usar `orders_unpaid_cancel_after_hours`, com padrao de 24 horas, em fuso `America/Sao_Paulo`.
- Os e-mails `order_unpaid_payment_reminder` e `order_unpaid_auto_cancelled` usam `mail_templates`, sao editaveis no administrativo e enviam copia oculta para admin/superadmin.
- A tela `/painel/compras` passou a calcular o prazo de expiracao com a mesma regra do cron, evitando divergencia visual.
- Rotas preservadas: `admin.cron.*`, `panel.admin.cron.*`, `admin.backups.*`, `admin.orders.*`, `panel.admin.orders.*` e `panel.purchases.*`.

### Atualizacao aplicada em 19/06/2026 - centralizacao total de crons e e-mails por template

- O `app/Console/Kernel.php` passou a carregar somente tarefas ativas de `scheduled_tasks`, mantendo `/admin/cron` como fonte operacional dos crons.
- O catalogo de comandos/frequencias padrao foi centralizado em `config/cron-panel.php`, usado por `/admin/cron/create`, `/painel/admin/cron/create`, seeders e migrations.
- Adicionados os comandos `cron:heartbeat` e `emails:process-queue`, incluindo o monitoramento do scheduler e a fila de e-mails no painel de cron.
- Todos os crons operacionais padrao foram inseridos/alinhados na tabela `scheduled_tasks`: filas, backups, pedidos, WAF, notificacoes, faturas, assinaturas, reputacao, carrinhos, eventos, videos e dashboards.
- `/admin/backups` continua sendo a tela operacional de backup, mas ao salvar configuracoes ela sincroniza os registros `backup:database` e `backup:config` no painel `/admin/cron`.
- A tela `/admin/mailtest` foi convertida para disparo por `mail_templates`, sem assunto/mensagem livres enviados diretamente por PHPMailer.
- Envios existentes por Mailables continuam passando por `UsesMailTemplate`; envios de servicos/controllers usam `SystemMailTemplateService` ou notifications baseadas em templates.

### Atualizacao aplicada em 19/06/2026 - tema e codificacao

- Corrigida a alternancia dark/light do `/admin`: o navbar passou a usar o tema real resolvido por `resources/views/admin/layouts/app.blade.php`, sem depender de `$settings` especifico de cada tela.
- Reforcada a alternancia dark/light do `/painel`: o botao do painel moderno agora valida a resposta de `theme.toggle`, bloqueia clique concorrente e reverte a interface se o salvamento falhar.
- Mantida a separacao de regra por painel: `/admin` usa a configuracao global `site_theme`; `/painel` usa a preferencia do usuario em `theme_pref`.
- Corrigidos textos com acentuacao quebrada no menu administrativo e na tela de indicacoes.
- Adicionada a validacao `php tools/check-text-encoding.php` para barrar UTF-8 BOM e sequencias comuns de mojibake antes de novas entregas.

### Atualizacao aplicada em 19/06/2026 - detalhe de vendas

- Reorganizada a tela de detalhe da venda no painel principal em `/admin/orders/{order}`, mantendo o controller `app/Http/Controllers/Admin/OrderController.php` e a view `resources/views/admin/orders/show.blade.php`.
- Reorganizada a tela equivalente do painel moderno em `/painel/admin/orders/{order}`, mantendo o controller `app/Http/Controllers/Panel/Admin/OrderController.php` e a view `resources/views/panel/admin/orders/show.blade.php`.
- As duas telas preservam a mesma regra de negocio do model `Order`, incluindo valor bruto, total liquido, desconto, saldo reembolsavel, cupom, gateway, metodo, transacao, fatura, itens e acoes de cancelamento/estorno.
- O ID da transacao passou a ser renderizado como texto alinhado aos demais campos do resumo, sem bloco de codigo escuro ou aparencia de input.
- Rotas preservadas: `admin.orders.show`, `admin.orders.cancel`, `admin.orders.refund`, `admin.orders.invoice`, `panel.admin.orders.show`, `panel.admin.orders.cancel`, `panel.admin.orders.refund` e `panel.admin.orders.invoice`.
- Diferencas visuais mantidas: `/admin` continua usando AdminLTE e `/painel/admin` continua usando o layout Tailwind moderno, sem reutilizar view de um painel no outro.
- Validacoes executadas: `php artisan route:list --path=orders`, `php -l` nas duas views, `php tools/check-no-bom.php` e compilacao remota com `php artisan view:cache`.

### Atualizacao aplicada em 19/06/2026 - compradores por item no relatorio

- Adicionada a lista alfabetica de compradores por item vendido em `/admin/orders/sales-report`, acionada por botao na linha do item.
- Adicionada a mesma lista em `/painel/admin/orders/sales-report`, preservando o layout Tailwind do painel moderno.
- A regra compartilhada fica em `app/Services/SalesAnalyticsService.php`, que consolida por pedido os campos nome do membro, valor do item, data da compra, quantidade adquirida e tipo da compra.
- O endpoint compartilhado `app/Http/Controllers/Admin/SalesReportBuyerController.php` serve HTML para modal, pagina de impressao A4 e PDF via Dompdf.
- Rotas adicionadas/preservadas no painel principal: `admin.orders.sales-report.buyers`, `admin.orders.sales-report.buyers.print` e `admin.orders.sales-report.buyers.pdf`.
- Rotas adicionadas/preservadas no painel moderno: `panel.admin.orders.sales-report.buyers`, `panel.admin.orders.sales-report.buyers.print` e `panel.admin.orders.sales-report.buyers.pdf`.
- Diferencas visuais mantidas: `/admin` usa modal Bootstrap/AdminLTE; `/painel/admin` usa modal Tailwind/vanilla JS; ambos consomem a mesma consulta de negocio.
- Validacoes previstas: `php artisan route:list --path=orders`, `php artisan view:cache`, `php tools/check-no-bom.php`, `php tools/check-text-encoding.php` e `git diff --check`.

### Atualizacao aplicada em 19/06/2026 - marca padrao em PDFs

- Criado `app/Support/PdfBranding.php` para aplicar a logo do sistema como marca d'agua central e clara em PDFs gerados via Dompdf.
- A resolucao da logo prioriza `logo_admin`, `logo_front`, `logo_image` e `logo_auth`, com fallback para `public/img/logo.svg`.
- PDFs atualizados para usar o padrao: faturas, certificados, relatorio financeiro de pedidos e lista de compradores por item.
- A regra injeta uma camada HTML fixa antes do conteudo, com 15% de opacidade, para manter a marca d'agua atras do texto.
- O PDF do relatorio financeiro de pedidos foi ajustado para A4 paisagem, tabela compacta, colunas proporcionais e quebra de texto, evitando corte lateral de conteudo.
- Validacoes previstas: `php -l` nos arquivos alterados, geracao isolada de PDF, `php artisan view:cache`, `php tools/check-no-bom.php`, `php tools/check-text-encoding.php` e `git diff --check`.

### Regra permanente

O sistema utiliza dois painéis administrativos distintos e integrados:

- `/admin`: painel administrativo principal, operacional e de gestão global.
- `/painel`: painel moderno do usuário, membro, administrador e áreas específicas de conta.

Toda funcionalidade administrativa deve ser analisada nos dois fluxos antes de ser considerada concluída. A regra de negócio deve ser compartilhada sempre que possível; as views podem ser diferentes para respeitar o layout de cada painel.

### Recursos existentes em `/admin`

- Gestão de eventos em `/admin/events`.
- CRUD administrativo de eventos com names `admin.events.*`.
- Cupons de eventos em `/admin/events/{event}/coupons`.
- Gestão de expositores vinculados a eventos em `/admin/events/{event}/exhibitors`.
- Scanner de eventos em `/admin/events/{event}/scanner`.
- Upload e remoção de mídias de eventos em `/admin/events/{event}/media`.
- Link do grupo do WhatsApp do evento no formulário administrativo, controlado por permissão.

### Recursos existentes em `/painel`

- Área de membro em `/painel`.
- Área administrativa moderna em `/painel/admin`.
- Gestão de eventos em `/painel/admin/events`.
- CRUD administrativo moderno de eventos com names `panel.admin.events.*`.
- Cupons de eventos em `/painel/admin/events/{event}/coupons`.
- Gestão de expositores vinculados a eventos em `/painel/admin/events/{event}/exhibitors`.
- Scanner de eventos em `/painel/admin/events/{event}/scanner`.
- Upload e remoção de mídias de eventos em `/painel/admin/events/{event}/media`.
- Link do grupo do WhatsApp do evento no formulário moderno, controlado por permissão.
- Ingressos do membro em `/painel/ingressos`.

### Recursos compartilhados

- Models compartilhados: `Event`, `EventCoupon`, `EventRegistration`, `Order` e `OrderItem`.
- Permissões granulares compartilhadas:
  - `admin.events.coupons.view`
  - `admin.events.coupons.create`
  - `admin.events.coupons.edit`
  - `admin.events.coupons.delete`
  - `admin.events.coupons.toggle`
  - `admin.events.group_link.manage`
- Validação do link de grupo: `WhatsAppGroupLinkRule`.
- Acesso ao grupo do evento: `EventGroupAccessService`.
- Revogação de acessos/ingressos ao cancelar compra: `OrderAccessRevocationService`.

### Rotas preservadas

- `/admin/events`
- `/admin/events/{event}`
- `/admin/events/{event}/edit`
- `/admin/events/{event}/coupons`
- `/admin/events/{event}/coupons/create`
- `/admin/events/{event}/coupons/{coupon}/edit`
- `/painel/admin/events`
- `/painel/admin/events/{event}`
- `/painel/admin/events/{event}/edit`
- `/painel/admin/events/{event}/coupons`
- `/painel/admin/events/{event}/coupons/create`
- `/painel/admin/events/{event}/coupons/{coupon}/edit`
- `/eventos/{event}/checkout`
- `/eventos/{event}/reservar`
- `/eventos/{event}/entrar-no-grupo`

Observação: a regra conceitual pode citar `/admin/eventos`, mas neste checkout as rotas legadas ativas usam `/admin/events` e `/painel/admin/events`. Essas rotas não devem ser renomeadas sem compatibilidade ou redirecionamento seguro.

### Rotas adicionadas nesta auditoria

Nenhuma rota foi adicionada nesta atualização. A alteração foi documental, para consolidar a regra permanente dos dois painéis.

### Controllers reutilizados

- `app/Http/Controllers/Panel/Admin/EventCouponController.php` estende `app/Http/Controllers/Admin/EventCouponController.php`.
- O controller do painel moderno define prefixos próprios de view e rota:
  - `panel.admin.events.coupons`
  - `panel.admin.events`

### Services compartilhados

- `EventGroupAccessService` centraliza a liberação do link do grupo do evento.
- `OrderAccessRevocationService` centraliza a revogação de acessos após cancelamento.
- A gestão de cupons reutiliza o controller principal no painel moderno; se houver aumento de complexidade, o próximo passo correto é extrair service compartilhado para cupons de eventos.

### Testes e validações dos dois painéis

Validação executada nesta atualização:

- `php artisan route:list`
- Conferência das rotas `admin.events.*`.
- Conferência das rotas `panel.admin.events.*`.
- Conferência das rotas públicas de checkout, reserva e entrada no grupo do evento.
- `php tools/check-no-bom.php`
- `git diff --check`

Matriz obrigatória para futuras alterações administrativas:

1. Admin autenticado acessa `/admin/events`.
2. Admin autenticado acessa `/painel/admin/events`.
3. Usuário sem permissão recebe 403 ou redirecionamento seguro em `/admin`.
4. Usuário sem permissão recebe 403 ou redirecionamento seguro em `/painel`.
5. Cupom criado em `/admin` aparece em `/painel/admin`.
6. Cupom criado em `/painel/admin` aparece em `/admin`.
7. Ativação realizada em um painel reflete no outro.
8. Exclusão realizada em um painel reflete no outro.
9. Não existem registros duplicados.
10. As mesmas validações funcionam em ambos os painéis.
11. As mesmas permissões funcionam em ambos os painéis.
12. A regra de negócio compartilhada é reaproveitada nos dois fluxos.

### Permissões por painel

As permissões administrativas mantêm o namespace `admin.events.*` mesmo quando usadas pelo painel moderno:

- `/admin`: aplica permissões administrativas no layout AdminLTE.
- `/painel/admin`: aplica as mesmas permissões administrativas no layout moderno.
- `/painel`: recursos de membro não devem liberar recursos administrativos sem permissão explícita.

### Diferenças visuais mantidas

- `/admin`: usa views `admin.*`, layout AdminLTE, sidebar administrativa e componentes legados.
- `/painel/admin`: usa views `panel.admin.*`, layout moderno, sidebar/navegação do painel e componentes Tailwind.
- Views de um painel não devem ser reutilizadas diretamente no outro sem validação de layout, seções, scripts, sidebar e compatibilidade visual.
