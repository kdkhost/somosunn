# UNN â€” Plataforma de Networking

## Novidades Recentes (mar/2026)

- **Reconciliacao historica de pontos:** O comando `points:reconcile-legacy-members` e as migrations de marco agora recalculam pontos antigos com base em registros reais de **cadastro, perfil completo, primeiro curso, mentor, curso concluido, certificado, evento, mentoria e avaliacoes**.
- **Programa de indicacao com rastreio completo:** O painel de indicacoes passou a mostrar **cliques, visitas unicas, pageviews, cadastros atribuidos, checkouts iniciados, compras confirmadas, receita rastreada, copias, compartilhamentos e recompartilhamentos** do link de afiliado.
- **Afiliados com rastreio detalhado em tempo real:** O modulo de indicacoes agora tambem exibe **tabela detalhada por clique/visita, URL de origem exata, landing page, dispositivo, navegador, cidade/pais, funil por canal, exportacao CSV e visao global no admin em `/painel/admin/indicacoes`**, com atualizacao automatica no painel do afiliado.
- **Kit promocional e API REST para afiliados:** O afiliado agora recebe **materiais prontos para compartilhamento, blocos de landing page, ofertas recomendadas e endpoints autenticados em `/api/v1/affiliate/*`** para montar site, painel ou microsite externo com o proprio link de indicacao.
- **Painel analitico de indicacoes:** O modulo de afiliados agora inclui **graficos por dia e por canal** para acompanhar performance do link, aquisicao por origem e distribuicao dos compartilhamentos.
- **Planos pagos com perfil comercial embutido:** Todos os planos pagos passaram a incluir permissoes de **instrutor/vendedor**, e o fluxo de resgates por pontos foi preparado para operar no mesmo ambiente entre admin e vendedor com fornecedor rastreavel.
- **Painel novo estabilizado:** Foram corrigidos fluxos e layouts de **eventos, mentorias, faturas, regras de pontos, imagens administrativas e editores ricos**, mantendo o padrao visual do painel moderno.
- **Upload e navegacao padronizados no painel:** Uploads do painel agora usam experiencia unificada com **arrastar e soltar, preview, progresso e tempo restante**, alem de sidebar com submenu e paginacao nas listagens extensas.
- **Sistema de gamificacao completo (Pontos):** Todas as 21 regras de pontos ativas agora sao disparadas automaticamente pelas acoes do usuario. Veja a secao de funcionalidades para detalhes.
- **Pagina "Meus Pontos" para membros:** O membro visualiza seu saldo, posicao no ranking, pontos do mes, top 10 da plataforma e historico completo de pontos ganhos (acessivel em `/painel/meus-pontos` e no sidebar).
- **Programa de Indicacao ("Indique e Ganhe"):** Cada membro possui um link de referral unico. Ao indicar alguem que se cadastra, o indicador recebe pontos automaticamente.
- **Bonus de aniversario:** Sistema agendado concede pontos no aniversario do membro (executa diariamente as 01h).
- **Ranking Top 10:** Toda semana (domingo a meia-noite), os 10 membros com mais pontos recebem bonus automatico.
- **Streak de Login:** Logins consecutivos por 7 ou 30 dias concedem bonus progressivos.

## Novidades Recentes (fev/2026)

- **Menu mobile 100% responsivo:** NavegaÃ§Ã£o aprimorada em smartphones, com abertura/fechamento suave e acessibilidade total.
- **Dashboard com mÃ©tricas em tempo real:** Contadores de visitas e vendas atualizados automaticamente via websockets (Laravel Echo + Pusher).
- **CMS integrado ao frontend:** Conteudos do CMS (Home, Sobre, Manifesto, Valores e Rodape/Redes Sociais) agora refletem diretamente nas paginas publicas, com fallback para Settings quando vazio.
- **Sistema de Parceiros e Cupons:** Nova plataforma de parcerias com carrossel premium no Marketplace, Ã¡rea de cupons exclusivos para membros adimplentes e **painel de autogestÃ£o** para que parceiros cadastrem seus prÃ³prios benefÃ­cios.
- **Widgets customizados por perfil:** Cada membro vÃª mÃ©tricas e atalhos conforme seu plano; admin/superadmin tÃªm visÃ£o global consolidada.
- **Gerenciamento de tarefas agendadas (cron) pelo painel:** Superadmin pode criar, ativar/desativar, rodar e monitorar tarefas agendadas sem depender do cron da hospedagem.
- **Logs detalhados de execuÃ§Ãµes:** HistÃ³rico de execuÃ§Ãµes e falhas disponÃ­vel para cada tarefa agendada.

Veja instruÃ§Ãµes detalhadas abaixo para uso dessas funcionalidades.

## Melhorias de Dashboard (fev/2026)

- Dashboards de membro e admin totalmente refatoradas com widgets dinÃ¢micos Blade.
- ExibiÃ§Ã£o de mÃ©tricas, vendas, comunidade, ranking, etc. conforme permissÃµes e plano do usuÃ¡rio.
- Estrutura pronta para integraÃ§Ã£o com dados em tempo real (websockets/Redis) e cache otimizado.
- Novos componentes Blade em `resources/views/components/widgets/` para fÃ¡cil expansÃ£o e manutenÃ§Ã£o.
- CÃ³digo preparado para uso de Redis (requer extensÃ£o fileinfo habilitada no PHP para produÃ§Ã£o).

> **AtenÃ§Ã£o:** Para usar cache Redis, habilite a extensÃ£o `fileinfo` no PHP e instale o Predis (`composer require predis/predis`).

# VisÃ£o Geral do Sistema

O UNN Ã© uma plataforma completa de networking, cursos e mentorias, desenvolvida em Laravel 10.

## Funcionalidades Principais

### 1. GestÃ£o de ConteÃºdo (LMS)
- **Cursos:** Aulas em vÃ­deo, anexos, controle de progresso e certificaÃ§Ã£o.
- **Mentorias:** Agendamento, controle de vagas e venda de sessÃµes.
- **Eventos:** CalendÃ¡rio interativo, venda de ingressos e check-in.

### 2. Networking e Comunidade
- **Feed Social:** PublicaÃ§Ãµes, curtidas e comentÃ¡rios (estilo rede social).
- **ConexÃµes:** Sistema de solicitaÃ§Ã£o/aceite de conexÃµes entre membros.
- **Chat:** Mensagens em tempo real (polling otimizado para cPanel).
- **Ranking:** GamificaÃ§Ã£o baseada em avaliaÃ§Ãµes e interaÃ§Ãµes.

### 3. Administrativo
- **NÃ­veis de Acesso:** Granularidade total (SuperAdmin, Admin, Editor, Instrutor, Membro).
- **RelatÃ³rios:** Dashboards financeiros, de vendas e de engajamento.
- **ConfiguraÃ§Ãµes:** Controle total da plataforma via painel (cores, imagens, textos, integraÃ§Ãµes).

### 4. GamificaÃ§Ã£o (Sistema de Pontos)
- **21 regras ativas:** Pontos concedidos por login, streak, publicaÃ§Ã£o, comentÃ¡rio, curtida, compartilhamento, aula, curso, certificado, evento, mentoria, avaliaÃ§Ã£o, indicaÃ§Ã£o, aniversÃ¡rio e ranking.
- **NÃ£o-repetiÃ§Ã£o automÃ¡tica:** O `PointsService` bloqueia automaticamente aÃ§Ãµes Ãºnicas (ex: completar perfil, primeiro curso) sem lÃ³gica duplicada nos controllers.
- **Limite diÃ¡rio:** Regras com `max_daily` (ex: posts, comentÃ¡rios) sÃ£o respeitadas automaticamente.
- **Streak de login:** 7 e 30 dias consecutivos concedem bÃ´nus progressivos.
- **Programa de indicaÃ§Ã£o:** Link de referral Ãºnico por membro; indicador pontua ao novo usuÃ¡rio se cadastrar.
- **BÃ´nus de aniversÃ¡rio:** Concedido automaticamente 1x por ano (comando `points:award-birthday-bonus`).
- **Ranking Top 10:** PremiaÃ§Ã£o semanal automÃ¡tica (comando `points:award-top-ranking`).
- **Regras configurÃ¡veis** pelo painel admin em `/admin/points-rules`.
- **PÃ¡gina "Meus Pontos"** em `/painel/meus-pontos`: saldo, posiÃ§Ã£o no ranking, pontos do mÃªs, top 10 e histÃ³rico completo.

### 5. Novidades 2026
- **Menu Mobile Responsivo:** Menu principal funcional em todas as telas e dispositivos.
- **Dashboard DinÃ¢mica:** Widgets e mÃ©tricas em tempo real, segmentados por perfil.
- **Cron Interno:** Gerencie tarefas agendadas direto pelo painel admin (menu "Cron").
- **Logs de ExecuÃ§Ã£o:** Visualize histÃ³rico de execuÃ§Ãµes e falhas de cada tarefa agendada.


## InstalaÃ§Ã£o e Deploy

## UTF-8 sem BOM (OBRIGATÃ“RIO)

- Este projeto usa **UTF-8 sem BOM** em TODOS os arquivos de texto (PHP, Blade, JS, CSS, JSON, MD, etc.).
- Nunca salve arquivos como **UTF-8 com BOM** (bytes `EF BB BF` no inÃ­cio do arquivo), pois causa erros de acentuaÃ§Ã£o/pontuaÃ§Ã£o.
- Antes de commitar, rode: `php tools/check-no-bom.php`.
- Para bloquear commits com BOM automaticamente, configure uma vez: `git config core.hooksPath .githooks`.

### Requisitos
- PHP 8.1+
- MySQL 5.7+ / Mariadb
- Composer 2+

### InstruÃ§Ãµes RÃ¡pidas (cPanel/Compartilhado)
1. Configure o banco de dados e o arquivo `.env`.
2. Execute as migraÃ§Ãµes: `php artisan migrate --seed`.
3. Configure o cron job para rodar `php artisan schedule:run` a cada minuto.
4. Para filas, use QUEUE_CONNECTION=database e configure o worker.

> **Importante (mar/2026):** As entregas de rastreio de afiliados, reconcilia??o hist?rica de pontos e expans?o comercial dependem de migrations recentes. Em toda publica??o, garanta a execu??o de `php artisan migrate`.

#### Para usar o cron interno (painel):
- Acesse o menu **Admin > Cron**
- Cadastre comandos Artisan (ex: `schedule:run`, `queue:work`, etc.) e defina a frequÃªncia (cron/preset)
- Ative/desative tarefas conforme necessÃ¡rio
- Execute manualmente e visualize logs de cada execuÃ§Ã£o

#### Para dashboards em tempo real:
- Certifique-se de que as variÃ¡veis PUSHER estÃ£o configuradas no `.env`
- O painel usarÃ¡ websockets para atualizar contadores automaticamente

#### Menu mobile:
- O menu principal estÃ¡ 100% funcional em smartphones e tablets, com navegaÃ§Ã£o fluida e acessÃ­vel

### Webhooks de Pagamento
Configure as URLs no seu gateway (MercadoPago/PagSeguro):
- `YOUR_DOMAIN/api/v1/webhooks/mercadopago`
- `YOUR_DOMAIN/api/v1/webhooks/pagseguro`

### SMTP e Emails
Configure as credenciais no painel admin em **ConfiguraÃ§Ãµes > SMTP**. Use a ferramenta de "Teste de Envio" para validar.

### 02/03/2026 (Tarde) - Compartilhamento com Aprovacao
- **Feature share-with-approval na Comunidade:**
  - Ao compartilhar um post com um membro, e criada uma **solicitacao pendente** (ShareRequest) em vez de publicar diretamente.
  - O destinatario recebe notificacao e ve as solicitacoes no sidebar do feed e em /compartilhamentos/pendentes.
  - **Aprovacao:** cria o post na timeline do destinatario com o conteudo original e mensagem opcional.
  - **Rejeicao/Expiracao:** solicitacao e marcada como rejeitada ou expirada (7 dias); remetente notificado em ambos os casos.
  - Acoes de aprovar/rejeitar via **AJAX com SweetAlert2** no feed, sem refresh de pagina.
  - Comando agendado share-requests:expire (diario 02:00) expira solicitacoes antigas em lote.
  - Pagina dedicada /compartilhamentos/pendentes com listagem paginada das solicitacoes recebidas.

### 02/03/2026 (Tarde) - Compartilhamento com Aprovacao
- **Feature share-with-approval:**
  - Compartilhamento entre membros agora requer aprovacao do destinatario antes de publicar na timeline.
  - **ShareRequest:** criada com status pendente, expira em 7 dias.
  - Aprovacao via AJAX (SweetAlert2) diretamente no sidebar do feed.
  - Rejeicao notifica o remetente; aprovacao cria o post e tambem notifica.
  - Inbox dedicado: /compartilhamentos/pendentes.
  - Comando share-requests:expire (diario 02:00) expira solicitacoes em lote.

### 06/03/2026 - Reconciliacao Historica, Afiliados e Consolidacao do Painel
- **Reconciliacao historica de pontos (fase 2):**
  - Expansao do fluxo retroativo para considerar registros reais de **cursos concluidos, certificados emitidos, inscricoes em eventos, mentorias e avaliacoes**.
  - Novo processamento seguro e idempotente via `LegacyMemberPointsBackfillService` e `points:reconcile-legacy-members`.
  - Migration adicional para aplicar a segunda etapa da reconciliacao em producao durante o deploy.
- **Programa de indicacao / afiliados com rastreio completo:**
  - Novas tabelas de tracking para visitas e eventos do link de referral.
  - Atribuicao persistente desde o clique ate o cadastro, inicio de checkout e compra confirmada.
  - Painel `/painel/indicacoes` com metricas de **cliques, visitas, cadastros, checkouts, compras, receita, copias, compartilhamentos e recompartilhamentos**.
  - Novos graficos no painel para leitura **diaria (14 dias)** e por **canal/origem**, facilitando acompanhar evolucao, conversao e canais de compartilhamento.
  - Expansao do painel com **log detalhado por clique/visita**, origem exata, landing page, dispositivo, navegador, cidade/pais, **funil por canal**, exportacao CSV e atualizacao automatica sem refresh.
  - Nova visao global em `/painel/admin/indicacoes` para ranking de afiliados e acompanhamento consolidado de toda a plataforma.
  - Novo **kit promocional do afiliado** com textos prontos, CTA, ativos da marca, ofertas recomendadas e estrutura de landing page pronta para divulgacao.
  - Nova **API REST autenticada** em `/api/v1/affiliate/overview`, `/materials`, `/offers`, `/landing-page` e `/analytics` para o membro montar site, painel ou microsite externo usando o proprio token.
  - Rastreamento de compartilhamento tambem nos atalhos rapidos do perfil e da area de indicacoes.
- **Fluxo comercial em planos pagos e resgates:**
  - Planos pagos passam a carregar permissoes de instrutor e vendedor automaticamente.
  - Planos gratuitos deixaram de liberar, em tempo de execucao, as permissoes comerciais de instrutor e vendedor, mesmo em cadastros antigos.
  - Resgates por pontos foram preparados para operacao compartilhada entre admin e vendedor, com fornecedor identificado e trilha de entrega/rastreio.
- **Eventos e gamificacao com regras mais restritas:**
  - A edicao de eventos no painel passou a exigir imagem de capa, ganhou acoes rapidas de ativar/desativar e excluir, e a aba de certificado so fica habilitada quando o certificado do evento estiver ativo.
  - Membros em plano gratuito deixaram de receber pontos recorrentes em regras repetiveis, preservando apenas pontuacoes unicas quando aplicavel.
- **Padronizacao e correcoes do painel novo:**
  - Ajustes em uploads de imagens administrativas, FullCalendar de eventos, telas de mentorias/faturas, lista de regras de pontos e inputs ricos com Summernote.
  - Sidebar recolhivel, uploads avancados com FilePond e paginacao aplicada em listagens extensas para reduzir paginas longas.

---
? 2026 UNN Networking. Todos os direitos reservados.
