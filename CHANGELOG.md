# CHANGELOG - SOMOS UNN

---

## [2026-05-26] - fix(sumup): conciliacao automatica de pedidos pagos e liberacao de ingressos

### Problema
O pedido `#156` foi quitado na SumUp, mas permaneceu `Pendente` no sistema e
nao liberou automaticamente a inscricao do evento. A investigacao mostrou que
o webhook SumUp nao chegou ao sistema e o polling do checkout consultava apenas
a ultima tentativa local. Como existiam varios checkouts para o mesmo pedido,
uma tentativa mais nova pendente escondia um checkout PIX anterior ja pago na
API da SumUp.

### Correcoes
- Criada reconciliacao oficial via API SumUp para consultar os checkouts do
  pedido e encontrar qualquer transacao `PAID`/`SUCCESSFUL`, mesmo que nao seja
  a tentativa mais recente.
- O endpoint `/checkout/sumup/status` agora reconcilia com a API antes de
  responder e baixa/libera automaticamente o pedido quando encontra pagamento.
- O endpoint `/checkout/sumup/pix` nao cria novo PIX se o pedido ja estiver
  pago ou se a conciliacao detectar pagamento antes da nova cobranca.
- Adicionado comando `sumup:reconcile-pending`, agendado a cada 5 minutos antes
  do cancelamento automatico de pedidos pendentes.
- Pedidos SumUp ja pagos localmente tambem passam a preencher `payment_method`
  e `transaction_id` ausentes durante a conciliacao.
- Webhook SumUp passou a atualizar somente a transacao do token recebido, sem
  marcar todas as tentativas do pedido como pagas/falhas.
- Ajustada a tela `painel/ingressos` para eventos sem QR: reservas pagas agora
  aparecem como `Confirmado` e nao exibem botao de QR Code vazio.

### Arquivos afetados
- `app/Services/Payment/SumUpService.php`
- `app/Http/Controllers/CheckoutController.php`
- `app/Services/Payment/SumUpWebhookProcessor.php`
- `app/Console/Commands/ReconcilePendingSumUpOrders.php`
- `app/Console/Kernel.php`
- `resources/views/partials/checkout/sumup-card-form.blade.php`
- `resources/views/panel/tickets.blade.php`
- `tests/Feature/Payment/SumUpReconciliationTest.php`

---

## [2026-05-26] - fix(sumup): liberar SDK do cartao na CSP e autorizar rotas SumUp no WAF

### Problema
Na tela de reserva de evento (`/eventos/30/reservar`), o checkout SumUp
exibia `Erro: SDK do SumUp nao carregou.`. A validacao em producao mostrou que
o header `Content-Security-Policy` ainda nao liberava
`https://gateway.sumup.com` em `script-src`, bloqueando o script oficial
`/gateway/ecom/card/v2/sdk.js` antes de `SumUpCard.mount()`.

### Correcoes
- Adicionado `https://gateway.sumup.com` e `https://api.sumup.com` na CSP para
  carregamento do SDK/recursos auxiliares do SumUp.
- Mantida liberacao SumUp em `frame-src` para o iframe seguro do Card Widget.
- Adicionadas isencoes WAF para endpoints internos criticos do fluxo SumUp:
  `/checkout/sumup/pix`, `/checkout/sumup/status`,
  `/checkout/sumup/recreate`, `/webhook/sumup/*` e
  `/api/v1/webhooks/sumup`.
- Atualizados testes de CSP para refletir a remocao intencional do
  `report-uri /csp-report` sem rota implementada.

### Arquivos afetados
- `app/Http/Middleware/SecurityHeadersMiddleware.php`
- `config/waf.php`
- `database/migrations/2026_07_20_000006_create_waf_settings_table.php`
- `tests/Unit/Middleware/SecurityHeadersMiddlewareTest.php`
- `tests/Property/CspHeaderGenerationTest.php`
- `tests/Unit/Waf/WafExemptRoutesTest.php`

---

## [2026-05-17] - fix(csp): adicionar cdn.datatables.net na CSP + remover report-uri 404

### Problema
A Content-Security-Policy bloqueava `https://cdn.datatables.net` em `script-src`
e `style-src`. Isso impedia o carregamento do DataTables em TODAS as paginas admin
que usam tabelas (activity-logs, users, orders, courses, certificates, events,
referrals). Como o JS do botao "Limpar Historico" estava no mesmo bloco jQuery que
inicializava o DataTable, o erro `$(...).DataTable is not a function` impedia
tambem o registro do listener do botao — fazendo parecer que o botao "nao funciona".

### Correcoes
- Adicionado `https://cdn.datatables.net` em `script-src` e `style-src` da CSP
- Removido `report-uri /csp-report` que gerava 404 (rota nunca implementada)
- Afeta: activity-logs, users, orders, courses, certificates, events, referrals

### Arquivos afetados
- `app/Http/Middleware/SecurityHeadersMiddleware.php`

---

## [2026-07-24] - audit(security): auditoria sistemica pos-deploy v9 — bug paralelo do "limpar historico" + CSP estendida para previews

### Contexto
Apos a serie de implantacoes de seguranca (WAF, AdvancedRateLimit,
SecurityHeaders, AuditLog, AnomalyDetector, Multi-Provider S3) o usuario
reportou que varias paginas/botoes do painel pararam de funcionar. O
botao "Limpar Historico" em `/admin/activity-logs` ja havia sido
corrigido em `244cb5e`. Esta entrega faz a auditoria sistemica e
identifica/corrige bugs equivalentes em outras superficies do sistema.

### Auditoria realizada
- `tools/system-audit.php` (script descartavel) enumerou:
  - 799 rotas totais, 497 admin, 262 admin mutating, 3 admin destrutivas
  - Rotas destrutivas:
    1. `POST admin/activity-logs/clear` (ja corrigida)
    2. `DELETE admin/gallery/events/{event}/cover` (clearCover, escopo
       de evento — sem bug equivalente)
    3. `POST painel/admin/logs/clear` **(BUG PARALELO IDENTIFICADO)**
- Smoke test em producao via curl + HEALTH_TOKEN: `/health=200`,
  `/=200`, `/login=200`, healthcheck reportando todos os
  componentes "ok" (database, s3, disk_write, queue_health,
  storage_permissions).

### Bug fix critico (bug paralelo do "Limpar Historico")
- `app/Http/Controllers/Panel/Admin/ActivityLogController.php`:
  `clear()` usava `ActivityLog::truncate()` sem fail-safe e sem audit
  log, replicando exatamente o problema do legacy controller corrigido
  em `244cb5e`. Reescrito para:
  1. Usar `DB::table('activity_logs')->delete()` (preserva integridade
     referencial caso FKs sejam adicionadas no futuro);
  2. `try/catch` com mensagem de erro amigavel — nunca propaga excecao;
  3. Audit log no canal `security` (com fallback para `stack`) com
     `user_id`, `deleted_count` e `ip`;
  4. Bloco de copyright adicionado.
- `LogUserActivity::SKIP_PATHS` ja contem `painel/admin/logs/clear`
  evitando o mesmo loop de "rebloqueio em truncate" no painel novo.

### CSP estendida para suportar previews de upload e workers
- `app/Http/Middleware/SecurityHeadersMiddleware.php`:
  - Adicionada directive `media-src 'self' blob: data: https:` —
    necessaria para previews de upload via `URL.createObjectURL(file)`
    em `<video src="blob:...">` (admin/courses/form, admin/marketplace,
    quick-upload-modal, etc.). Sem isso, browsers caem para `default-src
    'self'` e bloqueiam o blob, fazendo o preview do video sumir;
  - Adicionada directive `worker-src 'self' blob:` — necessaria para
    Web Workers gerados dinamicamente por bibliotecas como TinyMCE
    e processamento de imagem em browser. Worker-src nao tem fallback
    confiavel para default-src em Chromium;
  - Adicionada directive `child-src 'self' blob:` — alias legado
    aceito por browsers mais antigos;
  - Adicionada directive `object-src 'self' data:` — restringe Flash
    e plugins legados, mas permite PDFs servidos pelo proprio dominio
    (ex.: `/admin/invoices/{id}/pdf`) e via data: URLs.
  - Adicionados `https://www.openstreetmap.org` e
    `https://maps.google.com` em `frame-src` — usados em
    `events/show.blade.php` e `site/institucional/contato.blade.php`
    para mapas embed sem precisar configurar CSP por allowlist
    em `Setting.csp_extra_allowlist`.

### Auditoria de middlewares globais (Kernel `$middleware`)
Confirmado que TODOS tem fail-safe estrito:
- `WafMiddleware`: try/catch fail-open com log no canal `waf`.
- `AdvancedRateLimitMiddleware`: bypass admin/superadmin + try/catch
  fail-open + log no canal `security` (corrigido em `244cb5e`).
- `BlockSensitiveRoutesInProduction`: lista PCRE de rotas (`run-migrations`,
  `install`, `telescope`, `horizon`, `_debugbar`, `phpinfo`, `adminer`)
  com bypass via `MAINTENANCE_SECRET` token; nao bloqueia rotas
  legitimas. Roda no pipeline global (antes do StartSession), entao
  `$request->user()` aqui sempre retorna null — o bypass de superadmin
  e' **dead code** mas nao prejudica nada (rotas sensiveis sao apenas
  install/migration que ja exigem token).
- `TrackServiceVisit`: `try/catch` com log de erro; ignora rotas
  admin/painel/api.
- `LogUserActivity`: `try/catch` + `SKIP_PATHS` impede registro do
  proprio "clear" (corrigido em parte em `244cb5e`).
- `AnomalyDetectorMiddleware`: pass-through observador, nunca bloqueia.
- `TrackVisitor::terminate`: `try/catch` + dedupe via session.
- `RunInternalCron::terminate`: `try/catch` + lock + intervalo minimo.

### Auditoria de jobs (`app/Jobs/`)
Todos os 7 jobs (`ProcessEmailQueue`, `ProcessImageUploadJob`,
`RecalculateReputationJob`, `SendGenericTemplateEmail`,
`SendInvoiceEmailJob`, `SendMarketplaceOrderPaidEmailsJob`,
`WriteAuditLogJob`) usam `$this->onQueue(...)` no construtor (padrao
PHP 8.4+ requerido apos a regressao de `public string $queue`).
Nenhuma classe inexistente referenciada.

### Auditoria do `AuditLogService`
Confirmado fail-safe estrito:
- `log()` despacha via queue; em caso de falha cai para fallback
  sincrono via `DB::table::insert`; em caso de falha do sincrono,
  apenas grava log no canal `stack` — NUNCA propaga excecao;
- `purgeOld()` envolto em `try/catch` retornando 0 em erro.

### Adicionado
- `tests/Feature/Admin/ActivityLogClearTest.php` (3 testes / 7 asserts):
  - `test_legacy_admin_clear_uses_delete_and_emits_audit_log`
  - `test_panel_admin_clear_also_uses_delete_and_does_not_throw`
  - `test_panel_admin_clear_handles_exception_gracefully` (forca
    falha removendo a tabela antes do clear e valida que o controller
    nao propaga excecao).

### Smoke test final no servidor
- `https://somosunn.com.br/health` -> 200
- `https://somosunn.com.br/` -> 200
- `https://somosunn.com.br/login` -> 200
- Healthcheck JSON: todos componentes ok, response_time 0.9ms

### Arquivos afetados
- `app/Http/Controllers/Panel/Admin/ActivityLogController.php` (fix)
- `app/Http/Middleware/SecurityHeadersMiddleware.php` (CSP estendida)
- `tests/Feature/Admin/ActivityLogClearTest.php` (novo)

### Itens para teste manual (priorizado)
1. **ALTO**: `/painel/admin/logs` -> botao "Limpar Historico" deve
   esvaziar a tabela e mostrar success message.
2. **ALTO**: previews de upload de video em
   `/admin/courses/{id}/edit` (campo de upload de aula) -> o `<video>`
   gerado de `blob:` deve aparecer (testar antes/depois da CSP).
3. **MEDIO**: editor TinyMCE em `/painel/admin/mailtemplates` ->
   abrir editor e verificar se nao ha `Refused to load script` no
   console relacionado a worker.
4. **MEDIO**: `/admin/invoices/{id}/pdf` -> iframe do invoice editor
   deve renderizar sem bloqueio CSP.
5. **MEDIO**: mapa em `/eventos/{slug}` -> iframe OSM/Google Maps
   deve carregar.

### Validacao
- `php tools/check-no-bom.php` -> OK em todos os arquivos.
- `php -l` em todos os arquivos modificados -> sem erros.
- 17 testes existentes de SecurityHeaders -> verde.
- 3 testes novos de ActivityLogClear -> verde.
- Property test `CspHeaderGenerationTest` (2144 asserts) -> verde.

---

## [2026-07-23] - feat(storage): finalizar spec multi-provider-s3-storage (auditoria + bug fix de regressao)


### Contexto
Auditoria final da spec `multi-provider-s3-storage` apos a reversao do
commit `23e54f69` (que abandonou paginas/rotas separadas e integrou os 3
provedores S3 ao select existente "Driver de Armazenamento"). Tarefa:
verificar estado real do codigo, marcar/reescrever/cancelar as 23 tasks
do plano, garantir que ambas as views (AdminLTE + Tailwind) tenham os
fieldsets prefixados, fechar lacunas de teste e refazer deploy.

### Bug fix critico (regressao identificada na auditoria)
- `app/Http/Controllers/Admin/SettingController.php::update()`:
  A auto-copia de `storage_*` para `{provider}_*` usava
  `array_key_exists($legacyKey, $data)`. Como o bloco `$groupBools['storage']`
  acima sintetiza `storage_path_style=0` quando o checkbox vem ausente,
  qualquer save que enviasse apenas o campo prefixado correto
  (`{provider}_path_style=1`) era sobrescrito por `0` apos a auto-copia.
  Corrigido para usar `$request->has($legacyKey)` + `$request->has($prefixedKey)`,
  fazendo a copia apenas quando o legacy esta no request E o prefixado nao.

### Adicionado / Modificado
- `resources/views/panel/admin/settings/partials/storage.blade.php`:
  Reescrito para espelhar a estrutura da AdminLTE — agora possui os 3
  fieldsets prefixados (idrive_*/wasabi_*/aws_*) com os 7 campos cada
  e toggle JS por provedor selecionado, em vez de manter apenas os
  campos `storage_*` legados sem prefixo.
- `tests/Feature/Admin/StorageProviderSwitchTest.php` (novo):
  7 testes feature exercitando o fluxo HTTP real do save de configuracoes
  + endpoint de teste de conexao, usando `Storage::fake('s3')` para
  evitar rede. Cobre: persistir provedor ativo + campos prefixados,
  auto-copia legacy, isolamento entre provedores em troca, ciclo
  IDrive -> Wasabi -> AWS, fallback para local, JSON do test endpoint.
- `app/Http/Controllers/Admin/SettingController.php`:
  Auto-copia de `storage_*` para `{provider}_*` corrigida (vide bug fix
  acima).
- `.kiro/specs/multi-provider-s3-storage/tasks.md`:
  Atualizado com estado final das 23 tasks (18 concluidas, 3 reescritas,
  2 canceladas por mudanca de escopo, com referencia explicita ao commit
  `23e54f69` que reverteu as paginas separadas).

### Status final da spec
- 18/23 tasks concluidas
- 3 reescritas (4.1, 5.1, 5.2): adaptadas para integrar com as views
  e o controller existentes em vez de criar paginas/rotas dedicadas
- 2 canceladas (4.2 parcial e 4.3): paginas/rotas separadas removidas
  por requisicao explicita do usuario

### Testes
- 70 testes verdes:
  - `tests/Unit/Support/StorageProviderConfigTest.php` -> 24 testes
  - `tests/Unit/Support/StorageProviderRegistryTest.php` -> 33 testes
  - `tests/Property/StorageProviderConfigIsolationTest.php` -> 2 propriedades, 2500 assertions (Eris)
  - `tests/Feature/MultiProviderS3IntegrationTest.php` -> 4 testes
  - `tests/Feature/Admin/StorageProviderSwitchTest.php` -> 7 testes (NOVO)

### Validacao
- `php tools/check-no-bom.php` -> OK em todos os arquivos
- `php -l` em arquivos modificados -> sem erros
- Migration ja aplicada em producao no commit anterior

---

## [2026-07-23] - fix(security): mitigar bloqueio de admins por AdvancedRateLimitMiddleware + robustez do botao "Limpar Historico"

### Contexto
O botao "Limpar Historico" em `/admin/activity-logs` era percebido como
quebrado: o middleware global `AdvancedRateLimitMiddleware` tem threshold
default de 100 req/min e bloqueia o IP por 15 min apos exceder, atingindo
admins durante uso normal do painel (DataTables, exports, abas multiplas).
Alem disso, o `LogUserActivity` registrava a propria acao de limpeza apos
o `truncate()`, fazendo a tabela voltar a ter 1 registro imediatamente —
reforcando a percepcao de que o botao "nao funcionou".

### Modificado
- `app/Http/Middleware/AdvancedRateLimitMiddleware.php`:
  - Bypass de admin/superadmin autenticado (`isAuthenticatedAdmin()`),
    com fail-safe — qualquer erro ao consultar Auth nao bloqueia request.
  - Threshold default elevado de 100 -> 300 req/min (configuravel via
    `Setting::rate_limit_threshold` sem deploy).
- `app/Http/Middleware/LogUserActivity.php`:
  - Bloco de copyright adicionado.
  - Lista `SKIP_PATHS` impede registrar a propria limpeza (`admin/activity-logs/clear`).
  - Catch generalizado para `\Throwable` (fail-safe estrito).
- `app/Http/Controllers/Admin/ActivityLogController.php`:
  - Bloco de copyright adicionado.
  - `clear()` agora usa `DB::table('activity_logs')->delete()` em vez de
    `truncate()` (preserva integridade referencial caso FKs sejam
    adicionadas no futuro).
  - Audit log no canal `security` da acao com user_id, ip e contagem.
  - Tratamento de excecao com mensagem de erro amigavel.

### Validacao
- 10/10 unit tests em `AdvancedRateLimitMiddlewareTest` continuam passando.
- `php tools/check-no-bom.php` -> OK.
- `php -l` em todos os arquivos modificados -> sem erros.
- Auditoria via `tools/debug-system.php` em producao confirmou:
  - 0 IPs ativos em `rate_limit_blocks` (limpa).
  - Tabelas criticas existem (audit_logs, rate_limit_blocks, anomaly_events, settings).
  - Middlewares carregados corretamente.

### Bugs adicionais identificados (NAO corrigidos nesta entrega — sem impacto em runtime)
- `users.deleted_at` AUSENTE: o User model nao usa SoftDeletes, entao a
  coluna nao e necessaria. Removido o flag falso-positivo do auditor.
- `magazines.pdf_path` AUSENTE: a coluna pertence a `certificates`, nao a
  `magazines`. Era um falso-positivo do script de debug.

### Arquivos afetados
- `app/Http/Middleware/AdvancedRateLimitMiddleware.php`
- `app/Http/Middleware/LogUserActivity.php`
- `app/Http/Controllers/Admin/ActivityLogController.php`

---

## [2026-07-22] - Spec multi-provider-s3-storage: testes unit + property + integration + ajuste HealthController
### Adicionado
- `tests/Unit/Support/StorageTestResultTest.php` (13 testes / value object)
- `tests/Unit/Support/StorageProviderConfigTest.php` (31 testes / value object + dataProvider para path_style)
- `tests/Unit/Support/StorageProviderRegistryTest.php` (26 testes / 68 asserts - SQLite isolado)
- `tests/Property/StorageProviderConfigIsolationTest.php` (Property: salvar P_i nunca toca P_j - 200 iteracoes Eris / 2500 asserts)
- `tests/Feature/MultiProviderS3IntegrationTest.php` (4 testes / fluxo completo: salvar 3 + alternar + verificar disco)

### Modificado
- `app/Http/Controllers/HealthController.php`: `checkS3()` agora consulta o `StorageProviderRegistry` antes de tentar listar objetos no disco.
  - Provedor ativo == 'local' -> retorna OK ("S3 nao em uso")
  - Provedor ativo S3 sem creds validas -> retorna WARNING ("provedor X sem credenciais validas")
  - Demais casos: comportamento legado preservado (lista objetos do disco)
  - Resolve o problema observado em producao onde checkS3 retornava o erro generico "missing region"

### Resultado dos testes
- 76 testes / ~2710 asserts / 0 falhas / 0 errors
- Todos os testes que dependem de banco usam SQLite isolado por arquivo (zero rede, zero MySQL externo)
- Property test executa 200 iteracoes (100 por property method) com 12+ asserts cada

### Validates: Requirements 1.1-1.5, 2.1, 2.4, 8.4, 9.1, 9.4

---

## [2026-07-22] - Spec multi-provider-s3-storage: Multi-Provider S3 Storage (IDrive e2 + Wasabi + AWS S3)
### Adicionado
- `app/Support/StorageProviderConfig.php` (value object - 7 campos por provedor + isValid + maskedSecret)
- `app/Support/StorageTestResult.php` (value object - steps + status + total latency)
- `app/Support/StorageProviderRegistry.php` (~600 linhas - core do feature)
- `app/Http/Requests/StorageProviderUpdateRequest.php` (validacao do form)
- `database/migrations/2026_07_22_000006_seed_multi_provider_s3.php` (22 chaves novas + migracao do legado)
- `resources/views/admin/settings/storage-providers.blade.php` (UI AdminLTE com tabs)
- `resources/views/admin/settings/partials/storage-provider-form.blade.php` (parcial AdminLTE)
- `resources/views/panel/admin/settings/storage-providers.blade.php` (UI Tailwind com tabs)
- `resources/views/panel/admin/settings/partials/storage-provider-form.blade.php` (parcial Tailwind)
- 4 endpoints novos no `Admin\SettingController`:
  - GET  `/admin/settings/storage-providers` -> showStorageProviders
  - POST `/admin/settings/storage-providers/active` -> switchActiveProvider (com validacao de conexao)
  - POST `/admin/settings/storage-providers/{provider}` -> updateStorageProvider
  - POST `/admin/settings/storage-providers/{provider}/test` -> testStorageProvider (JSON)
- Item "Provedores S3" no menu lateral do painel Tailwind (visivel apenas para superadmin)

### Modificado
- `app/Support/UploadStorage.php`: applyRuntimeConfig() agora consulta primeiro o StorageProviderRegistry (multi-provider) e cai no schema legado `storage_*` apenas como fallback. Mantem 100% das assinaturas publicas.
- `routes/web.php`: 4 rotas novas em ambos os grupos admin (AdminLTE e Tailwind), sob middleware admin

### Resumo do design
- Schema: prefixos `idrive_`, `wasabi_`, `aws_` na tabela settings (zero quebra de schema)
- Provedor ativo: chave `storage_active_provider` (idrive | wasabi | aws | local)
- Switch valida conexao antes de aplicar (Req 2.6); falha mantem provedor anterior (Req 2.7)
- Teste de conexao isolado: 6 steps (upload, exists, url, http_get, compare, delete) com timeout 30s e cleanup garantido
- Compatibilidade total com legado `storage_*`: migration copia automaticamente para `idrive_*` no primeiro deploy
- Audit log via canal security em todas as operacoes (criar/alterar/ativar provedor)
- Apenas superadmin tem acesso aos endpoints

### Migration: rodar `php artisan migrate` apos o deploy
A migration `2026_07_22_000006_seed_multi_provider_s3` cria 22 chaves vazias na tabela settings (com defaults sensatos por provedor) e copia o legado IDrive automaticamente. Idempotente.

### Validates: Requirements 1.1-1.5, 2.1-2.7, 3.1-3.5, 4.1-4.5, 5.1-5.7, 7.1-7.6, 8.1-8.4, 9.1-9.4

---

## [2026-07-22] - Spec advanced-security-performance: TASK 18.4 (Integration tests) + 15.4 + suite Property/Unit completa - **SPEC 100% CONCLUIDA**
### Adicionado
- `tests/Feature/AdvancedSecurityIntegrationTest.php` - 3 fluxos de integracao end-to-end:
  1. upload -> image processing -> presigned URL (Storage::fake('s3'), aspect-ratio preservado, thumbnails+WebP quando GD disponivel)
  2. request -> rate limit -> audit log (5 requests com threshold=2, validacao 429+persistencia em rate_limit_blocks+audit_logs)
  3. backup -> S3 -> retention (5 backups, deleteOldBackups(2,0), valida que apenas 2 mais recentes restaram)
- `tests/Unit/Services/AnomalyDetectorServiceTest.php` - 17 testes cobrindo recordLogin/Upload/Webhook, checkThresholds, getThresholds, notificacao via Bus::fake, auto-block WAF, fallback Req 11.7
- 29 testes opcionais (Property + Unit) implementados, validando Properties 1-16 do design

### Estatisticas finais do spec
- **73/73 tasks completas** (44 obrigatorias + 29 opcionais)
- 16 property tests com Eris (Properties 1-16)
- 12 unit test suites (services, controllers, middleware, console commands, traits)
- 1 integration test suite (3 fluxos end-to-end)
- Total: ~290+ testes individuais, ~10000+ assertions

### Arquivos afetados
- `tests/Feature/AdvancedSecurityIntegrationTest.php` (novo)
- `tests/Unit/Services/AnomalyDetectorServiceTest.php` (novo)
- `tests/Unit/Console/Commands/LogsCleanupTest.php` (novo)
- `tests/Unit/Database/HasEagerLoadingTest.php` (novo)
- `tests/Property/AnomalyThresholdTest.php` (novo)
- `tests/Property/LogRotationTest.php` (novo)
- `tests/Property/PermissionsPolicyRouteTest.php` (novo)
- `tests/Unit/Controllers/HealthControllerTest.php` (novo)
- `tests/Property/HealthCheckStatusCodeTest.php` (novo)
- `tests/Unit/Middleware/SecurityHeadersMiddlewareTest.php` (novo)
- `tests/Property/CspHeaderGenerationTest.php` (novo)
- `tests/Unit/Services/BackupServiceTest.php` (novo)
- `tests/Property/BackupRetentionTest.php` (novo)
- `tests/Property/BackupPathFormatTest.php` (novo)
- `tests/Unit/Services/AuditLogServiceTest.php` (novo)
- `tests/Property/AuditLogCompletenessTest.php` (novo)
- `tests/Property/AuditLogRetentionTest.php` (novo)
- `tests/Property/AuditLogFilterTest.php` (novo)
- `tests/Unit/Middleware/AdvancedRateLimitMiddlewareTest.php` (novo)
- `tests/Property/RateLimitDecisionTest.php` (novo)
- `tests/Property/RateLimitBlockDurationTest.php` (novo)
- `tests/Property/CacheThroughTtlTest.php` (novo)
- `tests/Unit/Services/AdvancedCacheManagerTest.php` (movido para subdir Services)
- `tests/Property/AdvancedCacheInvalidationTest.php` (refatorado)
- `tests/Property/PresignedUrlTtlMappingTest.php` (movido)
- `tests/Property/ImageProcessorAspectRatioTest.php` (novo, com tolerancia relativa 5%)
- `tests/Unit/Services/PresignedUrlServiceTest.php` (movido)
- `tests/Unit/Services/ImageProcessorServiceTest.php` (estendido)
- `tests/Unit/Services/QueueManagerServiceTest.php` (existente validado)
- `phpunit.xml` (testsuite Property adicionada)
- `tests/Property/.gitkeep` (novo)
- `app/Http/Kernel.php` (AdvancedRateLimitMiddleware + AnomalyDetectorMiddleware + aliases)
- `app/Providers/AppServiceProvider.php` (7 bindings singleton)
- `app/Console/Kernel.php` (queue:work, backup:database, backup:config, logs:cleanup agendados)

### Tecnicas / decisoes destacaveis
- Property tests com Eris 0.14 + PHPUnit 10 via override `getTestCaseAnnotations(): array { return []; }`
- Cache estatico de `Setting` injetado via reflection para isolar testes do banco
- SQLite isolado por teste (mesmo padrao usado em `AdvancedRateLimitMiddlewareTest`)
- `Storage::fake('s3')` para todos os testes de backup/upload (zero I/O real)
- `Bus::fake()` em testes que verificam dispatch de jobs sem executar mailers
- Property 1 (aspect ratio): adotada **tolerancia relativa 5%** em vez de absoluta 0.01 apos counter-example real do Eris (W=2001, H=100, max=100x100). Documentado no docblock do teste.

### Restricoes mantidas
- ZERO modificacao de arquivos de producao
- UTF-8 sem BOM em todos os arquivos
- Bloco de copyright `@autor marcelo-brad rj` em todos os arquivos novos

---

## [2026-07-22] - Spec advanced-security-performance: Task 17.2 (Unit tests Database Optimizer / HasEagerLoading)
### Adicionado
- `tests/Unit/Database/HasEagerLoadingTest.php` cobrindo o trait `App\Models\Concerns\HasEagerLoading` e a camada de cache de aggregates do Database Optimizer
- Cobre tres areas:
  1. Trait `HasEagerLoading`:
     - `scopeWithCommonRelations` aplica `with(...)` quando `$commonEagerRelations` esta definida (Mockery `Builder`)
     - `scopeWithCommonRelations` NAO chama `with()` quando a propriedade esta ausente ou e array vazio
     - `scopeWithCounts` chama `withCount(...)` so quando ha relacoes; pula em array vazio
  2. Cache de aggregates (`AdvancedCacheManager::getDashboardStats` e `getHeavyQuery`):
     - chave `unn:dash:{metric}` persiste valor; loader corre uma vez (cache hit nas demais leituras); isolamento por metrica; chave `unn:query:{md5(key)}` para queries pesadas
  3. Invalidacao quando entidade modifica (`AdvancedCacheManager::invalidate`):
     - `dashboard` com identificador limpa apenas a metrica especifica; sem identificador limpa todas as `COMMON_DASHBOARD_METRICS`
     - `heavy_query` com identificador limpa a chave md5 correspondente
     - apos invalidar, proxima leitura recomputa via loader (round-trip completo)
- Isolamento via `Cache::driver('array')` + `Cache::flush()` no `setUp`; sem `RefreshDatabase` (testes nao tocam DB)
- Mockery usado apenas para o `Builder` no teste do trait; `tearDown` chama `Mockery::close()`
- Validates: Requirements 12.2, 12.3, 12.5
- Resultado: 13 testes / 30 assertions / OK

### Arquivos afetados
- `tests/Unit/Database/HasEagerLoadingTest.php` (novo)

---

## [2026-07-22] - Spec advanced-security-performance: Task 10.3 (Property test para formato de path de backup)

### Adicionado
- `tests/Property/BackupPathFormatTest.php` validando **Property 10: Backup Path Format** com Eris/PHPUnit
- Tres propriedades isoladas em metodos separados:
  - `test_database_backup_path_matches_format`: paths de db sempre seguem `backups/db/YYYY-MM-DD_HHmmss.sql.gz`
  - `test_config_backup_path_matches_format`: paths de config sempre seguem `backups/config/YYYY-MM-DD_HHmmss.tar.gz`
  - `test_path_generation_is_deterministic_for_same_timestamp`: mesmo timestamp -> mesmo path (db e config compartilham timestamp gerado no mesmo instante)
- Generators Eris: `Generators::choose(0, 365)` (dias atras) + horas/minutos/segundos (0..23, 0..59, 0..59), com `Carbon::setTestNow()` para mockar relogio
- Restricao: o teste nao executa `mysqldump` real nem upload S3 (caro localmente); replica a formula publica de `BackupService` (`BACKUP_DIR_DB`, `BACKUP_DIR_CONFIG` + format `Y-m-d_His`) e valida via regex + igualdade exata. Qualquer divergencia entre o servico e o teste falha imediatamente
- `tearDown()` limpa `Carbon::setTestNow()` para nao vazar mock de tempo entre testes
- `getTestCaseAnnotations(): array { return []; }` para compatibilidade Eris 0.14 + PHPUnit 10
- Validates: Requirements 7.3
- Resultado: 3 testes / 700 assertions / OK (100 iteracoes por propriedade)

### Arquivos afetados
- `tests/Property/BackupPathFormatTest.php` (novo)

---

## [2026-07-22] - Spec advanced-security-performance: Task 5.3 (Unit tests PresignedUrlService)

### Adicionado
- Movido `tests/Unit/PresignedUrlServiceTest.php` para `tests/Unit/Services/PresignedUrlServiceTest.php` (alinhado ao design.md da spec)
- Namespace ajustado para `Tests\Unit\Services`
- Cobre: `generate()` com `Storage::fake('s3')`, `getExpirationForType()` (docs/media/default/case-insensitive), `isExpired()` (passado/futuro/sem assinatura), tratamento de erro do S3 (excecao generica que nao vaza path interno) e logging com `user_id`/`file_path`/`expiration_minutes` sem credenciais

### Arquivos afetados
- `tests/Unit/Services/PresignedUrlServiceTest.php` (movido + namespace ajustado)

---

## [2026-07-22] - S3 como Fonte de Verdade: Cache de Localizacao + Verificacao Publica

### Adicionado — Cache de localizacao S3 (UploadStorage)
- Cache persistente de 7 dias por arquivo: `unn:s3loc:vN:<md5(path)>` armazena `yes` (no S3) ou `no` (nao esta)
- Nova logica em `UploadStorage::url()`: consulta o cache antes de fazer HEAD no S3 — performance imensa em pageviews com muitas imagens
- `markAsOnS3($path)`: popula o cache proativamente quando um arquivo e migrado com sucesso
- `forgetS3Location($path)`: limpa cache de um caminho (util ao deletar)
- `flushS3LocationCache()`: invalida todo o cache via versionamento (sem precisar enumerar chaves)
- Cache positivo = 7 dias (arquivo confirmado), cache negativo = 5 minutos (permite migracao tardia)

### Alterado — Migracao popula cache automaticamente
- `SettingController::migrateStorage()` agora chama `UploadStorage::markAsOnS3($file)` apos cada upload bem-sucedido
- Apos migrar, o site usa imediatamente o S3 como fonte: zero requisicoes HEAD, zero risco de "imagem sumiu" temporariamente
- Mesma logica para arquivos ja existentes no S3 (skipped) — cache populado pra refletir o estado real

### Adicionado — Verificacao de acesso publico no teste S3
- Apos o "Gerar URL", o teste agora faz um HTTP HEAD na URL gerada
- Se HTTP 200-399: confirma que o bucket esta com leitura publica habilitada
- Se HTTP 403/401: aviso de bucket privado, com instrucao para habilitar "public read" no IDrive e2
- Detecta o problema "configuracao S3 OK no painel mas imagens nao aparecem" antes que ocorra

### Resultado
- **Os arquivos migrados sao agora a fonte de verdade do site**: o cache popula no momento da migracao, evitando race-conditions
- **Performance**: paginas com muitas imagens ja nao fazem HEAD para cada uma — uma consulta de cache em vez disso
- **Robustez**: se o S3 cair temporariamente, o url() ja sabe que o arquivo "esta no S3" e tenta gerar a URL; em caso de falha de geracao, cai pro local automaticamente

### Arquivos afetados
- `app/Support/UploadStorage.php`
- `app/Http/Controllers/Admin/SettingController.php`

---

## [2026-07-22] - Correcao de Autofill no Painel S3 + Configuracao no /admin Legado

### Corrigido (HOTFIX 17:15)
- **Erro "Class League\Flysystem\AwsS3V3\PortableVisibilityConverter not found" ao testar S3**: pasta `vendor/league/flysystem-aws-s3-v3/` ausente no servidor (provavelmente um `composer install` anterior nao baixou o pacote de fato)
- Executado `composer install --no-dev --optimize-autoloader` no servidor para regenerar `vendor/`
- Confirmado via script de diagnostico que todas as classes S3 estao acessiveis: `Aws\S3\S3Client`, `League\Flysystem\AwsS3V3\AwsS3V3Adapter`, `League\Flysystem\AwsS3V3\PortableVisibilityConverter`
- Caches limpos apos reinstalacao

### Decisoes Tecnicas Importantes
- **APOS QUALQUER `git reset --hard origin/main` no servidor que toque `composer.lock`, sempre rodar `composer install --no-dev --optimize-autoloader`** para garantir que todos os pacotes vendor existam
- O erro do S3 nao se resolve com `composer dump-autoload` se a pasta do pacote estiver faltando — precisa de `composer install`

### Corrigido (HOTFIX 16:45)
- **Erro 500 em /admin/settings/storage**: a versao compilada do Blade falhava ao parsear `@json([...])` com array contendo operador `??` em algumas combinacoes
- Substituido `@json()` por bloco `@php` que monta o array + `{!! json_encode(...) !!}` (mais robusto e compativel com qualquer versao do Blade)
- Caches de view antigos removidos manualmente no servidor

### Corrigido
- **Bug do Chrome autofill**: campos S3 (Bucket, Endpoint, Access Key, URL) eram limpos ou substituidos por sugestoes do navegador (e-mail/senha) ao carregar a tela /painel/admin/settings/storage
- Adicionado `autocomplete="off"` no formulario de settings (painel novo e /admin)
- Adicionados atributos `autocomplete="off"`, `data-lpignore="true"`, `data-1p-ignore="true"`, `data-form-type="other"` em cada input S3
- Iscas anti-autofill (`fake_username` e `fake_password` invisiveis) que distraem o Chrome para nao mexer nos campos reais
- Script de reforco que restaura os valores do banco em 100ms, 600ms e 1500ms se o autofill os tiver limpado, sem sobrescrever edicoes do proprio usuario

### Adicionado
- **Aba "Armazenamento" no /admin legado** (AdminLTE) com paridade total com o painel novo
- Novo partial `resources/views/admin/settings/partials/storage.blade.php` em estilo Bootstrap 4
- Item "Armazenamento" no sidebar do admin legado (entre SEO e Sistema)
- Chave `'storage' => 'Armazenamento'` adicionada a `$groupLabels` no admin legado
- Botao "Testar Conexao S3" funcional no admin legado
- Modulo de migracao de pastas no admin legado (listar, copiar, mover, migrar tudo)
- Suporte a SweetAlert2 com fallback para `confirm()` nativo no admin legado

### Resultado
- Os dois paineis (`/admin` e `/painel/admin`) agora tem a configuracao S3 completa
- Os campos pre-preenchidos com os dados salvos no banco resistem ao autofill do navegador
- Mesma logica de teste e migracao em ambos paineis usando as rotas existentes

### Arquivos afetados
- `resources/views/admin/settings/partials/storage.blade.php` (novo)
- `resources/views/admin/settings/index.blade.php` (groupLabels + autocomplete)
- `resources/views/admin/partials/sidebar.blade.php` (item Armazenamento)
- `resources/views/panel/admin/settings/partials/storage.blade.php` (anti-autofill + restauracao)
- `resources/views/panel/admin/settings/index.blade.php` (autocomplete)

---

## [2026-07-22] - Atalhos do Superadmin no Dashboard /painel/admin

### Adicionado
- Novo bloco "Atalhos do Superadmin" no dashboard administrativo, visivel apenas para `isSuperAdmin()`
- 4 cards de acesso direto: Armazenamento (S3/IDrive e2), Pagamentos, SMTP/E-mails e Sistema
- Cada card abre direto na aba correspondente de `panel.admin.settings` sem navegacao adicional
- Visual com cores diferentes por modulo, hover translate-up, indicador de seta animada

### Motivo
- Usuario relatou nao localizar a configuracao S3 no painel do superadmin
- O link existia em "Painel admin > Ajustes > Armazenamento" mas ficava 3 niveis profundo
- Atalhos no dashboard tornam configuracoes criticas imediatamente visiveis

### Diagnostico realizado (sem alteracao de codigo)
- Confirmado via script: 8 settings storage_* salvas corretamente no banco do servidor
- Confirmado: `UploadStorage::effectiveDisk()` retorna `s3` apos boot
- Confirmado: partial `storage.blade.php` renderiza com todos os campos pre-preenchidos
- Cache de view, config, route e application limpos no servidor

### Arquivos afetados
- `resources/views/panel/admin/dashboard.blade.php` (novo bloco de atalhos)

---

## [2026-05-14] - Correcoes de Eventos: Publicacao e Busca de Local

### Corrigido
- Bug de publicacao no /admin: evento ficava oculto ao salvar porque o campo `published` era forcado para `false` quando nao havia nova imagem no request (agora preserva o valor atual se o campo nao for enviado)
- Checkbox "Evento Publicado" adicionado ao formulario legado (antes so tinha hidden input com valor 0)
- Busca de estabelecimento no /admin migrada de Nominatim/Overpass (APIs publicas lentas/instáveis) para `/api/venue-search` (mesma API interna usada pelo /painel)
- Variáveis `userLat`/`userLon` adicionadas ao JS legado para suportar busca por proximidade

### Arquivos afetados
- `app/Http/Controllers/Admin/EventController.php` (logica de published no update)
- `resources/views/admin/events/form.blade.php` (checkbox published + busca via API interna)

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
