# Design: Sistema Administrativo Total e Marketplace

## Visão Geral
Este design foca na transformação da plataforma em um ecossistema de multi-vendedores (Marketplace) e na centralização do gerenciamento de conteúdo (CMS) no painel administrativo. 

## Arquitetura Técnica

### 1. CMS Dinâmico (Frontend Management)
- **Implementação**: Utilizaremos uma tabela `settings` ou `site_contents` para armazenar blobs JSON indexados por slug de página (ex: `home_hero`, `about_us`).
- **Controlador**: `Admin/CMSController` com métodos para editar campos específicos (títulos, descrições, imagens).
- **Frontend**: Helper global para recuperar esses dados nas views do site institucional.

### 2. Sistema de Vendedores (Instrutores)
- **Propriedade**: Os modelos `Course` e `Mentorship` serão estendidos para garantir que o `user_id` do criador seja validado contra o plano do usuário (VIP/Completo).
- **Vendor Panel**: Um sub-menu no Admin que filtra apenas os itens criados pelo usuário autenticado.

### 3. Gateway de Pagamento e Divisão de Comissão
- **Tokens por Membro**: Utilização do modelo `GatewayAccount`. O controlador de checkout (`SubscriptionController` ou um novo `OrderController`) buscará as credenciais do **dono do produto** em vez das credenciais globais da plataforma.
- **Lógica de Comissão**:
    - O Admin configurará uma `platform_fee_percent` global.
    - **Mercado Pago Split**: Utilizaremos a funcionalidade de *Split de Pagamento* ou *Marketplace Application* onde o valor é dividido no ato da transação (Vendor recebe X, Plataforma recebe Y).
    - **Registro**: Cada transação gerará uma entrada na tabela `commissions` para auditoria administrativa.

### 4. Chat Integrado no Admin
- **Layout**: Adaptação da interface de chat para o `content-wrapper` do AdminLTE.
- **Sidebar de Contatos**: Lista de contatos (conexões aceitas) renderizada na lateral do painel administrativo.

## Mudanças no Banco de Dados
- **`settings`**: Adição de campos para comissão padrão e chaves globais da plataforma.
- **`courses` / `mentorships`**: Garantir integridade do `user_id`.
- **`gateway_accounts`**: Onde o membro insere seu `access_token` e `public_key`.

## Segurança e Permissões
- **Middleware `check.feature:vendor`**: Protege o acesso às ferramentas de criação de conteúdo.
- **Validação de Credenciais**: Teste de conexão do gateway antes de permitir a ativação do perfil de vendedor.
