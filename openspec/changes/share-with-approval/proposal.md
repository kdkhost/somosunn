## Why

Atualmente, quando um membro compartilha um post para a linha do tempo de outro membro, o post é publicado imediatamente sem consentimento do destinatário. Isso pode causar spam e conteúdo indesejado nos perfis dos usuários. O sistema precisa permitir que o destinatário aprove ou rejeite compartilhamentos antes de aparecerem em sua linha do tempo.

## What Changes

- **Novo fluxo de compartilhamento para outros membros**: Ao compartilhar para outro membro, cria-se uma solicitação pendente ao invés de postar diretamente
- **Sistema de aprovação/rejeição**: O destinatário recebe notificação e pode aprovar ou rejeitar o compartilhamento
- **Inbox de solicitações**: Nova interface para o membro visualizar e gerenciar solicitações de compartilhamento pendentes
- **Expiração automática**: Solicitações não respondidas expiram após período configurável
- **Compartilhamento na própria timeline**: Continua funcionando como hoje (sem aprovação)
- **Compartilhamento em redes sociais externas**: Continua funcionando como hoje (WhatsApp, Facebook, Telegram, copiar link)

## Capabilities

### New Capabilities
- `share-requests`: Sistema de solicitações de compartilhamento entre membros com fluxo de aprovação/rejeição
- `share-notifications`: Notificações para novos compartilhamentos pendentes e respostas

### Modified Capabilities
<!-- Nenhuma capability existente tem suas regras de negócio alteradas -->

## Impact

- **Database**: Nova tabela `share_requests` (post_id, from_user_id, to_user_id, message, status, expires_at)
- **Controllers**: Modificação em `SocialController::sharePostToUser()` para criar solicitação ao invés de post direto
- **Views**: Nova seção no perfil/feed para gerenciar solicitações pendentes
- **Rotas**: Novas rotas para aprovar/rejeitar solicitações
- **Notificações**: Integração com sistema de notificações existente
