## 1. Database & Model

- [x] 1.1 Criar migration para tabela `share_requests`
- [x] 1.2 Criar model `ShareRequest` com relacionamentos e scopes (pending, expired)
- [x] 1.3 Adicionar índices para performance

## 2. Controller & Rotas

- [x] 2.1 Modificar `SocialController::sharePostToUser()` para criar ShareRequest ao invés de Post direto
- [x] 2.2 Criar método `ShareRequestController::approve()` que cria o Post e marca request como approved
- [x] 2.3 Criar método `ShareRequestController::reject()` que marca request como rejected
- [x] 2.4 Criar método `ShareRequestController::index()` para listar pendentes do usuário logado
- [x] 2.5 Adicionar rotas POST `/compartilhamentos/{id}/aprovar`, `/compartilhamentos/{id}/recusar`, GET `/compartilhamentos/pendentes`

## 3. Views & Frontend

- [x] 3.1 Atualizar view de feed para mostrar seção de solicitações pendentes (se houver)
- [x] 3.2 Criar partial para exibir card de solicitação com botões Aprovar/Rejeitar
- [x] 3.3 Atualizar UI de compartilhamento — mensagem clara de "solicitação" enviada
- [x] 3.4 Adicionar funções JS approveShareRequest/rejectShareRequest com feedback SweetAlert2

## 4. Notificações

- [x] 4.1 Criar notificação para destinatário ao receber nova solicitação
- [x] 4.2 Criar notificação para remetente quando solicitação é aprovada ou recusada
- [x] 4.3 Integrar badge de pendentes com sistema de polling existente

## 5. Jobs & Limpeza

- [x] 5.1 Criar comando `share-requests:expire` para expirar solicitações antigas (7+ dias)
- [x] 5.2 Agendar comando no Kernel (diário às 02:00)

## 6. Testes & Validação

- [x] 6.1 Testar fluxo completo: criar solicitação → aprovar → verificar post criado
- [x] 6.2 Testar fluxo de rejeição
- [x] 6.3 Testar expiração automática
- [x] 6.4 Testar que membro não pode aprovar solicitação de outro membro
