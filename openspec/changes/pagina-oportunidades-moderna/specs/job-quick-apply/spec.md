## ADDED Requirements

### Requirement: Candidatura rápida
O sistema SHALL permitir ao usuário candidatar-se rapidamente a uma vaga, sem etapas desnecessárias.

#### Scenario: Candidatura com perfil completo
- **WHEN** o usuário clica em "Candidatar-se" e possui perfil completo
- **THEN** o sistema envia a candidatura e exibe confirmação

#### Scenario: Candidatura com perfil incompleto
- **WHEN** o usuário clica em "Candidatar-se" e não possui perfil completo
- **THEN** o sistema solicita preenchimento dos campos obrigatórios antes de enviar

### Requirement: Visualização de detalhes
O sistema MUST permitir ao usuário visualizar detalhes da vaga antes de se candidatar.

#### Scenario: Visualizar detalhes
- **WHEN** o usuário clica em "Ver detalhes"
- **THEN** o sistema exibe informações completas da vaga
