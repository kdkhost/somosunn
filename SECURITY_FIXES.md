# SECURITY_FIXES.md — Correções de Segurança Aplicadas

> Gerado automaticamente pela spec `waf-e-auditoria-seguranca`
> Última atualização: 2026-05-12

---

## Resumo Executivo

| Métrica | Valor |
|---------|-------|
| Findings totais detectados | 373 |
| Críticos (prazo imediato) | 4 |
| Altos (prazo 7 dias) | 216 |
| Médios (prazo 30 dias) | 150 |
| Baixos (prazo 90 dias) | 3 |
| Mitigáveis pelo WAF | 180 (48%) |

---

## Middlewares Criados

| Middleware | Função | Registrado em |
|---|---|---|
| `WafMiddleware` | WAF próprio (inspeção, bloqueio, rate limit, challenge) | Kernel global (antes de TrackServiceVisit) |
| `BlockSensitiveRoutesInProduction` | Bloqueia /install, /run-migrations, /test-connection em produção | Kernel global |
| `SecurityHeadersMiddleware` | CSP, HSTS, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, COOP, CORP | Grupo `web` |

---

## Serviços Criados

| Serviço | Função |
|---|---|
| `App\Services\Waf\WafEngine` | Motor principal do WAF (blocklist → allowlist → rate limit → regras → score → decisão) |
| `App\Services\Waf\RiskScoreCalculator` | Soma clampada [0,100] + classificação por limiares |
| `App\Services\Waf\IpListService` | Gestão de IP blocklist/allowlist com CIDR v4/v6 |
| `App\Services\Waf\RateLimitStore` | Token bucket sobre cache Laravel |
| `App\Services\Waf\SensitiveDataMasker` | Mascara senhas, tokens, PAN, CPF, CNPJ em logs |
| `App\Services\Waf\WafEventLogger` | Persiste eventos + canal de log `waf` |
| `App\Services\Waf\WafRuleRepository` | Cache versionado de regras + versionamento append-only |
| `App\Services\Waf\ChallengeManager` | Desafio JS cookie para requisições suspeitas |
| `App\Services\Waf\Matchers\*` | 4 estratégias de matching (regex, list, numeric, function) |
| `App\Services\SecureUploadService` | Validação MIME real, extensão, nome seguro, bloqueio de executáveis |

---

## Riscos Corrigidos

### Críticos
1. **Rotas sensíveis expostas em produção** → `BlockSensitiveRoutesInProduction` bloqueia /install, /run-migrations, /test-connection
2. **Webhooks sem HMAC** → Detectados pela auditoria; WAF mitiga com regra `Webhook_Invalid_Signature` até correção direta
3. **Uploads sem validação** → `SecureUploadService` + `.htaccess` em `public/uploads/` bloqueia execução de PHP
4. **Cabeçalhos de segurança ausentes** → `SecurityHeadersMiddleware` adiciona CSP, HSTS, X-Frame-Options, nosniff, COOP, CORP

### Altos
5. **SQL Injection via whereRaw/selectRaw** → WAF detecta padrões SQLi; auditoria lista cada ocorrência para correção manual
6. **XSS via {!! !!} em Blade** → WAF detecta payloads XSS; auditoria lista 47 ocorrências
7. **Login sem rate limit** → WAF aplica rate limit por IP+email na rota de login
8. **Impersonação sem log** → Detectado; correção na Fase 5 (2FA + log append-only)
9. **Cookies de sessão inseguros** → Variáveis `SESSION_SECURE_COOKIE`, `SESSION_HTTP_ONLY`, `SESSION_SAME_SITE` adicionadas ao `.env.example`

### Médios
10. **Rotas API sem throttle** → WAF aplica rate limit por escopo `api`
11. **Uploads sem limite de tamanho** → `SecureUploadService::validate()` aceita `$maxKb`
12. **X-Powered-By exposto** → `SecurityHeadersMiddleware` remove o header

---

## Riscos Restantes (pendentes de correção manual)

| ID | Severidade | Descrição | Prazo |
|---|---|---|---|
| SEC-WEBHOOK-HMAC-* | Crítico | 4 webhooks sem validação HMAC | Imediato |
| SEC-XSS-* | Alto | 47 usos de `{!! !!}` em Blade | 7 dias |
| SEC-SQL-* | Alto/Médio | ~150 usos de whereRaw/selectRaw | 30 dias |
| SEC-AUTH-2FA | Alto | LoginController sem 2FA para superadmin | 7 dias |
| SEC-IMP-LOG | Alto | Impersonação sem log de auditoria | 7 dias |

---

## Arquivos Criados/Alterados

### Criados
- `app/Http/Middleware/WafMiddleware.php`
- `app/Http/Middleware/BlockSensitiveRoutesInProduction.php`
- `app/Http/Middleware/SecurityHeadersMiddleware.php`
- `app/Services/SecureUploadService.php`
- `app/Services/Waf/` (20+ arquivos: Engine, Matchers, Scanners, Models)
- `config/waf.php`
- `database/migrations/2026_07_20_00000*` (8 migrations)
- `app/Models/Waf/` (8 models)
- `app/Console/Commands/SecurityAudit.php`
- `public/uploads/.htaccess`
- `tests/Property/.gitkeep`, `tests/Property/Waf/.gitkeep`

### Alterados
- `app/Http/Kernel.php` (3 middlewares adicionados)
- `config/logging.php` (canais `waf` e `security`)
- `.env.example` (variáveis WAF + sessão + MAINTENANCE_SECRET)
- `composer.json` (`giorgiosironi/eris` em require-dev)
- `phpunit.xml` (suite Property)
- `CHANGELOG.md`

---

## Melhorias Futuras

1. Migrar CSP de `unsafe-inline` para nonce-based (requer refatorar scripts inline)
2. Implementar 2FA TOTP obrigatório para superadmin
3. Implementar log append-only de impersonação
4. Corrigir os 47 usos de `{!! !!}` com HTMLPurifier
5. Corrigir os 150 usos de whereRaw com bindings
6. Implementar painel WAF no superadmin (Fase 6)
7. Ativar WAF em detection-only e depois enforce (Fase 7)
8. Integrar MaxMind GeoLite2 para bloqueio por país/ASN
