# Relatório de Correções - Eventos, Cupons e Segurança

Data: 17/06/2026

Checkpoint de retorno: `checkpoint-2026-06-17-1915` / commit `0882cacb16ffa0f6383cdd3311fbce97e039fbfe`.

## Escopo aplicado

- Cupons de evento validados por `EventCouponRequest`, usado pelo painel antigo `/admin` e pelo painel novo.
- Link do grupo do WhatsApp validado por `WhatsAppGroupLinkRule`, aceitando somente hosts oficiais do WhatsApp.
- Acesso ao grupo centralizado em `EventGroupAccessService`, com checagem de inscrição confirmada/paga ou gratuita e registro de `joined_group_at`.
- Botão "Entrar no grupo do evento" extraído para `resources/views/events/partials/group-access.blade.php`.
- Cupons já usados em inscrição deixam de poder ser removidos, preservando referência financeira.
- Rota `events.group.join` com `auth` e `throttle:10,1`.
- Rotas sensíveis movidas para `routes/modules/maintenance.php` e protegidas pelo middleware de manutenção.
- Middleware de rotas sensíveis fechado por padrão com 404, sem resposta técnica.
- Configurações adicionadas: `config/maintenance.php`, `config/marketplace.php` e flags em `config/payments.php`.
- Condicional morto `if (false && ...)` substituído por `config('marketplace.require_seller_enabled', false)`.
- Descrição do `composer.json` corrigida para Laravel 10.

## Variáveis novas

- `ALLOW_MAINTENANCE_ROUTES=false`
- `ALLOW_INSTALLER_ROUTES=false`
- `MARKETPLACE_REQUIRE_SELLER_ENABLED=false`
- `PAYMENT_MERCADOPAGO_ENABLED=true`
- `PAYMENT_SUMUP_ENABLED=true`

## Observações

- As rotas de migração e demo agora aceitam `POST` no módulo de manutenção.
- O SumUp continua respeitando o toggle do painel administrativo; a nova flag de env funciona como chave global para desativação controlada.
- Nenhuma dependência de QR Code foi adicionada.
