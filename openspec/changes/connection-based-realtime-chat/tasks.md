# Tasks: Implementação de Chat Real-Time e Conexões

## Infraestrutura e Banco de Dados
- [x] Criar migration para a tabela `connections` (user_id, friend_id, status, hide_profile) <!-- id: 0 -->
- [x] Adicionar índices nas colunas `user_id` e `friend_id` para otimizar busca de status <!-- id: 1 -->
- [x] Criar modelo `Connection` com escopos para `accepted`, `pending` e `blocked` <!-- id: 2 -->

## Fluxo de Conexão e Notificações
- [x] Implementar `ConnectionController` com métodos `request`, `accept`, `reject` e `block` <!-- id: 3 -->
- [x] Adicionar middleware ou lógica no `SocialController` para notificar novos convites via pooling <!-- id: 4 -->
- [x] Atualizar componente de perfil para exibir botões dinâmicos baseados no status da conexão <!-- id: 5 -->

## Privacidade e Controle de Acesso
- [x] Implementar política de privacidade que esconde perfis de usuários na busca se `hide_profile` ou `blocked` <!-- id: 6 -->
- [x] Criar middleware `EnsureConnectionIsAccepted` para proteger rotas de chat <!-- id: 7 -->

## Motor de Chat Real-Time (cPanel)
- [x] Criar endpoint `/chat/sync` no `ChatController` que retorna JSON de novas mensagens <!-- id: 8 -->
- [x] Implementar lógica de frontend (Alpine.js) para polling adaptativo (3s a 30s) <!-- id: 9 -->
- [x] Integrar janela de chat no layout do painel administrativo (AdminLTE) <!-- id: 10 -->

## Testes e Validação
- [x] Testar fluxo de convite -> aceite -> liberação de chat entre dois usuários de teste <!-- id: 11 -->
- [x] Validar bloqueio e interrupção imediata da sincronização do chat <!-- id: 12 -->
- [x] Monitorar carga de requisições no servidor via logs para garantir estabilidade no cPanel <!-- id: 13 -->
