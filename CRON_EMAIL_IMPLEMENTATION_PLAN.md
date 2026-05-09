# Plano de Implementação: Cron Autônomo + Emails Personalizados

## Status: EM ANDAMENTO

---

## 1. CRON AUTÔNOMO (sem depender do servidor)

### Já implementado:
- ✅ Middleware `RunInternalCron` executa `schedule:run` a cada request
- ✅ Timezone: `America/Sao_Paulo`
- ✅ Lock para evitar execução simultânea
- ✅ Heartbeat para monitoramento

### Comandos já agendados:
| Comando | Frequência | Módulo |
|---------|-----------|--------|
| `queue:work` | A cada minuto | Fila de emails |
| `lessons:process-pending-videos` | A cada minuto | Cursos/Vídeos |
| `points:award-top-ranking` | Semanal (dom 00:05) | Ranking |
| `points:award-birthday-bonus` | Diário (01:00) | Pontos |
| `share-requests:expire` | Diário (02:00) | Compartilhamento |
| `dashboard:warm-cache` | A cada 5 min | Dashboard |
| `orders:cancel-unpaid` | A cada 5 min | Pedidos |
| `cart:cleanup-expired` | A cada hora | Carrinho |

### Comandos a criar:
| Comando | Frequência | Módulo |
|---------|-----------|--------|
| `subscriptions:check-expired` | Diário (06:00) | Assinaturas |
| `subscriptions:send-renewal-reminders` | Diário (08:00) | Assinaturas |
| `invoices:send-pending` | Diário (09:00) | Faturas |
| `events:send-reminders` | Diário (07:00) | Eventos |
| `mentorships:send-reminders` | Diário (07:30) | Mentorias |
| `notifications:cleanup` | Diário (03:00) | Notificações |
| `marketplace:send-abandoned-cart` | A cada 6h | Marketplace |

---

## 2. EMAILS PERSONALIZADOS (todos via MailTemplate)

### Já usando MailTemplate:
- ✅ `password_reset` (ResetPasswordNotification)
- ✅ `email_verification` (VerifyEmailNotification)
- ✅ `welcome` / `welcome_email`
- ✅ `marketplace_order_paid_buyer`
- ✅ `marketplace_order_paid_seller`
- ✅ `payment_paid`
- ✅ `order_created_pix`
- ✅ `abandoned-cart` (OrderAbandonedCart)
- ✅ `job_apply_owner` / `job_apply_candidate`

### Precisam migrar para MailTemplate:
| Mail/Notification | Template slug a criar |
|-------------------|----------------------|
| `WelcomeMail` | `welcome_subscription` |
| `CertificateIssued` | `certificate_issued` |
| `InvoiceMail` | `invoice_sent` |
| `JobVacancyPublished` | `job_vacancy_published` |
| `RedemptionRequestedForProvider` | `redemption_requested` |
| `RedemptionStatusUpdated` | `redemption_status_updated` |

### Templates novos a criar:
| Slug | Uso |
|------|-----|
| `subscription_expiring` | Lembrete de renovação (3 dias antes) |
| `subscription_expired` | Aviso de expiração |
| `event_reminder` | Lembrete de evento (24h antes) |
| `mentorship_reminder` | Lembrete de mentoria agendada |
| `invoice_overdue` | Fatura em atraso |

---

## 3. CONFIGURAÇÃO INDIVIDUAL POR MÓDULO

Cada cron terá uma setting no banco para ativar/desativar:
- `cron_subscriptions_enabled` (default: 1)
- `cron_invoices_enabled` (default: 1)
- `cron_events_reminders_enabled` (default: 1)
- `cron_mentorships_reminders_enabled` (default: 1)
- `cron_marketplace_abandoned_cart_enabled` (default: 1)
- `cron_notifications_cleanup_enabled` (default: 1)

---

## Prioridade de Implementação:
1. ✅ Cron autônomo (já funciona via middleware)
2. 🔄 Migrar emails existentes para MailTemplate
3. 🔄 Criar comandos de cobrança/lembrete
4. 🔄 Criar settings de configuração individual
5. 🔄 Adicionar UI no painel admin para gerenciar crons
