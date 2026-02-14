## MODIFIED Requirements

### Requirement: Dashboard personalizada e métricas em tempo real
O sistema SHALL exibir dashboards customizadas para cada membro conforme plano/pacote, e dashboards de admin/superadmin com visão consolidada, contadores de visitas em tempo real e métricas de cada produto (mentoria, cursos, palestras, eventos, site). Responsáveis por produtos devem acessar suas próprias métricas.

#### Scenario: Membro acessa dashboard conforme plano
- **WHEN** um membro faz login
- **THEN** o sistema exibe dashboard com informações e acessos de acordo com seu plano/pacote

#### Scenario: Admin/superadmin acessa dashboard de controle
- **WHEN** admin ou superadmin acessa o painel
- **THEN** o sistema exibe visão consolidada do sistema, com contadores em tempo real e métricas de todos os produtos

#### Scenario: Responsável visualiza métricas do seu produto
- **WHEN** um responsável por mentoria, curso, palestra ou evento acessa o painel
- **THEN** o sistema exibe as métricas e pesquisas apenas dos produtos sob sua responsabilidade

#### Scenario: Contadores de visitas atualizam em tempo real
- **WHEN** há novas visitas em qualquer produto
- **THEN** os contadores e gráficos das dashboards são atualizados automaticamente, sem recarregar a página
