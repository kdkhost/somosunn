# UNN — Plataforma de Networking

- **Padronização e Customização Visual de E-mails (24/03):** Implementada a unificação visual de todos os e-mails transacionais do sistema através de um layout mestre dinâmico. Foi introduzida uma nova seção de "Design do E-mail" em ambos os painéis administrativos (Moderno e AdminLTE 3.2), permitindo a customização do cabeçalho com fundos sólidos ou degradês lineares de até 3 cores, com sistema de pré-visualização em tempo real. O template de contato foi refatorado para total conformidade com o padrão global de branding da plataforma.
- **Sincronização de Configurações e Permissões Hierárquicas (23/03):** Implementada a paridade total entre os painéis (AdminLTE e Moderno) com a adição do campo de expiração do PIX no painel legado. Além disso, o sistema de gestão de usuários foi aprimorado para identificar e exibir automaticamente as permissões concedidas pelo plano ativo do membro, garantindo que o Superadmin tenha uma visão clara do controle de acessos individuais e herança de planos.
- **Gestão de Split e Chaves PIX (23/03):** Implementada a interface para controle de porcentagens de split e configuração de chaves PIX de destino (Plataforma e Tráfego Pago) em ambos os painéis administrativos. A lógica foi integrada ao Webhook de pagamentos para automatizar o registro de repasses com as chaves corretas em cada pedido, garantindo transparência e agilidade financeira.
- **Navegação Mobile PWA e Bottom Nav (23/03):** Introduzida uma nova experiência de "App Nativo" para dispositivos móveis. O sistema agora conta com uma barra de navegação inferior (Bottom Nav) com efeitos de desfoque e indicadores de estado ativo. Além disso, o menu lateral (drawer) foi corrigido para destacar visualmente a página atual, proporcionando uma navegação intuitiva e fluida no smartphone.
- **Verificação de E-mail Obrigatória (23/03):** Implementado o fluxo completo de verificação de e-mail para novos usuários. Inclui bloqueio de funcionalidades via middleware `verified`, página de aviso premium (`verify-email.blade.php`), banner global de notificação e opção de reenvio direto pelo perfil e dashboard. Garante a autenticidade da base de membros e segurança da plataforma.

- **Reversão para Layout Boxed (23/03):** O layout da plataforma foi fixado no modo "Boxed" (centralizado com 1280px) em resposta ao feedback do usuário e do cliente. A opção de alternância para modo "Full" foi removida do painel administrativo para simplificar a gestão e manter a consistência visual em todas as páginas (Home, Premium, Ranking, etc.). A classe `.unn-container` agora garante o alinhamento centralizado de forma global e nativa.
- **Calendário Interativo no Novo Painel (23/03):** Habilitada a funcionalidade de drag-and-drop e redimensionamento visual (resize) para os eventos no FullCalendar. As alterações são sincronizadas automaticamente via AJAX e protegidas por camadas de permissão, garantindo que apenas administradores ou instrutores autorizados possam reorganizar a agenda. A integração conta com feedback instantâneo via SweetAlert2.
- **Logomarca Dinâmica Somos Únicas e Gestão de Imagens (23/03):** Implementada a funcionalidade de troca automática de logotipo no menu de navegação para a seção "Somos Únicas". A configuração foi adicionada a ambos os painéis administrativos (Novo e AdminLTE 3.2). Além disso, o campo "Logo Site (Frontend)" foi exposto em ambos os painéis para permitir o controle total da identidade visual principal, que agora alterna dinamicamente conforme o contexto da página.
- **Sincronização de Credenciais Mercado Pago (23/03):** Expandida a correção de mismatch de `publicKey` para todos os fluxos de checkout: **Cursos, Eventos e Mentorias**. A lógica de detecção de conta do vendedor vs plataforma agora é unificada, garantindo que o gateway carregue corretamente independente do tipo de produto.
- **Correção da Carteira Mercado Pago (23/03):** Resolvido problema onde a opção de pagamento via Conta Mercado Pago (Wallet) não aparecia no checkout. Foram adicionadas chaves de configuração redundantes (`wallet`, `account_money`) para garantir a ativação do método no SDK conforme a preferência do administrador.
- **Correção de Sintaxe na Página de Indicação (22/03):** Resolvido o erro "unexpected end of file" na visualização do programa de indicações (`index.blade.php`), causado por uma diretiva `@if` não fechada.
- **Melhoria de Diagnóstico no Checkout (22/03):** Adicionado log de erro detalhado no console do navegador para falhas no Mercado Pago Brick, permitindo identificar rapidamente problemas de configuração de conta ou comunicação com a API.
- **Correção de Rotas de Checkout (22/03):** Resolvidos erros de "rota não definida" no fluxo de compra de cursos. Foram adicionadas as rotas faltantes (`checkout.process`, `checkout.success`, `checkout.pending`, `checkout.failure`) e implementados os métodos de tratamento correspondentes no `CheckoutController`.
- **Validação de CPF no Mercado Pago (22/03):** Implementada trava de segurança no `MercadoPagoService` para garantir que o CPF enviado possua exatamente 11 dígitos, evitando erros de processamento (código 2067).
- **Consolidação de Gateways de Pagamento (11/03):** Removidas as integrações com **SumUp** e **PagSeguro**, consolidando o sistema para utilizar exclusivamente o **MercadoPago**. Esta mudança simplifica a manutenção, elimina códigos obsoletos em diversos controladores (Checkout, Mentorias, Eventos) e otimiza a experiência de configuração para o administrador.
- **Limpeza de Webhooks e Serviços (11/03):** Excluídas rotas e métodos de webhook redundantes, além de arquivos de serviço órfãos dos gateways removidos, reduzindo a dívida técnica do projeto.

- **Scanner Universal com Antifraude (GPS 10m):** O Scanner Universal (`/panel/admin/quick-scanner`) agora conta com travas de geolocalização rigorosas (precisão de 10 metros), garantindo que o check-in ocorra apenas no local exato do evento. O acesso foi expandido para **Instrutores e Mentores**, que agora podem validar seus próprios eventos diretamente pelo celular, com feedback sonoro e visual instantâneo para agilizar filas e evitar fraudes de duplicidade ou localização.
- **Galeria de Eventos e Palestras:** A página pública e do painel (`/galeria`) exibe as fotos em um layout `masonry` inteligente e responsivo, adaptando naturalmente as imagens de todos os tamanhos e proporções ao tamanho real da tela. Sem restrição de cortes, a galeria entrega uma experiência autêntica, animações dinâmicas de passagem e Lightbox fluído.
- **Remoção de Marca d'água na Galeria:** A funcionalidade automática de imposição de marca d'água foi descontinuada para a Galeria, permitindo que as fotos dos eventos sejam enviadas e exibidas na resolução e qualidade originais, proporcionando uma vitrine fotográfica mais orgânica e leve.
- **Otimizações de Performance no Dashboard:** Realizada auditoria e refatoração do `DashboardMetricsService` para eliminar consultas redundantes. Múltiplas queries SQL em loops foram substituídas por agregação direta no banco de dados (`groupBy`), reduzindo o tempo de carregamento e o consumo de recursos do servidor.
- **Página de Planos e Animações:** A rota `/premium` foi renomeada para `/planos` para melhor clareza semântica. A página recebeu animações dinâmicas de entrada e efeitos de hover nos cards de benefícios e depoimentos, alinhando-se à identidade visual da "Somos Únicas".
- **Acesso Dual à Listagem de Eventos:** Implementada a funcionalidade de listagem de eventos no painel legado (AdminLTE 3.2), permitindo que SuperAdmins e administradores gerenciem eventos de forma tabular em ambos os painéis (moderno e legado). O menu lateral do AdminLTE foi atualizado para manter o contexto do usuário, e a nova listagem conta com busca, paginação e ações rápidas integradas.
- **Correção do Preloader do Sistema:** O preloader foi ajustado para ser exibido exclusivamente no site público, conforme o comportamento original, removendo exibições indesejadas dentro do painel administrativo que causavam sobreposição visual na navegação entre menus.
- **Controle de Visibilidade "Somos Únicas":** Administradores agora podem definir se Cursos, Eventos e Mentorias são exibidos em "Ambas as plataformas", "Apenas UNN" ou "Exclusivo Somos Únicas". O frontend foi atualizado para filtrar o conteúdo automaticamente com base nessa configuração.
- **Unificação de E-mails do Sistema:** Todos os templates de e-mail foram refatorados para utilizar o layout padrão da UNN de forma automática, eliminando aninhamentos e inconsistências visuais. O editor Summernote agora sincroniza corretamente antes de salvar, e a funcionalidade de "Enviar Teste" no painel administrativo foi corrigida.
- **Flexibilidade de Variáveis e Gestão de Vagas:** Implementado suporte a chaves simples `{var}` e duplas `{{var}}` nos templates, com inclusão de dados reais de exemplo para testes. O editor de e-mail agora conta com botões rápidos para inserção de variáveis de candidatura (candidato, vaga, links de gestão), facilitando a personalização das notificações de emprego.
- **Integração CMS "Somos Únicas":** As páginas de conteúdo institucional da "Somos Únicas" (Home e Sobre) foram integradas ao CMS administrativo. Agora podem ser editadas de forma facilitada através de formulários padronizados com suporte a imagens via `upload-box` e textos ricos via `Summernote`, eliminando a necessidade de edição direta em JSON.
- **Melhoria no Editor de Eventos:** O editor Summernote foi corrigido para inicializar corretamente dentro do modal do calendário de eventos, garantindo que descrições ricas possam ser editadas sem erros de carregamento.
- **Página "Sobre a Comunidade":** Foi criada uma nova landing page dedicada e otimizada (em `/somos-unicas/sobre`) para explicar a missão e os benefícios da comunidade feminina "Somos Únicas", acessível via dropdown no menu de navegação.
- **Área Temática "Somos Únicas":** Nova seção pública e exclusiva para mulheres empreendedoras, agregando cursos, palestras e mentorias com a temática rosa (`/somos-unicas`). O gerenciamento é feito no painel Admin, com um checklist marcante para definir o conteúdo que pertence à área.
- **Página Pública de Ranking:** A comunidade ganhou uma página oficial (`/ranking`) para o Ranking de Membros, reutilizando o layout de pódio da página inicial (Top 3) e estendendo a listagem para todo o público, além de corrigir o redirecionamento para o perfil dos membros.- **Reconciliacao historica de pontos:** O comando `points:reconcile-legacy-members` e as migrations de marco agora recalculam pontos antigos com base em registros reais de **cadastro, perfil completo, primeiro curso, mentor, curso concluido, certificado, evento, mentoria e avaliacoes**.
- **Programa de indicacao com rastreio completo:** O painel de indicacoes passou a mostrar **cliques, visitas unicas, pageviews, cadastros atribuidos, checkouts iniciados, compras confirmadas, receita rastreada, copias, compartilhamentos e recompartilhamentos** do link de afiliado.
- **Afiliados com rastreio detalhado em tempo real:** O modulo de indicacoes agora tambem exibe **tabela detalhada por clique/visita, URL de origem exata, landing page, dispositivo, navegador, cidade/pais, funil por canal, exportacao CSV e visao global no admin em `/painel/admin/indicacoes`**, com atualizacao automatica no painel do afiliado.
- **Dashboards com visitas segmentadas em tempo real:** As dashboards de membro, admin e superadmin agora mostram **contadores de visitas por produto, ranking dos itens mais acessados, distribuicao por tipo e atualizacao automatica por websocket/polling**, sem recarregar a pagina.
- **Kit promocional e API REST para afiliados:** O afiliado agora recebe **materiais prontos para compartilhamento, blocos de landing page, ofertas recomendadas e endpoints autenticados em `/api/v1/affiliate/*`** para montar site, painel ou microsite externo com o proprio link de indicacao.
- **Central externa do afiliado com embeds, criativos e sandbox:** A area `/painel/indicacoes` agora tambem entrega **widgets em iframe/HTML responsivo, criativos em tamanhos especificos, playground da API e ticket de acesso ao sandbox com motivo, IP e dominio** para homologacao controlada.
- **Tokens pessoais da API no painel do afiliado:** A area `/painel/indicacoes` agora permite **gerar token, copiar na hora, renomear por dispositivo, revogar acesso e acompanhar ultimo uso/IP** sem depender de chamada manual da API.
- **Gestao de afiliados tambem no AdminLTE legado:** O superadmin agora acompanha o programa de indicacoes e gerencia seus **tokens pessoais da API** tambem em `/admin/indicacoes`, mantendo link proprio, indicados e rastreio global no painel antigo.
- **Painel analitico de indicacoes:** O modulo de afiliados agora inclui **graficos por dia e por canal** para acompanhar performance do link, aquisicao por origem e distribuicao dos compartilhamentos.
- **Planos pagos com perfil comercial embutido:** Todos os planos pagos passaram a incluir permissoes de **instrutor/vendedor**, e o fluxo de resgates por pontos foi preparado para operar no mesmo ambiente entre admin e vendedor com fornecedor rastreavel.
- **Painel novo estabilizado:** Foram corrigidos fluxos e layouts de **eventos, mentorias, faturas, regras de pontos, imagens administrativas e editores ricos**, mantendo o padrao visual do painel moderno.
- **Upload e navegacao padronizados no painel:** Uploads do painel agora usam experiencia unificada com **arrastar e soltar, preview, progresso e tempo restante**, alem de sidebar com submenu e paginacao nas listagens extensas.
- **Página de oportunidades validada em produção:** A rota oficial `/vagas-abertas` agora conta com cobertura automatizada para **listagem pública, filtros server-side, destaque de parceiros e fluxo de candidatura na página de detalhes**.
- **Dashboards com cache aquecido e auditoria de acesso:** As métricas do painel agora usam **cache com aquecimento agendado (`dashboard:warm-cache`)**, mantêm o **polling configurável** e registram **tentativas negadas** por plano ou permissão.
- **Sistema de gamificacao completo (Pontos):** Todas as 21 regras de pontos ativas agora sao disparadas automaticamente pelas acoes do usuario. Veja a secao de funcionalidades para detalhes.
- **Pagina "Meus Pontos" para membros:** O membro visualiza seu saldo, posicao no ranking, pontos do mes, top 10 da plataforma e historico completo de pontos ganhos (acessivel em `/painel/meus-pontos` e no sidebar).
- **Programa de Indicacao ("Indique e Ganhe"):** Cada membro possui um link de referral unico. Ao indicar alguem que se cadastra, o indicador recebe pontos automaticamente.
- **Bonus de aniversario:** Sistema agendado concede pontos no aniversario do membro (executa diariamente as 01h).
- **Ranking Top 10:** Toda semana (domingo a meia-noite), os 10 membros com mais pontos recebem bonus automatico.
- **Streak de Login:** Logins consecutivos por 7 ou 30 dias concedem bonus progressivos.

## Novidades Recentes (mar/2026)

- **Refatoração Premium do Painel do Membro:** A barra lateral (sidebar) e o dashboard principal receberam um upgrade visual massivo com efeitos de *glassmorphism* (blur e transparência), sombreamentos 3D e animações suaves de expansão. Os menus da barra lateral agora utilizam a tag `<details>` com o atributo `name`, funcionando como um **acordeão exclusivo nativo** (abrir um menu fecha o outro automaticamente), garantindo uma experiência "de dar água na boca" e otimização sem JavaScript extra.

## Novidades Recentes (fev/2026)

- **Menu mobile 100% responsivo:** Navegação aprimorada em smartphones, com abertura/fechamento suave e acessibilidade total.
- **Dashboard com métricas em tempo real:** Contadores de visitas e vendas atualizados automaticamente via websockets (Laravel Echo + Pusher).
- **CMS integrado ao frontend:** Conteudos do CMS (Home, Sobre, Manifesto, Valores e Rodape/Redes Sociais) agora refletem diretamente nas paginas publicas, com fallback para Settings quando vazio.
- **Sistema de Parceiros e Cupons:** Nova plataforma de parcerias com carrossel premium no Marketplace, área de cupons exclusivos para membros adimplentes e **painel de autogestão** para que parceiros cadastrem seus próprios benefícios.
- **Widgets customizados por perfil:** Cada membro vê métricas e atalhos conforme seu plano; admin/superadmin têm visão global consolidada.
- **Gerenciamento de tarefas agendadas (cron) pelo painel:** Superadmin pode criar, ativar/desativar, rodar e monitorar tarefas agendadas sem depender do cron da hospedagem.
- **Logs detalhados de execuções:** Histórico de execuções e falhas disponível para cada tarefa agendada.

Veja instruções detalhadas abaixo para uso dessas funcionalidades.

## Melhorias de Dashboard (fev/2026)

- Dashboards de membro e admin totalmente refatoradas com widgets dinâmicos Blade.
- Exibição de métricas, vendas, comunidade, ranking, etc. conforme permissões e plano do usuário.
- Estrutura pronta para integração com dados em tempo real (websockets/Redis) e cache otimizado.
- Novos componentes Blade em `resources/views/components/widgets/` para fácil expansão e manutenção.
- Código preparado para uso de Redis (requer extensão fileinfo habilitada no PHP para produção).

> **Atenção:** Para usar cache Redis, habilite a extensão `fileinfo` no PHP e instale o Predis (`composer require predis/predis`).

# Visão Geral do Sistema

O UNN é uma plataforma completa de networking, cursos e mentorias, desenvolvida em Laravel 10.

## Funcionalidades Principais

### 1. Gestão de Conteúdo (LMS)
- **Cursos:** Aulas em vídeo, anexos, controle de progresso e certificação.
- **Mentorias:** Agendamento, controle de vagas e venda de sessões.
- **Eventos:** Calendário interativo, venda de ingressos e check-in.

### 2. Networking e Comunidade
- **Feed Social:** Publicações, curtidas e comentários (estilo rede social).
- **Conexões:** Sistema de solicitação/aceite de conexões entre membros.
- **Chat:** Mensagens em tempo real (polling otimizado para cPanel).
- **Ranking:** Gamificação baseada em avaliações e interações.

### 3. Administrativo
- **Níveis de Acesso:** Granularidade total (SuperAdmin, Admin, Editor, Instrutor, Membro).
- **Relatórios:** Dashboards financeiros, de vendas e de engajamento.
- **Configurações:** Controle total da plataforma via painel (cores, imagens, textos, integrações).

### 4. Gamificação (Sistema de Pontos)
- **21 regras ativas:** Pontos concedidos por login, streak, publicação, comentário, curtida, compartilhamento, aula, curso, certificado, evento, mentoria, avaliação, indicação, aniversário e ranking.
- **Não-repetição automática:** O `PointsService` bloqueia automaticamente ações únicas (ex: completar perfil, primeiro curso) sem lógica duplicada nos controllers.
- **Limite diário:** Regras com `max_daily` (ex: posts, comentários) são respeitadas automaticamente.
- **Streak de login:** 7 e 30 dias consecutivos concedem bônus progressivos.
- **Programa de indicação:** Link de referral único por membro; indicador pontua ao novo usuário se cadastrar.
- **Bônus de aniversário:** Concedido automaticamente 1x por ano (comando `points:award-birthday-bonus`).
- **Ranking Top 10:** Premiação semanal automática (comando `points:award-top-ranking`).
- **Regras configuráveis** pelo painel admin em `/admin/points-rules`.
- **Página "Meus Pontos"** em `/painel/meus-pontos`: saldo, posição no ranking, pontos do mês, top 10 e histórico completo.

### 5. Novidades 2026
- **Menu Mobile Responsivo:** Menu principal funcional em todas as telas e dispositivos.
- **Dashboard Dinâmica:** Widgets e métricas em tempo real, segmentados por perfil.
- **Cron Interno:** Gerencie tarefas agendadas direto pelo painel admin (menu "Cron").
- **Logs de Execução:** Visualize histórico de execuções e falhas de cada tarefa agendada.


## Instalação e Deploy

## UTF-8 sem BOM (OBRIGATÓRIO)

- Este projeto usa **UTF-8 sem BOM** em TODOS os arquivos de texto (PHP, Blade, JS, CSS, JSON, MD, etc.).
- Nunca salve arquivos como **UTF-8 com BOM** (bytes `EF BB BF` no início do arquivo), pois causa erros de acentuação/pontuação.
- Antes de commitar, rode: `php tools/check-no-bom.php`.
- Para bloquear commits com BOM automaticamente, configure uma vez: `git config core.hooksPath .githooks`.

### Requisitos
- PHP 8.1+
- MySQL 5.7+ / Mariadb
- Composer 2+

### Instruções Rápidas (cPanel/Compartilhado)
1. Configure o banco de dados e o arquivo `.env`.
2. Execute as migrações: `php artisan migrate --seed`.
3. Configure o cron job para rodar `php artisan schedule:run` a cada minuto.
4. Para filas, use QUEUE_CONNECTION=database e configure o worker.

> **Importante (mar/2026):** As entregas de rastreio de afiliados, reconciliação histórica de pontos e expansão comercial dependem de migrations recentes. Em toda publicação, garanta a execução de `php artisan migrate`.

> **Dashboard (mar/2026):** O aquecimento das métricas roda em `dashboard:warm-cache` a cada 5 minutos pelo scheduler. Para ajustar cache e polling use `DASHBOARD_CACHE_TTL_SECONDS`, `DASHBOARD_REFRESH_INTERVAL_MS` e `DASHBOARD_CACHE_STORE`.

#### Para usar o cron interno (painel):
- Acesse o menu **Admin > Cron**
- Cadastre comandos Artisan (ex: `schedule:run`, `queue:work`, etc.) e defina a frequência (cron/preset)
- Ative/desative tarefas conforme necessário
- Execute manualmente e visualize logs de cada execução

#### Para dashboards em tempo real:
- Certifique-se de que as variáveis PUSHER estão configuradas no `.env`
- O painel usará websockets para atualizar contadores automaticamente

#### Menu mobile:
- O menu principal está 100% funcional em smartphones e tablets, com navegação fluida e acessível

### Webhooks de Pagamento
Configure as URLs no seu gateway (MercadoPago):
- `YOUR_DOMAIN/api/v1/webhooks/mercadopago`

### SMTP e Emails
Configure as credenciais no painel admin em **Configurações > SMTP**. Use a ferramenta de "Teste de Envio" para validar.

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
  - O kit do afiliado passou a incluir **criativos graficos em tamanhos especificos**, **snippets embed/iframe para blog e site externo**, e um **playground interno** para testar respostas reais da API sem sair do painel.
  - Foi criado um fluxo de **sandbox de homologacao** para afiliados, com solicitacao por ticket, motivo do uso, dominio, IP de origem e aprovacao/revogacao pelo superadmin.
  - Nova gestao web de **tokens pessoais da API** dentro de `/painel/indicacoes`, com emissao, copia imediata, renomeacao por dispositivo, revogacao e leitura do ultimo uso/IP.
  - O AdminLTE legado ganhou a tela `/admin/indicacoes`, combinando **link pessoal do superadmin, lista de indicados, gestao de tokens da API** e o rastreio global consolidado de afiliados.
  - O AdminLTE legado tambem passou a exibir a **fila de tickets do sandbox** para aprovar ou bloquear o uso da API de testes por afiliado.
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
  - Dashboards do membro/admin agora contam com **cache de métricas**, **comando de aquecimento agendado**, **logs de negação por plano/permissão** e testes automatizados cobrindo visibilidade de widgets e cache do endpoint `/painel/dashboard/stats`.
  - O rastreio de visitas foi expandido para as dashboards com **ranking em tempo real, segmentacao por cursos/eventos/mentorias/palestras/site e visao por responsavel do produto**, incluindo endpoints JSON dedicados para atualizacao sem refresh.

---
© 2026 UNN Networking. Todos os direitos reservados.
