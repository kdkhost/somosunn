## MODIFIED Requirements

### Requirement: Exibição condicional de widgets e métricas
O sistema MUST controlar a exibição de widgets e métricas na dashboard com base em permissões, plano e papel do usuário, usando traits, policies e middlewares customizados.

#### Scenario: Widget restrito não aparece para membro sem permissão
- **WHEN** um membro sem permissão tenta acessar um widget restrito
- **THEN** o widget não é exibido na dashboard

#### Scenario: Admin ajusta permissões e efeito é imediato
- **WHEN** o admin altera permissões de um plano
- **THEN** a mudança reflete imediatamente nas dashboards dos membros vinculados
