# Tarefas: Assinaturas Obrigatórias e Gestão de Planos

## 1. Infraestrutura e Banco de Dados
- [x] Criar migração para adicionar `plan_id` e `plan_expires_at` na tabela `users`.
- [x] Migrar campos de `plans` para suportar `benefits` (JSON) e `permissions` (JSON) se ainda não existirem.
- [x] Executar migrations e atualizar o modelo `User` com os novos campos e relacionamentos.
- [x] Atualizar o Trait `HasFeatureAccess` para considerar o `plan_id` manual quando não houver assinatura ativa.

## 2. Gestão Administrativa de Planos (CRUD)
- [x] Implementar `PlanController` para CRUD completo no painel administrativo.
- [x] Criar views para listagem, criação e edição de planos (AdminLTE).
- [x] Adicionar suporte para edição de benefícios (lista dinâmica) e permissões (checkboxes).
- [x] Garantir que o campo `highlight` seja gerenciado para destacar o plano na vitrine.

## 3. Paywall e Controle de Acesso
- [x] Criar o middleware `EnsureUserHasActivePlan`.
- [x] Implementar lógica de white-list no middleware (Perfil, Logout, Pagamento).
- [x] Registrar o middleware no `Kernel.php`.
- [x] Aplicar o middleware nas rotas internas de membros no `web.php`.
- [x] Atualizar o fluxo de registro para redirecionar novos membros diretamente para `/premium`.

## 4. Navegação e Perfil
- [x] Corrigir links de "Meu Perfil" na sidebar e navbar para apontar para `admin.profile.edit`.
- [x] Remover redirecionamentos forçados para `/portal` no `AdminMiddleware` que ignoram a intenção do usuário.
- [x] Validar que membros sem plano ativo conseguem editar seus próprios perfis.

## 5. Vitrine Dinâmica de Planos
- [x] Atualizar o `HomeController` para buscar planos ativos do banco de dados para a rota `/premium`.
- [x] Refatorar a view `resources/views/site/premium.blade.php` para renderizar os cards de planos dinamicamente.
- [x] Adaptar o design CSS/Tailwind para lidar com quantidades variáveis de planos.

## 6. Atribuição Manual de Planos
- [x] Adicionar seção de "Gestão de Assinatura" na página de edição de usuários do admin.
- [x] Implementar lógica para salvar `plan_id` e `plan_expires_at` manualmente.
- [x] Testar se a atribuição manual desbloqueia os recursos corretamente para o usuário.

## 7. Correção do FullCalendar v4 e Eventos Reais
- [x] Revisar `EventController@index` (Admin) para garantir consistência no feed JSON.
- [x] Corrigir `DashboardController` para passar eventos reais para o widget do calendário.
- [x] Ajustar `HomeController` para carregar eventos reais na seção "Palestras Gratuitas" (incluindo pagos se necessário).
- [x] Garantir que o `EventController` (Público) exiba os dados reais do banco na listagem.
- [x] Testar a interatividade do calendário (clique para detalhes) em ambos os contextos.
- [x] Validar que o design do front-end permanece idêntico ao original ("sem mudar uma vírgula").
