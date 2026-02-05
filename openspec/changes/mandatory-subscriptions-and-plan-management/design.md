# Design Técnico: Assinaturas Obrigatórias e Gestão de Planos

## Contexto
Este documento detalha a implementação técnica para forçar a seleção de um plano após o cadastro, permitir a gestão dinâmica de planos via painel administrativo e corrigir fluxos de navegação e acesso para membros.

## Decisões Técnicas

### 1. Modelo de Dados e Atribuição Manual
Para suportar o vínculo direto de planos sem passar pelo checkout (atribuição manual), o modelo `User` será expandido.

- **Alteração no Banco de Dados (Tabela `users`)**:
    - Adição de `plan_id` (foreign key para `plans`, nullable).
    - Adição de `plan_expires_at` (datetime, nullable).
- **Lógica de Verificação de Acesso**:
    - O método `canAccessFeature($feature)` do Trait `HasFeatureAccess` será atualizado para verificar se o usuário possui um `plan_id` com data de expiração válida (ou nula para vitalício) caso não encontre uma assinatura ativa na tabela `subscriptions`.

### 2. Middleware de Paywall (`EnsureUserHasActivePlan`)
Um novo middleware será responsável por interceptar requisições e garantir que apenas usuários pagantes ou administradores acessem as áreas internas.

- **Fluxo de Redirecionamento**:
    - Se o usuário **não for administrador** E **não possuir plano ativo**:
        - Permitir acesso apenas a rotas de "white-list" (Perfil, Logout, Pagamento).
        - Redirecionar todas as outras requisições para `/premium`.
- **White-list de Rotas**:
    - `admin.profile.*`
    - `logout`
    - `checkout.*`
    - `premium` (view pública)

### 3. Gestão Dinâmica de Planos (Admin CRUD)
A página `/premium` deixará de ser estática para refletir o estado do banco de dados.

- **Controlador Administrativo**: `App\Http\Controllers\Admin\PlanController` lidará com o CRUD.
- **Interface de Edição**:
    - Campo "Benefícios": Implementado como um campo JSON onde o admin insere uma lista de frases.
    - Campo "Permissões": Checkboxes baseados nas chaves de recursos (`courses`, `chat`, `events`, `mentorships`, `community`).
- **Página Premium Pública**:
    - O `HomeController` passará os planos ativos para a view.
    - O layout Blade será adaptado para um grid flexível (CSS Grid) que suporte de 1 a 4 planos de forma responsiva.

### 4. Correção de Navegação
- **Redirecionamento de Perfil**: O link "Meu Perfil" no menu lateral será explicitamente mapeado para a rota `admin.profile.edit`.
- **Fim do Loop `/portal`**: Removeremos lógicas redundantes no `AdminMiddleware` que forçavam redirecionamentos baseados apenas no papel do usuário, permitindo que a rota solicitada pelo usuário seja respeitada, desde que ele passe pelo middleware de paywall.

### 5. FullCalendar v4 e Dados do Banco
- **Padronização de ISO8601**: O modelo `Event` já possui acessores `getStartAttribute` e `getEndAttribute` que convertem para ISO8601 string. Garantiremos que todos os controllers utilizem esses atributos para o FullCalendar.
- **Remoção de Mock Data**: Ajustar os `else` blocks nos controladores `HomeController` e `EventController` para garantir que o fallback para dados demo só ocorra se `Event::count() == 0`.
- **Integração Dashboard**: O widget do Dashboard será atualizado para consumir os eventos reais formatados no controlador, garantindo que o `json_encode` gere o formato aceito pelo FullCalendar v4 (usando `plugins: ['dayGrid', 'interaction', 'bootstrap']`).

## Arquitetura de Implementação

1.  **Migração**: Criar migration para adicionar colunas em `users` e garantir que a tabela `plans` tenha todos os campos necessários.
2.  **Middleware**: Registrar o novo middleware no `Kernel.php` dentro do grupo `web`.
3.  **Controllers**:
    *   Atualizar o CRUD de usuários no Admin para incluir o seletor de plano.
    *   Finalizar o CRUD de Planos.
4.  **Views**:
    *   Refatorar `resources/views/site/premium.blade.php`.
    *   Ajustar links de navegação em `partials/sidebar.blade.php` e `partials/navbar.blade.php`.

## Considerações de Segurança
- Somente SuperAdmins poderão atribuir planos manualmente.
- O bypass de Admin será mantido em todas as verificações para garantir que a gestão do sistema nunca seja bloqueada por falta de plano.
