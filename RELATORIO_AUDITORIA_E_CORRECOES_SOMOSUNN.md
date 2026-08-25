# Relatório de Auditoria e Correções - SOMOS UNN

## Painéis Administrativos

### Atualização aplicada em 24/08/2026 - integridade visual das capas de eventos

- A restauração do banco recuperou caminhos de capas, mas sete arquivos binários não estavam presentes no storage nem nos backups de arquivos disponíveis.
- `Event::image_url` agora retorna somente arquivos acessíveis e reutiliza mídia válida da galeria quando a capa cadastrada está ausente.
- A listagem `/painel/admin/events` antecipa os relacionamentos de mídia, evitando N+1 durante o fallback; eventos sem arquivo recuperável exibem placeholder em vez de imagem quebrada.
- Os fluxos equivalentes do painel AdminLTE e as vitrines públicas de Eventos, Marketplace e Somos ÚNICAS também foram alinhados ao mesmo resolvedor de imagem.

### Atualização aplicada em 24/08/2026 - responsividade da listagem de revistas

- As listagens em `/admin/magazines` e `/painel/admin/magazines` deixaram de usar rolagem horizontal.
- A tabela usa layout fixo e quebra segura de títulos; categoria, visualizações, capa e visibilidade são ocultadas progressivamente conforme a largura.
- Destaque e ações permanecem acessíveis em todas as larguras, preservando o fluxo operacional dos dois painéis.

### Atualização aplicada em 24/08/2026 - feedback das ações de revistas

- `/admin/magazines` e `/painel/admin/magazines` compartilham confirmação SweetAlert2 para destaque e exclusão.
- Respostas do backend são apresentadas por Toastr; não há fallback para diálogos nativos do navegador.
- O fluxo AJAX trata token CSRF ausente, sessão expirada, resposta não JSON e falha de rede sem acessar propriedades nulas.
- A regra de negócio permanece no controller compartilhado `Admin\\MagazineController` e as rotas existentes foram preservadas.

### Atualização aplicada em 24/08/2026 - proteção do banco contra testes

- Foi identificado que o cache local de configuração podia prevalecer sobre `DB_DATABASE=testing` durante PHPUnit e direcionar `RefreshDatabase` ao banco incorreto.
- `APP_CONFIG_CACHE=bootstrap/cache/config-testing.php` isola a configuração usada pela suíte.
- `tests/CreatesApplication.php` interrompe a execução antes dos traits de banco se o ambiente não for `testing` ou se o database efetivo não for exatamente `testing`.
- O banco de produção foi restaurado pelo backup íntegro `2026-08-24_221343.sql.gz`; a validação confirmou usuários, conteúdos, configurações, revistas, depoimentos e tarefas agendadas.

### Atualização aplicada em 24/08/2026 - depoimentos em Somos Únicas

- A página pública `/somos-unicas` passou a consumir o mesmo modelo e scope `Testimonial::forSite()` usado na exposição pública dos depoimentos.
- A consulta carrega o usuário vinculado antecipadamente, limita o resultado a seis registros e prioriza destaques e registros recentes.
- Somente depoimentos aprovados e ativos são exibidos; a gestão e as permissões existentes em `/admin` e `/painel/admin` foram preservadas.
- Na ausência de registros públicos, são exibidos os mesmos depoimentos institucionais usados como fallback na home.
- Foi adicionada cobertura para impedir a exposição de depoimentos pendentes e inativos.

### Atualização aplicada em 24/08/2026 - apresentação dos destaques de revistas

- A home pública passou a reproduzir o card da banca de revistas: categoria, título, edição e data sobre a capa, selo de destaque e ação de abertura.
- A faixa informativa usa `magazines_overlay_opacity`, mantendo a configuração visual compartilhada com a página pública de revistas e com os dois fluxos administrativos.
- Não houve alteração de rotas, permissões, controllers, banco de dados ou comportamento dos painéis `/admin` e `/painel/admin`.
- Validações previstas: compilação Blade, UTF-8 sem BOM, codificação textual e `git diff --check`.

Data de atualização: 20/06/2026

### Atualização aplicada em 24/08/2026 - dropzone no tema escuro

- O componente compartilhado `x-unn-dropzone` ganhou tratamento específico para `.dark-mode`, usado pelo AdminLTE em `/admin`.
- Estados vazio, hover, foco, arrastar, arquivo selecionado e erro mantêm contraste compatível com os cards azul-grafite do painel.
- O seletor `.dark` do painel Tailwind foi preservado, mantendo a mesma implementação funcional nos dois painéis e diferenças visuais coerentes.
- Nenhuma rota, permissão, validação ou regra de upload foi alterada.

### Atualização aplicada em 24/08/2026 - destaque rápido de revistas

- `/admin/magazines` e `/painel/admin/magazines` exibem a mesma ação de estrela para alternar `is_featured` com salvamento AJAX imediato.
- A regra compartilhada permanece em `Admin\MagazineController::toggleFeatured()` e reutiliza `ensureCanManage()` para administradores, superadministradores, editores e proprietários autorizados.
- As interfaces mantêm seus estilos próprios, exibem carregamento durante a requisição e notificam o resultado por Toastify, com fallback de erro.
- Rotas POST com CSRF preservado: `admin.magazines.toggle-featured` e `panel.admin.magazines.toggle-featured`.
- Testes validam a existência, URI e método das rotas nos dois painéis.

### Atualização aplicada em 24/08/2026 - acesso e descrição das revistas

- A banca pública foi incluída no menu Comunidade para visitantes e no menu móvel correspondente.
- A sidebar do painel moderno exibe Revistas para todos os membros, enquanto os links administrativos continuam restritos pelas permissões existentes.
- O destaque público da home passou a apresentar título, edição/categoria e `short_description`, preservando o layout de capas.
- A consulta compartilhada usa `Magazine::visibleTo()`, impedindo a exposição na home de revistas configuradas como `members` para visitantes.
- Rotas existentes `magazines.*`, `admin.magazines.*` e `panel.admin.magazines.*` foram preservadas.

### Atualização aplicada em 24/08/2026 - sincronização oficial de revistas

- A regra de importação permanece única no comando `magazines:import-manchete` e atende às revistas exibidas e gerenciadas pelos dois painéis.
- O catálogo oficial é consultado diariamente pela central interna de cron, com descoberta de novas edições e publicação pública por padrão.
- Registros existentes são preservados; quando o PDF cadastrado desapareceu do storage, o comando recupera o arquivo e atualiza o mesmo registro.
- Títulos legados de edições numeradas são associados ao registro original e a visibilidade administrativa `public` ou `members` não é sobrescrita.
- Downloads usam streaming, arquivo temporário, limite de tamanho, validação `%PDF-`, troca atômica e lista restrita de domínios HTTPS oficiais.
- A tarefa `magazines:import-manchete` foi incluída na lista permitida e agendada para `30 2 * * *` sem alterar rotas ou permissões de `/admin` e `/painel/admin`.
- Validações: parser do catálogo, distinção entre edições Judiciário, proteção contra URL externa, sintaxe PHP, rotas, views, BOM e codificação.

### Atualização aplicada em 24/08/2026 - segurança, desempenho e PDFs

- **Dados pessoais em currículos:** acesso anônimo a `storage/resumes` bloqueado no Apache e no proxy Laravel; novos arquivos são privados e o download permanece restrito ao responsável autenticado pela vaga.

- O middleware compartilhado de segurança passou a proteger também a API e a impedir cache de respostas privadas em `/admin`, `/painel`, autenticação e rotas autenticadas.
- A criptografia de sessão foi ativada por padrão, com flags de cookie controladas pelo ambiente e valores seguros no arquivo de exemplo.
- Nenhuma rota ou permissão dos dois painéis foi removida; AdminLTE e Tailwind continuam usando a mesma camada global de proteção.
- Índices compostos aditivos aceleram listagens públicas de eventos, revistas e depoimentos sem modificar dados existentes.
- PDFs locais passaram a suportar requisições parciais, cache revalidável e streaming limitado ao intervalo solicitado.
- O leitor StPageFlip preserva o layout e a detecção de páginas duplas, mas analisa páginas em lotes paralelos para abrir mais rapidamente.
- Validações previstas: sintaxe PHP, Blade, rotas, testes de Range/cache, migration aditiva, BOM, encoding e `git diff --check`.

### Atualização aplicada em 24/08/2026 - publicação de depoimentos aprovados

- Preservadas as rotas `admin.testimonials.*` e `panel.admin.testimonials.*`, sem alteração de URLs ou permissões.
- A aprovação em `/painel/admin` agora define `is_active = true`, mantendo a mesma regra já aplicada em `/admin`.
- A regra pública compartilhada `Testimonial::forSite()` passou a ser usada na home, na página de planos e na API de depoimentos.
- A home passou a distinguir corretamente o campo `content` do model e o campo legado `text` do CMS, exibindo integralmente o que o membro escreveu.
- Interfaces AdminLTE e Tailwind permanecem visualmente distintas; model, estado de publicação e regra de consulta continuam compartilhados.
- Adicionados testes para depoimento de membro e para o formato legado configurado pelo CMS.

### Atualização aplicada em 20/06/2026 - ecossistema empresarial e patrocinadores

- Novos recursos globais adicionados em `/admin/companies`, `/admin/sponsors`, `/admin/sponsor-plans` e `/admin/sponsor-banners`.
- Equivalencia administrativa preservada no painel moderno com `/painel/admin/companies`, `/painel/admin/sponsors`, `/painel/admin/sponsor-plans` e `/painel/admin/sponsor-banners`.
- Nova area dedicada ao patrocinador em `/painel/patrocinador`, com dashboard, leads, financeiro, campanhas e relatorios.
- Regra de negocio compartilhada centralizada em `CompanyService`, `SponsorService`, `SponsorBannerService`, `SponsorLeadService`, `CrmScoreService` e `BusinessMatchService`.
- Rotas publicas preservadas e ampliadas com `GET /empresa/{slug}` sem alterar URLs legadas de parceiros ou loja.
- Permissoes granulares novas: `sponsor.dashboard`, `sponsor.leads`, `sponsor.billing`, `sponsor.reports`, `sponsor.events`, `sponsor.campaigns`, alem do conjunto administrativo de empresas, patrocinadores, planos e banners.
- Diferencas visuais mantidas: AdminLTE em `/admin`, Tailwind em `/painel/admin` e `/painel/patrocinador`, com a mesma camada de negocio por tras.
- Testes de cobertura adicionados para rotas, request de consentimento, CRM e business match, sem executar automacao nesta entrega.

### Atualização aplicada em 20/06/2026 - cópias ocultas de controle das vendas

- Criado o job idempotente `SendOrderControlCopyEmailJob`, integrado à fila de e-mails configurada no painel de cron.
- Todas as liquidações pelo `OrderSettlementService` e os fluxos legados de Mercado Pago, API e SumUp disparam a cópia de controle quando o pedido fica pago.
- As mensagens são enviadas exclusivamente por CCO ao Administrador principal e ao Super Administrador, sem destinatários visíveis e sem endereços fixos no código.
- `OrderControlCopyRecipientService` respeita `platform_admin_user_id` e `platform_owner_id`, usando fallback conservador para as contas com papel administrativo.
- O template personalizável `order_sale_control_copy` reúne dados operacionais e financeiros da venda sem expor payload bruto ou dados sensíveis de cartão.
- O metadata registra reivindicação, data de envio e quantidade de destinatários para evitar duplicidade em webhooks repetidos.
- A implementação é compartilhada entre os dois painéis e não exige rota, permissão ou migration nova.

### Atualização aplicada em 20/06/2026 - restrição da chave PIX de recebimentos

- A regra compartilhada `User::canManageReceivingPixKey()` passou a considerar exclusivamente os papéis `admin`, `superadmin` e o usuário configurado em `platform_marketing_user_id`.
- Níveis de gamificação não concedem acesso à chave PIX.
- Os formulários `/admin/users/*` e `/painel/admin/users/*` ocultam e desabilitam o campo para membros comuns, exibindo-o dinamicamente apenas quando o papel selecionado exige recebimentos.
- `Admin\UserRequest`, `Admin\ProfileController` e `Panel\ProfileController` exigem a chave para destinatários autorizados e descartam o campo quando enviado por membro comum.
- A definição do responsável de marketing nos dois painéis exige e grava uma chave PIX antes de ativar a responsabilidade.
- As interfaces AdminLTE e Tailwind permanecem distintas e compartilham a mesma regra do model e a mesma validação administrativa.
- Nenhuma rota, permissão existente ou estrutura de banco foi alterada.

### Atualização aplicada em 19/06/2026 - cadastro administrativo completo de membros

- `/admin/users/create` e `/painel/admin/users/create` passaram a oferecer cadastro completo com dados pessoais, profissionais, endereço, redes sociais, privacidade, plano e recursos individuais.
- Os dois painéis mantêm suas interfaces próprias, AdminLTE e Tailwind, mas compartilham `Admin\UserRequest` e `AdminUserService` para validação, criação, edição e estado do e-mail.
- Rotas adicionadas e preservadas: `admin.users.verify-email`, `panel.admin.users.verify-email` e todos os recursos existentes `admin.users.*` e `panel.admin.users.*`.
- Administradores podem criar o cadastro com `email_verified_at` preenchido ou validar manualmente um endereço pendente nas listagens.
- E-mail alterado somente permanece validado quando o administrador confirmar explicitamente essa opção; caso contrário, o template personalizado de verificação continua sendo enviado.
- Permissões de Super Admin continuam protegidas nos dois controllers; membros não recebem acesso administrativo.
- Validações previstas: rotas equivalentes, compilação Blade, sintaxe PHP, teste de criação completa, teste de validação manual, BOM e codificação textual.

### Atualização aplicada em 19/06/2026 - recuperação de checkout duplicado SumUp

- Confirmada em produção a resposta `DUPLICATED_CHECKOUT` para `ORDER-220`, cujo checkout remoto permanecia pendente e não estava registrado localmente.
- `Payment\SumUpService` passou a recuperar checkout remoto por referência, validando valor, moeda e status antes da reutilização.
- A correção fica centralizada para todos os fluxos que usam o serviço compartilhado, sem alterar pedidos, usuários ou registros financeiros existentes.
- Checkout incompatível ou encerrado não é reutilizado; nesse caso o serviço gera referência exclusiva para uma nova tentativa.

### Atualização aplicada em 19/06/2026 - restauração visual do DataTables em `/admin/cron`

- A implementação server-side existente estava publicada, mas a entrada pelo menu usava PJAX e não carregava os assets declarados em `@push('styles')` e `@push('scripts')`.
- O link do menu e o prefixo `/admin/cron` agora exigem carregamento completo da página.
- DataTables, Bootstrap 4 Responsive e idioma PT-BR foram internalizados em `public/assets/admin/datatables` para eliminar dependência de CDN, bloqueadores do navegador e o bloqueio HTTP reservado ao caminho `/vendor`.
- O `.htaccess` expõe exclusivamente o prefixo público `/assets/admin/datatables`, sem alterar a proteção dos diretórios privados da aplicação.
- Preservados o controller compartilhado de tarefas, o endpoint `admin.cron.data`, as permissões administrativas e todas as rotas `admin.cron.*`.
- O painel moderno `/painel/admin/cron` permanece funcional com sua interface Tailwind própria; a paginação DataTables é exclusiva da listagem AdminLTE em `/admin/cron`.

### Atualização aplicada em 19/06/2026 - cupons de evento e limite por usuário

- Confirmado em produção que o erro de cadastro era causado pela ausência da coluna `event_coupons.applies_to`; a migration pendente deve ser aplicada no deploy.
- Criada migration idempotente para `event_coupons.max_uses_per_user`, sem rollback destrutivo.
- A regra compartilhada em `EventCouponService` controla o limite por usuário para ingresso e expositor e preserva o padrão de uma utilização para cupom de expositor.
- `/admin/events/{event}/coupons` e `/painel/admin/events/{event}/coupons` continuam usando o mesmo request, model, service e controller-base, mantendo apenas as views AdminLTE e Tailwind distintas.
- Os dois formulários agora exibem `%` para desconto percentual, `R$` para valor fixo e campo de usos por usuário para escopos `exhibitor` e `both`.
- Rotas e permissões preservadas: `admin.events.coupons.*`, `panel.admin.events.coupons.*` e permissões granulares `admin.events.coupons.*`.

### Atualização aplicada em 19/06/2026 - elegibilidade de compra e revistas na home

- Criado o middleware compartilhado `purchase.eligible`, que exige autenticação, verificação do e-mail e aceite da versão atual dos documentos legais antes de abrir ou processar compras.
- Rotas protegidas: assinaturas, cursos, eventos, expositor, mentorias, marketplace e checkout SumUp autenticado da API.
- O cadastro público e o cadastro pela API exigem aceite expresso e usam `ValidEmailAddress` para validar formato e domínio do e-mail.
- Alterações de e-mail nos perfis `/admin` e `/painel`, bem como na gestão global de usuários dos dois painéis, anulam a verificação anterior e solicitam nova confirmação.
- Os dois painéis continuam usando controllers e models compartilhados para usuários; suas views e fluxos visuais permanecem distintos.
- A home pública passou a carregar revistas `published` e `is_featured` e exibir suas capas em carrossel abaixo do Ranking do networking.
- A rota pública de leitura permite acesso direto somente a revistas publicadas e destacadas; os demais registros continuam respeitando `public`, `members` e `interest`.
- Testes adicionados em `PurchaseEligibilityTest`, `ValidEmailAddressTest` e `FeaturedMagazineAccessTest`: 11 testes e 50 asserções aprovados para compra, e-mail e acesso direto às revistas destacadas.
- Rotas administrativas preservadas: `admin.users.*`, `panel.admin.users.*`, `admin.magazines.*` e `panel.admin.magazines.*`.

### Atualização aplicada em 19/06/2026 - revistas em português brasileiro

- A banca digital pública passou a formatar meses com localização `pt_BR`, eliminando abreviações em inglês como `Feb`.
- Os leitores DearFlip e StPageFlip receberam rótulos, mensagens, acessibilidade e controles em português brasileiro.
- As rotas administrativas preservadas são `admin.magazines.*` em `/admin/magazines` e `panel.admin.magazines.*` em `/painel/admin/magazines`.
- Os dois painéis continuam compartilhando `Admin\MagazineController` e o model `Magazine`, mantendo views distintas para AdminLTE e Tailwind.
- Formulários, listagens, estados, visibilidade, ações e contadores foram revisados nos dois painéis sem alterar regras de permissão.
- Validações previstas: compilação das views, sintaxe PHP, rotas dos dois painéis, UTF-8 sem BOM, verificação de mojibake e `git diff --check`.

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

### Atualizacao aplicada em 24/08/2026 - visibilidade das revistas

- Revistas publicadas sao acessiveis sem login, salvo quando o administrador seleciona explicitamente `members`.
- `/admin` e `/painel/admin` usam as mesmas duas opcoes de visibilidade: Publico e Todos os membros.
- A regra compartilhada permanece no model e no controller publico, sem duplicacao entre os paineis.

### Atualizacao aplicada em 24/08/2026 - retorno do leitor de revistas

- Os leitores DearFlip e PDF.js/StPageFlip voltam para a pagina anterior pelo historico do navegador.
- A rota `/revistas` funciona como fallback publico e continua respeitando a visibilidade calculada pelo `MagazineController`.
- A alteracao nao modifica rotas administrativas nem permissoes dos dois paineis.

### Atualizacao aplicada em 24/08/2026 - sincronizacao de depoimentos

- No painel `/admin`, aprovacao e recusa por AJAX atualizam imediatamente a tabela e os indicadores.
- Os indicadores usam agregacao no banco e permanecem corretos com filtros e paginacao.
- O painel `/painel/admin` mantem o fluxo proprio com redirecionamento apos a moderacao, compartilhando o mesmo model e as mesmas regras publicas.
- `/painel/admin`: usa views `panel.admin.*`, layout moderno, sidebar/navegação do painel e componentes Tailwind.
- Views de um painel não devem ser reutilizadas diretamente no outro sem validação de layout, seções, scripts, sidebar e compatibilidade visual.
