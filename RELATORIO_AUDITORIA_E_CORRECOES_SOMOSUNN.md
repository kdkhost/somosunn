# Relatório de Auditoria e Correções - SOMOS UNN

## Painéis Administrativos

Data de atualização: 18/06/2026

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
