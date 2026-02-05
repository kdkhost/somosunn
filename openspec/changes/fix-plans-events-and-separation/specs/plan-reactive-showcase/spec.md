# Spec: Plan Reactive Showcase

## ADDED Requirements

### Requirement: Premium page renders active plans from the database
O sistema SHALL renderizar a vitrine de planos em `GET /premium` a partir dos dados persistidos na tabela `plans`, sem usar dados mock/hardcoded para os cards de planos.

#### Scenario: Visitor sees active plans from database
- **WHEN** um visitante acessa `GET /premium`
- **THEN** a resposta SHALL exibir um card para cada plano com `is_active = true`
- **THEN** os cards SHALL refletir os campos persistidos (`name`, `description`, `price`, `period`, `benefits`, `image`, `highlight`)
- **THEN** a ordenação SHALL ser `highlight DESC`, depois `price ASC`

### Requirement: Exactly one plan can be highlighted
O sistema MUST garantir que exista no máximo 1 (um) plano com `highlight = true` por vez.

#### Scenario: Admin highlights a plan
- **WHEN** um admin marca `highlight = true` no plano A e salva
- **THEN** o sistema MUST definir `highlight = false` para todos os outros planos no mesmo fluxo de persistência
- **THEN** um acesso subsequente a `GET /premium` SHALL exibir apenas o plano A como destacado

#### Scenario: Admin removes highlight from the highlighted plan
- **WHEN** um admin desmarca `highlight` no plano atualmente destacado e salva
- **THEN** o sistema SHALL manter todos os planos com `highlight = false` até que outro plano seja destacado

### Requirement: Admin plan CRUD persists fields used by the frontend
O CRUD de planos no Admin MUST permitir criar/editar os campos necessários para renderização e checkout no Site: `name`, `slug`, `description`, `price`, `period`, `benefits`, `image`, `is_active`, `highlight`.

#### Scenario: Admin creates plan without slug
- **WHEN** um admin cria um plano sem informar `slug`
- **THEN** o sistema MUST gerar um `slug` a partir do `name` (kebab-case) e MUST garantir unicidade

#### Scenario: Admin updates description and benefits
- **WHEN** um admin atualiza `description` e `benefits` de um plano e salva
- **THEN** `GET /premium` SHALL exibir a descrição e a lista de benefícios exatamente conforme persistidos

### Requirement: Admin plan price accepts Brazilian currency input
O Admin MUST aceitar valores monetários no formato brasileiro (ex.: `97,00` e `R$ 97,00`) e MUST normalizar para decimal (`97.00`) antes de validar/persistir.

#### Scenario: Admin saves price with comma
- **WHEN** um admin informa `price = "49,90"` e salva
- **THEN** o valor persistido MUST ser `49.90`
- **THEN** `GET /premium` SHALL refletir o novo valor após refresh

### Requirement: Premium primary CTA uses database plan and never hardcodes price
O CTA primário do `/premium` MUST ser derivado do banco (nunca hardcoded): ele MUST apontar para o checkout do plano destacado; caso não exista plano destacado, MUST apontar para o plano ativo pago de menor preço; caso não exista plano pago, MUST apontar para o primeiro plano ativo.

#### Scenario: Highlighted plan drives CTA
- **WHEN** existe um plano com `highlight = true`
- **THEN** o CTA primário SHALL exibir nome/preço/período do plano destacado e SHALL linkar para o checkout desse plano

