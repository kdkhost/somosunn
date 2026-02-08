## ADDED Requirements

### Requirement: Membro pode solicitar compartilhamento para outro membro
O sistema DEVE permitir que um membro autenticado solicite compartilhar um post na linha do tempo de outro membro com quem está conectado.

#### Scenario: Solicitação de compartilhamento bem-sucedida
- **WHEN** membro clica em "Compartilhar" e seleciona outro membro conectado
- **THEN** sistema cria uma solicitação de compartilhamento com status "pending"
- **AND** destinatário é notificado sobre a nova solicitação
- **AND** remetente vê mensagem de confirmação "Solicitação enviada"

#### Scenario: Tentativa de compartilhar para membro não conectado
- **WHEN** membro tenta compartilhar para usuário com quem não está conectado
- **THEN** sistema retorna erro 403 (proibido)

### Requirement: Destinatário pode aprovar solicitação
O sistema DEVE permitir que o destinatário aprove uma solicitação de compartilhamento pendente.

#### Scenario: Aprovação de solicitação
- **WHEN** destinatário clica em "Aprovar" em uma solicitação pendente
- **THEN** sistema cria o post compartilhado na timeline do destinatário
- **AND** solicitação é marcada como "approved"
- **AND** remetente é notificado sobre a aprovação

### Requirement: Destinatário pode rejeitar solicitação
O sistema DEVE permitir que o destinatário rejeite uma solicitação de compartilhamento pendente.

#### Scenario: Rejeição de solicitação
- **WHEN** destinatário clica em "Rejeitar" em uma solicitação pendente
- **THEN** solicitação é marcada como "rejected"
- **AND** solicitação é removida da lista de pendentes
- **AND** remetente NÃO é notificado (para evitar constrangimento)

### Requirement: Solicitações expiram após período configurado
O sistema DEVE expirar automaticamente solicitações não respondidas após 7 dias.

#### Scenario: Expiração automática
- **WHEN** solicitação completa 7 dias sem resposta
- **THEN** solicitação é marcada como "expired"
- **AND** solicitação é removida da lista de pendentes

### Requirement: Listar solicitações pendentes
O sistema DEVE exibir lista de solicitações de compartilhamento pendentes para o membro logado.

#### Scenario: Visualização de pendentes
- **WHEN** membro acessa área de solicitações pendentes
- **THEN** sistema exibe lista de solicitações com preview do post, nome do remetente e data
- **AND** lista é ordenada por data (mais recente primeiro)

#### Scenario: Sem solicitações pendentes
- **WHEN** membro não tem solicitações pendentes
- **THEN** sistema exibe mensagem "Nenhuma solicitação pendente"

### Requirement: Compartilhamento na própria timeline continua direto
O sistema DEVE manter o comportamento atual de compartilhar na própria timeline sem aprovação.

#### Scenario: Compartilhar na comunidade
- **WHEN** membro clica em "Compartilhar na comunidade"
- **THEN** sistema cria o post imediatamente na timeline do membro
- **AND** nenhuma solicitação é criada
