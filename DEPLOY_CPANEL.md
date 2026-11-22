# Deploy para cPanel / LiteSpeed (SOMOS UNN)

## 1) Requisitos do servidor
- **PHP 8.1+ (recomendado PHP 8.3 ou 8.4)** com extensões `pdo_mysql`, `gd`, `mbstring`, `openssl`, `curl`, `fileinfo`, `zip` (e `imagick` opcional para geração de capas PDF).
- **MariaDB 10.6+ / MySQL 8+** com charset `utf8mb4` e collation `utf8mb4_unicode_ci`.
- **LiteSpeed** com OPcache ativo no MultiPHP INI Editor.

## 2) Estrutura de Subdomínios e DocumentRoot (cPanel)
Para isolamento de carga e cache estático em produção:
1. **`somosunn.com.br`** (Landing / Institucional / Conteúdo Público Cacheável) -> DocumentRoot `/home/somosunn/public_html`
2. **`app.somosunn.com.br`** (Painéis `/admin`, `/painel` e Área do Membro) -> DocumentRoot `/home/somosunn/public_html`
3. **`api.somosunn.com.br`** (APIs `/api/v1/*` e Webhooks de Pagamento) -> DocumentRoot `/home/somosunn/public_html`
4. **`cdn.somosunn.com.br`** (Mídias estáticas e uploads sem cookie) -> DocumentRoot `/home/somosunn/public_html/public`

> **Retrocompatibilidade:** enquanto os subdomínios estiverem em transição no DNS/cPanel, o domínio principal mantém fallback seguro das rotas se a opção de isolamento estrito não estiver habilitada no painel Admin.

## 3) Variáveis de ambiente essenciais (`.env`)
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://somosunn.com.br`
- `APP_TIMEZONE=America/Sao_Paulo`
- `SESSION_DOMAIN=.somosunn.com.br` *(compartilha sessão com `app.`)*
- `SESSION_SECURE_COOKIE=true`
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax`
- `SESSION_ENCRYPT=true`
- `QUEUE_CONNECTION=database`
- `QUEUE_FAILED_DRIVER=database-uuids`

> **Banco de dados primeiro:** Credenciais de gateways (Mercado Pago, SumUp, Efí Pro), SMTP, SEO, PWA, logos/favicons, domínios/subdomínios e Modo Manutenção (com IP/dispositivo autorizado) são gerenciados no banco MariaDB pelo painel `/admin/settings`.

## 4) Sincronismo GitHub <-> cPanel (SSH)
Execute no servidor após `git push` na `main`:

```bash
rm -f /home/somosunn/public_html/.git/index.lock && \
cd /home/somosunn/public_html && \
git fetch origin && \
git reset --hard origin/main && \
chmod -R ug+rwX storage bootstrap/cache && \
php artisan migrate --force && \
php artisan optimize:clear && \
php artisan view:clear && \
php artisan route:clear && \
php artisan config:clear && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache
```

## 5) Central de Cron Única (apenas 1 linha no cPanel)
Todas as rotinas agendadas (fila de e-mails, fila de jobs/webhooks, conciliação SumUp/MP, lembretes, backups, WAF e cache do dashboard) são controladas na tabela `scheduled_tasks` (`/admin/cron` e `/painel/admin/cron`).

Configure **apenas 1 tarefa Cron** no cPanel:
```cron
* * * * * /usr/local/bin/php /home/somosunn/public_html/artisan schedule:run >> /dev/null 2>&1
```

## 6) LiteSpeed / Cloudflare (Performance e Cache)
- **`somosunn.com.br` (público):** páginas institucionais e home sem cookie de sessão em GET anônimo; elegível para cache de borda no Cloudflare / LSCache.
- **`app.somosunn.com.br`:** **Bypass Cache** obrigatório no Cloudflare (e `Cache-Control: no-store, private` automático no Laravel).
- **`api.somosunn.com.br`:** **Bypass Cache** obrigatório para webhooks e endpoints autenticados, com rate limiting ativo.
- **`cdn.somosunn.com.br`:** Cache agressivo de longo prazo para `.webp`, `.svg`, `.png`, `.jpg`, `.css`, `.js`, `.woff2` (sem cookies de sessão).
