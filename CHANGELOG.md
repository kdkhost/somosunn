# CHANGELOG - SOMOS UNN

---

## [2026-05-11] - Fase 1 WAF - Auditoria de Seguranca (spec waf-e-auditoria-seguranca)

### Adicionado
- Comando Artisan `php artisan security:audit` (assinatura `--paths= --format=md|json|both --out= --only=`) que analisa o codigo-fonte e gera relatorio em `storage/app/security/audit-report-YYYYMMDD-HHMMSS.md` + `.json` + snapshot `audit-report-latest.*`
- Infraestrutura de scanners em `app/Services/Waf/Scanners/`: `Scanner` (interface), `AuditContext`, `AuditFinding`, `AbstractScanner`, `AuditReportBuilder`
- 8 scanners implementados:
  - `PhpAstScanner`: usa `nikic/php-parser` para detectar `DB::raw`, `whereRaw`, `orderByRaw`, `selectRaw`, `havingRaw`, `eval`, `shell_exec`, `exec`, `passthru`, `system`, `popen`, `proc_open`, `assert`, `create_function`, `unserialize` sem allowed_classes
  - `BladeScanner`: detecta `{!! $var !!}` classificando pela heuristica do conteudo (critical se `request()/old()`, high se nome tipico de texto livre, medium nos demais) e `@php` com funcoes perigosas
  - `RouteScanner`: rotas mutantes (POST/PUT/PATCH/DELETE) sem middleware `auth|admin|signed` e rotas API sem `throttle`
  - `UploadScanner`: uso de `UploadStorage::storeUploadedFile()`, presenca de `mimes/mimetypes/extensions` e limite `max:` em FormRequests
  - `WebhookScanner`: webhooks sem HMAC, janela de timestamp e idempotencia por `event_id`
  - `ConfigScanner`: `APP_DEBUG=true` no example, variaveis de sessao seguras ausentes, segredos hardcoded fora de `env()`
  - `HeaderScanner`: ausencia de CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, COOP, CORP, e `X-Powered-By` nao removido
  - `AuthScanner`: `ImpersonateController` sem log, sem 2FA, `LoginController` sem rate limit explicito
- Relatorio gerado na primeira execucao: **373 findings** (4 criticos, 216 altos, 150 medios, 3 baixos). 48% mitigaveis pelo WAF
- Geracao simultanea em Markdown + JSON pelo `AuditReportBuilder`; ordenacao por severidade e agrupamento por area funcional
- Resumo exibido no stdout com top 10 areas afetadas

### Findings Criticos Detectados (4) - prazo: imediato
1. `app/Http/Controllers/Api/WebhookController.php` - sem HMAC
2. `app/Http/Controllers/Panel/Admin/SumUpController.php` - sem HMAC explicito
3-4. Outros webhooks (ver `storage/app/security/audit-report-latest.md`)

### Findings Altos Principais
- LoginController sem 2FA (SEC-AUTH-2FA)
- LoginController sem rate limit explicito (SEC-AUTH-THROTTLE)
- ImpersonateController sem log de auditoria e sem 2FA (SEC-IMP-LOG, SEC-IMP-2FA)
- CSP, HSTS, X-Frame-Options ausentes (SEC-HDR)
- 47 usos de `{!! !!}` em Blade (SEC-XSS) - maioria em painel admin/novo
- Cookies de sessao sem `Secure/HttpOnly/SameSite` no .env.example (SEC-CFG-SESSION)
- popen() usado em 1 local (SEC-RCE)
- `selectRaw`/`whereRaw` em varios pontos (SEC-SQL)
- Rotas mutantes sem auth em rotas publicas de checkout/contato (SEC-ROUTE-NOAUTH)

### Decisoes Tecnicas
- Scanners implementam `Scanner` interface (Strategy) e sao registrados no comando para permitir filtro via `--only=php-ast,blade,routes`
- `AuditFinding` e imutavel com todos os campos previstos no Requisito 23.1 (id, severidade, area, arquivo, linha, contexto, recomendacao, prazo, mitigavel pelo WAF, controle compensatorio)
- Scanner AST usa parser PHP via `nikic/php-parser v5.7.0` (ja disponivel como dep transitiva do Laravel)
- `areaFromPath()` classifica findings por area funcional para o relatorio agrupar por `Auth | Uploads | Webhooks | Impersonacao | API | Painel Admin | Painel Novo | Area Publica | Headers | Config | SQL | Blade | Outros`
- Relatorio Markdown mostra contexto (trecho de linhas) apenas para severidade critical/high (requisito 1.15)
- Fixtures negativas e property tests (3.12, 3.13, 3.14) marcados como opcionais - implementacao adiada para quando Eris estiver resolvido

### Alterado
- `AbstractScanner::areaFromPath()` refinado para classificar corretamente rotas e controllers publicos
- `WebhookScanner` apertado para nao gerar falsos positivos em controllers que apenas mencionam "webhook" (agora exige nome de arquivo, classe `*Webhook*` ou chamada a `hash_hmac`)

### Pendente (Fase 2+)
- Revisar os 4 findings criticos (webhooks sem HMAC) e aplicar correcoes
- Property tests do scanner (depende do Eris)
- Painel do superadmin para visualizar o relatorio (Fase 6)

---

## [2026-05-11] - Fase 0 WAF - Preparacao (spec waf-e-auditoria-seguranca)

### Adicionado
- Spec completa `.kiro/specs/waf-e-auditoria-seguranca/` (requirements.md, design.md, tasks.md) cobrindo auditoria ponta a ponta, WAF proprio e painel do superadmin
- `config/waf.php` com chaves `enabled`, `mode`, `thresholds`, `fail_policy`, `retention`, `exempt_routes`, `rate_limits`, `auto_block`, `masking` e timeouts
- Variaveis em `.env.example`: `WAF_ENABLED=false`, `WAF_MODE=detection-only`, `WAF_FAIL_POLICY=open` e limiares default
- Canal de log `waf` em `config/logging.php` (driver daily, 30 dias de retencao)
- Esqueleto de `app/Http/Middleware/WafMiddleware.php` (curto-circuita enquanto `WAF_ENABLED=false`)
- `WafMiddleware` registrado em `app/Http/Kernel.php` apos os middlewares base e antes de `TrackServiceVisit`
- 8 migrations criadas (nao aplicadas ainda, aguardando deploy controlado):
  - `waf_rules` (id, uid ULID, name, attack_pattern, scope JSON, matcher_type, matcher_payload JSON, score, action, severity, is_active, quarantined)
  - `waf_rule_versions` (historico append-only com snapshot do estado anterior)
  - `waf_events` (id, uid ULID, request_id, occurred_at, ip, country, asn, user_id, method, route, path, status, risk_score, decision, rules_fired JSON, samples JSON)
  - `waf_ip_blocklist` e `waf_ip_allowlist` (CIDR + ip_start/ip_end BINARY com indice de range)
  - `waf_settings` (key/value com seeds iniciais)
  - `waf_false_positives` (marcacoes manuais)
  - `waf_alerts_config` (canais email/webhook por gatilho)
- 8 models Eloquent em `app/Models/Waf/` (WafRule, WafRuleVersion, WafEvent, WafIpBlocklistEntry, WafIpAllowlistEntry, WafSetting, WafFalsePositive, WafAlertConfig) com scopes e constantes de decisao/severidade
- Dependencia `giorgiosironi/eris ^0.14` adicionada em `require-dev` (composer update pendente por conflito de dompdf com PHP 8.5)
- Suite `Property` ja registrada em `phpunit.xml` apontando para `tests/Property`

### Decisoes Tecnicas
- Spec dividida em 8 fases; esta entrega e a Fase 0 (preparacao sem impacto em producao)
- Middleware posicionado no topo do pipeline global para inspecionar toda requisicao antes de rotas/sessoes
- IP_Blocklist/Allowlist usam `BINARY(16)` para range lookup eficiente em IPv4 e IPv6
- WAF_Events correlacionam com logs da aplicacao via `request_id` (UUID propagado pelo middleware)
- Fail policy configuravel (`open`/`closed`) para escolher entre permitir ou bloquear em caso de exceao do engine

### Pendente (proximas fases)
- Aplicar `php artisan migrate` em producao (aguardando janela)
- Fase 1: auditoria de seguranca ponta a ponta (`php artisan security:audit`) com 8 scanners
- Fase 2-3: engine, regras, rate limit, parser/serializador com round-trip
- Fase 5-6: hardening global + painel do superadmin AdminLTE

---

## [2026-05-11] - Modulo Revistas Digitais (Flipbook)

### Adicionado
- Modulo completo de Revistas Digitais com visualizador flipbook
- Migration `magazines` (titulo, slug, PDF, capa, categoria, edicao, status, visibility, views_count, soft deletes)
- Model `Magazine` com scope `visibleTo($user)` baseado em interesse "Noticias"
- Controller publico (listagem + flipbook viewer)
- Controller admin (CRUD com upload drag-and-drop)
- Dois engines de flipbook alternaveis pelo admin:
  - DearFlip (padrao): leve, streaming progressivo, controles nativos
  - PDF.js + StPageFlip: renderizacao Mozilla com efeito 3D page-flip
- Deteccao automatica de spreads: paginas landscape (aspect > 1.15) divididas em 2
- Carregamento progressivo: renderiza 4 paginas iniciais, lazy-load restante
- Som de page-flip realista via Web Audio API
- Loading branded com logo da plataforma no centro do circulo
- Setas laterais customizadas posicionadas junto ao livro
- Toolbar inferior com navegacao, som, zoom, download, fullscreen
- Pagina /revistas com hero, cards com mascara fume, grid responsivo, Swiper mobile
- Permissoes: magazines.access, magazines.publish
- Configuracoes no admin (plugin, revistas por pagina, opacidade mascara)
- Comando `php artisan magazines:import-manchete` para importar edicoes
- 14 edicoes da Revista Manchete importadas e publicadas
- Componente `x-unn-dropzone` reutilizavel para drag-and-drop

### Corrigido
- Superadmin com acesso irrestrito ao painel novo
- Upload de PDF: getSize() antes do storeUploadedFile
- Inversao de cores no flipbook: forcado color-scheme light
- Ultima pagina nao mais se sobrepoe (showCover + spread detection)

---

## [2026-05-11] - Cron Autonomo

### Corrigido
- Tarefas do banco agora atualizam `last_run_at` automaticamente quando executadas pelo scheduler
- Registra log automatico em `scheduled_task_logs` apos cada execucao
- Botao "Executar Todas" adicionado na pagina de cron (ambos paineis)

---

## [2026-05-10] - Busca de Estabelecimentos (TomTom)

### Adicionado
- TomTom como provedor primario de busca de estabelecimentos
- Cascata com fallback: TomTom > Google Places > LocationIQ
- Se busca com bias retorna vazio, tenta sem bias
- Flag `out_of_radius` na resposta
- Campo TomTom API Key no admin

---

## Stack Tecnico

- Backend: Laravel 10.x, PHP 8.x, MySQL/MariaDB
- Frontend site + painel novo: Tailwind CSS (CDN), jQuery 3.6, FilePond, Cropper.js
- Frontend admin antigo: AdminLTE 3.2, Bootstrap 4, jQuery
- Deploy: git push + ssh git fetch && git reset --hard origin/main
- Storage: local em public/storage/

## Cores da Plataforma

- Azul principal: #1F5EDB (--unn-azul-1)
- Azul secundario: #177FD6 (--unn-azul-2)
- Azul escuro: #1D3FC4 (--unn-azul-3)
