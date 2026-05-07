# Changelog

Todas as alterações relevantes deste projeto são documentadas neste arquivo.

O formato segue [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

---

## [Unreleased]

### Adicionado
- Instruções detalhadas para configuração da SumUp nos painéis de gateway, com origem da API Key, Merchant Code, credenciais OAuth e Webhook Secret.
- Atalho "Configurar Credenciais" e resumo operacional na tela administrativa de transações SumUp.

### Corrigido
- Salvamento das credenciais SumUp do vendedor, incluindo API Key e Merchant Code em `gateway_accounts.extra`.
- Validação de webhooks SumUp compatível com o cabeçalho oficial `x-payload-signature`.

---

## [2026-04-23]

### Adicionado
- Integração completa do gateway de pagamento **SumUp** como opção adicional ao Mercado Pago
  - Migrations: `sumup_transactions`, `sumup_webhook_logs`, `sumup_saved_cards`, coluna `sumup_subscription_id` em `subscriptions`
  - Models: `SumUpTransaction`, `SumUpWebhookLog`, `SumUpSavedCard`
  - `SumUpService`: checkout, pagamento com cartão (tokenização), PIX com QR Code inline, reembolso total/parcial, assinaturas recorrentes, webhooks dinâmicos por transação, validação HMAC
  - `SumUpWebhookProcessor`: dispatcher idempotente de eventos (`payment.succeeded`, `payment.failed`, `payment.refunded`, `subscription.renewed`, `subscription.cancelled`)
  - `SumUpController` no painel admin: listagem de transações com filtros, detalhes, reembolso, relatório com gráfico Chart.js, exportação CSV, teste de conexão
  - Rotas: webhook público `POST /webhook/sumup/{orderId}/{token}` e grupo `panel.admin.sumup.*`
  - Item "SumUp" no sidebar do painel admin
  - Seção SumUp nas configurações de gateway em ambos os painéis (`/admin` e `/painel/admin`) com campos padronizados, toggle ativo/inativo, taxas, métodos e botão de teste de conexão
  - Sincronização automática das credenciais SumUp com `gateway_accounts` ao salvar configurações (mesmo padrão do Mercado Pago)

### Corrigido
- `OrderRefundService`: adicionado suporte ao gateway `sumup` no `match` de `refundOnGateway()`
- `SettingController::testGateway()`: estendido para aceitar `gateway=sumup` com chamada real à API `GET /v0.1/me`
- `GatewayAccount`: adicionado método `resolveForSellerSumUp()` seguindo o mesmo padrão de `resolveForSeller()`
- Rotas `panel.admin.mailtemplates.*` corrigidas na view `panel/admin/mailtemplates/index.blade.php` — todas as referências atualizadas de `admin.mailtemplates.*` para `panel.admin.mailtemplates.*`

---

## [2026-04-22]

### Corrigido
- Erro `Route [panel.admin.mailtemplates.index] not defined` no painel admin Tailwind
- Adicionadas rotas `panel.admin.mailtemplates.*` com nomes únicos para o prefixo `/painel/admin`
- View de listagem de mail templates detecta corretamente qual painel está ativo e usa as rotas correspondentes
- Ambos os painéis (superadmin `/admin` e admin `/painel/admin`) funcionam de forma independente sem conflito de rotas

---

## [2026-04-14]

### Corrigido
- Troca de abas quebrada na página de edição de eventos: `restoreActiveTab()` sobrescrevia o parâmetro `?tab=` da URL ao carregar a página
- Adicionada guarda para pular restauração do localStorage quando `?tab=` está presente na URL
- Script de inicialização de aba forçado corretamente no formulário de eventos

---

## [2026-04-13]

### Corrigido
- Refinamento da visibilidade de campos e abas no painel administrativo legado (AdminLTE)

---

## [2026-04-12]

### Adicionado
- **Módulo Acervo de Mídia**: desacoplamento completo da galeria de eventos — álbuns genéricos independentes de eventos
- Rotas puras `/acervo/create` para isolamento do módulo Acervo
- Botão "Definir como Capa" pela galeria de fotos do evento
- Capa opcional para álbuns (sem obrigatoriedade)
- Comando `events:update-batches` para tracking automático de virada de lotes de ingressos
- Botão Salvar no modal de edição de eventos com exibição de duração
- Link direto para galeria/acervo na home pública

### Corrigido
- Ativação da aba Galeria e submissão do formulário de Acervo no painel legado
- `RouteNotFoundException` ao redirecionar após salvar/deletar um acervo
- Importação ausente de `UploadStorage` que causava erro 500 no salvamento de capa
- Erro `Cannot set properties of undefined` no DataTables na listagem do acervo
- Erro `Incorrect column count` do DataTables
- Summernote vazio ao abrir modal de edição de eventos
- Notificação toastr duplicada no layout do painel
- Popup SweetAlert2 duplicado nas notificações de flash session
- Lógica VIP de lotes removida — todos os usuários veem o lote real baseado nas datas
- Exclusão do CSRF header nas requisições Axios da galeria
- Variáveis não definidas na inclusão do componente de galeria
- Redirecionamento pós-save diferenciado entre Acervo e Evento
- Validação nativa de data desativada em álbuns
- Obrigatoriedade e visibilidade de datas removidas para registros do tipo álbum
- Botão da galeria na home corrigido para rota correta do acervo

### Alterado
- Labels do formulário de eventos adaptados para o tipo Acervo
- Campos de Mapa/Localização ocultados para registros do tipo álbum
- Marca d'água removida globalmente de eventos e álbuns

---

## [2026-03-26]

### Adicionado
- **Loja Virtual por Vendedor**: vitrine pública premium com produtos, canais de venda e pontos
- Summernote ativado na bio da loja virtual
- Separação da loja oficial da plataforma da elegibilidade comum de vendedores

### Corrigido
- Salvamento de produtos contra bloqueios 403 do ModSecurity com normalização de valores monetários
- Camadas do dropdown de cores do Summernote
- Paleta e preview de cores do Summernote
- Contraste do Summernote entre tema claro e escuro
- Alinhamento do Summernote ao tema ativo dos painéis
- Seletor de cores do Summernote nos painéis
- Espaço acima do cabeçalho da loja pública
- Layout mobile da loja
- Textos com acentuação corrompida
- Método `normalizeMoneyInput` removido acidentalmente no `Admin\SellerProductController`

### Alterado
- Layout da vitrine da loja alinhado ao padrão de e-commerce
- Faixa de filtros da loja pública reorganizada
- Rodapé informativo da loja em três colunas
- Cards de benefícios em quatro colunas
- Seção institucional da loja em três colunas

---

## [2026-03-25]

### Adicionado
- Implementação completa da loja virtual por vendedor no marketplace

---

## [2026-03-24]

### Adicionado
- **Consentimento LGPD**: modal de consentimento com enforcement e páginas de documentos legais (Termos, Privacidade, LGPD)
- Publicação de conteúdo legal via CMS com editor Summernote rico
- Suporte a vídeos na galeria de eventos do painel
- Centralização da publicação das páginas legais no CMS

### Corrigido
- Edição das páginas legais no admin
- Desequilíbrio de divs em partials legados e restauração de visibilidade de abas
- Lógica de abas AdminLTE e correção de visibilidade SEO
- Syntax error no editor AdminLTE que causava erro 500
- Rodapé público refatorado para layout full-width

### Alterado
- Editor de páginas admin convertido para sistema de abas
- Rodapé público redesenhado com links refinados
- Menu mobile expandido com links institucionais

---

## [2026-03-23]

### Adicionado
- **Sistema de Verificação de E-mail** com interface premium e banner de alerta
- **Bottom Navigation** para dispositivos móveis (PWA)
- **Drag-and-Drop no FullCalendar** com AJAX e SweetAlert2
- **Logomarca Dinâmica Somos Únicas** com sincronização de credenciais de checkout
- **Gestão administrativa de split e chaves PIX** nos painéis admin e moderno
- **Ranking público** redesenhado com pódio premium, coroa flutuante e medalhas
- Toggle de layout dinâmico Full/Boxed controlado pelo painel administrativo
- Paridade total entre painéis (Moderno/AdminLTE) no CMS com sistema de abas Zero Refresh
- Repeaters visuais com Drag-and-Drop e Upload AJAX nos partials institucionais
- Páginas institucionais (Termos, Privacidade, LGPD) com SEO e CMS completo
- Preview de e-mail em tempo real sincronizado entre os painéis
- Unificação de e-mails com design de cabeçalho padronizado

### Corrigido
- Erro 500 na página de ranking por tag `@endif` ausente
- Responsividade mobile da seção journey
- Layout boxed em todas as seções da home e navbar
- Checkout unificado em cursos, eventos e mentorias
- Visibilidade da carteira MP no checkout
- Syntax Blade na indicação e credenciais MP no checkout
- Rotas de checkout não definidas
- Validação de CPF no Mercado Pago (exatamente 11 dígitos)
- Redundância de taxa marketplace no gateway
- Preview de e-mail ultra-responsivo e resiliente a modais lentos
- Sincronização do preview em tempo real para editores em modais

### Alterado
- Painel SPA de alta velocidade com interceptador AJAX vanilla JS
- Troca de tema claro/escuro sem `window.reload`
- Dashboard admin refatorado com métricas de hoje e ações rápidas
- Cartões de rastreio redesenhados para layout vertical flexível

---

## [2026-03-22]

### Adicionado
- **Scanner de Ingressos Universal** com antifraude por GPS (10m), validação de horário e suporte para instrutores
- **Checkout Transparente para Eventos** com temporizador PIX
- Meta tags dinâmicas Open Graph e Twitter Card para eventos, cursos, mentorias, parceiros e vagas
- Componente avançado de drag-and-drop para capa do evento
- Campo de descrição com Summernote no formulário legado de eventos

### Corrigido
- Scanner universal que não abria a câmera — migrado para `Html5Qrcode` de baixo nível
- Scanner do instrutor não redirecionava mais para admin — rota própria no painel
- Sidebar: abre grupo Eventos e marca Scanner universal ativo
- Sidebar: marca menu Extrato de Splits como ativo e abre grupo Marketplace
- Sidebar: marca menu Galeria de Fotos como ativo em todas as rotas `admin.gallery.*`
- Rotas `admin.fonts.*` ausentes no `web.php`
- Rotas `admin.upload.chunk` e `admin.upload.assemble` ausentes
- Rota `admin.events.calendar.settings` que causava erro 500
- Erro `Undefined variable $course` em `admin/courses/create`
- Editor Summernote via `x-init` Alpine e estrutura de scripts corrigida
- Links de mapa com fallback e inicialização do Summernote via IIFE compatível com SPA
- Erro 2067 Invalid user identification no PIX do MercadoPago

---

## [2026-03-21]

### Adicionado
- **SPA de Alta Velocidade** no novo painel com interceptador AJAX vanilla JS customizado
- **Migração de Lotes de Ingressos** com tracking automático
- **Hub de Notificações** refatorado com cards individuais de notificação com lido/não-lido rastreável
- Expansão da estética premium glassmorphism para todos os dashboards secundários
- Refatoração estética premium da barra lateral com acordeão nativo e glassmorphism
- Experiência imersiva nativa no lightbox da galeria
- Melhoria na visualização de mídia no lightbox pelo celular

### Corrigido
- Quick Scanner abrindo no layout AdminLTE legado dentro do novo painel
- `@endforeach` ausente que causava HTTP 500 na Homepage
- Scroll horizontal, erro JS e métricas da dashboard

---

## [2026-03-14]

### Adicionado
- Suporte a HEIC e otimização automática de imagens no upload da galeria
- Controllers admin migrados para respostas JSON — sem refresh de página no CRUD

### Corrigido
- Rotas de checkout de mentorias ausentes
- Rota `share.product` ausente
- Suporte ao tema dark na página de FAQ do painel admin
- Suporte ao tema dark na página de depoimentos admin
- Scanner do instrutor não redirecionava mais para admin

---

## [2026-03-13]

### Corrigido
- Upload XHR por arquivo, capa personalizada e rota destroy corrigida na galeria admin
- Destaque automático removido da galeria — todos os álbuns na grade igual
- Páginas públicas de galeria simplificadas removendo textos desnecessários

---

## [2026-03-12]

### Adicionado
- Redesign completo da vitrine pública da galeria
- Alinhamento das galerias admin e painel ao visual premium
- Watermark obrigatório e upload rápido modernizado

### Corrigido
- Restauração de rotas faltantes: galeria, quick-scanner, marketplace, redemptions, fontes, mídia
- Restauração de rotas admin legadas e aliases
- Upload da galeria admin: fluxo, modal, limite e preview inline
- Redirecionamento de feature bloqueada
- Uploads S3 compatíveis com IDrive E2
- Ajuste de uploads admin ao padrão AdminLTE
- Namespace `MailTemplateController` corrigido
- Rota `panel.admin.redemptions.index` para sidebar

---

## [2026-03-11]

### Adicionado
- **Redesign Completo do Gateway Admin**: hero gradiente, tabs, segmented-control de ambiente, cards de métodos, reveal/hide de tokens
- Componente global de upload (drag & drop, barra de progresso, preview) em todas as páginas do admin
- Suporte completo a OAuth 2.0 na integração SumUp (posteriormente removido e reintegrado em 2026-04-23)

### Corrigido
- Painel admin e gateway legado
- Modal de upload rápido no painel reescrito em Tailwind e JS puro
- Upload chunked no admin: estabilização, limite e permissões
- Editor legado e upload de páginas
- Preloader do frontend restaurado
- Recorrência nas regras de pontos legado
- Layout da página de evento
- Contraste do botão no scanner admin
- Legibilidade do overlay do scanner
- Galeria do painel redesenhada
- Campos do portal no CMS persistidos e populados com defaults

---

## [2026-03-10]

### Adicionado
- **SEO Dinâmico** com estatísticas e CTA editáveis na página de membros
- **UNNBIT**: fluxo de valorização e resgate
- **Controles de geofence configuráveis** para o scanner de ingressos
- Padronização premium de uploads no painel do membro e galeria
- Integração global do SweetAlert2
- Migração completa do sistema de gerenciamento de páginas para Tailwind CSS
- DataTables na listagem de eventos
- Confirmações globais via SweetAlert2
- Persistência de acknowledgements no hub de notificações
- Unificação do editor de certificados no painel

### Corrigido
- Máscaras de telefone para fixo e celular
- Binding Alpine nas notificações da navbar
- Tratamento global de CRUD via AJAX
- Sino de notificações com acknowledgement instantâneo

---

## [2026-03-09]

### Adicionado
- **Scanner Universal de Ingressos** com antifraude por GPS (10m), validação de horário e suporte para instrutores
- **Galeria de Eventos** com controles de marca d'água e otimizações de performance
- **Múltiplos Ingressos Individuais** com QR Code por ingresso
- Permissões obrigatórias (GPS, Câmera, Vibrate) e manifest PWA aprimorado para scanner
- Agrupamento de múltiplos ingressos no mesmo e-mail
- Exibição de ingressos e melhoria no feedback do scanner
- Animações dinâmicas na página Premium
- Dualidade de painéis para listagem de eventos

### Corrigido
- Restrição de duplicidade de ingressos por usuário removida
- Disponibilidade do scanner de eventos
- Tratamento de reservas duplicadas de eventos
- Normalização de telefone brasileiro e máscaras
- Layout mobile do quick scanner
- Scroll horizontal, erro JS e métricas da dashboard
- Preloader restaurado no painel administrativo

---

## [2026-03-08]

### Adicionado
- **Galeria de Fotos e Vídeos** com marca d'água dinâmica em eventos
- **Sistema de Validação de Ingressos QR Code** com pontuação no app
- **Controle de Visibilidade de Conteúdo** por seção
- **Página 'Sobre' da Comunidade Somos Únicas**
- Suporte a variáveis flexíveis nos templates de e-mail
- Bypass de firewall (403) usando Base64 para salvamento de HTML em templates
- Tema roxo profundo para Somos Únicas com campo de imagem networking no CMS
- Método `canMessageUser` no model User

### Corrigido
- Erro 500 no perfil — lógica de conexão movida para o model `Connection`
- Caminhos de imagem de capa de cursos Somos Únicas
- Datas e range do calendário de eventos Somos Únicas
- Rota `panel.admin.courses.show`
- Fallback do checkout MercadoPago para eventos
- Flash de modal de ingresso ao recarregar
- Toast de erro duplicado na página de evento
- Disponibilidade de evento e fallback de preferência MP
- Refunds de produtos no admin e descrição de produto no MercadoPago
- Split desativado no Mercado Pago para vendas do próprio administrador
- Ambiente MP alterado para produção com melhoria no tratamento de erro no saldo
- Fallback para token da plataforma no saldo do Mercado Pago
- Sincronização forçada do Summernote e consistência de resposta AJAX
- Limpeza automática de boilerplate HTML nos templates de e-mail
- Unificação de templates de e-mail com layout da UNN
- Importação de serviço e validação de email no admin
- Rota de quem somos no submenu Somos Únicas
- Syntax error no controller de eventos que impedia finalização de compra
- Placeholders de imagem na demo e autoria atribuída ao admin logado

---

## [2026-03-07]

### Adicionado
- **Área Somos Únicas** na cor rosa com página oficial do Ranking da Comunidade
- Componente global de upload (drag & drop, barra de progresso, preview) em todas as páginas do admin
- Script para importar credenciais do `.env` para o banco de dados automaticamente
- Modal de evento com background, layout, texto e botão atualizados
- Exibição dinâmica da forma de pagamento selecionada no card de Resumo do checkout de eventos

### Corrigido
- Bug 500 no dashboard AdminLTE com diretiva `@json` multilinha corrompida pelo parser Blade
- Bug 500 do `RouteNotFound` ao acessar log de atividades
- Leitura de chaves do PagSeguro (Global) no `EventReservationController`
- Lógica de gateway: permite compra se credenciais globais estiverem configuradas
- Credenciais globais de pagamento buscadas exclusivamente no banco de dados (Setting::get)
- Textos corrigidos para 'SOMOS UNN' nas views do frontend
- Páginas do CMS restauradas e impedidas de sumir da listagem do admin

---

## [2026-03-06]

### Adicionado
- **Rastreio Completo de Afiliados**: cliques, visitas e compras com log detalhado, CSV e visão global no admin
- **Kit de Divulgação e API REST** para o afiliado montar páginas externas
- **Gestão de Tokens da API** no painel do afiliado
- **Dashboards com Visitas em Tempo Real** e métricas por responsável
- Gráficos diários e por canal no painel de indicações
- Organização das indicações em abas no admin legado e no painel novo
- Itens de resgate destacados no menu admin
- Reconciliação de pontos históricos de membros antigos sem duplicar saldo
- Instrutor e vendedor incluídos nos planos pagos

### Corrigido
- Ícone do Pix nos checkouts e nas configurações
- Sincronização do resumo do checkout com o gateway selecionado
- Restrição de whitelist do Pix no PagSeguro sem expor erro bruto no checkout
- Brechas de eventos, plano gratuito e pontuação recorrente
- CSV de rastreio para abrir corretamente no Excel sem UTF-8 com BOM
- Erro no painel quando as colunas novas de resgates ainda não existem
- HTML bruto nos resumos públicos de mentorias, eventos e cursos
- Botões de eventos e mentorias encerrados bloqueados nas vitrines públicas
- Imagem do plano no resumo do checkout de assinatura

---

## [2026-03-05]

### Adicionado
- Upload avançado, menu recolhível e paginação nas listas do painel
- Summernote sem upload no detalhe dos itens de resgate
- Regras de pontos em lista colorida por categoria

### Corrigido
- Listagem de regras de pontos no painel novo
- Telas de fatura alinhadas ao padrão visual do painel
- Calendário e fluxo de eventos no painel admin novo
- Edição de mentorias alinhada ao layout novo do painel admin
- Rodapé público usando o nome configurado no painel em vez de UNN fixo
- Tamanho visual das imagens nas configurações do admin
- JavaScript global do admin para reativar os uploads de imagens

---

## [2026-03-02]

### Adicionado
- **Sistema de Gamificação Completo**:
  - `PointsService` com guarda de não-repetição, limite diário e cálculo de streak
  - Hooks de autenticação: login diário com detecção de streak 7/30 dias, bônus de indicação no cadastro, pontos por completar perfil
  - Hooks de comunidade: publicar post, comentar, receber curtida, compartilhar nas redes sociais
  - Hooks de conteúdo: aula concluída, primeiro/qualquer curso, certificado, evento, mentoria, avaliação e mentor ao criar mentoria
  - Comandos agendados: bônus semanal para top 10 do ranking (domingo) e bônus de aniversário diário (01h)
- **Página 'Meus Pontos'**: histórico paginado, posição no ranking, pontos do mês, top 10 motivacional
- **Programa de Indicações**: link de referral, botão de copiar, compartilhamento WhatsApp/Telegram
- **Share-with-Approval**: compartilhamento entre membros com aprovação do destinatário, inbox de solicitações, notificações e expiração automática em 7 dias
- **Widget de Pontos UNN** no painel do membro com ranking e pontos do mês
- **Página de Oportunidades** moderna com filtros empresa/tipo, badge parceiro e melhorias ARIA
- **CMS de Páginas de App**: premium, eventos, membros, vagas, cursos, portal e feed integrados com título, hero e SEO configurável
- Login social robusto com cadastro redirecionando para planos
- Campo de data de aniversário no perfil

### Corrigido
- Relacionamento `conversations()` no model User
- Variável `$splits` não passada para a view `splits/index`
- Bloco `@empty` duplicado que causava ParseError na view de vagas

---

## [2026-02-27] — [2026-03-01]

### Adicionado
- **Sistema de Split de Pagamentos**: modelo `OrderSplit`, cálculo automático de splits por pedido pago
- **Chave PIX** no perfil do usuário
- **Campos de período e preços** em planos (mensal, trimestral, semestral, anual)
- **Plano gratuito** com flag `is_free`

### Corrigido
- Campo `paid_at` em pedidos
- Satisfações e feedback

---

## [2026-02-20] — [2026-02-26]

### Adicionado
- **Vagas de Emprego**: modelo `JobVacancy`, candidaturas, visibilidade e campos avançados
- **Itens Resgatáveis (UNNBIT)**: modelo `RedeemableItem`, resgates e catálogo
- **Parceiros**: módulo completo com cupons aninhados, slug e página pública
- Aprovação manual de pedidos com campos de auditoria

### Corrigido
- Campos faltantes em lições
- Aprovação manual financeira de pedidos

---

## [2026-02-10] — [2026-02-19]

### Adicionado
- **Certificados para Mentorias e Eventos** com campos de carga horária
- **Logs de Atividade** do sistema
- **Galeria de Eventos** com tabela `event_media`
- **Ingressos de Eventos**: campos de ticket, QR Code e scanner
- **Wishlist** de cursos para membros
- Campo de gênero e data de nascimento no perfil do usuário

### Corrigido
- Campos de certificado em cursos e mentorias
- Metadados de certificados

---

## [2026-02-05] — [2026-02-09]

### Adicionado
- **Conexões entre Membros**: aceitar, remover, bloquear, notificações
- **Chat em Tempo Real**: conversas, mensagens, leitura
- **Feed Social**: posts, comentários, reações, compartilhamentos, denúncias, ocultação
- **Notificações**: hub de notificações, leitura, exclusão
- **Avaliações de Itens**: cursos e mentorias
- **Bookmarks de Aulas** com progresso de reprodução
- **Índice de Performance** com índices de banco de dados

---

## [2026-02-02] — [2026-02-04]

### Adicionado
- **Sistema de Pedidos (Orders)**: modelo `Order`, `OrderItem`, `OrderShipment`, status, gateway, transaction_id
- **Assinaturas**: modelo `Subscription` com gateway e período
- **Contas de Gateway**: modelo `GatewayAccount` com suporte a MercadoPago OAuth
- **Webhooks MercadoPago**: processamento de pagamentos, fulfillment de pedidos, ativação de planos
- **Checkout Transparente**: cursos, mentorias com MercadoPago
- **Faturas (Invoices)**: geração, PDF, envio por e-mail
- **Cupons de Desconto**: modelo `Coupon`, `CouponRedemption`, validação no checkout
- **Fontes Personalizadas**: upload e gestão de fontes no admin
- **Progresso de Aulas**: rastreamento de progresso e retomada de vídeo
- **Vídeo Seguro**: streaming com chave de acesso por aula

---

## [2026-01-28] — [2026-02-01]

### Adicionado
- **Estrutura Base do Projeto**:
  - Migrations iniciais: usuários, cursos, aulas, eventos, mentorias, matrículas, pagamentos, certificados, configurações
  - **Planos de Assinatura**: modelo `Plan` com permissões, slug, descrição, destaque, comparação, ciclo e pró-rata
  - **Sistema de Permissões**: roles, permissions, plan_permission
  - **Templates de E-mail**: modelo `MailTemplate` com slug, categoria, assunto, corpo e status
  - **Regras de Pontos**: modelo `PointsRule` com categoria e recorrência
  - **Logs de Pontos**: modelo `PointsLog`
  - **Tabelas de Rede Social**: conexões, conversas, posts
  - **Notificações**: tabela de notificações do sistema
  - Campos sociais nos usuários (bio, avatar, redes sociais)
  - Campos de plano nos usuários (plan_id, plan_expires_at)
  - Campos de perfil profissional e conexões nos usuários
  - Campos de vídeo avançado nas aulas (player, retomada, bookmarks)
  - Campos de certificado em cursos (template, assinatura, carga horária)
  - Campos de SEO e OG nas configurações
  - Índices de performance no banco de dados
  - Jobs: `ProcessEmailQueue`, `SendInvoiceEmailJob`, `SendMarketplaceOrderPaidEmailsJob`
  - Middleware: `AdminMiddleware`, `CheckFeature`, `CheckPermission`, `EnsureUserIsAdmin`, `LogUserActivity`, `TrackReferralLink`, `TrackVisitor`

---

*Changelog gerado em 23/04/2026.*
