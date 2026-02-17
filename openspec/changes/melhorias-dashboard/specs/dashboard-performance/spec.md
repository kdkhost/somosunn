## ADDED Requirements

### Requirement: Otimização de queries e uso de cache
Todas as consultas, updates e deletes relacionados à dashboard MUST ser otimizados para evitar sobrecarga no banco de dados, utilizando cache (Redis), agregação periódica e eager loading.

#### Scenario: Consulta de métricas usa cache
- **WHEN** um widget de métrica é carregado
- **THEN** o sistema utiliza cache para retornar o valor, evitando consulta direta ao banco

#### Scenario: Agregação periódica de dados pesados
- **WHEN** métricas de grande volume são necessárias
- **THEN** jobs periódicos agregam e armazenam os dados para acesso rápido
