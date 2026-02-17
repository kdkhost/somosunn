## MODIFIED Requirements

### Requirement: Dashboard do membro adaptável
A dashboard do membro SHALL adaptar widgets, métricas e gráficos conforme permissões, plano e papel, garantindo experiência personalizada e responsiva.

#### Scenario: Membro com plano básico vê widgets limitados
- **WHEN** um membro com plano básico acessa a dashboard
- **THEN** o sistema exibe apenas widgets e métricas permitidas pelo plano

#### Scenario: Membro com plano avançado vê widgets extras
- **WHEN** um membro com plano avançado acessa a dashboard
- **THEN** o sistema exibe widgets e métricas adicionais conforme o plano
