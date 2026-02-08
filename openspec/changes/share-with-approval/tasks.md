## 1. Database & Model

- [ ] 1.1 Criar migration para tabela `share_requests` (id, post_id, from_user_id, to_user_id, message, status, expires_at, timestamps)
- [ ] 1.2 Criar model `ShareRequest` com relacionamentos (post, fromUser, toUser) e scopes (pending, expired)
- [ ] 1.3 Adicionar índices para performance (to_user_id + status, expires_at)

## 2. Controller & Rotas

- [ ] 2.1 Modificar `SocialController::sharePostToUser()` para criar ShareRequest ao invés de Post direto
- [ ] 2.2 Criar método `ShareRequestController::approve()` que cria o Post e marca request como approved
- [ ] 2.3 Criar método `ShareRequestController::reject()` que marca request como rejected
- [ ] 2.4 Criar método `ShareRequestController::index()` para listar pendentes do usuário logado
- [ ] 2.5 Adicionar rotas POST `/share-request/{id}/approve`, `/share-request/{id}/reject`, GET `/share-requests`

## 3. Views & Frontend

- [ ] 3.1 Atualizar view de feed para mostrar seção de solicitações pendentes (se houver)
- [ ] 3.2 Criar partial para exibir card de solicitação com botões Aprovar/Rejeitar
- [ ] 3.3 Atualizar UI de compartilhamento para deixar claro que é "solicitação" quando para outro membro
- [ ] 3.4 Adicionar badge de contagem no menu/sidebar quando há pendentes

## 4. Notificações

- [ ] 4.1 Criar notificação para destinatário ao receber nova solicitação
- [ ] 4.2 Criar notificação para remetente quando solicitação é aprovada
- [ ] 4.3 Integrar badge de pendentes com sistema de polling existente

## 5. Jobs & Limpeza

- [ ] 5.1 Criar comando/job para expirar solicitações antigas (7+ dias)
- [ ] 5.2 Agendar job no scheduler (diário)

## 6. Testes & Validação

- [ ] 6.1 Testar fluxo completo: criar solicitação → aprovar → verificar post criado
- [ ] 6.2 Testar fluxo de rejeição
- [ ] 6.3 Testar expiração automática
- [ ] 6.4 Testar que membro não pode aprovar solicitação de outro membro
