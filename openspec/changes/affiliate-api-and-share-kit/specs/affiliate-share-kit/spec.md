## ADDED Requirements

### Requirement: Afiliado deve receber materiais prontos para divulgação
O sistema SHALL exibir no painel do afiliado um kit de divulgação com textos prontos, CTA, benefícios, ativos da marca e ofertas recomendadas já parametrizadas com o link de indicação do membro.

#### Scenario: Exibir materiais no painel
- **WHEN** o afiliado acessar a página de indicações
- **THEN** o sistema MUST mostrar blocos de copy, CTA, benefícios, ativos visuais e ofertas prontas para divulgação com o link individual dele

#### Scenario: Aplicar fallback quando faltar conteúdo institucional
- **WHEN** um campo institucional do CMS ou Settings estiver vazio
- **THEN** o sistema MUST preencher o kit com conteúdo padrão coerente sem quebrar a interface

### Requirement: Kit deve servir criação de landing page externa
O sistema SHALL estruturar o material promocional em blocos reutilizáveis para que o afiliado consiga montar uma landing page externa.

#### Scenario: Expor blocos reutilizáveis
- **WHEN** o kit for gerado
- **THEN** o sistema MUST separar hero, benefícios, prova social, ofertas e CTA em estruturas independentes e testáveis

#### Scenario: Ofertas devem vir prontas para divulgação
- **WHEN** o sistema incluir planos, cursos, eventos ou mentorias no kit
- **THEN** cada item MUST trazer nome, descrição resumida, preço, imagem e URL já preparada para conversão com o código do afiliado
