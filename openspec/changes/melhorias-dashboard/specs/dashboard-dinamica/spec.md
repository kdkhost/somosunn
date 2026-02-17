## ADDED Requirements

### Requirement: Dashboard dinâmica por permissões, plano e papel
A dashboard de cada usuário (membro, admin, superadmin) SHALL exibir apenas widgets, métricas e gráficos permitidos pelo seu plano, permissões e papel, de forma personalizada e responsiva.

#### Scenario: Membro visualiza dashboard personalizada
- **WHEN** um membro acessa sua dashboard
- **THEN** o sistema exibe apenas widgets e métricas permitidas pelo seu plano e permissões

#### Scenario: Admin visualiza dashboard completa
- **WHEN** um admin acessa sua dashboard
- **THEN** o sistema exibe todos os widgets e métricas administrativas

#### Scenario: Superadmin visualiza dashboard hyper completa
- **WHEN** um superadmin acessa sua dashboard
- **THEN** o sistema exibe todos os widgets, métricas e gráficos disponíveis

### Requirement: Atualização em tempo real de widgets e gráficos
A dashboard SHALL atualizar widgets e gráficos em tempo real, sem recarregar a página, usando websockets (Laravel Echo + Redis/Pusher) ou fallback para polling.

#### Scenario: Widget de visitas atualiza em tempo real
- **WHEN** há uma nova visita registrada
- **THEN** o contador de visitas na dashboard é atualizado instantaneamente para todos os usuários com permissão
