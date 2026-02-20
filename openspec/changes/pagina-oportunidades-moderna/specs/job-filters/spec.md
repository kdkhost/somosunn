## ADDED Requirements

### Requirement: Filtros avançados de vagas
A página de oportunidades MUST permitir ao usuário filtrar vagas por área, local, empresa e tipo de vaga.

#### Scenario: Filtrar por área
- **WHEN** o usuário seleciona uma área
- **THEN** o sistema exibe apenas vagas daquela área

#### Scenario: Filtrar por local
- **WHEN** o usuário seleciona um local
- **THEN** o sistema exibe apenas vagas daquele local

#### Scenario: Filtrar por empresa
- **WHEN** o usuário seleciona uma empresa
- **THEN** o sistema exibe apenas vagas daquela empresa

#### Scenario: Filtrar por tipo de vaga
- **WHEN** o usuário seleciona um tipo de vaga
- **THEN** o sistema exibe apenas vagas daquele tipo

### Requirement: Limpar filtros
O sistema SHALL permitir ao usuário limpar todos os filtros e retornar à lista completa.

#### Scenario: Limpar filtros
- **WHEN** o usuário clica em "Limpar filtros"
- **THEN** o sistema exibe todas as vagas disponíveis
