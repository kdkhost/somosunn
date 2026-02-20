## ADDED Requirements

### Requirement: Destaque visual para empresas parceiras
A página de oportunidades SHALL destacar visualmente vagas de empresas parceiras, utilizando badges, cores ou cards especiais.

#### Scenario: Empresa parceira
- **WHEN** a vaga pertence a uma empresa parceira
- **THEN** o sistema exibe badge ou destaque visual na vaga

#### Scenario: Empresa comum
- **WHEN** a vaga pertence a uma empresa comum
- **THEN** o sistema exibe a vaga sem destaque especial

### Requirement: Regras de destaque
O sistema MUST definir regras claras para personalização e destaque de empresas.

#### Scenario: Regras de destaque
- **WHEN** uma empresa solicita destaque
- **THEN** o sistema verifica critérios e aplica destaque conforme regras
