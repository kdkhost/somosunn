## ADDED Requirements

### Requirement: Notificar destinatário sobre nova solicitação
O sistema DEVE notificar o destinatário quando receber uma nova solicitação de compartilhamento.

#### Scenario: Notificação de nova solicitação
- **WHEN** membro recebe nova solicitação de compartilhamento
- **THEN** sistema cria notificação com título "Solicitação de compartilhamento"
- **AND** notificação inclui nome do remetente e preview do conteúdo
- **AND** notificação direciona para a lista de solicitações pendentes

### Requirement: Badge de contagem no menu
O sistema DEVE exibir badge com contagem de solicitações pendentes no menu.

#### Scenario: Exibição de badge
- **WHEN** membro tem solicitações pendentes
- **THEN** sistema exibe badge numérico próximo ao item de menu relevante
- **AND** badge é atualizado em tempo real (polling ou push)

#### Scenario: Sem pendentes
- **WHEN** membro não tem solicitações pendentes
- **THEN** badge não é exibido

### Requirement: Notificar remetente sobre aprovação
O sistema DEVE notificar o remetente quando sua solicitação for aprovada.

#### Scenario: Notificação de aprovação
- **WHEN** destinatário aprova solicitação
- **THEN** sistema cria notificação para o remetente com título "Compartilhamento aprovado"
- **AND** notificação inclui nome do aprovador e link para o post
