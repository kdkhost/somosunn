## ADDED Requirements

### Requirement: Marketplace principal exibe produtos proprios publicados
O sistema MUST incluir uma secao de produtos proprios publicados no marketplace principal.

#### Scenario: Produtos proprios aparecem na vitrine
- **WHEN** existem produtos proprios publicados por vendedores elegiveis
- **THEN** o marketplace exibe esses itens na secao de produtos

### Requirement: Cards comerciais mostram a loja do vendedor
O sistema MUST exibir o vendedor responsavel pelos cards comerciais e MUST oferecer CTA para a loja do vendedor quando ela estiver publicada.

#### Scenario: Loja publicada habilita CTA de descoberta
- **WHEN** um curso, mentoria, evento ou produto proprio pertence a um vendedor com loja publicada
- **THEN** o card exibe "Anunciado e vendido por"
- **AND** exibe o botao "Ver mais desse vendedor"

#### Scenario: Loja nao publicada oculta CTA
- **WHEN** o vendedor ainda nao publicou sua loja
- **THEN** o card exibe apenas a informacao do vendedor sem CTA de loja

### Requirement: Compra direta continua disponivel no marketplace
O sistema MUST manter o botao de compra direta existente no marketplace mesmo quando houver CTA de descoberta da loja do vendedor.

#### Scenario: Card com compra direta e descoberta da loja
- **WHEN** um card comercial do marketplace e renderizado para um vendedor com loja publicada
- **THEN** o cliente continua vendo o CTA de compra direta
- **AND** tambem pode navegar para a loja do vendedor
