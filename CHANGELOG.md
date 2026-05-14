# CHANGELOG - SOMOS UNN

---

## [2026-05-14] - Paridade de Eventos: /admin e /painel

### Adicionado ao painel legado /admin
- Método `togglePublished()` em `Admin\EventController` (publicar/despublicar com bloqueio se sem capa)
- Método `move()` em `Admin\EventController` (drag & drop no calendário)
- Rota `POST admin/events/{event}/toggle-published` → `admin.events.toggle-published`
- Rota `POST admin/events/{event}/move` → `admin.events.move`

### Adicionado ao painel moderno /painel/admin
- Método `updateCalendarSettings()` em `Panel\Admin\EventController` (cores, templates, view inicial)
- Método `defaultCalendarSettings()` (configurações padrão do calendário)
- `loadCalendarSettings()` expandido para ler/validar JSON do Setting com fallback robusto
- Rota `POST painel/admin/events/calendar/settings` → `panel.admin.events.calendar.settings`

### Resultado
- Ambos os painéis agora têm: `index`, `show`, `create`, `store`, `edit`, `update`, `destroy`, `feed`, `list`, `toggleField`, `setCover`, `togglePublished`, `move`, `updateCalendarSettings`
- Operações idênticas, apenas com prefixos diferentes (`/admin/events/*` e `/painel/admin/events/*`)

### Arquivos afetados
- `app/Http/Controllers/Admin/EventController.php`
- `app/Http/Controllers/Panel/Admin/EventController.php`
- `routes/web.php`

---

## [2026-05-14] - Padronizacao do Layout: /connection/blocked

### Alterado
- Pagina `/connection/blocked` corrigida para usar `@section('panel_content')` (estava usando `@section('content')` que nao envolvia no layout do painel)
- Adicionado `@section('panel_breadcrumb')`
- Hero card vermelho/slate consistente com o tom da feature (bloqueio)
- Card da lista com header padronizado (icone + titulo + contador)
- Avatares com ring rosa sutil
- Botao "Desbloquear" estilizado em rosa com border
- Suporte completo a dark mode

### Arquivos afetados
- `resources/views/panel/connections/blocked.blade.php`

---

## [2026-05-14] - Padronizacao do Layout: /meu-parceiro

### Alterado
- Pagina `/meu-parceiro` migrada de `layouts.app` (publico) para `panel.layouts.app` (painel)
- Substituidos estilos inline por classes Tailwind padrao do painel
- Hero card com gradient padrao do sistema (azul/slate)
- Cards de stats redesenhados (ícone + label + valor) seguindo padrao do painel
- Lista de cupons com badges e botões padronizados
- Modal de cupom com estilo consistente (border-radius, sombras, focus rings)
- Suporte a dark mode
- Sem alteracao de funcionalidade — apenas visual

### Arquivos afetados
- `resources/views/member/partner/index.blade.php`

---

## [2026-05-14] - Correcao Critica: S3 Funcional via Painel

### Corrigido — UploadStorage agora aplica configuracao S3 do banco
- `applyRuntimeConfig()` agora le credenciais S3 do banco (Setting) com fallback para .env
- `selectedDisk()`, `effectiveDisk()`, `disk()` e `isLocal()` agora respeitam a configuracao salva
- Sobrescreve dinamicamente `filesystems.disks.s3.*` em runtime sem usar closures (mantem `config:cache` funcional)
- Fallback automatico para disco `public` se credenciais S3 estiverem incompletas
- Sem isso, o painel de Armazenamento era apenas cosmetico — agora salva e aplica de verdade

### Arquivos afetados
- `app/Support/UploadStorage.php`

---

## [2026-05-14] - Polimento Final: IDrive e2 + Pagina de Reputacao

### Adicionado — Guia de credenciais IDrive e2
- Card informativo no painel de Armazenamento explicando como obter credenciais IDrive e2
- Link direto para `idrive.com/s3-storage-e2` e passo a passo (Bucket, Access Keys, Endpoint)
- Aviso de seguranca sobre armazenamento de credenciais

### Adicionado — Pagina de detalhes da Reputacao do membro
- Nova rota `panel.reputation` (`/painel/reputacao`)
- `ReputationController@show` com dados de score + historico dos ultimos 6 meses
- Hero card com badge grande, score numerico, label e barra de progresso
- 4 barras de progresso (uma por dimensao: Entrega, Relacionamento, Interacao, Engajamento)
- Secao de dicas de melhoria (exibida apenas para dimensoes com score < 50)
- Grafico historico HTML/CSS dos ultimos 6 meses com cores por faixa de score
- Item "Minha Reputacao" no menu principal da sidebar do painel

### Arquivos criados
- `app/Http/Controllers/Panel/ReputationController.php`
- `resources/views/panel/reputation/show.blade.php`

### Arquivos alterados
- `routes/web.php` (rota `panel.reputation`)
- `resources/views/panel/admin/settings/partials/storage.blade.php` (guia IDrive e2)
- `resources/views/panel/partials/sidebar.blade.php` (item "Minha Reputacao")

---

## [2026-05-14] - Sistema de Reputação do Membro

### Adicionado
- Tabela `member_reputation_scores` (score geral + 4 dimensões por membro)
- Tabela `member_reputation_history` (histórico diário para gráfico de evolução)
- `ReputationService` com cálculo completo: Entrega (40%), Relacionamento (25%), Interação (20%), Engajamento (15%)
- Comando `php artisan reputation:recalculate` (diário, com --user para recálculo individual)
- Job `RecalculateReputationJob` para recálculo assíncrono via eventos
- Blade Component `<x-reputation-badge>` com 3 tamanhos (sm/md/lg) e tooltip com breakdown
- Badge integrado no perfil do membro e nos posts do feed social
- Accessor `$user->reputation_score` no model User (cacheado, sem recálculo no request)
- 5 níveis: Excelente (90-100), Confiável (70-89), Regular (50-69), Atenção (30-49), Baixa Reputação (0-29)
- Decay por inatividade: -2pts/semana após 30 dias sem login (mínimo 20)
- Cache de 24h por membro para performance

### Arquivos criados
- `app/Services/ReputationService.php`
- `app/Models/MemberReputationScore.php`
- `app/Models/MemberReputationHistory.php`
- `app/Console/Commands/RecalculateReputationScores.php`
- `app/Jobs/RecalculateReputationJob.php`
- `app/View/Components/ReputationBadge.php`
- `resources/views/components/reputation-badge.blade.php`
- `database/migrations/2026_07_21_000003_create_member_reputation_scores_table.php`
- `database/migrations/2026_07_21_000004_create_member_reputation_history_table.php`

### Arquivos alterados
- `app/Console/Kernel.php` (schedule diário)
- `app/Models/User.php` (accessor reputation_score)
- `resources/views/social/feed.blade.php` (badge nos posts)
- `resources/views/social/profile.blade.php` (badge no perfil)

---

## [2026-05-14] - Implementação de 3 Features Pendentes

### Adicionado — Lista de Bloqueados Visível
- Link "Bloqueados" com badge na sidebar do feed social
- Link "Bloqueados" com badge no cabeçalho do chat
- Link "Bloqueados" com badge no sidebar do painel (condicionado a feature community)
- Contagem de usuários bloqueados exibida em tempo real

### Adicionado — Cancelamento Automático de Pedidos (PIX 24h / Cartão 48h)
- Método `cancelCheckout()` no SumUpService para cancelar checkouts via API
- Deadlines configuráveis: `pix_cancel_hours` (default 24h), `card_cancel_hours` (default 48h)
- Liberação automática de cupons reservados ao cancelar pedido
- Metadados detalhados: "Auto-cancel: payment window expired (pix, 24h)"
- Liberação de vagas de eventos ao cancelar pedido

### Adicionado — Taxa da Plataforma em Assinaturas
- Migration `add_seller_id_to_plans_table` (FK nullable para users)
- Relacionamento `seller()` no model Plan
- SubscriptionController aplica `MarketplaceFee::amount()` quando plano tem seller_id
- Registra `platform_fee_percent` nos metadados do pedido

### Arquivos afetados
- `resources/views/social/feed.blade.php`
- `resources/views/chat/index.blade.php`
- `resources/views/panel/partials/sidebar.blade.php`
- `app/Console/Commands/CancelUnpaidOrders.php`
- `app/Services/Payment/SumUpService.php`
- `app/Models/Plan.php`
- `app/Http/Controllers/SubscriptionController.php`
- `database/migrations/2026_07_21_000002_add_seller_id_to_plans_table.php` (novo)

---

## [2026-05-14] - Segurança: Finalização Completa

### Adicionado
- Tabela `payment_webhook_logs` para auditoria de webhooks (provider, external_id, request_id, signature, status, payload, ip)
- Model `PaymentWebhookLog` para registrar cada chamada de webhook
- Logging de webhooks MP no banco de dados (cada chamada registrada para auditoria)
- Regras de bloqueio no `public/.htaccess`: .env, .git, composer.*, package*.json, storage/, vendor/, config/, database/
- Bloqueio de dotfiles e extensões sensíveis (.sql, .log, .ini, .conf, .bak, .yml, .yaml, .lock) via FilesMatch

### Alterado
- `PublicStorageProxyController`: double-decode (urldecode×2), extensões perigosas bloqueadas (php, phtml, phar, cgi, exe, sh, bat, cmd, js), logging no canal security
- `PaymentWebhookController`: cada webhook MP agora é registrado na tabela payment_webhook_logs
- `config/logging.php`: canal security com nível warning (era info)

### Verificação de segurança completa
- ✅ BlockSensitiveRoutesInProduction (global, 11 padrões)
- ✅ SecurityHeadersMiddleware (X-Frame, nosniff, Referrer-Policy, HSTS, sem camera=())
- ✅ Path traversal protection (double-decode, blacklist + whitelist)
- ✅ Webhook MP (anti-replay, header check, DB logging, consulta API)
- ✅ Login throttle (10/min login, 5/min register, 5/min reset)
- ✅ Security log channel (daily, 90 dias)
- ✅ .htaccess hardening (public + uploads)
- ✅ WAF com 18 regras ativas

### Arquivos afetados
- `app/Http/Controllers/PaymentWebhookController.php`
- `app/Http/Controllers/PublicStorageProxyController.php`
- `app/Models/PaymentWebhookLog.php` (novo)
- `database/migrations/2026_07_21_000001_create_payment_webhook_logs_table.php` (novo)
- `config/logging.php`
- `public/.htaccess`

---

## [2026-05-14] - Preparação S3 Cloud Storage (IDrive e2)

### Adicionado
- Pacote `league/flysystem-aws-s3-v3` instalado no servidor (suporte S3)
- Painel administrativo de configuração de armazenamento em Configurações > Armazenamento
- Campos configuráveis pelo painel: Driver, Bucket, Endpoint, Region, Access Key, Secret Key, URL, Path Style
- Botão "Testar Conexão S3" com resultado inline (upload, exists, url, read, delete)
- Comando artisan `php artisan storage:test-s3` para teste via CLI
- Disco S3 em `config/filesystems.php` lendo do banco (Setting) com fallback para .env
- `FILESYSTEM_DISK` controla disco ativo (default: public/local)
- `.env.example` atualizado com todas as variáveis S3/IDrive e2
- Compatível com: IDrive e2, AWS S3, Cloudflare R2, Wasabi, MinIO, Backblaze B2
- Indicador visual de status (Local/S3 ativo) no header da página

### Alterado
- `config/filesystems.php`: lê configurações do banco de dados com fallback seguro para env()
- `SettingController`: grupo 'storage' adicionado com testS3() endpoint
- Sidebar do painel: link "Armazenamento" com ícone cloud

### Arquivos afetados
- `config/filesystems.php`
- `app/Http/Controllers/Admin/SettingController.php`
- `app/Console/Commands/TestS3Connection.php` (novo)
- `resources/views/panel/admin/settings/partials/storage.blade.php` (novo)
- `resources/views/panel/admin/settings/index.blade.php`
- `resources/views/panel/partials/sidebar.blade.php`
- `routes/web.php`
- `.env.example`
- `composer.json` / `composer.lock`

---

## [2026-05-13] - Segurança: Travas de Rotas Sensíveis e Hardening

### Adicionado
- Rota `/demo-somos-unicas` adicionada à lista de padrões bloqueados em produção
- Alias `sensitive.production` registrado no Kernel.php para uso explícito em rotas
- Proteção contra path traversal no `PublicStorageProxyController` (bloqueia `..`, `.env`, `config/`, `vendor/`, etc.)
- Validação de extensões permitidas em `/storage/{path}` e `/uploads/{path}`
- Proteção anti-replay no webhook Mercado Pago (cache de `x-request-id` por 24h)
- Verificação de headers `x-signature` e `x-request-id` no webhook MP (log warning se ausentes)

### Alterado
- Extensões permitidas em storage/uploads reduzidas para: imagens, PDF, vídeo, áudio e fontes
- Removidas extensões perigosas: zip, rar, json, txt, doc, docx, xls, xlsx, ppt, pptx

### Arquivos afetados
- `app/Http/Middleware/BlockSensitiveRoutesInProduction.php`
- `app/Http/Kernel.php`
- `app/Http/Controllers/PublicStorageProxyController.php`
- `app/Http/Controllers/PaymentWebhookController.php`

---

## [2026-05-13] - Editor de Certificado: Redesign Layout Vertical

### Adicionado
- Layout vertical: certificado inteiro visível em cima, controles organizados abaixo em grid
- Zoom e botão Fit integrados na barra do canvas
- Preview de certificado aceita GET (dados salvos) além de POST (dados do formulário)

### Removido
- Card hero azul decorativo que ocupava espaço sem funcionalidade
- Scroll do canvas (agora overflow-hidden)
- Layout side-by-side que cortava o certificado

### Alterado
- Cards de controle: 2 colunas (lg), gradiente no dark mode, sombras, separadores nos headers
- Rota `courses/{course}/certificate/preview` aceita GET e POST (corrige erro 405)

### Arquivos afetados
- `resources/views/panel/admin/partials/certificate-editor.blade.php`
- `app/Http/Controllers/Panel/Admin/CourseController.php`
- `routes/web.php`

---

## [2026-05-13] - Multi-Gateway Checkout (spec multi-gateway-checkout)

### Adicionado
- Suporte a multiplos gateways simultaneos (Mercado Pago + SumUp) no checkout
- Metodo `resolveAllActiveGatewaysForSeller()` em `GatewayAccount` que resolve todos os gateways ativos independentemente
- Seletor visual de gateway no checkout quando ambos estao ativos (cards clicaveis com icones)
- Configuracoes independentes de parcelamento por gateway (mercadopago_max_installments, mercadopago_installments_no_interest, mercadopago_installment_tax)
- Configuracao de expiracao do PIX por gateway (mercadopago_pix_expiration_minutes, sumup_pix_expiration_minutes)
- Validacao de metodo minimo por gateway (HTTP 422 se gateway ativo sem metodo de pagamento)
- Clamping de valores para novas settings de parcelamento e expiracao PIX
- Paridade de configuracoes entre painel moderno (Tailwind) e painel legado (AdminLTE)
- Testes de integracao em `tests/Feature/MultiGateway/GatewayResolutionTest.php`

### Removido
- Logica de exclusividade entre gateways no `SettingController` (toggle e update)
- Aviso de exclusividade de gateway nas views de configuracao (ambos os paineis)

### Alterado
- `EventReservationController`: checkout e reserve agora suportam multiplos gateways ativos
- `CheckoutController`: PIX SumUp usa `sumup_pix_expiration_minutes` (antes hardcoded 30min)
- `CheckoutController`: PIX Mercado Pago usa `mercadopago_pix_expiration_minutes` com fallback
- View `checkout/transparent.blade.php`: renderiza seletor de gateway quando 2 ativos
- View `partials/checkout/sumup-card-form.blade.php`: timer PIX usa valor configuravel
- `resolveActiveGatewayForSeller()` marcado como `@deprecated` (usa internamente o novo metodo)

### Arquivos principais afetados
- `app/Models/GatewayAccount.php`
- `app/Http/Controllers/Admin/SettingController.php`
- `app/Http/Controllers/EventReservationController.php`
- `app/Http/Controllers/CheckoutController.php`
- `resources/views/checkout/transparent.blade.php`
- `resources/views/partials/checkout/sumup-card-form.blade.php`
- `resources/views/admin/settings/partials/gateway.blade.php`
- `resources/views/panel/admin/settings/partials/gateway.blade.php`
- `tests/Feature/MultiGateway/GatewayResolutionTest.php`

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
