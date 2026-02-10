# Novidades Fevereiro 2026

- Todos os títulos principais das páginas públicas (Home, Portal, Membros, Premium) agora exibem efeito de gradiente azul (Azul Royal, Oceano, Ciano Vivo) para reforçar a identidade visual UNN.
- Carrossel de cursos em destaque na Home e Portal, exibindo até 6 cursos aleatórios, com navegação responsiva.
- Paleta UNN aplicada em todo o frontend público, mantendo o painel admin com cores originais.
- Sidebar do admin modernizada e responsiva.
- Correções de layout, responsividade e experiência visual em todas as páginas públicas.

# Refatoração de Configurações (Fevereiro 2026)

A página de configurações do administrador (`/admin/settings`) foi completamente reconstruída para organizar melhor as opções do sistema:

- **Abas Organizadas:** Configurações separadas em categorias claras: Geral, Aparência, Imagens, Player, Anúncios, PWA, Gateway, SMTP, Login Social, SEO e Sistema.
- **Uploads com Drag-and-drop:** Melhoria visual e funcional nos campos de upload (logos, favicons, etc.) com suporte a arrastar e soltar.
- **Separação de Anúncios:** Configurações de Ads (AdSense e HTML personalizado) agora têm sua própria aba, separada das configurações do Video Player.
- **Teste de SMTP:** Funcionalidade integrada para testar o envio de e-mails diretamente da aba SMTP.


# Granularidade de Permissões (Fevereiro 2026)

O sistema agora possui controle total e granular de permissões para todas as áreas (cursos, mentorias, eventos, marketplace, uploads, faturas, certificados, pontos, cupons, planos, permissões, usuários, FAQ, fontes, mailtemplates, ranking, social, orders, invoices, reviews, depoimentos, comunidade, chat, etc).

Cada rota e ação está protegida por middleware `check.feature:<feature>`, permitindo máxima flexibilidade para upgrades, downgrades e personalização de planos.

## Testes Automatizados

Execute os testes para validar granularidade:
```
php vendor/bin/phpunit --filter=GranularPermissionTest
```

## Seeder de Plano Granular
Para criar um plano com todas as permissões:
```
php artisan db:seed --class=GranularPlanSeeder
```

## Deploy

1. Faça o pull do repositório
2. Rode as migrations se necessário
3. Rode os seeders para planos/permissões
4. Execute os testes para validar permissões

# UNN — Plataforma de Networking (Scaffold)

Este diretório contém o scaffold inicial para um projeto Laravel 10 preparado para PHP 8.4 e hospedagem em cPanel.

Principais instruções rápidas:

1. Copie o conteúdo deste diretório para o servidor (ou use `composer create-project`):
   - `composer install`
   - `cp .env.example .env` e configure as variáveis (DB, pagamentos, social)
   - Ajuste `config/services.php` com as redirect URLs para Socialite
   - `php artisan key:generate`
   - `php artisan migrate --seed`
   - `php artisan storage:link`


## Atualização recente (Fevereiro 2026)

- Aplicação da paleta UNN (Azul Royal #2E3192, Azul Oceano #0071BC, Ciano Vivo #29ABE2) em todo o front-end público (portal, home, cursos, eventos, botões, backgrounds, títulos, links).
- Painel administrativo mantido com cores originais, apenas melhorias de responsividade e organização visual.
- Seção de cursos em destaque exibe carrossel automático (3 na tela, passando 1 por vez, looping infinito), só aparece se houver cursos destacados.
- Sidebar e configurações do painel admin organizadas, modernas e responsivas.
- Layout e responsividade aprimorados em todas as páginas públicas.

## Deploy em hospedagem compartilhada (cPanel)

Resumo (leia o guia completo em `DEPLOY_CPANEL.md`):
- Use filas em banco: `QUEUE_CONNECTION=database` (e rode migrations para `jobs`, `failed_jobs`, `job_batches`).
- Configure Cron no cPanel para processar filas sem daemon: `queue:work --stop-when-empty` a cada 1 minuto.
- Após deploy, rode `php artisan optimize` (e, se necessário, `php artisan optimize:clear` + `optimize`).

PHPMailer
- Instale `composer require phpmailer/phpmailer` (already listed in composer.json)
- Configure `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` in .env
- Acesse `/admin/mail-test` para enviar um e-mail de teste

Payments (MercadoPago / PagSeguro)
- Configure as credenciais no `.env` conforme `MERCADOPAGO_ACCESS_TOKEN`, `PAGSEGURO_TOKEN`
- Webhooks endpoints:
  - /webhook/mercadopago
  - /webhook/pagseguro

## API REST (Apps Mobile / Integrações)

Base URL: `/api/v1`

### Padrão de comunicação
- Formato: `application/json`
- Charset: `UTF-8` (sem BOM)
- Versionamento: prefixo `/v1`
- Paginação: endpoints de listagem retornam `data`, `links` e `meta` (padrão Laravel)

### Autenticação (Laravel Sanctum)

Fluxo recomendado para Android/iOS/Windows:
1. `POST /api/v1/auth/login` (ou `register`) para obter token.
2. Guardar token no storage seguro do app.
3. Enviar `Authorization: Bearer {token}` nas rotas protegidas.
4. Encerrar sessão com `POST /api/v1/auth/logout`.

Rotas de autenticação:
- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout` (requer token)
- `GET /api/v1/me` (requer token)

Exemplo de request de login:
```bash
curl -X POST "https://SEU-DOMINIO/api/v1/auth/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@email.com",
    "password": "senha",
    "device_name": "android"
  }'
```

Exemplo de resposta de login:
```json
{
  "token": "1|TOKEN_GERADO",
  "user": {
    "id": 10,
    "name": "Fulano",
    "email": "user@email.com",
    "phone": "(11) 99999-9999",
    "photo_url": "https://SEU-DOMINIO/storage/...",
    "role": "member",
    "level": "basic",
    "plan_id": 1,
    "plan_expires_at": "2026-12-31T23:59:59+00:00",
    "created_at": "2026-02-07T12:00:00+00:00"
  }
}
```

Exemplo de rota autenticada:
```bash
curl "https://SEU-DOMINIO/api/v1/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|TOKEN_GERADO"
```

### Endpoints públicos

#### Healthcheck
- `GET /api/v1/health`
- Resposta:
```json
{
  "status": "ok",
  "timestamp": "2026-02-07T18:20:00+00:00"
}
```

#### Eventos
- `GET /api/v1/events?status=upcoming|past|all&per_page=20`
- `GET /api/v1/events/{event}`
- Campos principais: `id`, `title`, `speaker`, `description`, `image_url`, `start_at`, `end_at`, `all_day`, `location`, `address`, `latitude`, `longitude`, `price`, `current_price`, `batch_label`, `capacity`, `confirmed_seats`, `remaining_seats`, `published`, `color`.

#### Cursos
- `GET /api/v1/courses?status=published|all&per_page=20`
- `GET /api/v1/courses/{course}`
- Campos principais: `id`, `title`, `slug`, `price`, `duration`, `total_hours`, `thumbnail_url`, `short_description`, `full_description`, `author_name`, `status`, `is_featured`.

#### Aulas (progresso e marcadores)
- `GET /courses/{course}/lessons/{lesson}` (web, autenticado quando não for prévia gratuita)
- `POST /courses/{course}/lessons/{lesson}/progress` (auth)
  - payload: `{ "current_time_seconds": 123 }`
  - uso: salvar ponto atual do vídeo (retomada automática)
- `POST /courses/{course}/lessons/{lesson}/bookmarks` (auth)
  - payload: `{ "position_seconds": 123, "note": "Revisar este trecho" }`
  - uso: criar marcador com comentário na timeline da aula
- `DELETE /courses/{course}/lessons/{lesson}/bookmarks/{bookmark}` (auth)
  - uso: remover marcador do usuário logado

#### Mentorias
- `GET /api/v1/mentorships?per_page=20`
- `GET /api/v1/mentorships/{mentorship}`
- Campos principais: `id`, `title`, `description`, `price`, `slots`, `schedule`, `mentor`.

#### Planos
- `GET /api/v1/plans?per_page=20`
- `GET /api/v1/plans/{plan}`
- Campos principais: `id`, `name`, `slug`, `price`, `period`, `billing_cycle`, `prorata`, `description`, `image_url`, `is_featured`, `highlight`, `coupons_enabled`, `benefits`, `permissions`, `comparison`, `is_active`.

#### Depoimentos
- `GET /api/v1/testimonials?per_page=20`
- Retorna apenas itens aprovados.

### Contrato de erros
- `401 Unauthorized`: token ausente/inválido.
- `404 Not Found`: recurso não publicado/inexistente.
- `422 Unprocessable Entity`: erro de validação (ex.: login/registro).
- `500 Internal Server Error`: erro inesperado.

Exemplo de validação:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "Credenciais inválidas."
    ]
  }
}
```

### Dicas para apps
- Defina timeout e retry no cliente HTTP.
- Renove token refazendo login quando receber `401`.
- Prefira `per_page` entre `10` e `50` para melhor desempenho em redes móveis.
- Use cache local para listas (`events`, `courses`, `plans`) e invalide por tempo.
## Correções recentes (Admin)

- Calendário de Eventos (`/admin/events`) com inicialização reforçada no FullCalendar v4 para evitar tela em branco em carregamentos fora do fluxo padrão.
- Feed de eventos ajustado no backend (`Admin\\EventController@feed`) para garantir JSON válido com `textColor` em todos os itens.
- Área do calendário com altura mínima para manter visualização estável mesmo quando não houver eventos.

## Cupons de desconto (Eventos / Cursos / Mentorias)

- Admin: gerencie em `/admin/coupons` (criação, edição e remoção).
- Regras suportadas:
  - Desconto **percentual** ou **valor fixo**
  - Escopo: **Geral**, **Eventos**, **Cursos** ou **Mentorias**
  - Opcional: limitar por **ID** do item (promoção direcionada)
  - Validade por período (início/término) e limites de uso (total e por usuário)
- Aplicação no site:
  - Eventos: campo de cupom no checkout em `/eventos/{event}/checkout`
  - Cursos: campo de cupom no checkout em `/checkout/{course}`
- Controle de uso:
  - O cupom é **reservado** por 30 minutos ao criar o pedido e marcado como **usado** quando o webhook confirma o pagamento.

## Faturas (PDF por e-mail)

- Admin: gerencie em `/admin/invoices` (criar/editar/ver, enviar e gerar PDF).
- Pedido (Admin): em `/admin/orders/{order}`, use “Emitir e enviar fatura” quando ainda não existir fatura.
- Automático: ao confirmar pagamento (webhooks MercadoPago/PagSeguro e assinaturas), o sistema emite a fatura do pedido e enfileira o envio por e-mail.
- Requisitos:
  - Rode `php artisan migrate` para criar `invoices` e `invoice_items`.
  - Mantenha a fila ativa (em cPanel: `QUEUE_CONNECTION=database` + cron/cron interno) para processar a queue `emails`.

## FAQ (Perguntas Frequentes)

- Admin: gerencie em `/admin/faqs` (contexto, ordem e status).
- Site:
  - Páginas que consomem: `/premium` (contexto `premium`) e `/contato` (contexto `contact`).
  - Fallback automático: se não houver perguntas no contexto da página, busca em `general`.
  - Até 4 itens: exibe cartões abertos (pergunta + resposta).
  - Mais de 4 itens: exibe em acordeon e paginação (4 por página).

PWA
- Ative/desative pelo painel administrativo em Configurações → PWA
- Upload de ícones no painel atualiza `manifest.webmanifest` dinamicamente
- O manifest dinâmico é servido em `GET /manifest.webmanifest` e respeita a flag `pwa_enabled`

## Depoimentos (moderação)

- **Site:** a página `/premium` exibe depoimentos aprovados e permite envio autenticado via `POST /depoimentos` (fica como **pendente** até moderação).
- **Admin:** moderação em `/admin/testimonials` (aprovar/recusar/editar; exclusão só para quem tiver permissão).
- **Permissões (RBAC):** `testimonials.view`, `testimonials.moderate`, `testimonials.delete`.

## Avaliações de Cursos e Mentorias

- **Página de venda:** cursos (`/courses/{course}`) e mentorias (`/mentorships/{mentorship}`) exibem nota média, total de avaliações e grade com comentários aprovados.
- **Envio com estrelas:** membro autenticado envia avaliação de 1 a 5 estrelas + comentário; curso exige acesso ativo para avaliação.
- **Moderação:** toda avaliação entra como `pending` e pode ser aprovada/recusada em `/admin/reviews`.
- **Gestor ou plataforma:** admins moderam tudo; gestores moderam apenas avaliações dos próprios cursos/mentorias.
- **Rotas principais:**
  - `POST /courses/{course}/reviews`
  - `POST /mentorships/{mentorship}/reviews`
  - `GET /admin/reviews`

## Contato + reCAPTCHA v3

- **Página:** `/contato` (dados como e-mail/telefone/endereço são lidos de Configurações → Geral).
- **Envio:** `POST /contato` envia e-mail para `company_email` (fallback `mail.from.address`) aplicando automaticamente as configs SMTP salvas no banco (`smtp_*`).
- **reCAPTCHA v3:** configure no `.env` ou no Admin (Configurações → Geral → Segurança (reCAPTCHA v3)):
  - `RECAPTCHA_V3_SITE_KEY`
  - `RECAPTCHA_V3_SECRET_KEY`
  - `RECAPTCHA_V3_MIN_SCORE` (padrão 0.5)

## SEO & Analytics (Admin)

- **Admin:** Configurações → **SEO & Analytics**
  - Meta padrão: title/description/keywords + robots + google verification
  - Imagens sociais: OpenGraph e Twitter (com validação mínima de tamanho)
  - Trechos de rastreamento: `tracking_head` (HEAD) e `tracking_body` (BODY)
- **Contador de visitas:** tabela `visitor_logs` + cards/resumos no mesmo tab.
- **Geolocalização (opcional):** informe `IPINFO_TOKEN` no `.env` para enriquecer país/cidade/região.

## Cron interno (fallback)

Para hospedagens sem cron confiável, existe um fallback que roda `schedule:run` em background (na finalização das requisições do site), respeitando um intervalo mínimo.

- Variáveis no `.env`:
  - `INTERNAL_CRON_ENABLED=true`
  - `INTERNAL_CRON_MIN_INTERVAL_SECONDS=60`
  - `INTERNAL_CRON_RUN_QUEUE_WORKER=true`

Observação: por depender de tráfego, isso não é “cirúrgico” como um cron real. Em produção, recomenda-se configurar cron do cPanel para `php artisan schedule:run` e para processar filas.

Instalador Web
- Acesse `/install` em ambiente sem `APP_INSTALLED=true` para executar migrations, seeders e criar o administrador inicial via formulário web.
- O instalador gerará `APP_KEY`, rodará `php artisan migrate --seed` e adicionará `APP_INSTALLED=true` no `.env` (se permitido).

Uploads
- Teste de upload chunked disponível em `/admin/upload-test`
- Configurações de formatos/limites e disco de upload em `config/uploads.php` ou no Admin (Configurações → Geral → Limites de Upload / Armazenamento S3)
2. Para hospedagem em cPanel sem SSH, gere o projeto localmente e suba os arquivos (incluindo `vendor/`).

3. Gateways de pagamento configurados por placeholders: MercadoPago e PagSeguro.

4. Frontend inicial convertido para Blade em `resources/views/site` (você pode ajustar e adicionar assets em `public/`).

Recursos incluídos no scaffold:
- Rotas básicas
- Controller `HomeController` com views: `index`, `portal`, `premium`
- PWA com manifest dinâmico e service-worker
- Arquivos de autenticação (esqueleto) e página administrativa a integrar (AdminLTE)

Próximo passo sugerido: rodar `composer install` e executar o `artisan` para validar o scaffold.

## Instalação resiliente

- O `.htaccess` na raiz encaminha as rotas para `public/index.php` e expõe assets sem `/public` (ex.: `/img`, `/uploads`, `/service-worker.js`, `/manifest.webmanifest`).
- O `.htaccess` dentro de `public/` segue o padrão do Laravel para quando a hospedagem aponta o DocumentRoot diretamente para `public/`.
- Enquanto o banco estiver offline, o layout público (e o painel admin) exibem um aviso em amarelo, e o `AppServiceProvider` registra `Banco de dados indisponível` nos logs.
- Depois que o banco estiver disponível, rode `php artisan migrate --force` e `php artisan db:seed` para restaurar os dados e habilitar a interface administrativa.

## Próximos passos recomendados

1. Conferir se `public/storage` (symlink do `storage/app/public`) e `storage/logs` existem e têm permissão de escrita; valide `public/service-worker.js` e demais assets no servidor.
2. Atualizar `.env` com `APP_URL`, credenciais de pagamentos e as variáveis do PHPMailer.
3. Testar rotas de webhook e o instalador (`/install`) antes de abrir o admin.

## Novas funcionalidades do portal

- Os dados de eventos gratuitos, mentorias pagas e ranking agora são carregados do backend Laravel (`HomeController`) e podem ser acessados publicamente mesmo se o banco estiver no modo leitura (avisos aparecem nas views públicas). Os templates Blade nas pastas `resources/views/site` exibem cards dinâmicos com essas informações.
- Endpoints JSON expõem o fluxo de networking:
  - `POST /api/interactions` recebe `user_from_id`, `user_to_id`, `message` e meta opcional para registrar conexões dentro do mesmo nível.
  - `POST /api/satisfactions` grava a pesquisa de satisfação com `interaction_id`, `rating` (1-5) e comentário, atualizando automaticamente o ranking.
  - `GET /api/ranking` mostra os primeiros colocados e o resumo por nível para alimentar dashboards ou widgets externos.
- O serviço `App\Services\RankingService` recalcula média, contagem e score baseado nos feedbacks e armazena em `rankings`, garantindo que o ranking do portal sempre reflita o histórico real das conexões.

## Atualizações de Design e Responsividade (Fevereiro 2026)

- **Landing Page (Home):** Otimização da seção Hero e Stats para dispositivos móveis (redução de padding e fontes).
- **Portal de Networking:** Ajustes de layout no cabeçalho e grids de estatísticas para melhor visualização em celulares.
- **Eventos:** Correção de espaçamentos no cabeçalho e listagem de eventos. Detalhes do evento agora totalmente responsivos.
- **Página de Membros:** Otimização dos cards de membros e cabeçalho.
- **Páginas Institucionais:**
  - **Sobre Nós:** Ajustes de tipografia e espaçamento no mobile.
  - **Contato:** Layout otimizado para formulário e informações de contato em telas pequenas.
  - **Como Funciona:** Redesign da seção de passos e cards de preços para mobile.
  - **Manifesto:** Melhoria na legibilidade do texto e citações em dispositivos móveis.
  - **Quem Somos:** Ajustes na seção de equipe e fundadores.
  - **Valores:** Otimização do grid de valores e espaçamentos.
- **Geral:** Padronização de classes utilitárias (`pt-10 md:pt-24`, fontes responsivas) em todo o frontend para garantir uma experiência consistente em qualquer dispositivo.

## Sistema de Permissões e Networking (Fevereiro 2026)

- **Permissões Granulares:**
  - Sidebar dinâmica: Membros comuns veem apenas Dashboard, Comunidade e Chat. Painéis administrativos (Financeiro, Cursos, Usuários) restritos a Administradores.
  - Segurança de Rotas: Middleware garante que usuários comuns não acessem áreas restritas via URL direta.

- **Conexões Inteligentes:**
  - **Auto-conexão:** Novos membros conectam-se automaticamente a todos os administradores ao se registrar, facilitando o onboarding.
  - **Isolamento de Super Admin:** O Super Administrador permanece invisível nas listas de conexão e seu perfil é privado, atuando estritamente como gestor.

- **Chat & Comunidade:**
  - Integração direta entre perfil e chat privado.
  - Botões de ação dinâmicos (Conectar, Aceitar, Mensagem) baseados no status da relação entre usuários.

## Refinamento da Gestão de Usuários (Melhorias de UI/UX)

- **Listagem Avançada:**
  - Implementação de `DataTables` para listagem de usuários com busca instantânea, ordenação e paginação.
  - Otimização do carregamento de dados e interface responsiva.

- **Controle de Visibilidade:**
  - Filtros automáticos: Usuários com nível `superadmin` são ocultados da lista para administradores comuns.
  - Segurança aprimorada contra alterações não autorizadas em contas críticas.

- **Formulário Inteligente:**
  - Padronização de campos `Papel` e `Nível` com menus de seleção (Dropdowns).
  - Lógica de permissão: Opção `Super Admin` restrita a quem já possui o cargo.
  - Bloqueio de auto-edição: Usuários não podem alterar seu próprio nível de acesso.

- **Correções de UI/UX:**
  - **Upload de Perfil:** Barra de progresso visual para foto de capa e perfil.
  - **Feedback Instantâneo:** Preview de imagens atualizado via AJAX sem necessidade de recarregar a página.

- **Perfil Público Dinâmico:**
  - Exibição de dados reais: Biografia, Localização e Cargo agora são puxados diretamente do cadastro.
  - Integração visual completa com o feed de atividades do usuário.

## Impersonation e Dashboards Personalizados (Fevereiro 2026)

- **Admin Impersonation (Acessar como Usuário):**
  - **Permissões:** Admins podem acessar contas de membros; SuperAdmins podem acessar admins e membros.
  - **Proteção:** SuperAdmins não podem impersonate outros superadmins.
  - **Funcionalidade:** Botão "Acessar como usuário" (🕵️) na listagem de usuários permite suporte e testes.
  - **Retorno:** Botão "Voltar ao Admin" permite retornar à sessão original do administrador.

- **Separação de Dashboards:**
  - **Dashboard Admin:** Analytics, vendas, reembolsos, usuários registrados, gráficos de performance (restrito a admins).
  - **Portal do Membro:** Eventos, cursos, mentorias disponíveis, ranking da comunidade, grupos WhatsApp (para todos).
  - **Acesso Unificado:** Todo acesso ao painel é via `/admin` (membro/admin). A rota `/portal` é um alias e redireciona para o dashboard do painel.

- **Sidebar Dinâmica:**
  - **Para Membros:** Dashboard (portal), Comunidade, Chat, Cursos (Meus Cursos), Eventos (Calendário), Mentorias (Disponíveis).
  - **Para Admins:** Todos os itens de membros + Seção "ADMINISTRAÇÃO" com Usuários, Configurações, Planos, Vendas, etc.
  - **Menu Híbrido:** Cursos, Eventos e Mentorias têm submenus diferentes: membros veem "Meus Cursos", admins veem "Gerenciar" e "Novo".

- **Navbar e Layout:**
  - **Banner Admin:** Oculto para membros.
  - **Banner Portal:** Adicionado banner amarelo no topo do site (`layout.app`) quando em modo impersonate, permitindo retorno rápido ao painel admin.

- **Segurança:**
  - `AdminMiddleware` usa `isAdmin()` do model User para validação robusta.

## Controle de Acesso e Experiência Unificada (Fevereiro 2026)

- **Controle Estrito de Conteúdo (Courses):**
  - **Validação de Matrícula/Compra:** Membros só conseguem acessar ("Assistir") cursos que possuem matrícula ativa ou pedido pago (`HasPackageAccess`). Caso contrário, são convidados a "Adquirir".
  - **Proteção de Rotas:** Tentativas de acesso direto a URLs de cursos não comprados retornam 403 Forbidden para membros.

- **Admin Bypass Global:**
  - **Correção de Permissões:** Administradores agora possuem bypass global (`isAdmin()`) nas Policies e Traits (`HasRoles`), permitindo inspeção de contas de alunos sem erros de "Acesso Negado".
  - **Visualização de Membro:** Ao inspecionar um aluno, o Admin vê exatamente o que o aluno tem (ou não tem) acesso, sem ser bloqueado pelo sistema.

- **Dashboard Unificado & Impersonation Seguro:**
  - **Interface Adaptável:** O `dashboard.blade.php` foi unificado. Widgets administrativos (Financeiro) aparecem apenas para Admins. Widgets de usuário (Meus Cursos) aparecem para todos.
  - **Impersonation Seguro:** A função "Acessar como usuário" agora redireciona explicitamente para o Dashboard Seguro, evitando loops de redirecionamento ou erros de permissão em rotas protegidas.

## Sistema RBAC Multi-Tenant e Chat em Tempo Real (Fevereiro 2026)

- **RBAC Multi-Tenant Avançado:**
  - **Trait `HasFeatureAccess`:** Controle centralizado de acesso a funcionalidades baseado em planos.
  - **Middleware `CheckFeature`:** Bloqueio dinâmico de rotas conforme a assinatura do membro.
  - **Planos Dinâmicos:** Permissões de JSON injetadas diretamente nos planos (Eventos, Mentoria, Chat, Cursos).
  - **Segurança Admin:** Bypass global para administradores em todas as verificações de feature.

- **Sistema de Conexões e Privacidade:**
  - **Fluxo de Convites:** Solicitação de conexão necessária para comunicação entre membros.
  - **Privacidade de Perfil:** Opção de ocultar perfil para não-conectados, permitindo apenas a visualização de nome e foto básica.
  - **Controle de Bloqueio:** Usuários bloqueados são filtrados do feed social e da lista de membros.

- **Chat Real-Time Otimizado (cPanel Friendly):**
  - **Sincronização via Polling:** Atualização instantânea de mensagens sem recarregar a página, compatível com qualquer hospedagem PHP/MySQL.
  - **Gestão de Sessão:** Detecção de aba ativa para reduzir o consumo de recursos do servidor.
  - **Notificações em Tempo Real:** Contador de convites pendentes no cabeçalho atualizado dinamicamente.

## Gestão de Planos e Controle de Acesso (Fevereiro 2026)

- **Paywall e Middleware de Acesso:**
  - **Middleware `EnsureUserHasActivePlan`**: Implementação de bloqueio automático para usuários autenticados sem plano ativo, com redirecionamento para a vitrine de planos.
  - **Whitelist de Rotas**: Acesso garantido a Perfil, Checkout e Logout mesmo para usuários inadimplentes ou sem plano.
  - **Onboarding Otimizado**: O fluxo de registro agora direciona novos membros diretamente para a página `/premium` para escolha do plano.

- **Administração de Planos (CRUD):**
  - **Gestor de Planos**: Interface administrativa para criação e edição de planos (nome, preço, periodicidade e destaque).
  - **Sistema de Benefícios**: Armazenamento flexível de benefícios por plano para exibição dinâmica no frontend.
  - **Atribuição Manual**: Admins podem conceder acesso manualmente a usuários através do campo `plan_id` e definir data de expiração personalizada no formulário de edição de usuário.

- **Vitrine e Conteúdo Real:**
  - **Vitrine Dinâmica**: A página `/premium` agora renderiza os planos diretamente do banco de dados, respeitando o status de destaque ("Mais Popular").
  - **Fim dos Dados de Demonstração**: Os controladores de Eventos e Home agora carregam exclusivamente dados reais do banco de dados, removendo os fallbacks estáticos (mock data).
  - **Sincronização FullCalendar**: O calendário de eventos agora consome o feed real em formato ISO8601, garantindo precisão nas datas e interatividade.
  - **Imagem do Evento**: Upload de imagem no Admin (calendário e formulário) e exibição no destaque de `/eventos` e no card da página do evento.
