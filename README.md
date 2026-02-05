# UNN — Plataforma de Networking (Scaffold)

Este diretório contém o scaffold inicial para um projeto Laravel 12 preparado para PHP 8.4 e hospedagem em cPanel.

Principais instruções rápidas:

1. Copie o conteúdo deste diretório para o servidor (ou use `composer create-project`):
   - `composer install`
   - `cp .env.example .env` e configure as variáveis (DB, pagamentos, social)
   - Ajuste `config/services.php` com as redirect URLs para Socialite
   - `php artisan key:generate`
   - `php artisan migrate --seed`
   - `php artisan storage:link`

PHPMailer
- Instale `composer require phpmailer/phpmailer` (already listed in composer.json)
- Configure `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` in .env
- Acesse `/admin/mail-test` para enviar um e-mail de teste

Payments (MercadoPago / PagSeguro)
- Configure as credenciais no `.env` conforme `MERCADOPAGO_ACCESS_TOKEN`, `PAGSEGURO_TOKEN`
- Webhooks endpoints:
  - /webhook/mercadopago
  - /webhook/pagseguro

PWA
- Ative/desative pelo painel administrativo em Configurações → PWA
- Upload de ícones no painel atualiza `manifest.webmanifest` dinamicamente

Instalador Web
- Acesse `/install` em ambiente sem `APP_INSTALLED=true` para executar migrations, seeders e criar o administrador inicial via formulário web.
- O instalador gerará `APP_KEY`, rodará `php artisan migrate --seed` e adicionará `APP_INSTALLED=true` no `.env` (se permitido).

Uploads
- Teste de upload chunked disponível em `/admin/upload-test`
- Configurações de formatos e limites em `config/uploads.php`
2. Para hospedagem em cPanel sem SSH, gere o projeto localmente e suba os arquivos (incluindo `vendor/`).

3. Gateways de pagamento configurados por placeholders: MercadoPago e PagSeguro.

4. Frontend inicial convertido para Blade em `resources/views/site` (você pode ajustar e adicionar assets em `public/`).

Recursos incluídos no scaffold:
- Rotas básicas
- Controller `HomeController` com views: `index`, `portal`, `premium`
- PWA manifest e service-worker placeholders
- Arquivos de autenticação (esqueleto) e página administrativa a integrar (AdminLTE)

Próximo passo sugerido: rodar `composer install` e executar o `artisan` para validar o scaffold.

## Instalação resiliente

- O `.htaccess` de raiz do repositório serve o `index.html` mesmo se o banco não estiver acessível; as requisições que usam `backend` são encaminhadas para `backend/public`.
- O `.htaccess` dentro de `backend/public` garante que futuras rotas Laravel caiam no front controller (mesmo que esse front controller ainda esteja sendo finalizado).
- Enquanto o banco estiver offline, o layout público (e o painel admin) exibem um aviso em amarelo, e o `AppServiceProvider` registra `Banco de dados indisponível` nos logs.
- Depois que o banco estiver disponível, rode `php artisan migrate --force` e `php artisan db:seed` para restaurar os dados e habilitar a interface administrativa.

## Próximos passos recomendados

1. Conferir se `backend/public/storage` e `storage/logs` existem e têm permissão de escrita; copie `public/service-worker.js`, `public/manifest.webmanifest` e demais assets para o servidor.
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
  - **Redirecionamento Automático:** Membros que tentam acessar `/admin` são redirecionados automaticamente para `/portal`.

- **Sidebar Dinâmica:**
  - **Para Membros:** Dashboard (portal), Comunidade, Chat, Cursos (Meus Cursos), Eventos (Calendário), Mentorias (Disponíveis).
  - **Para Admins:** Todos os itens de membros + Seção "ADMINISTRAÇÃO" com Usuários, Configurações, Planos, Vendas, etc.
  - **Menu Híbrido:** Cursos, Eventos e Mentorias têm submenus diferentes: membros veem "Meus Cursos", admins veem "Gerenciar" e "Novo".

- **Navbar e Layout:**
  - **Banner Admin:** Oculto para membros.
  - **Banner Portal:** Adicionado banner amarelo no topo do site (`layout.app`) quando em modo impersonate, permitindo retorno rápido ao painel admin.

- **Segurança:**
  - `AdminMiddleware` usa `isAdmin()` do model User para validação robusta.
  - `RedirectMembersFromAdmin` middleware registrado como backup.
  - Bloqueio efetivo de rotas `/admin` para não-administradores.
